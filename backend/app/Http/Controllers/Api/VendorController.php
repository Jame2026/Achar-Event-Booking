<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $vendor = $request->user();
        $eventIds = $vendor->events()->pluck('id');

        return response()->json([
            'events_count' => $eventIds->count(),
            'bookings_count' => \App\Models\Booking::whereIn('event_id', $eventIds)->count(),
        ]);
    }

    public function myEvents(Request $request): JsonResponse
    {
        $events = $request->user()
            ->events()
            ->withCount('bookings')
            ->latest('starts_at')
            ->paginate(15);

        return response()->json($events);
    }

    public function storeEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'event_type' => ['required', Rule::in($this->allowedEventTypes())],
            'description' => ['nullable', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $event = $request->user()->events()->create($validated);

        return response()->json($event, 201);
    }

    public function updateEvent(Request $request, Event $event): JsonResponse
    {
        if (! $this->canManageEvent($request, $event)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'event_type' => ['sometimes', Rule::in($this->allowedEventTypes())],
            'description' => ['nullable', 'string'],
            'location' => ['sometimes', 'string', 'max:255'],
            'starts_at' => ['sometimes', 'date'],
            'ends_at' => ['nullable', 'date'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'capacity' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('ends_at', $validated) && $validated['ends_at'] !== null) {
            $startsAt = $validated['starts_at'] ?? $event->starts_at;
            if (Carbon::parse($validated['ends_at'])->lt(Carbon::parse($startsAt))) {
                return response()->json([
                    'message' => 'The ends_at field must be a date after or equal to starts_at.',
                ], 422);
            }
        }

        $event->update($validated);

        return response()->json($event->fresh());
    }

    public function destroyEvent(Request $request, Event $event): JsonResponse
    {
        if (! $this->canManageEvent($request, $event)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $event->delete();

        return response()->noContent();
    }

    private function canManageEvent(Request $request, Event $event): bool
    {
        $user = $request->user();

        return $user->isAdmin() || $event->vendor_id === $user->id;
    }

    private function allowedEventTypes(): array
    {
        return [
            'wedding',
            'monk_ceremony',
            'house_blessing',
            'company_party',
            'birthday',
            'school_event',
            'engagement',
            'anniversary',
            'baby_shower',
            'graduation',
            'festival',
            'other',
        ];
    }
}
