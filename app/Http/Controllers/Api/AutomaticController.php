<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\AutoCourse;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Teacher;
use App\Models\TopicCourse;
use App\Models\Unit;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AutomaticController extends Controller
{
    public function courses_store(Request $request)
    {
        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $validator = Validator::make(
            $request->all(),
            [
                'file_json' => 'required|file',
                'topic_course_id' => 'required|integer',
            ],
            [],
            [
                'file_json' => 'File JSON',
                'topic_course_id' => 'Topic',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()], 403);
        }

        $checkTopic = TopicCourse::find($request->topic_course_id);

        if (!$checkTopic) return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Topic is not found!'
        ], 401);

        $file_json = json_decode(file_get_contents($request->file_json), true);
        $startTime = microtime(true);

        foreach ($file_json as $course) {
            $slug = SlugService::createSlug(Course::class, 'slug', $course['name']);
            $uuid = substr(Str::uuid()->toString(), 0, 8);

            $data = [
                'name' => $course['name'],
                'sub_name' => $course['sub_name']  ?: '',
                'video_demo' => $course['video_url'] ?: '',
                'description' => $course['description'] ?: '',
                'price' => $course['price'],
                'form_of_learning' => 'Online',
                'level' => $course['level'],
                'topic_course_id' => $request->topic_course_id,
                'slug' => $slug . '-' . $uuid,
            ];

            if ($course['image']) {
                $info = pathinfo($course['image']);
                $filename = time() . rand(1, 10) . $info['basename'];

                $img = public_path('uploads/') . $filename;
                file_put_contents($img, @file_get_contents($course['image']));

                $data['image'] = $filename;
            }

            $sendCourseJob = new AutoCourse($course, $data, $checkTopic);
            dispatch($sendCourseJob);
        }

        $endTime = microtime(true);
        $timeExecute = $endTime - $startTime;


        return response([
            'status' => 200,
            'success' => 'success',
            'message' => 'Successfully!'
        ], 200);
    }
}
