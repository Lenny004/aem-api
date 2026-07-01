<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Autenticación JWT — infraestructura transversal, no un recurso de negocio versionado.
 * Vive fuera de Api\V1 porque login/logout no pertenecen al dominio Holding/Empresa/Sucursal.
 */
class AuthController extends Controller
{
    /**
     * Único endpoint público: emite el token Bearer tras validar email/password.
     */
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

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    public function logout(): JsonResponse
    {
        Auth::guard('api')->logout();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    /**
     * Emite un token nuevo antes de que expire el actual (TTL configurado en config/jwt.php).
     */
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
