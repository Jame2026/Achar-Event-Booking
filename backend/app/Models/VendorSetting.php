<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'timezone',
        'weekly_schedule',
        'blocked_dates',
        'blocked_ranges',
        'auto_accept_bookings',
        'booking_lead_time_hours',
        'buffer_minutes_between_bookings',
        'max_bookings_per_day',
        'deposit_percent',
        'cancellation_policy_hours',
        'reschedule_policy_hours',
        'notify_email',
        'notify_sms',
        'quiet_hours_start',
        'quiet_hours_end',
    ];

    protected $casts = [
        'weekly_schedule' => 'array',
        'blocked_dates' => 'array',
        'blocked_ranges' => 'array',
        'auto_accept_bookings' => 'boolean',
        'booking_lead_time_hours' => 'integer',
        'buffer_minutes_between_bookings' => 'integer',
        'max_bookings_per_day' => 'integer',
        'deposit_percent' => 'float',
        'cancellation_policy_hours' => 'integer',
        'reschedule_policy_hours' => 'integer',
        'notify_email' => 'boolean',
        'notify_sms' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
