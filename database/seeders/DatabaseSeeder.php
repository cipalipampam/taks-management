<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'users.manage',
            'tasks.manage',
            'tasks.manage.staff',
            'tasks.update-status',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(['users.manage', 'tasks.manage', 'tasks.manage.staff', 'tasks.update-status']);

        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);
        $supervisorRole->syncPermissions(['tasks.manage.staff', 'tasks.update-status']);

        $staffRole = Role::firstOrCreate(['name' => 'staff']);
        $staffRole->syncPermissions(['tasks.update-status']);

        // Check if admin already exists
        if (! User::where('email', 'admin@example.com')->exists()) {
            $admin = User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
            ]);
        } else {
            $admin = User::where('email', 'admin@example.com')->first();
        }

        $admin->syncRoles([$adminRole]);

        // Check if supervisor already exists
        if (! User::where('email', 'supervisor@example.com')->exists()) {
            $supervisor = User::create([
                'name' => 'Supervisor User',
                'email' => 'supervisor@example.com',
                'password' => bcrypt('password'),
            ]);
        } else {
            $supervisor = User::where('email', 'supervisor@example.com')->first();
        }

        $supervisor->syncRoles([$supervisorRole]);

        // Check if staff user already exists
        if (! User::where('email', 'staff@example.com')->exists()) {
            $staff = User::create([
                'name' => 'Staff User',
                'email' => 'staff@example.com',
                'password' => bcrypt('password'),
            ]);
        } else {
            $staff = User::where('email', 'staff@example.com')->first();
        }

        $staff->syncRoles([$staffRole]);

        // Create sample tasks only if none exist
        if (Task::count() === 0) {
            Task::factory(10)->create([
                'created_by' => $admin->id,
            ])->each(function (Task $task) use ($staff) {
                $task->assignees()->sync([$staff->id]);
            });

            // Some tasks created by supervisor user
            Task::factory(5)->create([
                'created_by' => $supervisor->id,
            ])->each(function (Task $task) use ($staff) {
                $task->assignees()->sync([$staff->id]);
            });
        }
    }
}
