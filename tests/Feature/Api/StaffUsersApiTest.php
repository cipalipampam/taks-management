<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StaffUsersApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_can_list_staff_users(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::firstOrCreate(['name' => 'tasks.manage.staff']);
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);
        $supervisorRole->syncPermissions(['tasks.manage.staff']);
        Role::firstOrCreate(['name' => 'staff']);

        $supervisor = User::factory()->create();
        $supervisor->syncRoles([$supervisorRole]);

        $staff = User::factory()->create(['name' => 'Staff A']);
        $staff->assignRole('staff');

        $token = $supervisor->createToken('api')->plainTextToken;

        $response = $this->getJson('/api/users/staff', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data'])
            ->assertJsonFragment(['id' => $staff->id, 'name' => 'Staff A']);
    }

    public function test_staff_cannot_list_staff_users(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'staff']);
        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $token = $staff->createToken('api')->plainTextToken;

        $response = $this->getJson('/api/users/staff', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(403);
    }
}
