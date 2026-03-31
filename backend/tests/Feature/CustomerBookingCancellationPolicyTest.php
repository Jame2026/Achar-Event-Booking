<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerBookingCancellationPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_cancel_with_full_refund_within_first_three_days_and_chat_notice_is_created(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'email' => 'vendor@example.com',
        ]);

        $customer = User::factory()->create([
            'role' => 'user',
            'email' => 'customer@example.com',
            'phone' => '+85512345678',
        ]);

        $event = Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'Monk Ceremony',
            'event_type' => 'monk_ceremony',
            'location' => 'Phnom Penh',
            'starts_at' => now()->addDays(10),
            'price' => 500,
            'capacity' => 5,
            'is_active' => true,
        ]);

        $booking = Booking::query()->create([
            'event_id' => $event->id,
            'user_id' => $customer->id,
            'quantity' => 1,
            'status' => 'pending',
            'payment_status' => 'confirmed',
            'deposit_percent' => 30,
            'deposit_amount' => 150,
            'service_fee_amount' => 17.50,
            'balance_due_amount' => 350,
            'customer_name' => 'Customer One',
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'service_name' => 'Monk Ceremony',
            'requested_event_date' => now()->addDays(10)->toDateString(),
            'total_amount' => 500,
            'paid_at' => now()->subDay(),
            'vendor_cancellation_deadline_at' => now()->addDays(2),
        ]);

        $response = $this
            ->actingAs($customer)
            ->patchJson("/api/user/bookings/{$booking->id}/cancel");

        $response
            ->assertOk()
            ->assertJsonPath('status', 'cancelled')
            ->assertJsonPath('payment_status', 'refunded')
            ->assertJsonPath('refund_amount', '167.50')
            ->assertJsonPath('cancellation_actor', 'customer');

        $conversation = ChatConversation::query()
            ->where('vendor_user_id', $vendor->id)
            ->where('customer_email', $customer->email)
            ->whereNull('booking_id')
            ->first();

        $this->assertNotNull($conversation);
        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conversation->id,
            'sender_role' => 'customer',
        ]);

        $message = ChatMessage::query()->where('conversation_id', $conversation->id)->latest('id')->first();
        $this->assertStringContainsString('Cancellation request sent for Monk Ceremony', (string) $message?->body);
    }

    public function test_customer_late_cancellation_only_refunds_fifteen_percent_of_initial_payment(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'email' => 'vendor@example.com',
        ]);

        $customer = User::factory()->create([
            'role' => 'user',
            'email' => 'customer@example.com',
            'phone' => '+85512345678',
        ]);

        $event = Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'Wedding Decor',
            'event_type' => 'wedding',
            'location' => 'Battambang',
            'starts_at' => now()->addDays(14),
            'price' => 1000,
            'capacity' => 5,
            'is_active' => true,
        ]);

        $booking = Booking::query()->create([
            'event_id' => $event->id,
            'user_id' => $customer->id,
            'quantity' => 1,
            'status' => 'confirmed',
            'payment_status' => 'confirmed',
            'deposit_percent' => 30,
            'deposit_amount' => 300,
            'service_fee_amount' => 35,
            'balance_due_amount' => 700,
            'customer_name' => 'Customer One',
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'service_name' => 'Wedding Decor',
            'requested_event_date' => now()->addDays(14)->toDateString(),
            'total_amount' => 1000,
            'paid_at' => now()->subDays(5),
            'vendor_cancellation_deadline_at' => now()->subDay(),
        ]);

        $response = $this
            ->actingAs($customer)
            ->patchJson("/api/user/bookings/{$booking->id}/cancel");

        $response
            ->assertOk()
            ->assertJsonPath('status', 'cancelled')
            ->assertJsonPath('payment_status', 'refunded')
            ->assertJsonPath('refund_amount', '50.25')
            ->assertJsonPath('cancellation_actor', 'customer');
    }
}
