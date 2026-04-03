<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Support\PublicEventCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index(): JsonResponse
    {
        $perPage = (int) request()->integer('per_page', 15);
        $perPage = max(1, min($perPage, 100));
        $includeInactive = request()->boolean('include_inactive', false);
        if ($includeInactive) {
            $events = Event::query()
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
                    'created_at',
                    'updated_at',
                ])
                ->with('vendor:id,name,profile_image_url')
                ->withCount(['bookings', 'ratings'])
                ->withAvg('ratings as rating_average', 'rating')
                ->latest('starts_at')
                ->paginate($perPage);

            return response()->json($events);
        }

        $events = PublicEventCache::rememberIndex($perPage, function () use ($perPage) {
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
                    'created_at',
                    'updated_at',
                ])
                ->where('is_active', true)
                ->whereHas('vendor.vendorSetting', function ($query) {
                    $query
                        ->where('subscription_status', 'active')
                        ->whereNotNull('subscription_expires_at')
                        ->where('subscription_expires_at', '>=', now());
                })
                ->with('vendor:id,name,profile_image_url')
                ->withCount(['bookings', 'ratings'])
                ->withAvg('ratings as rating_average', 'rating')
                ->latest('starts_at')
                ->paginate($perPage);
        });

        return response()->json($events);
    }

    public function store(Request $request): JsonResponse
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

        $event = Event::create($validated);
        PublicEventCache::invalidate();

        return response()->json($event, 201);
    }

    public function show(Event $event): JsonResponse
    {
        $event = Event::query()
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
                'created_at',
                'updated_at',
            ])
            ->with([
                'vendor:id,name,profile_image_url',
                'vendor.vendorSetting:user_id,subscription_status,subscription_expires_at',
            ])
            ->withCount(['bookings', 'ratings'])
            ->withAvg('ratings as rating_average', 'rating')
            ->findOrFail($event->id);

        $vendorSetting = $event->vendor?->vendorSetting;
        $publiclyVisible = $event->is_active
            && $vendorSetting
            && strtolower((string) $vendorSetting->subscription_status) === 'active'
            && $vendorSetting->subscription_expires_at
            && $vendorSetting->subscription_expires_at->gte(now());

        if (! $publiclyVisible) {
            return response()->json(['message' => 'Service not found.'], 404);
        }

        return response()->json($event);
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        $this->normalizePackages($request);
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
        PublicEventCache::invalidate();

        return response()->json($event->fresh());
    }

    public function destroy(Event $event): JsonResponse
    {
        $this->deleteStoredEventImage($event->image_url);
        $event->delete();
        PublicEventCache::invalidate();

        return response()->json(null, 204);
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

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = Str::lower((string) $image->getClientOriginalExtension());

        if (! in_array($extension, $allowedExtensions, true)) {
            return 'The service image must be a JPG, PNG, GIF, or WEBP file.';
        }

        return null;
    }
}
