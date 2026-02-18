<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Book;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

      // Reset cached roles and permissions
      app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();


      // create permissions
      Permission::create(['name' => 'manage-users']);
      Permission::create(['name' => 'create-users']);
      Permission::create(['name' => 'edit-users']);
      Permission::create(['name' => 'delete-users']);

      Permission::create(['name' => 'manage-books']);
      Permission::create(['name' => 'create-books']);
      Permission::create(['name' => 'edit-books']);
      Permission::create(['name' => 'delete-books']);

      Permission::create(['name' => 'manage-categories']);
      Permission::create(['name' => 'create-categories']);
      Permission::create(['name' => 'edit-categories']);
      Permission::create(['name' => 'delete-categories']);

      // gets all permissions via Gate::before rule; see AuthServiceProvider
      $super_admin_role = Role::create(['name' => 'Super-Admin']);
      $role = Role::create(['name' => 'Admin']);
      $role->givePermissionTo(Permission::all());

      $user = \App\Models\User::factory()->create([
        'name' => 'Super Admin',
        'email' => 'superadmin@example.com',
        'password' => bcrypt('password'),
      ]);
      $user->assignRole($super_admin_role);

      $user = \App\Models\User::factory()->create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
      ]);
      $user->assignRole($role);



//      $role->givePermissionTo([
//        'manage-users',
//        'create-users',
//      ]);

//      $editorRole->givePermissionTo([
//        'create-books',
//        'edit-books',
//        'delete-books',
//      ]);


    }
}
