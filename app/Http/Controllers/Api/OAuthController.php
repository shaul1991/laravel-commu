<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\SocialAccountModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

final class OAuthController extends Controller
{
    private const SUPPORTED_PROVIDERS = ['google', 'github', 'keycloak'];

    public function redirect(string $provider): RedirectResponse
    {
        if (! in_array($provider, self::SUPPORTED_PROVIDERS, true)) {
            return redirect('/login?error=unsupported_provider');
        }

        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function callback(Request $request, string $provider): Response|RedirectResponse
    {
        if (! in_array($provider, self::SUPPORTED_PROVIDERS, true)) {
            return redirect('/login?error=unsupported_provider');
        }

        if (! $request->has('code')) {
            \Log::warning('OAuth callback missing code', [
                'provider' => $provider,
                'query' => $request->query(),
                'url' => $request->fullUrl(),
            ]);

            return redirect('/login?error=oauth_failed');
        }

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();

            // Check if user was previously deleted (soft deleted)
            $deletedUser = UserModel::withTrashed()
                ->where('email', $socialUser->getEmail())
                ->whereNotNull('deleted_at')
                ->first();

            if ($deletedUser) {
                return redirect('/login?error=account_deleted');
            }

            $user = DB::transaction(function () use ($provider, $socialUser) {
                // Step 1: Check if social account exists
                $socialAccount = SocialAccountModel::where('provider', $provider)
                    ->where('provider_id', $socialUser->getId())
                    ->first();

                if ($socialAccount) {
                    // Social account exists - update tokens and return user
                    $this->updateSocialAccountTokens($socialAccount, $socialUser);

                    return UserModel::find($socialAccount->user_id);
                }

                // Step 2: Check if user exists by email
                $user = UserModel::where('email', $socialUser->getEmail())->first();

                if ($user) {
                    // User exists - create social account and link
                    $this->createSocialAccount($user, $provider, $socialUser);

                    return $user;
                }

                // Step 3: Create new user and social account
                $username = $this->generateUniqueUsername(
                    $socialUser->getNickname() ?? $socialUser->getName()
                );

                $user = UserModel::create([
                    'uuid' => (string) Str::uuid(),
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'username' => $username,
                    'password' => Hash::make(Str::random(32)),
                    'avatar_url' => $socialUser->getAvatar(),
                    'email_verified_at' => now(),
                ]);

                $this->createSocialAccount($user, $provider, $socialUser);

                return $user;
            });

            // Create Access Token (Passport)
            $tokenResult = $user->createToken('access_token');
            $accessToken = $tokenResult->accessToken;

            // Create Refresh Token and set as HTTP-only cookie
            $refreshTokenResult = $user->createToken('refresh_token');
            $refreshExpiresInMinutes = (int) config('passport.refresh_tokens_expire_in', 10080);

            $refreshTokenCookie = Cookie::make(
                name: 'refresh_token',
                value: $refreshTokenResult->accessToken,
                minutes: $refreshExpiresInMinutes,
                path: '/api/auth',
                domain: null,
                secure: app()->environment('production'),
                httpOnly: true,
                raw: false,
                sameSite: 'lax'
            );

            // Return HTML that stores token and redirects
            $userData = json_encode([
                'id' => $user->uuid,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'bio' => $user->bio,
                'avatar_url' => $user->avatar_url,
            ]);

            $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>로그인 중...</title>
</head>
<body>
    <script>
        localStorage.setItem('auth_token', '{$accessToken}');
        localStorage.setItem('auth_user', '{$userData}');
        window.location.href = '/';
    </script>
</body>
</html>
HTML;

            return response($html)->header('Content-Type', 'text/html')->withCookie($refreshTokenCookie);
        } catch (\Exception $e) {
            \Log::error('OAuth callback error', [
                'provider' => $provider,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect('/login?error=oauth_failed');
        }
    }

    private function createSocialAccount(UserModel $user, string $provider, $socialUser): SocialAccountModel
    {
        return SocialAccountModel::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'provider_email' => $socialUser->getEmail(),
            'nickname' => $socialUser->getNickname(),
            'avatar_url' => $socialUser->getAvatar(),
            'access_token' => $socialUser->token,
            'refresh_token' => $socialUser->refreshToken ?? null,
            'token_expires_at' => $socialUser->expiresIn
                ? Carbon::now()->addSeconds($socialUser->expiresIn)
                : null,
        ]);
    }

    private function updateSocialAccountTokens(SocialAccountModel $socialAccount, $socialUser): void
    {
        $socialAccount->update([
            'provider_email' => $socialUser->getEmail(),
            'nickname' => $socialUser->getNickname(),
            'avatar_url' => $socialUser->getAvatar(),
            'access_token' => $socialUser->token,
            'refresh_token' => $socialUser->refreshToken ?? null,
            'token_expires_at' => $socialUser->expiresIn
                ? Carbon::now()->addSeconds($socialUser->expiresIn)
                : null,
        ]);
    }

    private function generateUniqueUsername(string $baseName): string
    {
        $username = Str::slug($baseName, '');

        if (strlen($username) < 3) {
            $username = 'user'.Str::random(6);
        }

        $originalUsername = $username;
        $counter = 1;

        while (UserModel::where('username', $username)->exists()) {
            $username = $originalUsername.$counter;
            $counter++;
        }

        return $username;
    }
}
