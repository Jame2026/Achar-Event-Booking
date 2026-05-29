<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use App\Models\VendorSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VendorSettingController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $this->resolveVendorFromRequest($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $eventId = $request->integer('event_id') ?: null;

        if ($eventId) {
            $event = Event::find($eventId);
            if (! $event) {
                return response()->json(['message' => 'Service not found.'], 404);
            }
            if (! $user->isAdmin() && (int) $event->vendor_id !== (int) $user->id) {
                return response()->json(['message' => 'You cannot manage settings for this service.'], 403);
            }
        }

        $settings = VendorSetting::firstOrCreate(
            ['user_id' => $user->id, 'event_id' => $eventId],
            [
                'timezone' => config('app.timezone', 'UTC'),
                'weekly_schedule' => $this->defaultWeeklySchedule(),
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
            ],
        );

        return response()->json([
            'settings' => $settings->only([
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
                'updated_at',
                'event_id',
            ]),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $this->resolveVendorFromRequest($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $data = $request->validate([
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'vendor_user_id' => ['nullable', 'integer', 'min:1'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'weekly_schedule' => ['nullable', 'array'],
            'weekly_schedule.*.day' => ['required', 'string', Rule::in(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])],
            'weekly_schedule.*.closed' => ['sometimes', 'boolean'],
            'weekly_schedule.*.slots' => ['nullable', 'array'],
            'weekly_schedule.*.slots.*.start' => ['required_with:weekly_schedule.*.slots.*.end', 'date_format:H:i'],
            'weekly_schedule.*.slots.*.end' => ['required_with:weekly_schedule.*.slots.*.start', 'date_format:H:i'],
            'blocked_dates' => ['nullable', 'array'],
            'blocked_dates.*' => ['date_format:Y-m-d'],
            'blocked_ranges' => ['nullable', 'array'],
            'blocked_ranges.*.start_date' => ['required_with:blocked_ranges.*.end_date', 'date_format:Y-m-d'],
            'blocked_ranges.*.end_date' => ['required_with:blocked_ranges.*.start_date', 'date_format:Y-m-d'],
            'blocked_ranges.*.start_day' => ['nullable', 'string', Rule::in(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])],
            'blocked_ranges.*.end_day' => ['nullable', 'string', Rule::in(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])],
            'blocked_ranges.*.start_time' => ['nullable', 'date_format:H:i'],
            'blocked_ranges.*.end_time' => ['nullable', 'date_format:H:i'],
            'blocked_ranges.*.date' => ['nullable', 'date_format:Y-m-d'],
            'blocked_ranges.*.note' => ['nullable', 'string', 'max:255'],
            'auto_accept_bookings' => ['sometimes', 'boolean'],
            'booking_lead_time_hours' => ['nullable', 'integer', 'min:0', 'max:168'],
            'buffer_minutes_between_bookings' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'max_bookings_per_day' => ['nullable', 'integer', 'min:1', 'max:500'],
            'deposit_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'cancellation_policy_hours' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'reschedule_policy_hours' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'notify_email' => ['sometimes', 'boolean'],
            'notify_sms' => ['sometimes', 'boolean'],
            'quiet_hours_start' => ['nullable', 'date_format:H:i'],
            'quiet_hours_end' => ['nullable', 'date_format:H:i'],
        ]);

        $eventId = isset($data['event_id']) ? (int) $data['event_id'] : null;
        if ($eventId) {
            $event = Event::find($eventId);
            if (! $event) {
                return response()->json(['message' => 'Service not found.'], 404);
            }
            if (! $user->isAdmin() && (int) $event->vendor_id !== (int) $user->id) {
                return response()->json(['message' => 'You cannot manage settings for this service.'], 403);
            }
        }

        $settings = VendorSetting::firstOrCreate(
            ['user_id' => $user->id, 'event_id' => $eventId],
            [
                'timezone' => config('app.timezone', 'UTC'),
                'weekly_schedule' => $this->defaultWeeklySchedule(),
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
            ],
        );

        $settings->timezone = (string) ($data['timezone'] ?? $settings->timezone ?? config('app.timezone', 'UTC'));
        $settings->weekly_schedule = $this->normalizeWeeklySchedule($data['weekly_schedule'] ?? $settings->weekly_schedule ?? []);
        $settings->blocked_dates = $this->normalizeBlockedDates($data['blocked_dates'] ?? $settings->blocked_dates ?? []);
        $settings->blocked_ranges = $this->normalizeBlockedRanges($data['blocked_ranges'] ?? $settings->blocked_ranges ?? []);
        $settings->auto_accept_bookings = filter_var(
            $data['auto_accept_bookings'] ?? $settings->auto_accept_bookings ?? false,
            FILTER_VALIDATE_BOOL
        );
        $settings->booking_lead_time_hours = $this->normalizeInt(
            $data['booking_lead_time_hours'] ?? $settings->booking_lead_time_hours ?? 24,
            0,
            168
        );
        $settings->buffer_minutes_between_bookings = $this->normalizeInt(
            $data['buffer_minutes_between_bookings'] ?? $settings->buffer_minutes_between_bookings ?? 30,
            0,
            1440
        );
        $settings->max_bookings_per_day = $this->normalizeNullableInt(
            $data['max_bookings_per_day'] ?? $settings->max_bookings_per_day ?? null,
            1,
            500
        );
        $settings->deposit_percent = $this->normalizePercent(
            $data['deposit_percent'] ?? $settings->deposit_percent ?? 20.0
        );
        $settings->cancellation_policy_hours = $this->normalizeInt(
            $data['cancellation_policy_hours'] ?? $settings->cancellation_policy_hours ?? 72,
            0,
            1440
        );
        $settings->reschedule_policy_hours = $this->normalizeInt(
            $data['reschedule_policy_hours'] ?? $settings->reschedule_policy_hours ?? 48,
            0,
            1440
        );
        $settings->notify_email = filter_var(
            $data['notify_email'] ?? $settings->notify_email ?? true,
            FILTER_VALIDATE_BOOL
        );
        $settings->notify_sms = filter_var(
            $data['notify_sms'] ?? $settings->notify_sms ?? false,
            FILTER_VALIDATE_BOOL
        );
        $settings->quiet_hours_start = $this->normalizeTimeOrNull(
            $data['quiet_hours_start'] ?? $settings->quiet_hours_start ?? null
        );
        $settings->quiet_hours_end = $this->normalizeTimeOrNull(
            $data['quiet_hours_end'] ?? $settings->quiet_hours_end ?? null
        );
        $settings->save();

        return response()->json([
            'message' => 'Vendor settings saved.',
            'settings' => $settings->only([
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
                'updated_at',
                'event_id',
            ]),
        ]);
    }

    private function resolveVendorFromRequest(Request $request): User|JsonResponse
    {
        $authenticatedUser = $request->user();
        if ($authenticatedUser instanceof User && in_array($authenticatedUser->role, ['vendor', 'admin'], true)) {
            return $authenticatedUser;
        }

        $validated = $request->validate([
            'vendor_user_id' => ['required', 'integer', 'min:1'],
        ]);

        $vendor = User::query()
            ->select(['id', 'role'])
            ->find((int) $validated['vendor_user_id']);

        if (! $vendor) {
            return response()->json(['message' => 'Selected vendor account does not exist.'], 422);
        }

        if (! in_array($vendor->role, ['vendor', 'admin'], true)) {
            return response()->json(['message' => 'Selected user is not a vendor account.'], 422);
        }

        return $vendor;
    }

    private function defaultWeeklySchedule(): array
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

    private function normalizeWeeklySchedule(mixed $input): array
    {
        $validDays = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        $seen = [];
        $result = [];

        foreach ((array) $input as $row) {
            $day = strtolower((string) ($row['day'] ?? ''));
            if ($day === '' || ! in_array($day, $validDays, true) || isset($seen[$day])) {
                continue;
            }

            $closed = filter_var($row['closed'] ?? false, FILTER_VALIDATE_BOOL);
            $slots = [];
            foreach ((array) ($row['slots'] ?? []) as $slot) {
                $start = is_string($slot['start'] ?? null) ? $slot['start'] : null;
                $end = is_string($slot['end'] ?? null) ? $slot['end'] : null;
                if (! $start || ! $end) {
                    continue;
                }
                if ($start === $end) {
                    continue;
                }
                if ($start > $end) {
                    [$start, $end] = [$end, $start];
                }
                $slots[] = ['start' => $start, 'end' => $end];
            }

            $result[] = [
                'day' => $day,
                'closed' => $closed,
                'slots' => $closed ? [] : $slots,
            ];
            $seen[$day] = true;
        }

        if (count($result) === 0) {
            return $this->defaultWeeklySchedule();
        }

        usort($result, fn ($a, $b) => array_search($a['day'], $validDays, true) <=> array_search($b['day'], $validDays, true));

        return $result;
    }

    private function normalizeBlockedDates(mixed $input): array
    {
        $dates = [];
        foreach ((array) $input as $value) {
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }
            if (! preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $value)) {
                continue;
            }
            $dates[$value] = true;
        }

        $unique = array_keys($dates);
        sort($unique);

        return $unique;
    }

    private function normalizeBlockedRanges(mixed $input): array
    {
        $ranges = [];
        $validDays = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        foreach ((array) $input as $row) {
            $start = trim((string) ($row['start_date'] ?? ''));
            $end = trim((string) ($row['end_date'] ?? ''));
            $singleDate = trim((string) ($row['date'] ?? ''));
            $startDay = strtolower(trim((string) ($row['start_day'] ?? '')));
            $endDay = strtolower(trim((string) ($row['end_day'] ?? '')));
            $startTime = trim((string) ($row['start_time'] ?? ''));
            $endTime = trim((string) ($row['end_time'] ?? ''));
            $note = trim((string) ($row['note'] ?? ''));

            $hasDateRange = preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $start)
                && preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $end);

            if ($hasDateRange && $start > $end) {
                [$start, $end] = [$end, $start];
            }

            $hasSingleDate = preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $singleDate);
            $hasDayRange = in_array($startDay, $validDays, true) && in_array($endDay, $validDays, true);
            $hasTimeRange = preg_match('/^(?:[01]\\d|2[0-3]):[0-5]\\d$/', $startTime)
                && preg_match('/^(?:[01]\\d|2[0-3]):[0-5]\\d$/', $endTime)
                && $startTime !== $endTime;

            if ($hasTimeRange && $startTime > $endTime) {
                [$startTime, $endTime] = [$endTime, $startTime];
            }

            // At least one kind of boundary (date range, single date, or day range) is required.
            if (! ($hasDateRange || $hasSingleDate || $hasDayRange)) {
                continue;
            }

            $entry = [];
            if ($hasDateRange) {
                $entry['start_date'] = $start;
                $entry['end_date'] = $end;
            }
            if ($hasSingleDate) {
                $entry['date'] = $singleDate;
            }
            if ($hasDayRange) {
                $entry['start_day'] = $startDay;
                $entry['end_day'] = $endDay;
            }
            if ($hasTimeRange) {
                $entry['start_time'] = $startTime;
                $entry['end_time'] = $endTime;
            }
            $entry['note'] = $note === '' ? null : $note;

            $ranges[] = $entry;
        }

        usort($ranges, function ($a, $b) {
            $validDays = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

            $aDate = $a['date'] ?? $a['start_date'] ?? null;
            $bDate = $b['date'] ?? $b['start_date'] ?? null;
            if ($aDate && $bDate) {
                return [$aDate, $a['start_time'] ?? ''] <=> [$bDate, $b['start_time'] ?? ''];
            }
            if ($aDate && ! $bDate) {
                return -1;
            }
            if ($bDate && ! $aDate) {
                return 1;
            }

            $aDay = $a['start_day'] ?? null;
            $bDay = $b['start_day'] ?? null;
            if ($aDay && $bDay) {
                return array_search($aDay, $validDays, true) <=> array_search($bDay, $validDays, true);
            }
            if ($aDay && ! $bDay) {
                return -1;
            }
            if ($bDay && ! $aDay) {
                return 1;
            }

            return 0;
        });

        return $ranges;
    }

    private function normalizeInt(mixed $value, int $min, int $max): int
    {
        $intValue = is_numeric($value) ? (int) $value : $min;
        return max($min, min($intValue, $max));
    }

    private function normalizeNullableInt(mixed $value, int $min, int $max): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $intValue = is_numeric($value) ? (int) $value : null;
        if ($intValue === null) {
            return null;
        }

        return max($min, min($intValue, $max));
    }

    private function normalizePercent(mixed $value): float
    {
        $number = is_numeric($value) ? (float) $value : 0.0;
        if ($number < 0.0) {
            return 0.0;
        }
        if ($number > 100.0) {
            return 100.0;
        }

        return round($number, 2);
    }

    private function normalizeTimeOrNull(mixed $value): ?string
    {
        $time = is_string($value) ? trim($value) : '';
        if ($time === '') {
            return null;
        }

        if (! preg_match('/^(?:[01]\\d|2[0-3]):[0-5]\\d$/', $time)) {
            return null;
        }

        return $time;
    }
}
