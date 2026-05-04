<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $moduleLabels = [
            'books' => 'Book',
            'products' => 'Product',
            'courses' => 'Course',
            'users' => 'User',
            'role-permissions' => 'Role Permission',
            'permissions' => 'Permission',
        ];

        foreach (PermissionCatalog::modules() as $module) {
            foreach (PermissionCatalog::actions() as $action) {
                Permission::updateOrCreate(
                    [
                        'name' => $module . '.' . $action,
                    ],
                    [
                        'model_route' => ucfirst($action) . ' ' . ($moduleLabels[$module] ?? ucfirst(rtrim($module, 's'))),
                    ]
                );
            }
        }
    }
}
