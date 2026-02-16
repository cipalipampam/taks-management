<?php

namespace Tests\Feature\Api;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithTaskPermission(): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::firstOrCreate(['name' => 'tasks.manage']);
        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->syncPermissions(['tasks.manage']);

        $user = User::factory()->create();
        $user->syncRoles([$role]);

        return $user;
    }

    public function test_authenticated_user_with_permission_can_list_tasks(): void
    {
        $user = $this->createUserWithTaskPermission();
        Task::factory()->count(2)->create(['created_by' => $user->id]);
        $token = $user->createToken('api')->plainTextToken;

        $response = $this->getJson('/api/tasks', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonCount(2, 'data');
    }

    public function test_authenticated_user_with_permission_can_create_task(): void
    {
        $user = $this->createUserWithTaskPermission();
        $token = $user->createToken('api')->plainTextToken;

        $response = $this->postJson('/api/tasks', [
            'title' => 'API Task',
            'description' => 'From API',
            'status' => 'todo',
            'assignees' => [],
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'API Task', 'status' => 'todo']);

        $this->assertDatabaseHas('tasks', ['title' => 'API Task', 'created_by' => $user->id]);
    }

    public function test_authenticated_user_with_permission_can_show_task(): void
    {
        $user = $this->createUserWithTaskPermission();
        $task = Task::factory()->create(['created_by' => $user->id]);
        $token = $user->createToken('api')->plainTextToken;

        $response = $this->getJson('/api/tasks/'.$task->id, [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $task->id, 'title' => $task->title]);
    }

    public function test_authenticated_user_with_permission_can_update_task(): void
    {
        $user = $this->createUserWithTaskPermission();
        $task = Task::factory()->create(['created_by' => $user->id, 'status' => 'todo']);
        $token = $user->createToken('api')->plainTextToken;

        $response = $this->putJson('/api/tasks/'.$task->id, [
            'title' => 'Updated Title',
            'status' => 'doing',
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Updated Title', 'status' => 'doing']);

        $task->refresh();
        $this->assertSame('Updated Title', $task->title);
        $this->assertSame('doing', $task->status);
    }

    public function test_authenticated_user_with_permission_can_delete_task(): void
    {
        $user = $this->createUserWithTaskPermission();
        $task = Task::factory()->create(['created_by' => $user->id]);
        $token = $user->createToken('api')->plainTextToken;

        $response = $this->deleteJson('/api/tasks/'.$task->id, [], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(204);
        $this->assertModelMissing($task);
    }

    public function test_tasks_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/tasks');

        $response->assertStatus(401);
    }

    public function test_user_without_task_permission_cannot_list_tasks(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'staff']);
        $user = User::factory()->create();
        $user->assignRole('staff');
        $token = $user->createToken('api')->plainTextToken;

        $response = $this->getJson('/api/tasks', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(403);
    }

    public function test_my_tasks_returns_assigned_tasks_for_staff(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::firstOrCreate(['name' => 'tasks.update-status']);
        $staffRole = Role::firstOrCreate(['name' => 'staff']);
        $staffRole->syncPermissions(['tasks.update-status']);

        $staff = User::factory()->create();
        $staff->syncRoles([$staffRole]);

        $otherUser = User::factory()->create();
        $assignedTask = Task::factory()->create(['created_by' => $otherUser->id]);
        $assignedTask->assignees()->sync([$staff->id]);

        $token = $staff->createToken('api')->plainTextToken;

        $response = $this->getJson('/api/my-tasks', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data'])
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $assignedTask->id, 'title' => $assignedTask->title]);
    }

    public function test_assignee_can_update_task_status(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::firstOrCreate(['name' => 'tasks.update-status']);
        $staffRole = Role::firstOrCreate(['name' => 'staff']);
        $staffRole->syncPermissions(['tasks.update-status']);

        $staff = User::factory()->create();
        $staff->syncRoles([$staffRole]);

        $task = Task::factory()->create(['status' => 'todo']);
        $task->assignees()->sync([$staff->id]);

        $token = $staff->createToken('api')->plainTextToken;

        $response = $this->patchJson('/api/tasks/'.$task->id.'/status', [
            'status' => 'doing',
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'doing']);

        $task->refresh();
        $this->assertSame('doing', $task->status);
    }

    public function test_non_assignee_cannot_update_task_status(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::firstOrCreate(['name' => 'tasks.update-status']);
        $staffRole = Role::firstOrCreate(['name' => 'staff']);
        $staffRole->syncPermissions(['tasks.update-status']);

        $staff = User::factory()->create();
        $staff->syncRoles([$staffRole]);

        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['status' => 'todo']);
        $task->assignees()->sync([$otherUser->id]);

        $token = $staff->createToken('api')->plainTextToken;

        $response = $this->patchJson('/api/tasks/'.$task->id.'/status', [
            'status' => 'doing',
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(403);
    }

    public function test_supervisor_can_list_own_created_tasks(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::firstOrCreate(['name' => 'tasks.manage.staff']);
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);
        $supervisorRole->syncPermissions(['tasks.manage.staff']);

        $supervisor = User::factory()->create();
        $supervisor->syncRoles([$supervisorRole]);

        Role::firstOrCreate(['name' => 'staff']);
        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $task = Task::factory()->create(['created_by' => $supervisor->id]);
        $task->assignees()->sync([$staff->id]);

        $token = $supervisor->createToken('api')->plainTextToken;

        $response = $this->getJson('/api/supervisor/tasks', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data'])
            ->assertJsonCount(1, 'data');
        $this->assertSame($task->id, $response->json('data.0.id'));
    }

    public function test_staff_cannot_access_supervisor_tasks(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::firstOrCreate(['name' => 'tasks.update-status']);
        Role::firstOrCreate(['name' => 'staff']);
        $staff = User::factory()->create();
        $staff->assignRole('staff');
        $staff->givePermissionTo('tasks.update-status');

        $token = $staff->createToken('api')->plainTextToken;

        $response = $this->getJson('/api/supervisor/tasks', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(403);
    }
}
