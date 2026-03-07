<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ChatController extends Controller
{
    public function vendorIndex(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vendor_user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $vendor = User::query()->select(['id', 'name', 'role'])->findOrFail((int) $validated['vendor_user_id']);
        if (! in_array($vendor->role, ['vendor', 'admin'], true)) {
            return response()->json(['message' => 'This user is not a vendor account.'], 422);
        }

        $this->syncVendorBookingConversations($vendor->id);

        $conversations = ChatConversation::query()
            ->with([
                'booking:id,event_id,status,customer_name,customer_email,service_name,requested_event_type,created_at',
                'booking.event:id,title,event_type,starts_at,location',
                'messages' => fn ($query) => $query->latest()->limit(80),
            ])
            ->where('vendor_user_id', $vendor->id)
            ->orderByRaw('COALESCE(last_message_at, created_at) DESC')
            ->get()
            ->map(fn (ChatConversation $conversation) => $this->mapConversationResponse($conversation));

        return response()->json([
            'data' => $conversations,
        ]);
    }

    public function vendorSendMessage(Request $request, ChatConversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'vendor_user_id' => ['required', 'integer', 'exists:users,id'],
            'body' => ['required', 'string', 'max:4000'],
        ]);

        $vendor = User::query()->select(['id', 'name', 'role'])->findOrFail((int) $validated['vendor_user_id']);
        if (! in_array($vendor->role, ['vendor', 'admin'], true)) {
            return response()->json(['message' => 'This user is not a vendor account.'], 422);
        }

        if ((int) $conversation->vendor_user_id !== (int) $vendor->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_user_id' => $vendor->id,
            'sender_role' => 'vendor',
            'sender_name' => $vendor->name,
            'body' => trim((string) $validated['body']),
        ]);

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
            $conversation = ChatConversation::query()->firstOrCreate(
                ['booking_id' => $booking->id],
                [
                    'vendor_user_id' => $vendorUserId,
                    'customer_user_id' => $booking->user_id,
                    'customer_name' => $booking->customer_name,
                    'customer_email' => strtolower((string) $booking->customer_email),
                    'service_name' => $booking->service_name ?: ($booking->event?->title ?? 'Service Booking'),
                ]
            );

            $conversation->fill([
                'vendor_user_id' => $vendorUserId,
                'customer_user_id' => $booking->user_id,
                'customer_name' => $booking->customer_name,
                'customer_email' => strtolower((string) $booking->customer_email),
                'service_name' => $booking->service_name ?: ($booking->event?->title ?? 'Service Booking'),
            ]);

            if ($conversation->isDirty()) {
                $conversation->save();
            }
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

        $customerName = $conversation->customer_name
            ?: ($conversation->booking?->customer_name ?: 'Customer');

        return [
            'id' => $conversation->id,
            'booking_id' => $conversation->booking_id,
            'customer_name' => $customerName,
            'customer_email' => $conversation->customer_email ?: $conversation->booking?->customer_email,
            'service_name' => $conversation->service_name ?: ($conversation->booking?->service_name ?: 'Service Booking'),
            'booking_status' => $conversation->booking?->status ?: 'pending',
            'preview' => $latestMessage?->body ?: 'No messages yet.',
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
            'created_at' => $message->created_at?->toIso8601String(),
            'time_label' => $this->timeLabel($message->created_at),
        ];
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
}
