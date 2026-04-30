<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Support\PermissionCatalog;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('role_permissions')) {
            Schema::create('role_permissions', function (Blueprint $table) {
                $table->id();
                $table->string('role', 50);
                $table->string('permission', 100);
                $table->boolean('allowed')->default(false);
                $table->timestamps();

                $table->unique(['role', 'permission']);
            });
        }

        $now = now();
        $defaults = config('permissions.defaults', []);

        $rows = [];

        foreach ($defaults as $role => $permissions) {
            foreach ($permissions as $permission => $allowed) {
                $rows[] = [
                    'role' => $role,
                    'permission' => $permission,
                    'allowed' => $allowed,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (DB::table('role_permissions')->count() === 0) {
            DB::table('role_permissions')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
