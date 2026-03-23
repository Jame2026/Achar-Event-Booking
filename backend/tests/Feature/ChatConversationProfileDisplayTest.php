<?php

namespace Tests\Feature;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatConversationProfileDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_chat_index_prefers_customer_profile_name_and_image(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'name' => 'Vendor One',
            'email' => 'vendor@example.com',
        ]);

        $customer = User::factory()->create([
            'name' => 'Chem Profile',
            'email' => 'chem@example.com',
            'phone' => '+855882446786',
            'location' => 'Battambang',
            'profile_image_url' => 'https://example.com/profiles/chem.jpg',
        ]);

        $conversation = ChatConversation::query()->create([
            'vendor_user_id' => $vendor->id,
            'customer_user_id' => $customer->id,
            'customer_name' => 'Old Booking Name',
            'customer_email' => $customer->email,
            'service_name' => 'Whole Wedding Controll',
        ]);

        ChatMessage::query()->create([
            'conversation_id' => $conversation->id,
            'sender_user_id' => $customer->id,
            'sender_role' => 'customer',
            'sender_name' => 'Old Booking Name',
            'body' => 'hiii',
        ]);

        $response = $this->getJson('/api/vendor/chats?vendor_user_id='.$vendor->id);

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.customer_name', 'Chem Profile')
            ->assertJsonPath('data.0.customer_image_url', 'https://example.com/profiles/chem.jpg')
            ->assertJsonPath('data.0.customer_phone', '+855882446786')
            ->assertJsonPath('data.0.customer_location', 'Battambang');
    }

    public function test_customer_messages_use_current_profile_name(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'name' => 'Vendor One',
            'email' => 'vendor@example.com',
        ]);

        $customer = User::factory()->create([
            'name' => 'Chem Profile',
            'email' => 'chem@example.com',
        ]);

        $conversation = ChatConversation::query()->create([
            'vendor_user_id' => $vendor->id,
            'customer_user_id' => $customer->id,
            'customer_name' => 'Old Booking Name',
            'customer_email' => $customer->email,
            'service_name' => 'Wedding Decor',
        ]);

        $response = $this->postJson("/api/user/chats/{$conversation->id}/messages", [
            'customer_email' => $customer->email,
            'body' => 'Hello vendor',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.sender_name', 'Chem Profile');

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conversation->id,
            'sender_role' => 'customer',
            'sender_name' => 'Chem Profile',
            'body' => 'Hello vendor',
        ]);
    }
}
