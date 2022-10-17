<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Program;
use App\Models\TopicCourse;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
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
                'sub_name' => 'string',
                'video_demo' => 'url',
                'description' => 'string',
                'image' => 'image|mimes:jpg,png|max:2048',
                'price' => 'numeric',
                'topic_course_id' => 'required',
            ],
            [
                'required' => ':attribute không được để trống',
                'string' => ':attribute phải là dạng chuỗi',
                'numeric' => ':attribute phải là dạng numeric',
                'url' => ':attribute phải là dạng url video (Youtube)',
                'image' => ':attribute phải là dạng file hình ảnh',
                'mimes' => ':attribute chỉ chấp nhận đuôi "jpg" or "png"',
                'max' => ':attribute phải dưới 2048kb',
                'array' => ':attribute là một danh sách',
                'min' => ':attribute ít nhất 1',
                'distinct' => ':attribute không được trùng lặp'
            ],
            [
                'name' => 'Tên chương trình',
                'sub_name' => 'Tên phụ',
                'description' => 'Mô tả chương trình',
                'image' => 'Hình ảnh',
                'price' => 'Giá',
                'topic_course_id' => 'Chủ đề khoá học',
                'video_demo' => 'Video demo'
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }

        $checkTopic = TopicCourse::find($request->topic_course_id);

        if (!$checkTopic) return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Không tìm thấy chủ đề khoá học!'
        ], 401);

        $slug = SlugService::createSlug(Course::class, 'slug', $request->name);
        $uuid = substr(Str::uuid()->toString(), 0, 8);

        if ($request->has('image')) {
            $filename = time() . rand(1, 10) . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move('uploads/', $filename);

            $data = [
                'name' => $request->name,
                'subname' => $request->subname,
                'video_demo' => $request->video_demo,
                'description' => $request->description,
                'image' => $filename,
                'price' => $request->price,
                'topic_course_id' => $request->topic_course_id,
                'slug' => $slug . '-' . $uuid,
            ];
        } else {
            $data = [
                'name' => $request->name,
                'subname' => $request->subname,
                'video_demo' => $request->video_demo,
                'description' => $request->description,
                'price' => $request->price,
                'topic_course_id' => $request->topic_course_id,
                'slug' => $slug . '-' . $uuid,
            ];
        }

        $newCourse = Course::create($data);

        if ($newCourse) {
            $newCourse['topic_courses'] = $newCourse->topic_courses; 

            $response = [
                'status' => 201,
                'success' => 'success',
                'message' => 'Thêm thành công!',
                'data' => $newCourse
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
                'program_id' => 'required',
                'name' => 'required|string',
                'price' => 'decimal',
            ],
            [
                'required' => ':attribute không được để trống',
                'string' => ':attribute phải là một chuỗi',
                'decimal' => ':attribute phải là một số',
            ],
            [
                'program_id' => 'Chương trình',
                'name' => 'Tên môn học',
                'price' => 'Giá',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }

        $checkProgram = Program::find($request->program_id);

        if (!$checkProgram) return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Không tìm thấy chương trình!'
        ], 401);

        $updated = $slug->update([
            'program_id' => $request->program_id,
            'name' => $request->name,
            'description' => $request->description,
            'image' => $request->image,
            'price' => $request->price,
        ]); 

        if ($updated) {
            $slug['programs'] = $slug->programs; 

            $response = [
                'status' => 200,
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
