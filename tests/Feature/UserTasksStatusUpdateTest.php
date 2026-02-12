<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserTasksStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_cannot_update_task_status(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'tasks.update-status']);
        $role = Role::firstOrCreate(['name' => 'supervisor']);
        $role->syncPermissions([]);

        $supervisor = User::factory()->create();
        $supervisor->syncRoles([$role]);

        $task = Task::factory()->create(['status' => 'todo']);
        $task->assignees()->sync([$supervisor->id]);

        $this->actingAs($supervisor);

        Volt::test('user-tasks')
            ->call('updateTaskStatus', $task->id, 'doing');

        $task->refresh();
        $this->assertSame('todo', $task->status);
    }

    public function test_user_with_permission_can_update_assigned_task_status(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'tasks.update-status']);
        $role = Role::firstOrCreate(['name' => 'supervisor']);
        $role->syncPermissions(['tasks.update-status']);

        $supervisor = User::factory()->create();
        $supervisor->syncRoles([$role]);

        $task = Task::factory()->create(['status' => 'todo']);
        $task->assignees()->sync([$supervisor->id]);

        $this->actingAs($supervisor);

        Volt::test('user-tasks')
            ->call('updateTaskStatus', $task->id, 'doing')
            ->assertHasNoErrors();

        $task->refresh();
        $this->assertSame('doing', $task->status);
    }
}
