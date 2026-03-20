<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $email = is_string($request->input('email')) ? strtolower(trim((string) $request->input('email'))) : null;
        $phone = is_string($request->input('phone')) ? $this->normalizePhone((string) $request->input('phone')) : null;
        $request->merge([
            'email' => $email,
            'phone' => $phone,
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-]+$/u'],
            'email' => [
                'nullable',
                'string',
                'email:rfc,dns',
                'max:255',
                'required_without:phone',
                'unique:users,email',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $email = strtolower(trim((string) $value));
                    if ($email === '') {
                        return;
                    }
                    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $fail('Please enter a valid email address.');
                        return;
                    }
                    if (str_ends_with($email, '@users.achar.local')) {
                        $fail('Please use a real email domain.');
                    }
                },
            ],
            'phone' => [
                'nullable',
                'string',
                'required_without:email',
                'unique:users,phone',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $this->isValidPhone($value)) {
                        $fail('The phone number is invalid. Please include a valid country code (e.g. +85512345678).');
                    }
                },
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:user,vendor'],
        ]);

        $userEmail = (string) ($validated['email'] ?? '');
        $userPhone = (string) ($validated['phone'] ?? '');


        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $userEmail !== '' ? $userEmail : null,
            'phone'    => $userPhone !== '' ? $userPhone : null,
            'password' => $validated['password'],
            'role'     => $validated['role'],
        ]);

        return response()->json([
            'message' => 'Registration successful.',
            'user' => $user->only(['id', 'name', 'email', 'phone', 'location', 'profile_image_url', 'role']),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $input = trim((string) $value);
                    $isEmail = filter_var($input, FILTER_VALIDATE_EMAIL) !== false;
                    if (! $isEmail && ! $this->isValidPhone($input)) {
                        $fail('Please enter a valid email address or phone number.');
                    }
                },
            ],
            'password' => ['required', 'string'],
        ]);

        $identifier = trim((string) $validated['login']);
        if ($identifier === '') {
            return response()->json([
                'message' => 'Email or phone is required.',
            ], 422);
        }

        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
        if ($isEmail && str_ends_with(strtolower($identifier), '@users.achar.local')) {
            return response()->json([
                'message' => 'Invalid login credentials.',
            ], 401);
        }

        $identifier = $isEmail ? strtolower($identifier) : $this->normalizePhone($identifier);

        $user = $isEmail
            ? User::query()
            ->select(['id', 'name', 'email', 'phone', 'location', 'profile_image_url', 'role', 'password'])
            ->where('email', $identifier)
            ->whereNotNull('email')
            ->first()
            : User::query()
            ->select(['id', 'name', 'email', 'phone', 'location', 'profile_image_url', 'role', 'password'])
            ->where('phone', $identifier)
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid login credentials.',
            ], 401);
        }

        return response()->json([
            'message' => 'Login successful.',
            'user' => $user->only(['id', 'name', 'email', 'phone', 'location', 'profile_image_url', 'role']),
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $status = Password::sendResetLink([
            'email' => strtolower(trim((string) $validated['email'])),
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => __($status),
            ]);
        }

        return response()->json([
            'message' => __($status),
        ], 422);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:8'],
        ]);

        $status = Password::reset(
            [
                'email' => strtolower(trim((string) $validated['email'])),
                'password' => $validated['password'],
                'password_confirmation' => $validated['password_confirmation'],
                'token' => $validated['token'],
            ],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => __($status),
            ]);
        }

        return response()->json([
            'message' => __($status),
        ], 422);
    }

    private function isValidPhone(string $value): bool
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return false;
        }

        // Auto-convert local format: 0882446786 → +855882446786
        if (! str_starts_with($trimmed, '+')) {
            $digits = preg_replace('/\D+/', '', $trimmed);
            if (str_starts_with((string) $digits, '0')) {
                $digits = substr((string) $digits, 1); // remove leading 0
            }
            $trimmed = '+855' . $digits;
        }

        try {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $parsed    = $phoneUtil->parse($trimmed, null);
            return $phoneUtil->isValidNumber($parsed);
        } catch (NumberParseException) {
            return false;
        }
    }

    private function normalizePhone(string $value): string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return '';
        }

        // Auto-convert local format: 0882446786 → +855882446786
        if (! str_starts_with($trimmed, '+')) {
            $digits = preg_replace('/\D+/', '', $trimmed);
            if (is_string($digits) && str_starts_with($digits, '0')) {
                $digits = substr($digits, 1); // remove leading 0
            }
            return '+855' . $digits;
        }

        $digits = preg_replace('/\D+/', '', $trimmed);
        return is_string($digits) && $digits !== '' ? '+' . $digits : '';
    }

    private function phoneFallbackEmail(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (! is_string($digits) || $digits === '') {
            return '';
        }

        return 'phone_' . $digits . '@users.achar.local';
    }
}
