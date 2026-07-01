<?php

namespace App\Services;

use App\Enums\BranchStatus;
use App\Enums\EnterpriseStatus;
use App\Exceptions\Domain\BranchNotFoundException;
use App\Exceptions\Domain\EnterpriseInactiveException;
use App\Exceptions\Domain\EnterpriseNotFoundException;
use App\Models\Branch;
use App\Repositories\Contracts\BranchRepositoryInterface;
use App\Repositories\Contracts\EnterpriseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BranchService
{
    public function __construct(
        private readonly BranchRepositoryInterface $branchs,
        private readonly EnterpriseRepositoryInterface $enterprises,
    ) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->branchs->paginate($filters, $perPage);
    }

    public function getById(int $id): Branch
    {
        $branch = $this->branchs->findById($id);

        if (! $branch) {
            throw new BranchNotFoundException($id);
        }

        return $branch;
    }

    /**
     * Verifica preexistencia de la enterprise padre (mismo requisito que
     * EnterpriseService::create() aplica un nivel más abajo) y que esté activa.
     */
    public function create(array $data): Branch
    {
        $enterprise = $this->enterprises->findById($data['enterprise_id']);

        if (! $enterprise) {
            throw new EnterpriseNotFoundException($data['enterprise_id']);
        }

        if ($enterprise->enterprises_status === EnterpriseStatus::Inactive) {
            throw new EnterpriseInactiveException($enterprise->id);
        }

        return $this->branchs->create($data);
    }

    public function update(int $id, array $data): Branch
    {
        $branch = $this->getById($id);

        return $this->branchs->update($branch, $data);
    }

    public function deactivate(int $id): Branch
    {
        $branch = $this->getById($id);

        return $this->branchs->update($branch, [
            'branchs_status' => BranchStatus::Inactive->value,
        ]);
    }

    public function activate(int $id): Branch
    {
        $branch = $this->getById($id);

        return $this->branchs->update($branch, [
            'branchs_status' => BranchStatus::Active->value,
        ]);
    }

    public function delete(int $id): void
    {
        $branch = $this->getById($id);

        $this->branchs->softDelete($branch);
    }
}
