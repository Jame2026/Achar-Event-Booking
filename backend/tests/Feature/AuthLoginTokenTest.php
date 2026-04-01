<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthLoginTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_a_personal_access_token_for_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'login-user@example.com',
            'password' => 'secret123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login-user@example.com',
            'password' => 'secret123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Login successful.')
            ->assertJsonPath('user.email', 'login-user@example.com')
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'phone', 'location', 'profile_image_url', 'role'],
                'token',
            ]);

        $token = $response->json('token');

        $this->assertIsString($token);
        $this->assertNotSame('', trim($token));
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertSame($user->id, PersonalAccessToken::query()->firstOrFail()->tokenable_id);
    }
}
