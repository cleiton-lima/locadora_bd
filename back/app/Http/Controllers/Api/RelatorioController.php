<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RelatorioService;

class RelatorioController extends Controller
{
    public function __construct(private readonly RelatorioService $service)
    {
    }

    public function frotaDisponivel()
    {
        return response()->json(
            $this->service->frotaDisponivel()
        );
    }

    public function ocupacaoFrota()
    {
        return response()->json(
            $this->service->ocupacaoFrota()
        );
    }

    public function contratoFrotaResumo()
    {
        return response()->json(
            $this->service->contratoFrotaResumo()
        );
    }

    public function veiculosManutencao()
    {
        return response()->json(
            $this->service->veiculosManutencao()
        );
    }
}
