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
            ['name' => 'View Permissions', 'slug' => 'view-permissions'],
            ['name' => 'View Permission', 'slug' => 'view-permission'],

            // Roles
            ['name' => 'View Roles', 'slug' => 'view-roles'],
            ['name' => 'View Role', 'slug' => 'view-role'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['slug' => $permission['slug']], $permission);
        }
    }
}
