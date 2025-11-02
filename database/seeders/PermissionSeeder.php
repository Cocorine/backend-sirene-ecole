<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            // Permissions
            ['nom' => 'View Permissions', 'slug' => 'view-permissions'],
            ['nom' => 'View Permission', 'slug' => 'view-permission'],

            // Roles
            ['nom' => 'View Roles', 'slug' => 'view-roles'],
            ['nom' => 'View Role', 'slug' => 'view-role'],
        ];

        foreach ($permissions as $permissionData) {
            $permission = Permission::firstOrNew(['slug' => $permissionData['slug']], $permissionData);
            if (!$permission->exists) {
                $permission->id = Permission::generateUlid();
                $permission->fill($permissionData);
                $permission->save();
            }
        }
    }
}
