<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class UserController extends Controller
{
    public function getUser(Request $request) {
        $user =  auth('sanctum')->user();

        $response = [
            'status' => 201,
            'success' => 'success',
            'user' => $user,
        ];

        return response($response, 201);
    }
}
