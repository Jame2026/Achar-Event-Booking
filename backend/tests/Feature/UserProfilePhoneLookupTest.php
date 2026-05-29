<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfilePhoneLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_can_be_loaded_by_phone(): void
    {
        $user = User::factory()->create([
            'email' => null,
            'phone' => '+855882446786',
            'name' => 'Chem',
        ]);

        $response = $this->getJson('/api/user/profile?phone=%2B855882446786');

        $response
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.phone', '+855882446786')
            ->assertJsonPath('user.name', 'Chem');
    }
}
