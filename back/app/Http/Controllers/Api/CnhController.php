<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Cnh\StoreCnhRequest;
use App\Http\Requests\Cnh\UpdateCnhRequest;
use App\Services\CnhService;

class CnhController extends SimpleCrudController
{
    public function __construct(private readonly CnhService $cnhService)
    {
        parent::__construct($cnhService);
    }

    public function store(StoreCnhRequest $request)
    {
        $data = $request->validated();

        if (! $this->cnhService->estadoExiste($data['estado'])) {
            return $this->relacionadoNaoEncontrado();
        }

        if ($this->cnhService->buscarPorId($data['numero'])) {
            return $this->conflito();
        }

        $id = $this->cnhService->criar($data);

        return response()->json([
            'message' => 'Registro criado com sucesso.',
            'id' => $id,
        ], 201);
    }

    public function update(UpdateCnhRequest $request, string $id)
    {
        if (! $this->cnhService->buscarPorId($id)) {
            return response()->json([
                'message' => 'Registro não encontrado.',
            ], 404);
        }

        $data = $request->validated();

        if (! $this->cnhService->estadoExiste($data['estado'])) {
            return $this->relacionadoNaoEncontrado();
        }

        $this->cnhService->atualizar($id, $data);

        return response()->json([
            'message' => 'Registro atualizado com sucesso.',
        ]);
    }
}
