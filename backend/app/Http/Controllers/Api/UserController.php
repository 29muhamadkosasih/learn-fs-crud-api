<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $perPageInput = strtolower((string) $request->query('per_page', '10'));
        $allowedPerPage = ['10', '25', '50', '100', 'all'];

        if (!in_array($perPageInput, $allowedPerPage, true)) {
            $perPageInput = '10';
        }

        $perPage = $perPageInput === 'all'
            ? max(User::count(), 1)
            : (int) $perPageInput;

        $users = User::latest()->paginate($perPage);

        return new UserResource(true, 'List Data Users', $users);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,manager,user',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return new UserResource(true, 'Data User Berhasil Ditambahkan!', $user);
    }

    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Data user tidak ditemukan.',
            ], 404);
        }

        return new UserResource(true, 'Detail Data User!', $user);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:admin,manager,user',
            'password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Data user tidak ditemukan.',
            ], 404);
        }

        $payload = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $payload['password'] = Hash::make($request->password);
        }

        $user->update($payload);

        return new UserResource(true, 'Data User Berhasil Diubah!', $user);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Data user tidak ditemukan.',
            ], 404);
        }

        $user->delete();

        return new UserResource(true, 'Data User Berhasil Dihapus!', null);
    }
}
