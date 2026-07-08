<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SimpleCrudService;

abstract class SimpleCrudController extends Controller
{
    public function __construct(protected readonly SimpleCrudService $service)
    {
    }

    public function index()
    {
        return response()->json(
            $this->service->listar()
        );
    }

    public function show(int|string $id)
    {
        $registro = $this->service->buscarPorId($id);

        if (! $registro) {
            return response()->json([
                'message' => 'Registro não encontrado.',
            ], 404);
        }

        return response()->json($registro);
    }

    public function destroy(int|string $id)
    {
        if (! $this->service->buscarPorId($id)) {
            return response()->json([
                'message' => 'Registro não encontrado.',
            ], 404);
        }

        $this->service->remover($id);

        return response()->json(null, 204);
    }

    protected function conflito(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => 'Já existe um registro com estes dados.',
        ], 409);
    }

    protected function relacionadoNaoEncontrado(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => 'Registro relacionado não encontrado.',
        ], 404);
    }
}
