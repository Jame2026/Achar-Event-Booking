<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(): JsonResponse
    {
        $events = Event::query()
            ->latest('starts_at')
            ->paginate(15);

        return response()->json($events);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $event = Event::create($validated);

        return response()->json($event, 201);
    }

    public function show(Event $event): JsonResponse
    {
        $event->loadCount('bookings');

        return response()->json($event);
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
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

    public function destroy(Event $event): JsonResponse
    {
        $event->delete();

        return response()->noContent();
    }
}
