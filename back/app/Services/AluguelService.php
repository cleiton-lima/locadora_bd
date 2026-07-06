<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AluguelService
{
    public function listar(): array
    {
        return DB::select("
            SELECT id, Atendente_entrega_id, Atendente_devolucao_id, Pessoa_Fisica_id,
                   status, valor, tipo, data_inicial, data_final, data_final_prevista,
                   Contrato_frota_id, Veiculo_id
            FROM Aluguel
            ORDER BY id
        ");
    }

    public function buscarPorId(int $id): ?object
    {
        return DB::selectOne("
            SELECT id, Atendente_entrega_id, Atendente_devolucao_id, Pessoa_Fisica_id,
                   status, valor, tipo, data_inicial, data_final, data_final_prevista,
                   Contrato_frota_id, Veiculo_id
            FROM Aluguel
            WHERE id = ?
        ", [$id]);
    }

    public function atendenteExiste(int $funcionarioId): bool
    {
        return DB::selectOne("
            SELECT Funcionario_id FROM Atendente WHERE Funcionario_id = ?
        ", [$funcionarioId]) !== null;
    }

    public function pessoaFisicaExiste(int $clienteId): bool
    {
        return DB::selectOne("
            SELECT Cliente_id FROM Pessoa_Fisica WHERE Cliente_id = ?
        ", [$clienteId]) !== null;
    }

    public function contratoFrotaExiste(int $id): bool
    {
        return DB::selectOne("
            SELECT id FROM Contrato_Frota WHERE id = ?
        ", [$id]) !== null;
    }

    public function veiculoExiste(int $id): bool
    {
        return DB::selectOne("
            SELECT id FROM Veiculo WHERE id = ?
        ", [$id]) !== null;
    }

    public function criar(array $data): int
    {
        return DB::transaction(function () use ($data) {
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
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                $data['atendente_entrega_id'],
                $data['atendente_devolucao_id'] ?? null,
                $data['pessoa_fisica_id'] ?? null,
                $data['status'],
                $data['valor'],
                $data['tipo'],
                $data['data_inicial'],
                $data['data_final'] ?? null,
                $data['data_final_prevista'],
                $data['contrato_frota_id'] ?? null,
                $data['veiculo_id'],
            ]);

            $id = (int) DB::getPdo()->lastInsertId();

            DB::update("
                UPDATE Veiculo
                SET status = 'ALUGADO'
                WHERE id = ?
            ", [$data['veiculo_id']]);

            return $id;
        });
    }

    public function atualizar(int $id, array $data): int
    {
        return DB::update("
            UPDATE Aluguel
            SET Atendente_entrega_id = ?,
                Atendente_devolucao_id = ?,
                Pessoa_Fisica_id = ?,
                status = ?,
                valor = ?,
                tipo = ?,
                data_inicial = ?,
                data_final = ?,
                data_final_prevista = ?,
                Contrato_frota_id = ?,
                Veiculo_id = ?
            WHERE id = ?
        ", [
            $data['atendente_entrega_id'],
            $data['atendente_devolucao_id'] ?? null,
            $data['pessoa_fisica_id'] ?? null,
            $data['status'],
            $data['valor'],
            $data['tipo'],
            $data['data_inicial'],
            $data['data_final'] ?? null,
            $data['data_final_prevista'],
            $data['contrato_frota_id'] ?? null,
            $data['veiculo_id'],
            $id,
        ]);
    }

    public function remover(int $id): int
    {
        return DB::delete("
            DELETE FROM Aluguel
            WHERE id = ?
        ", [$id]);
    }
}
