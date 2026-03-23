<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_can_be_created_with_phone_only(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'phone' => '+85599888777',
        ]);

        $event = Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'Family Party Setup',
            'event_type' => 'family_party',
            'location' => 'Battambang',
            'starts_at' => now()->addDays(5),
            'price' => 1000,
            'capacity' => 5,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/bookings', [
            'event_id' => $event->id,
            'quantity' => 1,
            'customer_name' => 'Chem',
            'customer_phone' => '+855882446786',
            'service_name' => 'Family Party Setup',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('customer_email', null)
            ->assertJsonPath('customer_phone', '+855882446786');

        $this->assertDatabaseHas('bookings', [
            'event_id' => $event->id,
            'customer_name' => 'Chem',
            'customer_email' => null,
            'customer_phone' => '+855882446786',
        ]);
    }

    public function test_event_booking_keeps_customized_total_and_items(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
        ]);

        $event = Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'Wedding Package',
            'event_type' => 'wedding',
            'location' => 'Phnom Penh',
            'starts_at' => now()->addDays(7),
            'price' => 1000,
            'capacity' => 10,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/bookings', [
            'event_id' => $event->id,
            'quantity' => 2,
            'customer_name' => 'Dara',
            'customer_email' => 'dara@example.com',
            'service_name' => 'Wedding Package',
            'total_amount' => 2691.50,
            'booked_items' => [
                [
                    'type' => 'package',
                    'name' => 'Wedding Package',
                    'qty' => 2,
                    'unitPrice' => 1000,
                    'totalPrice' => 2000,
                ],
                [
                    'type' => 'service',
                    'name' => 'Lighting Upgrade',
                    'qty' => 2,
                    'unitPrice' => 300,
                    'totalPrice' => 600,
                ],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('total_amount', '2691.50')
            ->assertJsonPath('booked_items.1.name', 'Lighting Upgrade');

        $this->assertDatabaseHas('bookings', [
            'event_id' => $event->id,
            'customer_name' => 'Dara',
            'total_amount' => 2691.5,
        ]);
    }

    public function test_package_event_booking_uses_sum_of_included_service_prices(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
        ]);

        $event = Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'Birthday Package',
            'event_type' => 'birthday',
            'location' => 'Siem Reap',
            'starts_at' => now()->addDays(4),
            'service_mode' => 'package',
            'packages' => [
                ['name' => 'Backdrop', 'price' => 90, 'details' => 'Theme backdrop'],
                ['name' => 'Balloons', 'price' => 60, 'details' => 'Balloon setup'],
            ],
            'price' => 0,
            'capacity' => 5,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/bookings', [
            'event_id' => $event->id,
            'quantity' => 2,
            'customer_name' => 'Sokha',
            'customer_email' => 'sokha@example.com',
            'service_name' => 'Birthday Package',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('total_amount', '300.00');

        $this->assertDatabaseHas('bookings', [
            'event_id' => $event->id,
            'customer_name' => 'Sokha',
            'total_amount' => 300,
        ]);
    }
}
