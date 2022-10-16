<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CategoryCourse;
use App\Models\Program;
use App\Models\SearchKeyword;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryCourseController extends Controller
{
    public function store(Request $request)
    {

        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'description' => 'string',
                'image' => 'image|mimes:jpg,png|max:2048',
                'program_id' => 'required',
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
                'program_id' => 'Chương trình đào tạo',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()], 403);
        }

        $checkProgram = Program::find($request->program_id);

        if (!$checkProgram) return response([
            'status' => 403,
            'success' => 'danger',
            'message' => 'Không tìm thấy dữ liệu!'
        ], 403);

        $slug = SlugService::createSlug(CategoryCourse::class, 'slug', $request->name);
        $uuid = substr(Str::uuid()->toString(), 0, 8);

        if ($request->has('image')) {
            $filename = time() . rand(1, 10) . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move('uploads/', $filename);

            $data = [
                'name' => $request->name,
                'description' => $request->description,
                'image' => $filename,
                'slug' => $slug . '-' . $uuid,
                'program_id' => $request->program_id
            ];
        } else {
            $data = [
                'name' => $request->name,
                'description' => $request->description,
                'slug' => $slug . '-' . $uuid,
                'program_id' => $request->program_id
            ];
        }

        $newCategoryCourse = CategoryCourse::create($data);

        if ($newCategoryCourse) {
            foreach ($request->keywords as $value) {
                SearchKeyword::create([
                    'keyword' => $value,
                    'category_course_id' => $newCategoryCourse->id
                ]);
            }

            $newCategoryCourse['search_keywords'] = $newCategoryCourse->search_keywords;

            $response = [
                'status' => 201,
                'success' => 'success',
                'message' => 'Thêm thành công!',
                'data' => $newCategoryCourse,
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
