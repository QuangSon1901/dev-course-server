<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrainingProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TrainingProgramController extends Controller
{
    public function show() {
        return TrainingProgram::all();
    }

    public function store(Request $request) {

        if (Gate::denies('role-admin')) return response(['message' => 'You do not have access!'], 401);

        $request->validate([
            'name' => 'required',
        ]);

        $data = TrainingProgram::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        $response = [
            'status' => 200,
            'data' => $data
        ];

        return response($response, 201);
    }
}
