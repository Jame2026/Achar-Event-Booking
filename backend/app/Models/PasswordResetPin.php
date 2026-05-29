<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetPin extends Model
{
    protected $table = 'password_reset_pins';

    protected $fillable = [
        'user_id',
        'login',
        'channel',
        'destination',
        'code_hash',
        'expires_at',
        'attempts',
        'last_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_sent_at' => 'datetime',
        ];
    }
}

