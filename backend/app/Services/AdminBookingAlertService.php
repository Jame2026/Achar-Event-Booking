<?php

namespace App\Services;

use App\Mail\AdminBookingAlertMail;
use App\Models\Booking;
use App\Models\BookingNotification;
use App\Models\User;
use App\Support\NotificationCache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class AdminBookingAlertService
{
    public function notifyBookingCreated(Booking $booking): void
    {
        $booking = $this->hydrateBooking($booking);

        $serviceName = $this->serviceName($booking);
        $eventDate = $this->eventDateLabel($booking);

        $this->broadcast(
            $booking,
            'booking_created',
            'New booking request',
            "{$booking->customer_name} requested {$booking->quantity} seat(s) for {$serviceName} on {$eventDate}."
        );

        $this->emailAdmins(
            $booking,
            'New booking request received',
            'Pending vendor confirmation'
        );
    }

    public function notifyBookingStatusChanged(Booking $booking, string $nextStatus): void
    {
        $booking = $this->hydrateBooking($booking);

        $statusLabel = ucfirst($nextStatus);
        $serviceName = $this->serviceName($booking);
        $eventDate = $this->eventDateLabel($booking);

        $this->broadcast(
            $booking,
            'booking_status_changed',
            "Booking {$statusLabel}",
            "Booking #{$booking->id} for {$serviceName} on {$eventDate} is now {$statusLabel}."
        );

        $this->emailAdmins(
            $booking,
            "Booking {$statusLabel}",
            $statusLabel
        );
    }

    private function broadcast(Booking $booking, string $kind, string $title, string $message): void
    {
        $admins = $this->adminRecipients();
        if ($admins->isEmpty()) {
            return;
        }

        foreach ($admins as $admin) {
            BookingNotification::create([
                'booking_id' => $booking->id,
                'recipient_type' => 'admin',
                'recipient_user_id' => $admin['id'],
                'recipient_email' => $admin['email'],
                'kind' => $kind,
                'title' => $title,
                'message' => $message,
            ]);

            $this->flushNotificationCache($admin['id'], $admin['email']);
        }
    }

    private function emailAdmins(Booking $booking, string $subjectLine, string $statusLabel): void
    {
        $emails = $this->adminRecipients()->pluck('email')->unique()->filter();

        if ($emails->isEmpty()) {
            return;
        }

        try {
            Mail::to($emails->all())->send(new AdminBookingAlertMail(
                $booking,
                $subjectLine,
                $statusLabel,
                $this->ctaUrl($booking)
            ));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function adminRecipients(): Collection
    {
        return User::query()
            ->select('id', 'email')
            ->where('role', 'admin')
            ->whereNotNull('email')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'email' => strtolower(trim((string) $user->email)),
            ]);
    }

    private function hydrateBooking(Booking $booking): Booking
    {
        $booking->loadMissing([
            'event:id,title,starts_at,vendor_id',
            'event.vendor:id,name,email',
            'user:id,name,email,phone,location',
        ]);

        return $booking;
    }

    private function serviceName(Booking $booking): string
    {
        return $booking->service_name
            ?: ($booking->event?->title ?? 'Service Booking');
    }

    private function eventDateLabel(Booking $booking): string
    {
        if ($booking->requested_event_date) {
            return Carbon::parse($booking->requested_event_date)->format('M d, Y');
        }

        if ($booking->event?->starts_at) {
            return Carbon::parse($booking->event->starts_at)->format('M d, Y g:i A');
        }

        return 'the scheduled date';
    }

    private function ctaUrl(Booking $booking): string
    {
        $baseUrl = rtrim(
            (string) (config('app.frontend_url') ?: env('FRONTEND_URL', config('app.url'))),
            '/'
        );

        return "{$baseUrl}/legacy-app?page=admin-bookings&bookingId={$booking->id}";
    }

    private function flushNotificationCache(?int $userId, ?string $email): void
    {
        $normalizedEmail = $email ? strtolower(trim($email)) : null;

        NotificationCache::flushScope(NotificationCache::scopeKey('admin', $userId, $normalizedEmail));

        if ($userId) {
            NotificationCache::flushScope(NotificationCache::scopeKey('admin', $userId, null));
        }

        if ($normalizedEmail) {
            NotificationCache::flushScope(NotificationCache::scopeKey('admin', null, $normalizedEmail));
        }
    }
}
