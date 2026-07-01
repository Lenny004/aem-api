<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

/**
 * Autenticación JWT — infraestructura transversal, no un recurso de negocio versionado.
 * Vive fuera de Api\V1 porque login/logout no pertenecen al dominio Holding/Empresa/Sucursal.
 */
class AuthController extends Controller
{
    /**
     * Único endpoint público: emite el token Bearer tras validar email/password.
     */
    #[OA\Post(
        path: '/v1/auth/login',
        tags: ['Auth'],
        summary: 'Iniciar sesión y obtener un token JWT',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', example: 'admin@aem.test'),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
            ]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Token emitido', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'access_token', type: 'string'),
                    new OA\Property(property: 'token_type', type: 'string', example: 'bearer'),
                    new OA\Property(property: 'expires_in', type: 'integer', example: 3600),
                ]
            )),
            new OA\Response(response: 422, description: 'Credenciales inválidas', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $token = Auth::guard('api')->attempt($credentials);

        if (! $token) {
            // 422 con mensaje en español — mismo formato que otros errores de validación.
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no son válidas.'],
            ]);
        }

        return $this->respondWithToken($token);
    }

    #[OA\Get(
        path: '/v1/auth/me',
        tags: ['Auth'],
        summary: 'Obtener el usuario autenticado actual',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Usuario autenticado'),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    #[OA\Post(
        path: '/v1/auth/logout',
        tags: ['Auth'],
        summary: 'Cerrar sesión (invalidar el token actual)',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Sesión cerrada')]
    )]
    public function logout(): JsonResponse
    {
        Auth::guard('api')->logout();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    /**
     * Emite un token nuevo antes de que expire el actual (TTL configurado en config/jwt.php).
     */
    #[OA\Post(
        path: '/v1/auth/refresh',
        tags: ['Auth'],
        summary: 'Renovar el token JWT antes de que expire',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Nuevo token emitido')]
    )]
    public function refresh(): JsonResponse
    {
        $token = Auth::guard('api')->refresh();

        return $this->respondWithToken($token);
    }

    private function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            // getTTL() está en minutos; la convención OAuth espera segundos.
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
        ]);
    }
}
