<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\RolePermission;

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
        $adminRole = Role::firstOrNew(['slug' => 'admin']);
        if (!$adminRole->exists) {
            $adminRole->id = Role::generateUlid();
            $adminRole->nom = 'Admin';
            $adminRole->save();
        }

        $permissions = Permission::all()->pluck('id');

        $adminRole->permissions()->sync(
            $permissions->mapWithKeys(function ($permissionId) {
                return [
                    $permissionId => [
                        'created_at' => now(),
                        'updated_at' => now(),
                        'id' => RolePermission::generateUlid(), // si tu as une colonne supplémentaire
                    ]
                ];
            })->toArray()
        );

        //adminRole->permissions()->sync(Permission::all());

        // Create User Role
        $userRole = Role::firstOrNew(['slug' => 'user']);
        if (!$userRole->exists) {
            $userRole->id = Role::generateUlid();
            $userRole->nom = 'User';
            $userRole->save();
        }
        $userPermissions = Permission::whereIn('slug', ['view-permission', 'view-role'])->get()->pluck('id');

        $userRole->permissions()->sync(
            $userPermissions->mapWithKeys(function ($permissionId) {
                return [
                    $permissionId => [
                        'created_at' => now(),
                        'updated_at' => now(),
                        'id' => RolePermission::generateUlid(), // si tu as une colonne supplémentaire
                    ]
                ];
            })->toArray()
        );
        //$userRole->permissions()->sync($userPermissions);

        // Create Ecole Role
        $ecoleRole = Role::firstOrNew(['slug' => 'ecole']);
        if (!$ecoleRole->exists) {
            $ecoleRole->id = Role::generateUlid();
            $ecoleRole->nom = 'ecole';
            $ecoleRole->save();
        }
        $ecolePermissions = Permission::whereIn('slug', ['view-permission', 'view-role'])->get()->pluck('id');

        $ecoleRole->permissions()->sync(
            $ecolePermissions->mapWithKeys(function ($permissionId) {
                return [
                    $permissionId => [
                        'created_at' => now(),
                        'updated_at' => now(),
                        'id' => RolePermission::generateUlid(), // si tu as une colonne supplémentaire
                    ]
                ];
            })->toArray()
        );
        //$ecoleRole->permissions()->sync($ecolePermissions);

        // Create Technicien Role
        $technicienRole = Role::firstOrNew(['slug' => 'technicien']);
        if (!$technicienRole->exists) {
            $technicienRole->id = Role::generateUlid();
            $technicienRole->nom = 'technicien';
            $technicienRole->save();
        }
        $technicienPermissions = Permission::whereIn('slug', ['view-permission', 'view-role'])->get()->pluck('id');

        $technicienRole->permissions()->sync(
            $technicienPermissions->mapWithKeys(function ($permissionId) {
                return [
                    $permissionId => [
                        'created_at' => now(),
                        'updated_at' => now(),
                        'id' => RolePermission::generateUlid(), // si tu as une colonne supplémentaire
                    ]
                ];
            })->toArray()
        );
        $technicienRole->permissions()->sync($technicienPermissions);
    }
}
