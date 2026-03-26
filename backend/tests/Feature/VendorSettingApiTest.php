<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorSettingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_settings_can_be_saved_by_vendor_user_id(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
        ]);

        $response = $this->patchJson('/api/vendor/settings', [
            'vendor_user_id' => $vendor->id,
            'blocked_ranges' => [
                [
                    'start_date' => '2026-04-01',
                    'end_date' => '2026-04-04',
                    'note' => 'Vacation mode',
                ],
            ],
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('settings.event_id', null)
            ->assertJsonPath('settings.blocked_ranges.0.start_date', '2026-04-01')
            ->assertJsonPath('settings.blocked_ranges.0.end_date', '2026-04-04');

        $this->assertDatabaseHas('vendor_settings', [
            'user_id' => $vendor->id,
            'event_id' => null,
        ]);
    }

    public function test_saved_vendor_day_off_blocks_public_availability(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
        ]);

        $event = Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'Birthday',
            'event_type' => 'birthday',
            'location' => 'Phnom Penh',
            'starts_at' => '2026-04-10 09:00:00',
            'price' => 1150,
            'capacity' => 1,
            'is_active' => true,
        ]);

        $saveResponse = $this->patchJson('/api/vendor/settings', [
            'vendor_user_id' => $vendor->id,
            'blocked_ranges' => [
                [
                    'start_date' => '2026-04-01',
                    'end_date' => '2026-04-04',
                    'note' => 'Vacation mode',
                ],
            ],
        ]);

        $saveResponse->assertOk();

        $availabilityResponse = $this->getJson("/api/events/{$event->id}/availability?requested_date=2026-04-02&quantity=1");

        $availabilityResponse
            ->assertOk()
            ->assertJsonPath('service_available', true)
            ->assertJsonPath('vendor_available', false)
            ->assertJsonPath('has_vendor_day_off', true)
            ->assertJsonPath('vendor_day_off_scope', 'account');
    }
}
