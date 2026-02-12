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

class SupervisorTasksTest extends TestCase
{
    use RefreshDatabase;

    protected User $supervisor;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'tasks.manage.staff']);
        Permission::firstOrCreate(['name' => 'tasks.update-status']);
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);
        $supervisorRole->syncPermissions(['tasks.manage.staff', 'tasks.update-status']);
        $staffRole = Role::firstOrCreate(['name' => 'staff']);

        $this->supervisor = User::factory()->create(['email' => 'supervisor@test.com']);
        $this->supervisor->syncRoles([$supervisorRole]);

        $this->staff = User::factory()->create(['email' => 'staff@test.com']);
        $this->staff->syncRoles([$staffRole]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get(route('supervisor.tasks'));
        $response->assertRedirect(route('login'));
    }

    public function test_staff_cannot_access_supervisor_tasks_page(): void
    {
        $this->actingAs($this->staff);

        $response = $this->get(route('supervisor.tasks'));
        $response->assertForbidden();
    }

    public function test_supervisor_can_access_supervisor_tasks_page(): void
    {
        $this->actingAs($this->supervisor);

        $response = $this->get(route('supervisor.tasks'));
        $response->assertOk();
        $response->assertSee('Manage Staff Tasks');
    }

    public function test_user_with_direct_permission_can_see_manage_staff_tasks_menu_in_sidebar(): void
    {
        $permissionOnlyUser = User::factory()->create(['email' => 'sidebar-perm@test.com']);
        $permissionOnlyUser->givePermissionTo('tasks.manage.staff');

        $this->actingAs($permissionOnlyUser);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee(route('supervisor.tasks'));
        $response->assertSee('Manage Staff Tasks');
    }

    public function test_supervisor_task_manager_component_redirects_without_permission(): void
    {
        $this->actingAs($this->staff);

        Volt::test('supervisor-task-manager')
            ->assertRedirect(route('dashboard'));
    }

    public function test_supervisor_can_create_task(): void
    {
        $this->actingAs($this->supervisor);

        Volt::test('supervisor-task-manager')
            ->call('openCreate')
            ->set('title', 'New Task Title')
            ->set('description', 'Task description')
            ->set('status', 'todo')
            ->set('assignees', [$this->staff->id])
            ->call('saveTask')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'title' => 'New Task Title',
            'description' => 'Task description',
            'status' => 'todo',
            'created_by' => $this->supervisor->id,
        ]);

        $task = Task::query()->where('title', 'New Task Title')->first();
        $this->assertNotNull($task);
        $this->assertTrue($task->assignees->contains($this->staff));
    }

    public function test_supervisor_can_edit_task(): void
    {
        $this->actingAs($this->supervisor);

        $task = Task::factory()->create([
            'title' => 'Original Title',
            'created_by' => $this->supervisor->id,
        ]);
        $task->assignees()->sync([$this->staff->id]);

        Volt::test('supervisor-task-manager')
            ->call('openEdit', $task->id)
            ->set('title', 'Updated Title')
            ->set('status', 'doing')
            ->call('updateTask')
            ->assertHasNoErrors();

        $task->refresh();
        $this->assertSame('Updated Title', $task->title);
        $this->assertSame('doing', $task->status);
    }

    public function test_supervisor_can_delete_task(): void
    {
        $this->actingAs($this->supervisor);

        $task = Task::factory()->create([
            'created_by' => $this->supervisor->id,
        ]);
        $task->assignees()->sync([$this->staff->id]);
        $taskId = $task->id;

        Volt::test('supervisor-task-manager')
            ->call('confirmDelete', $taskId)
            ->call('deleteTask');

        $this->assertDatabaseMissing('tasks', ['id' => $taskId]);
    }

    public function test_supervisor_sees_only_staff_assignable_tasks(): void
    {
        $this->actingAs($this->supervisor);

        $taskWithStaff = Task::factory()->create(['created_by' => $this->supervisor->id]);
        $taskWithStaff->assignees()->sync([$this->staff->id]);

        $taskAlone = Task::factory()->create(['created_by' => $this->supervisor->id]);
        $taskAlone->assignees()->sync([]);

        Volt::test('supervisor-task-manager')
            ->assertSee($taskWithStaff->title);

        $component = Volt::test('supervisor-task-manager');
        $tasks = $component->get('tasks');
        $ids = array_column($tasks, 'id');
        $this->assertContains($taskWithStaff->id, $ids);
        $this->assertNotContains($taskAlone->id, $ids);
    }

    public function test_supervisor_can_assign_task_to_user_with_direct_permission_without_staff_role(): void
    {
        $this->actingAs($this->supervisor);

        $permissionOnlyUser = User::factory()->create(['email' => 'perm-only@test.com']);
        $permissionOnlyUser->givePermissionTo('tasks.update-status');

        Volt::test('supervisor-task-manager')
            ->call('openCreate')
            ->set('title', 'Task For Permission User')
            ->set('description', 'Assigned to a user without staff role')
            ->set('status', 'todo')
            ->set('assignees', [$permissionOnlyUser->id])
            ->call('saveTask')
            ->assertHasNoErrors();

        $task = Task::query()->where('title', 'Task For Permission User')->firstOrFail();
        $this->assertTrue($task->assignees->contains($permissionOnlyUser));
    }
}
