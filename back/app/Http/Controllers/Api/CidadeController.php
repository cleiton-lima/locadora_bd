<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Cidade\StoreCidadeRequest;
use App\Http\Requests\Cidade\UpdateCidadeRequest;
use App\Services\CidadeService;

class CidadeController extends SimpleCrudController
{
    public function __construct(private readonly CidadeService $cidadeService)
    {
        parent::__construct($cidadeService);
    }

    public function store(StoreCidadeRequest $request)
    {
        $data = $request->validated();

        if (! $this->cidadeService->estadoExiste($data['estado_sigla'])) {
            return $this->relacionadoNaoEncontrado();
        }

        $id = $this->cidadeService->criar($data);

        return response()->json([
            'message' => 'Registro criado com sucesso.',
            'id' => $id,
        ], 201);
    }

    public function update(UpdateCidadeRequest $request, int $id)
    {
        if (! $this->cidadeService->buscarPorId($id)) {
            return response()->json([
                'message' => 'Registro não encontrado.',
            ], 404);
        }

        $data = $request->validated();

        if (! $this->cidadeService->estadoExiste($data['estado_sigla'])) {
            return $this->relacionadoNaoEncontrado();
        }

        $this->cidadeService->atualizar($id, $data);

        return response()->json([
            'message' => 'Registro atualizado com sucesso.',
        ]);
    }
}
