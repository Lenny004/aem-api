<?php

namespace App\Exceptions\Domain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Exception;

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

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => null,
        ], $this->statusCode);
    }
}
