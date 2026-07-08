<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class PrecificacaoAluguelService
{
    private const ADICIONAIS = [
        'LAVAGEM_GARANTIDA' => ['nome' => 'Lavagem garantida', 'valor' => 49.95, 'por_dia' => false],
        'PROTECAO_COMPLETA' => ['nome' => 'Protecao completa', 'valor' => 65.19, 'por_dia' => true],
        'CONDUTOR_ADICIONAL' => ['nome' => 'Condutor adicional', 'valor' => 21.95, 'por_dia' => true],
    ];

    public function tipoPorPeriodo(string $dataRetirada, string $dataDevolucao): string
    {
        return $this->horas($dataRetirada, $dataDevolucao) < 24 ? 'CURTA_DURACAO' : 'LONGA_DURACAO';
    }

    public function calcularBase(string $grupo, int $filialId, string $dataRetirada, string $dataDevolucao): float
    {
        $horas = $this->horas($dataRetirada, $dataDevolucao);
        $tipo = $this->tipoPorPeriodo($dataRetirada, $dataDevolucao);

        if ($tipo === 'CURTA_DURACAO') {
            $base = ['A' => 25, 'B' => 35, 'C' => 45, 'D' => 60, 'E' => 80][$grupo] ?? 40;

            return round($base * $horas * $this->multiplicadorEscassez($filialId, $tipo), 2);
        }

        $dias = $this->dias($dataRetirada, $dataDevolucao);
        $base = ['A' => 120, 'B' => 160, 'C' => 220, 'D' => 300, 'E' => 420][$grupo] ?? 180;

        return round(
            $base * $dias * $this->multiplicadorEscassez($filialId, $tipo) * $this->multiplicadorDesconto($dias),
            2
        );
    }

    /**
     * @param  list<string>  $adicionais
     * @return array{base: float, total: float, dias: int, adicional_total: float, adicionais: list<array{id: string, nome: string, total: float}>}
     */
    public function calcularTotal(
        string $grupo,
        int $filialId,
        string $dataRetirada,
        string $dataDevolucao,
        string $quilometragemTipo,
        array $adicionais = [],
    ): array {
        $base = $this->calcularBase($grupo, $filialId, $dataRetirada, $dataDevolucao);
        $dias = $this->dias($dataRetirada, $dataDevolucao);
        $subtotal = $quilometragemTipo === 'ECONOMICA' ? round($base * 0.85, 2) : $base;
        $itens = [];
        $adicionalTotal = 0.0;

        foreach ($adicionais as $id) {
            if (! isset(self::ADICIONAIS[$id])) {
                continue;
            }

            $adicional = self::ADICIONAIS[$id];
            $total = $adicional['por_dia'] ? (float) $adicional['valor'] * $dias : (float) $adicional['valor'];
            $total = round($total, 2);
            $adicionalTotal += $total;
            $itens[] = [
                'id' => $id,
                'nome' => $adicional['nome'],
                'total' => $total,
            ];
        }

        return [
            'base' => $base,
            'total' => round($subtotal + $adicionalTotal, 2),
            'dias' => $dias,
            'adicional_total' => round($adicionalTotal, 2),
            'adicionais' => $itens,
        ];
    }

    /**
     * @return list<array{id: string, nome: string, valor: float, por_dia: bool}>
     */
    public function adicionaisDisponiveis(): array
    {
        $adicionais = [];

        foreach (self::ADICIONAIS as $id => $dados) {
            $adicionais[] = [
                'id' => $id,
                'nome' => $dados['nome'],
                'valor' => $dados['valor'],
                'por_dia' => $dados['por_dia'],
            ];
        }

        return $adicionais;
    }

    private function horas(string $dataRetirada, string $dataDevolucao): int
    {
        $inicio = new DateTimeImmutable($dataRetirada);
        $fim = new DateTimeImmutable($dataDevolucao);

        return max(1, (int) ceil(($fim->getTimestamp() - $inicio->getTimestamp()) / 3600));
    }

    private function dias(string $dataRetirada, string $dataDevolucao): int
    {
        return max(1, (int) ceil($this->horas($dataRetirada, $dataDevolucao) / 24));
    }

    private function multiplicadorEscassez(int $filialId, string $tipo): float
    {
        $resultado = DB::selectOne("
            SELECT COUNT(*) AS total
            FROM Veiculo
            WHERE Filial_id = ?
              AND finalidade = ?
              AND status = 'DISPONIVEL'
        ", [$filialId, $tipo]);

        return 1 + max(0, 5 - (int) $resultado->total) * 0.10;
    }

    private function multiplicadorDesconto(int $dias): float
    {
        if ($dias >= 30) {
            return 0.80;
        }

        if ($dias >= 15) {
            return 0.85;
        }

        if ($dias >= 7) {
            return 0.90;
        }

        return 1.00;
    }
}
