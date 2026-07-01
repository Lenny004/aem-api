<?php

namespace Tests\Feature\Api\V1;

use App\Models\Company;
use App\Models\Enterprise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnterpriseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_crear_enterprise_con_payload_valido_responde_201(): void
    {
        $company = Company::factory()->create();

        $response = $this->withHeaders($this->authenticatedHeaders())
            ->postJson('/api/v1/enterprises', [
                'company_id' => $company->id,
                'name' => 'Enterprise Asociada SV',
                'doc_number' => 'EN-0001',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.company_id', $company->id);
    }

    public function test_crear_enterprise_con_payload_incompleto_responde_422(): void
    {
        $response = $this->withHeaders($this->authenticatedHeaders())
            ->postJson('/api/v1/enterprises', ['name' => 'Falta company_id y doc_number']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['company_id', 'doc_number']);
    }

    /**
     * Comportamiento real observado por HTTP: el FormRequest (exists:companys,id)
     * intercepta el company_id inexistente antes de que el Service pueda lanzar
     * el 404 de negocio — ver EnterpriseServiceTest para la prueba de esa otra capa.
     */
    public function test_crear_enterprise_con_company_id_inexistente_responde_422(): void
    {
        $response = $this->withHeaders($this->authenticatedHeaders())
            ->postJson('/api/v1/enterprises', [
                'company_id' => 999999,
                'name' => 'Enterprise Huérfana',
                'doc_number' => 'EN-9999',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['company_id']);
    }

    public function test_crear_enterprise_con_company_padre_inactiva_responde_409(): void
    {
        $company = Company::factory()->inactive()->create();

        $response = $this->withHeaders($this->authenticatedHeaders())
            ->postJson('/api/v1/enterprises', [
                'company_id' => $company->id,
                'name' => 'Enterprise Bloqueada',
                'doc_number' => 'EN-8888',
            ]);

        $response->assertStatus(409);
    }

    public function test_obtener_enterprise_por_id_inexistente_responde_404(): void
    {
        $response = $this->withHeaders($this->authenticatedHeaders())
            ->getJson('/api/v1/enterprises/999999');

        $response->assertStatus(404);
    }

    public function test_listar_enterprises_filtrando_por_company_id(): void
    {
        $company = Company::factory()->create();
        Enterprise::factory()->count(2)->create(['company_id' => $company->id]);
        Enterprise::factory()->count(3)->create(); // ruido: otra compañía

        $response = $this->withHeaders($this->authenticatedHeaders())
            ->getJson("/api/v1/enterprises?company_id={$company->id}");

        $response->assertOk()->assertJsonCount(2, 'data');
    }
}
