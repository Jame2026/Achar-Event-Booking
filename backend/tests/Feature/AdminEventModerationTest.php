<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use App\Models\VendorSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEventModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_vendor_event_listing(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.com',
        ]);

        $vendor = User::factory()->create([
            'role' => 'vendor',
            'email' => 'vendor@example.com',
        ]);

        $customer = User::factory()->create([
            'role' => 'user',
            'email' => 'customer@example.com',
            'phone' => '+855882446786',
        ]);

        $event = Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'Misleading Listing',
            'event_type' => 'birthday',
            'location' => 'Phnom Penh',
            'starts_at' => now()->addDays(7),
            'price' => 250,
            'capacity' => 10,
            'is_active' => true,
        ]);

        VendorSetting::query()->create([
            'user_id' => $vendor->id,
            'event_id' => $event->id,
        ]);

        $booking = Booking::query()->create([
            'event_id' => $event->id,
            'user_id' => $customer->id,
            'quantity' => 1,
            'status' => 'confirmed',
            'payment_status' => 'confirmed',
            'customer_name' => 'Samnang',
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'service_name' => 'Misleading Listing',
            'requested_event_date' => now()->addDays(7)->toDateString(),
            'total_amount' => 250,
        ]);

        $this->deleteJson("/api/admin/events/{$event->id}?admin_user_id={$admin->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Listing deleted successfully.');

        $this->assertDatabaseMissing('events', [
            'id' => $event->id,
        ]);

        $this->assertDatabaseMissing('bookings', [
            'id' => $booking->id,
        ]);

        $this->assertDatabaseMissing('vendor_settings', [
            'user_id' => $vendor->id,
            'event_id' => $event->id,
        ]);
    }
}
