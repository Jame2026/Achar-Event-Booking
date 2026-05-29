<?php

namespace Tests\Feature;

use App\Models\ChatConversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChatImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_send_chat_image(): void
    {
        Storage::fake('public');

        $vendor = User::factory()->create([
            'role' => 'vendor',
            'email' => 'vendor@example.com',
        ]);

        $customer = User::factory()->create([
            'role' => 'user',
            'email' => 'customer@example.com',
        ]);

        $conversation = ChatConversation::query()->create([
            'vendor_user_id' => $vendor->id,
            'customer_user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'service_name' => 'Wedding Decor',
        ]);

        $response = $this->post('/api/user/chats/'.$conversation->id.'/messages', [
            'customer_email' => $customer->email,
            'image' => UploadedFile::fake()->create('customer-proof.png', 32, 'image/png'),
        ], [
            'Accept' => 'application/json',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.sender_role', 'customer');

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conversation->id,
            'sender_role' => 'customer',
        ]);
    }

    public function test_vendor_can_send_chat_image(): void
    {
        Storage::fake('public');

        $vendor = User::factory()->create([
            'role' => 'vendor',
            'email' => 'vendor@example.com',
        ]);

        $customer = User::factory()->create([
            'role' => 'user',
            'email' => 'customer@example.com',
        ]);

        $conversation = ChatConversation::query()->create([
            'vendor_user_id' => $vendor->id,
            'customer_user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'service_name' => 'Wedding Decor',
        ]);

        $response = $this->post('/api/vendor/chats/'.$conversation->id.'/messages', [
            'vendor_user_id' => $vendor->id,
            'image' => UploadedFile::fake()->create('vendor-proof.png', 32, 'image/png'),
        ], [
            'Accept' => 'application/json',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.sender_role', 'vendor');

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conversation->id,
            'sender_role' => 'vendor',
        ]);
    }
}
