<?php

namespace App\Support;

use App\Models\Event;
use App\Models\VendorSetting;
use Illuminate\Support\Carbon;

class VendorDayOff
{
    /**
     * Resolve whether a vendor or service setting blocks bookings on the requested date.
     *
     * @return array{is_day_off: bool, scope: ?string, message: ?string, date: ?string}
     */
    public static function resolve(Event $event, ?string $requestedDate = null): array
    {
        $dateKey = self::normalizeDateKey($requestedDate ?: $event->starts_at?->toDateString());
        $vendorId = (int) ($event->vendor_id ?? 0);

        if ($vendorId <= 0 || ! $dateKey) {
            return [
                'is_day_off' => false,
                'scope' => null,
                'message' => null,
                'date' => $dateKey,
            ];
        }

        $date = Carbon::createFromFormat('Y-m-d', $dateKey)->startOfDay();

        $settings = VendorSetting::query()
            ->where('user_id', $vendorId)
            ->where(function ($query) use ($event) {
                $query
                    ->whereNull('event_id')
                    ->orWhere('event_id', $event->id);
            })
            ->get()
            ->sortBy(fn (VendorSetting $setting) => $setting->event_id === null ? 0 : 1)
            ->values();

        foreach ($settings as $setting) {
            if (! self::matchesSetting($setting, $date)) {
                continue;
            }

            $isAccountDayOff = $setting->event_id === null;

            return [
                'is_day_off' => true,
                'scope' => $isAccountDayOff ? 'account' : 'service',
                'message' => $isAccountDayOff
                    ? 'Vendor is unavailable on the selected date because the account is marked as day off.'
                    : 'Vendor is unavailable on the selected date because this service is marked as day off.',
                'date' => $dateKey,
            ];
        }

        return [
            'is_day_off' => false,
            'scope' => null,
            'message' => null,
            'date' => $dateKey,
        ];
    }

    private static function matchesSetting(VendorSetting $setting, Carbon $date): bool
    {
        return self::matchesBlockedDate($setting, $date->toDateString())
            || self::matchesBlockedRange($setting, $date)
            || self::matchesClosedWeekday($setting, $date);
    }

    private static function matchesBlockedDate(VendorSetting $setting, string $dateKey): bool
    {
        foreach ((array) $setting->blocked_dates as $blockedDate) {
            if (self::normalizeDateKey($blockedDate) === $dateKey) {
                return true;
            }
        }

        return false;
    }

    private static function matchesBlockedRange(VendorSetting $setting, Carbon $date): bool
    {
        foreach ((array) $setting->blocked_ranges as $range) {
            if (! is_array($range)) {
                continue;
            }

            // Booking currently happens at day granularity, so a matched blocked range
            // makes the entire date unavailable.
            if (self::matchesBlockedRangeEntry($range, $date)) {
                return true;
            }
        }

        return false;
    }

    private static function matchesBlockedRangeEntry(array $range, Carbon $date): bool
    {
        $dateKey = $date->toDateString();
        $hasDateConstraint = false;
        $dateMatches = true;

        $singleDate = self::normalizeDateKey($range['date'] ?? null);
        if ($singleDate) {
            $hasDateConstraint = true;
            $dateMatches = $dateMatches && $singleDate === $dateKey;
        }

        $startDate = self::normalizeDateKey($range['start_date'] ?? null);
        $endDate = self::normalizeDateKey($range['end_date'] ?? null);
        if ($startDate && $endDate) {
            if ($startDate > $endDate) {
                [$startDate, $endDate] = [$endDate, $startDate];
            }

            $hasDateConstraint = true;
            $dateMatches = $dateMatches && $dateKey >= $startDate && $dateKey <= $endDate;
        }

        $hasDayConstraint = false;
        $dayMatches = true;

        $startDay = self::normalizeDayKey($range['start_day'] ?? null);
        $endDay = self::normalizeDayKey($range['end_day'] ?? null);
        if ($startDay && $endDay) {
            $hasDayConstraint = true;
            $dayMatches = self::dayFallsWithinRange(self::dayKeyForDate($date), $startDay, $endDay);
        }

        if (! $hasDateConstraint && ! $hasDayConstraint) {
            return false;
        }

        return $dateMatches && $dayMatches;
    }

    private static function matchesClosedWeekday(VendorSetting $setting, Carbon $date): bool
    {
        $dayKey = self::dayKeyForDate($date);

        foreach ((array) $setting->weekly_schedule as $scheduleRow) {
            if (! is_array($scheduleRow)) {
                continue;
            }

            if (self::normalizeDayKey($scheduleRow['day'] ?? null) !== $dayKey) {
                continue;
            }

            return filter_var($scheduleRow['closed'] ?? false, FILTER_VALIDATE_BOOL);
        }

        return false;
    }

    private static function normalizeDateKey(mixed $value): ?string
    {
        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $trimmed) === 1) {
            return $trimmed;
        }

        try {
            return Carbon::parse($trimmed)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private static function normalizeDayKey(mixed $value): ?string
    {
        $day = strtolower(trim((string) $value));
        $validDays = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

        return in_array($day, $validDays, true) ? $day : null;
    }

    private static function dayKeyForDate(Carbon $date): string
    {
        return strtolower($date->format('D'));
    }

    private static function dayFallsWithinRange(string $candidate, string $start, string $end): bool
    {
        $dayIndexes = [
            'mon' => 0,
            'tue' => 1,
            'wed' => 2,
            'thu' => 3,
            'fri' => 4,
            'sat' => 5,
            'sun' => 6,
        ];

        $candidateIndex = $dayIndexes[$candidate] ?? null;
        $startIndex = $dayIndexes[$start] ?? null;
        $endIndex = $dayIndexes[$end] ?? null;

        if ($candidateIndex === null || $startIndex === null || $endIndex === null) {
            return false;
        }

        if ($startIndex <= $endIndex) {
            return $candidateIndex >= $startIndex && $candidateIndex <= $endIndex;
        }

        return $candidateIndex >= $startIndex || $candidateIndex <= $endIndex;
    }
}
