<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CotacaoService
{
    public function __construct(private readonly PrecificacaoAluguelService $precificacao)
    {
    }

    public function listarFiliais(): array
    {
        return DB::select('SELECT id, nome FROM Filial ORDER BY nome, id');
    }

    /**
     * @return array<string, mixed>|null
     */
    public function cotar(array $data): ?array
    {
        $filialRetirada = $this->buscarFilial((int) $data['filial_retirada_id']);
        $filialDevolucao = $this->buscarFilial((int) $data['filial_devolucao_id']);

        if (! $filialRetirada || ! $filialDevolucao) {
            return null;
        }

        $tipo = $this->precificacao->tipoPorPeriodo($data['data_retirada'], $data['data_devolucao']);
        $grupos = DB::select("
            SELECT grupo, COUNT(*) AS disponiveis, MIN(quilometragem) AS menor_quilometragem
            FROM Veiculo
            WHERE Filial_id = ?
              AND finalidade = ?
              AND status = 'DISPONIVEL'
            GROUP BY grupo
            ORDER BY grupo
        ", [$filialRetirada->id, $tipo]);

        return [
            'filial_retirada' => $filialRetirada,
            'filial_devolucao' => $filialDevolucao,
            'data_retirada' => $data['data_retirada'],
            'data_devolucao' => $data['data_devolucao'],
            'tipo' => $tipo,
            'adicionais_disponiveis' => $this->precificacao->adicionaisDisponiveis(),
            'grupos' => array_map(fn (object $grupo): array => $this->formatarGrupo($grupo, $data), $grupos),
        ];
    }

    private function buscarFilial(int $id): ?object
    {
        return DB::selectOne('SELECT id, nome FROM Filial WHERE id = ?', [$id]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatarGrupo(object $grupo, array $data): array
    {
        $economica = $this->precificacao->calcularTotal(
            $grupo->grupo,
            (int) $data['filial_retirada_id'],
            $data['data_retirada'],
            $data['data_devolucao'],
            'ECONOMICA',
        );
        $ilimitada = $this->precificacao->calcularTotal(
            $grupo->grupo,
            (int) $data['filial_retirada_id'],
            $data['data_retirada'],
            $data['data_devolucao'],
            'ILIMITADA',
        );

        return [
            'grupo' => $grupo->grupo,
            'disponiveis' => (int) $grupo->disponiveis,
            'menor_quilometragem' => (int) $grupo->menor_quilometragem,
            'opcoes_quilometragem' => [
                [
                    'tipo' => 'ECONOMICA',
                    'nome' => 'Quilometragem economica',
                    'franquia_km_por_dia' => 200,
                    'valor_km_excedente' => 0.50,
                    'base' => $economica['base'],
                    'total' => $economica['total'],
                ],
                [
                    'tipo' => 'ILIMITADA',
                    'nome' => 'Quilometragem ilimitada',
                    'franquia_km_por_dia' => null,
                    'valor_km_excedente' => null,
                    'base' => $ilimitada['base'],
                    'total' => $ilimitada['total'],
                ],
            ],
        ];
    }
}
