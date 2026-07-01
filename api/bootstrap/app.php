<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return null;
            }

            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $throwable) {
            return $request->is('api/*') || $request->expectsJson();
        });

        // 422 — payload con formato inválido (campos faltantes, tipos incorrectos, unique/exists fallidos)
        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => 'Los datos enviados no son válidos.',
                'errors' => $e->errors(),
            ], 422);
        });

        // 401 — falta token JWT o el token no es válido/expiró
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => 'No autenticado. Debes iniciar sesión para acceder a este recurso.',
                'errors' => null,
            ], 401);
        });

        // 404 — Eloquent no encontró el modelo (ej. algún ->findOrFail() interno de Laravel)
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => 'El recurso solicitado no existe.',
                'errors' => null,
            ], 404);
        });

        // 404 — URL que no coincide con ninguna ruta definida
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => 'La ruta solicitada no existe.',
                'errors' => null,
            ], 404);
        });

        // Cualquier otra excepción HTTP conocida de Symfony/Laravel (405 Method Not Allowed, 403, etc.)
        // — se conserva su código real, pero se limpia el mensaje interno.
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => $e->getMessage() ?: 'Ocurrió un error al procesar la solicitud.',
                'errors' => null,
            ], $e->getStatusCode());
        });

        // Red de seguridad final: CUALQUIER excepción no prevista (bug de PHP, fallo real de
        // conexión a Postgres, etc.) — nunca se expone el detalle real al cliente, solo se loguea.
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            Log::error('Excepción no controlada: '.$e->getMessage(), [
                'exception' => $e::class,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'message' => 'Ocurrió un error interno. Intenta nuevamente más tarde.',
                'errors' => null,
            ], 500);
        });
    })->create();
