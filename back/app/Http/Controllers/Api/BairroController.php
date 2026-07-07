<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Bairro\StoreBairroRequest;
use App\Http\Requests\Bairro\UpdateBairroRequest;
use App\Services\BairroService;

class BairroController extends SimpleCrudController
{
    public function __construct(private readonly BairroService $bairroService)
    {
        parent::__construct($bairroService);
    }

    public function store(StoreBairroRequest $request)
    {
        $data = $request->validated();

        if (! $this->bairroService->cidadeExiste($data['cidade_id'])) {
            return $this->relacionadoNaoEncontrado();
        }

        $id = $this->bairroService->criar($data);

        return response()->json([
            'message' => 'Registro criado com sucesso.',
            'id' => $id,
        ], 201);
    }

    public function update(UpdateBairroRequest $request, int $id)
    {
        if (! $this->bairroService->buscarPorId($id)) {
            return response()->json([
                'message' => 'Registro não encontrado.',
            ], 404);
        }

        $data = $request->validated();

        if (! $this->bairroService->cidadeExiste($data['cidade_id'])) {
            return $this->relacionadoNaoEncontrado();
        }

        $this->bairroService->atualizar($id, $data);

        return response()->json([
            'message' => 'Registro atualizado com sucesso.',
        ]);
    }
}
