<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ApiAuthMiddleware;
use App\Http\Middleware\CorsMiddleware;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::match(['get', 'head', 'post'], '/pagos/izipay/ipn', [PaymentController::class, 'izipayWebhook'])
                ->name('izipay.ipn');
            Route::match(['get', 'head', 'post'], '/izipay-ipn', [PaymentController::class, 'izipayWebhook']);
            Route::match(['get', 'head', 'post'], '/izipay-ipn.php', [PaymentController::class, 'izipayWebhook'])
                ->name('izipay.ipn.php');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->appendToGroup('api', CorsMiddleware::class);
        $middleware->alias([
            'auth.api' => ApiAuthMiddleware::class,
            'admin' => AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
