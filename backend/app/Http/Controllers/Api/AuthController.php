<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VendorSetting;
use App\Support\ContactIdentity;
use App\Support\IdentityBlacklist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $email = is_string($request->input('email')) ? ContactIdentity::normalizeEmail((string) $request->input('email')) : null;
        $phone = is_string($request->input('phone')) ? ContactIdentity::normalizePhone((string) $request->input('phone')) : null;
        $request->merge([
            'email' => $email,
            'phone' => $phone,
        ]);

        if ($entry = IdentityBlacklist::findActiveEntry($email, $phone)) {
            return response()->json([
                'message' => IdentityBlacklist::blockedMessage(
                    $entry,
                    'This email or phone number has been blacklisted. Please contact the admin for approval.'
                ),
            ], 422);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-]+$/u'],
            'email' => [
                'nullable',
                'string',
                'email:rfc',
                'max:255',
                'required_without:phone',
                'unique:users,email',
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
            'vendor_plan' => [
                'nullable',
                'required_if:role,vendor',
                Rule::in(array_keys(VendorSetting::availableSubscriptionPlans())),
            ],
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

        if ($user->isVendor()) {
            $selectedPlan = (string) $validated['vendor_plan'];
            VendorSetting::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'event_id' => null,
                ],
                [
                    ...VendorSetting::defaultGlobalAttributes(),
                    ...VendorSetting::pendingSubscriptionPayloadForPlan($selectedPlan),
                ],
            );
        }

        $responsePayload = [
            'message' => $user->isVendor()
                ? 'Vendor account created. Scan the QR code to pay your listing plan before publishing services.'
                : 'Registration successful.',
            'user' => $user->only(['id', 'name', 'email', 'phone', 'location', 'profile_image_url', 'role']),
        ];

        if ($user->isVendor()) {
            $responsePayload['subscription_checkout'] = VendorSetting::subscriptionCheckoutPayloadForPlan(
                (string) $validated['vendor_plan']
            );
        }

        return response()->json($responsePayload, 201);
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
        $normalizedEmail = $isEmail ? ContactIdentity::normalizeEmail($identifier) : null;
        $normalizedPhone = $isEmail ? null : ContactIdentity::normalizePhone($identifier);

        if ($entry = IdentityBlacklist::findActiveEntry($normalizedEmail, $normalizedPhone)) {
            return response()->json([
                'message' => IdentityBlacklist::blockedMessage(
                    $entry,
                    'This account has been blacklisted. Please contact the admin for approval.'
                ),
            ], 403);
        }

        if ($isEmail && str_ends_with(strtolower($identifier), '@users.achar.local')) {
            return response()->json([
                'message' => 'Invalid login credentials.',
            ], 401);
        }

        $user = $isEmail
            ? User::query()
            ->select(['id', 'name', 'email', 'phone', 'location', 'profile_image_url', 'role', 'password'])
            ->where('email', $normalizedEmail)
            ->whereNotNull('email')
            ->first()
            : User::query()
            ->select(['id', 'name', 'email', 'phone', 'location', 'profile_image_url', 'role', 'password'])
            ->where('phone', $normalizedPhone)
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid login credentials.',
            ], 401);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'user' => $user->only(['id', 'name', 'email', 'phone', 'location', 'profile_image_url', 'role']),
            'token' => $token,
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
        return ContactIdentity::normalizePhone($value) ?? '';

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
