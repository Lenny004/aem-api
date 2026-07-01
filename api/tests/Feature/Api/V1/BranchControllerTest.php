<?php

namespace Tests\Feature\Api\V1;

use App\Models\Branch;
use App\Models\Enterprise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_crear_sucursal_con_payload_valido_responde_201(): void
    {
        $enterprise = Enterprise::factory()->create();

        $response = $this->withHeaders($this->authenticatedHeaders())
            ->postJson('/api/v1/branchs', [
                'enterprise_id' => $enterprise->id,
                'name' => 'Sucursal Centro',
                'address' => 'Calle Arce 123, San Salvador',
                'municipality_codigo' => 'SS-01',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.municipality_codigo', 'SS-01');
    }

    public function test_crear_sucursal_con_municipio_fuera_del_catalogo_responde_422(): void
    {
        $enterprise = Enterprise::factory()->create();

        $response = $this->withHeaders($this->authenticatedHeaders())
            ->postJson('/api/v1/branchs', [
                'enterprise_id' => $enterprise->id,
                'name' => 'Sucursal Fantasma',
                'address' => 'Dirección cualquiera',
                'municipality_codigo' => 'ZZ-99', // no existe en config/municipalities.php
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['municipality_codigo']);
    }

    public function test_obtener_sucursal_por_id_inexistente_responde_404(): void
    {
        $response = $this->withHeaders($this->authenticatedHeaders())
            ->getJson('/api/v1/branchs/999999');

        $response->assertStatus(404);
    }

    /**
     * Requisito explícito de la guía: filtrado avanzado combinado de branchs
     * por enterprises_id + municipality_codigo. Se crea "ruido" a propósito
     * (misma empresa con otro municipio, y otra empresa con el mismo municipio)
     * para probar que el filtro combinado excluye ambos casos correctamente.
     */
    public function test_listar_sucursales_filtrando_por_enterprise_y_municipio(): void
    {
        $enterprise = Enterprise::factory()->create();

        $esperada = Branch::factory()->create([
            'enterprise_id' => $enterprise->id,
            'municipality_codigo' => 'SS-01',
        ]);

        Branch::factory()->create([
            'enterprise_id' => $enterprise->id,
            'municipality_codigo' => 'SA-01', // misma empresa, otro municipio → no debe salir
        ]);

        Branch::factory()->create([
            'municipality_codigo' => 'SS-01', // mismo municipio, otra empresa → no debe salir
        ]);

        $response = $this->withHeaders($this->authenticatedHeaders())
            ->getJson("/api/v1/branchs?enterprises_id={$enterprise->id}&municipality_codigo=SS-01");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $esperada->id);
    }
}
