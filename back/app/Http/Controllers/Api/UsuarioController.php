<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Usuario\StoreUsuarioRequest;
use App\Http\Requests\Usuario\UpdateUsuarioRequest;
use App\Services\UsuarioService;

class UsuarioController extends Controller
{
    public function __construct(private readonly UsuarioService $service)
    {
    }

    public function index()
    {
        return response()->json(
            $this->service->listar()
        );
    }

    public function store(StoreUsuarioRequest $request)
    {
        $data = $request->validated();

        if ($this->service->usernameExiste($data['username'])) {
            return response()->json([
                'message' => 'Já existe um registro com estes dados.',
            ], 409);
        }

        if ($this->service->emailExiste($data['email'])) {
            return response()->json([
                'message' => 'Já existe um registro com estes dados.',
            ], 409);
        }

        $id = $this->service->criar($data);

        return response()->json([
            'message' => 'Registro criado com sucesso.',
            'id' => $id,
        ], 201);
    }

    public function show(int $id)
    {
        $registro = $this->service->buscarPorId($id);

        if (! $registro) {
            return response()->json([
                'message' => 'Registro não encontrado.',
            ], 404);
        }

        return response()->json($registro);
    }

    public function update(UpdateUsuarioRequest $request, int $id)
    {
        if (! $this->service->buscarPorId($id)) {
            return response()->json([
                'message' => 'Registro não encontrado.',
            ], 404);
        }

        $data = $request->validated();

        if ($this->service->usernameExiste($data['username'], $id)) {
            return response()->json([
                'message' => 'Já existe um registro com estes dados.',
            ], 409);
        }

        if ($this->service->emailExiste($data['email'], $id)) {
            return response()->json([
                'message' => 'Já existe um registro com estes dados.',
            ], 409);
        }

        $this->service->atualizar($id, $data);

        return response()->json([
            'message' => 'Registro atualizado com sucesso.',
        ]);
    }

    public function destroy(int $id)
    {
        if (! $this->service->buscarPorId($id)) {
            return response()->json([
                'message' => 'Registro não encontrado.',
            ], 404);
        }

        $this->service->remover($id);

        return response()->json(null, 204);
    }
}
