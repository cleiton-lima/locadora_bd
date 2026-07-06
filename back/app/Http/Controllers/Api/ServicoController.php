<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Servico\StoreServicoRequest;
use App\Http\Requests\Servico\UpdateServicoRequest;
use App\Services\ServicoService;

class ServicoController extends Controller
{
    public function __construct(private readonly ServicoService $service)
    {
    }

    public function index()
    {
        return response()->json(
            $this->service->listar()
        );
    }

    public function store(StoreServicoRequest $request)
    {
        $data = $request->validated();

        if (! $this->service->veiculoExiste($data['veiculo_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! $this->service->administradorExiste($data['administrador_funcionario_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! $this->service->oficinaExiste($data['oficina_id'])) {
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

    public function update(UpdateServicoRequest $request, int $id)
    {
        if (! $this->service->buscarPorId($id)) {
            return response()->json([
                'message' => 'Registro não encontrado.',
            ], 404);
        }

        $data = $request->validated();

        if (! $this->service->veiculoExiste($data['veiculo_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! $this->service->administradorExiste($data['administrador_funcionario_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! $this->service->oficinaExiste($data['oficina_id'])) {
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
