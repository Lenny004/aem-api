<?php

namespace Tests\Feature\Api\V1;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_crear_compania_con_payload_valido_responde_201(): void
    {
        $response = $this->withHeaders($this->authenticatedHeaders())
            ->postJson('/api/v1/companys', [
                'name' => 'Grupo AEM SV',
                'doc_number' => 'CO-0001',
                'email' => 'contacto@aem.sv',
                'phone' => '2222-3333',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Grupo AEM SV')
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('companys', ['doc_number' => 'CO-0001']);
    }

    public function test_crear_compania_con_payload_incompleto_responde_422(): void
    {
        $response = $this->withHeaders($this->authenticatedHeaders())
            ->postJson('/api/v1/companys', ['email' => 'sin-los-campos-requeridos@aem.sv']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'doc_number']);
    }

    public function test_obtener_compania_por_id_inexistente_responde_404(): void
    {
        $response = $this->withHeaders($this->authenticatedHeaders())
            ->getJson('/api/v1/companys/999999');

        $response->assertStatus(404)
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_listar_companias_sin_token_responde_401(): void
    {
        $response = $this->getJson('/api/v1/companys');

        $response->assertStatus(401);
    }

    public function test_listar_companias_responde_200_con_datos_reales(): void
    {
        Company::factory()->count(3)->create();

        $response = $this->withHeaders($this->authenticatedHeaders())
            ->getJson('/api/v1/companys');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }
}
