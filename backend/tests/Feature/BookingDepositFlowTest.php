<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingDepositFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_payment_marks_deposit_paid_but_keeps_booking_pending_for_vendor_review(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'email' => 'vendor@example.com',
        ]);

        $event = Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'Wedding Decor Premium',
            'event_type' => 'wedding',
            'location' => 'Phnom Penh',
            'starts_at' => now()->addDays(10),
            'price' => 1000,
            'capacity' => 5,
            'is_active' => true,
        ]);

        $createResponse = $this->postJson('/api/bookings', [
            'event_id' => $event->id,
            'quantity' => 1,
            'customer_name' => 'Dara',
            'customer_email' => 'dara@example.com',
            'service_name' => 'Wedding Decor Premium',
        ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('payment_status', 'pending')
            ->assertJsonPath('deposit_amount', '300.00')
            ->assertJsonPath('service_fee_amount', '35.00')
            ->assertJsonPath('balance_due_amount', '700.00');

        $bookingId = (int) $createResponse->json('id');
        $paymentToken = (string) $createResponse->json('payment_token');

        $confirmResponse = $this->postJson("/api/bookings/{$bookingId}/confirm-payment", [
            'payment_token' => $paymentToken,
            'payment_method' => 'aba',
        ]);

        $confirmResponse
            ->assertOk()
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('payment_status', 'confirmed')
            ->assertJsonPath('deposit_amount', '300.00')
            ->assertJsonPath('service_fee_amount', '35.00')
            ->assertJsonPath('balance_due_amount', '700.00');

        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'status' => 'pending',
            'payment_status' => 'confirmed',
            'deposit_amount' => 300,
            'service_fee_amount' => 35,
            'balance_due_amount' => 700,
        ]);
    }

    public function test_only_deposit_paid_bookings_block_the_service_date(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
        ]);
        $requestedDate = now()->addDays(8)->toDateString();

        $event = Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'Birthday Setup',
            'event_type' => 'birthday',
            'location' => 'Siem Reap',
            'starts_at' => $requestedDate.' 09:00:00',
            'price' => 800,
            'capacity' => 5,
            'is_active' => true,
        ]);

        $booking = Booking::query()->create([
            'event_id' => $event->id,
            'quantity' => 1,
            'total_amount' => 800,
            'status' => 'pending',
            'payment_status' => 'pending',
            'deposit_percent' => 30,
            'deposit_amount' => 240,
            'service_fee_amount' => 28,
            'balance_due_amount' => 560,
            'customer_name' => 'Guest User',
            'customer_email' => 'guest@example.com',
            'service_name' => 'Birthday Setup',
            'requested_event_date' => $requestedDate,
        ]);

        $unpaidAvailability = $this->getJson("/api/events/{$event->id}/availability?requested_date={$requestedDate}");

        $unpaidAvailability
            ->assertOk()
            ->assertJsonPath('service_available', true);

        $booking->update([
            'payment_status' => 'confirmed',
            'paid_at' => now(),
        ]);

        $paidAvailability = $this->getJson("/api/events/{$event->id}/availability?requested_date={$requestedDate}");

        $paidAvailability
            ->assertOk()
            ->assertJsonPath('service_available', false);
    }
}
