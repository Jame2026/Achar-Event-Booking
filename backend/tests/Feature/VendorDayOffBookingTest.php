<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Models\VendorSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorDayOffBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_account_day_off_blocks_booking_requests(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
        ]);

        $event = Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'Wedding Styling',
            'event_type' => 'wedding',
            'location' => 'Phnom Penh',
            'starts_at' => now()->addDays(20),
            'price' => 1200,
            'capacity' => 5,
            'is_active' => true,
        ]);

        $blockedDate = now()->addDays(10)->toDateString();

        VendorSetting::query()->create([
            'user_id' => $vendor->id,
            'event_id' => null,
            'timezone' => 'UTC',
            'weekly_schedule' => [],
            'blocked_dates' => [],
            'blocked_ranges' => [
                [
                    'start_date' => $blockedDate,
                    'end_date' => $blockedDate,
                    'note' => 'Vacation mode',
                ],
            ],
        ]);

        $response = $this->postJson('/api/bookings', [
            'event_id' => $event->id,
            'quantity' => 1,
            'customer_name' => 'Dara',
            'customer_email' => 'dara@example.com',
            'requested_event_date' => $blockedDate,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('message', 'Vendor is unavailable on the selected date because the account is marked as day off.');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_vendor_account_day_off_marks_service_availability_as_unavailable(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
        ]);

        Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'Wedding Styling',
            'event_type' => 'wedding',
            'location' => 'Phnom Penh',
            'starts_at' => now()->addDays(20),
            'price' => 1200,
            'capacity' => 5,
            'is_active' => true,
        ]);

        $event = Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'Birthday Package',
            'event_type' => 'birthday',
            'location' => 'Siem Reap',
            'starts_at' => now()->addDays(22),
            'price' => 700,
            'capacity' => 3,
            'is_active' => true,
        ]);

        $blockedDate = now()->addDays(12)->toDateString();

        VendorSetting::query()->create([
            'user_id' => $vendor->id,
            'event_id' => null,
            'timezone' => 'UTC',
            'weekly_schedule' => [],
            'blocked_dates' => [],
            'blocked_ranges' => [
                [
                    'start_date' => $blockedDate,
                    'end_date' => $blockedDate,
                    'note' => 'Vacation mode',
                ],
            ],
        ]);

        $response = $this->getJson("/api/events/{$event->id}/availability?requested_date={$blockedDate}&quantity=1");

        $response
            ->assertOk()
            ->assertJsonPath('service_available', true)
            ->assertJsonPath('vendor_available', false)
            ->assertJsonPath('has_vendor_day_off', true)
            ->assertJsonPath('vendor_day_off_scope', 'account')
            ->assertJsonPath('message', 'Vendor is unavailable on the selected date because the account is marked as day off.');
    }

    public function test_vendor_account_day_off_marks_calendar_day_as_booked(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
        ]);

        $blockedDate = now()->addDays(15)->toDateString();

        $event = Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'House Blessing Service',
            'event_type' => 'house_blessing',
            'location' => 'Battambang',
            'starts_at' => now()->addDays(25),
            'price' => 850,
            'capacity' => 4,
            'is_active' => true,
        ]);

        VendorSetting::query()->create([
            'user_id' => $vendor->id,
            'event_id' => null,
            'timezone' => 'UTC',
            'weekly_schedule' => [],
            'blocked_dates' => [],
            'blocked_ranges' => [
                [
                    'start_date' => $blockedDate,
                    'end_date' => $blockedDate,
                    'note' => 'Vacation mode',
                ],
            ],
        ]);

        $month = substr($blockedDate, 0, 7);
        $response = $this->getJson("/api/events/{$event->id}/availability-calendar?month={$month}");

        $response->assertOk();

        $blockedDay = collect($response->json('days'))->firstWhere('date', $blockedDate);

        $this->assertNotNull($blockedDay);
        $this->assertSame('booked', $blockedDay['status']);
        $this->assertFalse($blockedDay['is_available']);
        $this->assertTrue($blockedDay['has_vendor_day_off']);
        $this->assertSame('account', $blockedDay['vendor_day_off_scope']);
    }
}
