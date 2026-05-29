<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventIndexVendorProfileImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_index_includes_vendor_profile_image_for_admin_listing(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'name' => 'Vendor Photo Test',
            'profile_image_url' => 'https://example.com/vendor-photo.jpg',
        ]);

        $event = Event::create([
            'vendor_id' => $vendor->id,
            'title' => 'Birthday Master Package',
            'event_type' => 'birthday',
            'description' => 'Full birthday service.',
            'service_mode' => 'overall',
            'location' => 'Phnom Penh',
            'starts_at' => now()->addWeek()->toDateTimeString(),
            'price' => 250,
            'capacity' => 20,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/events?include_inactive=1&per_page=100');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $event->id)
            ->assertJsonPath('data.0.vendor.id', $vendor->id)
            ->assertJsonPath('data.0.vendor.profile_image_url', 'https://example.com/vendor-photo.jpg');
    }
}
