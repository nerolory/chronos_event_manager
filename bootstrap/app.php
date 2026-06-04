<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson()) {
                // Не перехватываем ValidationException - Laravel обрабатывает его автоматически
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return null;
                }

                Log::error('API Exception', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $statusCode = $e instanceof HttpException ? $e->getStatusCode() : 500;

                return response()->json([
                    'success' => false,
                    'message' => $statusCode === 500 ? 'Внутренняя ошибка сервера' : $e->getMessage(),
                ], $statusCode);
            }
        });
    })->create();
