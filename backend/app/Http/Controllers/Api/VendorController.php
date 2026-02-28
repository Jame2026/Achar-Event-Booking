<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    // Get default vendor user (for public API testing)
    private function getDefaultVendor(): User
    {
        $vendor = User::where('role', 'vendor')->first();
        if (!$vendor) {
            $vendor = User::create([
                'name' => 'Default Vendor',
                'email' => 'vendor@achar.com',
                'password' => bcrypt('password'),
                'role' => 'vendor',
            ]);
        }
        return $vendor;
    }

    public function publicServices(): JsonResponse
    {
        $vendor = $this->getDefaultVendor();
        $services = $vendor->events()
            ->select('id', 'title', 'event_type', 'description', 'location', 'starts_at', 'ends_at', 'price', 'capacity', 'is_active', 'created_at')
            ->latest('created_at')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->title,
                    'category' => $event->event_type,
                    'description' => $event->description,
                    'location' => $event->location,
                    'startDate' => $event->starts_at,
                    'endDate' => $event->ends_at,
                    'price' => $event->price,
                    'minGuests' => 10,
                    'maxGuests' => $event->capacity,
                    'autoConfirm' => $event->is_active,
                    'created_at' => $event->created_at,
                ];
            });

        return response()->json($services);
    }

    public function publicStoreService(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'startDate' => ['required', 'date'],
            'endDate' => ['nullable', 'date'],
            'price' => ['required', 'numeric', 'min:0'],
            'minGuests' => ['nullable', 'integer', 'min:1'],
            'maxGuests' => ['required', 'integer', 'min:1'],
            'autoConfirm' => ['sometimes', 'boolean'],
        ]);

        $vendor = $this->getDefaultVendor();
        
        $event = $vendor->events()->create([
            'title' => $validated['name'],
            'event_type' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'location' => $validated['location'],
            'starts_at' => $validated['startDate'],
            'ends_at' => $validated['endDate'] ?? null,
            'price' => $validated['price'],
            'capacity' => $validated['maxGuests'],
            'is_active' => $validated['autoConfirm'] ?? true,
        ]);

        return response()->json([
            'id' => $event->id,
            'name' => $event->title,
            'category' => $event->event_type,
            'description' => $event->description,
            'location' => $event->location,
            'startDate' => $event->starts_at,
            'endDate' => $event->ends_at,
            'price' => $event->price,
            'minGuests' => 10,
            'maxGuests' => $event->capacity,
            'autoConfirm' => $event->is_active,
        ], 201);
    }

    public function publicUpdateService(Request $request, int $id): JsonResponse
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'category' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['sometimes', 'string', 'max:255'],
            'startDate' => ['sometimes', 'date'],
            'endDate' => ['nullable', 'date'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'minGuests' => ['nullable', 'integer', 'min:1'],
            'maxGuests' => ['sometimes', 'integer', 'min:1'],
            'autoConfirm' => ['sometimes', 'boolean'],
        ]);

        $updateData = [];
        if (isset($validated['name'])) $updateData['title'] = $validated['name'];
        if (isset($validated['category'])) $updateData['event_type'] = $validated['category'];
        if (array_key_exists('description', $validated)) $updateData['description'] = $validated['description'];
        if (isset($validated['location'])) $updateData['location'] = $validated['location'];
        if (isset($validated['startDate'])) $updateData['starts_at'] = $validated['startDate'];
        if (array_key_exists('endDate', $validated)) $updateData['ends_at'] = $validated['endDate'];
        if (isset($validated['price'])) $updateData['price'] = $validated['price'];
        if (isset($validated['maxGuests'])) $updateData['capacity'] = $validated['maxGuests'];
        if (isset($validated['autoConfirm'])) $updateData['is_active'] = $validated['autoConfirm'];

        $event->update($updateData);

        return response()->json([
            'id' => $event->id,
            'name' => $event->title,
            'category' => $event->event_type,
            'description' => $event->description,
            'location' => $event->location,
            'startDate' => $event->starts_at,
            'endDate' => $event->ends_at,
            'price' => $event->price,
            'minGuests' => 10,
            'maxGuests' => $event->capacity,
            'autoConfirm' => $event->is_active,
        ]);
    }

    public function publicDestroyService(int $id): JsonResponse
    {
        $event = Event::findOrFail($id);
        $event->delete();

        return response()->noContent();
    }

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

    // Services API - maps frontend fields to backend Event model
    public function myServices(Request $request): JsonResponse
    {
        $services = $request->user()
            ->events()
            ->select('id', 'title', 'event_type', 'description', 'location', 'starts_at', 'ends_at', 'price', 'capacity', 'is_active', 'created_at')
            ->latest('created_at')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->title,
                    'category' => $event->event_type,
                    'description' => $event->description,
                    'location' => $event->location,
                    'startDate' => $event->starts_at,
                    'endDate' => $event->ends_at,
                    'price' => $event->price,
                    'minGuests' => 10,
                    'maxGuests' => $event->capacity,
                    'autoConfirm' => $event->is_active,
                    'created_at' => $event->created_at,
                ];
            });

        return response()->json($services);
    }

    public function storeService(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'startDate' => ['required', 'date'],
            'endDate' => ['nullable', 'date'],
            'price' => ['required', 'numeric', 'min:0'],
            'minGuests' => ['nullable', 'integer', 'min:1'],
            'maxGuests' => ['required', 'integer', 'min:1'],
            'autoConfirm' => ['sometimes', 'boolean'],
        ]);

        $event = $request->user()->events()->create([
            'title' => $validated['name'],
            'event_type' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'location' => $validated['location'],
            'starts_at' => $validated['startDate'],
            'ends_at' => $validated['endDate'] ?? null,
            'price' => $validated['price'],
            'capacity' => $validated['maxGuests'],
            'is_active' => $validated['autoConfirm'] ?? true,
        ]);

        return response()->json([
            'id' => $event->id,
            'name' => $event->title,
            'category' => $event->event_type,
            'description' => $event->description,
            'location' => $event->location,
            'startDate' => $event->starts_at,
            'endDate' => $event->ends_at,
            'price' => $event->price,
            'minGuests' => 10,
            'maxGuests' => $event->capacity,
            'autoConfirm' => $event->is_active,
        ], 201);
    }

    public function updateService(Request $request, int $id): JsonResponse
    {
        $event = Event::findOrFail($id);
        
        if (! $this->canManageEvent($request, $event)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'category' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['sometimes', 'string', 'max:255'],
            'startDate' => ['sometimes', 'date'],
            'endDate' => ['nullable', 'date'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'minGuests' => ['nullable', 'integer', 'min:1'],
            'maxGuests' => ['sometimes', 'integer', 'min:1'],
            'autoConfirm' => ['sometimes', 'boolean'],
        ]);

        $updateData = [];
        if (isset($validated['name'])) $updateData['title'] = $validated['name'];
        if (isset($validated['category'])) $updateData['event_type'] = $validated['category'];
        if (array_key_exists('description', $validated)) $updateData['description'] = $validated['description'];
        if (isset($validated['location'])) $updateData['location'] = $validated['location'];
        if (isset($validated['startDate'])) $updateData['starts_at'] = $validated['startDate'];
        if (array_key_exists('endDate', $validated)) $updateData['ends_at'] = $validated['endDate'];
        if (isset($validated['price'])) $updateData['price'] = $validated['price'];
        if (isset($validated['maxGuests'])) $updateData['capacity'] = $validated['maxGuests'];
        if (isset($validated['autoConfirm'])) $updateData['is_active'] = $validated['autoConfirm'];

        $event->update($updateData);

        return response()->json([
            'id' => $event->id,
            'name' => $event->title,
            'category' => $event->event_type,
            'description' => $event->description,
            'location' => $event->location,
            'startDate' => $event->starts_at,
            'endDate' => $event->ends_at,
            'price' => $event->price,
            'minGuests' => 10,
            'maxGuests' => $event->capacity,
            'autoConfirm' => $event->is_active,
        ]);
    }

    public function destroyService(Request $request, int $id): JsonResponse
    {
        $event = Event::findOrFail($id);
        
        if (! $this->canManageEvent($request, $event)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $event->delete();

        return response()->noContent();
    }
}
