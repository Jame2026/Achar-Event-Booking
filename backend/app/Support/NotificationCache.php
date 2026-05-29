<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

class NotificationCache
{
    private const TTL_SECONDS = 20;

    public static function rememberIndex(string $scopeKey, int $limit, Closure $resolver): mixed
    {
        $cacheKey = "notifications:{$scopeKey}:limit:{$limit}";

        try {
            return Cache::remember($cacheKey, now()->addSeconds(self::TTL_SECONDS), $resolver);
        } catch (\Throwable) {
            return $resolver();
        }
    }

    public static function flushScope(string $scopeKey): void
    {
        foreach ([10, 12, 20, 50] as $limit) {
            self::forget("notifications:{$scopeKey}:limit:{$limit}");
        }
    }

    public static function scopeKey(?string $role, ?int $userId, ?string $email): string
    {
        $normalizedRole = $role ?: 'all';
        $normalizedUserId = $userId ?: 0;
        $normalizedEmail = $email ? md5(strtolower(trim($email))) : 'none';

        return "{$normalizedRole}:{$normalizedUserId}:{$normalizedEmail}";
    }

    private static function forget(string $key): void
    {
        try {
            Cache::forget($key);
        } catch (\Throwable) {
            // no-op
        }
    }
}
