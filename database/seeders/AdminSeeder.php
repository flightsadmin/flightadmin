<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $actions = ['create', 'edit', 'view', 'delete'];
        $models = ['User', 'Role', 'Permission', 'Schedule', 'Flight'];

        foreach ($models as $model) {
            foreach ($actions as $action) {
                $methodName = $action.ucfirst($model);
                Permission::create(['name' => $methodName]);
            }
        }

        $roles = [
            [
                'name' => 'user',
                'permissions' => ['viewUser', 'viewRole', 'viewPermission'],
            ],
            [
                'name' => 'admin',
                'permissions' => Permission::where(function ($query) {
                    $query->where('name', 'like', 'view%');
                })->pluck('name')->toArray(),
            ],
            [
                'name' => 'super-admin',
                'permissions' => Permission::pluck('name')->toArray(),
            ],
        ];

        foreach ($roles as $key => $roleData) {
            $role = Role::create(['name' => $roleData['name']]);
            $role->givePermissionTo($roleData['permissions']);

            User::create([
                'name' => ucwords(explode('-', $roleData['name'])[0]).' User',
                'email' => $roleData['name'].'@flightadmin.info',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(30),
            ])->assignRole($role);
        }

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ])->assignRole('user');

        User::factory()->create([
            'name' => 'Wab Admin',
            'email' => 'wab@flightadmin.info',
        ])->assignRole('super-admin');
    }
}
