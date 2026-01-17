<?php

use App\Exceptions\BaseException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
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
        // SPA 인증을 위한 Sanctum 상태 유지 미들웨어 추가
        // 이 미들웨어는 동일 도메인에서 오는 요청에 대해 세션 기반 인증을 활성화합니다
        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        // API 인증 라우트는 CSRF 검증에서 제외 (토큰 기반 인증 사용)
        $middleware->validateCsrfTokens(except: [
            'api/auth/login',
            'api/auth/register',
            'api/auth/logout',
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
