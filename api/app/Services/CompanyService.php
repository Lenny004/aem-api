<?php

namespace App\Services;

use App\Enums\CompanyStatus;
use App\Exceptions\Domain\CompanyHasActiveEnterprisesException;
use App\Exceptions\Domain\CompanyNotFoundException;
use App\Models\Company;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Repositories\Contracts\EnterpriseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CompanyService
{
    public function __construct(
        private readonly CompanyRepositoryInterface $companies,
        private readonly EnterpriseRepositoryInterface $enterprises,
    ) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->companies->paginate($filters, $perPage);
    }

    public function getById(int $id): Company
    {
        $company = $this->companies->findById($id);

        if (! $company) {
            throw new CompanyNotFoundException($id);
        }

        return $company;
    }

    public function create(array $data): Company
    {
        return $this->companies->create($data);
    }

    public function update(int $id, array $data): Company
    {
        $company = $this->getById($id);

        return $this->companies->update($company, $data);
    }

    /**
     * Cambia companys_status a 'inactive'. Reversible vía activate().
     * No afecta deleted_at — la compañía sigue existiendo y visible.
     */
    public function deactivate(int $id): Company
    {
        $company = $this->getById($id);

        return $this->companies->update($company, [
            'companys_status' => CompanyStatus::Inactive->value,
        ]);
    }

    public function activate(int $id): Company
    {
        $company = $this->getById($id);

        return $this->companies->update($company, [
            'companys_status' => CompanyStatus::Active->value,
        ]);
    }

    /**
     * Soft delete (deleted_at). Distinto de deactivate(): esto es la "eliminación segura"
     */
    public function delete(int $id): void
    {
        $company = $this->getById($id);

        $hasActiveEnterprises = $this->enterprises
            ->filterBy(['company_id' => $company->id, 'enterprises_status' => CompanyStatus::Active->value])
            ->exists();

        if ($hasActiveEnterprises) {
            throw new CompanyHasActiveEnterprisesException($id);
        }

        $this->companies->softDelete($company);
    }
}
