<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class VendorSetting extends Model
{
    use HasFactory;

    public const PLAN_QUARTERLY = 'quarterly';
    public const PLAN_ANNUAL = 'annual';

    protected $fillable = [
        'user_id',
        'event_id',
        'timezone',
        'weekly_schedule',
        'blocked_dates',
        'blocked_ranges',
        'auto_accept_bookings',
        'booking_lead_time_hours',
        'buffer_minutes_between_bookings',
        'max_bookings_per_day',
        'deposit_percent',
        'cancellation_policy_hours',
        'reschedule_policy_hours',
        'notify_email',
        'notify_sms',
        'quiet_hours_start',
        'quiet_hours_end',
        'subscription_plan_code',
        'subscription_plan_name',
        'subscription_price_amount',
        'subscription_duration_months',
        'subscription_service_limit',
        'subscription_package_limit',
        'subscription_status',
        'subscription_started_at',
        'subscription_paid_at',
        'subscription_expires_at',
    ];

    protected $casts = [
        'weekly_schedule' => 'array',
        'blocked_dates' => 'array',
        'blocked_ranges' => 'array',
        'auto_accept_bookings' => 'boolean',
        'booking_lead_time_hours' => 'integer',
        'buffer_minutes_between_bookings' => 'integer',
        'max_bookings_per_day' => 'integer',
        'deposit_percent' => 'float',
        'cancellation_policy_hours' => 'integer',
        'reschedule_policy_hours' => 'integer',
        'notify_email' => 'boolean',
        'notify_sms' => 'boolean',
        'subscription_price_amount' => 'float',
        'subscription_duration_months' => 'integer',
        'subscription_service_limit' => 'integer',
        'subscription_package_limit' => 'integer',
        'subscription_started_at' => 'datetime',
        'subscription_paid_at' => 'datetime',
        'subscription_expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function defaultWeeklySchedule(): array
    {
        $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

        return array_map(
            fn (string $day) => [
                'day' => $day,
                'closed' => false,
                'slots' => [
                    ['start' => '09:00', 'end' => '17:00'],
                ],
            ],
            $days,
        );
    }

    public static function defaultAvailabilityAttributes(): array
    {
        return [
            'timezone' => config('app.timezone', 'UTC'),
            'weekly_schedule' => static::defaultWeeklySchedule(),
            'blocked_dates' => [],
            'blocked_ranges' => [],
            'auto_accept_bookings' => false,
            'booking_lead_time_hours' => 24,
            'buffer_minutes_between_bookings' => 30,
            'max_bookings_per_day' => null,
            'deposit_percent' => 20.0,
            'cancellation_policy_hours' => 72,
            'reschedule_policy_hours' => 48,
            'notify_email' => true,
            'notify_sms' => false,
            'quiet_hours_start' => null,
            'quiet_hours_end' => null,
        ];
    }

    public static function defaultSubscriptionAttributes(): array
    {
        return [
            'subscription_plan_code' => null,
            'subscription_plan_name' => null,
            'subscription_price_amount' => 0,
            'subscription_duration_months' => null,
            'subscription_service_limit' => null,
            'subscription_package_limit' => null,
            'subscription_status' => 'inactive',
            'subscription_started_at' => null,
            'subscription_paid_at' => null,
            'subscription_expires_at' => null,
        ];
    }

    public static function defaultGlobalAttributes(): array
    {
        return [
            ...static::defaultAvailabilityAttributes(),
            ...static::defaultSubscriptionAttributes(),
        ];
    }

    public static function availableSubscriptionPlans(): array
    {
        return [
            static::PLAN_QUARTERLY => [
                'code' => static::PLAN_QUARTERLY,
                'name' => 'Vendor 3 Months',
                'price' => 10.0,
                'duration_months' => 3,
                'service_limit' => 3,
                'package_limit' => 1,
            ],
            static::PLAN_ANNUAL => [
                'code' => static::PLAN_ANNUAL,
                'name' => 'Vendor 1 Year',
                'price' => 100.0,
                'duration_months' => 12,
                'service_limit' => null,
                'package_limit' => null,
            ],
        ];
    }

    public static function resolveSubscriptionPlan(?string $code): ?array
    {
        if (! is_string($code)) {
            return null;
        }

        $normalized = strtolower(trim($code));

        return static::availableSubscriptionPlans()[$normalized] ?? null;
    }

    public static function subscriptionPayloadForPlan(string $code, ?Carbon $startsAt = null): array
    {
        $plan = static::resolveSubscriptionPlan($code);
        if (! $plan) {
            return static::defaultSubscriptionAttributes();
        }

        $startedAt = ($startsAt ?? now())->copy();

        return [
            'subscription_plan_code' => $plan['code'],
            'subscription_plan_name' => $plan['name'],
            'subscription_price_amount' => $plan['price'],
            'subscription_duration_months' => $plan['duration_months'],
            'subscription_service_limit' => $plan['service_limit'],
            'subscription_package_limit' => $plan['package_limit'],
            'subscription_status' => 'active',
            'subscription_started_at' => $startedAt,
            'subscription_paid_at' => $startedAt,
            'subscription_expires_at' => $startedAt->copy()->addMonthsNoOverflow((int) $plan['duration_months']),
        ];
    }

    public static function pendingSubscriptionPayloadForPlan(string $code): array
    {
        $plan = static::resolveSubscriptionPlan($code);
        if (! $plan) {
            return static::defaultSubscriptionAttributes();
        }

        return [
            'subscription_plan_code' => $plan['code'],
            'subscription_plan_name' => $plan['name'],
            'subscription_price_amount' => $plan['price'],
            'subscription_duration_months' => $plan['duration_months'],
            'subscription_service_limit' => $plan['service_limit'],
            'subscription_package_limit' => $plan['package_limit'],
            'subscription_status' => 'pending_payment',
            'subscription_started_at' => null,
            'subscription_paid_at' => null,
            'subscription_expires_at' => null,
        ];
    }

    public static function subscriptionCheckoutPayloadForPlan(string $code): array
    {
        $plan = static::resolveSubscriptionPlan($code);
        if (! $plan) {
            return [];
        }

        return [
            'code' => $plan['code'],
            'name' => $plan['name'],
            'price' => $plan['price'],
            'duration_months' => $plan['duration_months'],
            'service_limit' => $plan['service_limit'],
            'package_limit' => $plan['package_limit'],
            'payment_qr_url' => config('services.vendor_subscription.qr_url', '/ABAqr.png'),
            'payment_note' => config(
                'services.vendor_subscription.payment_note',
                'Scan this QR code and pay the selected vendor listing plan to Achar before your account can go live.'
            ),
        ];
    }

    public function subscriptionIsActive(?Carbon $at = null): bool
    {
        $status = strtolower(trim((string) $this->subscription_status));
        if ($status !== 'active') {
            return false;
        }

        $expiresAt = $this->subscription_expires_at;
        if (! $expiresAt instanceof Carbon) {
            return false;
        }

        return ! $expiresAt->lt($at ?? now());
    }

    public function resolvedSubscriptionStatus(?Carbon $at = null): string
    {
        if ($this->subscriptionIsActive($at)) {
            return 'active';
        }

        $expiresAt = $this->subscription_expires_at;
        if ($expiresAt instanceof Carbon && $expiresAt->lt($at ?? now())) {
            return 'expired';
        }

        return strtolower(trim((string) $this->subscription_status)) ?: 'inactive';
    }
}
