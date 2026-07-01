<?php

namespace Tests\Unit\Services;

use App\Enums\CompanyStatus;
use App\Exceptions\Domain\CompanyInactiveException;
use App\Exceptions\Domain\CompanyNotFoundException;
use App\Models\Company;
use App\Services\EnterpriseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnterpriseServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_lanza_company_not_found_exception_si_el_padre_no_existe(): void
    {
        $this->expectException(CompanyNotFoundException::class);

        app(EnterpriseService::class)->create([
            'company_id' => 999999,
            'name' => 'Enterprise Huérfana',
            'doc_number' => 'EN-9999',
        ]);
    }

    public function test_create_lanza_company_inactive_exception_si_el_padre_esta_inactivo(): void
    {
        $company = Company::factory()->create(['companys_status' => CompanyStatus::Inactive]);

        $this->expectException(CompanyInactiveException::class);

        app(EnterpriseService::class)->create([
            'company_id' => $company->id,
            'name' => 'Enterprise Bloqueada',
            'doc_number' => 'EN-8888',
        ]);
    }

    public function test_create_funciona_si_el_padre_existe_y_esta_activo(): void
    {
        $company = Company::factory()->create();

        $enterprise = app(EnterpriseService::class)->create([
            'company_id' => $company->id,
            'name' => 'Enterprise Válida',
            'doc_number' => 'EN-0001',
        ]);

        $this->assertSame($company->id, $enterprise->company_id);
    }
}
