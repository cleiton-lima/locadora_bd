<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\FilialEndereco\StoreFilialEnderecoRequest;
use App\Http\Requests\FilialEndereco\UpdateFilialEnderecoRequest;
use App\Services\FilialEnderecoService;

class FilialEnderecoController extends SimpleCrudController
{
    public function __construct(private readonly FilialEnderecoService $filialEnderecoService)
    {
        parent::__construct($filialEnderecoService);
    }

    public function store(StoreFilialEnderecoRequest $request)
    {
        $data = $request->validated();

        if (! $this->filialEnderecoService->filialExiste($data['filial_id']) || ! $this->filialEnderecoService->cepExiste($data['cep'])) {
            return $this->relacionadoNaoEncontrado();
        }

        $id = $this->filialEnderecoService->criar($data);

        return response()->json([
            'message' => 'Registro criado com sucesso.',
            'id' => $id,
        ], 201);
    }

    public function update(UpdateFilialEnderecoRequest $request, int $id)
    {
        if (! $this->filialEnderecoService->buscarPorId($id)) {
            return response()->json([
                'message' => 'Registro não encontrado.',
            ], 404);
        }

        $data = $request->validated();

        if (! $this->filialEnderecoService->filialExiste($data['filial_id']) || ! $this->filialEnderecoService->cepExiste($data['cep'])) {
            return $this->relacionadoNaoEncontrado();
        }

        $this->filialEnderecoService->atualizar($id, $data);

        return response()->json([
            'message' => 'Registro atualizado com sucesso.',
        ]);
    }
}
