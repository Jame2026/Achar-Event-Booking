<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminBookingAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public string $subjectLine,
        public string $statusLabel,
        public string $ctaUrl
    ) {
        $this->subject($subjectLine);
    }

    public function build(): static
    {
        $booking = $this->booking;
        $booking->loadMissing([
            'event:id,title,starts_at,vendor_id',
            'event.vendor:id,name,email',
            'user:id,name,email,phone,location',
        ]);

        $serviceName = $booking->service_name ?: ($booking->event?->title ?? 'Service Booking');
        $eventDate = $booking->requested_event_date
            ? $booking->requested_event_date->format('M d, Y')
            : ($booking->event?->starts_at?->format('M d, Y g:i A') ?? 'the scheduled date');

        return $this
            ->markdown('emails.booking.admin-alert', [
                'booking' => $booking,
                'serviceName' => $serviceName,
                'eventDate' => $eventDate,
                'statusLabel' => $this->statusLabel,
                'ctaUrl' => $this->ctaUrl,
            ]);
    }
}
