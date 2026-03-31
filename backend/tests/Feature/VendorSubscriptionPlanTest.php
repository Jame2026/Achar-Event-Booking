<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Models\VendorSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class VendorSubscriptionPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_registration_requires_a_plan_and_creates_pending_checkout(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-01 09:00:00'));

        $missingPlanResponse = $this->postJson('/api/register', [
            'name' => 'Vendor Owner',
            'email' => 'vendor-owner@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'vendor',
        ]);

        $missingPlanResponse
            ->assertStatus(422)
            ->assertJsonValidationErrors(['vendor_plan']);

        $response = $this->postJson('/api/register', [
            'name' => 'Vendor Owner',
            'email' => 'vendor-owner@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'vendor',
            'vendor_plan' => VendorSetting::PLAN_QUARTERLY,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('user.role', 'vendor')
            ->assertJsonPath('subscription_checkout.code', VendorSetting::PLAN_QUARTERLY)
            ->assertJsonPath('subscription_checkout.price', 10)
            ->assertJsonPath('subscription_checkout.service_limit', 3)
            ->assertJsonPath('subscription_checkout.package_limit', 1)
            ->assertJsonPath('subscription_checkout.payment_qr_url', '/ABAqr.png');

        $vendor = User::query()->where('email', 'vendor-owner@example.com')->firstOrFail();
        $settings = VendorSetting::query()
            ->where('user_id', $vendor->id)
            ->whereNull('event_id')
            ->firstOrFail();

        $this->assertSame(VendorSetting::PLAN_QUARTERLY, $settings->subscription_plan_code);
        $this->assertSame('pending_payment', $settings->subscription_status);
        $this->assertSame(10.0, (float) $settings->subscription_price_amount);
        $this->assertSame(3, (int) $settings->subscription_duration_months);
        $this->assertSame(3, (int) $settings->subscription_service_limit);
        $this->assertSame(1, (int) $settings->subscription_package_limit);
        $this->assertNull($settings->subscription_started_at);
        $this->assertNull($settings->subscription_expires_at);

        Carbon::setTestNow();
    }

    public function test_admin_can_activate_a_pending_vendor_subscription(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-01 09:00:00'));

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'email' => 'pending-vendor@example.com',
        ]);

        VendorSetting::query()->create([
            'user_id' => $vendor->id,
            'event_id' => null,
            ...VendorSetting::defaultAvailabilityAttributes(),
            ...VendorSetting::pendingSubscriptionPayloadForPlan(VendorSetting::PLAN_QUARTERLY),
        ]);

        $this->postJson('/api/vendor/settings/subscription/complete-payment', [
            'vendor_user_id' => $vendor->id,
        ])->assertOk()
            ->assertJsonPath('settings.subscription_status', 'pending_approval');

        $response = $this->postJson("/api/admin/users/{$vendor->id}/activate-vendor-subscription", [
            'admin_user_id' => $admin->id,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('user.vendor_setting.subscription_status', 'active');

        $settings = VendorSetting::query()
            ->where('user_id', $vendor->id)
            ->whereNull('event_id')
            ->firstOrFail();

        $this->assertTrue($settings->subscriptionIsActive());
        $this->assertTrue(
            $settings->subscription_expires_at->equalTo(Carbon::parse('2026-07-01 09:00:00'))
        );

        Carbon::setTestNow();
    }

    public function test_admin_cannot_activate_vendor_before_payment_is_completed(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $vendor = User::factory()->create([
            'role' => 'vendor',
        ]);

        VendorSetting::query()->create([
            'user_id' => $vendor->id,
            'event_id' => null,
            ...VendorSetting::defaultAvailabilityAttributes(),
            ...VendorSetting::pendingSubscriptionPayloadForPlan(VendorSetting::PLAN_QUARTERLY),
        ]);

        $this->actingAs($admin)
            ->postJson("/api/admin/users/{$vendor->id}/activate-vendor-subscription")
            ->assertStatus(422)
            ->assertJsonPath('message', 'This vendor has not clicked Complete Payment yet.');
    }

    public function test_vendor_cannot_publish_services_after_plan_expiry(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-15 11:30:00'));

        $vendor = User::factory()->create([
            'role' => 'vendor',
            'email' => 'expired-vendor@example.com',
        ]);

        VendorSetting::query()->create([
            'user_id' => $vendor->id,
            'event_id' => null,
            ...VendorSetting::defaultAvailabilityAttributes(),
            ...VendorSetting::subscriptionPayloadForPlan(
                VendorSetting::PLAN_QUARTERLY,
                Carbon::parse('2026-04-01 09:00:00')
            ),
        ]);

        $response = $this->postJson('/api/vendor/services', [
            'vendor_user_id' => $vendor->id,
            'title' => 'Wedding Decor',
            'event_type' => 'wedding',
            'location' => 'Phnom Penh',
            'starts_at' => '2026-09-01 10:00:00',
            'price' => 500,
            'capacity' => 5,
            'is_active' => true,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('message', 'Your vendor listing plan expired on Jul 01, 2026 9:00 AM. Renew it before publishing services again.');

        $this->assertDatabaseCount('events', 0);

        Carbon::setTestNow();
    }

    public function test_quarterly_plan_limits_vendor_to_three_services_and_one_package(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-01 09:00:00'));

        $vendor = User::factory()->create([
            'role' => 'vendor',
            'email' => 'limited-vendor@example.com',
        ]);

        VendorSetting::query()->create([
            'user_id' => $vendor->id,
            'event_id' => null,
            ...VendorSetting::defaultAvailabilityAttributes(),
            ...VendorSetting::subscriptionPayloadForPlan(
                VendorSetting::PLAN_QUARTERLY,
                Carbon::parse('2026-04-01 09:00:00')
            ),
        ]);

        foreach (range(1, 3) as $index) {
            $this->postJson('/api/vendor/services', [
                'vendor_user_id' => $vendor->id,
                'title' => "Service {$index}",
                'event_type' => 'wedding',
                'location' => 'Phnom Penh',
                'starts_at' => "2026-05-0{$index} 10:00:00",
                'price' => 100 * $index,
                'capacity' => 10,
                'service_mode' => 'overall',
                'is_active' => true,
            ])->assertCreated();
        }

        $this->postJson('/api/vendor/services', [
            'vendor_user_id' => $vendor->id,
            'title' => 'Service 4',
            'event_type' => 'wedding',
            'location' => 'Phnom Penh',
            'starts_at' => '2026-05-09 10:00:00',
            'price' => 450,
            'capacity' => 10,
            'service_mode' => 'overall',
            'is_active' => true,
        ])->assertStatus(422)
            ->assertJsonPath('message', 'Your current plan allows only 3 service listings. Upgrade or remove an existing service before adding another one.');

        $this->postJson('/api/vendor/services', [
            'vendor_user_id' => $vendor->id,
            'title' => 'Starter Package',
            'event_type' => 'wedding',
            'location' => 'Siem Reap',
            'starts_at' => '2026-05-10 10:00:00',
            'price' => 0,
            'capacity' => 5,
            'service_mode' => 'package',
            'packages' => [
                [
                    'name' => 'Package A',
                    'price' => 250,
                    'details' => 'Flowers and lights',
                ],
            ],
            'is_active' => true,
        ])->assertCreated();

        $this->postJson('/api/vendor/services', [
            'vendor_user_id' => $vendor->id,
            'title' => 'Second Package',
            'event_type' => 'wedding',
            'location' => 'Siem Reap',
            'starts_at' => '2026-05-11 10:00:00',
            'price' => 0,
            'capacity' => 5,
            'service_mode' => 'package',
            'packages' => [
                [
                    'name' => 'Package B',
                    'price' => 350,
                    'details' => 'Food and music',
                ],
            ],
            'is_active' => true,
        ])->assertStatus(422)
            ->assertJsonPath('message', 'Your current plan allows only 1 package listing. Upgrade or remove an existing package before adding another one.');

        $this->assertDatabaseCount('events', 4);

        Carbon::setTestNow();
    }

    public function test_vendor_cannot_publish_services_until_admin_approves_the_account(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'email' => 'awaiting-approval@example.com',
        ]);

        VendorSetting::query()->create([
            'user_id' => $vendor->id,
            'event_id' => null,
            ...VendorSetting::defaultAvailabilityAttributes(),
            ...VendorSetting::pendingSubscriptionPayloadForPlan(VendorSetting::PLAN_QUARTERLY),
        ]);

        $this->postJson('/api/vendor/settings/subscription/complete-payment', [
            'vendor_user_id' => $vendor->id,
        ])->assertOk();

        $response = $this->postJson('/api/vendor/services', [
            'vendor_user_id' => $vendor->id,
            'title' => 'Approval Pending Service',
            'event_type' => 'wedding',
            'location' => 'Phnom Penh',
            'starts_at' => '2026-05-01 10:00:00',
            'price' => 300,
            'capacity' => 5,
            'is_active' => true,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('message', 'Your vendor plan payment was submitted and is waiting for admin approval.');
    }

    public function test_public_event_index_hides_expired_vendor_services(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-15 11:30:00'));

        $expiredVendor = User::factory()->create([
            'role' => 'vendor',
            'email' => 'expired-marketplace@example.com',
        ]);
        $activeVendor = User::factory()->create([
            'role' => 'vendor',
            'email' => 'active-marketplace@example.com',
        ]);

        VendorSetting::query()->create([
            'user_id' => $expiredVendor->id,
            'event_id' => null,
            ...VendorSetting::defaultAvailabilityAttributes(),
            ...VendorSetting::subscriptionPayloadForPlan(
                VendorSetting::PLAN_QUARTERLY,
                Carbon::parse('2026-04-01 09:00:00')
            ),
        ]);
        VendorSetting::query()->create([
            'user_id' => $activeVendor->id,
            'event_id' => null,
            ...VendorSetting::defaultAvailabilityAttributes(),
            ...VendorSetting::subscriptionPayloadForPlan(
                VendorSetting::PLAN_ANNUAL,
                Carbon::parse('2026-08-01 09:00:00')
            ),
        ]);

        $expiredEvent = Event::query()->create([
            'vendor_id' => $expiredVendor->id,
            'title' => 'Expired Service',
            'event_type' => 'wedding',
            'location' => 'Phnom Penh',
            'starts_at' => Carbon::parse('2026-09-02 10:00:00'),
            'price' => 200,
            'capacity' => 10,
            'is_active' => true,
        ]);
        $activeEvent = Event::query()->create([
            'vendor_id' => $activeVendor->id,
            'title' => 'Active Service',
            'event_type' => 'wedding',
            'location' => 'Siem Reap',
            'starts_at' => Carbon::parse('2026-09-04 10:00:00'),
            'price' => 400,
            'capacity' => 8,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/events');

        $response->assertOk();
        $eventIds = collect($response->json('data'))->pluck('id')->all();

        $this->assertNotContains($expiredEvent->id, $eventIds);
        $this->assertContains($activeEvent->id, $eventIds);

        $this->getJson("/api/events/{$expiredEvent->id}")
            ->assertStatus(404);

        Carbon::setTestNow();
    }
}
