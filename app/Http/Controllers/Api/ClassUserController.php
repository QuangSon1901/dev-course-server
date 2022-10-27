<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassesUsers;
use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClassUserController extends Controller
{
    public function show() {
        $all = ClassRoom::find(3);
        $all['users'] = $all->users;
        $response = [
            'status' => 201,
            'success' => 'success',
            'message' => 'Thêm thành công!',
            'data' => $all
        ];

        return response($response, 201);
    }

    
}
