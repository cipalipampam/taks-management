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
}
