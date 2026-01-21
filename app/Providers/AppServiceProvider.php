<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use SocialiteProviders\Keycloak\KeycloakExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePassport();
        $this->configureSentryUserContext();
        $this->configureSocialiteProviders();
    }

    /**
     * Configure Socialite Providers (Keycloak)
     */
    private function configureSocialiteProviders(): void
    {
        Event::listen(SocialiteWasCalled::class, KeycloakExtendSocialite::class);
    }

    /**
     * Configure Laravel Passport
     */
    private function configurePassport(): void
    {
        // Token expiration times from config
        Passport::tokensExpireIn(now()->addMinutes((int) config('passport.tokens_expire_in', 15)));
        Passport::refreshTokensExpireIn(now()->addMinutes((int) config('passport.refresh_tokens_expire_in', 10080)));
        Passport::personalAccessTokensExpireIn(now()->addMinutes((int) config('passport.personal_access_tokens_expire_in', 10080)));

        // Enable Personal Access Client ID and Secret from environment
        if ($clientId = config('passport.personal_access_client.id')) {
            Passport::personalAccessClientId($clientId);
        }
        if ($clientSecret = config('passport.personal_access_client.secret')) {
            Passport::personalAccessClientSecret($clientSecret);
        }
    }

    /**
     * Sentry에 사용자 컨텍스트 설정
     */
    private function configureSentryUserContext(): void
    {
        if (! app()->bound('sentry')) {
            return;
        }

        Auth::resolved(function ($auth) {
            $auth->extend('sentry', function () use ($auth) {
                return $auth;
            });
        });

        // 인증된 사용자 정보를 Sentry에 전달
        $this->app->booted(function () {
            \Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
                if (Auth::check()) {
                    $user = Auth::user();
                    $scope->setUser([
                        'id' => $user->id,
                        'email' => $user->email,
                        'username' => $user->name ?? null,
                    ]);
                }
            });
        });
    }
}
