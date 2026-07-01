<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // Registra routes/api.php con prefijo /api y middleware group 'api' (Fase 6).
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // En rutas API devuelve 401 JSON en lugar de redirigir a una ruta web de login.
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return null;
            }

            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Errores de /api/* siempre en JSON, nunca HTML de depuración.
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $throwable) {
            return $request->is('api/*') || $request->expectsJson();
        });
    })->create();
