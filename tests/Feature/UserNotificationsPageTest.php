<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserNotificationsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_notifications_page(): void
    {
        $response = $this->get(route('notifications'));

        $response->assertRedirect();
    }

    public function test_authenticated_user_can_access_notifications_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('notifications'));

        $response->assertOk();
        $response->assertSeeLivewire('user-notifications');
    }
}
