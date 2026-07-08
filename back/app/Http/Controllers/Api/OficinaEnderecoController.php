<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\OficinaEndereco\StoreOficinaEnderecoRequest;
use App\Http\Requests\OficinaEndereco\UpdateOficinaEnderecoRequest;
use App\Services\OficinaEnderecoService;

class OficinaEnderecoController extends SimpleCrudController
{
    public function __construct(private readonly OficinaEnderecoService $oficinaEnderecoService)
    {
        parent::__construct($oficinaEnderecoService);
    }

    public function store(StoreOficinaEnderecoRequest $request)
    {
        $data = $request->validated();

        if (! $this->oficinaEnderecoService->oficinaExiste($data['oficina_id']) || ! $this->oficinaEnderecoService->cepExiste($data['cep'])) {
            return $this->relacionadoNaoEncontrado();
        }

        $id = $this->oficinaEnderecoService->criar($data);

        return response()->json([
            'message' => 'Registro criado com sucesso.',
            'id' => $id,
        ], 201);
    }

    public function update(UpdateOficinaEnderecoRequest $request, int $id)
    {
        if (! $this->oficinaEnderecoService->buscarPorId($id)) {
            return response()->json([
                'message' => 'Registro não encontrado.',
            ], 404);
        }

        $data = $request->validated();

        if (! $this->oficinaEnderecoService->oficinaExiste($data['oficina_id']) || ! $this->oficinaEnderecoService->cepExiste($data['cep'])) {
            return $this->relacionadoNaoEncontrado();
        }

        $this->oficinaEnderecoService->atualizar($id, $data);

        return response()->json([
            'message' => 'Registro atualizado com sucesso.',
        ]);
    }
}
