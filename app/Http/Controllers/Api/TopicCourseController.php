<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SearchKeyword;
use App\Models\TopicCourse;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TopicCourseController extends Controller
{
    public function showSlug(TopicCourse $slug, Request $request) {
        $slug['search_keywords'] = $slug->search_keywords;
        return response([
            'topic_course' => $slug,
        ], 201);
    }

    public function store(Request $request)
    {

        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'description' => 'string',
                'image' => 'image|mimes:jpg,png|max:2048',
                'keywords' => 'required|array|min:1',
                "keywords.*"  => "required|string|distinct",
            ],
            [
                'required' => ':attribute không được để trống',
                'string' => ':attribute phải là dạng chuỗi',
                'image' => ':attribute phải là dạng file hình ảnh',
                'mimes' => ':attribute chỉ chấp nhận đuôi "jpg" or "png"',
                'max' => ':attribute phải dưới 2048kb',
                'array' => ':attribute là một danh sách',
                'min' => ':attribute ít nhất 1',
                'distinct' => ':attribute không được trùng lặp'
            ],
            [
                'name' => 'Tên chương trình',
                'description' => 'Mô tả chương trình',
                'image' => 'Hình ảnh',
                'keywords' => 'Từ khoá',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()], 403);
        }

        $slug = SlugService::createSlug(TopicCourse::class, 'slug', $request->name);
        $uuid = substr(Str::uuid()->toString(), 0, 8);

        if ($request->has('image')) {
            $filename = time() . rand(1, 10) . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move('uploads/', $filename);

            $data = [
                'name' => $request->name,
                'description' => $request->description,
                'image' => $filename,
                'slug' => $slug . '-' . $uuid,
            ];
        } else {
            $data = [
                'name' => $request->name,
                'description' => $request->description,
                'slug' => $slug . '-' . $uuid,
            ];
        }

        $newTopicCourse = TopicCourse::create($data);

        if ($newTopicCourse) {
            foreach ($request->keywords as $value) {
                SearchKeyword::create([
                    'keyword' => $value,
                    'topic_course_id' => $newTopicCourse->id
                ]);
            }

            $newTopicCourse['search_keywords'] = $newTopicCourse->search_keywords;

            $response = [
                'status' => 201,
                'success' => 'success',
                'message' => 'Thêm thành công!',
                'data' => $newTopicCourse,
            ];

            return response($response, 201);
        }

        $response = [
            'status' => 403,
            'success' => 'danger',
            'message' => 'Thêm thất bại!',
        ];

        return response($response, 403);
    }
}
