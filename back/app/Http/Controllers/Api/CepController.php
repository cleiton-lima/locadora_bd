<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Cep\StoreCepRequest;
use App\Http\Requests\Cep\UpdateCepRequest;
use App\Services\CepService;

class CepController extends SimpleCrudController
{
    public function __construct(private readonly CepService $cepService)
    {
        parent::__construct($cepService);
    }

    public function store(StoreCepRequest $request)
    {
        $data = $request->validated();

        if (! $this->cepService->logradouroExiste($data['logradouro_id'])) {
            return $this->relacionadoNaoEncontrado();
        }

        if ($this->cepService->buscarPorId($data['cep'])) {
            return $this->conflito();
        }

        $id = $this->cepService->criar($data);

        return response()->json([
            'message' => 'Registro criado com sucesso.',
            'id' => $id,
        ], 201);
    }

    public function update(UpdateCepRequest $request, string $id)
    {
        if (! $this->cepService->buscarPorId($id)) {
            return response()->json([
                'message' => 'Registro não encontrado.',
            ], 404);
        }

        $data = $request->validated();

        if (! $this->cepService->logradouroExiste($data['logradouro_id'])) {
            return $this->relacionadoNaoEncontrado();
        }

        $this->cepService->atualizar($id, $data);

        return response()->json([
            'message' => 'Registro atualizado com sucesso.',
        ]);
    }
}
