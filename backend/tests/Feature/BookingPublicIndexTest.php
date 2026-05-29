<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingPublicIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_bookings_can_be_filtered_by_customer_phone(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'phone' => '+85599888777',
        ]);

        $customer = User::factory()->create([
            'email' => 'phone-login@example.com',
            'phone' => '+85512345678',
        ]);

        $event = Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'Wedding Decor',
            'event_type' => 'wedding',
            'location' => 'Phnom Penh',
            'starts_at' => now()->addWeek(),
            'price' => 1200,
            'capacity' => 10,
            'is_active' => true,
        ]);

        $matchedBooking = Booking::query()->create([
            'event_id' => $event->id,
            'user_id' => $customer->id,
            'quantity' => 1,
            'total_amount' => 1200,
            'status' => 'pending',
            'customer_name' => 'Phone Login User',
            'customer_email' => 'backup-contact@example.com',
            'customer_phone' => '+85512345678',
            'service_name' => 'Wedding Decor',
        ]);

        Booking::query()->create([
            'event_id' => $event->id,
            'quantity' => 1,
            'total_amount' => 900,
            'status' => 'pending',
            'customer_name' => 'Another User',
            'customer_email' => 'another@example.com',
            'customer_phone' => '+85570111222',
            'service_name' => 'Wedding Decor',
        ]);

        $response = $this->getJson('/api/bookings?customer_phone=012345678');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchedBooking->id);
    }

    public function test_public_bookings_can_be_filtered_by_related_user_email(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'phone' => '+85599888777',
        ]);

        $customer = User::factory()->create([
            'email' => 'planner@example.com',
            'phone' => '+85512345678',
        ]);

        $event = Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'Corporate Setup',
            'event_type' => 'company_party',
            'location' => 'Siem Reap',
            'starts_at' => now()->addDays(10),
            'price' => 1800,
            'capacity' => 25,
            'is_active' => true,
        ]);

        $matchedBooking = Booking::query()->create([
            'event_id' => $event->id,
            'user_id' => $customer->id,
            'quantity' => 2,
            'total_amount' => 3600,
            'status' => 'confirmed',
            'customer_name' => 'Planner Account',
            'customer_email' => 'checkout-copy@example.com',
            'service_name' => 'Corporate Setup',
        ]);

        Booking::query()->create([
            'event_id' => $event->id,
            'quantity' => 1,
            'total_amount' => 1400,
            'status' => 'pending',
            'customer_name' => 'Guest User',
            'customer_email' => 'guest@example.com',
            'service_name' => 'Corporate Setup',
        ]);

        $response = $this->getJson('/api/bookings?customer_email=planner@example.com');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchedBooking->id);
    }

    public function test_public_booking_index_returns_customer_bookings_with_vendor_service_details(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'name' => 'Golden Vendor',
            'profile_image_url' => 'https://example.com/vendor.jpg',
        ]);

        $customer = User::factory()->create([
            'role' => 'user',
            'name' => 'Customer One',
            'email' => 'customer-one@example.com',
        ]);

        $event = Event::create([
            'vendor_id' => $vendor->id,
            'title' => 'Birthday Celebration Package',
            'event_type' => 'birthday',
            'description' => 'Birthday package.',
            'service_mode' => 'package',
            'location' => 'Phnom Penh',
            'starts_at' => now()->addDays(5)->toDateTimeString(),
            'price' => 300,
            'capacity' => 50,
            'is_active' => true,
        ]);

        $booking = Booking::create([
            'event_id' => $event->id,
            'user_id' => $customer->id,
            'quantity' => 2,
            'total_amount' => 600,
            'status' => 'confirmed',
            'customer_name' => 'Customer One',
            'customer_email' => 'customer-one@example.com',
            'customer_phone' => '+85512345678',
            'customer_location' => 'Sen Sok',
            'service_name' => 'Premium Birthday Package',
            'requested_event_date' => now()->addDays(5)->toDateString(),
            'booked_items' => [
                ['name' => 'Premium Birthday Package'],
                ['name' => 'Balloon Backdrop'],
            ],
        ]);

        $response = $this->getJson('/api/bookings?per_page=100');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $booking->id)
            ->assertJsonPath('data.0.customer_name', 'Customer One')
            ->assertJsonPath('data.0.service_name', 'Premium Birthday Package')
            ->assertJsonPath('data.0.event.title', 'Birthday Celebration Package')
            ->assertJsonPath('data.0.event.vendor.name', 'Golden Vendor')
            ->assertJsonPath('data.0.event.vendor.profile_image_url', 'https://example.com/vendor.jpg');
    }
}
