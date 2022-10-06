<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Room;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimeFrame;
use App\Models\WeekDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use \Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Support\Str;

class ClassRoomController extends Controller
{
    public function show() {
        return ClassRoom::all();
    }

    public function store(Request $request) {

        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);
        
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'quantity_minimum' => 'required|integer',
                'quantity_maxnimum' => 'required|integer|gte:quantity_minimum',
                'opening_day' => 'required',
                'course_id' => 'required|integer',
                'room_id' => 'required|integer',
                'teacher_id' => 'required|integer',
                'time_frame_id' => 'required|integer',
                'week_day_id' => 'required|integer',
            ],
            [
                'required' => ':attribute không được để trống',
                'integer' => ':attribute phải là một số',
                'gte' => ':attribute phải lớn hơn hoặc bằng số lượng tối thiểu',
            ],
            [
                'name' => 'Tên / Mã lớp học',
                'quantity_minimum' => 'Số lượng tối thiểu',
                'quantity_maxnimum' => 'Số lượng tối đa',
                'opening_day' => 'Ngày khai giảng',
                'course_id' => 'Khoá học',
                'room_id' => 'Phòng',
                'teacher_id' => 'Giảng viên',
                'time_frame_id' => 'Mốc thời gian',
                'week_day_id' => 'Thứ',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }

        $checkCourse = Course::find($request->course_id);
        $checkRoom = Room::find($request->room_id);
        $checkTeacher = Teacher::find($request->teacher_id);
        $checkTimeFrame = TimeFrame::find($request->time_frame_id);
        $checkWeekDay = WeekDay::find($request->week_day_id);

        if (!$checkCourse || !$checkRoom || !$checkTeacher || !$checkTimeFrame || !$checkWeekDay) return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Không tìm thấy dữ liệu!'
        ], 401);

        $slug = SlugService::createSlug(ClassRoom::class, 'slug', $request->name);
        $uuid = substr(Str::uuid()->toString(), 0, 8);

        $data = ClassRoom::create([
            'name' => $request->name,
            'quantity_minimum' => $request->quantity_minimum,
            'quantity_maxnimum' => $request->quantity_maxnimum,
            'opening_day' => $request->opening_day,
            'status' => 0,
            'slug' => $slug . '-' . $uuid,
            'course_id' => $request->course_id,
            'room_id' => $request->room_id,
            'teacher_id' => $request->teacher_id,
            'time_frame_id' => $request->time_frame_id,
            'week_day_id' => $request->week_day_id,
        ]);

        if ($data) {
            $data['courses'] = $data->courses;
            $data['rooms'] = $data->rooms;
            $data['teachers'] = $data->teachers;
            $data['time_frames'] = $data->time_frames;
            $data['week_days'] = $data->week_days;

            $response = [
                'status' => 201,
                'success' => 'success',
                'message' => 'Thêm thành công!',
                'data' => $data
            ];
    
            return response($response, 201);
        }


        return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Thêm thất bại!'
        ], 401);
    }

    public function update(ClassRoom $slug, Request $request) {

        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'quantity_minimum' => 'required|integer',
                'quantity_maxnimum' => 'required|integer|gte:quantity_minimum',
                'opening_day' => 'required',
                'status' => 'required|integer',
                'course_id' => 'required|integer',
                'room_id' => 'required|integer',
                'teacher_id' => 'required|integer',
                'time_frame_id' => 'required|integer',
                'week_day_id' => 'required|integer',
            ],
            [
                'required' => ':attribute không được để trống',
                'integer' => ':attribute phải là một số',
                'gte' => ':attribute phải lớn hơn hoặc bằng số lượng tối thiểu',
            ],
            [
                'name' => 'Tên / Mã lớp học',
                'quantity_minimum' => 'Số lượng tối thiểu',
                'quantity_maxnimum' => 'Số lượng tối đa',
                'opening_day' => 'Ngày khai giảng',
                'status' => 'Tình trạng',
                'course_id' => 'Khoá học',
                'room_id' => 'Phòng',
                'teacher_id' => 'Giảng viên',
                'time_frame_id' => 'Mốc thời gian',
                'week_day_id' => 'Thứ',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }

        $checkCourse = Course::find($request->course_id);
        $checkRoom = Room::find($request->room_id);
        $checkTeacher = Teacher::find($request->teacher_id);
        $checkTimeFrame = TimeFrame::find($request->time_frame_id);
        $checkWeekDay = WeekDay::find($request->week_day_id);

        if (!$checkCourse || !$checkRoom || !$checkTeacher || !$checkTimeFrame || !$checkWeekDay) return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Không tìm thấy dữ liệu!'
        ], 401);

        $updated = $slug->update([
            'name' => $request->name,
            'quantity_minimum' => $request->quantity_minimum,
            'quantity_maxnimum' => $request->quantity_maxnimum,
            'opening_day' => $request->opening_day,
            'status' => $request->status,
            'course_id' => $request->course_id,
            'room_id' => $request->room_id,
            'teacher_id' => $request->teacher_id,
            'time_frame_id' => $request->time_frame_id,
            'week_day_id' => $request->week_day_id,
        ]);

        if ($updated) {
            $slug['courses'] = $slug->courses;
            $slug['rooms'] = $slug->rooms;
            $slug['teachers'] = $slug->teachers;
            $slug['time_frames'] = $slug->time_frames;
            $slug['week_days'] = $slug->week_days;
            $response = [
                'status' => 201,
                'success' => 'success',
                'message' => 'Cập nhật thành công!',
                'data' => $slug
            ];
    
            return response($response, 201);
        }


        return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Cập nhật thất bại!'
        ], 401);
    }

    public function destroy(ClassRoom $slug) {
        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $deleted = $slug->destroy($slug->id);

        if ($deleted) {
            $response = [
                'status' => 201,
                'success' => 'success',
                'message' => 'Xoá thành công!'
            ];
    
            return response($response, 201);
        }

        return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Xoá thất bại!'
        ], 401);
    }
}
