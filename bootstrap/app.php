<?php

use App\Exceptions\BaseException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Sentry\Laravel\Integration;
use Sentry\State\Scope;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Passport는 토큰 기반 API 인증을 사용하므로
        // 별도의 상태 유지 미들웨어가 필요하지 않습니다
        // CreateFreshApiToken은 웹 라우트에서 쿠키 기반 API 토큰 발급에 사용됩니다

        // API 인증 라우트는 CSRF 검증에서 제외 (토큰 기반 인증 사용)
        $middleware->validateCsrfTokens(except: [
            'api/auth/login',
            'api/auth/register',
            'api/auth/logout',
            'api/auth/refresh',
        ]);

        // refresh_token 쿠키는 암호화 제외 (JWT 자체가 서명됨)
        $middleware->encryptCookies(except: [
            'refresh_token',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Sentry 통합 - BaseException context를 Sentry에 전달
        Integration::handles($exceptions);

        // BaseException에서 Sentry context 설정
        $exceptions->report(function (BaseException $e) {
            if (! empty($e->getContext())) {
                \Sentry\configureScope(function (Scope $scope) use ($e): void {
                    $scope->setContext('exception_context', $e->getContext());
                    $scope->setTag('error_code', $e->getErrorCode());
                });
            }
        });

        // BaseException 렌더링 (JSON 응답)
        $exceptions->render(function (BaseException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $e->render();
            }

            return null;
        });
    })->create();
