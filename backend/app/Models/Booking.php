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
        'customer_name',
        'customer_email',
        'service_name',
        'requested_event_type',
        'requested_event_date',
        'booked_items',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'requested_event_date' => 'date',
            'booked_items' => 'array',
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
}
