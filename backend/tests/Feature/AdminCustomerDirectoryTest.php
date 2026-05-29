<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCustomerDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_directory_returns_customers_with_booking_history_for_admin_request(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $vendor = User::factory()->create([
            'role' => 'vendor',
            'name' => 'Elite Gatherings',
            'email' => 'vendor@example.com',
        ]);

        $customer = User::factory()->create([
            'role' => 'user',
            'name' => 'Samnang Sim',
            'email' => 'samnang@example.com',
            'phone' => '+85512345678',
            'location' => 'Phnom Penh',
            'profile_image_url' => 'https://example.com/customer.jpg',
        ]);

        User::factory()->create([
            'role' => 'vendor',
            'email' => 'other-vendor@example.com',
        ]);

        $event = Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'Birthday Celebration',
            'event_type' => 'birthday',
            'location' => 'Phnom Penh',
            'starts_at' => now()->addWeek(),
            'price' => 500,
            'capacity' => 30,
            'is_active' => true,
            'service_mode' => 'overall',
        ]);

        Booking::query()->create([
            'event_id' => $event->id,
            'user_id' => $customer->id,
            'quantity' => 1,
            'total_amount' => 500,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'customer_location' => $customer->location,
            'service_name' => 'Birthday Celebration',
            'requested_event_date' => now()->addWeek()->toDateString(),
            'booked_items' => [['name' => 'Stage Setup', 'price' => 500]],
        ]);

        Booking::query()->create([
            'event_id' => $event->id,
            'user_id' => $customer->id,
            'quantity' => 2,
            'total_amount' => 1000,
            'status' => 'pending',
            'payment_status' => 'pending',
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'customer_location' => $customer->location,
            'service_name' => 'Birthday Celebration',
            'requested_event_date' => now()->addDays(10)->toDateString(),
            'booked_items' => [['name' => 'Balloon Decor', 'price' => 1000]],
        ]);

        $response = $this->getJson("/api/admin/customer-directory?admin_user_id={$admin->id}&per_page=100");

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $customer->id)
            ->assertJsonPath('data.0.email', 'samnang@example.com')
            ->assertJsonPath('data.0.bookings_count', 2)
            ->assertJsonPath('data.0.confirmed_bookings_count', 1)
            ->assertJsonPath('data.0.pending_bookings_count', 1)
            ->assertJsonPath('data.0.cancelled_bookings_count', 0)
            ->assertJsonPath('data.0.bookings.0.event.title', 'Birthday Celebration')
            ->assertJsonPath('data.0.bookings.0.event.vendor.name', 'Elite Gatherings');
    }

    public function test_customer_directory_rejects_non_admin_request_context(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $this->getJson("/api/admin/customer-directory?admin_user_id={$user->id}")
            ->assertForbidden()
            ->assertJson([
                'message' => 'Admin account not found.',
            ]);
    }
}
