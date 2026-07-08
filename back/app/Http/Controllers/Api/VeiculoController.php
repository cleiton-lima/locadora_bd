<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Veiculo\StoreVeiculoRequest;
use App\Http\Requests\Veiculo\UpdateVeiculoRequest;
use App\Services\VeiculoService;

class VeiculoController extends Controller
{
    public function __construct(private readonly VeiculoService $service)
    {
    }

    public function index()
    {
        return response()->json(
            $this->service->listar()
        );
    }

    public function store(StoreVeiculoRequest $request)
    {
        $data = $this->normalizarAdministradores($request->validated());

        if (! $this->service->administradorExiste($data['administrador_cadastro_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! empty($data['administrador_responsavel_id']) && ! $this->service->administradorExiste($data['administrador_responsavel_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! $this->service->filialExiste($data['filial_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! $this->service->loteExiste($data['lote_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if ($this->service->placaExiste($data['placa'])) {
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

    public function update(UpdateVeiculoRequest $request, int $id)
    {
        if (! $this->service->buscarPorId($id)) {
            return response()->json([
                'message' => 'Registro não encontrado.',
            ], 404);
        }

        $data = $this->normalizarAdministradores($request->validated());

        if (! $this->service->administradorExiste($data['administrador_cadastro_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! empty($data['administrador_responsavel_id']) && ! $this->service->administradorExiste($data['administrador_responsavel_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! $this->service->filialExiste($data['filial_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! $this->service->loteExiste($data['lote_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if ($this->service->placaExiste($data['placa'], $id)) {
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

    private function normalizarAdministradores(array $data): array
    {
        if ($data['status'] === 'VENDIDO') {
            $data['administrador_responsavel_id'] = null;
            return $data;
        }

        if (empty($data['administrador_responsavel_id'])) {
            $data['administrador_responsavel_id'] = $data['administrador_cadastro_id'];
        }

        return $data;
    }
}
