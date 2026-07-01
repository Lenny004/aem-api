<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Crea un usuario real y devuelve el header Authorization listo para
     * pasar a withHeaders() — evita repetir el flujo JWT en cada test.
     *
     * @return array{Authorization: string}
     */
    protected function authenticatedHeaders(?User $user = null): array
    {
        $user ??= User::factory()->create();
        $token = auth('api')->login($user);

        return ['Authorization' => "Bearer {$token}"];
    }
}
