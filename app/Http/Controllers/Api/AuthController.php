<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'role_id' => 'required|integer',
                'email' => 'required|string|unique:users,email|email',
                'password' => 'required|string|confirmed',
            ],
            [
                'required' => ':attribute không được để trống',
                'string' => ':attribute phải là một chuỗi',
                'integer' => ':attribute phải là số',
                'unique' => ':attribute đã tồn tại',
                'email' => ':attribute sai định dạng',
                'confirmed' => ':attribute xác nhận mật khẩu chưa chính xác',
            ],
            [
                'name' => 'Họ tên',
                'role_id' => 'Quyền hạn',
                'email' => 'Email',
                'password' => 'Mật khẩu',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }

        $checkRole = Role::find($request->role_id);

        if (!$checkRole) return response(['status' => 401, 'success' => 'danger', 'message' => 'Không tìm thấy thông tin!'], 401);

        $user = User::create([
            'role_id' => $request->role_id,
            'name' => $request->name,
            'sex' => $request->sex,
            'birth' => $request->birth,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $token = $user->createToken('usertoken')->plainTextToken;

        $response = [
            'status' => 200,
            'success' => 'success',
            'message' => 'Đăng ký tài khoản thành công!',
            'user' => $user,
            'token' => $token
        ];


        return response($response, 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ],
            [
                'required' => ':attribute không được để trống',
                'string' => ':attribute phải là một chuỗi',
                'email' => ':attribute sai định dạng',
            ],
            [
                'email' => 'Email',
                'password' => 'Mật khẩu',
            ]
        );
        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }
        $checkUser = User::where('email', $request->email)->first();
        if (!$checkUser || !Hash::check($request->password, $checkUser->password)) return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Email hoặc Mật khẩu không chính xác!'
        ], 401);
        $token = $checkUser->createToken('usertoken')->plainTextToken;
        $response = [
            'status' => 201,
            'success' => 'success',
            'message' => 'Đăng nhập thành công!',
            'user' => $checkUser,
            'token' => $token
        ];
        return response($response, 201);
    }

    public function adminLogin(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ],
            [
                'required' => ':attribute không được để trống',
                'string' => ':attribute phải là một chuỗi',
                'email' => ':attribute sai định dạng',
            ],
            [
                'email' => 'Email',
                'password' => 'Mật khẩu',
            ]
        );
        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }
        $checkUser = User::where('email', $request->email)->first();
        if (!$checkUser || $checkUser->roles->role != 'Admin' || !Hash::check($request->password, $checkUser->password)) return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Email hoặc Mật khẩu không chính xác!'
        ], 401);
        $token = $checkUser->createToken('usertoken')->plainTextToken;
        $response = [
            'status' => 201,
            'success' => 'success',
            'message' => 'Đăng nhập thành công!',
            'user' => $checkUser,
            'token' => $token
        ];
        return response($response, 201);
    }

    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();

        $response = [
            'status' => 201,
            'success' => 'success',
            'message' => 'Đăng xuất thành công!'
        ];


        return response($response, 201);
    }
}
