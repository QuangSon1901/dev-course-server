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

        $keyword = SearchKeyword::select('id', 'keyword')->where('keyword', 'LIKE', '%' . $request->q . '%')->take(8)->get();
        $courses = Course::select('id', 'name', 'slug')->where('name', 'LIKE', $request->q . '%')->take(3)->get();

        $result = [...$keyword, ...$courses];

        $response = [
            'status' => 200,
            'success' => 'success',
            'suggests' => $result,
        ];

        return response($response, 200);
    }

    public function searchKeyword(Request $request)
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
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()], 403);
        }

        $keywords = SearchKeyword::select('id')->where('keyword', 'LIKE', $request->q . '%')->get();

        if (count($keywords) > 0) {

            foreach ($keywords as $keyword) {
                $key[] = $keyword->id;
            }

            $courses = Course::query();

            if ($request->level) {
                $level = $request->level;
                $courses = $courses->where(function ($query) use ($level) {
                    foreach ($level as $key => $value) {
                        $query->orWhere('level', 'LIKE', '%' . $value . '%');
                    }
                });
            }

            $courses = $courses->where(function ($query1) use ($key, $request) {
                $query1->whereHas('search_keywords', function ($query) use ($key) {
                    $query->whereIn('id', $key);
                })->orWhere('name', 'like', '%' . $request->q . '%');
            });

            if ($request->sort === 'lowest-price') {
                $courses = $courses->orderBy('price');
            } else if ($request->sort === 'highest-price') {
                $courses = $courses->orderByDesc('price');
            } else if ($request->sort === 'lastest') {
                $courses = $courses->orderByDesc('created_at');
            }

            $courses = $courses->paginate(20)->onEachSide(1);

            $response = [
                'status' => 200,
                'success' => 'success',
                'result' => $courses,
            ];

            return response($response, 200);
        }

        $response = [
            'status' => 403,
            'success' => 'danger',
        ];

        return response($response, 403);
    }
}
