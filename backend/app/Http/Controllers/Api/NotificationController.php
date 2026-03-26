<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookingNotification;
use App\Support\NotificationCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $this->validateRecipientRequest($request, true);
        $limit = (int) ($validated['limit'] ?? 12);
        $scopeKey = $this->notificationScopeKey($validated);

        $payload = NotificationCache::rememberIndex($scopeKey, $limit, function () use ($validated, $limit) {
            $query = BookingNotification::query()
                ->select([
                    'id',
                    'booking_id',
                    'recipient_type',
                    'recipient_user_id',
                    'recipient_email',
                    'kind',
                    'title',
                    'message',
                    'is_read',
                    'created_at',
                ])
                ->with('booking.event:id,title,event_type,starts_at,location')
                ->latest();

            $this->applyRecipientFilters($query, $validated);

            $unreadCountQuery = BookingNotification::query()->where('is_read', false);
            $this->applyRecipientFilters($unreadCountQuery, $validated);

            return [
                'data' => $query->take($limit)->get(),
                'unread_count' => $unreadCountQuery->count(),
            ];
        });

        return response()->json($payload);
    }

    public function markRead(Request $request, BookingNotification $notification): JsonResponse
    {
        $validated = $this->validateRecipientRequest($request, false);

        if (! $this->canAccessNotification($notification, $validated)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $notification->update(['is_read' => true]);
        $this->flushNotificationScopes($notification);

        return response()->json([
            'message' => 'Notification marked as read.',
            'data' => $notification->fresh()->load('booking.event:id,title,event_type,starts_at,location'),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $validated = $this->validateRecipientRequest($request, false);

        $query = BookingNotification::query()->where('is_read', false);
        $this->applyRecipientFilters($query, $validated);

        $updated = $query->update(['is_read' => true]);
        $this->flushNotificationScopeByRecipient($validated);

        return response()->json([
            'message' => 'Notifications marked as read.',
            'updated' => $updated,
        ]);
    }

    private function validateRecipientRequest(Request $request, bool $allowLimit): array
    {
        $rules = [
            'role' => ['nullable', Rule::in(['user', 'vendor', 'admin'])],
            'user_id' => ['nullable', 'integer', 'min:1', 'required_without:email'],
            'email' => ['nullable', 'email', 'max:255', 'required_without:user_id'],
        ];

        if ($allowLimit) {
            $rules['limit'] = ['nullable', 'integer', 'min:1', 'max:50'];
        }

        $validated = $request->validate($rules);

        if (isset($validated['email']) && is_string($validated['email'])) {
            $validated['email'] = strtolower(trim($validated['email']));
        }

        return $validated;
    }

    private function applyRecipientFilters(Builder $query, array $validated): void
    {
        $role = $validated['role'] ?? null;
        $userId = $validated['user_id'] ?? null;
        $email = $validated['email'] ?? null;

        if ($role) {
            $query->where('recipient_type', $role);
        }

        if ($userId && $email) {
            $query->where(function (Builder $subQuery) use ($userId, $email) {
                $subQuery
                    ->where('recipient_user_id', $userId)
                    ->orWhere('recipient_email', $email);
            });

            return;
        }

        if ($userId) {
            $query->where('recipient_user_id', $userId);

            return;
        }

        if ($email) {
            $query->where('recipient_email', $email);
        }
    }

    private function canAccessNotification(BookingNotification $notification, array $validated): bool
    {
        if (($validated['role'] ?? null) && $notification->recipient_type !== $validated['role']) {
            return false;
        }

        $userId = $validated['user_id'] ?? null;
        $email = $validated['email'] ?? null;

        if ($userId && (int) $notification->recipient_user_id === (int) $userId) {
            return true;
        }

        if ($email && strtolower((string) $notification->recipient_email) === strtolower((string) $email)) {
            return true;
        }

        return false;
    }

    private function notificationScopeKey(array $validated): string
    {
        $role = isset($validated['role']) ? (string) $validated['role'] : null;
        $userId = isset($validated['user_id']) ? (int) $validated['user_id'] : null;
        $email = isset($validated['email']) ? (string) $validated['email'] : null;

        return NotificationCache::scopeKey($role, $userId, $email);
    }

    private function flushNotificationScopes(BookingNotification $notification): void
    {
        $recipientType = $notification->recipient_type ? (string) $notification->recipient_type : null;
        $recipientUserId = $notification->recipient_user_id ? (int) $notification->recipient_user_id : null;
        $recipientEmail = $notification->recipient_email ? (string) $notification->recipient_email : null;

        if ($recipientUserId !== null) {
            NotificationCache::flushScope(NotificationCache::scopeKey($recipientType, $recipientUserId, null));
        }

        if ($recipientEmail !== null) {
            NotificationCache::flushScope(NotificationCache::scopeKey($recipientType, null, $recipientEmail));
        }

        NotificationCache::flushScope(NotificationCache::scopeKey($recipientType, $recipientUserId, $recipientEmail));
    }

    private function flushNotificationScopeByRecipient(array $validated): void
    {
        NotificationCache::flushScope($this->notificationScopeKey($validated));

        $role = isset($validated['role']) ? (string) $validated['role'] : null;
        $userId = isset($validated['user_id']) ? (int) $validated['user_id'] : null;
        $email = isset($validated['email']) ? (string) $validated['email'] : null;

        if ($userId !== null) {
            NotificationCache::flushScope(NotificationCache::scopeKey($role, $userId, null));
        }

        if ($email !== null) {
            NotificationCache::flushScope(NotificationCache::scopeKey($role, null, $email));
        }
    }
}
