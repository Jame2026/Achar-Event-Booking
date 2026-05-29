<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

class PublicEventCache
{
    private const TTL_SECONDS = 300;
    private const VERSION_KEY = 'public_events_version';

    public static function rememberIndex(int $perPage, Closure $resolver): mixed
    {
        $version = self::version();
        $cacheKey = "public_events:v{$version}:per_page:{$perPage}";

        try {
            return Cache::remember($cacheKey, now()->addSeconds(self::TTL_SECONDS), $resolver);
        } catch (\Throwable) {
            return $resolver();
        }
    }

    public static function invalidate(): void
    {
        try {
            Cache::forever(self::VERSION_KEY, self::version() + 1);
        } catch (\Throwable) {
            // no-op: the response should still succeed without cache invalidation
        }
    }

    private static function version(): int
    {
        try {
            return (int) Cache::get(self::VERSION_KEY, 1);
        } catch (\Throwable) {
            return 1;
        }
    }
}
