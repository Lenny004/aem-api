<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnterpriseRequest;
use App\Http\Requests\UpdateEnterpriseRequest;
use App\Http\Resources\EnterpriseResource;
use App\Services\EnterpriseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * Capa de entrada HTTP para Enterprise (empresa dentro de un Holding).
 * Misma forma delgada que CompanyController: validar → Service → Resource.
 */
class EnterpriseController extends Controller
{
    public function __construct(private readonly EnterpriseService $service)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['company_id', 'enterprises_status']);
        $perPage = (int) $request->query('per_page', 15);

        return EnterpriseResource::collection($this->service->list($filters, $perPage));
    }

    public function show(int $id): EnterpriseResource
    {
        return new EnterpriseResource($this->service->getById($id));
    }

    public function store(StoreEnterpriseRequest $request): JsonResponse
    {
        $enterprise = $this->service->create($request->validated());

        return (new EnterpriseResource($enterprise))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateEnterpriseRequest $request, int $id): EnterpriseResource
    {
        return new EnterpriseResource($this->service->update($id, $request->validated()));
    }

    public function destroy(int $id): Response
    {
        $this->service->delete($id);

        return response()->noContent();
    }

    public function activate(int $id): EnterpriseResource
    {
        return new EnterpriseResource($this->service->activate($id));
    }

    public function deactivate(int $id): EnterpriseResource
    {
        return new EnterpriseResource($this->service->deactivate($id));
    }
}
