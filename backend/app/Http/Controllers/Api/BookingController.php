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
    public function index(): JsonResponse
    {
        $bookings = Booking::query()
            ->with(['event:id,title,starts_at,location', 'user:id,name,email'])
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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
        ]);

        $event = Event::findOrFail($validated['event_id']);
        if (! $event->is_active) {
            return response()->json(['message' => 'This event is not available for booking.'], 422);
        }

        if (! $this->hasCapacity($event, $validated['quantity'])) {
            return response()->json(['message' => 'Not enough seats available for this booking.'], 422);
        }

        $booking = Booking::create([
            ...$validated,
            'user_id' => $request->user()?->id,
            'status' => 'pending',
            'total_amount' => $this->calculateTotal($event->price, $validated['quantity']),
        ]);

        return response()->json($booking->load('event:id,title,starts_at,location'), 201);
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

        return response()->json($booking->fresh()->load('event:id,title,starts_at,location'));
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

    private function calculateTotal(string|float|int $price, int $quantity): float
    {
        return round(((float) $price) * $quantity, 2);
    }
}
