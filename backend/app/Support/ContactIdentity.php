<?php

namespace App\Support;

class ContactIdentity
{
    public static function normalizeEmail(?string $value): ?string
    {
        $normalized = strtolower(trim((string) $value));

        return $normalized !== '' ? $normalized : null;
    }

    public static function normalizePhone(?string $value): ?string
    {
        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $trimmed);
        if (! is_string($digits) || $digits === '') {
            return null;
        }

        if (str_starts_with($trimmed, '+')) {
            return '+'.$digits;
        }

        if (str_starts_with($digits, '855')) {
            return '+'.$digits;
        }

        if (str_starts_with($digits, '0') && strlen($digits) > 1) {
            $digits = substr($digits, 1);
        }

        return '+855'.$digits;
    }
}
