<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    public function index()
    {
        $items = Permission::query()
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'List Data Permissions',
            'data' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:permissions',
            'model_route' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $item = Permission::create([
            'name' => $request->name,
            'model_route' => $request->model_route,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permission berhasil ditambahkan.',
            'data' => $item,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:permissions,name,' . $id,
            'model_route' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $item = Permission::find($id);

        if (!$item) {
            return response()->json([
                'message' => 'Data permission tidak ditemukan.',
            ], 404);
        }

        $item->update([
            'name' => $request->name,
            'model_route' => $request->model_route,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permission berhasil diubah.',
            'data' => $item,
        ]);
    }

    public function destroy($id)
    {
        $item = Permission::find($id);

        if (!$item) {
            return response()->json([
                'message' => 'Data permission tidak ditemukan.',
            ], 404);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permission berhasil dihapus.',
        ]);
    }
}
