<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ContactIdentity;
use App\Support\IdentityBlacklist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function profile(Request $request): JsonResponse
    {
        $this->normalizePhoneInput($request);

        $validated = $request->validate([
            'user_id' => ['nullable', 'integer'],
            'email' => ['nullable', 'string', 'email'],
            'phone' => ['nullable', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
        ]);

        $user = $this->resolveUser($validated);
        if (! $user) {
            return response()->json(['message' => 'User profile not found.'], 404);
        }

        return response()->json([
            'user' => $user->only(['id', 'name', 'email', 'phone', 'location', 'profile_image_url', 'role']),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $this->normalizePhoneInput($request);

        $validated = $request->validate([
            'user_id' => ['nullable', 'integer'],
            'email' => ['nullable', 'string', 'email'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
            'location' => ['nullable', 'string', 'max:255'],
            'remove_image' => ['nullable', 'boolean'],
            'profile_image' => ['nullable', 'file'],
        ]);

        $user = $this->resolveUser($validated);
        if (! $user) {
            return response()->json(['message' => 'User profile not found.'], 404);
        }

        if (array_key_exists('phone', $validated) && $validated['phone']) {
            if ($entry = IdentityBlacklist::findActiveEntry(null, (string) $validated['phone'])) {
                return response()->json([
                    'message' => IdentityBlacklist::blockedMessage(
                        $entry,
                        'This phone number has been blacklisted. Please contact the admin for approval.'
                    ),
                ], 422);
            }

            $phoneAlreadyUsed = User::query()
                ->where('phone', $validated['phone'])
                ->whereKeyNot($user->id)
                ->exists();

            if ($phoneAlreadyUsed) {
                return response()->json([
                    'message' => 'That phone number is already used by another account.',
                ], 422);
            }
        }

        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $validationError = $this->validateUploadedImage($file);
            if ($validationError !== null) {
                return response()->json(['message' => $validationError], 422);
            }
            $this->deleteStoredProfileImage($user->profile_image_url);
            $user->profile_image_url = $this->storeProfileImage($file);
        } elseif ((bool) ($validated['remove_image'] ?? false)) {
            $this->deleteStoredProfileImage($user->profile_image_url);
            $user->profile_image_url = null;
        }

        $user->name = (string) $validated['name'];
        if (array_key_exists('phone', $validated)) {
            $user->phone = $validated['phone'] ?: null;
        }
        $user->location = $validated['location'] ?? null;
        $user->save();

        return response()->json([
            'message' => 'Profile updated.',
            'user' => $user->only(['id', 'name', 'email', 'phone', 'location', 'profile_image_url', 'role']),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('bookings.event:id,title,starts_at,location,vendor_id', 'bookings.event.vendor:id,name');

        return response()->json($user);
    }

    public function myBookings(Request $request): JsonResponse
    {
        $bookings = $request->user()
            ->bookings()
            ->with('event:id,title,starts_at,location,vendor_id', 'event.vendor:id,name')
            ->latest()
            ->paginate(15);

        return response()->json($bookings);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $this->normalizePhoneInput($request);

        $validated = $request->validate([
            'user_id' => ['nullable', 'integer'],
            'email' => ['nullable', 'string', 'email'],
            'phone' => ['nullable', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        /** @var \App\Models\User|null $user */
        $user = $request->user();
        if (! $user) {
            $user = $this->resolveUser($validated);
        }

        if (! $user) {
            return response()->json(['message' => 'User profile not found.'], 404);
        }

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return response()->json(['message' => 'Password updated successfully.']);
    }

    private function resolveUser(array $validated): ?User
    {
        $userId = (int) ($validated['user_id'] ?? 0);
        $email = strtolower(trim((string) ($validated['email'] ?? '')));
        $phone = trim((string) ($validated['phone'] ?? ''));

        if ($userId > 0) {
            $user = User::query()->find($userId);
            if ($user) {
                return $user;
            }
        }

        if ($email !== '') {
            return User::query()->where('email', $email)->first();
        }

        if ($phone !== '') {
            return User::query()->where('phone', $phone)->first();
        }

        return null;
    }

    private function normalizePhoneInput(Request $request): void
    {
        if (! $request->exists('phone')) {
            return;
        }

        $request->merge([
            'phone' => $this->normalizePhone((string) $request->input('phone')),
        ]);
    }

    private function normalizePhone(string $value): ?string
    {
        return ContactIdentity::normalizePhone($value);
    }

    private function storeProfileImage(UploadedFile $image): string
    {
        $disk = (string) config('media.profile_image_disk', 'public');
        $directory = trim((string) config('media.profile_image_directory', 'profiles'), '/');

        if ($disk === 'cloudinary') {
            $prefix = trim((string) config("filesystems.disks.{$disk}.prefix", ''), '/');
            $folder = ltrim(collect([$prefix, $directory])->filter()->implode('/'), '/');
            $publicId = 'profile_'.Str::uuid()->toString();
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
                throw new \RuntimeException('Cloudinary did not return a secure URL.');
            }

            return $secureUrl;
        }

        $extension = Str::lower((string) ($image->getClientOriginalExtension() ?: $image->guessExtension() ?: 'bin'));
        $filename = 'profile_'.Str::uuid().'.'.$extension;
        $path = Storage::disk($disk)->putFileAs($directory, $image, $filename, ['visibility' => 'public']);
        if (! is_string($path) || trim($path) === '') {
            throw new \RuntimeException('Failed to store profile image.');
        }

        return Storage::disk($disk)->url($path);
    }

    private function deleteStoredProfileImage(?string $imageUrl): void
    {
        if (! is_string($imageUrl) || trim($imageUrl) === '') {
            return;
        }

        try {
            $disk = (string) config('media.profile_image_disk', 'public');
            if ($disk === 'cloudinary' && str_contains($imageUrl, 'cloudinary.com')) {
                $publicId = $this->extractCloudinaryPublicId($imageUrl);
                if ($publicId !== null) {
                    cloudinary()->uploadApi()->destroy($publicId, ['resource_type' => 'image']);
                }

                return;
            }

            $path = $this->extractLocalStoragePath($imageUrl);
            if ($path) {
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
        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, '/storage/')) {
            return ltrim(substr($path, strlen('/storage/')), '/');
        }

        return null;
    }

    private function validateUploadedImage(UploadedFile $image): ?string
    {
        if (! $image->isValid()) {
            return 'The selected image upload is invalid.';
        }

        if ($image->getSize() > 5 * 1024 * 1024) {
            return 'The profile image must not be larger than 5 MB.';
        }

        $extension = Str::lower((string) $image->getClientOriginalExtension());
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            return 'The profile image must be a JPG, PNG, GIF, or WEBP file.';
        }

        return null;
    }
}
