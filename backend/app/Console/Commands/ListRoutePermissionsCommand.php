<?php

namespace App\Console\Commands;

use App\Support\PermissionCatalog;
use Illuminate\Console\Command;

class ListRoutePermissionsCommand extends Command
{
    protected $signature = 'list:route-permissions
                            {--format=table : Output format (table, json, or snippets)}
                            {--module= : Filter by specific module}';

    protected $description = 'Display required permission middleware for all modules and routes';

    public function handle()
    {
        $format = $this->option('format');
        $filter = $this->option('module');

        $modules = PermissionCatalog::modules();
        $actions = PermissionCatalog::actions();

        // Filter modules if specified
        if ($filter) {
            $modules = array_filter($modules, fn ($m) => $m === strtolower($filter));
            if (empty($modules)) {
                $this->error("Module '{$filter}' not found in config/permissions.php");
                return 1;
            }
        }

        match ($format) {
            'json' => $this->outputJson($modules, $actions),
            'snippets' => $this->outputSnippets($modules, $actions),
            default => $this->outputTable($modules, $actions),
        };

        return 0;
    }

    private function outputTable($modules, $actions)
    {
        $this->info('');
        $this->info('=== Route Permission Middleware Reference ===');
        $this->info('');

        foreach ($modules as $module) {
            $this->line("<fg=cyan>$module</>");

            $rows = [];
            foreach ($actions as $action) {
                $permission = "$module.$action";

                // Map action to HTTP method and middleware pattern
                $httpMethod = match ($action) {
                    'view' => 'GET',
                    'create' => 'POST',
                    'edit' => 'PUT/PATCH',
                    'delete' => 'DELETE',
                    default => '?',
                };

                $middleware = "permission:$permission";

                $rows[] = [
                    '  Action' => $action,
                    'HTTP' => $httpMethod,
                    'Middleware' => $middleware,
                ];
            }

            $this->table(
                array_keys($rows[0]),
                $rows,
                'compact'
            );
            $this->line('');
        }

        $this->info('Example usage in routes/api.php:');
        $this->line('');
        $this->line("  Route::get('/books', [BookController::class, 'index'])");
        $this->line("      ->middleware('permission:books.view');");
        $this->line('');
        $this->line("  Route::post('/books', [BookController::class, 'store'])");
        $this->line("      ->middleware('permission:books.create');");
        $this->line('');
    }

    private function outputSnippets($modules, $actions)
    {
        $this->info('');
        $this->info('=== Copy-paste snippets for routes/api.php ===');
        $this->info('');

        foreach ($modules as $module) {
            $controller = ucfirst($module) . 'Controller';

            $this->line("<fg=cyan>// $module routes</>");
            $this->line("Route::middleware('permission:$module.view')->group(function () {");
            $this->line("    Route::get('/$module', [$controller::class, 'index']);");
            $this->line("    Route::get('/$module/{id}', [$controller::class, 'show']);");
            $this->line("});");
            $this->line("");
            $this->line("Route::post('/$module', [$controller::class, 'store'])");
            $this->line("    ->middleware('permission:$module.create');");
            $this->line("");
            $this->line("Route::put('/$module/{id}', [$controller::class, 'update'])");
            $this->line("    ->middleware('permission:$module.edit');");
            $this->line("");
            $this->line("Route::delete('/$module/{id}', [$controller::class, 'destroy'])");
            $this->line("    ->middleware('permission:$module.delete');");
            $this->line('');
        }

        $this->info('To add a new module:');
        $this->info('  1. Add module name to config/permissions.php MODULES array');
        $this->info('  2. Define default permissions in config/permissions.php for all roles');
        $this->info('  3. Use snippets above as template for new module routes');
        $this->info('  4. Create module migration if new permissions added');
        $this->info('');
    }

    private function outputJson($modules, $actions)
    {
        $result = [];

        foreach ($modules as $module) {
            $result[$module] = [];

            foreach ($actions as $action) {
                $permission = "$module.$action";

                $httpMethod = match ($action) {
                    'view' => 'GET',
                    'create' => 'POST',
                    'edit' => 'PUT/PATCH',
                    'delete' => 'DELETE',
                    default => '?',
                };

                $result[$module][$action] = [
                    'permission' => $permission,
                    'http_method' => $httpMethod,
                    'middleware' => "permission:$permission",
                ];
            }
        }

        $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
