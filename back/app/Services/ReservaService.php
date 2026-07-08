<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ReservaService
{
    public function __construct(private readonly PrecificacaoAluguelService $precificacao)
    {
    }

    public function pessoaFisicaDoUsuario(int $usuarioId): ?object
    {
        return DB::selectOne('SELECT Cliente_id, CPF, CNH_numero FROM Pessoa_Fisica WHERE Cliente_id = ?', [$usuarioId]);
    }

    public function buscarVeiculoDisponivel(array $data): ?object
    {
        $tipo = $this->precificacao->tipoPorPeriodo($data['data_retirada'], $data['data_devolucao']);

        return DB::selectOne("
            SELECT id, grupo, Filial_id, finalidade
            FROM Veiculo
            WHERE Filial_id = ?
              AND finalidade = ?
              AND grupo = ?
              AND status = 'DISPONIVEL'
            ORDER BY quilometragem, id
            LIMIT 1
        ", [$data['filial_retirada_id'], $tipo, $data['grupo']]);
    }

    public function buscarAtendenteDaFilial(int $filialId): ?object
    {
        return DB::selectOne("
            SELECT a.Funcionario_id
            FROM Atendente a
            INNER JOIN Funcionario f ON f.Usuario_id = a.Funcionario_id
            WHERE f.Filial_id = ?
            ORDER BY a.Funcionario_id
            LIMIT 1
        ", [$filialId]);
    }

    /**
     * @return array{aluguel_id: int, total: float, veiculo_id: int, atendente_id: int}
     */
    public function confirmar(int $usuarioId, array $data, object $veiculo, object $atendente): array
    {
        $total = $this->precificacao->calcularTotal(
            $data['grupo'],
            (int) $data['filial_retirada_id'],
            $data['data_retirada'],
            $data['data_devolucao'],
            $data['quilometragem_tipo'],
            $data['adicionais'] ?? [],
        )['total'];
        $tipo = $this->precificacao->tipoPorPeriodo($data['data_retirada'], $data['data_devolucao']);

        return DB::transaction(function () use ($usuarioId, $data, $veiculo, $atendente, $total, $tipo): array {
            DB::insert("
                INSERT INTO Aluguel (
                    Atendente_entrega_id,
                    Atendente_devolucao_id,
                    Pessoa_Fisica_id,
                    status,
                    valor,
                    tipo,
                    data_inicial,
                    data_final,
                    data_final_prevista,
                    Contrato_frota_id,
                    Veiculo_id
                ) VALUES (?, NULL, ?, 'ATIVO', ?, ?, ?, NULL, ?, NULL, ?)
            ", [
                $atendente->Funcionario_id,
                $usuarioId,
                $total,
                $tipo,
                $data['data_retirada'],
                $data['data_devolucao'],
                $veiculo->id,
            ]);

            $aluguelId = (int) DB::getPdo()->lastInsertId();

            DB::update("UPDATE Veiculo SET status = 'ALUGADO' WHERE id = ?", [$veiculo->id]);

            return [
                'aluguel_id' => $aluguelId,
                'total' => $total,
                'veiculo_id' => (int) $veiculo->id,
                'atendente_id' => (int) $atendente->Funcionario_id,
            ];
        });
    }
}
