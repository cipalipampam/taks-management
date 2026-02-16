<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserDirectPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_direct_permission_users_manage_can_access_user_crud_without_admin_role(): void
    {
        Permission::create(['name' => 'users.manage']);
        $staffRole = Role::create(['name' => 'staff']);

        $userWithPermissionOnly = User::factory()->create();
        $userWithPermissionOnly->syncRoles([$staffRole->id]);
        $userWithPermissionOnly->givePermissionTo('users.manage');

        $this->actingAs($userWithPermissionOnly);

        $this->assertTrue(UserResource::canViewAny());
        $this->assertTrue(UserResource::canCreate());

        Livewire::test(ListUsers::class)
            ->assertSuccessful();
    }

    public function test_user_without_users_manage_permission_cannot_access_user_resource(): void
    {
        Role::create(['name' => 'staff']);
        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $this->actingAs($staff);

        $this->assertFalse(UserResource::canViewAny());
        $this->assertFalse(UserResource::canCreate());
    }

    public function test_admin_can_create_user_with_additional_direct_permissions(): void
    {
        $manageUsers = Permission::create(['name' => 'users.manage']);
        $extraPermission = Permission::create(['name' => 'tasks.override-approval']);

        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->syncPermissions([$manageUsers->id]);

        $admin = User::factory()->create();
        $admin->syncRoles([$adminRole->id]);

        $staffRole = Role::create(['name' => 'staff']);

        $this->actingAs($admin);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Budi',
                'email' => 'budi@example.com',
                'password' => 'password',
                'roles' => [(string) $staffRole->id],
                'permissions' => [(string) $extraPermission->id],
            ])
            ->call('create');

        $createdUser = User::query()->where('email', 'budi@example.com')->firstOrFail();

        $this->assertTrue($createdUser->hasRole('staff'));
        $this->assertTrue($createdUser->hasDirectPermission('tasks.override-approval'));
        $this->assertFalse($createdUser->hasRole('admin'));
    }

    public function test_admin_can_update_user_direct_permissions_without_changing_roles(): void
    {
        $manageUsers = Permission::create(['name' => 'users.manage']);
        $permissionA = Permission::create(['name' => 'reports.view']);
        $permissionB = Permission::create(['name' => 'reports.export']);

        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->syncPermissions([$manageUsers->id]);

        $admin = User::factory()->create();
        $admin->syncRoles([$adminRole->id]);

        $staffRole = Role::create(['name' => 'staff']);

        $user = User::factory()->create();
        $user->syncRoles([$staffRole->id]);
        $user->syncPermissions([$permissionA->id]);

        $this->actingAs($admin);

        Livewire::test(EditUser::class, ['record' => $user->getKey()])
            ->fillForm([
                'roles' => [(string) $staffRole->id],
                'permissions' => [(string) $permissionB->id],
            ])
            ->call('save');

        $user->refresh();

        $this->assertTrue($user->hasRole('staff'));
        $this->assertFalse($user->hasDirectPermission('reports.view'));
        $this->assertTrue($user->hasDirectPermission('reports.export'));
    }
}
