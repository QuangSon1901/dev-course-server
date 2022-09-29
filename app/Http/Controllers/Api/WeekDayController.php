<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WeekDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class WeekDayController extends Controller
{
    public function show() {
        return WeekDay::all();
    }

    public function store(Request $request) {

        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $validator = Validator::make(
            $request->all(),
            [
                'week_day' => 'required|string',
            ],
            [
                'required' => ':attribute không được để trống',
                'string' => ':attribute phải là một chuỗi',
            ],
            [
                'week_day' => 'Thứ',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }

        $data = WeekDay::create([
            'week_day' => $request->week_day,
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

    public function update($idWeekDay, Request $request) {

        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $validator = Validator::make(
            $request->all(),
            [
                'week_day' => 'required|string',
            ],
            [
                'required' => ':attribute không được để trống',
                'string' => ':attribute phải là một chuỗi',
            ],
            [
                'week_day' => 'Thứ',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }

        $checkWeekDay = WeekDay::find($idWeekDay);

        if (!$checkWeekDay) return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Không tìm thấy thứ!'
        ], 401);

        $updated = $checkWeekDay->update([
            'week_day' => $request->week_day,
        ]);

        if ($updated) {
            $response = [
                'status' => 201,
                'success' => 'success',
                'message' => 'Cập nhật thành công!',
                'data' => $checkWeekDay
            ];
    
            return response($response, 201);
        }


        return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Cập nhật thất bại!'
        ], 401);
    }

    public function destroy($idWeekDay) {
        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $weekDay = WeekDay::find($idWeekDay);

        if (!$weekDay) return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Không tìm thấy thứ!'
        ], 401);

        $deleted = $weekDay->destroy($idWeekDay);

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
