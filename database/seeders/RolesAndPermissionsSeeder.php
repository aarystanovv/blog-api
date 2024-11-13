<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Список разрешений
        $permissions = [
            'create_posts',
            'edit_posts',
            'delete_posts',
            'manage_posts',
            'publish_posts',
            'manage_categories',
            'manage_users',
            'view_posts',
        ];

        // Создаем разрешения
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Роль Admin - полные права
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo(Permission::all()); // Даем все разрешения

        // Роль Editor - разрешения на управление постами
        $editorRole = Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);
        $editorRole->givePermissionTo(['manage_posts', 'publish_posts', 'edit_posts', 'delete_posts']);

        // Роль Author - разрешения на создание и редактирование своих постов
        $authorRole = Role::firstOrCreate(['name' => 'Author', 'guard_name' => 'web']);
        $authorRole->givePermissionTo(['create_posts', 'edit_posts']);

        // Роль Reader - разрешение на просмотр постов
        $readerRole = Role::firstOrCreate(['name' => 'Reader', 'guard_name' => 'web']);
        $readerRole->givePermissionTo(['view_posts']);
    }
}
