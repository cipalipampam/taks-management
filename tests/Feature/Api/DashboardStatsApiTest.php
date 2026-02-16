<?php

namespace Tests\Feature\Api;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DashboardStatsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_stats_requires_authentication(): void
    {
        $response = $this->getJson('/api/dashboard/stats');

        $response->assertStatus(401);
    }

    public function test_admin_user_receives_admin_stats(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::firstOrCreate(['name' => 'tasks.manage']);
        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->syncPermissions(['tasks.manage']);

        $user = User::factory()->create();
        $user->syncRoles([$role]);

        Task::factory()->count(3)->create(['status' => 'todo']);
        Task::factory()->count(2)->create(['status' => 'done']);

        $token = $user->createToken('api')->plainTextToken;

        $response = $this->getJson('/api/dashboard/stats', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'type' => 'admin',
                'stats' => [
                    'total_tasks' => 5,
                    'todo_tasks' => 3,
                    'done_tasks' => 2,
                    'total_users' => 1,
                ],
            ])
            ->assertJsonStructure(['type', 'stats' => ['completion_rate']]);
    }

    public function test_regular_user_receives_user_stats(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::firstOrCreate(['name' => 'tasks.update-status']);
        $role = Role::firstOrCreate(['name' => 'staff']);
        $role->syncPermissions(['tasks.update-status']);

        $user = User::factory()->create();
        $user->syncRoles([$role]);

        $taskTodo = Task::factory()->create(['status' => 'todo']);
        $taskDone = Task::factory()->create(['status' => 'done']);
        $user->assignedTasks()->sync([$taskTodo->id, $taskDone->id]);

        $token = $user->createToken('api')->plainTextToken;

        $response = $this->getJson('/api/dashboard/stats', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200)
            ->assertJson(['type' => 'user'])
            ->assertJsonStructure(['type', 'stats']);
        $this->assertIsArray($response->json('stats'));
        $this->assertNotEmpty($response->json('stats'));
    }
}
