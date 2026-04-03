<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'quantity',
        'total_amount',
        'status',
        'deposit_percent',
        'deposit_amount',
        'service_fee_amount',
        'balance_due_amount',
        'refund_amount',
        'vendor_penalty_amount',
        'customer_compensation_amount',
        'admin_compensation_amount',
        'payment_status',
        'payment_method',
        'payment_reference',
        'payment_token',
        'paid_at',
        'vendor_cancellation_deadline_at',
        'cancelled_at',
        'cancellation_actor',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_location',
        'service_name',
        'requested_event_type',
        'requested_event_date',
        'booked_items',
    ];

    protected $hidden = [
        'payment_token',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'deposit_percent' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'service_fee_amount' => 'decimal:2',
            'balance_due_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'vendor_penalty_amount' => 'decimal:2',
            'customer_compensation_amount' => 'decimal:2',
            'admin_compensation_amount' => 'decimal:2',
            'requested_event_date' => 'date',
            'booked_items' => 'array',
            'paid_at' => 'datetime',
            'vendor_cancellation_deadline_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(BookingNotification::class);
    }

    public function conversation(): HasOne
    {
        return $this->hasOne(ChatConversation::class, 'booking_id');
    }

    public function rating(): HasOne
    {
        return $this->hasOne(BookingRating::class);
    }
}
