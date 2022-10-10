<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\MailSend;
use App\Models\PasswordReset;
use App\Models\User;
use App\Notifications\ResetPasswordRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function sendMail(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
            ],
            [
                'required' => ':attribute không được để trống',
            ],
            [
                'email' => 'Email',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Email không tồn tại trên hệ thống!'
        ], 401);

        $passwordReset = PasswordReset::updateOrCreate([
            'email' => $user->email,
        ], [
            'token' => Str::random(60),
        ]);

        if ($passwordReset) {
            $user->notify(new ResetPasswordRequest($passwordReset->token));
        }

        return response([
            'status' => 201,
            'success' => 'success',
            'message' => 'Chúng tôi đã gửi qua e-mail liên kết đặt lại mật khẩu của bạn!'
        ], 201);
    }

    public function reset(Request $request, $token)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'password' => 'required|string|confirmed',
            ],
            [
                'required' => ':attribute không được để trống',
                'string' => ':attribute phải là một chuỗi',
                'confirmed' => ':attribute xác nhận mật khẩu chưa chính xác',
            ],
            [
                'password' => 'Mật khẩu',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }

        $passwordReset = PasswordReset::where('token', $token)->first();

        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();

            return response()->json([
                'message' => 'Mã thông báo đặt lại mật khẩu này không hợp lệ.',
            ], 422);
        }

        $user = User::where('email', $passwordReset->email)->firstOrFail();

        $updatePasswordUser = $user->update(bcrypt($request->password));
        $passwordReset->delete();

        if ($updatePasswordUser) {
            $response = [
                'status' => 201,
                'success' => 'success',
                'message' => 'Đặt lại mật khẩu thành công!'
            ];
            return response($response, 201);
        }

        $response = [
            'status' => 403,
            'success' => 'success',
            'message' => 'Đặt lại mật khẩu thất bại!'
        ];
        return response($response, 403);
    }
}
