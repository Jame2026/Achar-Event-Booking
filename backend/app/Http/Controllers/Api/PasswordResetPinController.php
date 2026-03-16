<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetPin;
use App\Models\User;
use App\Notifications\PasswordResetPinNotification;
use App\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetPinController extends Controller
{
    private const EXPIRES_MINUTES = 10;
    private const MAX_ATTEMPTS = 5;
    private const RESEND_THROTTLE_SECONDS = 60;

    public function requestPin(Request $request, SmsService $sms): JsonResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'max:255'],
        ]);

        $rawLogin = trim((string) $validated['login']);
        [$loginType, $normalizedLogin] = $this->normalizeLogin($rawLogin);

        if ($normalizedLogin === '') {
            return response()->json([
                'message' => 'If an account exists, we sent a verification code.',
            ]);
        }

        $user = $this->findUserByLogin($loginType, $normalizedLogin);
        if (! $user) {
            return response()->json([
                'message' => 'If an account exists, we sent a verification code.',
            ]);
        }

        $destination = $loginType === 'email'
            ? strtolower(trim((string) $user->email))
            : trim((string) $user->phone);

        if ($destination === '') {
            return response()->json([
                'message' => 'If an account exists, we sent a verification code.',
            ]);
        }

        $existing = PasswordResetPin::query()->where('login', $normalizedLogin)->first();
        $now = now();

        if ($existing && $existing->last_sent_at && $existing->last_sent_at->diffInSeconds($now) < self::RESEND_THROTTLE_SECONDS) {
            return response()->json([
                'message' => 'If an account exists, we sent a verification code.',
            ]);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = $now->copy()->addMinutes(self::EXPIRES_MINUTES);

        PasswordResetPin::query()->updateOrCreate(
            ['login' => $normalizedLogin],
            [
                'user_id' => $user?->id,
                'channel' => $loginType,
                'destination' => $destination,
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'last_sent_at' => $now,
                'expires_at' => $expiresAt,
            ]
        );

        if ($loginType === 'email') {
            $user->notify(new PasswordResetPinNotification($code, self::EXPIRES_MINUTES));
        } else {
            $sms->send($destination, "Your Achar password reset code is {$code}. It expires in ".self::EXPIRES_MINUTES.' minutes.');
        }

        $payload = [
            'message' => 'If an account exists, we sent a verification code.',
        ];

        if (app()->environment('local') && config('app.debug')) {
            $payload['debug_code'] = $code;
        }

        return response()->json($payload);
    }

    public function verifyPin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'regex:/^[0-9]{6}$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:8'],
        ]);

        $rawLogin = trim((string) $validated['login']);
        [$loginType, $normalizedLogin] = $this->normalizeLogin($rawLogin);

        if ($normalizedLogin === '') {
            return response()->json([
                'message' => 'Invalid or expired code.',
            ], 422);
        }

        $record = PasswordResetPin::query()->where('login', $normalizedLogin)->first();
        if (! $record || ! $record->expires_at || $record->expires_at->isPast()) {
            return response()->json([
                'message' => 'Invalid or expired code.',
            ], 422);
        }

        if ((int) $record->attempts >= self::MAX_ATTEMPTS) {
            $record->delete();
            return response()->json([
                'message' => 'Invalid or expired code.',
            ], 422);
        }

        $code = (string) $validated['code'];
        if (! Hash::check($code, (string) $record->code_hash)) {
            $record->increment('attempts');
            return response()->json([
                'message' => 'Invalid or expired code.',
            ], 422);
        }

        $user = null;
        if ($record->user_id) {
            $user = User::query()->find($record->user_id);
        }
        if (! $user) {
            $user = $this->findUserByLogin($loginType, $normalizedLogin);
        }

        if (! $user) {
            $record->delete();
            return response()->json([
                'message' => 'Invalid or expired code.',
            ], 422);
        }

        $user->forceFill([
            'password' => $validated['password'],
            'remember_token' => Str::random(60),
        ])->save();

        $record->delete();

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }

    /**
     * @return array{0: 'email'|'phone', 1: string}
     */
    private function normalizeLogin(string $login): array
    {
        $trimmed = trim($login);
        if ($trimmed === '') {
            return ['email', ''];
        }

        $isEmail = filter_var($trimmed, FILTER_VALIDATE_EMAIL) !== false;
        if ($isEmail) {
            return ['email', strtolower($trimmed)];
        }

        return ['phone', $this->normalizePhoneDigits($trimmed)];
    }

    private function findUserByLogin(string $type, string $value): ?User
    {
        if ($value === '') {
            return null;
        }

        if ($type === 'email') {
            return User::query()
                ->select(['id', 'email', 'phone', 'password'])
                ->where('email', strtolower($value))
                ->first();
        }

        $digits = $this->normalizePhoneDigits($value);
        if ($digits === '') {
            return null;
        }

        return User::query()
            ->select(['id', 'email', 'phone', 'password'])
            ->whereIn('phone', [$digits, '+'.$digits])
            ->first();
    }

    private function normalizePhoneDigits(string $value): string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return '';
        }

        $digits = preg_replace('/\\D+/', '', $trimmed);
        if (! is_string($digits)) {
            return '';
        }

        if ($digits === '') {
            return '';
        }

        return $digits;
    }
}
