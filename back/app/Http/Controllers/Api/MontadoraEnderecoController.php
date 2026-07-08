<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\MontadoraEndereco\StoreMontadoraEnderecoRequest;
use App\Http\Requests\MontadoraEndereco\UpdateMontadoraEnderecoRequest;
use App\Services\MontadoraEnderecoService;

class MontadoraEnderecoController extends SimpleCrudController
{
    public function __construct(private readonly MontadoraEnderecoService $montadoraEnderecoService)
    {
        parent::__construct($montadoraEnderecoService);
    }

    public function store(StoreMontadoraEnderecoRequest $request)
    {
        $data = $request->validated();

        if (! $this->montadoraEnderecoService->montadoraExiste($data['montadora_id']) || ! $this->montadoraEnderecoService->cepExiste($data['cep'])) {
            return $this->relacionadoNaoEncontrado();
        }

        $id = $this->montadoraEnderecoService->criar($data);

        return response()->json([
            'message' => 'Registro criado com sucesso.',
            'id' => $id,
        ], 201);
    }

    public function update(UpdateMontadoraEnderecoRequest $request, int $id)
    {
        if (! $this->montadoraEnderecoService->buscarPorId($id)) {
            return response()->json([
                'message' => 'Registro não encontrado.',
            ], 404);
        }

        $data = $request->validated();

        if (! $this->montadoraEnderecoService->montadoraExiste($data['montadora_id']) || ! $this->montadoraEnderecoService->cepExiste($data['cep'])) {
            return $this->relacionadoNaoEncontrado();
        }

        $this->montadoraEnderecoService->atualizar($id, $data);

        return response()->json([
            'message' => 'Registro atualizado com sucesso.',
        ]);
    }
}
