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
        //
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
