<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerCompletedBookingDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_delete_completed_booking(): void
    {
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
            'title' => 'Wedding Whole',
            'event_type' => 'wedding',
            'location' => 'Phnom Penh',
            'starts_at' => now()->subDay(),
            'price' => 200,
            'capacity' => 5,
            'is_active' => true,
        ]);

        $booking = Booking::query()->create([
            'event_id' => $event->id,
            'user_id' => $customer->id,
            'quantity' => 1,
            'status' => 'confirmed',
            'payment_status' => 'confirmed',
            'customer_name' => 'Chem Khoeurn',
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'service_name' => 'Wedding Whole',
            'requested_event_date' => now()->subDay()->toDateString(),
            'total_amount' => 200,
        ]);

        $response = $this
            ->actingAs($customer)
            ->deleteJson("/api/user/bookings/{$booking->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('bookings', [
            'id' => $booking->id,
        ]);
    }
}
