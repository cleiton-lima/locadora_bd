<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class RelatorioService
{
    public function frotaDisponivel(): array
    {
        return DB::select('SELECT * FROM vw_frota_disponivel');
    }

    public function ocupacaoFrota(): array
    {
        return DB::select('SELECT * FROM vw_ocupacao_frota');
    }

    public function contratoFrotaResumo(): array
    {
        return DB::select('SELECT * FROM vw_contrato_frota_resumo');
    }

    public function veiculosManutencao(): array
    {
        return DB::select('SELECT * FROM vw_veiculos_manutencao');
    }
}
