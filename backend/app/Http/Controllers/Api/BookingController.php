<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingNotification;
use App\Models\Event;
use App\Support\NotificationCache;
use App\Support\VendorCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\CarbonPeriod;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function publicIndex(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_email' => ['nullable', 'email', 'max:255'],
            'event_type' => ['nullable', 'string', 'max:60'],
            'status' => ['nullable', Rule::in(['pending', 'confirmed', 'cancelled'])],
        ]);

        $bookings = Booking::query()
            ->with([
                'event:id,title,event_type,image_url,starts_at,location,vendor_id',
                'event.vendor:id,name,email',
                'user:id,name,email',
            ])
            ->when(
                $validated['customer_email'] ?? null,
                fn ($query, $email) => $query->where('customer_email', $email)
            )
            ->when(
                $validated['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            )
            ->when(
                $validated['event_type'] ?? null,
                fn ($query, $eventType) => $query->whereHas(
                    'event',
                    fn ($eventQuery) => $eventQuery->where('event_type', $eventType)
                )
            )
            ->latest()
            ->paginate(20);

        return response()->json($bookings);
    }

    public function index(): JsonResponse
    {
        $bookings = Booking::query()
            ->with([
                'event:id,title,event_type,image_url,starts_at,location,vendor_id',
                'event.vendor:id,name,email',
                'user:id,name,email',
            ])
            ->latest()
            ->paginate(15);

        return response()->json($bookings);
    }

    public function indexByEvent(Event $event): JsonResponse
    {
        $bookings = $event->bookings()
            ->with('user:id,name,email')
            ->latest()
            ->paginate(15);

        return response()->json($bookings);
    }

    public function availability(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'requested_date' => ['nullable', 'date'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $requestedDate = $validated['requested_date'] ?? $event->starts_at?->toDateString();
        $requestedQuantity = (int) ($validated['quantity'] ?? 1);

        $reserved = $this->reservedQuantityForDate($event, $requestedDate);
        $hasExistingBooking = $this->hasBookingOnDate($event, $requestedDate);

        $remainingCapacity = $event->capacity === 0
            ? null
            : max(0, $event->capacity - $reserved);

        $hasOtherVendorBooking = $this->vendorHasAnotherBookedEvent($event, $requestedDate);

        $serviceAvailable = $event->is_active
            && ! $hasExistingBooking
            && ($event->capacity === 0 || $remainingCapacity >= $requestedQuantity);

        return response()->json([
            'event_id' => $event->id,
            'requested_date' => $requestedDate,
            'service_available' => $serviceAvailable,
            'vendor_available' => ! $hasOtherVendorBooking,
            'is_busy' => ! $serviceAvailable || $hasOtherVendorBooking,
            'has_another_booked' => $hasOtherVendorBooking,
            'has_existing_booking' => $hasExistingBooking,
            'is_active' => (bool) $event->is_active,
            'capacity' => $event->capacity,
            'requested_quantity' => $requestedQuantity,
            'reserved' => (int) $reserved,
            'remaining_capacity' => $remainingCapacity,
            'message' => $this->availabilityMessage($event, $serviceAvailable, $hasOtherVendorBooking, $remainingCapacity, $requestedDate),
        ]);
    }

    public function availabilityCalendar(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $cursor = isset($validated['month'])
            ? Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth()
            : now()->startOfMonth();

        $startOfMonth = $cursor->copy()->startOfMonth();
        $endOfMonth = $cursor->copy()->endOfMonth();
        $days = [];

        foreach (CarbonPeriod::create($startOfMonth, $endOfMonth) as $date) {
            $dateString = $date->toDateString();
            $reserved = $this->reservedQuantityForDate($event, $dateString);
            $hasExistingBooking = $this->hasBookingOnDate($event, $dateString);
            $remainingCapacity = $event->capacity === 0
                ? null
                : max(0, $event->capacity - $reserved);
            $hasOtherVendorBooking = $this->vendorHasAnotherBookedEvent($event, $dateString);
            $isAvailable = $event->is_active
                && ! $hasExistingBooking
                && ! $hasOtherVendorBooking
                && ($event->capacity === 0 || $remainingCapacity > 0);

            $days[] = [
                'date' => $dateString,
                'status' => $isAvailable ? 'available' : 'booked',
                'is_available' => $isAvailable,
                'reserved' => (int) $reserved,
                'remaining_capacity' => $remainingCapacity,
                'has_another_booked' => $hasOtherVendorBooking,
                'has_existing_booking' => $hasExistingBooking,
            ];
        }

        return response()->json([
            'event_id' => $event->id,
            'month' => $startOfMonth->format('Y-m'),
            'days' => $days,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => ['nullable', 'exists:events,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'customer_location' => ['nullable', 'string', 'max:255'],
            'service_name' => ['nullable', 'string', 'max:255'],
            'requested_event_type' => ['nullable', 'string', 'max:60'],
            'requested_event_date' => ['nullable', 'date'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'booked_items' => ['nullable', 'array'],
            'booked_items.*.type' => ['nullable', 'string', 'max:40'],
            'booked_items.*.name' => ['nullable', 'string', 'max:255'],
            'booked_items.*.description' => ['nullable', 'string'],
            'booked_items.*.qty' => ['nullable', 'integer', 'min:1'],
            'booked_items.*.unitPrice' => ['nullable', 'numeric', 'min:0'],
            'booked_items.*.totalPrice' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:40'],
        ]);

        $eventId = $validated['event_id'] ?? null;
        $event = $eventId ? Event::with('vendor:id,name,email')->findOrFail($eventId) : null;
        $totalAmount = 0.0;
        $requestedDate = $validated['requested_event_date'] ?? $event?->starts_at?->toDateString();

        if ($event) {
            if (! $event->is_active) {
                return response()->json(['message' => 'This event is not available for booking.'], 422);
            }

            if ($this->hasBookingOnDate($event, $requestedDate)) {
                return response()->json(['message' => 'This service is already booked on the selected date.'], 422);
            }

            if (! $this->hasCapacity($event, $validated['quantity'], null, $requestedDate)) {
                return response()->json(['message' => 'Not enough seats available for this booking.'], 422);
            }

            if ($this->vendorHasAnotherBookedEvent($event, $requestedDate)) {
                return response()->json(['message' => 'Vendor is already booked on the selected date.'], 422);
            }

            $totalAmount = $this->calculateTotal($event->price, $validated['quantity']);
        } else {
            $totalAmount = round((float) ($validated['total_amount'] ?? 0), 2);
            if ($totalAmount <= 0) {
                return response()->json([
                    'message' => 'total_amount is required when event_id is not provided.',
                ], 422);
            }
        }

        $paymentToken = Str::uuid()->toString();
        $booking = Booking::create([
            ...$validated,
            'user_id' => $request->user() ? $request->user()->id : null,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_token' => $paymentToken,
            'service_name' => $validated['service_name'] ?? ($event?->title ?? 'Custom Booking'),
            'requested_event_date' => $requestedDate,
            'total_amount' => $totalAmount,
            'booked_items' => $validated['booked_items'] ?? null,
        ]);

        $booking->setRelation('event', $event);
        $this->flushVendorCacheForBooking($booking);
        $this->createBookingCreatedNotifications($booking);

        return response()->json(
            $booking
                ->load('event.vendor:id,name,email')
                ->makeVisible('payment_token'),
            201
        );
    }

    public function show(Booking $booking): JsonResponse
    {
        return response()->json($booking->load(['event.vendor:id,name,email', 'user:id,name,email,phone,location']));
    }

    public function update(Request $request, Booking $booking): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'status' => ['sometimes', Rule::in(['pending', 'confirmed', 'cancelled'])],
            'customer_name' => ['sometimes', 'string', 'max:255'],
            'customer_email' => ['sometimes', 'email', 'max:255'],
            'customer_phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'customer_location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'service_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'requested_event_type' => ['sometimes', 'nullable', 'string', 'max:60'],
            'booked_items' => ['sometimes', 'nullable', 'array'],
            'booked_items.*.type' => ['nullable', 'string', 'max:40'],
            'booked_items.*.name' => ['nullable', 'string', 'max:255'],
            'booked_items.*.description' => ['nullable', 'string'],
            'booked_items.*.qty' => ['nullable', 'integer', 'min:1'],
            'booked_items.*.unitPrice' => ['nullable', 'numeric', 'min:0'],
            'booked_items.*.totalPrice' => ['nullable', 'numeric', 'min:0'],
        ]);

        $event = $booking->event;
        $newQuantity = $validated['quantity'] ?? $booking->quantity;
        $previousStatus = $booking->status;

        if ($event) {
            $requestedDate = $booking->requested_event_date?->toDateString();

            if ($this->hasBookingOnDate($event, $requestedDate, $booking->id)) {
                return response()->json(['message' => 'This service is already booked on the selected date.'], 422);
            }

            if (! $this->hasCapacity($event, $newQuantity, $booking->id, $requestedDate)) {
                return response()->json(['message' => 'Not enough seats available for this update.'], 422);
            }
        }

        $booking->update([
            ...$validated,
            'total_amount' => $event
                ? $this->calculateTotal($event->price, $newQuantity)
                : $booking->total_amount,
        ]);
        $this->flushVendorCacheForBooking($booking);

        $updatedBooking = $booking->fresh()->load('event.vendor:id,name,email');

        if (array_key_exists('status', $validated) && $validated['status'] !== $previousStatus) {
            $this->createBookingStatusNotifications($updatedBooking, $validated['status']);
        }

        return response()->json($updatedBooking->load(['event.vendor:id,name', 'user:id,name,email,phone,location']));
    }

    public function confirmPayment(Request $request, Booking $booking): JsonResponse
    {
        $validated = $request->validate([
            'payment_token' => ['required', 'string', 'max:120'],
            'payment_method' => ['nullable', 'string', 'max:40'],
            'payment_reference' => ['nullable', 'string', 'max:120'],
        ]);

        $providedToken = (string) $validated['payment_token'];
        $storedToken = (string) ($booking->payment_token ?? '');
        if ($storedToken === '' || ! hash_equals($storedToken, $providedToken)) {
            return response()->json(['message' => 'Invalid payment token.'], 403);
        }

        $previousStatus = (string) ($booking->status ?? 'pending');
        $nextStatus = 'confirmed';

        if ($booking->status !== $nextStatus || $booking->payment_status !== 'confirmed') {
            $booking->update([
                'status' => $nextStatus,
                'payment_status' => 'confirmed',
                'payment_method' => $validated['payment_method'] ?? $booking->payment_method,
                'payment_reference' => $validated['payment_reference'] ?? $booking->payment_reference,
                'paid_at' => now(),
            ]);
        }

        $updatedBooking = $booking->fresh()->load('event.vendor:id,name,email');

        if ($previousStatus !== $nextStatus) {
            $this->createBookingStatusNotifications($updatedBooking, $nextStatus);
        }

        return response()->json($updatedBooking->load(['event.vendor:id,name', 'user:id,name,email']));
    }

    public function destroy(Booking $booking): JsonResponse
    {
        $this->flushVendorCacheForBooking($booking);
        $booking->delete();

        return response()->json(null, 204);
    }

    private function hasCapacity(Event $event, int $requestedQuantity, ?int $ignoreBookingId = null, ?string $requestedDate = null): bool
    {
        if ($event->capacity === 0) {
            return true;
        }

        $reserved = $this->reservedQuantityForDate($event, $requestedDate, $ignoreBookingId);

        return ($reserved + $requestedQuantity) <= $event->capacity;
    }

    private function calculateTotal($price, int $quantity): float
    {
        return round(((float) $price) * $quantity, 2);
    }

    private function vendorHasAnotherBookedEvent(Event $event, ?string $requestedDate = null): bool
    {
        if (! $event->vendor_id) {
            return false;
        }

        if ($requestedDate) {
            return Event::query()
                ->where('vendor_id', $event->vendor_id)
                ->where('id', '!=', $event->id)
                ->whereHas(
                    'bookings',
                    fn ($query) => $query
                        ->whereIn('status', ['pending', 'confirmed'])
                        ->where(function ($dateQuery) use ($requestedDate) {
                            $dateQuery
                                ->whereDate('requested_event_date', $requestedDate)
                                ->orWhere(function ($fallbackQuery) use ($requestedDate) {
                                    $fallbackQuery
                                        ->whereNull('requested_event_date')
                                        ->whereHas('event', fn ($eventQuery) => $eventQuery->whereDate('starts_at', $requestedDate));
                                });
                        })
                )
                ->exists();
        }

        if (! $event->starts_at) {
            return false;
        }

        $eventStart = $event->starts_at->toDateTimeString();
        $eventEnd = ($event->ends_at ?? $event->starts_at)->toDateTimeString();

        return Event::query()
            ->where('vendor_id', $event->vendor_id)
            ->where('id', '!=', $event->id)
            ->whereHas(
                'bookings',
                fn ($query) => $query->whereIn('status', ['pending', 'confirmed'])
            )
            ->whereRaw(
                'starts_at <= ? AND COALESCE(ends_at, starts_at) >= ?',
                [$eventEnd, $eventStart]
            )
            ->exists();
    }

    private function availabilityMessage(
        Event $event,
        bool $serviceAvailable,
        bool $hasOtherVendorBooking,
        ?int $remainingCapacity,
        ?string $requestedDate = null
    ): string {
        if (! $event->is_active) {
            return 'Service is not active right now.';
        }

        if ($this->hasBookingOnDate($event, $requestedDate)) {
            return 'Service is already booked on that selected date.';
        }

        if ($event->capacity > 0 && $remainingCapacity === 0) {
            return 'Service is fully booked.';
        }

        if ($hasOtherVendorBooking) {
            return 'Vendor is busy at this time with another booked service.';
        }

        if ($event->capacity > 0 && $remainingCapacity !== null) {
            return "Service is available. {$remainingCapacity} spot(s) left.";
        }

        return 'Service and vendor are available.';
    }

    private function reservedQuantityForDate(Event $event, ?string $requestedDate = null, ?int $ignoreBookingId = null): int
    {
        return (int) Booking::query()
            ->where('event_id', $event->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->when($ignoreBookingId, fn ($query) => $query->where('id', '!=', $ignoreBookingId))
            ->when(
                $requestedDate,
                function ($query, $requestedDate) {
                    $query->where(function ($dateQuery) use ($requestedDate) {
                        $dateQuery
                            ->whereDate('requested_event_date', $requestedDate)
                            ->orWhere(function ($fallbackQuery) use ($requestedDate) {
                                $fallbackQuery
                                    ->whereNull('requested_event_date')
                                    ->whereHas('event', fn ($eventQuery) => $eventQuery->whereDate('starts_at', $requestedDate));
                            });
                    });
                }
            )
            ->sum('quantity');
    }

    private function hasBookingOnDate(Event $event, ?string $requestedDate = null, ?int $ignoreBookingId = null): bool
    {
        return Booking::query()
            ->where('event_id', $event->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->when($ignoreBookingId, fn ($query) => $query->where('id', '!=', $ignoreBookingId))
            ->when(
                $requestedDate,
                function ($query, $requestedDate) {
                    $query->where(function ($dateQuery) use ($requestedDate) {
                        $dateQuery
                            ->whereDate('requested_event_date', $requestedDate)
                            ->orWhere(function ($fallbackQuery) use ($requestedDate) {
                                $fallbackQuery
                                    ->whereNull('requested_event_date')
                                    ->whereHas('event', fn ($eventQuery) => $eventQuery->whereDate('starts_at', $requestedDate));
                            });
                    });
                }
            )
            ->exists();
    }

    private function createBookingCreatedNotifications(Booking $booking): void
    {
        $event = $booking->event;
        if (! $event) {
            return;
        }

        $serviceName = $booking->service_name ?: ($event->title ?: 'Service Booking');
        $eventDate = $booking->requested_event_date
            ? Carbon::parse($booking->requested_event_date)->format('M d, Y')
            : ($event->starts_at ? $event->starts_at->format('M d, Y g:i A') : 'the scheduled date');

        BookingNotification::create([
            'booking_id' => $booking->id,
            'recipient_type' => 'user',
            'recipient_user_id' => $booking->user_id,
            'recipient_email' => strtolower((string) $booking->customer_email),
            'kind' => 'booking_created',
            'title' => 'Booking request received',
            'message' => "Your booking for {$serviceName} on {$eventDate} is pending approval.",
        ]);
        $this->flushNotificationCache('user', $booking->user_id, strtolower((string) $booking->customer_email));

        if (! $event->vendor_id) {
            return;
        }

        BookingNotification::create([
            'booking_id' => $booking->id,
            'recipient_type' => 'vendor',
            'recipient_user_id' => $event->vendor_id,
            'recipient_email' => $event->vendor ? strtolower((string) $event->vendor->email) : null,
            'kind' => 'booking_created',
            'title' => 'New booking request',
            'message' => "{$booking->customer_name} requested {$booking->quantity} seat(s) for {$serviceName}.",
        ]);
        $this->flushNotificationCache(
            'vendor',
            $event->vendor_id,
            $event->vendor ? strtolower((string) $event->vendor->email) : null
        );
    }

    private function createBookingStatusNotifications(Booking $booking, string $nextStatus): void
    {
        $event = $booking->event;
        if (! $event) {
            return;
        }

        $statusLabel = ucfirst($nextStatus);
        $serviceName = $booking->service_name ?: ($event->title ?: 'Service Booking');
        $eventDate = $booking->requested_event_date
            ? Carbon::parse($booking->requested_event_date)->format('M d, Y')
            : ($event->starts_at ? $event->starts_at->format('M d, Y g:i A') : 'the scheduled date');

        BookingNotification::create([
            'booking_id' => $booking->id,
            'recipient_type' => 'user',
            'recipient_user_id' => $booking->user_id,
            'recipient_email' => strtolower((string) $booking->customer_email),
            'kind' => 'booking_status_changed',
            'title' => "Booking {$statusLabel}",
            'message' => "Your booking for {$serviceName} on {$eventDate} is now {$statusLabel}.",
        ]);
        $this->flushNotificationCache('user', $booking->user_id, strtolower((string) $booking->customer_email));

        if (! $event->vendor_id) {
            return;
        }

        BookingNotification::create([
            'booking_id' => $booking->id,
            'recipient_type' => 'vendor',
            'recipient_user_id' => $event->vendor_id,
            'recipient_email' => $event->vendor ? strtolower((string) $event->vendor->email) : null,
            'kind' => 'booking_status_changed',
            'title' => "Booking {$statusLabel}",
            'message' => "Booking #{$booking->id} for {$serviceName} is now {$statusLabel}.",
        ]);
        $this->flushNotificationCache(
            'vendor',
            $event->vendor_id,
            $event->vendor ? strtolower((string) $event->vendor->email) : null
        );
    }

    private function flushVendorCacheForBooking(Booking $booking): void
    {
        $event = $booking->relationLoaded('event')
            ? $booking->event
            : $booking->event()->select(['id', 'vendor_id'])->first();

        $vendorId = (int) ($event?->vendor_id ?? 0);
        if ($vendorId > 0) {
            VendorCache::flushVendor($vendorId);
        }
    }

    private function flushNotificationCache(string $role, ?int $userId, ?string $email): void
    {
        NotificationCache::flushScope(NotificationCache::scopeKey($role, $userId, $email));

        if ($userId) {
            NotificationCache::flushScope(NotificationCache::scopeKey($role, $userId, null));
        }

        if ($email) {
            NotificationCache::flushScope(NotificationCache::scopeKey($role, null, $email));
        }
    }
}
