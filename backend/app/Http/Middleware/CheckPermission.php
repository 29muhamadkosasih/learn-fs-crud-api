<?php

namespace App\Http\Middleware;

use App\Models\RolePermission;
use App\Support\PermissionCatalog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $requiredPermissions = collect($permissions)
            ->flatMap(fn ($permission) => explode('|', $permission))
            ->map(fn ($permission) => trim($permission))
            ->filter()
            ->values()
            ->all();

        if (empty($requiredPermissions)) {
            return $next($request);
        }

        foreach ($requiredPermissions as $permission) {
            if ($this->hasPermission($user->role ?? 'user', $permission)) {
                return $next($request);
            }
        }

        return response()->json([
            'message' => 'Forbidden. Permission tidak diizinkan.',
        ], 403);
    }

    private function hasPermission(string $role, string $permission): bool
    {
        $override = RolePermission::query()
            ->where('role', $role)
            ->where('permission', $permission)
            ->first();

        if ($override !== null) {
            return (bool) $override->allowed;
        }

        return PermissionCatalog::defaultsForRole($role)[$permission] ?? false;
    }
}
