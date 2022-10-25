<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Teacher;
use App\Models\Unit;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Support\Str;

class AutoCourse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $course;
    protected $topic;

    public function __construct($course, $data, $topic)
    {
        $this->course = $course;
        $this->data = $data;
        $this->topic = $topic;
    }

    public function handle()
    {
        $newCourse = Course::create($this->data);
        if ($newCourse) {

            // Add Teacher
            $teacher = Teacher::updateOrCreate([
                'name' => $this->course['teacher'][0]['title']
            ], ['name' => $this->course['teacher'][0]['title']]);

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
            array_push($search_keywords, ...$this->topic->search_keywords);


            foreach ($this->topic->category_courses as $category_course) {
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
            foreach ($this->course['units'] as $unit) {
                $newUnit = Unit::create([
                    'name' => $unit['title'],
                    'z_index' => $unit['index'],
                    'slug' => SlugService::createSlug(Unit::class, 'slug', $unit['title']),
                    'course_id' => $newCourse->id,
                ]);

                if ($newUnit) {
                    foreach ($unit['items'] as $lesson) {
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
        }
    }
}
