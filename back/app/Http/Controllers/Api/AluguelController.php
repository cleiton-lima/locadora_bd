<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Aluguel\DevolverAluguelRequest;
use App\Http\Requests\Aluguel\StoreAluguelRequest;
use App\Http\Requests\Aluguel\UpdateAluguelRequest;
use App\Services\AluguelService;

class AluguelController extends Controller
{
    public function __construct(private readonly AluguelService $service)
    {
    }

    public function index()
    {
        return response()->json(
            $this->service->listar()
        );
    }

    public function store(StoreAluguelRequest $request)
    {
        $data = $request->validated();

        if (! $this->service->atendenteExiste($data['atendente_entrega_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! empty($data['atendente_devolucao_id']) && ! $this->service->atendenteExiste($data['atendente_devolucao_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! empty($data['pessoa_fisica_id']) && ! $this->service->pessoaFisicaExiste($data['pessoa_fisica_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! empty($data['contrato_frota_id']) && ! $this->service->contratoFrotaExiste($data['contrato_frota_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! $this->service->veiculoExiste($data['veiculo_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! empty($data['pessoa_fisica_id']) && ! $this->service->cnhValidaParaAluguel($data['pessoa_fisica_id'], $data['data_inicial'])) {
            return response()->json([
                'message' => 'CNH vencida para a data de retirada.',
            ], 409);
        }

        $bloqueio = $this->service->bloqueioVeiculoParaAluguel(
            $data['veiculo_id'],
            ! empty($data['pessoa_fisica_id'])
        );

        if ($bloqueio !== null) {
            return response()->json([
                'message' => $bloqueio,
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

    public function update(UpdateAluguelRequest $request, int $id)
    {
        if (! $this->service->buscarPorId($id)) {
            return response()->json([
                'message' => 'Registro não encontrado.',
            ], 404);
        }

        $data = $request->validated();

        if (! $this->service->atendenteExiste($data['atendente_entrega_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! empty($data['atendente_devolucao_id']) && ! $this->service->atendenteExiste($data['atendente_devolucao_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! empty($data['pessoa_fisica_id']) && ! $this->service->pessoaFisicaExiste($data['pessoa_fisica_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! empty($data['contrato_frota_id']) && ! $this->service->contratoFrotaExiste($data['contrato_frota_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        if (! $this->service->veiculoExiste($data['veiculo_id'])) {
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

    public function devolver(DevolverAluguelRequest $request, int $id)
    {
        if (! $this->service->buscarPorId($id)) {
            return response()->json([
                'message' => 'Registro não encontrado.',
            ], 404);
        }

        $data = $request->validated();

        if (! $this->service->atendenteExiste($data['atendente_devolucao_id'])) {
            return response()->json([
                'message' => 'Registro relacionado não encontrado.',
            ], 404);
        }

        $this->service->devolver($id, $data);

        return response()->json([
            'message' => 'Registro atualizado com sucesso.',
        ]);
    }
}
