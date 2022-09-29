<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TimeFrame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class TimeFrameController extends Controller
{
    public function show() {
        return TimeFrame::all();
    }

    public function store(Request $request) {

        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $validator = Validator::make(
            $request->all(),
            [
                'start_time' => 'required|date_format:H:i:s',
                'end_time' => 'required|date_format:H:i:s|after_or_equal:start_time',
            ],
            [
                'required' => ':attribute không được để trống',
                'date_format' => ':attribute phải có định dạng giờ - phút - giây',
                'after_or_equal' => ':attribute phải sau thời gian bắt đầu',
            ],
            [
                'start_time' => 'Thời gian bắt đầu',
                'end_time' => 'Thời gian kết thúc',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }

        $data = TimeFrame::create([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
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

    public function update($idTimeFrame, Request $request) {

        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $validator = Validator::make(
            $request->all(),
            [
                'start_time' => 'required|date_format:H:i:s',
                'end_time' => 'required|date_format:H:i:s|after_or_equal:start_time',
            ],
            [
                'required' => ':attribute không được để trống',
                'date_format' => ':attribute phải có định dạng giờ - phút - giây',
                'after_or_equal' => ':attribute phải sau thời gian bắt đầu',
            ],
            [
                'start_time' => 'Thời gian bắt đầu',
                'end_time' => 'Thời gian kết thúc',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }

        $checkTimeFrame = TimeFrame::find($idTimeFrame);

        if (!$checkTimeFrame) return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Không tìm thấy mốc thời gian!'
        ], 401);

        $updated = $checkTimeFrame->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        if ($updated) {
            $response = [
                'status' => 201,
                'success' => 'success',
                'message' => 'Cập nhật thành công!',
                'data' => $checkTimeFrame
            ];
    
            return response($response, 201);
        }


        return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Cập nhật thất bại!'
        ], 401);
    }

    public function destroy($idTimeFrame) {
        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $timeFrame = TimeFrame::find($idTimeFrame);

        if (!$timeFrame) return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Không tìm thấy mốc thời gian!'
        ], 401);

        $deleted = $timeFrame->destroy($idTimeFrame);

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
