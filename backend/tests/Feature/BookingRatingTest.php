<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingRatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_rate_completed_confirmed_booking(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'name' => 'Golden Vendor',
        ]);

        $customer = User::factory()->create([
            'role' => 'user',
            'email' => 'customer@example.com',
        ]);

        $event = Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'Wedding Decor',
            'event_type' => 'wedding',
            'location' => 'Phnom Penh',
            'starts_at' => now()->subDays(3),
            'price' => 1200,
            'capacity' => 10,
            'is_active' => true,
        ]);

        $booking = Booking::query()->create([
            'event_id' => $event->id,
            'user_id' => $customer->id,
            'quantity' => 1,
            'total_amount' => 1200,
            'status' => 'confirmed',
            'customer_name' => 'Booked Customer',
            'customer_email' => 'customer@example.com',
            'service_name' => 'Wedding Decor',
            'requested_event_date' => now()->subDays(3)->toDateString(),
        ]);

        $response = $this->patchJson("/api/user/bookings/{$booking->id}/rating", [
            'customer_email' => 'customer@example.com',
            'rating' => 5,
            'review' => 'Very professional and on time.',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('rating.rating', 5)
            ->assertJsonPath('rating.review', 'Very professional and on time.');

        $this->assertDatabaseHas('booking_ratings', [
            'booking_id' => $booking->id,
            'event_id' => $event->id,
            'vendor_id' => $vendor->id,
            'rating' => 5,
        ]);

        $eventResponse = $this->getJson('/api/events?include_inactive=1&per_page=100');

        $eventResponse
            ->assertOk()
            ->assertJsonPath('data.0.id', $event->id)
            ->assertJsonPath('data.0.rating_average', 5)
            ->assertJsonPath('data.0.ratings_count', 1);
    }

    public function test_customer_can_rate_cancelled_booking(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'name' => 'Cancelled Vendor',
        ]);

        $event = Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'Birthday Sound System',
            'event_type' => 'birthday',
            'location' => 'Siem Reap',
            'starts_at' => now()->addWeek(),
            'price' => 500,
            'capacity' => 5,
            'is_active' => true,
        ]);

        $booking = Booking::query()->create([
            'event_id' => $event->id,
            'quantity' => 1,
            'total_amount' => 500,
            'status' => 'cancelled',
            'customer_name' => 'Cancelled Customer',
            'customer_email' => 'cancelled@example.com',
            'service_name' => 'Birthday Sound System',
            'requested_event_date' => now()->addWeek()->toDateString(),
        ]);

        $this->patchJson("/api/user/bookings/{$booking->id}/rating", [
            'customer_email' => 'cancelled@example.com',
            'rating' => 3,
            'review' => 'Booking was cancelled but communication was clear.',
        ])
            ->assertOk()
            ->assertJsonPath('rating.rating', 3);
    }
}
