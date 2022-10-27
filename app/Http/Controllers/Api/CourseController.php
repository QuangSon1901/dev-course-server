<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Program;
use App\Models\TopicCourse;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function show()
    {
        return Course::all();
    }
    
    public function show_by_slug_checkout(Course $slug) {
        if (!$slug) return response([
            'status' => 403,
            'success' => 'danger',
            'message' => 'Slug is not found'
        ], 403);
        
        return response([
            'status' => 200,
            'success' => 'success',
            'course' => [
                'id' => $slug->id,
                'name' => $slug->name,
                'image' => $slug->image,
                'price' => $slug->price,
                'slug' => $slug->slug
            ]
        ], 200);
    }

    public function show_by_slug(Course $slug)
    {
        if (!$slug) return response([
            'status' => 403,
            'success' => 'danger',
            'message' => 'Slug is not found'
        ], 403);

        $total_lectures = 0;
        foreach ($slug->units as $unit) {
            $total_lectures += $unit->lessons->count();
        }

        return response([
            'status' => 200,
            'success' => 'success',
            'course' => [
                'id' => $slug->id,
                'name' => $slug->name,
                'sub_name' => $slug->sub_name,
                'description' => $slug->description,
                'objectives' => json_decode($slug->objectives),
                'image' => $slug->image,
                'price' => $slug->price,
                'form_of_learning' => $slug->form_of_learning,
                'level' => $slug->level,
                'slug' => $slug->slug,
                'topic_course_id' => $slug->topic_course_id,
                'total_sections' => $slug->units->count(),
                'total_lectures' => $total_lectures,
                'class_room' => $slug->class_rooms,
            ]
        ], 200);
    }

    public function store(Request $request)
    {

        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'sub_name' => 'string',
                'video_demo' => 'url',
                'description' => 'string',
                'image' => 'image|mimes:jpg,png|max:2048',
                'price' => 'numeric',
                'topic_course_id' => 'required',
            ],
            [
                'required' => ':attribute không được để trống',
                'string' => ':attribute phải là dạng chuỗi',
                'numeric' => ':attribute phải là dạng numeric',
                'url' => ':attribute phải là dạng url video (Youtube)',
                'image' => ':attribute phải là dạng file hình ảnh',
                'mimes' => ':attribute chỉ chấp nhận đuôi "jpg" or "png"',
                'max' => ':attribute phải dưới 2048kb',
                'array' => ':attribute là một danh sách',
                'min' => ':attribute ít nhất 1',
                'distinct' => ':attribute không được trùng lặp'
            ],
            [
                'name' => 'Tên chương trình',
                'sub_name' => 'Tên phụ',
                'description' => 'Mô tả chương trình',
                'image' => 'Hình ảnh',
                'price' => 'Giá',
                'topic_course_id' => 'Chủ đề khoá học',
                'video_demo' => 'Video demo'
            ]
        );



        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }

        $checkTopic = TopicCourse::find($request->topic_course_id);

        if (!$checkTopic) return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Không tìm thấy chủ đề khoá học!'
        ], 401);

        $slug = SlugService::createSlug(Course::class, 'slug', $request->name);
        $uuid = substr(Str::uuid()->toString(), 0, 8);

        if ($request->has('image')) {
            $filename = time() . rand(1, 10) . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move('uploads/', $filename);

            $data = [
                'name' => $request->name,
                'sub_name' => $request->sub_name,
                'video_demo' => $request->video_demo,
                'description' => $request->description,
                'image' => $filename,
                'price' => $request->price,
                'topic_course_id' => $request->topic_course_id,
                'slug' => $slug . '-' . $uuid,
            ];
        } else {
            $data = [
                'name' => $request->name,
                'sub_name' => $request->sub_name,
                'video_demo' => $request->video_demo,
                'description' => $request->description,
                'price' => $request->price,
                'topic_course_id' => $request->topic_course_id,
                'slug' => $slug . '-' . $uuid,
            ];
        }

        $newCourse = Course::create($data);

        if ($newCourse) {

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

            $response = [
                'status' => 201,
                'success' => 'success',
                'message' => 'Thêm thành công!',
                'data' => $newCourse
            ];

            return response($response, 201);
        }


        return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Thêm thất bại!'
        ], 401);
    }

    public function store_auto(Request $request)
    {
        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $checkTopic = TopicCourse::find($request->topic_course_id);

        if (!$checkTopic) return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Không tìm thấy chủ đề khoá học!'
        ], 401);

        $data = json_decode($request->data_json, true);
        foreach ($data as $item) {
            $slug = SlugService::createSlug(Course::class, 'slug', $item['name']);
            $uuid = substr(Str::uuid()->toString(), 0, 8);

            $info = pathinfo($item['image']);
            $filename = time() . rand(1, 10) . $info['basename'];

            $img = public_path('uploads/') . $filename;
            file_put_contents($img, file_get_contents($item['image']));

            $data = [
                'name' => $item['name'],
                'sub_name' => $item['sub_name'],
                'image' => $filename,
                'price' => $item['price'],
                'topic_course_id' => $request->topic_course_id,
                'slug' => $slug . '-' . $uuid,
            ];

            $newCourse = Course::create($data);

            if ($newCourse) {

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
            }
        }
    }

    public function update(Course $slug, Request $request)
    {

        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $validator = Validator::make(
            $request->all(),
            [
                'program_id' => 'required',
                'name' => 'required|string',
                'price' => 'decimal',
            ],
            [
                'required' => ':attribute không được để trống',
                'string' => ':attribute phải là một chuỗi',
                'decimal' => ':attribute phải là một số',
            ],
            [
                'program_id' => 'Chương trình',
                'name' => 'Tên môn học',
                'price' => 'Giá',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }

        $checkProgram = Program::find($request->program_id);

        if (!$checkProgram) return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Không tìm thấy chương trình!'
        ], 401);

        $updated = $slug->update([
            'program_id' => $request->program_id,
            'name' => $request->name,
            'description' => $request->description,
            'image' => $request->image,
            'price' => $request->price,
        ]);

        if ($updated) {
            $slug['programs'] = $slug->programs;

            $response = [
                'status' => 200,
                'success' => 'success',
                'message' => 'Cập nhật thành công!',
                'data' => $slug
            ];

            return response($response, 201);
        }


        return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Cập nhật thất bại!'
        ], 401);
    }

    public function destroy(Course $slug)
    {
        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $deleted = $slug->destroy($slug->id);

        if ($deleted) {
            $response = [
                'status' => 201,
                'success' => 'success',
                'message' => 'Xoá thành công!'
            ];

            return response($response, 201);
        }

        return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Xoá thất bại!'
        ], 401);
    }
}
