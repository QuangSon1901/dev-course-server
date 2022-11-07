<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\UserEmail;
use App\Models\PasswordReset;
use App\Models\User;
use App\Notifications\ResetPasswordRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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
                'email' => ':attribute sai định dạng',
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
            'message' => 'Email không tồn tại trên hệ thống!',
        ], 401);

        $token = Str::random(60);

        $passwordReset = PasswordReset::updateOrCreate([
            'email' => $user->email,
        ], [
            'token' => bcrypt($token),
        ]);

        if ($passwordReset) {
            return response([
                'status' => 201,
                'success' => 'success',
                'message' => 'Chúng tôi đã gửi qua e-mail liên kết đặt lại mật khẩu của bạn!',
                'token' => $token
            ], 201);
        }
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

        $passwordReset = PasswordReset::where('email', $request->email)->first();

        if (!$passwordReset || !Hash::check($token, $passwordReset->token)) return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Email hoặc Mã thông báo đặt lại mật khẩu này không hợp lệ.!'
        ], 401);

        if (Carbon::parse($passwordReset['updated_at'])->addMinutes(720)->isPast()) {
            $passwordReset->delete();

            return response()->json([
                'message' => 'Mã thông báo đặt lại mật khẩu này không hợp lệ.',
            ], 422);
        }

        $user = User::where('email', $passwordReset->email)->firstOrFail();

        $updatePasswordUser = $user->update([
            'password' => bcrypt($request->password)
        ]);
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

    public function sendEmailReminder(Request $request)
    {
        $mailData = [
            'title' => 'Mail from ItSolutionStuff.com',
            'body' => 'This is for testing email using smtp.'
        ];
         
        Mail::to('quangsonbmt760@gmail.com')->send(new UserEmail($mailData));
           
        dd("Email is sent successfully.");
        
    }
}
