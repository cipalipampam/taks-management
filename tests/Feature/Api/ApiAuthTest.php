<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'api@example.com',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'api@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'token_type', 'user'])
            ->assertJson(['token_type' => 'Bearer', 'user' => ['email' => 'api@example.com']]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'api@example.com']);

        $response = $this->postJson('/api/login', [
            'email' => 'api@example.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid credentials.']);
    }

    public function test_login_validation_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_user_can_access_protected_route_with_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $response = $this->getJson('/api/user', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $user->id, 'email' => $user->email]);
    }

    public function test_user_cannot_access_protected_route_without_token(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_logout_revokes_token(): void
    {
        $user = User::factory()->create();
        $accessToken = $user->createToken('api');
        $token = $accessToken->plainTextToken;
        $tokenId = $accessToken->accessToken->id;

        $response = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);
    }
}
