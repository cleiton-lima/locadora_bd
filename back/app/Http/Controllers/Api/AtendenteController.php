<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Atendente\StoreAtendenteRequest;
use App\Http\Requests\Atendente\UpdateAtendenteRequest;
use App\Services\AtendenteService;

class AtendenteController extends Controller
{
    public function __construct(private readonly AtendenteService $service)
    {
    }

    public function index()
    {
        return response()->json(
            $this->service->listar()
        );
    }

    public function store(StoreAtendenteRequest $request)
    {
        $data = $request->validated();

        if (! $this->service->funcionarioExiste($data['funcionario_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if ($this->service->buscarPorId($data['funcionario_id'])) {
            return response()->json([
                'message' => 'Já existe um registro com estes dados.',
            ], 409);
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

    public function update(UpdateAtendenteRequest $request, int $id)
    {
        if (! $this->service->buscarPorId($id)) {
            return response()->json([
                'message' => 'Registro não encontrado.',
            ], 404);
        }

        $data = $request->validated();

        if (! $this->service->funcionarioExiste($data['funcionario_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if ($data['funcionario_id'] !== $id && $this->service->buscarPorId($data['funcionario_id'])) {
            return response()->json([
                'message' => 'Já existe um registro com estes dados.',
            ], 409);
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
