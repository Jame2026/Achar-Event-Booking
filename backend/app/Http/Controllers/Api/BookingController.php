<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingNotification;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Event;
use App\Models\User;
use App\Support\ContactIdentity;
use App\Support\IdentityBlacklist;
use App\Support\NotificationCache;
use App\Support\PublicEventCache;
use App\Support\VendorDayOff;
use App\Support\VendorCache;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    private const SERVICE_FEE_RATE = 0.035;
    private const DEPOSIT_PERCENT = 30.0;
    private const VENDOR_CANCELLATION_GRACE_DAYS = 3;
    private const LATE_CUSTOMER_CANCELLATION_REFUND_RATE = 0.15;

    public function publicIndex(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'min:1'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'event_type' => ['nullable', 'string', 'max:60'],
            'status' => ['nullable', Rule::in(['pending', 'confirmed', 'cancelled'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $perPage = max(1, min((int) ($validated['per_page'] ?? 20), 100));

        $customerEmail = isset($validated['customer_email'])
            ? ContactIdentity::normalizeEmail((string) $validated['customer_email'])
            : null;
        $customerPhone = ContactIdentity::normalizePhone($validated['customer_phone'] ?? null);
        $customerPhoneVariants = $this->bookingPhoneLookupVariants($customerPhone);
        $userId = isset($validated['user_id']) ? (int) $validated['user_id'] : null;

        if ($entry = $this->resolveBlacklistedEntry($userId, $customerEmail, $customerPhone)) {
            return response()->json([
                'message' => IdentityBlacklist::blockedMessage(
                    $entry,
                    'This email or phone number has been blacklisted. Please contact the admin for approval.'
                ),
            ], 403);
        }

        $bookings = Booking::query()
            ->with([
                'event:id,title,event_type,image_url,qr_code_url,starts_at,location,vendor_id,service_mode',
                'event.vendor:id,name,email,profile_image_url',
                'rating:id,booking_id,rating,review,updated_at',
                'user:id,name,email,phone,location,profile_image_url',
            ])
            ->when($userId || $customerEmail || $customerPhoneVariants, function ($query) use ($userId, $customerEmail, $customerPhoneVariants) {
                $query->where(function ($identityQuery) use ($userId, $customerEmail, $customerPhoneVariants) {
                    if ($userId) {
                        $identityQuery->orWhere('user_id', $userId);
                    }

                    if ($customerEmail) {
                        $identityQuery
                            ->orWhereRaw('LOWER(customer_email) = ?', [$customerEmail])
                            ->orWhereHas('user', function ($userQuery) use ($customerEmail) {
                                $userQuery->whereRaw('LOWER(email) = ?', [$customerEmail]);
                            });
                    }

                    if ($customerPhoneVariants) {
                        $identityQuery
                            ->orWhereIn('customer_phone', $customerPhoneVariants)
                            ->orWhereHas('user', function ($userQuery) use ($customerPhoneVariants) {
                                $userQuery->whereIn('phone', $customerPhoneVariants);
                            });
                    }
                });
            })
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
            ->paginate($perPage);

        return response()->json($bookings);
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $perPage = max(1, min((int) ($validated['per_page'] ?? 15), 100));

        $bookings = Booking::query()
            ->with([
                'event:id,title,event_type,image_url,qr_code_url,starts_at,location,vendor_id,service_mode',
                'event.vendor:id,name,email,profile_image_url',
                'rating:id,booking_id,rating,review,updated_at',
                'user:id,name,email,phone,location,profile_image_url',
            ])
            ->latest()
            ->paginate($perPage);

        return response()->json($bookings);
    }

    public function indexByEvent(Event $event): JsonResponse
    {
        $bookings = $event->bookings()
            ->with(['user:id,name,email', 'rating:id,booking_id,rating,review,updated_at'])
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

        $vendorDayOff = VendorDayOff::resolve($event, $requestedDate);
        $hasOtherVendorBooking = $this->vendorHasAnotherBookedEvent($event, $requestedDate);

        $serviceAvailable = $event->is_active
            && ! $hasExistingBooking
            && ($event->capacity === 0 || $remainingCapacity >= $requestedQuantity);

        $vendorAvailable = ! $hasOtherVendorBooking && ! ($vendorDayOff['is_day_off'] ?? false);

        return response()->json([
            'event_id' => $event->id,
            'requested_date' => $requestedDate,
            'service_available' => $serviceAvailable,
            'vendor_available' => $vendorAvailable,
            'is_busy' => ! $serviceAvailable || ! $vendorAvailable,
            'has_another_booked' => $hasOtherVendorBooking,
            'has_existing_booking' => $hasExistingBooking,
            'has_vendor_day_off' => (bool) ($vendorDayOff['is_day_off'] ?? false),
            'vendor_day_off_scope' => $vendorDayOff['scope'] ?? null,
            'is_active' => (bool) $event->is_active,
            'capacity' => $event->capacity,
            'requested_quantity' => $requestedQuantity,
            'reserved' => (int) $reserved,
            'remaining_capacity' => $remainingCapacity,
            'message' => $this->availabilityMessage(
                $event,
                $serviceAvailable,
                $hasOtherVendorBooking,
                $remainingCapacity,
                $requestedDate,
                $vendorDayOff
            ),
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
            $vendorDayOff = VendorDayOff::resolve($event, $dateString);
            $hasOtherVendorBooking = $this->vendorHasAnotherBookedEvent($event, $dateString);
            $isAvailable = $event->is_active
                && ! $hasExistingBooking
                && ! ($vendorDayOff['is_day_off'] ?? false)
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
                'has_vendor_day_off' => (bool) ($vendorDayOff['is_day_off'] ?? false),
                'vendor_day_off_scope' => $vendorDayOff['scope'] ?? null,
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
            'customer_email' => ['nullable', 'email', 'max:255', 'required_without:customer_phone'],
            'customer_phone' => ['nullable', 'string', 'max:20', 'required_without:customer_email'],
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

        $validated['customer_email'] = ContactIdentity::normalizeEmail($validated['customer_email'] ?? null);
        $validated['customer_phone'] = ContactIdentity::normalizePhone($validated['customer_phone'] ?? null);

        if ($entry = $this->resolveBlacklistedEntry(
            $request->user()?->id,
            $validated['customer_email'] ?? null,
            $validated['customer_phone'] ?? null,
        )) {
            return response()->json([
                'message' => IdentityBlacklist::blockedMessage(
                    $entry,
                    'This email or phone number has been blacklisted. Please contact the admin for approval.'
                ),
            ], 422);
        }

        $eventId = $validated['event_id'] ?? null;
        $event = $eventId ? Event::with('vendor:id,name,email')->findOrFail($eventId) : null;
        $totalAmount = 0.0;
        $requestedDate = $validated['requested_event_date'] ?? $event?->starts_at?->toDateString();

        if ($event) {
            if (! $event->is_active) {
                return response()->json(['message' => 'This event is not available for booking.'], 422);
            }

            $vendorDayOff = VendorDayOff::resolve($event, $requestedDate);
            if ($vendorDayOff['is_day_off'] ?? false) {
                return response()->json([
                    'message' => $vendorDayOff['message'] ?? 'Vendor is unavailable on the selected date.',
                ], 422);
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

            $requestedTotalAmount = array_key_exists('total_amount', $validated)
                ? (float) $validated['total_amount']
                : null;
            $totalAmount = $this->resolveEventBookingTotal(
                $event,
                (int) $validated['quantity'],
                $requestedTotalAmount
            );
        } else {
            $totalAmount = round((float) ($validated['total_amount'] ?? 0), 2);
            if ($totalAmount <= 0) {
                return response()->json([
                    'message' => 'total_amount is required when event_id is not provided.',
                ], 422);
            }
        }

        $paymentToken = Str::uuid()->toString();
        $financials = $this->resolveBookingFinancials($totalAmount);
        $booking = Booking::create([
            ...$validated,
            'user_id' => $request->user() ? $request->user()->id : null,
            'status' => 'pending',
            'payment_status' => 'pending',
            'deposit_percent' => $financials['deposit_percent'],
            'deposit_amount' => $financials['deposit_amount'],
            'service_fee_amount' => $financials['service_fee_amount'],
            'balance_due_amount' => $financials['balance_due_amount'],
            'refund_amount' => 0,
            'vendor_penalty_amount' => 0,
            'customer_compensation_amount' => 0,
            'admin_compensation_amount' => 0,
            'payment_token' => $paymentToken,
            'vendor_cancellation_deadline_at' => now()->addDays(self::VENDOR_CANCELLATION_GRACE_DAYS),
            'service_name' => $validated['service_name'] ?? ($event?->title ?? 'Custom Booking'),
            'requested_event_date' => $requestedDate,
            'total_amount' => $totalAmount,
            'booked_items' => $validated['booked_items'] ?? null,
        ]);

        $booking->setRelation('event', $event);
        $this->flushVendorCacheForBooking($booking);

        return response()->json(
            $booking
                ->load('event.vendor:id,name,email')
                ->makeVisible('payment_token'),
            201
        );
    }

    public function show(Booking $booking): JsonResponse
    {
        return response()->json($booking->load([
            'event.vendor:id,name,email',
            'rating:id,booking_id,rating,review,updated_at',
            'user:id,name,email,phone,location',
        ]));
    }

    public function update(Request $request, Booking $booking): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'status' => ['sometimes', Rule::in(['pending', 'confirmed', 'cancelled'])],
            'customer_name' => ['sometimes', 'string', 'max:255'],
            'customer_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'customer_phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'customer_location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'service_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'requested_event_type' => ['sometimes', 'nullable', 'string', 'max:60'],
            'total_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
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

        $requestedTotalAmount = array_key_exists('total_amount', $validated)
            ? (float) $validated['total_amount']
            : null;

        $resolvedTotalAmount = $event
            ? $this->resolveEventBookingTotal(
                $event,
                (int) $newQuantity,
                $requestedTotalAmount,
                $booking,
                (int) $booking->quantity
            )
            : ($requestedTotalAmount !== null ? round($requestedTotalAmount, 2) : $booking->total_amount);
        $financials = $this->resolveBookingFinancials((float) $resolvedTotalAmount);

        $booking->update([
            ...$validated,
            'total_amount' => $resolvedTotalAmount,
            'deposit_percent' => $financials['deposit_percent'],
            'deposit_amount' => $financials['deposit_amount'],
            'service_fee_amount' => $financials['service_fee_amount'],
            'balance_due_amount' => $financials['balance_due_amount'],
        ]);
        $this->flushVendorCacheForBooking($booking);

        $updatedBooking = $booking->fresh()->load('event.vendor:id,name,email');

        if (array_key_exists('status', $validated) && $validated['status'] !== $previousStatus) {
            $this->createBookingStatusNotifications($updatedBooking, $validated['status']);
        }

        return response()->json($updatedBooking->load([
            'event.vendor:id,name',
            'rating:id,booking_id,rating,review,updated_at',
            'user:id,name,email,phone,location',
        ]));
    }

    public function confirmPayment(Request $request, Booking $booking): JsonResponse
    {
        $validated = $request->validate([
            'payment_token' => ['nullable', 'string', 'max:120'],
            'payment_method' => ['nullable', 'string', 'max:40'],
            'payment_reference' => ['nullable', 'string', 'max:120'],
            'user_id' => ['nullable', 'integer', 'min:1'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
        ]);

        $booking->loadMissing([
            'event.vendor:id,name,email',
            'user:id,name,email,phone',
        ]);

        if ($entry = $this->resolveBlacklistedEntry(
            $booking->user_id ? (int) $booking->user_id : null,
            $booking->customer_email,
            $booking->customer_phone
        )) {
            return response()->json([
                'message' => IdentityBlacklist::blockedMessage(
                    $entry,
                    'This email or phone number has been blacklisted. Please contact the admin for approval.'
                ),
            ], 403);
        }

        $providedToken = trim((string) ($validated['payment_token'] ?? ''));
        $storedToken = (string) ($booking->payment_token ?? '');

        if ($providedToken !== '') {
            if ($storedToken === '' || ! hash_equals($storedToken, $providedToken)) {
                return response()->json(['message' => 'Invalid payment token.'], 403);
            }
        } else {
            /** @var \App\Models\User|null $user */
            $user = $request->user();

            $canAccess = false;
            if ($user instanceof User) {
                $canAccess = $this->canUserAccessBooking($user, $booking);
            } else {
                $customerEmail = isset($validated['customer_email'])
                    ? strtolower(trim((string) $validated['customer_email']))
                    : null;
                $customerPhoneVariants = $this->bookingPhoneLookupVariants($validated['customer_phone'] ?? null);
                $userId = isset($validated['user_id']) ? (int) $validated['user_id'] : null;

                if (! $userId && ! $customerEmail && ! $customerPhoneVariants) {
                    return response()->json(['message' => 'Unauthenticated.'], 401);
                }

                $canAccess = $this->canIdentityAccessBooking(
                    $booking,
                    $userId,
                    $customerEmail,
                    $customerPhoneVariants,
                );
            }

            if (! $canAccess) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
        }

        if ((string) ($booking->status ?? 'pending') === 'cancelled') {
            return response()->json([
                'message' => 'Cancelled bookings cannot be paid.',
            ], 422);
        }

        $wasUnpaid = $booking->payment_status !== 'confirmed';

        if ($booking->payment_status !== 'confirmed') {
            $booking->update([
                'payment_status' => 'confirmed',
                'payment_method' => $validated['payment_method'] ?? $booking->payment_method,
                'payment_reference' => $validated['payment_reference'] ?? $booking->payment_reference,
                'paid_at' => now(),
                'vendor_cancellation_deadline_at' => $booking->vendor_cancellation_deadline_at
                    ?: ($booking->created_at
                        ? $booking->created_at->copy()->addDays(self::VENDOR_CANCELLATION_GRACE_DAYS)
                        : now()->addDays(self::VENDOR_CANCELLATION_GRACE_DAYS)),
            ]);
        }

        $updatedBooking = $booking->fresh()->load('event.vendor:id,name,email');

        if ($wasUnpaid && $updatedBooking->payment_status === 'confirmed') {
            $this->createBookingCreatedNotifications($updatedBooking);
            $this->flushVendorCacheForBooking($updatedBooking);
        }

        return response()->json($updatedBooking->load([
            'event.vendor:id,name',
            'rating:id,booking_id,rating,review,updated_at',
            'user:id,name,email',
        ]));
    }

    public function destroy(Booking $booking): JsonResponse
    {
        $this->flushVendorCacheForBooking($booking);
        $this->flushNotificationCacheForBooking($booking);
        $booking->delete();

        return response()->json(null, 204);
    }

    public function destroyForUser(Request $request, Booking $booking): JsonResponse
    {
        $booking->loadMissing([
            'event:id,title,starts_at,location,vendor_id',
            'event.vendor:id,name,email',
            'user:id,name,email,phone',
        ]);

        /** @var \App\Models\User|null $user */
        $user = $request->user();

        $canAccess = false;

        if ($user instanceof User) {
            $canAccess = $this->canUserAccessBooking($user, $booking);
        } else {
            $validated = $request->validate([
                'user_id' => ['nullable', 'integer', 'min:1'],
                'customer_email' => ['nullable', 'email', 'max:255'],
                'customer_phone' => ['nullable', 'string', 'max:20'],
            ]);

            $customerEmail = isset($validated['customer_email'])
                ? strtolower(trim((string) $validated['customer_email']))
                : null;
            $customerPhoneVariants = $this->bookingPhoneLookupVariants($validated['customer_phone'] ?? null);
            $userId = isset($validated['user_id']) ? (int) $validated['user_id'] : null;

            if (! $userId && ! $customerEmail && ! $customerPhoneVariants) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            $canAccess = $this->canIdentityAccessBooking(
                $booking,
                $userId,
                $customerEmail,
                $customerPhoneVariants,
            );
        }

        if (! $canAccess) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if (! $this->canCustomerDeleteBooking($booking)) {
            return response()->json(['message' => 'Only completed bookings can be deleted.'], 422);
        }

        $this->flushVendorCacheForBooking($booking);
        $this->flushNotificationCacheForBooking($booking);
        $booking->delete();

        return response()->json(null, 204);
    }

    public function cancelForUser(Request $request, Booking $booking): JsonResponse
    {
        $booking->loadMissing([
            'event:id,title,starts_at,location,vendor_id',
            'event.vendor:id,name,email',
            'rating:id,booking_id,rating,review,updated_at',
            'user:id,name,email,phone',
        ]);

        [$canAccess, $identityError] = $this->resolveCustomerBookingAccess($request, $booking);

        if ($identityError instanceof JsonResponse) {
            return $identityError;
        }

        if (! $canAccess) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ((string) ($booking->status ?? 'pending') === 'cancelled') {
            return response()->json(['message' => 'This booking is already cancelled.'], 422);
        }

        if (! $this->canCustomerCancelBooking($booking)) {
            return response()->json(['message' => 'Only active upcoming bookings can be cancelled.'], 422);
        }

        $cancelledAt = now();
        $booking->update([
            'status' => 'cancelled',
            ...$this->resolveCustomerCancellationSettlement($booking, $cancelledAt),
            'cancelled_at' => $cancelledAt,
            'cancellation_actor' => 'customer',
        ]);

        $this->flushVendorCacheForBooking($booking);
        $updatedBooking = $booking->fresh()->load([
            'event:id,title,starts_at,location,vendor_id',
            'event.vendor:id,name,email',
            'rating:id,booking_id,rating,review,updated_at',
            'user:id,name,email,phone',
        ]);
        $this->createBookingStatusNotifications($updatedBooking, 'cancelled');
        $this->createCustomerCancellationChatMessage($updatedBooking);

        return response()->json($updatedBooking);
    }

    public function upsertRatingForUser(Request $request, Booking $booking): JsonResponse
    {
        $booking->loadMissing([
            'event:id,vendor_id,starts_at',
            'rating:id,booking_id,rating,review,updated_at',
            'user:id,name,email,phone',
        ]);

        [$canAccess, $identityError] = $this->resolveCustomerBookingAccess($request, $booking);

        if ($identityError instanceof JsonResponse) {
            return $identityError;
        }

        if (! $canAccess) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if (! $booking->event_id || ! $booking->event || ! $booking->event->vendor_id) {
            return response()->json([
                'message' => 'Only live vendor services can be rated.',
            ], 422);
        }

        if (! $this->canCustomerRateBooking($booking)) {
            return response()->json([
                'message' => 'You can rate this service only after it is completed or cancelled.',
            ], 422);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'max:1000'],
        ]);

        $review = trim((string) ($validated['review'] ?? ''));

        $booking->rating()->updateOrCreate(
            [],
            [
                'event_id' => (int) $booking->event_id,
                'vendor_id' => (int) $booking->event->vendor_id,
                'user_id' => $booking->user_id ? (int) $booking->user_id : null,
                'rating' => (int) $validated['rating'],
                'review' => $review !== '' ? $review : null,
            ]
        );

        PublicEventCache::invalidate();

        return response()->json($booking->fresh()->load([
            'event:id,title,event_type,image_url,qr_code_url,starts_at,location,vendor_id,service_mode',
            'event.vendor:id,name,email,profile_image_url',
            'rating:id,booking_id,rating,review,updated_at',
            'user:id,name,email,phone,location,profile_image_url',
        ]));
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

    private function resolveEventBookingTotal(
        Event $event,
        int $quantity,
        ?float $requestedTotalAmount = null,
        ?Booking $currentBooking = null,
        ?int $previousQuantity = null
    ): float {
        $baseTotalAmount = $this->calculateTotal($this->resolveEventUnitPrice($event), $quantity);

        if ($requestedTotalAmount !== null && $requestedTotalAmount > 0) {
            return max($baseTotalAmount, round($requestedTotalAmount, 2));
        }

        if ($currentBooking && $previousQuantity && $previousQuantity > 0) {
            $currentTotalAmount = round((float) $currentBooking->total_amount, 2);

            if ($quantity !== $previousQuantity) {
                $scaledTotalAmount = round(($currentTotalAmount / $previousQuantity) * $quantity, 2);

                return max($baseTotalAmount, $scaledTotalAmount);
            }

            return max($baseTotalAmount, $currentTotalAmount);
        }

        return $baseTotalAmount;
    }

    private function resolveEventUnitPrice(Event $event): float
    {
        if (($event->service_mode ?? null) !== 'package' || ! is_array($event->packages)) {
            return (float) $event->price;
        }

        $packageTotal = round(array_reduce($event->packages, function ($sum, $package) {
            return $sum + (float) ($package['price'] ?? 0);
        }, 0), 2);

        return $packageTotal > 0 ? $packageTotal : (float) $event->price;
    }

    /**
     * @return array<int, string>
     */
    private function bookingPhoneLookupVariants(?string $phone): array
    {
        $trimmed = trim((string) $phone);
        if ($trimmed === '') {
            return [];
        }

        $digits = preg_replace('/\D+/', '', $trimmed);
        $variants = [$trimmed];

        if (is_string($digits) && $digits !== '') {
            $variants[] = $digits;
            $variants[] = '+'.$digits;

            if (str_starts_with($digits, '0') && strlen($digits) > 1) {
                $variants[] = '+855'.substr($digits, 1);
            }

            if (str_starts_with($digits, '855') && strlen($digits) > 3) {
                $variants[] = '0'.substr($digits, 3);
            }
        }

        return array_values(array_unique(array_filter(array_map(
            static fn ($value) => trim((string) $value),
            $variants
        ))));
    }

    private function resolveBlacklistedEntry(
        ?int $userId = null,
        ?string $customerEmail = null,
        ?string $customerPhone = null,
    ) {
        $user = null;

        if ($userId && $userId > 0) {
            $user = User::query()
                ->select(['id', 'email', 'phone'])
                ->find($userId);
        }

        return IdentityBlacklist::findActiveEntry(
            $user?->email ?? $customerEmail,
            $user?->phone ?? $customerPhone,
        );
    }

    private function canUserAccessBooking(User $user, Booking $booking): bool
    {
        $userEmail = strtolower(trim((string) ($user->email ?? '')));
        $userPhoneVariants = $this->bookingPhoneLookupVariants($user->phone ?? null);

        return $this->canIdentityAccessBooking(
            $booking,
            (int) $user->id,
            $userEmail !== '' ? $userEmail : null,
            $userPhoneVariants,
        );
    }

    private function resolveCustomerBookingAccess(Request $request, Booking $booking): array
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if ($user instanceof User) {
            return [$this->canUserAccessBooking($user, $booking), null];
        }

        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'min:1'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
        ]);

        $customerEmail = isset($validated['customer_email'])
            ? strtolower(trim((string) $validated['customer_email']))
            : null;
        $customerPhoneVariants = $this->bookingPhoneLookupVariants($validated['customer_phone'] ?? null);
        $userId = isset($validated['user_id']) ? (int) $validated['user_id'] : null;

        if (! $userId && ! $customerEmail && ! $customerPhoneVariants) {
            return [false, response()->json(['message' => 'Unauthenticated.'], 401)];
        }

        return [
            $this->canIdentityAccessBooking(
                $booking,
                $userId,
                $customerEmail,
                $customerPhoneVariants,
            ),
            null,
        ];
    }

    private function canIdentityAccessBooking(
        Booking $booking,
        ?int $userId = null,
        ?string $customerEmail = null,
        array $customerPhoneVariants = [],
    ): bool {
        if ($userId && (int) ($booking->user_id ?? 0) === $userId) {
            return true;
        }

        $bookingEmail = strtolower(trim((string) ($booking->customer_email ?? $booking->user?->email ?? '')));
        if ($customerEmail && $bookingEmail !== '' && $bookingEmail === strtolower(trim($customerEmail))) {
            return true;
        }

        $bookingPhoneVariants = array_values(array_unique(array_merge(
            $this->bookingPhoneLookupVariants($booking->customer_phone ?? null),
            $this->bookingPhoneLookupVariants($booking->user?->phone ?? null),
        )));

        return count(array_intersect($customerPhoneVariants, $bookingPhoneVariants)) > 0;
    }

    private function canCustomerDeleteBooking(Booking $booking): bool
    {
        return $this->hasBookingOccurred($booking);
    }

    private function canCustomerCancelBooking(Booking $booking): bool
    {
        $status = strtolower((string) ($booking->status ?? 'pending'));
        if (! in_array($status, ['pending', 'confirmed'], true)) {
            return false;
        }

        if ($booking->requested_event_date) {
            return Carbon::parse($booking->requested_event_date)->startOfDay()->gt(Carbon::today());
        }

        $event = $booking->relationLoaded('event')
            ? $booking->event
            : $booking->event()->select(['id', 'starts_at'])->first();

        if ($event?->starts_at) {
            return Carbon::parse($event->starts_at)->gt(now());
        }

        return true;
    }

    private function canCustomerRateBooking(Booking $booking): bool
    {
        $status = strtolower((string) ($booking->status ?? 'pending'));

        if ($status === 'cancelled') {
            return true;
        }

        if ($status !== 'confirmed') {
            return false;
        }

        return $this->hasBookingOccurred($booking);
    }

    private function hasBookingOccurred(Booking $booking): bool
    {
        if ($booking->requested_event_date) {
            return Carbon::parse($booking->requested_event_date)->startOfDay()->lte(Carbon::today());
        }

        $event = $booking->relationLoaded('event')
            ? $booking->event
            : $booking->event()->select(['id', 'starts_at'])->first();

        if ($event?->starts_at) {
            return Carbon::parse($event->starts_at)->lte(now());
        }

        return false;
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
                    fn ($query) => $this
                        ->applyCommittedBookingScope($query)
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
                fn ($query) => $this->applyCommittedBookingScope($query)
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
        ?string $requestedDate = null,
        array $vendorDayOff = []
    ): string {
        if (! $event->is_active) {
            return 'Service is not active right now.';
        }

        if ($vendorDayOff['is_day_off'] ?? false) {
            return $vendorDayOff['message'] ?? 'Vendor is unavailable on the selected date.';
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
        $query = Booking::query()
            ->where('event_id', $event->id)
            ->when($ignoreBookingId, fn ($builder) => $builder->where('id', '!=', $ignoreBookingId));

        $this->applyCommittedBookingScope($query);

        return (int) $query
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
        $query = Booking::query()
            ->where('event_id', $event->id)
            ->when($ignoreBookingId, fn ($builder) => $builder->where('id', '!=', $ignoreBookingId));

        $this->applyCommittedBookingScope($query);

        return $query
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

    private function applyCommittedBookingScope($query)
    {
        return $query->where(function ($committedQuery) {
            $committedQuery
                ->where('status', 'confirmed')
                ->orWhere(function ($pendingPaidQuery) {
                    $pendingPaidQuery
                        ->where('status', 'pending')
                        ->where('payment_status', 'confirmed');
                });
        });
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
        $depositAmount = $this->formatCurrency($booking->deposit_amount);
        $serviceFeeAmount = $this->formatCurrency($booking->service_fee_amount);

        BookingNotification::create([
            'booking_id' => $booking->id,
            'recipient_type' => 'user',
            'recipient_user_id' => $booking->user_id,
            'recipient_email' => strtolower((string) $booking->customer_email),
            'kind' => 'booking_created',
            'title' => 'Booking request received',
            'message' => "Your booking for {$serviceName} on {$eventDate} was sent to the vendor after your {$depositAmount} deposit and {$serviceFeeAmount} service fee payment.",
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
            'title' => 'New paid booking request',
            'message' => "{$booking->customer_name} booked {$booking->quantity} seat(s) for {$serviceName} and paid the 30% deposit plus service fee.",
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
        $userMessage = "Your booking for {$serviceName} on {$eventDate} is now {$statusLabel}.";
        $vendorMessage = "Booking #{$booking->id} for {$serviceName} is now {$statusLabel}.";

        if ($nextStatus === 'confirmed') {
            $remainingBalance = $this->formatCurrency($booking->balance_due_amount);
            $userMessage = "Your booking for {$serviceName} on {$eventDate} has been approved by the vendor. Remaining balance due on service day: {$remainingBalance}.";
            $vendorMessage = "Booking #{$booking->id} for {$serviceName} has been approved.";
        } elseif ($nextStatus === 'cancelled' && (float) $booking->refund_amount > 0) {
            $refundAmount = $this->formatCurrency($booking->refund_amount);
            $customerCompensation = (float) $booking->customer_compensation_amount > 0
                ? $this->formatCurrency($booking->customer_compensation_amount)
                : null;
            $cancelledByCustomer = strtolower((string) ($booking->cancellation_actor ?? '')) === 'customer';

            $userMessage = $cancelledByCustomer
                ? "Your booking for {$serviceName} on {$eventDate} was cancelled by you. Refund due: {$refundAmount}."
                : "Your booking for {$serviceName} on {$eventDate} was cancelled. Refund due: {$refundAmount}.";

            if ($customerCompensation !== null) {
                $userMessage .= " Additional customer compensation due: {$customerCompensation}.";
            }

            $vendorMessage = $cancelledByCustomer
                ? "Booking #{$booking->id} for {$serviceName} was cancelled by the customer."
                : $vendorMessage;
        }

        BookingNotification::create([
            'booking_id' => $booking->id,
            'recipient_type' => 'user',
            'recipient_user_id' => $booking->user_id,
            'recipient_email' => strtolower((string) $booking->customer_email),
            'kind' => 'booking_status_changed',
            'title' => "Booking {$statusLabel}",
            'message' => $userMessage,
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
            'message' => $vendorMessage,
        ]);
        $this->flushNotificationCache(
            'vendor',
            $event->vendor_id,
            $event->vendor ? strtolower((string) $event->vendor->email) : null
        );
    }

    private function resolveBookingFinancials(float $totalAmount): array
    {
        $depositPercent = round(self::DEPOSIT_PERCENT, 2);
        $depositAmount = round($totalAmount * ($depositPercent / 100), 2);
        $serviceFeeAmount = round($totalAmount * self::SERVICE_FEE_RATE, 2);
        $balanceDueAmount = round(max($totalAmount - $depositAmount, 0), 2);

        return [
            'deposit_percent' => $depositPercent,
            'deposit_amount' => $depositAmount,
            'service_fee_amount' => $serviceFeeAmount,
            'balance_due_amount' => $balanceDueAmount,
        ];
    }

    private function resolveCustomerCancellationSettlement(Booking $booking, Carbon $cancelledAt): array
    {
        $initialPaymentAmount = round((float) $booking->deposit_amount + (float) $booking->service_fee_amount, 2);
        $deadline = $booking->vendor_cancellation_deadline_at
            ? Carbon::parse($booking->vendor_cancellation_deadline_at)
            : ($booking->paid_at
                ? Carbon::parse($booking->paid_at)->addDays(self::VENDOR_CANCELLATION_GRACE_DAYS)
                : ($booking->created_at
                    ? Carbon::parse($booking->created_at)->addDays(self::VENDOR_CANCELLATION_GRACE_DAYS)
                    : null));
        $isLateCancellation = $initialPaymentAmount > 0 && $deadline instanceof Carbon && $cancelledAt->gt($deadline);
        $refundAmount = $isLateCancellation
            ? round($initialPaymentAmount * self::LATE_CUSTOMER_CANCELLATION_REFUND_RATE, 2)
            : $initialPaymentAmount;

        return [
            'refund_amount' => $refundAmount,
            'vendor_penalty_amount' => 0,
            'customer_compensation_amount' => 0,
            'admin_compensation_amount' => 0,
            'payment_status' => $initialPaymentAmount > 0 ? 'refunded' : $booking->payment_status,
        ];
    }

    private function createCustomerCancellationChatMessage(Booking $booking): void
    {
        $event = $booking->relationLoaded('event')
            ? $booking->event
            : $booking->event()->with('vendor:id,name,email')->select(['id', 'vendor_id', 'title', 'starts_at'])->first();

        if (! $event?->vendor_id || ! $booking->customer_email) {
            return;
        }

        $email = strtolower(trim((string) $booking->customer_email));
        $serviceName = $booking->service_name ?: ($event->title ?: 'Service Booking');
        $conversation = ChatConversation::query()->firstOrCreate(
            [
                'vendor_user_id' => (int) $event->vendor_id,
                'customer_email' => $email,
                'booking_id' => null,
            ],
            [
                'customer_user_id' => $booking->user_id,
                'customer_name' => $booking->customer_name,
                'service_name' => $serviceName,
            ],
        );

        $conversation->fill([
            'customer_user_id' => $booking->user_id ?: $conversation->customer_user_id,
            'customer_name' => $booking->customer_name ?: $conversation->customer_name,
            'service_name' => $serviceName,
        ]);

        if ($conversation->isDirty()) {
            $conversation->save();
        }

        $eventDate = $booking->requested_event_date
            ? Carbon::parse($booking->requested_event_date)->format('M d, Y')
            : ($event->starts_at ? Carbon::parse($event->starts_at)->format('M d, Y g:i A') : 'the scheduled date');
        $refundAmount = $this->formatCurrency($booking->refund_amount);
        $body = "Cancellation request sent for {$serviceName} on {$eventDate}. Booking #{$booking->id}. Refund due to customer: {$refundAmount}.";

        ChatMessage::query()->create([
            'conversation_id' => $conversation->id,
            'sender_user_id' => $booking->user_id,
            'sender_role' => 'customer',
            'sender_name' => $booking->customer_name ?: 'Customer',
            'body' => $body,
        ]);

        $conversation->update([
            'last_message_at' => now(),
        ]);
    }

    private function formatCurrency($value): string
    {
        return '$'.number_format((float) $value, 2);
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

    private function flushNotificationCacheForBooking(Booking $booking): void
    {
        $event = $booking->relationLoaded('event')
            ? $booking->event
            : $booking->event()
                ->with('vendor:id,email')
                ->select(['id', 'vendor_id'])
                ->first();

        if ($event && ! $event->relationLoaded('vendor')) {
            $event->loadMissing('vendor:id,email');
        }

        $this->flushNotificationCache(
            'user',
            $booking->user_id ? (int) $booking->user_id : null,
            $booking->customer_email ? strtolower(trim((string) $booking->customer_email)) : null
        );

        $this->flushNotificationCache(
            'vendor',
            $event?->vendor_id ? (int) $event->vendor_id : null,
            $event?->vendor?->email ? strtolower(trim((string) $event->vendor->email)) : null
        );
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
