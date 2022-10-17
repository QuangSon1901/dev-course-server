<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\SearchKeyword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use \Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Support\Str;

class ProgramController extends Controller
{
    public function show(Request $request)
    {
        if ($request->type == "less") {
            $program = Program::take(5)->get();
            foreach ($program as $item) {
                $item['courses'] = $item->courses;
            }

            $response = [
                'program' => $program
            ];

            return response($response, 201);
        }
        return Program::all();
    }

    public function showSlug(Program $slug, Request $request) {
        $slug['search_keywords'] = $slug->search_keywords;
        return response([
            'program' => $slug,
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

        $slug = SlugService::createSlug(Program::class, 'slug', $request->name);
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

        $newProgram = Program::create($data);

        if ($newProgram) {
            foreach($request->keywords as $value) {
                SearchKeyword::create([
                    'keyword' => $value,
                    'program_id' => $newProgram->id
                ]);
            }

            $newProgram['search_keywords'] = $newProgram->search_keywords;

            $response = [
                'status' => 201,
                'success' => 'success',
                'message' => 'Thêm thành công!',
                'data' => $newProgram,
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

    public function update(Program $slug, Request $request)
    {
        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
            ],
            [
                'required' => ':attribute không được để trống',
            ],
            [
                'name' => 'Tên chương trình',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }

        $updated = $slug->update([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $request->image,
        ]);

        if ($updated) {
            $response = [
                'status' => 201,
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

    public function destroy(Program $slug)
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
