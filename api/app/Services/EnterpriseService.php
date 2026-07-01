<?php

namespace App\Services;

use App\Enums\CompanyStatus;
use App\Enums\EnterpriseStatus;
use App\Exceptions\Domain\CompanyInactiveException;
use App\Exceptions\Domain\CompanyNotFoundException;
use App\Exceptions\Domain\EnterpriseHasActiveBranchsException;
use App\Exceptions\Domain\EnterpriseNotFoundException;
use App\Models\Enterprise;
use App\Repositories\Contracts\BranchRepositoryInterface;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Repositories\Contracts\EnterpriseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EnterpriseService
{
    public function __construct(
        private readonly EnterpriseRepositoryInterface $enterprises,
        private readonly CompanyRepositoryInterface $companies,
        private readonly BranchRepositoryInterface $branchs,
    ) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->enterprises->paginate($filters, $perPage);
    }

    public function getById(int $id): Enterprise
    {
        $enterprise = $this->enterprises->findById($id);

        if (! $enterprise) {
            throw new EnterpriseNotFoundException($id);
        }

        return $enterprise;
    }

    /**
     * Requisito explícito de la guía: verificar preexistencia de la
     * compañía padre. Además, bloquea la creación si el padre está inactivo.
     */
    public function create(array $data): Enterprise
    {
        $company = $this->companies->findById($data['company_id']);

        if (! $company) {
            throw new CompanyNotFoundException($data['company_id']);
        }

        if ($company->companys_status === CompanyStatus::Inactive) {
            throw new CompanyInactiveException($company->id);
        }

        return $this->enterprises->create($data);
    }

    public function update(int $id, array $data): Enterprise
    {
        $enterprise = $this->getById($id);

        return $this->enterprises->update($enterprise, $data);
    }

    public function deactivate(int $id): Enterprise
    {
        $enterprise = $this->getById($id);

        return $this->enterprises->update($enterprise, [
            'enterprises_status' => EnterpriseStatus::Inactive->value,
        ]);
    }

    public function activate(int $id): Enterprise
    {
        $enterprise = $this->getById($id);

        return $this->enterprises->update($enterprise, [
            'enterprises_status' => EnterpriseStatus::Active->value,
        ]);
    }

    public function delete(int $id): void
    {
        $enterprise = $this->getById($id);

        $hasActiveBranchs = $this->branchs
            ->filterBy(['enterprise_id' => $enterprise->id, 'branchs_status' => EnterpriseStatus::Active->value])
            ->exists();

        if ($hasActiveBranchs) {
            throw new EnterpriseHasActiveBranchsException($id);
        }

        $this->enterprises->softDelete($enterprise);
    }
}
