<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorServiceStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_package_service_price_is_summed_from_all_included_services(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
        ]);

        $response = $this->postJson('/api/vendor/services', [
            'vendor_user_id' => $vendor->id,
            'title' => 'Wedding Full Package',
            'event_type' => 'wedding',
            'description' => 'Complete wedding setup package.',
            'service_mode' => 'package',
            'packages' => [
                [
                    'name' => 'Photography',
                    'price' => 120,
                    'details' => 'Full day coverage',
                ],
                [
                    'name' => 'Decoration',
                    'price' => 230,
                    'details' => 'Stage and table decor',
                ],
            ],
            'price' => 0,
            'location' => 'Phnom Penh',
            'starts_at' => now()->addDays(10)->toDateTimeString(),
            'capacity' => 50,
            'is_active' => true,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('service_mode', 'package')
            ->assertJsonPath('price', '350.00');

        $this->assertDatabaseHas('events', [
            'vendor_id' => $vendor->id,
            'title' => 'Wedding Full Package',
            'price' => 350,
        ]);
    }
}
