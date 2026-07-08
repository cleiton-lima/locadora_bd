<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Estado\StoreEstadoRequest;
use App\Http\Requests\Estado\UpdateEstadoRequest;
use App\Services\EstadoService;

class EstadoController extends SimpleCrudController
{
    public function __construct(private readonly EstadoService $estadoService)
    {
        parent::__construct($estadoService);
    }

    public function store(StoreEstadoRequest $request)
    {
        $data = $request->validated();

        if ($this->estadoService->buscarPorId($data['sigla']) || $this->estadoService->valorExiste('nome', $data['nome'])) {
            return $this->conflito();
        }

        $id = $this->estadoService->criar($data);

        return response()->json([
            'message' => 'Registro criado com sucesso.',
            'id' => $id,
        ], 201);
    }

    public function update(UpdateEstadoRequest $request, string $id)
    {
        if (! $this->estadoService->buscarPorId($id)) {
            return response()->json([
                'message' => 'Registro não encontrado.',
            ], 404);
        }

        $data = $request->validated();

        if ($this->estadoService->valorExiste('nome', $data['nome'], $id)) {
            return $this->conflito();
        }

        $this->estadoService->atualizar($id, $data);

        return response()->json([
            'message' => 'Registro atualizado com sucesso.',
        ]);
    }
}
