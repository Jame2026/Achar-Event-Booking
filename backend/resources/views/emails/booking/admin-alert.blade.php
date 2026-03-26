@component('mail::message')
# Booking {{ $statusLabel }}

You have an update for booking **#{{ $booking->id }}**.

@component('mail::panel')
- **Service:** {{ $serviceName }}
- **Event date:** {{ $eventDate }}
- **Status:** {{ $statusLabel }}
- **Quantity:** {{ $booking->quantity }}
- **Total amount:** {{ number_format((float) $booking->total_amount, 2) }}
@endcomponent

**Customer**  
{{ $booking->customer_name ?? $booking->user?->name ?? 'N/A' }}  
Email: {{ $booking->customer_email ?? $booking->user?->email ?? 'N/A' }}  
Phone: {{ $booking->customer_phone ?? $booking->user?->phone ?? 'N/A' }}

**Vendor**  
{{ $booking->event?->vendor?->name ?? 'N/A' }}  
Email: {{ $booking->event?->vendor?->email ?? 'N/A' }}

@component('mail::button', ['url' => $ctaUrl])
Review booking #{{ $booking->id }}
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
