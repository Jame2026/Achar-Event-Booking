<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingNotification;
use App\Models\Event;
use App\Models\User;
use App\Models\VendorSetting;
use App\Support\NotificationCache;
use App\Support\VendorCache;
use App\Support\PublicEventCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    public function servicesByVendorId(Request $request): JsonResponse
    {
        $vendor = $this->resolveVendorFromRequest($request);
        if ($vendor instanceof JsonResponse) {
            return $vendor;
        }

        $events = VendorCache::rememberServices($vendor->id, function () use ($vendor) {
            return Event::query()
                ->select([
                    'id',
                    'vendor_id',
                    'title',
                    'event_type',
                    'description',
                    'packages',
                    'qr_code_url',
                    'service_mode',
                    'image_url',
                    'location',
                    'starts_at',
                    'ends_at',
                    'price',
                    'capacity',
                    'is_active',
                ])
                ->where('vendor_id', $vendor->id)
                ->latest('starts_at')
                ->get();
        });

        return response()->json([
            'data' => $events,
        ]);
    }

    public function storeServiceByVendorId(Request $request): JsonResponse
    {
        $this->normalizePackages($request);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'event_type' => ['required', Rule::in($this->allowedEventTypes())],
            'description' => ['nullable', 'string'],
            'packages' => ['nullable', 'array'],
            'packages.*.name' => ['nullable', 'string', 'max:255'],
            'packages.*.price' => ['nullable', 'numeric', 'min:0'],
            'packages.*.details' => ['nullable', 'string'],
            'qr_code_url' => ['nullable', 'string', 'max:2048'],
            'qr_code' => ['nullable', 'file'],
            'service_mode' => ['nullable', Rule::in(['overall', 'package'])],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'image' => ['nullable', 'file'],
            'location' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $validated = $this->syncPackagePrice($validated);

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

        if ($request->hasFile('qr_code')) {
            $imageValidationError = $this->validateUploadedImage($request->file('qr_code'));
            if ($imageValidationError !== null) {
                return response()->json(['message' => $imageValidationError], 422);
            }

            $validated['qr_code_url'] = $this->storeEventImage($request->file('qr_code'));
        }

        unset($validated['image']);
        unset($validated['qr_code']);

        $event = Event::create([
            ...$validated,
            'vendor_id' => $vendor->id,
        ]);
        VendorCache::flushVendor($vendor->id);
        PublicEventCache::invalidate();

        return response()->json($event, 201);
    }

    public function updateServiceByVendorId(Request $request, Event $event): JsonResponse
    {
        $this->normalizePackages($request);
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
            'packages' => ['nullable', 'array'],
            'packages.*.name' => ['nullable', 'string', 'max:255'],
            'packages.*.price' => ['nullable', 'numeric', 'min:0'],
            'packages.*.details' => ['nullable', 'string'],
            'qr_code_url' => ['nullable', 'string', 'max:2048'],
            'qr_code' => ['nullable', 'file'],
            'service_mode' => ['nullable', Rule::in(['overall', 'package'])],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'image' => ['nullable', 'file'],
            'location' => ['sometimes', 'string', 'max:255'],
            'starts_at' => ['sometimes', 'date'],
            'ends_at' => ['nullable', 'date'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'capacity' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $validated = $this->syncPackagePrice($validated);

        if ($request->hasFile('image')) {
            $imageValidationError = $this->validateUploadedImage($request->file('image'));
            if ($imageValidationError !== null) {
                return response()->json(['message' => $imageValidationError], 422);
            }

            $this->deleteStoredEventImage($event->image_url);
            $validated['image_url'] = $this->storeEventImage($request->file('image'));
        }

        if ($request->hasFile('qr_code')) {
            $imageValidationError = $this->validateUploadedImage($request->file('qr_code'));
            if ($imageValidationError !== null) {
                return response()->json(['message' => $imageValidationError], 422);
            }

            $this->deleteStoredEventImage($event->qr_code_url);
            $validated['qr_code_url'] = $this->storeEventImage($request->file('qr_code'));
        }

        unset($validated['image']);
        unset($validated['qr_code']);

        if (array_key_exists('ends_at', $validated) && $validated['ends_at'] !== null) {
            $startsAt = $validated['starts_at'] ?? $event->starts_at;
            if (Carbon::parse($validated['ends_at'])->lt(Carbon::parse($startsAt))) {
                return response()->json([
                    'message' => 'The ends_at field must be a date after or equal to starts_at.',
                ], 422);
            }
        }

        $event->update($validated);
        VendorCache::flushVendor($vendor->id);
        PublicEventCache::invalidate();

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

        $this->deleteManagedEvent($event);
        VendorCache::flushVendor($vendor->id);

        return response()->json(null, 204);
    }

    public function bookingsByVendorId(Request $request): JsonResponse
    {
        $vendor = $this->resolveVendorFromRequest($request);
        if ($vendor instanceof JsonResponse) {
            return $vendor;
        }

        $bookings = VendorCache::rememberBookings($vendor->id, function () use ($vendor) {
            return Booking::query()
                ->with(['event:id,title,event_type,image_url,starts_at,location,vendor_id', 'user:id,name,email,phone,location,profile_image_url'])
                ->whereHas('event', fn ($query) => $query->where('vendor_id', $vendor->id))
                ->latest()
                ->get();
        });

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

        $previousStatus = (string) ($booking->status ?? 'pending');
        $nextStatus = (string) $validated['status'];

        if ($previousStatus !== $nextStatus) {
            $booking->update([
                'status' => $nextStatus,
            ]);
        }

        VendorCache::flushVendor($vendor->id);

        $updatedBooking = $booking->fresh()->load([
            'event:id,title,event_type,image_url,starts_at,location,vendor_id',
            'event.vendor:id,name,email',
            'user:id,name,email,phone,location,profile_image_url',
        ]);

        if ($previousStatus !== $nextStatus) {
            $this->createCustomerBookingStatusNotification($updatedBooking, $nextStatus);
        }

        return response()->json($updatedBooking);
    }

    public function destroyBookingByVendorId(Request $request, Booking $booking): JsonResponse
    {
        $vendor = $this->resolveVendorFromRequest($request);
        if ($vendor instanceof JsonResponse) {
            return $vendor;
        }

        $event = $booking->relationLoaded('event')
            ? $booking->event
            : $booking->event()
                ->with('vendor:id,email')
                ->select(['id', 'vendor_id', 'starts_at'])
                ->first();

        if (! $event || (int) $event->vendor_id !== (int) $vendor->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if (! $this->bookingHasPassed($booking, $event)) {
            return response()->json(['message' => 'Only past bookings can be deleted.'], 422);
        }

        $this->flushNotificationCacheForBooking($booking, $event);
        $booking->delete();
        VendorCache::flushVendor($vendor->id);

        return response()->json(null, 204);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $vendor = $request->user();
        $summary = VendorCache::rememberDashboard((int) $vendor->id, function () use ($vendor) {
            $eventIds = $vendor->events()->pluck('id');

            return [
                'events_count' => $eventIds->count(),
                'bookings_count' => Booking::query()->whereIn('event_id', $eventIds)->count(),
            ];
        });

        return response()->json($summary);
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
            'service_mode' => ['nullable', Rule::in(['overall', 'package'])],
        ]);

        $event = $request->user()->events()->create($validated);
        VendorCache::flushVendor((int) $request->user()->id);

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
            'service_mode' => ['nullable', Rule::in(['overall', 'package'])],
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
        VendorCache::flushVendor((int) $event->vendor_id);

        return response()->json($event->fresh());
    }

    public function destroyEvent(Request $request, Event $event): JsonResponse
    {
        if (! $this->canManageEvent($request, $event)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $vendorId = (int) $event->vendor_id;
        $this->deleteManagedEvent($event);
        VendorCache::flushVendor($vendorId);
        PublicEventCache::invalidate();

        return response()->json(null, 204);
    }

    private function deleteManagedEvent(Event $event): void
    {
        // Clear event-specific setting rows so past services can always be removed cleanly.
        VendorSetting::query()
            ->where('event_id', $event->id)
            ->delete();

        $this->deleteStoredEventImage($event->image_url);
        $this->deleteStoredEventImage($event->qr_code_url);
        $event->delete();
        PublicEventCache::invalidate();
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

    private function normalizePackages(Request $request): void
    {
        $rawPackages = $request->input('packages');
        if (! is_string($rawPackages)) {
            return;
        }

        $decoded = json_decode($rawPackages, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return;
        }

        $request->merge(['packages' => $decoded]);
    }

    private function syncPackagePrice(array $validated): array
    {
        if (($validated['service_mode'] ?? null) !== 'package') {
            return $validated;
        }

        $packages = $validated['packages'] ?? [];
        if (! is_array($packages)) {
            $validated['price'] = 0;

            return $validated;
        }

        $validated['price'] = round(array_reduce($packages, function ($sum, $package) {
            return $sum + (float) ($package['price'] ?? 0);
        }, 0), 2);

        return $validated;
    }

    private function resolveVendorFromRequest(Request $request): User|JsonResponse
    {
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

    private function bookingHasPassed(Booking $booking, ?Event $event = null): bool
    {
        $bookingDate = $booking->requested_event_date
            ? Carbon::parse($booking->requested_event_date)->startOfDay()
            : ($event?->starts_at ? Carbon::parse($event->starts_at)->startOfDay() : null);

        if (! $bookingDate) {
            return false;
        }

        return $bookingDate->lt(Carbon::today());
    }

    private function createCustomerBookingStatusNotification(Booking $booking, string $nextStatus): void
    {
        $event = $booking->relationLoaded('event')
            ? $booking->event
            : $booking->event()
                ->with('vendor:id,name,email')
                ->select(['id', 'title', 'starts_at', 'vendor_id'])
                ->first();

        if (! $event || (! $booking->user_id && ! $booking->customer_email)) {
            return;
        }

        $statusLabel = ucfirst($nextStatus);
        $serviceName = $booking->service_name ?: ($event->title ?: 'Service Booking');
        $eventDate = $booking->requested_event_date
            ? Carbon::parse($booking->requested_event_date)->format('M d, Y')
            : ($event->starts_at ? Carbon::parse($event->starts_at)->format('M d, Y g:i A') : 'the scheduled date');
        $message = $nextStatus === 'confirmed'
            ? "Your booking for {$serviceName} on {$eventDate} has been confirmed by the vendor."
            : "Your booking for {$serviceName} on {$eventDate} is now {$statusLabel}.";

        BookingNotification::create([
            'booking_id' => $booking->id,
            'recipient_type' => 'user',
            'recipient_user_id' => $booking->user_id ? (int) $booking->user_id : null,
            'recipient_email' => $booking->customer_email ? strtolower(trim((string) $booking->customer_email)) : null,
            'kind' => 'booking_status_changed',
            'title' => "Booking {$statusLabel}",
            'message' => $message,
        ]);

        $this->flushNotificationCache(
            'user',
            $booking->user_id ? (int) $booking->user_id : null,
            $booking->customer_email ? strtolower(trim((string) $booking->customer_email)) : null
        );
    }

    private function flushNotificationCacheForBooking(Booking $booking, ?Event $event = null): void
    {
        $bookingEvent = $event;
        if (! $bookingEvent) {
            $bookingEvent = $booking->relationLoaded('event')
                ? $booking->event
                : $booking->event()
                    ->with('vendor:id,email')
                    ->select(['id', 'vendor_id'])
                    ->first();
        } elseif (! $bookingEvent->relationLoaded('vendor')) {
            $bookingEvent->loadMissing('vendor:id,email');
        }

        $this->flushNotificationCache(
            'user',
            $booking->user_id ? (int) $booking->user_id : null,
            $booking->customer_email ? strtolower(trim((string) $booking->customer_email)) : null
        );

        $this->flushNotificationCache(
            'vendor',
            $bookingEvent?->vendor_id ? (int) $bookingEvent->vendor_id : null,
            $bookingEvent?->vendor?->email ? strtolower(trim((string) $bookingEvent->vendor->email)) : null
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

    private function storeEventImage(UploadedFile $image): string
    {
        $disk = (string) config('media.event_image_disk', 'public');
        $directory = trim((string) config('media.event_image_directory', 'services'), '/');

        if (! config("filesystems.disks.{$disk}")) {
            throw new \RuntimeException("Image storage disk [{$disk}] is not configured.");
        }

        if ($disk === 'cloudinary') {
            return $this->storeCloudinaryEventImage($disk, $directory, $image);
        }

        $extension = Str::lower((string) ($image->getClientOriginalExtension() ?: $image->guessExtension() ?: 'bin'));
        $filename = Str::uuid()->toString().'.'.$extension;
        $path = Storage::disk($disk)->putFileAs($directory, $image, $filename, ['visibility' => 'public']);

        if (! is_string($path) || $path === '') {
            throw new \RuntimeException('Failed to store event image.');
        }

        return Storage::disk($disk)->url($path);
    }

    private function storeCloudinaryEventImage(string $disk, string $directory, UploadedFile $image): string
    {
        $prefix = trim((string) config("filesystems.disks.{$disk}.prefix", ''), '/');
        $folder = ltrim(collect([$prefix, $directory])->filter()->implode('/'), '/');
        $publicId = Str::uuid()->toString();
        $uploadOptions = [
            'public_id' => $publicId,
            'resource_type' => 'image',
        ];

        if ($folder !== '') {
            $uploadOptions['folder'] = $folder;
        }

        $result = cloudinary()->uploadApi()->upload($image->getRealPath(), $uploadOptions);
        $secureUrl = (string) data_get($result, 'secure_url', '');

        if ($secureUrl === '') {
            throw new \RuntimeException('Cloudinary did not return a secure URL for the uploaded image.');
        }

        return $secureUrl;
    }

    private function deleteStoredEventImage(?string $imageUrl): void
    {
        if (! is_string($imageUrl) || trim($imageUrl) === '') {
            return;
        }

        $disk = (string) config('media.event_image_disk', 'public');

        try {
            if ($disk === 'cloudinary') {
                $publicId = $this->extractCloudinaryPublicId($imageUrl);
                if ($publicId !== null) {
                    cloudinary()->uploadApi()->destroy($publicId, ['resource_type' => 'image']);
                }

                return;
            }

            $path = $this->extractLocalStoragePath($imageUrl);
            if ($path !== null && Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function extractCloudinaryPublicId(string $imageUrl): ?string
    {
        $path = (string) parse_url($imageUrl, PHP_URL_PATH);
        $marker = '/image/upload/';
        $position = strpos($path, $marker);

        if ($position === false) {
            return null;
        }

        $publicPath = substr($path, $position + strlen($marker));
        $publicPath = ltrim($publicPath, '/');

        if ($publicPath === '') {
            return null;
        }

        $segments = array_values(array_filter(explode('/', $publicPath), fn (string $segment) => $segment !== ''));
        if ($segments !== [] && preg_match('/^v\d+$/', $segments[0]) === 1) {
            array_shift($segments);
        }

        if ($segments === []) {
            return null;
        }

        $lastSegment = array_pop($segments);
        $extension = pathinfo($lastSegment, PATHINFO_EXTENSION);

        if ($extension !== '') {
            $lastSegment = pathinfo($lastSegment, PATHINFO_FILENAME);
        }

        $segments[] = $lastSegment;

        return implode('/', $segments);
    }

    private function extractLocalStoragePath(string $imageUrl): ?string
    {
        $path = (string) parse_url($imageUrl, PHP_URL_PATH);

        if (Str::startsWith($path, '/storage/')) {
            return Str::after($path, '/storage/');
        }

        if (Str::startsWith($path, '/uploads/')) {
            return Str::after($path, '/uploads/');
        }

        return null;
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
