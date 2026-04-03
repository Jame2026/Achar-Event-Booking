<?php

namespace App\Support;

use App\Models\BlacklistedIdentity;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class IdentityBlacklist
{
    public static function findActiveEntry(?string $email = null, ?string $phone = null): ?BlacklistedIdentity
    {
        $normalizedEmail = ContactIdentity::normalizeEmail($email);
        $normalizedPhone = ContactIdentity::normalizePhone($phone);

        if ($normalizedEmail === null && $normalizedPhone === null) {
            return null;
        }

        return BlacklistedIdentity::query()
            ->active()
            ->where(function (Builder $query) use ($normalizedEmail, $normalizedPhone): void {
                if ($normalizedEmail !== null) {
                    $query->orWhere('blocked_email', $normalizedEmail);
                }

                if ($normalizedPhone !== null) {
                    $query->orWhere('blocked_phone', $normalizedPhone);
                }
            })
            ->latest('blacklisted_at')
            ->latest('id')
            ->first();
    }

    public static function ensureAllowed(
        ?string $email = null,
        ?string $phone = null,
        ?string $message = null
    ): void {
        $entry = self::findActiveEntry($email, $phone);

        if (! $entry) {
            return;
        }

        throw ValidationException::withMessages([
            'contact' => [self::blockedMessage($entry, $message)],
        ]);
    }

    public static function blockedMessage(?BlacklistedIdentity $entry = null, ?string $fallback = null): string
    {
        if ($fallback !== null && trim($fallback) !== '') {
            return trim($fallback);
        }

        return 'This email or phone number has been blacklisted. Please contact the admin for approval.';
    }

    public static function blacklistUser(User $user, User $admin, string $reason): BlacklistedIdentity
    {
        $email = ContactIdentity::normalizeEmail($user->email);
        $phone = ContactIdentity::normalizePhone($user->phone);

        if ($email === null && $phone === null) {
            throw ValidationException::withMessages([
                'contact' => ['This account does not have an email or phone number to blacklist.'],
            ]);
        }

        $entry = self::findActiveEntry($email, $phone) ?? new BlacklistedIdentity();

        $entry->fill([
            'subject_name' => trim((string) $user->name) !== '' ? trim((string) $user->name) : $entry->subject_name,
            'subject_role' => $user->role,
            'blocked_email' => $email ?? $entry->blocked_email,
            'blocked_phone' => $phone ?? $entry->blocked_phone,
            'reason' => trim($reason),
            'blacklisted_by_user_id' => $admin->id,
            'blacklisted_at' => Carbon::now(),
            'approved_by_user_id' => null,
            'approved_at' => null,
        ]);

        $entry->save();

        return $entry->fresh(['blacklistedBy:id,name', 'approvedBy:id,name']);
    }

    public static function approve(BlacklistedIdentity $entry, User $admin): void
    {
        $approvedAt = Carbon::now();
        $email = $entry->blocked_email ? trim((string) $entry->blocked_email) : null;
        $phone = $entry->blocked_phone ? trim((string) $entry->blocked_phone) : null;

        if (($email === null || $email === '') && ($phone === null || $phone === '')) {
            $entry->forceFill([
                'approved_by_user_id' => $admin->id,
                'approved_at' => $approvedAt,
            ])->save();

            return;
        }

        BlacklistedIdentity::query()
            ->active()
            ->where(function (Builder $query) use ($entry): void {
                if ($entry->blocked_email) {
                    $query->orWhere('blocked_email', $entry->blocked_email);
                }

                if ($entry->blocked_phone) {
                    $query->orWhere('blocked_phone', $entry->blocked_phone);
                }
            })
            ->update([
                'approved_by_user_id' => $admin->id,
                'approved_at' => $approvedAt,
                'updated_at' => $approvedAt,
            ]);
    }
}
