<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'q' => 'required|string',
            ],
            [
                'required' => ':attribute không được để trống',
                'string' => ':attribute phải là một chuỗi'
            ],
            [
                'q' => 'Từ khoá'
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }

        $subjects = Subject::where('name', 'LIKE', '%' . $request->q . '%')->take(3)->get();
        $programs = Program::where('name', 'LIKE', '%' . $request->q . '%')->take(3)->get();
        $teachers = Teacher::where('name', 'LIKE', '%' . $request->q . '%')->take(3)->get();

        $response = [
            'status' => 200,
            'success' => 'success',
            'subjects' => $subjects,
            'programs' => $programs,
            'teachers' => $teachers
        ];

        $response['subjects_total'] = count($subjects);
        $response['programs_total'] = count($programs);
        $response['teachers_total'] = count($teachers);

        return response($response, 200);
    }
}
