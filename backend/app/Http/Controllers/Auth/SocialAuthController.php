<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    private const ALLOWED_PROVIDERS = ['google', 'facebook'];

    public function redirectToProvider(Request $request, string $provider): RedirectResponse
    {
        $preferredFrontendUrl = $this->sanitizeFrontendUrl($request->query('frontend_url'));

        if (! in_array($provider, self::ALLOWED_PROVIDERS, true)) {
            return $this->redirectToFrontendError('Unsupported social provider.', $preferredFrontendUrl);
        }

        $config = $this->providerConfig($provider);
        if (! $config) {
            return $this->redirectToFrontendError(ucfirst($provider) . ' OAuth is not configured.', $preferredFrontendUrl);
        }

        $state = $this->makeState($provider, $preferredFrontendUrl);
        [$authUrl, $query] = $this->providerAuthorizeConfig($provider, $config, $state);

        return redirect()->away($authUrl . '?' . http_build_query($query));
    }

    public function handleProviderCallback(Request $request, string $provider): RedirectResponse
    {
        if (! in_array($provider, self::ALLOWED_PROVIDERS, true)) {
            return $this->redirectToFrontendError('Unsupported social provider.');
        }

        $config = $this->providerConfig($provider);
        if (! $config) {
            return $this->redirectToFrontendError(ucfirst($provider) . ' OAuth is not configured.');
        }

        if ($request->filled('error')) {
            return $this->redirectToFrontendError($request->query('error_description', 'Social login was canceled.'));
        }

        $state = $request->query('state');
        if (! is_string($state)) {
            return $this->redirectToFrontendError('Invalid OAuth state. Please try again.');
        }
        $statePayload = $this->parseValidStatePayload($provider, $state);
        if (! is_array($statePayload)) {
            return $this->redirectToFrontendError('Invalid OAuth state. Please try again.');
        }
        $preferredFrontendUrl = $this->sanitizeFrontendUrl($statePayload['frontend_url'] ?? null);

        $code = $request->query('code');
        if (! is_string($code) || $code === '') {
            return $this->redirectToFrontendError('OAuth code was not provided.');
        }

        $socialUser = $this->resolveProviderUser($provider, $code, $config);
        if (! $socialUser) {
            return $this->redirectToFrontendError('Could not read your social account details.', $preferredFrontendUrl);
        }

        $email = strtolower(trim((string) ($socialUser['email'] ?? '')));
        if ($email === '') {
            return $this->redirectToFrontendError('Could not read an email from your social account.', $preferredFrontendUrl);
        }
        $name = trim((string) ($socialUser['name'] ?? ''));
        if ($name === '') {
            $name = strstr($email, '@', true) ?: 'Achar User';
        }

        $user = User::firstOrNew(['email' => $email]);
        if (! $user->exists) {
            $user->name = $name;
            $user->role = 'user';
            $user->password = Str::random(40);
        } elseif (! $user->name) {
            $user->name = $name;
        }
        $user->save();

        return $this->redirectToFrontendSuccess($user, $preferredFrontendUrl);
    }

    private function resolveProviderUser(string $provider, string $code, array $config): ?array
    {
        return match ($provider) {
            'google' => $this->resolveGoogleUser($code, $config),
            'facebook' => $this->resolveFacebookUser($code, $config),
            default => null,
        };
    }

    private function resolveGoogleUser(string $code, array $config): ?array
    {
        $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'redirect_uri' => $config['redirect_uri'],
            'grant_type' => 'authorization_code',
        ]);

        if (! $tokenResponse->successful()) {
            return null;
        }

        $token = $tokenResponse->json('access_token');
        if (! is_string($token) || $token === '') {
            return null;
        }

        $userResponse = Http::withToken($token)->get('https://www.googleapis.com/oauth2/v3/userinfo');
        if (! $userResponse->successful()) {
            return null;
        }

        return [
            'id' => $userResponse->json('id'),
            'name' => $userResponse->json('name'),
            'email' => $userResponse->json('email'),
        ];
    }

    private function resolveFacebookUser(string $code, array $config): ?array
    {
        $tokenResponse = Http::timeout(30)->asForm()->post('https://graph.facebook.com/v19.0/oauth/access_token', [
            'client_id'     => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'redirect_uri'  => $config['redirect_uri'],
            'code'          => $code,
        ]);

        if (!$tokenResponse->successful()) return null;

        $token = $tokenResponse->json('access_token');
        if (!is_string($token) || $token === '') return null;

        $userResponse = Http::timeout(30)->get('https://graph.facebook.com/me', [
            'fields'       => 'id,name,email',
            'access_token' => $token,
        ]);

        if (!$userResponse->successful()) return null;

        return [
            'id'    => $userResponse->json('id'),
            'name'  => $userResponse->json('name'),
            'email' => $userResponse->json('email'),
        ];
    }

    private function providerConfig(string $provider): ?array
    {
        $clientId = (string) config("services.$provider.client_id", '');
        $clientSecret = (string) config("services.$provider.client_secret", '');
        $redirectUri = (string) config("services.$provider.redirect", '');

        if ($clientId === '' || $clientSecret === '' || $redirectUri === '') {
            return null;
        }

        return [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
        ];
    }

    private function providerAuthorizeConfig(string $provider, array $config, string $state): array
    {
        return match ($provider) {
            'google' => [
                'https://accounts.google.com/o/oauth2/v2/auth',
                [
                    'client_id' => $config['client_id'],
                    'redirect_uri' => $config['redirect_uri'],
                    'response_type' => 'code',
                    'scope' => 'openid email profile',
                    'state' => $state,
                    'access_type' => 'online',
                    'prompt' => 'select_account',
                ],
            ],
            'facebook' => [
                'https://www.facebook.com/v19.0/dialog/oauth',
                [
                    'client_id' => $config['client_id'],
                    'redirect_uri' => $config['redirect_uri'],
                    'response_type' => 'code',
                    'scope' => 'email,public_profile',
                    'state' => $state,
                ],
            ],
            default => [
                '',
                [],
            ],
        };
    }

    private function makeState(string $provider, ?string $frontendUrl = null): string
    {
        $payloadData = [
            'provider' => $provider,
            'ts' => now()->timestamp,
            'nonce' => Str::random(24),
        ];
        if (is_string($frontendUrl) && $frontendUrl !== '') {
            $payloadData['frontend_url'] = $frontendUrl;
        }
        $payload = $this->base64UrlEncode(json_encode($payloadData));
        $signature = hash_hmac('sha256', $payload, $this->stateKey());

        return $payload . '.' . $signature;
    }

    private function parseValidStatePayload(string $provider, string $state): ?array
    {
        $parts = explode('.', $state, 2);
        if (count($parts) !== 2) return null;

        [$payload, $signature] = $parts;
        $expectedSignature = hash_hmac('sha256', $payload, $this->stateKey());
        if (! hash_equals($expectedSignature, $signature)) {
            return null;
        }

        $decoded = json_decode($this->base64UrlDecode($payload), true);
        if (! is_array($decoded)) return null;
        if (($decoded['provider'] ?? null) !== $provider) return null;

        $timestamp = (int) ($decoded['ts'] ?? 0);
        if (! ($timestamp > 0 && (now()->timestamp - $timestamp) <= 600)) {
            return null;
        }

        return $decoded;
    }

    private function stateKey(): string
    {
        return (string) config('app.key', 'achar-social-key');
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        $padding = strlen($value) % 4;
        if ($padding > 0) {
            $value .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'));
        return $decoded === false ? '' : $decoded;
    }

    private function frontendBaseUrl(?string $preferred = null): string
    {
        $sanitizedPreferred = $this->sanitizeFrontendUrl($preferred);
        if (is_string($sanitizedPreferred) && $sanitizedPreferred !== '') {
            return rtrim($sanitizedPreferred, '/');
        }

        return rtrim((string) config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')), '/');
    }

    private function redirectToFrontendSuccess(User $user, ?string $preferredFrontend = null): RedirectResponse
    {
        $query = http_build_query([
            'social' => 'success',
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return redirect()->away($this->frontendBaseUrl($preferredFrontend) . '/legacy-app?' . $query);
    }

    private function redirectToFrontendError(string $message, ?string $preferredFrontend = null): RedirectResponse
    {
        $query = http_build_query([
            'social' => 'error',
            'message' => $message,
        ]);

        return redirect()->away($this->frontendBaseUrl($preferredFrontend) . '/legacy-app?' . $query);
    }

    private function sanitizeFrontendUrl(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $candidate = trim($value);
        if (! filter_var($candidate, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($candidate);
        if (! is_array($parts)) {
            return null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if (! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $allowedHosts = ['localhost', '127.0.0.1'];
        if (! in_array($host, $allowedHosts, true)) {
            return null;
        }

        $port = isset($parts['port']) ? ':' . $parts['port'] : '';

        return $scheme . '://' . $host . $port;
    }
}
