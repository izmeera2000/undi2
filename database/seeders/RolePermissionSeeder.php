<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ======================
        // CREATE PERMISSIONS
        // ======================

        $permissions = [
            'members.add',
            'members.view',
            'members.edit',
            'members.delete',

            'elections.add',
            'elections.view',
            'elections.edit',
            'elections.delete',

            'staff.view',
            'staff.add',
            'staff.edit',
            'staff.delete',
            'staff.suspend',
            'staff.role',
            'staff.revoke',

            'pengundi.add',
            'pengundi.export',
            'pengundi.view',

            'task.add',
            'task.add.others',
            'task.edit',
            'task.delete.others',
            'task.delete',

            'event.add',
            'event.add.others',
            'event.delete',
            'event.delete.others',
            'event.view',

            'parlimen.view',
            'parlimen.add',
            'parlimen.edit',
            'parlimen.delete',

            'dun.view',
            'dun.add',
            'dun.edit',
            'dun.delete',
            'dm.view',
            'dm.add',
            'dm.edit',
            'dm.delete',

            'lokaliti.view',
            'lokaliti.add',
            'lokaliti.edit',
            'lokaliti.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ======================
        // CREATE ROLES
        // ======================

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $moderator = Role::firstOrCreate(['name' => 'moderator']);
        $user = Role::firstOrCreate(['name' => 'user']);

        // Admin gets all permissions
        $admin->syncPermissions(Permission::all());

        // Moderator permissions
        $moderator->syncPermissions([
            'members.add',
            'members.view',
            'members.edit',
            'members.delete',

            'elections.add',
            'elections.view',
            'elections.edit',
            'elections.delete',

            'staff.view',
            'staff.add',
            'staff.edit',
            'staff.delete',
            'staff.suspend',
            'staff.role',
            'staff.revoke',

            'pengundi.add',
            'pengundi.export',
            'pengundi.view',

            'task.add',
            'task.add.others',
            'task.edit',
            'task.delete.others',
            'task.delete',

            'event.add',
            'event.add.others',
            'event.delete',
            'event.delete.others',
            'event.view',

            'parlimen.view',
            'parlimen.add',
            'parlimen.edit',
            'parlimen.delete',

            'dun.view',
            'dun.add',
            'dun.edit',
            'dun.delete',
            'dm.view',
            'dm.add',
            'dm.edit',
            'dm.delete',
            
            'lokaliti.view',
            'lokaliti.add',
            'lokaliti.edit',
            'lokaliti.delete',
        ]);

        // User permissions
        $user->syncPermissions([
            'members.view',

            'elections.view',


            'staff.view',
            'staff.edit',


            'pengundi.export',
            'pengundi.view',

            'task.add',
            'task.edit',
            'task.delete',

            'event.add',
            'event.delete',
            'event.view',


        ]);
    }
}