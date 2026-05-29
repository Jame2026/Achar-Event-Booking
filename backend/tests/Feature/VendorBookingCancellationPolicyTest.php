<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorBookingCancellationPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_cancellation_within_three_days_refunds_deposit_without_penalty(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
        ]);

        $event = Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'Engagement Planning',
            'event_type' => 'engagement',
            'location' => 'Phnom Penh',
            'starts_at' => now()->addDays(12),
            'price' => 1000,
            'capacity' => 3,
            'is_active' => true,
        ]);

        $booking = Booking::query()->create([
            'event_id' => $event->id,
            'quantity' => 1,
            'total_amount' => 1000,
            'status' => 'confirmed',
            'payment_status' => 'confirmed',
            'deposit_percent' => 30,
            'deposit_amount' => 300,
            'service_fee_amount' => 35,
            'balance_due_amount' => 700,
            'customer_name' => 'Nita',
            'customer_email' => 'nita@example.com',
            'service_name' => 'Engagement Planning',
            'requested_event_date' => now()->addDays(12)->toDateString(),
            'paid_at' => now()->subDay(),
            'vendor_cancellation_deadline_at' => now()->addDays(2),
        ]);

        $response = $this->patchJson("/api/vendor/bookings/{$booking->id}/status", [
            'vendor_user_id' => $vendor->id,
            'status' => 'cancelled',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'cancelled')
            ->assertJsonPath('payment_status', 'refunded')
            ->assertJsonPath('refund_amount', '335.00')
            ->assertJsonPath('vendor_penalty_amount', '0.00')
            ->assertJsonPath('customer_compensation_amount', '0.00')
            ->assertJsonPath('admin_compensation_amount', '0.00')
            ->assertJsonPath('cancellation_actor', 'vendor');
    }

    public function test_vendor_cancellation_after_three_days_adds_penalty_and_admin_compensation(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
        ]);

        $event = Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'Corporate Decoration',
            'event_type' => 'company_party',
            'location' => 'Battambang',
            'starts_at' => now()->addDays(15),
            'price' => 1000,
            'capacity' => 6,
            'is_active' => true,
        ]);

        $booking = Booking::query()->create([
            'event_id' => $event->id,
            'quantity' => 1,
            'total_amount' => 1000,
            'status' => 'confirmed',
            'payment_status' => 'confirmed',
            'deposit_percent' => 30,
            'deposit_amount' => 300,
            'service_fee_amount' => 35,
            'balance_due_amount' => 700,
            'customer_name' => 'Sothea',
            'customer_email' => 'sothea@example.com',
            'service_name' => 'Corporate Decoration',
            'requested_event_date' => now()->addDays(15)->toDateString(),
            'paid_at' => now()->subDays(4),
            'vendor_cancellation_deadline_at' => now()->subDay(),
        ]);

        $response = $this->patchJson("/api/vendor/bookings/{$booking->id}/status", [
            'vendor_user_id' => $vendor->id,
            'status' => 'cancelled',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'cancelled')
            ->assertJsonPath('payment_status', 'refunded')
            ->assertJsonPath('refund_amount', '335.00')
            ->assertJsonPath('vendor_penalty_amount', '150.00')
            ->assertJsonPath('customer_compensation_amount', '150.00')
            ->assertJsonPath('admin_compensation_amount', '35.00')
            ->assertJsonPath('cancellation_actor', 'vendor');
    }
}
