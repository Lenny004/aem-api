<?php

namespace App\Repositories\Contracts;

use App\Models\Company;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

interface CompanyRepositoryInterface
{
    /**
     * Aplica filtros opcionales y devuelve el query builder (sin ejecutar).
     *
     * Filtros soportados:
     * - companys_status (string): 'active' | 'inactive'
     */
    public function filterBy(array $filters = []): Builder;

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Company;

    public function create(array $data): Company;

    public function update(Company $company, array $data): Company;

    public function softDelete(Company $company): bool;
}
