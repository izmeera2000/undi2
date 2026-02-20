<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
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
            // 'task.add.others',
            'task.edit',
            // 'task.delete.others',
            'task.delete',
            'event.add',
            // 'event.add.others',
            'event.delete',
            // 'event.delete.others',
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

        // Create the roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $moderator = Role::firstOrCreate(['name' => 'moderator']);
        $user = Role::firstOrCreate(['name' => 'user']);

        // Give permissions to the roles
        // Admin gets all permissions
        $admin->givePermissionTo(Permission::all());

        // Moderator gets a subset of permissions
        $moderator->givePermissionTo([
            'members.add',
            'members.view',
            'members.edit',
            'members.delete',
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
            'task.add.personal',
            'task.add.others',
            'task.edit',
            'task.delete.others',
            'task.delete.personal',
            'event.add.personal',
            'event.add.others',
            'event.delete.personal',
            'event.delete.others',
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
        ]);

        // User gets a more restricted set of permissions
        $user->givePermissionTo([
            'members.view',
            'staff.view',
            'pengundi.export',
            'pengundi.view',
            'task.add.personal',
            'task.edit',
            'task.delete.personal',
            'event.add.personal',
            'event.add.others',
            'event.delete.personal',
            'event.delete.others',
        ]);

        // ======================
        // CREATE A TEST USER
        // ======================

        // Create a test user
        $testUser = User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'password' => Hash::make('test'),
        ]);

        // Create a UserProfile for the seeded user
        $testUser->profile()->create([
            'phone' => '123-456-7890',
            'bio' => 'This is a test user bio.',
            'address' => '123 Test Street',
            'profile_picture' => null,
            'cover_picture' => null,
        ]);

        // Assign the admin role to this test user
        $testUser->assignRole('admin');
    }
}
