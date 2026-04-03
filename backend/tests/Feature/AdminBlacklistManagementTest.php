<?php

namespace Tests\Feature;

use App\Models\BlacklistedIdentity;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBlacklistManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_vendor_and_blacklist_contact_details(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.com',
        ]);

        $vendor = User::factory()->create([
            'role' => 'vendor',
            'name' => 'Fraud Vendor',
            'email' => 'fraud-vendor@example.com',
            'phone' => '+85512345678',
        ]);

        $event = Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'Fake Wedding Service',
            'event_type' => 'wedding',
            'location' => 'Phnom Penh',
            'starts_at' => now()->addWeek(),
            'price' => 1200,
            'capacity' => 20,
            'is_active' => true,
        ]);

        $response = $this->postJson("/api/admin/users/{$vendor->id}/delete-with-blacklist", [
            'admin_user_id' => $admin->id,
            'reason' => 'Vendor cheated customers and shared false pricing.',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('blacklist.subject_role', 'vendor')
            ->assertJsonPath('blacklist.blocked_email', 'fraud-vendor@example.com')
            ->assertJsonPath('blacklist.blocked_phone', '+85512345678');

        $this->assertDatabaseMissing('users', [
            'id' => $vendor->id,
        ]);

        $this->assertDatabaseHas('blacklisted_identities', [
            'subject_role' => 'vendor',
            'blocked_email' => 'fraud-vendor@example.com',
            'blocked_phone' => '+85512345678',
            'approved_at' => null,
        ]);

        $event->refresh();

        $this->assertNull($event->vendor_id);
        $this->assertFalse((bool) $event->is_active);

        $this->getJson("/api/admin/blacklist?admin_user_id={$admin->id}&role=vendor")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.subject_name', 'Fraud Vendor');
    }

    public function test_blacklisted_customer_cannot_register_or_book_until_admin_approves(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.com',
        ]);

        $customer = User::factory()->create([
            'role' => 'user',
            'name' => 'Cheat Customer',
            'email' => 'cheat-customer@example.com',
            'phone' => '+855882446786',
        ]);

        $vendor = User::factory()->create([
            'role' => 'vendor',
            'email' => 'trusted-vendor@example.com',
        ]);

        $event = Event::query()->create([
            'vendor_id' => $vendor->id,
            'title' => 'Trusted Decor',
            'event_type' => 'birthday',
            'location' => 'Siem Reap',
            'starts_at' => now()->addDays(10),
            'price' => 500,
            'capacity' => 5,
            'is_active' => true,
        ]);

        $this->postJson("/api/admin/users/{$customer->id}/delete-with-blacklist", [
            'admin_user_id' => $admin->id,
            'reason' => 'Customer used stolen payment details.',
        ])->assertOk();

        $entry = BlacklistedIdentity::query()->firstOrFail();

        $this->postJson('/api/register', [
            'name' => 'Retry User',
            'email' => 'retry-user@example.com',
            'phone' => '+855882446786',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'user',
        ])->assertStatus(422)
            ->assertJsonPath('message', 'This email or phone number has been blacklisted. Please contact the admin for approval.');

        $this->postJson('/api/bookings', [
            'event_id' => $event->id,
            'quantity' => 1,
            'customer_name' => 'Retry User',
            'customer_phone' => '+855882446786',
            'service_name' => 'Trusted Decor',
        ])->assertStatus(422)
            ->assertJsonPath('message', 'This email or phone number has been blacklisted. Please contact the admin for approval.');

        $this->patchJson("/api/admin/blacklist/{$entry->id}/approve", [
            'admin_user_id' => $admin->id,
        ])->assertOk()
            ->assertJsonPath('message', 'This email or phone number can be reused again.');

        $this->postJson('/api/register', [
            'name' => 'Retry User',
            'email' => 'retry-user@example.com',
            'phone' => '+855882446786',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'user',
        ])->assertCreated()
            ->assertJsonPath('user.phone', '+855882446786');
    }
}
