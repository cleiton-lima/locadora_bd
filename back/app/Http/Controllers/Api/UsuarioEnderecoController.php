<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\UsuarioEndereco\StoreUsuarioEnderecoRequest;
use App\Http\Requests\UsuarioEndereco\UpdateUsuarioEnderecoRequest;
use App\Services\UsuarioEnderecoService;

class UsuarioEnderecoController extends SimpleCrudController
{
    public function __construct(private readonly UsuarioEnderecoService $usuarioEnderecoService)
    {
        parent::__construct($usuarioEnderecoService);
    }

    public function store(StoreUsuarioEnderecoRequest $request)
    {
        $data = $request->validated();

        if (! $this->usuarioEnderecoService->usuarioExiste($data['usuario_id']) || ! $this->usuarioEnderecoService->cepExiste($data['cep'])) {
            return $this->relacionadoNaoEncontrado();
        }

        if ($this->usuarioEnderecoService->buscarPorId($data['usuario_id'])) {
            return $this->conflito();
        }

        $id = $this->usuarioEnderecoService->criar($data);

        return response()->json([
            'message' => 'Registro criado com sucesso.',
            'id' => $id,
        ], 201);
    }

    public function update(UpdateUsuarioEnderecoRequest $request, int $id)
    {
        if (! $this->usuarioEnderecoService->buscarPorId($id)) {
            return response()->json([
                'message' => 'Registro não encontrado.',
            ], 404);
        }

        $data = $request->validated();

        if (! $this->usuarioEnderecoService->cepExiste($data['cep'])) {
            return $this->relacionadoNaoEncontrado();
        }

        $this->usuarioEnderecoService->atualizar($id, $data);

        return response()->json([
            'message' => 'Registro atualizado com sucesso.',
        ]);
    }
}
