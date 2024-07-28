<?php

use App\Utils\JsendFormatter;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        apiPrefix: '',
    )
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        ['middleware' => ['web', 'auth:web,api']],
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->use([
            // \Illuminate\Http\Middleware\TrustHosts::class,
            \Illuminate\Http\Middleware\TrustProxies::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);

        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->group('api', [
            // 'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);

        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PROTO
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e) {
            return JsendFormatter::fail(
                ['message' => $e->getMessage() ?: 'Unauthenticated.'],
                401,
            );
        });

        $exceptions->render(function (ValidationException $e) {
            return JsendFormatter::fail(
                [
                    'message' => $e->getMessage() ?: 'The given data was invalid.',
                    'details' => $e->errors(),
                ],
                $e->status ?? 422,
            );
        });

        $exceptions->render(function (AccessDeniedHttpException $e) {
            return JsendFormatter::fail(
                array_merge(
                    [
                        'message' => $e->getMessage() ?: 'Forbidden.',
                    ],
                    config('app.debug') ? [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => collect($e->getTrace())->map(function ($trace) {
                            return Arr::except($trace, ['args']);
                        })->all(),
                    ] : []
                ),
                $e->getStatusCode(),
                $e->getHeaders() ?: [],
            );
        });

        $exceptions->render(function (HttpException $e) {
            if ($e->getStatusCode() <= 500) {
                return JsendFormatter::fail(
                    array_merge(
                        ['message' => $e->getMessage()],
                        config('app.debug') ? [
                            'exception' => get_class($e),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => collect($e->getTrace())->map(function ($trace) {
                                return Arr::except($trace, ['args']);
                            })->all(),
                        ] : []
                    ),
                    $e->getStatusCode(),
                    $e->getHeaders() ?: [],
                );
            } else {
                return JsendFormatter::error(
                    $e->getMessage(),
                    $e->getCode() ?: null,
                    config('app.debug') ? [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => collect($e->getTrace())->map(function ($trace) {
                            return Arr::except($trace, ['args']);
                        })->all(),
                    ] : null,
                    $e->getStatusCode() ?? 500,
                    $e->getHeaders() ?: [],
                );
            }
        });

        $exceptions->render(function (Throwable $e) {
            return JsendFormatter::error(
                config('app.debug') ? $e->getMessage() : 'Internal server error.',
                $e->getCode() ?: null,
                config('app.debug') ? [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => collect($e->getTrace())->map(function ($trace) {
                        return Arr::except($trace, ['args']);
                    })->all(),
                ] : null,
                500,
            );
        });
    })->create();
