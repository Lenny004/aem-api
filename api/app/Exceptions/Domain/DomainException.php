<?php

namespace App\Exceptions\Domain;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Base de todas las excepciones de dominio de la aplicación.
 * Cada subclase fija su propio código HTTP; render() lo traduce a JSON
 * sin depender del manejador global de excepciones (Fase 8).
 */
abstract class DomainException extends Exception
{
    public function __construct(string $message, private readonly int $statusCode)
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Laravel invoca este método automáticamente al capturar la excepción.
     * Devuelve el formato JSON estándar de error de la API con el status correcto.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => null,
        ], $this->statusCode);
    }
}
