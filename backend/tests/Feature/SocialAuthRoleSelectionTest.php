<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SocialAuthRoleSelectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_id' => 'test-google-client',
            'services.google.client_secret' => 'test-google-secret',
            'services.google.redirect' => 'https://achar-event-booking-232f.vercel.app/auth/google/callback',
            'app.frontend_url' => 'https://achar-event-booking-232f.vercel.app',
        ]);

        Http::preventStrayRequests();
    }

    public function test_google_callback_creates_vendor_when_vendor_role_was_selected(): void
    {
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'google-access-token',
            ]),
            'https://www.googleapis.com/oauth2/v3/userinfo' => Http::response([
                'id' => 'google-user-1',
                'name' => 'Vendor Google User',
                'email' => 'vendor-google@example.com',
            ]),
        ]);

        $state = $this->makeSocialState([
            'frontend_url' => 'https://achar-event-booking-232f.vercel.app',
            'role' => 'vendor',
        ]);

        $response = $this->get("/auth/google/callback?state={$state}&code=test-code");
        $location = (string) $response->headers->get('Location');

        $response->assertRedirect();
        $this->assertStringStartsWith('https://achar-event-booking-232f.vercel.app/auth/google/callback', $location);
        $this->assertStringContainsString('social=success', $location);
        $this->assertStringContainsString('role=vendor', $location);

        $this->assertDatabaseHas('users', [
            'email' => 'vendor-google@example.com',
            'role' => 'vendor',
        ]);
    }

    public function test_google_callback_upgrades_existing_user_to_vendor_when_selected_during_register(): void
    {
        User::factory()->create([
            'name' => 'Existing Planner',
            'email' => 'existing-google@example.com',
            'role' => 'user',
        ]);

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'google-access-token',
            ]),
            'https://www.googleapis.com/oauth2/v3/userinfo' => Http::response([
                'id' => 'google-user-2',
                'name' => 'Existing Planner',
                'email' => 'existing-google@example.com',
            ]),
        ]);

        $state = $this->makeSocialState([
            'frontend_url' => 'https://achar-event-booking-232f.vercel.app',
            'role' => 'vendor',
        ]);

        $response = $this->get("/auth/google/callback?state={$state}&code=test-code");
        $location = (string) $response->headers->get('Location');

        $response->assertRedirect();
        $this->assertStringStartsWith('https://achar-event-booking-232f.vercel.app/auth/google/callback', $location);
        $this->assertStringContainsString('role=vendor', $location);

        $this->assertDatabaseHas('users', [
            'email' => 'existing-google@example.com',
            'role' => 'vendor',
        ]);
    }

    private function makeSocialState(array $overrides = []): string
    {
        $payloadData = array_merge([
            'provider' => 'google',
            'ts' => now()->timestamp,
            'nonce' => 'social-auth-test-nonce',
        ], $overrides);

        $payload = rtrim(strtr(base64_encode(json_encode($payloadData)), '+/', '-_'), '=');
        $signature = hash_hmac('sha256', $payload, (string) config('app.key', 'achar-social-key'));

        return $payload.'.'.$signature;
    }
}
