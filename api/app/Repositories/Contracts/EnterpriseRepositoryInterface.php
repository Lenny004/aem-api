<?php

namespace App\Repositories\Contracts;

use App\Models\Enterprise;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

interface EnterpriseRepositoryInterface
{
    /**
     * Filtros soportados:
     * - company_id (int): Holding padre
     * - enterprises_status (string): 'active' | 'inactive'
     */
    public function filterBy(array $filters = []): Builder;

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Enterprise;

    public function create(array $data): Enterprise;

    public function update(Enterprise $enterprise, array $data): Enterprise;

    public function softDelete(Enterprise $enterprise): bool;
}
