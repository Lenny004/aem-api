<?php

namespace App\Repositories\Eloquent;

use App\Models\Enterprise;
use App\Repositories\Contracts\EnterpriseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EnterpriseRepository implements EnterpriseRepositoryInterface
{
    public function filterBy(array $filters = []): Builder
    {
        $query = Enterprise::query();

        if (isset($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }

        if (isset($filters['enterprises_status'])) {
            $query->where('enterprises_status', $filters['enterprises_status']);
        }

        return $query->orderBy('name');
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->filterBy($filters)->paginate($perPage);
    }

    public function findById(int $id): ?Enterprise
    {
        return Enterprise::find($id);
    }

    public function create(array $data): Enterprise
    {
        return Enterprise::create($data);
    }

    public function update(Enterprise $enterprise, array $data): Enterprise
    {
        $enterprise->update($data);

        return $enterprise->fresh();
    }

    public function softDelete(Enterprise $enterprise): bool
    {
        return (bool) $enterprise->delete();
    }
}
