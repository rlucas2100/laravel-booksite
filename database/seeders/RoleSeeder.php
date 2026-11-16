<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public const PERMISSIONS = [
        'manage-users',        // the members list: edit, delete
        'manage-entries',      // edit or delete anything on the shelves
        'manage-categories',   // add, rename, delete categories
        'moderate-community',  // delete reviews and comments
    ];

    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission);
        }

        // Gets every permission through Gate::before; see AppServiceProvider
        Role::findOrCreate(User::OWNER_ROLE);

        Role::findOrCreate(User::ADMIN_ROLE)->syncPermissions(self::PERMISSIONS);

        // Everyone else is a member: no role needed.
    }
}
