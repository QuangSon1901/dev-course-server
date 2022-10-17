<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Program;
use App\Models\SearchKeyword;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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

        $keyword = SearchKeyword::select('id', 'keyword')->where('keyword', 'LIKE', $request->q . '%')->take(8)->get();
        $courses = Course::select('id','name', 'slug')->where('name', 'LIKE', $request->q . '%')->take(3)->get();
        
        $result = [...$keyword, ...$courses];
        
        $response = [
            'status' => 200,
            'success' => 'success',
            'suggests' => $result,
        ];

        return response($response, 200);
    }
}
