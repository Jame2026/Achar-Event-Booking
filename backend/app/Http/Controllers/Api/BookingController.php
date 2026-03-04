<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            ->with(['event:id,title,event_type,starts_at,location', 'user:id,name,email'])
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
            ->with(['event:id,title,event_type,starts_at,location', 'user:id,name,email'])
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

    public function availability(Event $event): JsonResponse
    {
        $reserved = Booking::query()
            ->where('event_id', $event->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->sum('quantity');

        $remainingCapacity = $event->capacity === 0
            ? null
            : max(0, $event->capacity - $reserved);

        $hasOtherVendorBooking = $this->vendorHasAnotherBookedEvent($event);

        $serviceAvailable = $event->is_active
            && ($event->capacity === 0 || $remainingCapacity > 0);

        return response()->json([
            'event_id' => $event->id,
            'service_available' => $serviceAvailable,
            'vendor_available' => ! $hasOtherVendorBooking,
            'is_busy' => ! $serviceAvailable || $hasOtherVendorBooking,
            'has_another_booked' => $hasOtherVendorBooking,
            'is_active' => (bool) $event->is_active,
            'capacity' => $event->capacity,
            'reserved' => (int) $reserved,
            'remaining_capacity' => $remainingCapacity,
            'message' => $this->availabilityMessage($event, $serviceAvailable, $hasOtherVendorBooking, $remainingCapacity),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => ['nullable', 'exists:events,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'service_name' => ['nullable', 'string', 'max:255'],
            'requested_event_type' => ['nullable', 'string', 'max:60'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $eventId = $validated['event_id'] ?? null;
        $event = $eventId ? Event::findOrFail($eventId) : null;
        $totalAmount = 0.0;

        if ($event) {
            if (! $event->is_active) {
                return response()->json(['message' => 'This event is not available for booking.'], 422);
            }

            if (! $this->hasCapacity($event, $validated['quantity'])) {
                return response()->json(['message' => 'Not enough seats available for this booking.'], 422);
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

        $booking = Booking::create([
            ...$validated,
            'user_id' => $request->user() ? $request->user()->id : null,
            'status' => 'pending',
            'service_name' => $validated['service_name'] ?? ($event?->title ?? 'Custom Booking'),
            'total_amount' => $totalAmount,
        ]);

        return response()->json($booking->load('event:id,title,event_type,starts_at,location'), 201);
    }

    public function show(Booking $booking): JsonResponse
    {
        return response()->json($booking->load(['event', 'user:id,name,email']));
    }

    public function update(Request $request, Booking $booking): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'status' => ['sometimes', Rule::in(['pending', 'confirmed', 'cancelled'])],
            'customer_name' => ['sometimes', 'string', 'max:255'],
            'customer_email' => ['sometimes', 'email', 'max:255'],
            'service_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'requested_event_type' => ['sometimes', 'nullable', 'string', 'max:60'],
        ]);

        $event = $booking->event;
        $newQuantity = $validated['quantity'] ?? $booking->quantity;

        if ($event && ! $this->hasCapacity($event, $newQuantity, $booking->id)) {
            return response()->json(['message' => 'Not enough seats available for this update.'], 422);
        }

        $booking->update([
            ...$validated,
            'total_amount' => $event
                ? $this->calculateTotal($event->price, $newQuantity)
                : $booking->total_amount,
        ]);

        return response()->json($booking->fresh()->load('event:id,title,event_type,starts_at,location'));
    }

    public function destroy(Booking $booking): JsonResponse
    {
        $booking->delete();

        return response()->noContent();
    }

    private function hasCapacity(Event $event, int $requestedQuantity, ?int $ignoreBookingId = null): bool
    {
        if ($event->capacity === 0) {
            return true;
        }

        $reserved = Booking::query()
            ->where('event_id', $event->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->when($ignoreBookingId, fn ($query) => $query->where('id', '!=', $ignoreBookingId))
            ->sum('quantity');

        return ($reserved + $requestedQuantity) <= $event->capacity;
    }

    private function calculateTotal($price, int $quantity): float
    {
        return round(((float) $price) * $quantity, 2);
    }

    private function vendorHasAnotherBookedEvent(Event $event): bool
    {
        if (! $event->vendor_id || ! $event->starts_at) {
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
        ?int $remainingCapacity
    ): string {
        if (! $event->is_active) {
            return 'Service is not active right now.';
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
}
