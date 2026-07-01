<?php

namespace App\Repositories\Contracts;

use App\Models\Branch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

interface BranchRepositoryInterface
{
    /**
     * Filtros soportados (requisito de la guía en GET /api/v1/branchs):
     * - enterprise_id (int): ID de la empresa asociada
     * - enterprises_id (int): alias aceptado del query param de la API → se normaliza a enterprise_id
     * - municipality_codigo (string): código de municipio
     * - branchs_status (string): 'active' | 'inactive' | 'suspended'
     */
    public function filterBy(array $filters = []): Builder;

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Branch;

    public function create(array $data): Branch;

    public function update(Branch $branch, array $data): Branch;

    public function softDelete(Branch $branch): bool;
}
