<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Program;
use App\Models\TopicCourse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CombineController extends Controller
{
    public function show()
    {
        $programs = Program::select('id', 'name', 'slug')->get();
        foreach ($programs as $item) {
            $item['category_courses'] = $item->category_courses;
        }

        $new_courses = Course::select('id', 'name', 'image', 'price', 'slug')->where('created_at', '>=', Carbon::now()->subMonth(1))->take(10)->get();
        $popular_reactjs = Course::select('id', 'name', 'image', 'price', 'slug')->where('topic_course_id', 2)->take(10)->get();

        $response = [
            'status' => 201,
            'success' => 'success',
            'menu_programs' => $programs,
            'data' => [
                [
                    'title' => 'Newly launched programming course',
                    'sub_title' => 'Programming courses just released by DevIT',
                    'data' => $new_courses
                ],
                [
                    'title' => 'Featured courses in React JS',
                    'sub_title' => 'Featured courses in React JS',
                    'data' => $popular_reactjs
                ]
            ]
        ];

        return response($response, 201);
    }
}
