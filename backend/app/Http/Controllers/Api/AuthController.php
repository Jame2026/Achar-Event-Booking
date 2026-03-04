<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'required_without:phone', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'regex:/^\+?[0-9]{8,15}$/', 'required_without:email', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:user,vendor'],
        ]);

        $userEmail = (string) ($validated['email'] ?? '');
        $userPhone = (string) ($validated['phone'] ?? '');

        if ($userEmail === '' && $userPhone !== '') {
            $userEmail = $this->phoneFallbackEmail($userPhone);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $userEmail,
            'phone' => $userPhone !== '' ? $userPhone : null,
            'password' => $validated['password'],
            'role' => $validated['role'],
        ]);

        return response()->json([
            'message' => 'Registration successful.',
            'user' => $user,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['nullable', 'string'],
            'login' => ['nullable', 'string'],
            'password' => ['required', 'string'],
        ]);

        $identifier = trim((string) ($validated['login'] ?? $validated['email'] ?? ''));
        if ($identifier === '') {
            return response()->json([
                'message' => 'Email or phone is required.',
            ], 422);
        }

        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
        $user = $isEmail
            ? User::where('email', strtolower($identifier))->first()
            : User::where('phone', $this->normalizePhone($identifier))->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid login credentials.',
            ], 401);
        }

        return response()->json([
            'message' => 'Login successful.',
            'user' => $user,
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

    private function normalizePhone(string $value): string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return '';
        }

        $hasPlus = str_starts_with($trimmed, '+');
        $digits = preg_replace('/\D+/', '', $trimmed);
        if (! is_string($digits)) {
            return '';
        }

        if ($digits === '') {
            return '';
        }

        return $hasPlus ? '+'.$digits : $digits;
    }

    private function phoneFallbackEmail(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (! is_string($digits) || $digits === '') {
            return '';
        }

        return 'phone_'.$digits.'@users.achar.local';
    }
}
