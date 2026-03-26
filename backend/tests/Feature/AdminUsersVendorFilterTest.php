<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUsersVendorFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_users_endpoint_can_filter_vendor_directory_with_profile_fields(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $vendor = User::factory()->create([
            'role' => 'vendor',
            'email' => 'vendor-one@example.com',
            'phone' => '+85512345678',
            'location' => 'Phnom Penh',
            'profile_image_url' => 'https://example.com/vendor-one.jpg',
        ]);

        $secondVendor = User::factory()->create([
            'role' => 'vendor',
            'email' => 'vendor-two@example.com',
            'phone' => '+85598765432',
            'location' => 'Siem Reap',
            'profile_image_url' => 'https://example.com/vendor-two.jpg',
        ]);

        User::factory()->create([
            'role' => 'user',
            'email' => 'customer@example.com',
        ]);

        Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'Wedding Decor',
            'event_type' => 'wedding',
            'location' => 'Phnom Penh',
            'starts_at' => now()->addWeek(),
            'price' => 1000,
            'capacity' => 20,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->getJson('/api/admin/users?role=vendor&per_page=100');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonMissing(['email' => 'customer@example.com'])
            ->assertJsonFragment([
                'id' => $vendor->id,
                'email' => 'vendor-one@example.com',
                'role' => 'vendor',
                'phone' => '+85512345678',
                'location' => 'Phnom Penh',
                'profile_image_url' => 'https://example.com/vendor-one.jpg',
                'events_count' => 1,
            ])
            ->assertJsonFragment([
                'id' => $secondVendor->id,
                'email' => 'vendor-two@example.com',
                'role' => 'vendor',
                'phone' => '+85598765432',
                'location' => 'Siem Reap',
                'profile_image_url' => 'https://example.com/vendor-two.jpg',
                'events_count' => 0,
            ]);
    }
}
