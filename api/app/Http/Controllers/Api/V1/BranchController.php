<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Http\Resources\BranchResource;
use App\Services\BranchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class BranchController extends Controller
{
    public function __construct(private readonly BranchService $service)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['enterprises_id', 'municipality_codigo', 'branchs_status']);
        $perPage = (int) $request->query('per_page', 15);

        return BranchResource::collection($this->service->list($filters, $perPage));
    }

    public function show(int $id): BranchResource
    {
        return new BranchResource($this->service->getById($id));
    }

    public function store(StoreBranchRequest $request): JsonResponse
    {
        $branch = $this->service->create($request->validated());

        return (new BranchResource($branch))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateBranchRequest $request, int $id): BranchResource
    {
        return new BranchResource($this->service->update($id, $request->validated()));
    }

    public function destroy(int $id): Response
    {
        $this->service->delete($id);

        return response()->noContent();
    }

    public function activate(int $id): BranchResource
    {
        return new BranchResource($this->service->activate($id));
    }

    public function deactivate(int $id): BranchResource
    {
        return new BranchResource($this->service->deactivate($id));
    }
}
