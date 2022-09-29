<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

class CourseController extends Controller
{
    public function show() {
        return Course::all();
    }

    public function store(Request $request) {

        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);
        
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'quantity_minimum' => 'required|integer',
                'quantity_maxnimum' => 'required|integer|gte:quantity_minimum',
                'subject_id' => 'required|integer',
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
                'subject_id' => 'Môn học',
                'room_id' => 'Phòng',
                'teacher_id' => 'Giảng viên',
                'time_frame_id' => 'Mốc thời gian',
                'week_day_id' => 'Thứ',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }

        $checkSubject = Subject::find($request->subject_id);
        $checkRoom = Room::find($request->room_id);
        $checkTeacher = Teacher::find($request->teacher_id);
        $checkTimeFrame = TimeFrame::find($request->time_frame_id);
        $checkWeekDay = WeekDay::find($request->week_day_id);

        if (!$checkSubject || !$checkRoom || !$checkTeacher || !$checkTimeFrame || !$checkWeekDay) return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Không tìm thấy dữ liệu!'
        ], 401);

        $slug = SlugService::createSlug(Course::class, 'slug', $request->name);
        $uuid = substr(Str::uuid()->toString(), 0, 8);

        $data = Course::create([
            'name' => $request->name,
            'quantity_minimum' => $request->quantity_minimum,
            'quantity_maxnimum' => $request->quantity_maxnimum,
            'slug' => $slug . '-' . $uuid,
            'subject_id' => $request->subject_id,
            'room_id' => $request->room_id,
            'teacher_id' => $request->teacher_id,
            'time_frame_id' => $request->time_frame_id,
            'week_day_id' => $request->week_day_id,
        ]);

        if ($data) {
            $data['subject'] = $data->subjects;
            $data['room'] = $data->rooms;
            $data['teacher'] = $data->teachers;
            $data['time_frame'] = $data->time_frames;
            $data['week_day'] = $data->week_days;

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

    public function update(Course $slug, Request $request) {

        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $validator = Validator::make(
            $request->all(),
            [
                'quantity_minimum' => 'required|integer',
                'quantity_maxnimum' => 'required|integer|gte:quantity_minimum',
                'subject_id' => 'required|integer',
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
                'quantity_minimum' => 'Số lượng tối thiểu',
                'quantity_maxnimum' => 'Số lượng tối đa',
                'subject_id' => 'Môn học',
                'room_id' => 'Phòng',
                'teacher_id' => 'Giảng viên',
                'time_frame_id' => 'Mốc thời gian',
                'week_day_id' => 'Thứ',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }

        $checkSubject = Subject::find($request->subject_id);
        $checkRoom = Room::find($request->room_id);
        $checkTeacher = Teacher::find($request->teacher_id);
        $checkTimeFrame = TimeFrame::find($request->time_frame_id);
        $checkWeekDay = WeekDay::find($request->week_day_id);

        if (!$checkSubject || !$checkRoom || !$checkTeacher || !$checkTimeFrame || !$checkWeekDay) return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Không tìm thấy dữ liệu!'
        ], 401);

        $updated = $slug->update([
            'name' => $request->name,
            'quantity_minimum' => $request->quantity_minimum,
            'quantity_maxnimum' => $request->quantity_maxnimum,
            'subject_id' => $request->subject_id,
            'room_id' => $request->room_id,
            'teacher_id' => $request->teacher_id,
            'time_frame_id' => $request->time_frame_id,
            'week_day_id' => $request->week_day_id,
        ]);

        if ($updated) {
            $slug['subject'] = Course::find($slug->id)->subjects;
            $slug['room'] = Course::find($slug->id)->rooms;
            $slug['teacher'] = Course::find($slug->id)->teachers;
            $slug['time_frame'] = Course::find($slug->id)->time_frames;
            $slug['week_day'] = Course::find($slug->id)->week_days;
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

    public function destroy(Course $slug) {
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
