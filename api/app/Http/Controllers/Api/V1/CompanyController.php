<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CompanyController extends Controller
{
    public function __construct(private readonly CompanyService $service)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['companys_status']);
        $perPage = (int) $request->query('per_page', 15);

        return CompanyResource::collection($this->service->list($filters, $perPage));
    }

    public function show(int $id): CompanyResource
    {
        return new CompanyResource($this->service->getById($id));
    }

    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $company = $this->service->create($request->validated());

        return (new CompanyResource($company))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateCompanyRequest $request, int $id): CompanyResource
    {
        return new CompanyResource($this->service->update($id, $request->validated()));
    }

    public function destroy(int $id): Response
    {
        $this->service->delete($id);

        return response()->noContent();
    }
}
