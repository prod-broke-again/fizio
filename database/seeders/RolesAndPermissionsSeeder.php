<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'view users']);
        Permission::create(['name' => 'manage users']);

        Permission::create(['name' => 'view workouts']);
        Permission::create(['name' => 'manage workouts']);
        
        Permission::create(['name' => 'view meals']);
        Permission::create(['name' => 'manage meals']);

        Permission::create(['name' => 'view roles']);
        Permission::create(['name' => 'manage roles']);
        
        Permission::create(['name' => 'view logs']);

        // create roles and assign existing permissions
        $userRole = Role::create(['name' => 'user']);
        $userRole->givePermissionTo(['view workouts', 'view meals']);

        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all()); // Даем админу все права
    }
}