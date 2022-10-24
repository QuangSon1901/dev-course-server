<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        foreach ($file_json as $course) {
            $slug = SlugService::createSlug(Course::class, 'slug', $course['name']);
            $uuid = substr(Str::uuid()->toString(), 0, 8);

            $data = [
                'name' => $course['name'],
                'sub_name' => $course['sub_name'],
                'video_demo' => $course['video_url'],
                'description' => $course['description'],
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

            $newCourse = Course::create($data);
            if ($newCourse) {

                // Add Teacher
                $teacher = Teacher::updateOrCreate([
                    'name' => $course['teacher'][0]['title']
                ], ['name' => $course['teacher'][0]['title']]);

                // Add Class
                ClassRoom::updateOrCreate([
                    'course_id' => $newCourse->id
                ], [
                    'name' => substr(Str::uuid()->toString(), 0, 8),
                    'status' => 0,
                    'course_id' => $newCourse->id,
                    'teacher_id' => $teacher->id
                ]);


                // Add Search
                $search_keywords = array();
                array_push($search_keywords, ...$checkTopic->search_keywords);


                foreach ($checkTopic->category_courses as $category_course) {
                    array_push($search_keywords, ...$category_course->search_keywords);
                    $programs[$category_course->program_id] = $category_course->programs;
                }

                foreach ($programs as $program) {
                    array_push($search_keywords, ...$program->search_keywords);
                }

                foreach ($search_keywords as $key) {
                    $result[$key->id] = $key->id;
                }

                foreach ($result as $key => $value) {
                    $newCourse->search_keywords()->attach($value);
                }

                $newCourse['topic_courses'] = $newCourse->topic_courses;

                // Add Units
                foreach($course['units'] as $unit) {
                    $newUnit = Unit::create([
                        'name' => $unit['title'],
                        'z_index' => $unit['index'],
                        'slug' => SlugService::createSlug(Unit::class, 'slug', $unit['title']),
                        'course_id' => $newCourse->id,
                    ]);

                    if ($newUnit) {
                        foreach($unit['items'] as $lesson) {
                            Lesson::create([
                                'name' => $lesson['title'],
                                'description' => $lesson['description'],
                                'z_index' => $lesson['object_index'],
                                'slug' => SlugService::createSlug(Lesson::class, 'slug', $lesson['title']),
                                'unit_id' => $newUnit->id
                            ]);
                        }
                    }
                }

                // echo $newCourse;
            }
        }

        return response([
            'status' => 200,
            'success' => 'success',
            'message' => 'Successfully!'
        ], 200);
    }
}
