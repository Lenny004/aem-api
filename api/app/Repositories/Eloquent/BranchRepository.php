<?php

namespace App\Repositories\Eloquent;

use App\Models\Branch;
use App\Repositories\Contracts\BranchRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class BranchRepository implements BranchRepositoryInterface
{
    public function filterBy(array $filters = []): Builder
    {
        // La guía documenta el query param como enterprises_id; normalizamos aquí
        // para que Controller/Service puedan pasar el nombre tal cual llega del HTTP.
        if (isset($filters['enterprises_id']) && ! isset($filters['enterprise_id'])) {
            $filters['enterprise_id'] = $filters['enterprises_id'];
        }
        unset($filters['enterprises_id']);

        $query = Branch::query();

        if (isset($filters['enterprise_id'])) {
            $query->where('enterprise_id', $filters['enterprise_id']);
        }

        if (isset($filters['municipality_codigo'])) {
            $query->where('municipality_codigo', $filters['municipality_codigo']);
        }

        if (isset($filters['branchs_status'])) {
            $query->where('branchs_status', $filters['branchs_status']);
        }

        return $query->orderBy('name');
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->filterBy($filters)->paginate($perPage);
    }

    public function findById(int $id): ?Branch
    {
        return Branch::find($id);
    }

    public function create(array $data): Branch
    {
        return Branch::create($data);
    }

    public function update(Branch $branch, array $data): Branch
    {
        $branch->update($data);

        return $branch->fresh();
    }

    public function softDelete(Branch $branch): bool
    {
        return (bool) $branch->delete();
    }
}
