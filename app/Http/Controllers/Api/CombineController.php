<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;

class CombineController extends Controller
{
    public function show() {
        $programs = Program::all();
        foreach ($programs as $item) {
            $item['category_courses'] = $item->category_courses;
        }

        $response = [
            'status' => 201,
            'success' => 'success',
            'menu_programs' => $programs
        ];

        return response($response, 201);
    }
}
