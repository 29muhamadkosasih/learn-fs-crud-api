<?php

namespace App\Support;

class PermissionCatalog
{
    public static function roles(): array
    {
        return config('permissions.roles', []);
    }

    public static function modules(): array
    {
        return config('permissions.modules', []);
    }

    public static function actions(): array
    {
        return config('permissions.actions', []);
    }

    public static function permissions(): array
    {
        return array_values(array_unique(array_merge(...array_map(
            fn (string $module) => array_map(
                fn (string $action) => $module . '.' . $action,
                self::actions()
            ),
            self::modules()
        ))));
    }

    public static function defaultsForRole(?string $role): array
    {
        $role = $role ?: 'user';

        return config('permissions.defaults.' . $role, []);
    }

    /**
     * Get all route permissions mapped to HTTP methods
     * Useful for generating route documentation or middleware suggestions
     */
    public static function routePermissions(): array
    {
        $result = [];

        foreach (self::modules() as $module) {
            $result[$module] = [];

            foreach (self::actions() as $action) {
                $result[$module][$action] = [
                    'permission' => "$module.$action",
                    'http_method' => self::actionToHttpMethod($action),
                    'middleware' => "permission:$module.$action",
                    'route_path' => "/" . self::pluralizeModule($module),
                ];
            }
        }

        return $result;
    }

    /**
     * Map action name to HTTP method
     */
    public static function actionToHttpMethod(string $action): string
    {
        return match (strtolower($action)) {
            'view' => 'GET',
            'create' => 'POST',
            'edit' => 'PUT/PATCH',
            'delete' => 'DELETE',
            default => 'GET',
        };
    }

    /**
     * Pluralize module name for route path
     */
    public static function pluralizeModule(string $module): string
    {
        // Simple pluralization (just add 's')
        // Can be improved with Str::plural() if needed
        $irregulars = [
            'course' => 'courses',
            'product' => 'products',
            'book' => 'books',
            'user' => 'users',
        ];

        return $irregulars[strtolower($module)] ?? $module . 's';
    }

    /**
     * Get middleware string for a specific module action
     */
    public static function middlewareFor(string $module, string $action): string
    {
        return "permission:$module.$action";
    }

    /**
     * Generate a controller name from module name
     */
    public static function controllerNameFor(string $module): string
    {
        return ucfirst($module) . 'Controller';
    }
}
