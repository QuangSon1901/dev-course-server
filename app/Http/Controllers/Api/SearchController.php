<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
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

        $courses = Course::where('name', 'LIKE', '%' . $request->q . '%')->take(3)->get();
        $subjects = Subject::where('name', 'LIKE', '%' . $request->q . '%')->take(3)->get();
        $teachers = Teacher::where('name', 'LIKE', '%' . $request->q . '%')->take(3)->get();

        $response = [
            'status' => 200,
            'success' => 'success',
            'courses' => $courses,
            'subjects' => $subjects,
            'teachers' => $teachers
        ];

        $response['courses_total'] = count($courses);
        $response['subjects_total'] = count($subjects);
        $response['teachers_total'] = count($teachers);

        return response($response, 200);
    }
}
