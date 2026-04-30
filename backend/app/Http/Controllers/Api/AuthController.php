<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RolePermission;
use App\Models\User;
use App\Support\PermissionCatalog;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private function permissionsForRole(?string $role): array
    {
        $role = $role ?: 'user';

        $permissions = PermissionCatalog::defaultsForRole($role);

        $overrides = RolePermission::query()
            ->where('role', $role)
            ->pluck('allowed', 'permission')
            ->all();

        $resolved = [];

        foreach ($permissions as $permission => $value) {
            $resolved[$permission] = (bool) ($overrides[$permission] ?? $value);
        }

        return $resolved;
    }

    private function userPayload(User $user): array
    {
        $payload = $user->toArray();
        $payload['permissions'] = $this->permissionsForRole($user->role);

        return $payload;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => 'user', // Always create as 'user' role
        ]);

        $token = $user->createToken('authToken')->plainTextToken;

        if ($user) {
            return response()->json([
                'token_type' => 'Bearer',
                'access_token' => $token,
                'user' => $this->userPayload($user),
                'message' => "berhasil register"
            ], 201);
        }

        return response()->json([
            'success' => false,
        ], 409);
    }

    public function login(Request $request)
    {
        try {
            // validasi input
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            // cek Credentials Login
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json(['message' => 'Email User Salah'], 400);
            }


            // jika hash tidak sesuai muncul alert
            if (!Hash::check($request->password, $user->password, [])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password salah',
                ], 401);
            }

            // jika berhasil
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'token_type' => 'Bearer',
                'access_token' => $token,
                'user' => $this->userPayload($user),
                'message' => "berhasil login"
            ]);
        } catch (Exception $error) {
            return response()->json([
                'status' => false,
                'message' => $error->getMessage(),
            ],  500);
        }
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        return response()->json([
            'user' => $this->userPayload($user),
            'message' => "berhasil logout"
        ]);
    }
}
