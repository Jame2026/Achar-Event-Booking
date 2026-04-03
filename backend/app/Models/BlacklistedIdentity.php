<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlacklistedIdentity extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_name',
        'subject_role',
        'blocked_email',
        'blocked_phone',
        'reason',
        'blacklisted_by_user_id',
        'blacklisted_at',
        'approved_by_user_id',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'blacklisted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('approved_at');
    }

    public function blacklistedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blacklisted_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
