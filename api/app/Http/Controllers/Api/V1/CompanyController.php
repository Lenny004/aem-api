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
use OpenApi\Attributes as OA;

/**
 * Capa de entrada HTTP para Company (Holding).
 * Solo valida → delega en CompanyService → serializa con CompanyResource.
 * Recibe int $id en lugar de Route Model Binding para que el Service sea la única fuente de 404.
 */
class CompanyController extends Controller
{
    public function __construct(private readonly CompanyService $service)
    {
    }

    #[OA\Get(
        path: '/v1/companys',
        tags: ['Companys'],
        summary: 'Listar compañías (Holdings), con filtro y paginación',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'companys_status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['active', 'inactive'])),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Listado paginado', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/CompanyResource'))]
            )),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['companys_status']);
        $perPage = (int) $request->query('per_page', 15);

        return CompanyResource::collection($this->service->list($filters, $perPage));
    }

    #[OA\Get(
        path: '/v1/companys/{id}',
        tags: ['Companys'],
        summary: 'Obtener una compañía por ID',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(ref: '#/components/schemas/CompanyResource')),
            new OA\Response(response: 404, description: 'No existe', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function show(int $id): CompanyResource
    {
        return new CompanyResource($this->service->getById($id));
    }

    #[OA\Post(
        path: '/v1/companys',
        tags: ['Companys'],
        summary: 'Crear una compañía',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['name', 'doc_number'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Grupo AEM SV'),
                new OA\Property(property: 'doc_number', type: 'string', example: 'CO-0001'),
                new OA\Property(property: 'email', type: 'string', example: 'contacto@aem.sv'),
                new OA\Property(property: 'phone', type: 'string', example: '2222-3333'),
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: 'Creada', content: new OA\JsonContent(ref: '#/components/schemas/CompanyResource')),
            new OA\Response(response: 422, description: 'Validación fallida (payload incompleto o doc_number duplicado)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $company = $this->service->create($request->validated());

        return (new CompanyResource($company))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    #[OA\Put(
        path: '/v1/companys/{id}',
        tags: ['Companys'],
        summary: 'Actualizar una compañía',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'doc_number', type: 'string'),
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'phone', type: 'string'),
                new OA\Property(property: 'companys_status', type: 'string', enum: ['active', 'inactive']),
            ]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Actualizada', content: new OA\JsonContent(ref: '#/components/schemas/CompanyResource')),
            new OA\Response(response: 404, description: 'No existe', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Validación fallida', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function update(UpdateCompanyRequest $request, int $id): CompanyResource
    {
        return new CompanyResource($this->service->update($id, $request->validated()));
    }

    #[OA\Delete(
        path: '/v1/companys/{id}',
        tags: ['Companys'],
        summary: 'Eliminar (soft delete) una compañía',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Eliminada'),
            new OA\Response(response: 404, description: 'No existe', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 409, description: 'Tiene empresas asociadas activas — desactívalas o elimínalas primero', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function destroy(int $id): Response
    {
        $this->service->delete($id);

        return response()->noContent();
    }

    /**
     * PATCH /companys/{id}/activate — revierte deactivate() sin borrar el registro.
     */
    #[OA\Patch(
        path: '/v1/companys/{id}/activate',
        tags: ['Companys'],
        summary: 'Reactivar una compañía (companys_status = active)',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Reactivada', content: new OA\JsonContent(ref: '#/components/schemas/CompanyResource')),
            new OA\Response(response: 404, description: 'No existe', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function activate(int $id): CompanyResource
    {
        return new CompanyResource($this->service->activate($id));
    }

    /**
     * PATCH /companys/{id}/deactivate — cambia status a inactive; no hace soft delete.
     */
    #[OA\Patch(
        path: '/v1/companys/{id}/deactivate',
        tags: ['Companys'],
        summary: 'Desactivar una compañía (companys_status = inactive; no borra el registro)',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Desactivada', content: new OA\JsonContent(ref: '#/components/schemas/CompanyResource')),
            new OA\Response(response: 404, description: 'No existe', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function deactivate(int $id): CompanyResource
    {
        return new CompanyResource($this->service->deactivate($id));
    }
}
