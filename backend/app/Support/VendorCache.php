<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

class VendorCache
{
    private const SERVICES_TTL_SECONDS = 120;
    private const BOOKINGS_TTL_SECONDS = 60;
    private const DASHBOARD_TTL_SECONDS = 30;

    public static function rememberServices(int $vendorId, Closure $resolver): mixed
    {
        return self::remember(self::servicesKey($vendorId), self::SERVICES_TTL_SECONDS, $resolver);
    }

    public static function rememberBookings(int $vendorId, Closure $resolver): mixed
    {
        return self::remember(self::bookingsKey($vendorId), self::BOOKINGS_TTL_SECONDS, $resolver);
    }

    public static function rememberDashboard(int $vendorId, Closure $resolver): mixed
    {
        return self::remember(self::dashboardKey($vendorId), self::DASHBOARD_TTL_SECONDS, $resolver);
    }

    public static function flushVendor(int $vendorId): void
    {
        self::forget(self::servicesKey($vendorId));
        self::forget(self::bookingsKey($vendorId));
        self::forget(self::dashboardKey($vendorId));
    }

    private static function servicesKey(int $vendorId): string
    {
        return "vendor:{$vendorId}:services";
    }

    private static function bookingsKey(int $vendorId): string
    {
        return "vendor:{$vendorId}:bookings";
    }

    private static function dashboardKey(int $vendorId): string
    {
        return "vendor:{$vendorId}:dashboard";
    }

    private static function remember(string $key, int $ttlSeconds, Closure $resolver): mixed
    {
        try {
            return Cache::remember($key, now()->addSeconds($ttlSeconds), $resolver);
        } catch (\Throwable) {
            return $resolver();
        }
    }

    private static function forget(string $key): void
    {
        try {
            Cache::forget($key);
        } catch (\Throwable) {
            // no-op: cache backend may be temporarily unavailable
        }
    }
}
