<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ScheduleController extends Controller
{
    public function schedule(Request $request)
    {
        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $validator = Validator::make(
            $request->all(),
            [
                'course_id' => 'required|integer',
                'class_opening' => 'required',
                'teachers' => 'required',
            ],
            [
                'required' => ':attribute không được để trống',
                'int' => ':attribute phải là một chuỗi int',
            ],
            [
                'course_id' => 'Course',
                'class_opening' => 'ClassOpenings',
                'teachers' => 'Teachers',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()], 403);
        }

        $course_id = Course::find($request->course_id);

        if (!$course_id) return response([
            'status' => 403,
            'message' => 'Course not found!',
        ], 200);

        $class_opening = $request->class_opening;

        // Check opening và room
        $check_opening = [
            'date' => false,
            'room' => false
        ];
        foreach ($class_opening as $item) {
            if (new Carbon($item['date']) < Carbon::now()) {
                $check_opening['date'] = true;
                break;
            }

            $room = Room::find($item['room']);
            if (!$room) {
                $check_opening['room'] = true;
                break;
            }
        }

        if ($check_opening['date']) return response([
            'status' => 403,
            'message' => 'Opening date must be greater than current date!',
        ], 200);

        if ($check_opening['room']) return response([
            'status' => 403,
            'message' => 'Room not found!',
        ], 200);

        // Danh sách giáo viên
        $teachersInit = $request->teachers;

        // Check giáo viên
        $check_teacher = false;
        foreach ($teachersInit as $item) {
            $teacher = Teacher::find($item['id']);
            if (!$teacher) {
                $check_teacher = true;
                break;
            }
        }

        if ($check_teacher) return response([
            'status' => 403,
            'message' => 'Teacher not found!',
        ], 200);

        // Khởi tạo danh sách lớp
        $classesInit = $this->classes_init($class_opening, $course_id->total_lesson);

        // Phân tích danh sách lớp lần 1
        $classesInit = $this->analysis_1($classesInit, $course_id->total_lesson);

        // Phân tích danh sách lớp lần 2
        $classesInit = $this->analysis_2($classesInit);

        // Sắp xếp danh sách giáo viên
        usort($teachersInit, function ($a, $b) {
            if ($a["priority"] === $b["priority"]) {
                return $a["total_class_work"] - $b["total_class_work"];
            }

            return $b["priority"] - $a["priority"];
        });

        //Từ danh sách giáo viên và danh sách lớp ta suy ra danh sách phân công đầu tiên
        $assignment_table = $this->assignment_1($classesInit, $teachersInit);

        // Thêm danh sách lớp vào cơ sở dữ liệu
        foreach ($assignment_table as $item) {
            ClassRoom::create([
                'class_id' => $item['class_id'],
                'quantity_minimum' => 10,
                'quantity_maxnimum' => 30,
                'opening_day' => new Carbon($item['opening']),
                'estimated_end_time' => (new Carbon($item['opening']))->addWeeks($item['total_weeks']),
                'status' => 1,
                'course_id' => $course_id->id,
                'room_id' => $item['room'],
                'teacher_id' => $item['teacher']
            ]);
        }

        //Rã cụm phân công
        $assignment_table2 = $this->assignment_2($assignment_table);

        //Phân tách opening
        $phan_tach_opening = $this->phan_tach_opening($assignment_table2);

        // Khởi tạo thời khoá biểu trống
        $schedule = $this->khoi_tao_schedule_init($phan_tach_opening);


        //Rãi thời khoá biểu
        $schedule_spreaded = $this->schedule_spreaded($phan_tach_opening, $schedule);

        // Group by từng lớp
        $group_by_class = [];
        foreach ($assignment_table as $item) {
            $group_by_class[$item['class_id']] = [];
        }

        foreach ($group_by_class as $group_by_class_key => $group_by_class_value) {
            foreach ($schedule_spreaded as $date_schedule => $lessons) {
                foreach ($lessons as $lesson => $classes) {
                    foreach ($classes as $room => $class) {
                        if (!array_key_exists($date_schedule, $group_by_class[$class['class_id']])) {
                            $group_by_class[$class['class_id']][$date_schedule] = [];
                        }

                        if (in_array($lesson, $group_by_class[$class['class_id']][$date_schedule])) continue;
                        
                        array_push($group_by_class[$class['class_id']][$date_schedule], $lesson);
                    }
                }
            }
        }

        // Thêm danh sách lịch học của các lớp vào cơ sở dữ liệu
        foreach ($group_by_class as $class_id => $info) {
            foreach ($info as $date => $lesson) {
                $lesson_string = collect($lesson)->implode('-');
                $class = ClassRoom::where('class_id', $class_id)->first();
                Schedule::create([
                    'date_learn' => new Carbon($date),
                    'lesson' => $lesson_string,
                    'class_id' => $class->id
                ]);
            }
        }

        return response([
            'status' => 200,
            'message' => 'Successfully!',
        ], 200);
    }

    private function classes_init($class_opening, $total_lesson)
    {
        $classesInit = [];
        $class_id = substr(Str::uuid()->toString(), 0, 8);
        foreach ($class_opening as $item) {
            $classesInit[] = [
                'class_id' => "tinhocstar-" . $class_id++,
                'opening' => $item['date'],
                'room' => $item['room'],
                'total_lesson' => $total_lesson,
            ];
        };
        return $classesInit;
    }

    private function analysis_1($classesInit, $total_lesson)
    {
        $analysis_1 = [];

        $analysis_1[$total_lesson] = [];

        for ($i = 1; $i <= 7; $i++) {
            array_push($analysis_1[$total_lesson], [
                'num' => floor($total_lesson / $i),
                'remainder' => $total_lesson % $i
            ]);
        }

        // Xét điều kiện Y
        $analysis_1_dkY = [];

        foreach ($analysis_1[$total_lesson] as $item) {
            if ($item['num'] >= 9 && $item['num'] <= 25 && $item['remainder'] == 0) {
                $analysis_1_dkY[] = $item['num'];
            }
        }


        if (count($analysis_1_dkY) <= 0) {
            $item_temp = 10;
            $arr_temp = [];
            foreach ($analysis_1[$total_lesson] as $item) {
                if ($item['num'] >= 9 && $item['num'] <= 25) {
                    if ($item['remainder'] <= $item_temp) {
                        $item_temp = $item['remainder'];
                        $arr_temp[$item['num']] = $item['num'];
                    }
                }
            }

            foreach ($arr_temp as $key => $val) {
                $analysis_1_dkY[] = $val;
            }
        }

        // Xét điều kiện X
        {
            $x_temp = null;
            foreach ($analysis_1_dkY as $item) {
                if ($x_temp == null) {
                    $x_temp = $item;
                } else if ($item < $x_temp) {
                    $x_temp = $item;
                }
            }

            foreach ($classesInit as $key => $value) {
                $classesInit[$key]['lession_in_one_week'] = $x_temp;
                $classesInit[$key]['total_weeks'] = ceil($value['total_lesson'] / $x_temp);
            }
        }

        return $classesInit;
    }

    private function analysis_2($classesInit)
    {
        $dkY_temp = [];
        for ($i = 2; $i <= 5; $i++) {
            $so_tiet = ceil($classesInit[0]['lession_in_one_week'] / $i);
            if ($so_tiet >= 2 && $so_tiet <= 4)
                $dkY_temp[$i] = $so_tiet;
        }

        $x_tiet = null;
        $x_days = null;
        foreach ($dkY_temp as $key => $value) {
            if ($x_tiet == null) {
                $x_tiet = $value;
                $x_days = $key;
            } else if ($value > $x_tiet) {
                $x_tiet = $value;
                $x_days = $key;
            }
        }

        $total_tiet = 0;
        $arr_days = [];
        for ($i = $x_tiet; $i <= $classesInit[0]['lession_in_one_week']; $i += $x_tiet) {
            $total_tiet += $x_tiet;
            $arr_days[] = $x_tiet;
        }

        if (($classesInit[0]['lession_in_one_week'] - $total_tiet) > 0) {
            if ($classesInit[0]['lession_in_one_week'] - $total_tiet == 1)
                $arr_days[count($arr_days) - 1] = $arr_days[count($arr_days) - 1] + 1;
            else
                $arr_days[] = $classesInit[0]['lession_in_one_week'] - $total_tiet;
        }

        foreach ($classesInit as $key => $value) {
            $classesInit[$key]['days_in_one_week'] = $x_days;
            $classesInit[$key]['consecutive_lesson'] = $arr_days;
        }

        return $classesInit;
    }

    private function assignment_1($classesInit, $teachersInit)
    {
        foreach ($classesInit as $key => $value) {

            foreach ($teachersInit as $keyT => $valueT) {
                $classesInit[$key]['teacher'] = $valueT['id'];

                if ($valueT['total_class_work'] > 1) {
                    $teachersInit[$keyT]['total_class_work'] = $valueT['total_class_work'] - 1;
                } else {
                    array_shift($teachersInit);
                }

                break;
            }
        }

        return $classesInit;
    }

    private function assignment_2($classesInit)
    {
        $pc_table = [];
        foreach ($classesInit as $value) {
            foreach ($value['consecutive_lesson'] as $item) {
                $temp = $value;
                $temp['consecutive_lesson'] = $item;
                array_push($pc_table, $temp);
            }
        }

        return $pc_table;
    }

    private function phan_tach_opening($assignment_table)
    {
        $temp = [];
        foreach ($assignment_table as $item) {
            $temp[$item['opening']] = [];
        }

        foreach ($temp as $key => $item) {
            $temp[$key] = [...array_filter($assignment_table, function ($item2) use ($key) {
                return $item2['opening'] === $key;
            })];
        }

        return $temp;
    }

    private function khoi_tao_schedule_init($phan_tach_opening)
    {
        $start_date = new Carbon(array_keys($phan_tach_opening)[0]);
        $end_date = (new Carbon(array_keys($phan_tach_opening)[count($phan_tach_opening) - 1]))->addWeeks(4);
        $schedule = [];
        do {
            $loop = true;
            for ($tiet = 1; $tiet <= 10; $tiet++) {
                $schedule[date('Y-m-d', strtotime($start_date))]["$tiet"] = [];
            }
            if ($start_date == $end_date) $loop = false;
            $start_date->addDay();
        } while ($loop);

        return $schedule;
    }

    private function check_on_date($check_room_on_day) {
        $arr_temp = [];

        foreach ($check_room_on_day as $item) {
            if (count($item) > 0) {
                foreach ($item as $key => $value) {
                    $arr_temp[] = $value['class_id'];
                }
            }
        }

        return $arr_temp;
    }

    private function schedule_spreaded($phan_tach_opening, $schedule) {
        foreach ($phan_tach_opening as $date_opening => $classes) {
            foreach ($classes as $class) {
                foreach ($schedule as $date_schedule => $lessons) {

                    if (new Carbon($class['opening']) > new Carbon($date_schedule)) continue;

                    $check_ = false;

                    $tiet_trong_ngay = 1;

                    foreach ($lessons as $lesson => $lesson_value) {
                        if ($lesson == 1 || $lesson == 6) continue;

                        if (!array_key_exists($class['room'], $lesson_value)) {
                            if ($tiet_trong_ngay > $class['consecutive_lesson']) break;

                            $check_room_on_day = $this->check_on_date($schedule[$date_schedule]);
                            if (in_array($class['class_id'], $check_room_on_day)) {
                                if (array_count_values($check_room_on_day)[$class['class_id']] >= $class['consecutive_lesson']) break;
                            };

                            $date_temp = new Carbon($date_schedule);
                            

                            for ($i = 0; $i < $class['total_weeks']; $i++) {
                                $schedule[date('Y-m-d',  strtotime($date_temp))][$lesson] += [$class['room'] => [
                                    "class_id" => $class['class_id'],
                                    "teacher" => $class['teacher'],
                                    "opening" => $class['opening'],
                                    "total_weeks" => $class['total_weeks'],
                                ]]; 
                                $date_temp->addWeek();
                            }

                            $check_ = true;
                            $tiet_trong_ngay++;
                        }
                    }

                    if ($check_) break;
                }
            }
        }

        return $schedule;
    }
}
