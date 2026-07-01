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
use OpenApi\Attributes as OA;

/**
 * Capa de entrada HTTP para Enterprise (empresa dentro de un Holding).
 * Misma forma delgada que CompanyController: validar → Service → Resource.
 */
class EnterpriseController extends Controller
{
    public function __construct(private readonly EnterpriseService $service)
    {
    }

    #[OA\Get(
        path: '/v1/enterprises',
        tags: ['Enterprises'],
        summary: 'Listar empresas asociadas, filtrando por compañía padre y/o estado',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'company_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'enterprises_status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['active', 'inactive'])),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Listado paginado', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/EnterpriseResource'))]
            )),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['company_id', 'enterprises_status']);
        $perPage = (int) $request->query('per_page', 15);

        return EnterpriseResource::collection($this->service->list($filters, $perPage));
    }

    #[OA\Get(
        path: '/v1/enterprises/{id}',
        tags: ['Enterprises'],
        summary: 'Obtener una empresa asociada por ID',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(ref: '#/components/schemas/EnterpriseResource')),
            new OA\Response(response: 404, description: 'No existe', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function show(int $id): EnterpriseResource
    {
        return new EnterpriseResource($this->service->getById($id));
    }

    #[OA\Post(
        path: '/v1/enterprises',
        tags: ['Enterprises'],
        summary: 'Crear una empresa asociada (verifica que la compañía padre exista y esté activa)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['company_id', 'name', 'doc_number'],
            properties: [
                new OA\Property(property: 'company_id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'Enterprise Asociada SV'),
                new OA\Property(property: 'doc_number', type: 'string', example: 'EN-0001'),
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'phone', type: 'string'),
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: 'Creada', content: new OA\JsonContent(ref: '#/components/schemas/EnterpriseResource')),
            new OA\Response(response: 409, description: 'La compañía padre está inactiva', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Validación fallida (payload incompleto, doc_number duplicado, o company_id que no existe en la tabla companys)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function store(StoreEnterpriseRequest $request): JsonResponse
    {
        $enterprise = $this->service->create($request->validated());

        return (new EnterpriseResource($enterprise))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    #[OA\Put(
        path: '/v1/enterprises/{id}',
        tags: ['Enterprises'],
        summary: 'Actualizar una empresa asociada',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'doc_number', type: 'string'),
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'phone', type: 'string'),
                new OA\Property(property: 'enterprises_status', type: 'string', enum: ['active', 'inactive']),
            ]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Actualizada', content: new OA\JsonContent(ref: '#/components/schemas/EnterpriseResource')),
            new OA\Response(response: 404, description: 'No existe', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Validación fallida', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function update(UpdateEnterpriseRequest $request, int $id): EnterpriseResource
    {
        return new EnterpriseResource($this->service->update($id, $request->validated()));
    }

    #[OA\Delete(
        path: '/v1/enterprises/{id}',
        tags: ['Enterprises'],
        summary: 'Eliminar (soft delete) una empresa asociada',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Eliminada'),
            new OA\Response(response: 404, description: 'No existe', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 409, description: 'Tiene sucursales activas — desactívalas o elimínalas primero', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function destroy(int $id): Response
    {
        $this->service->delete($id);

        return response()->noContent();
    }

    #[OA\Patch(
        path: '/v1/enterprises/{id}/activate',
        tags: ['Enterprises'],
        summary: 'Reactivar una empresa asociada',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Reactivada', content: new OA\JsonContent(ref: '#/components/schemas/EnterpriseResource')),
            new OA\Response(response: 404, description: 'No existe', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function activate(int $id): EnterpriseResource
    {
        return new EnterpriseResource($this->service->activate($id));
    }

    #[OA\Patch(
        path: '/v1/enterprises/{id}/deactivate',
        tags: ['Enterprises'],
        summary: 'Desactivar una empresa asociada (bloquea nuevas sucursales bajo ella)',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Desactivada', content: new OA\JsonContent(ref: '#/components/schemas/EnterpriseResource')),
            new OA\Response(response: 404, description: 'No existe', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function deactivate(int $id): EnterpriseResource
    {
        return new EnterpriseResource($this->service->deactivate($id));
    }
}
