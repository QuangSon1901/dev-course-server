<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use \Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Support\Str;

class ProgramController extends Controller
{
    public function show(Request $request) {
        if ($request->type == "less")
        {
            $program = Program::take(5)->get();
            foreach($program as $item) {
                $item['courses'] = $item->courses;
            }
            
            $response = [
                'program' => $program
            ];
    
            return response($response, 201);
        }
        return Program::all();
    }

    public function store(Request $request) {

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

        $slug = SlugService::createSlug(Program::class, 'slug', $request->name);
        $uuid = substr(Str::uuid()->toString(), 0, 8);
        
        $data = Program::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $request->image,
            'slug' => $slug . '-' . $uuid,
        ]);

        $response = [
            'status' => 201,
            'data' => $data
        ];

        return response($response, 201);
    }

    public function update(Program $slug, Request $request) {
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

    public function destroy(Program $slug) {
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
