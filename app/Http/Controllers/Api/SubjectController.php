<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Program;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use \Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Support\Str;

class SubjectController extends Controller
{
    public function show() {
        return Subject::all();
    }

    public function store(Request $request) {

        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $validator = Validator::make(
            $request->all(),
            [
                'course_id' => 'required',
                'name' => 'required|string',
                'price' => 'decimal',
            ],
            [
                'required' => ':attribute không được để trống',
                'string' => ':attribute phải là một chuỗi',
                'decimal' => ':attribute phải là một số',
            ],
            [
                'course_id' => 'Chương trình',
                'name' => 'Tên môn học',
                'price' => 'Giá',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }

        $checkCourse = Course::find($request->course_id);

        if (!$checkCourse) return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Không tìm thấy khoá học!'
        ], 401);

        $slug = SlugService::createSlug(Subject::class, 'slug', $request->name);
        $uuid = substr(Str::uuid()->toString(), 0, 8);

        $data = Subject::create([
            'course_id' => $request->course_id,
            'name' => $request->name,
            'description' => $request->description,
            'image' => $request->image,
            'price' => $request->price,
            'slug' => $slug . '-' . $uuid,
        ]);

        if ($data) {
            $data['courses'] = $data->courses; 

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

    public function update(Subject $slug, Request $request) {

        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $validator = Validator::make(
            $request->all(),
            [
                'course_id' => 'required',
                'name' => 'required|string',
                'price' => 'decimal',
            ],
            [
                'required' => ':attribute không được để trống',
                'string' => ':attribute phải là một chuỗi',
                'decimal' => ':attribute phải là một số',
            ],
            [
                'course_id' => 'Chương trình',
                'name' => 'Tên môn học',
                'price' => 'Giá',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }

        $checkCourse = Course::find($request->course_id);

        if (!$checkCourse) return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Không tìm thấy khoá học!'
        ], 401);

        $updated = $slug->update([
            'course_id' => $request->course_id,
            'name' => $request->name,
            'description' => $request->description,
            'image' => $request->image,
            'price' => $request->price,
        ]); 

        if ($updated) {
            $slug['courses'] = $slug->courses; 

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

    public function destroy(Subject $slug) {
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
