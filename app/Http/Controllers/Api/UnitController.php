<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UnitController extends Controller
{
    public function units_by_course(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'course_id' => 'required|integer',
            ],
            [
                'required' => ':attribute không được để trống',
                'integer' => ':attribute phải là một số',
            ],
            [
                'course_id' => 'Course ID',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }

        $checkCourse = Course::find($request->course_id);

        if (!$checkCourse) return response(['status' => 403, 'success' => 'danger', 'message' => 'Course is not found!'], 403);

        $units = Unit::with('lessons')->withCount('lessons')->where('course_id', $request->course_id)->get();

        return response(['status' => 200, 'success' => 'success', 'data' => [
            'total_sections' => $units->count(),
            'total_lectures' => array_sum(array_map(function ($unit) {
                return $unit['lessons_count'];
            }, $units->toArray())),
            'units' => array_map(function ($unit) {
                return [
                    'id' => $unit['id'],
                    'name' => $unit['name'],
                    'z_index' => $unit['z_index'],
                    'lessons_count' => $unit['lessons_count'],
                    'lessons' => array_map(function ($lesson) {
                        return [
                            'id' => $lesson['id'],
                            'name' => $lesson['name'],
                            'z_index' => $lesson['z_index'],
                        ];
                    }, $unit['lessons'])
                ];
            }, $units->toArray())
        ]], 200);
    }
}
