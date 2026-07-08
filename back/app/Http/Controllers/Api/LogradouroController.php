<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Logradouro\StoreLogradouroRequest;
use App\Http\Requests\Logradouro\UpdateLogradouroRequest;
use App\Services\LogradouroService;

class LogradouroController extends SimpleCrudController
{
    public function __construct(private readonly LogradouroService $logradouroService)
    {
        parent::__construct($logradouroService);
    }

    public function store(StoreLogradouroRequest $request)
    {
        $data = $request->validated();

        if (! $this->logradouroService->bairroExiste($data['bairro_id'])) {
            return $this->relacionadoNaoEncontrado();
        }

        $id = $this->logradouroService->criar($data);

        return response()->json([
            'message' => 'Registro criado com sucesso.',
            'id' => $id,
        ], 201);
    }

    public function update(UpdateLogradouroRequest $request, int $id)
    {
        if (! $this->logradouroService->buscarPorId($id)) {
            return response()->json([
                'message' => 'Registro não encontrado.',
            ], 404);
        }

        $data = $request->validated();

        if (! $this->logradouroService->bairroExiste($data['bairro_id'])) {
            return $this->relacionadoNaoEncontrado();
        }

        $this->logradouroService->atualizar($id, $data);

        return response()->json([
            'message' => 'Registro atualizado com sucesso.',
        ]);
    }
}
