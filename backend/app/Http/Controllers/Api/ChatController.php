<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Event;
use App\Models\User;
use App\Support\ContactIdentity;
use App\Support\IdentityBlacklist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function vendorIndex(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vendor_user_id' => ['nullable', 'integer'],
            'vendor_email' => ['nullable', 'email'],
            'vendor_name' => ['nullable', 'string', 'max:255'],
        ]);

        $vendorId = $validated['vendor_user_id'] ?? null;
        $vendorEmail = $validated['vendor_email'] ?? null;
        $vendorName = trim((string) ($validated['vendor_name'] ?? ''));

        if (! $vendorId && ! $vendorEmail && $vendorName === '') {
            return response()->json(['message' => 'Vendor identifier is required.'], 422);
        }

        $vendor = null;
        if ($vendorId) {
            $vendor = User::query()->select(['id', 'name', 'role'])->find((int) $vendorId);
        }

        if ($vendor && ! in_array($vendor->role, ['vendor', 'admin'], true)) {
            $vendor = null;
        }

        if (! $vendor && $vendorEmail) {
            $vendor = User::query()
                ->select(['id', 'name', 'role'])
                ->where('email', $this->normalizeEmail((string) $vendorEmail))
                ->first();
        }

        if (! $vendor && $vendorName !== '') {
            $normalizedName = strtolower($vendorName);
            $vendor = User::query()
                ->select(['id', 'name', 'role'])
                ->whereIn('role', ['vendor', 'admin'])
                ->whereRaw('LOWER(name) = ?', [$normalizedName])
                ->first();

            if (! $vendor) {
                $vendor = User::query()
                    ->select(['id', 'name', 'role'])
                    ->whereIn('role', ['vendor', 'admin'])
                    ->whereRaw('LOWER(name) like ?', ['%'.$normalizedName.'%'])
                    ->first();
            }
        }

        if (! $vendor || ! in_array($vendor->role, ['vendor', 'admin'], true)) {
            return response()->json(['message' => 'This user is not a vendor account.'], 422);
        }

        $this->syncVendorBookingConversations($vendor->id);

        $conversations = ChatConversation::query()
            ->with([
                'booking:id,event_id,status,customer_name,customer_email,customer_phone,customer_location,service_name,requested_event_type,created_at',
                'booking.event:id,title,event_type,starts_at,location',
                'vendor:id,name,email,phone,location,profile_image_url',
                'customer:id,name,email,phone,location,profile_image_url',
                'messages' => fn ($query) => $query->latest()->limit(80),
            ])
            ->where('vendor_user_id', $vendor->id)
            ->whereNull('booking_id')
            ->orderByRaw('COALESCE(last_message_at, created_at) DESC')
            ->get()
            ->map(fn (ChatConversation $conversation) => $this->mapConversationResponse($conversation));

        return response()->json([
            'data' => $conversations,
        ]);
    }

    public function userIndex(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_email' => ['required', 'email'],
        ]);

        $email = $this->normalizeEmail((string) $validated['customer_email']);
        if ($entry = IdentityBlacklist::findActiveEntry($email, null)) {
            return response()->json([
                'message' => IdentityBlacklist::blockedMessage(
                    $entry,
                    'This email address has been blacklisted. Please contact the admin for approval.'
                ),
            ], 403);
        }

        $this->syncCustomerBookingConversationsByEmail($email);

        $conversations = ChatConversation::query()
            ->with([
                'booking:id,event_id,status,customer_name,customer_email,customer_phone,customer_location,service_name,requested_event_type,created_at',
                'booking.event:id,title,event_type,starts_at,location',
                'vendor:id,name,email,phone,location,profile_image_url',
                'customer:id,name,email,phone,location,profile_image_url',
                'messages' => fn ($query) => $query->latest()->limit(80),
            ])
            ->whereRaw('LOWER(customer_email) = ?', [$email])
            ->whereNull('booking_id')
            ->orderByRaw('COALESCE(last_message_at, created_at) DESC')
            ->get()
            ->map(fn (ChatConversation $conversation) => $this->mapConversationResponse($conversation));

        return response()->json([
            'data' => $conversations,
        ]);
    }

    public function userCreate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_email' => ['required', 'email'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'service_name' => ['nullable', 'string', 'max:255'],
            'vendor_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'vendor_email' => ['nullable', 'email'],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'event_id' => ['nullable', 'integer'],
        ]);

        $vendorId = $validated['vendor_user_id'] ?? null;
        $vendorEmail = $validated['vendor_email'] ?? null;
        $vendorName = trim((string) ($validated['vendor_name'] ?? ''));
        $eventId = $validated['event_id'] ?? null;
        $resolvedVendor = null;

        if (! $vendorId && ! $vendorEmail && $eventId) {
            $event = Event::query()->select(['id', 'vendor_id', 'title'])->find((int) $eventId);
            if ($event && $event->vendor_id) {
                $vendorId = (int) $event->vendor_id;
                if (! isset($validated['service_name']) || trim((string) ($validated['service_name'] ?? '')) === '') {
                    $validated['service_name'] = (string) $event->title;
                }
            }
        }

        if (! $vendorId && ! $vendorEmail && $vendorName !== '') {
            $normalizedName = strtolower($vendorName);
            $resolvedVendor = User::query()
                ->select(['id', 'name', 'email', 'role', 'phone', 'location', 'profile_image_url'])
                ->whereIn('role', ['vendor', 'admin'])
                ->whereRaw('LOWER(name) = ?', [$normalizedName])
                ->first();

            if (! $resolvedVendor) {
                $resolvedVendor = User::query()
                    ->select(['id', 'name', 'email', 'role', 'phone', 'location', 'profile_image_url'])
                    ->whereIn('role', ['vendor', 'admin'])
                    ->whereRaw('LOWER(name) like ?', ['%'.$normalizedName.'%'])
                    ->first();
            }

            if ($resolvedVendor) {
                $vendorId = (int) $resolvedVendor->id;
            }
        }

        if (! $vendorId && ! $vendorEmail) {
            return response()->json(['message' => 'Vendor identifier is required.'], 422);
        }

        $vendor = $resolvedVendor ?: ($vendorId
            ? User::query()->select(['id', 'name', 'email', 'role', 'phone', 'location', 'profile_image_url'])->findOrFail((int) $vendorId)
            : User::query()
                ->select(['id', 'name', 'email', 'role', 'phone', 'location', 'profile_image_url'])
                ->where('email', $this->normalizeEmail((string) $vendorEmail))
                ->first());

        if (! $vendor || ! in_array($vendor->role, ['vendor', 'admin'], true)) {
            return response()->json(['message' => 'This user is not a vendor account.'], 422);
        }

        $email = $this->normalizeEmail((string) $validated['customer_email']);
        if ($entry = IdentityBlacklist::findActiveEntry($email, null)) {
            return response()->json([
                'message' => IdentityBlacklist::blockedMessage(
                    $entry,
                    'This email address has been blacklisted. Please contact the admin for approval.'
                ),
            ], 422);
        }

        $customerName = trim((string) ($validated['customer_name'] ?? ''));
        $serviceName = trim((string) ($validated['service_name'] ?? ''));
        $customer = User::query()->select(['id', 'name'])->where('email', $email)->first();

        $conversation = ChatConversation::query()
            ->whereNull('booking_id')
            ->where('vendor_user_id', $vendor->id)
            ->whereRaw('LOWER(customer_email) = ?', [$email])
            ->first();

        if (! $conversation) {
            $conversation = ChatConversation::create([
                'vendor_user_id' => $vendor->id,
                'customer_user_id' => $customer?->id,
                'customer_name' => $customerName !== '' ? $customerName : ($customer?->name ?? null),
                'customer_email' => $email,
                'service_name' => $serviceName !== '' ? $serviceName : null,
            ]);
        } else {
            $conversation->fill([
                'customer_user_id' => $customer?->id ?? $conversation->customer_user_id,
                'customer_name' => $customerName !== '' ? $customerName : ($conversation->customer_name ?: $customer?->name),
                'customer_email' => $email,
                'service_name' => $serviceName !== '' ? $serviceName : $conversation->service_name,
            ]);
            if ($conversation->isDirty()) {
                $conversation->save();
            }
        }

        $conversation->load([
            'booking:id,event_id,status,customer_name,customer_email,customer_phone,customer_location,service_name,requested_event_type,created_at',
            'booking.event:id,title,event_type,starts_at,location',
            'vendor:id,name,email,phone,location,profile_image_url',
            'customer:id,name,email,phone,location,profile_image_url',
            'messages' => fn ($query) => $query->latest()->limit(80),
        ]);

        return response()->json([
            'data' => $this->mapConversationResponse($conversation),
        ], 201);
    }

    public function vendorSendMessage(Request $request, ChatConversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'vendor_user_id' => ['required', 'integer', 'exists:users,id'],
            'body' => ['nullable', 'string', 'max:4000', 'required_without:image'],
            'image' => ['nullable', 'file', 'required_without:body'],
        ]);

        $vendor = User::query()->select(['id', 'name', 'role'])->findOrFail((int) $validated['vendor_user_id']);
        if (! in_array($vendor->role, ['vendor', 'admin'], true)) {
            return response()->json(['message' => 'This user is not a vendor account.'], 422);
        }

        if ((int) $conversation->vendor_user_id !== (int) $vendor->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $body = trim((string) ($validated['body'] ?? ''));
        $image = $request->file('image');
        if ($image instanceof UploadedFile) {
            $imageValidationError = $this->validateUploadedImage($image);
            if ($imageValidationError !== null) {
                return response()->json(['message' => $imageValidationError], 422);
            }
        }
        if ($body === '' && ! ($image instanceof UploadedFile)) {
            return response()->json(['message' => 'Message cannot be empty.'], 422);
        }

        $message = $this->createConversationMessage(
            $conversation,
            $vendor->id,
            'vendor',
            $vendor->name,
            $body,
            $image instanceof UploadedFile ? $image : null,
        );

        $conversation->update([
            'last_message_at' => $message->created_at,
        ]);

        return response()->json([
            'data' => $this->mapMessageResponse($message),
        ], 201);
    }

    public function userSendMessage(Request $request, ChatConversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'customer_email' => ['required', 'email'],
            'body' => ['nullable', 'string', 'max:4000', 'required_without:image'],
            'image' => ['nullable', 'file', 'required_without:body'],
        ]);

        $email = $this->normalizeEmail((string) $validated['customer_email']);
        if ($entry = IdentityBlacklist::findActiveEntry($email, null)) {
            return response()->json([
                'message' => IdentityBlacklist::blockedMessage(
                    $entry,
                    'This email address has been blacklisted. Please contact the admin for approval.'
                ),
            ], 403);
        }

        $conversationEmail = $this->normalizeEmail(
            (string) ($conversation->customer_email ?: $conversation->booking?->customer_email ?: '')
        );

        if ($email === '' || $conversationEmail === '' || $conversationEmail !== $email) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $body = trim((string) ($validated['body'] ?? ''));
        $image = $request->file('image');
        if ($image instanceof UploadedFile) {
            $imageValidationError = $this->validateUploadedImage($image);
            if ($imageValidationError !== null) {
                return response()->json(['message' => $imageValidationError], 422);
            }
        }
        if ($body === '' && ! ($image instanceof UploadedFile)) {
            return response()->json(['message' => 'Message cannot be empty.'], 422);
        }

        $customer = $this->resolveConversationCustomer($conversation);
        $senderName = $this->filledValue($customer?->name)
            ?: $this->filledValue($conversation->customer_name)
            ?: 'Customer';

        $message = $this->createConversationMessage(
            $conversation,
            $conversation->customer_user_id,
            'customer',
            $senderName,
            $body,
            $image instanceof UploadedFile ? $image : null,
        );

        $conversation->update([
            'last_message_at' => $message->created_at,
        ]);

        return response()->json([
            'data' => $this->mapMessageResponse($message),
        ], 201);
    }

    private function syncVendorBookingConversations(int $vendorUserId): void
    {
        $bookings = Booking::query()
            ->with(['event:id,vendor_id,title'])
            ->whereHas('event', fn ($query) => $query->where('vendor_id', $vendorUserId))
            ->latest('id')
            ->get();

        foreach ($bookings as $booking) {
            $email = $this->normalizeEmail((string) $booking->customer_email);
            if ($email === '') {
                continue;
            }

            $this->upsertUnifiedConversation(
                $vendorUserId,
                $email,
                $booking->user_id,
                $this->filledValue($booking->customer_name),
                $this->filledValue($booking->service_name ?: ($booking->event?->title ?? 'Service Booking')),
                $booking->id
            );
        }
    }

    private function syncCustomerBookingConversationsByEmail(string $email): void
    {
        if ($email === '') {
            return;
        }

        $bookings = Booking::query()
            ->with(['event:id,vendor_id,title'])
            ->whereRaw('LOWER(customer_email) = ?', [$email])
            ->latest('id')
            ->get();

        foreach ($bookings as $booking) {
            if (! $booking->event || ! $booking->event->vendor_id) {
                continue;
            }

            $this->upsertUnifiedConversation(
                (int) $booking->event->vendor_id,
                $email,
                $booking->user_id,
                $this->filledValue($booking->customer_name),
                $this->filledValue($booking->service_name ?: ($booking->event?->title ?? 'Service Booking')),
                $booking->id
            );
        }
    }

    private function mapConversationResponse(ChatConversation $conversation): array
    {
        $latestMessage = $conversation->messages->sortByDesc('created_at')->first();
        $messages = $conversation->messages
            ->sortBy('created_at')
            ->values()
            ->map(fn (ChatMessage $message) => $this->mapMessageResponse($message))
            ->all();

        $vendor = $conversation->vendor ?: $conversation->booking?->event?->vendor;
        $customer = $this->resolveConversationCustomer($conversation);
        $customerName = $this->filledValue($customer?->name)
            ?: $this->filledValue($conversation->customer_name)
            ?: ($conversation->booking?->customer_name ?: 'Customer');
        $customerEmail = $this->filledValue($customer?->email)
            ?: $this->filledValue($conversation->customer_email)
            ?: $this->filledValue($conversation->booking?->customer_email);
        $customerPhone = $this->filledValue($customer?->phone)
            ?: $this->filledValue($conversation->booking?->customer_phone);
        $customerLocation = $this->filledValue($customer?->location)
            ?: $this->filledValue($conversation->booking?->customer_location);
        $preview = $latestMessage?->body;
        if (! is_string($preview) || trim($preview) === '') {
            $preview = $latestMessage?->image_url ? 'Shared an image' : '';
        }

        return [
            'id' => $conversation->id,
            'booking_id' => $conversation->booking_id,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'customer_location' => $customerLocation,
            'customer_image_url' => $customer?->profile_image_url,
            'vendor_id' => $conversation->vendor_user_id,
            'vendor_name' => $vendor?->name,
            'vendor_email' => $vendor?->email,
            'vendor_image_url' => $vendor?->profile_image_url,
            'vendor_phone' => $vendor?->phone,
            'vendor_location' => $vendor?->location,
            'service_name' => $conversation->service_name ?: ($conversation->booking?->service_name ?: 'Service Booking'),
            'booking_status' => $conversation->booking?->status ?: '',
            'preview' => $preview,
            'last_message_at' => $latestMessage?->created_at?->toIso8601String(),
            'messages' => $messages,
        ];
    }

    private function mapMessageResponse(ChatMessage $message): array
    {
        return [
            'id' => $message->id,
            'sender_role' => $message->sender_role,
            'sender_name' => $message->sender_name,
            'body' => $message->body,
            'image_url' => $message->image_url,
            'created_at' => $message->created_at?->toIso8601String(),
            'time_label' => $this->timeLabel($message->created_at),
        ];
    }

    private function createConversationMessage(
        ChatConversation $conversation,
        ?int $senderUserId,
        string $senderRole,
        ?string $senderName,
        string $body,
        ?UploadedFile $image = null
    ): ChatMessage {
        $imageUrl = null;
        $imageMime = null;
        $imageSize = null;

        if ($image instanceof UploadedFile) {
            $imageUrl = $this->storeChatImage($image);
            $imageMime = $image->getMimeType();
            $imageSize = $image->getSize();
        }

        return ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_user_id' => $senderUserId,
            'sender_role' => $senderRole,
            'sender_name' => $senderName,
            'body' => $body,
            'image_url' => $imageUrl,
            'image_mime' => $imageMime,
            'image_size' => $imageSize,
        ]);
    }

    private function timeLabel(?Carbon $at): string
    {
        if (! $at) {
            return 'Just now';
        }

        $diffMinutes = (int) $at->diffInMinutes(now());

        if ($diffMinutes < 1) {
            return 'Just now';
        }

        if ($diffMinutes < 60) {
            return $diffMinutes.'m ago';
        }

        if ($diffMinutes < 1440) {
            return (int) floor($diffMinutes / 60).'h ago';
        }

        return $at->format('M d');
    }

    private function normalizeEmail(string $email): string
    {
        return ContactIdentity::normalizeEmail($email) ?? '';
    }

    private function validateUploadedImage(UploadedFile $image): ?string
    {
        if (! $image->isValid()) {
            return 'The selected image upload is invalid.';
        }

        if ($image->getSize() > 5 * 1024 * 1024) {
            return 'Chat images must not be larger than 5 MB.';
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = Str::lower((string) ($image->getClientOriginalExtension() ?: $image->guessExtension() ?: ''));

        if (! in_array($extension, $allowedExtensions, true)) {
            return 'Chat images must be JPG, PNG, GIF, or WEBP.';
        }

        return null;
    }

    private function storeChatImage(UploadedFile $image): string
    {
        $disk = (string) config('media.chat_image_disk', 'public');
        $directory = trim((string) config('media.chat_image_directory', 'chat-images'), '/');

        if (! config("filesystems.disks.{$disk}")) {
            throw new \RuntimeException("Image storage disk [{$disk}] is not configured.");
        }

        if ($disk === 'cloudinary') {
            return $this->storeCloudinaryChatImage($disk, $directory, $image);
        }

        $extension = Str::lower((string) ($image->getClientOriginalExtension() ?: $image->guessExtension() ?: 'bin'));
        $filename = Str::uuid()->toString().'.'.$extension;
        $path = Storage::disk($disk)->putFileAs($directory, $image, $filename, ['visibility' => 'public']);

        if (! is_string($path) || $path === '') {
            throw new \RuntimeException('Failed to store chat image.');
        }

        return Storage::disk($disk)->url($path);
    }

    private function storeCloudinaryChatImage(string $disk, string $directory, UploadedFile $image): string
    {
        $prefix = trim((string) config("filesystems.disks.{$disk}.prefix", ''), '/');
        $folder = ltrim(collect([$prefix, $directory])->filter()->implode('/'), '/');
        $publicId = 'chat_'.Str::uuid()->toString();
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
            throw new \RuntimeException('Cloudinary did not return a secure URL for the uploaded chat image.');
        }

        return $secureUrl;
    }

    private function filledValue(?string $value): ?string
    {
        $trimmed = trim((string) $value);
        return $trimmed !== '' ? $trimmed : null;
    }

    private function findUnifiedConversation(int $vendorUserId, string $email, ?int $bookingId = null): ?ChatConversation
    {
        $conversation = ChatConversation::query()
            ->where('vendor_user_id', $vendorUserId)
            ->whereRaw('LOWER(customer_email) = ?', [$email])
            ->whereNull('booking_id')
            ->first();

        if ($conversation) {
            return $conversation;
        }

        if ($bookingId) {
            $conversation = ChatConversation::query()->where('booking_id', $bookingId)->first();
            if ($conversation) {
                $conversation->booking_id = null;
                $conversation->save();
                return $conversation;
            }
        }

        return null;
    }

    private function upsertUnifiedConversation(
        int $vendorUserId,
        string $email,
        ?int $customerUserId,
        ?string $customerName,
        ?string $serviceName,
        ?int $bookingId = null
    ): ChatConversation {
        $conversation = $this->findUnifiedConversation($vendorUserId, $email, $bookingId);

        if (! $conversation) {
            $conversation = new ChatConversation();
            $conversation->booking_id = null;
        }

        $conversation->vendor_user_id = $vendorUserId;
        $conversation->customer_email = $email;

        if (! $customerUserId && $email !== '') {
            $customerUserId = User::query()->where('email', $email)->value('id');
        }

        $customer = null;
        if ($customerUserId) {
            $conversation->customer_user_id = $customerUserId;
            $customer = User::query()
                ->select(['id', 'name'])
                ->find($customerUserId);
        }

        if ($customer?->name) {
            $conversation->customer_name = $customer->name;
        } elseif ($customerName) {
            $conversation->customer_name = $customerName;
        }

        if ($serviceName) {
            $conversation->service_name = $serviceName;
        }

        $conversation->save();

        return $conversation;
    }

    private function resolveConversationCustomer(ChatConversation $conversation): ?User
    {
        if ($conversation->relationLoaded('customer') && $conversation->customer) {
            return $conversation->customer;
        }

        $customer = $conversation->customer;
        if ($customer) {
            return $customer;
        }

        $resolved = null;
        $email = $this->normalizeEmail((string) ($conversation->customer_email ?: $conversation->booking?->customer_email ?: ''));

        if ($email !== '') {
            $resolved = User::query()
                ->select(['id', 'name', 'email', 'phone', 'location', 'profile_image_url'])
                ->whereRaw('LOWER(email) = ?', [$email])
                ->first();
        }

        if (! $resolved && $conversation->booking?->user_id) {
            $resolved = User::query()
                ->select(['id', 'name', 'email', 'phone', 'location', 'profile_image_url'])
                ->find((int) $conversation->booking->user_id);
        }

        if ($resolved) {
            $conversation->setRelation('customer', $resolved);
            if ((int) $conversation->customer_user_id !== (int) $resolved->id || $conversation->customer_name !== $resolved->name) {
                $conversation->customer_user_id = $resolved->id;
                $conversation->customer_name = $resolved->name;
                $conversation->save();
            }
        }

        return $resolved;
    }
}
