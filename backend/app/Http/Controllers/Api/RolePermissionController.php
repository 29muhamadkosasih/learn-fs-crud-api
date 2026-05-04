<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\RolePermission;
use App\Support\PermissionCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RolePermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::query()
            ->orderBy('name')
            ->get();

        $items = RolePermission::query()
            ->orderBy('role')
            ->orderBy('permission')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'List Data Role Permissions',
            'data' => $items,
            'meta' => [
                'roles' => PermissionCatalog::roles(),
                'permissions' => $permissions,
            ],
        ]);
    }

    protected function permissionValues(): array
    {
        return Permission::query()->pluck('name')->filter()->values()->all();
    }

    public function store(Request $request)
    {
        $permissionValues = $this->permissionValues();

        $validator = Validator::make($request->all(), [
            'role' => 'required|in:' . implode(',', PermissionCatalog::roles()),
            'permission' => 'required|in:' . implode(',', $permissionValues),
            'allowed' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $exists = RolePermission::query()
            ->where('role', $request->role)
            ->where('permission', $request->permission)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Permission untuk role ini sudah ada. Gunakan update.',
            ], 409);
        }

        $item = RolePermission::create([
            'role' => $request->role,
            'permission' => $request->permission,
            'allowed' => $request->boolean('allowed'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permission berhasil ditambahkan.',
            'data' => $item,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $permissionValues = $this->permissionValues();

        $validator = Validator::make($request->all(), [
            'role' => 'required|in:' . implode(',', PermissionCatalog::roles()),
            'permission' => 'required|in:' . implode(',', $permissionValues),
            'allowed' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $item = RolePermission::find($id);

        if (!$item) {
            return response()->json([
                'message' => 'Data role permission tidak ditemukan.',
            ], 404);
        }

        $duplicate = RolePermission::query()
            ->where('role', $request->role)
            ->where('permission', $request->permission)
            ->where('id', '!=', $id)
            ->exists();

        if ($duplicate) {
            return response()->json([
                'message' => 'Kombinasi role dan permission sudah digunakan.',
            ], 409);
        }

        $item->update([
            'role' => $request->role,
            'permission' => $request->permission,
            'allowed' => $request->boolean('allowed'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permission berhasil diubah.',
            'data' => $item,
        ]);
    }

    public function destroy($id)
    {
        $item = RolePermission::find($id);

        if (!$item) {
            return response()->json([
                'message' => 'Data role permission tidak ditemukan.',
            ], 404);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permission berhasil dihapus.',
        ]);
    }
}
