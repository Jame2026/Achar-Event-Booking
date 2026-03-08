<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    public function servicesByVendorId(Request $request): JsonResponse
    {
        $vendor = $this->resolveVendorFromRequest($request);
        if ($vendor instanceof JsonResponse) {
            return $vendor;
        }

        $events = Event::query()
            ->where('vendor_id', $vendor->id)
            ->withCount('bookings')
            ->latest('starts_at')
            ->get();

        return response()->json([
            'data' => $events,
        ]);
    }

    public function storeServiceByVendorId(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vendor_user_id' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'event_type' => ['required', Rule::in($this->allowedEventTypes())],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'image' => ['nullable', 'file'],
            'location' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $vendor = $this->resolveVendorFromRequest($request);
        if ($vendor instanceof JsonResponse) {
            return $vendor;
        }

        if ($request->hasFile('image')) {
            $imageValidationError = $this->validateUploadedImage($request->file('image'));
            if ($imageValidationError !== null) {
                return response()->json(['message' => $imageValidationError], 422);
            }

            $validated['image_url'] = $this->storeEventImage($request->file('image'));
        }

        unset($validated['image']);

        $event = Event::create([
            ...$validated,
            'vendor_id' => $vendor->id,
        ]);

        return response()->json($event, 201);
    }

    public function updateServiceByVendorId(Request $request, Event $event): JsonResponse
    {
        $vendor = $this->resolveVendorFromRequest($request);
        if ($vendor instanceof JsonResponse) {
            return $vendor;
        }

        if ((int) $event->vendor_id !== (int) $vendor->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'event_type' => ['sometimes', Rule::in($this->allowedEventTypes())],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'image' => ['nullable', 'file'],
            'location' => ['sometimes', 'string', 'max:255'],
            'starts_at' => ['sometimes', 'date'],
            'ends_at' => ['nullable', 'date'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'capacity' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            $imageValidationError = $this->validateUploadedImage($request->file('image'));
            if ($imageValidationError !== null) {
                return response()->json(['message' => $imageValidationError], 422);
            }

            $validated['image_url'] = $this->storeEventImage($request->file('image'));
        }

        unset($validated['image']);

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

    public function destroyServiceByVendorId(Request $request, Event $event): JsonResponse
    {
        $vendor = $this->resolveVendorFromRequest($request);
        if ($vendor instanceof JsonResponse) {
            return $vendor;
        }

        if ((int) $event->vendor_id !== (int) $vendor->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $event->delete();
        return response()->noContent();
    }

    public function bookingsByVendorId(Request $request): JsonResponse
    {
        $vendor = $this->resolveVendorFromRequest($request);
        if ($vendor instanceof JsonResponse) {
            return $vendor;
        }

        $bookings = Booking::query()
            ->with(['event:id,title,event_type,starts_at,location,vendor_id', 'user:id,name,email'])
            ->whereHas('event', fn ($query) => $query->where('vendor_id', $vendor->id))
            ->latest()
            ->get();

        return response()->json([
            'data' => $bookings,
        ]);
    }

    public function updateBookingStatusByVendorId(Request $request, Booking $booking): JsonResponse
    {
        $vendor = $this->resolveVendorFromRequest($request);
        if ($vendor instanceof JsonResponse) {
            return $vendor;
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'confirmed', 'cancelled'])],
        ]);

        $event = $booking->event;
        if (! $event || (int) $event->vendor_id !== (int) $vendor->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $booking->update([
            'status' => $validated['status'],
        ]);

        return response()->json($booking->fresh()->load(['event:id,title,event_type,starts_at,location,vendor_id', 'user:id,name,email']));
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

    private function resolveVendorFromRequest(Request $request): User|JsonResponse
    {
        $validated = $request->validate([
            'vendor_user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $vendor = User::query()
            ->select(['id', 'role'])
            ->findOrFail((int) $validated['vendor_user_id']);

        if (! in_array($vendor->role, ['vendor', 'admin'], true)) {
            return response()->json(['message' => 'Selected user is not a vendor account.'], 422);
        }

        return $vendor;
    }

    private function storeEventImage(UploadedFile $image): string
    {
        $directory = public_path('uploads/services');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = Str::uuid()->toString().'.'.$image->getClientOriginalExtension();
        $image->move($directory, $filename);

        return url('uploads/services/'.$filename);
    }

    private function validateUploadedImage(UploadedFile $image): ?string
    {
        if (! $image->isValid()) {
            return 'The selected image upload is invalid.';
        }

        if ($image->getSize() > 5 * 1024 * 1024) {
            return 'The service image must not be larger than 5 MB.';
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = Str::lower((string) $image->getClientOriginalExtension());

        if (! in_array($extension, $allowedExtensions, true)) {
            return 'The service image must be a JPG, PNG, GIF, or WEBP file.';
        }

        return null;
    }
}
