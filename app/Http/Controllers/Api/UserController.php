<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class UserController extends Controller
{
    public function getUser(Request $request)
    {
        $user =  auth('sanctum')->user();

        $response = [
            'status' => 201,
            'success' => 'success',
            'user' => $user,
        ];

        return response($response, 201);
    }

    public function get_learning_user(Request $request)
    {
        $user =  auth('sanctum')->user();

        $courses = [];
        foreach ($user->class_rooms as $item) {
            if ($item->pivot->status === 1) {
                $total_lecture = 0;
                foreach ($item->courses->units as $lecture) {
                    $total_lecture += count($lecture->lectures);
                }

                $courses[] = [
                    'id' => $item->courses->id,
                    'class_id' => $item->class_id,
                    'name' => $item->courses->name,
                    'slug' => $item->courses->slug,
                    'teacher' => $item->teachers->name,
                    'image' => $item->courses->image,
                    'total_unit' => count($item->courses->units),
                    'total_lecture' => $total_lecture,
                    'active' => $item->status,
                ];
            }
        }

        $wish_courses = [];
        foreach ($user->wish_courses as $item) {
            $total_lecture = 0;
            foreach ($item->units as $lecture) {
                $total_lecture += count($lecture->lectures);
            }

            $wish_courses[] = [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'image' => $item->image,
                'price' => $item->price,
                'total_unit' => count($item->units),
                'total_lecture' => $total_lecture
            ];
        }

        $response = [
            'status' => 201,
            'success' => 'success',
            'total_course' => count($courses),
            'courses' => $courses,
            'total_wish_course' => count($wish_courses),
            'wish_courses' => $wish_courses
        ];

        return response($response, 201);
    }

    public function get_class_user($class)
    {
        $user = auth('sanctum')->user();

        $classCheck = $user->class_rooms->where('class_id', $class)->first();

        if (!$classCheck) return response([
            'status' => 403,
            'success' => 'danger',
            'message' => 'You have not enrolled this class yet'
        ], 403);

        $units = [];
        foreach ($classCheck->courses->units as $item) {
            $lectures = [];
            foreach ($item->lectures as $lecture) {
                $lectures[] = [
                    'id' => $lecture->id,
                    'name' => $lecture->name,
                ];
            }
            $units[] = [
                'id' => $item->id,
                'name' => $item->name,
                'total_lecture' => count($item->lectures),
                'lectures' => $lectures
            ];
        }

        $response = [
            'status' => 201,
            'success' => 'success',
            'class' => [
                'id' => $classCheck->id,
                'class_id' => $classCheck->class_id,
                'name_course' => $classCheck->courses->name,
                'video_demo' => $classCheck->courses->video_demo,
                'units' => $units,
            ]
        ];

        return response($response, 201);
    }

    public function get_schedule_byclass_user($class, Request $request)
    {
        $user = auth('sanctum')->user();

        $classCheck = $user->class_rooms->where('class_id', $class)->first();

        if (!$classCheck) return response([
            'status' => 403,
            'success' => 'danger',
            'message' => 'You have not enrolled this class yet'
        ], 403);

        $schedule = [];
        foreach ($classCheck->schedule->where('date_learn', '>', (new Carbon($request->date_start))->subDay())->where('date_learn', '<', new Carbon($request->date_end)) as $item) {
            $schedule[] = [
                'class_id' => $classCheck->class_id,
                'date_learn' => $item->date_learn,
                'lesson' => $item->lesson,
                'room' => $classCheck->rooms->room,
                'teacher' => $classCheck->teachers->name,
                'name_course' => $classCheck->courses->name
            ];
        }

        $group_date = [];
        foreach ($schedule as $item) {
            $group_date[$item['date_learn']] = [];
        }

        foreach ($group_date as $date => $value) {
            $arr_temp = [];
            foreach ($schedule as $item) {
                if ($item['date_learn'] === $date)
                    array_push($arr_temp, $item);
            }
            $group_date[$date] = $arr_temp;
        }

        $response = [
            'status' => 201,
            'success' => 'success',
            'class' => [
                'id' => $classCheck->id,
                'class_id' => $classCheck->class_id,
                'name_course' => $classCheck->courses->name,
                'schedule' => $group_date,
            ]
        ];

        return response($response, 201);
    }

    public function get_schedule_user(Request $request)
    {
        $user = auth('sanctum')->user();

        $classes = $user->class_rooms;
        $schedule = [];
        foreach ($classes as $class) {
            foreach ($class->schedule->where('date_learn', '>', (new Carbon($request->date_start))->subDay())->where('date_learn', '<', new Carbon($request->date_end)) as $item) {
                $schedule[] = [
                    'class_id' => $class->class_id,
                    'name_course' => $class->courses->name,
                    'date_learn' => $item->date_learn,
                    'lesson' => $item->lesson,
                    'room' => $class->rooms->room,
                    'teacher' => $class->teachers->name,
                    'name_course' => $class->courses->name
                ];
            }
        }

        $group_date = [];
        foreach ($schedule as $item) {
            $group_date[$item['date_learn']] = [];
        }

        foreach ($group_date as $date => $value) {
            $arr_temp = [];
            foreach ($schedule as $item) {
                if ($item['date_learn'] === $date)
                    array_push($arr_temp, $item);
            }
            $group_date[$date] = $arr_temp;
        }
        

        $response = [
            'status' => 201,
            'success' => 'success',
            'class' => [
                'schedule' => $group_date
            ]
        ];

        return response($response, 201);
    }
}
