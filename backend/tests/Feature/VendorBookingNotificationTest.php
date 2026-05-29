<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingNotification;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorBookingNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_receives_notification_when_vendor_confirms_booking(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'email' => 'vendor@example.com',
        ]);

        $customer = User::factory()->create([
            'role' => 'user',
            'email' => 'customer@example.com',
        ]);

        $event = Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'Wedding Package',
            'event_type' => 'wedding',
            'location' => 'Phnom Penh',
            'starts_at' => now()->addDays(7),
            'price' => 1200,
            'capacity' => 10,
            'is_active' => true,
        ]);

        $booking = Booking::query()->create([
            'event_id' => $event->id,
            'user_id' => $customer->id,
            'quantity' => 1,
            'status' => 'pending',
            'payment_status' => 'pending',
            'customer_name' => 'Chem Khoeurn',
            'customer_email' => $customer->email,
            'service_name' => 'Wedding Package',
            'requested_event_date' => now()->addDays(7)->toDateString(),
            'total_amount' => 2050,
        ]);

        $response = $this->patchJson("/api/vendor/bookings/{$booking->id}/status", [
            'vendor_user_id' => $vendor->id,
            'status' => 'confirmed',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'confirmed');

        $this->assertDatabaseHas('booking_notifications', [
            'booking_id' => $booking->id,
            'recipient_type' => 'user',
            'recipient_user_id' => $customer->id,
            'recipient_email' => $customer->email,
            'kind' => 'booking_status_changed',
            'title' => 'Booking Confirmed',
        ]);

        $notificationsResponse = $this->getJson('/api/notifications/bookings?role=user&user_id='.$customer->id);

        $notificationsResponse
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('data.0.title', 'Booking Confirmed')
            ->assertJsonPath('data.0.kind', 'booking_status_changed');
    }

    public function test_notifications_can_be_loaded_by_email_when_user_id_is_stale(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'email' => 'vendor@example.com',
        ]);

        $customer = User::factory()->create([
            'role' => 'user',
            'email' => 'customer@example.com',
        ]);

        $event = Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'Wedding Package',
            'event_type' => 'wedding',
            'location' => 'Phnom Penh',
            'starts_at' => now()->addDays(7),
            'price' => 1200,
            'capacity' => 10,
            'is_active' => true,
        ]);

        $booking = Booking::query()->create([
            'event_id' => $event->id,
            'user_id' => $customer->id,
            'quantity' => 1,
            'status' => 'pending',
            'payment_status' => 'pending',
            'customer_name' => 'Chem Khoeurn',
            'customer_email' => $customer->email,
            'service_name' => 'Wedding Package',
            'requested_event_date' => now()->addDays(7)->toDateString(),
            'total_amount' => 2050,
        ]);

        BookingNotification::query()->create([
            'booking_id' => $booking->id,
            'recipient_type' => 'user',
            'recipient_user_id' => $customer->id,
            'recipient_email' => $customer->email,
            'kind' => 'booking_created',
            'title' => 'Booking request received',
            'message' => 'Your booking request has been received.',
            'is_read' => false,
        ]);

        $response = $this->getJson('/api/notifications/bookings?role=user&user_id=999999&email='.$customer->email);

        $response
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('data.0.title', 'Booking request received')
            ->assertJsonPath('data.0.kind', 'booking_created');
    }
}
