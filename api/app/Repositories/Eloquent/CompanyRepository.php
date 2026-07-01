<?php

namespace App\Repositories\Eloquent;

use App\Models\Company;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class CompanyRepository implements CompanyRepositoryInterface
{
    public function filterBy(array $filters = []): Builder
    {
        $query = Company::query();

        if (isset($filters['companys_status'])) {
            $query->where('companys_status', $filters['companys_status']);
        }

        return $query->orderBy('name');
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->filterBy($filters)->paginate($perPage);
    }

    public function findById(int $id): ?Company
    {
        return Company::find($id);
    }

    public function create(array $data): Company
    {
        return Company::create($data);
    }

    public function update(Company $company, array $data): Company
    {
        $company->update($data);

        return $company->fresh();
    }

    public function softDelete(Company $company): bool
    {
        return (bool) $company->delete();
    }
}
