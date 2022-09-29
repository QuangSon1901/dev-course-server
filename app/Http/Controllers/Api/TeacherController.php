<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class TeacherController extends Controller
{
    public function show() {
        return Teacher::all();
    }

    public function store(Request $request) {

        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'email' => 'required|email',
            ],
            [
                'required' => ':attribute không được để trống',
                'string' => ':attribute phải là một chuỗi',
                'email' => ':attribute sai định dạng',
            ],
            [
                'name' => 'Tên giảng viên',
                'email' => 'Email',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }

        $data = Teacher::create([
            'name' => $request->name,
            'email' => $request->email,
            'image' => $request->image,
        ]);

        if ($data) {
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

    public function update($idTeacher, Request $request) {

        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'email' => 'required|email',
            ],
            [
                'required' => ':attribute không được để trống',
                'string' => ':attribute phải là một chuỗi',
                'email' => ':attribute sai định dạng',
            ],
            [
                'name' => 'Tên giảng viên',
                'email' => 'Email',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }

        $checkTeacher = Teacher::find($idTeacher);

        if (!$checkTeacher) return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Không tìm thấy giảng viên!'
        ], 401);

        $updated = $checkTeacher->update([
            'name' => $request->name,
            'email' => $request->email,
            'image' => $request->image,
        ]);

        if ($updated) {
            $response = [
                'status' => 201,
                'success' => 'success',
                'message' => 'Cập nhật thành công!',
                'data' => $checkTeacher
            ];
    
            return response($response, 201);
        }


        return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Cập nhật thất bại!'
        ], 401);
    }

    public function destroy($idTeacher) {
        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $teacher = Teacher::find($idTeacher);

        if (!$teacher) return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Không tìm thấy giảng viên!'
        ], 401);

        $deleted = $teacher->destroy($idTeacher);

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
