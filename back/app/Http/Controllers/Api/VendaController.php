<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Venda\StoreVendaRequest;
use App\Http\Requests\Venda\UpdateVendaRequest;
use App\Services\VendaService;

class VendaController extends Controller
{
    public function __construct(private readonly VendaService $service)
    {
    }

    public function index()
    {
        return response()->json(
            $this->service->listar()
        );
    }

    public function store(StoreVendaRequest $request)
    {
        $data = $request->validated();

        if (! $this->service->veiculoExiste($data['veiculo_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! $this->service->pessoaFisicaExiste($data['pessoa_fisica_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! $this->service->gerenteComercialExiste($data['gerente_comercial_funcionario_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! $this->service->veiculoDisponivelParaVenda($data['veiculo_id'])) {
            return response()->json([
                'message' => 'Veículo indisponível para venda: está alugado.',
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

    public function update(UpdateVendaRequest $request, int $id)
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

        if (! $this->service->pessoaFisicaExiste($data['pessoa_fisica_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! $this->service->gerenteComercialExiste($data['gerente_comercial_funcionario_id'])) {
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
