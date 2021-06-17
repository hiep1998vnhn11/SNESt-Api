<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;
use App\Models\Info;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        Permission::create(['name' => 'show user']);
        Permission::create(['name' => 'edit user']);
        Permission::create(['name' => 'delete user']);
        Permission::create(['name' => 'create user']);
        Permission::create(['name' => 'block user']);
        Permission::create(['name' => 'unblock user']);
        Permission::create(['name' => 'set admin']);
        Permission::create(['name' => 'unset admin']);
        $role1 = Role::create(['name' => 'viewer']);
        $role1->givePermissionTo('show user');
        $role2 = Role::create(['name' => 'admin']);
        $role2->givePermissionTo('show user');
        $role2->givePermissionTo('edit user');
        $role2->givePermissionTo('delete user');
        $role2->givePermissionTo('create user');
        $role2->givePermissionTo('block user');
        $role2->givePermissionTo('unblock user');
        $role3 = Role::create(['name' => 'super-admin']);
        $role3->givePermissionTo(Permission::all());
        $role4 = Role::create(['name' => 'blocked']);
        $user = User::create([
            'first_name' => 'Hiệp',
            'last_name' => 'Trần',
            'full_name' => 'Hiệp Trần',
            'slug' => 'hiep-tran',
            'email' => 'hiep@gmail.com',
            'password' => bcrypt('123456'),
            'url' => 'hiep'
        ]);
        $user->assignRole($role1);
        Info::create([
            'user_id' => $user->id,
            'gender' => 'male',
        ]);
        $user = User::create([
            'first_name' => 'Hiệp',
            'last_name' => 'Trần',
            'full_name' => 'Hiệp Trần',
            'slug' => 'hiep-tran',
            'email' => 'admin@admin',
            'url' => 'admin',
            'password' => bcrypt('admin'),
        ]);
        $user->assignRole($role2);
        Info::create([
            'user_id' => $user->id,
            'gender' => 'male',
        ]);
    }
}
