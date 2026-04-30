<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Support\PermissionCatalog;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $defaults = PermissionCatalog::defaultsForRole('admin');
        $rows = [];

        foreach (PermissionCatalog::roles() as $role) {
            $rows[] = [
                'role' => $role,
                'permission' => 'reports.view',
                'allowed' => PermissionCatalog::defaultsForRole($role)['reports.view'] ?? false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach ($rows as $row) {
            $exists = DB::table('role_permissions')
                ->where('role', $row['role'])
                ->where('permission', $row['permission'])
                ->exists();

            if (!$exists) {
                DB::table('role_permissions')->insert($row);
            }
        }
    }

    public function down(): void
    {
        DB::table('role_permissions')->where('permission', 'reports.view')->delete();
    }
};
