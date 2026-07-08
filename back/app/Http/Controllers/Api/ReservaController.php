<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservaRequest;
use App\Services\ReservaService;

class ReservaController extends Controller
{
    public function __construct(private readonly ReservaService $reservaService)
    {
    }

    public function store(StoreReservaRequest $request)
    {
        $usuarioId = (int) $request->attributes->get('auth_usuario_id');
        $data = $request->validated();

        if (! $this->reservaService->pessoaFisicaDoUsuario($usuarioId)) {
            return response()->json([
                'message' => 'Complete o cadastro de pessoa física antes de confirmar a reserva.',
                'code' => 'PESSOA_FISICA_REQUIRED',
            ], 409);
        }

        $veiculo = $this->reservaService->buscarVeiculoDisponivel($data);

        if (! $veiculo) {
            return response()->json([
                'message' => 'Nenhum veículo disponível para o grupo selecionado.',
            ], 409);
        }

        $atendente = $this->reservaService->buscarAtendenteDaFilial((int) $data['filial_retirada_id']);

        if (! $atendente) {
            return response()->json([
                'message' => 'Nenhum atendente disponível na filial de retirada.',
            ], 409);
        }

        $reserva = $this->reservaService->confirmar($usuarioId, $data, $veiculo, $atendente);

        return response()->json([
            'message' => 'Reserva confirmada com sucesso.',
            ...$reserva,
        ], 201);
    }
}
