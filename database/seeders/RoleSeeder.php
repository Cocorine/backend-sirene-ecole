<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Admin Role
        $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
        $adminRole->permissions()->sync(Permission::all());

        // Create User Role
        $userRole = Role::firstOrCreate(['slug' => 'user'], ['name' => 'User']);
        $userPermissions = Permission::whereIn('slug', ['view-permission', 'view-role'])->get();
        $userRole->permissions()->sync($userPermissions);

        // Create Ecole Role
        $userRole = Role::firstOrCreate(['slug' => 'ecole'], ['name' => 'ecole']);
        $userPermissions = Permission::whereIn('slug', ['view-permission', 'view-role'])->get();
        $userRole->permissions()->sync($userPermissions);

        // Create Technicien Role
        $userRole = Role::firstOrCreate(['slug' => 'technicien'], ['name' => 'technicien']);
        $userPermissions = Permission::whereIn('slug', ['view-permission', 'view-role'])->get();
        $userRole->permissions()->sync($userPermissions);
    }
}
