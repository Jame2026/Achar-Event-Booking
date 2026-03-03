<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookingNotification;
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

        $query = BookingNotification::query()
            ->with('booking.event:id,title,event_type,starts_at,location')
            ->latest();

        $this->applyRecipientFilters($query, $validated);

        $unreadCountQuery = BookingNotification::query()->where('is_read', false);
        $this->applyRecipientFilters($unreadCountQuery, $validated);

        return response()->json([
            'data' => $query->take($limit)->get(),
            'unread_count' => $unreadCountQuery->count(),
        ]);
    }

    public function markRead(Request $request, BookingNotification $notification): JsonResponse
    {
        $validated = $this->validateRecipientRequest($request, false);

        if (! $this->canAccessNotification($notification, $validated)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $notification->update(['is_read' => true]);

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

        return response()->json([
            'message' => 'Notifications marked as read.',
            'updated' => $updated,
        ]);
    }

    private function validateRecipientRequest(Request $request, bool $allowLimit): array
    {
        $rules = [
            'role' => ['nullable', Rule::in(['user', 'vendor'])],
            'user_id' => ['nullable', 'integer', 'exists:users,id', 'required_without:email'],
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
}
