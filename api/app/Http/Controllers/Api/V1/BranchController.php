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
use OpenApi\Attributes as OA;

/**
 * Capa de entrada HTTP para Branch (sucursal).
 * Acepta filtros por empresa padre, municipio y estado en index().
 */
class BranchController extends Controller
{
    public function __construct(private readonly BranchService $service)
    {
    }

    #[OA\Get(
        path: '/v1/branchs',
        tags: ['Branchs'],
        summary: 'Listar sucursales, con filtrado avanzado por empresa y municipio',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'enterprises_id', in: 'query', description: 'ID de la empresa asociada (alias normalizado a enterprise_id internamente)', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'municipality_codigo', in: 'query', description: 'Código de municipio, ej. SS-01', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'branchs_status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['active', 'inactive', 'suspended'])),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Listado paginado', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/BranchResource'))]
            )),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['enterprises_id', 'municipality_codigo', 'branchs_status']);
        $perPage = (int) $request->query('per_page', 15);

        return BranchResource::collection($this->service->list($filters, $perPage));
    }

    #[OA\Get(
        path: '/v1/branchs/{id}',
        tags: ['Branchs'],
        summary: 'Obtener una sucursal por ID',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(ref: '#/components/schemas/BranchResource')),
            new OA\Response(response: 404, description: 'No existe', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function show(int $id): BranchResource
    {
        return new BranchResource($this->service->getById($id));
    }

    #[OA\Post(
        path: '/v1/branchs',
        tags: ['Branchs'],
        summary: 'Crear una sucursal (verifica que la empresa padre exista y esté activa)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['enterprise_id', 'name', 'address', 'municipality_codigo'],
            properties: [
                new OA\Property(property: 'enterprise_id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'Sucursal Centro'),
                new OA\Property(property: 'address', type: 'string', example: 'Calle Arce 123, San Salvador'),
                new OA\Property(property: 'municipality_codigo', type: 'string', example: 'SS-01'),
                new OA\Property(property: 'phone', type: 'string'),
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: 'Creada', content: new OA\JsonContent(ref: '#/components/schemas/BranchResource')),
            new OA\Response(response: 409, description: 'La empresa padre está inactiva', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Validación fallida (incluye municipality_codigo fuera del catálogo de 44 municipios, o enterprise_id que no existe en la tabla enterprises)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function store(StoreBranchRequest $request): JsonResponse
    {
        $branch = $this->service->create($request->validated());

        return (new BranchResource($branch))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    #[OA\Put(
        path: '/v1/branchs/{id}',
        tags: ['Branchs'],
        summary: 'Actualizar una sucursal',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'address', type: 'string'),
                new OA\Property(property: 'municipality_codigo', type: 'string'),
                new OA\Property(property: 'phone', type: 'string'),
                new OA\Property(property: 'branchs_status', type: 'string', enum: ['active', 'inactive', 'suspended']),
            ]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Actualizada', content: new OA\JsonContent(ref: '#/components/schemas/BranchResource')),
            new OA\Response(response: 404, description: 'No existe', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Validación fallida', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function update(UpdateBranchRequest $request, int $id): BranchResource
    {
        return new BranchResource($this->service->update($id, $request->validated()));
    }

    #[OA\Delete(
        path: '/v1/branchs/{id}',
        tags: ['Branchs'],
        summary: 'Eliminar (soft delete) una sucursal',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Eliminada'),
            new OA\Response(response: 404, description: 'No existe', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function destroy(int $id): Response
    {
        $this->service->delete($id);

        return response()->noContent();
    }

    #[OA\Patch(
        path: '/v1/branchs/{id}/activate',
        tags: ['Branchs'],
        summary: 'Reactivar una sucursal',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Reactivada', content: new OA\JsonContent(ref: '#/components/schemas/BranchResource')),
            new OA\Response(response: 404, description: 'No existe', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function activate(int $id): BranchResource
    {
        return new BranchResource($this->service->activate($id));
    }

    #[OA\Patch(
        path: '/v1/branchs/{id}/deactivate',
        tags: ['Branchs'],
        summary: 'Desactivar una sucursal (cierre temporal, no la elimina)',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Desactivada', content: new OA\JsonContent(ref: '#/components/schemas/BranchResource')),
            new OA\Response(response: 404, description: 'No existe', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function deactivate(int $id): BranchResource
    {
        return new BranchResource($this->service->deactivate($id));
    }
}
