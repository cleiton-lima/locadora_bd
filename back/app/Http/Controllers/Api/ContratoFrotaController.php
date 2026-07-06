<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContratoFrota\StoreContratoFrotaRequest;
use App\Http\Requests\ContratoFrota\UpdateContratoFrotaRequest;
use App\Services\ContratoFrotaService;

class ContratoFrotaController extends Controller
{
    public function __construct(private readonly ContratoFrotaService $service)
    {
    }

    public function index()
    {
        return response()->json(
            $this->service->listar()
        );
    }

    public function store(StoreContratoFrotaRequest $request)
    {
        $data = $request->validated();

        if (! $this->service->gerenteComercialExiste($data['gerente_comercial_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! $this->service->empresaExiste($data['empresa_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        $id = $this->service->criar($data);

        return response()->json([
            'message' => 'Registro criado com sucesso.',
            'id' => $id,
        ], 201);
    }

    public function show(int $id)
    {
        $registro = $this->service->buscarPorId($id);

        if (! $registro) {
            return response()->json([
                'message' => 'Registro não encontrado.',
            ], 404);
        }

        return response()->json($registro);
    }

    public function update(UpdateContratoFrotaRequest $request, int $id)
    {
        if (! $this->service->buscarPorId($id)) {
            return response()->json([
                'message' => 'Registro não encontrado.',
            ], 404);
        }

        $data = $request->validated();

        if (! $this->service->gerenteComercialExiste($data['gerente_comercial_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! $this->service->empresaExiste($data['empresa_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        $this->service->atualizar($id, $data);

        return response()->json([
            'message' => 'Registro atualizado com sucesso.',
        ]);
    }

    public function destroy(int $id)
    {
        if (! $this->service->buscarPorId($id)) {
            return response()->json([
                'message' => 'Registro não encontrado.',
            ], 404);
        }

        $this->service->remover($id);

        return response()->json(null, 204);
    }
}
