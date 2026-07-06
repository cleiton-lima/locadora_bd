<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class VeiculoService
{
    public function listar(): array
    {
        return DB::select("
            SELECT id, status, finalidade, placa, grupo, quilometragem,
                   Administrador_Funcionario_id, Filial_id, Lote_id
            FROM Veiculo
            ORDER BY id
        ");
    }

    public function buscarPorId(int $id): ?object
    {
        return DB::selectOne("
            SELECT id, status, finalidade, placa, grupo, quilometragem,
                   Administrador_Funcionario_id, Filial_id, Lote_id
            FROM Veiculo
            WHERE id = ?
        ", [$id]);
    }

    public function administradorExiste(int $administradorFuncionarioId): bool
    {
        return DB::selectOne("
            SELECT Funcionario_id FROM Administrador WHERE Funcionario_id = ?
        ", [$administradorFuncionarioId]) !== null;
    }

    public function filialExiste(int $filialId): bool
    {
        return DB::selectOne("
            SELECT id FROM Filial WHERE id = ?
        ", [$filialId]) !== null;
    }

    public function loteExiste(int $loteId): bool
    {
        return DB::selectOne("
            SELECT id FROM Lote WHERE id = ?
        ", [$loteId]) !== null;
    }

    public function placaExiste(string $placa, ?int $ignorarId = null): bool
    {
        if ($ignorarId !== null) {
            $registro = DB::selectOne("
                SELECT id FROM Veiculo WHERE placa = ? AND id != ?
            ", [$placa, $ignorarId]);
        } else {
            $registro = DB::selectOne("
                SELECT id FROM Veiculo WHERE placa = ?
            ", [$placa]);
        }

        return $registro !== null;
    }

    public function criar(array $data): int
    {
        DB::insert("
            INSERT INTO Veiculo (
                status,
                finalidade,
                placa,
                grupo,
                quilometragem,
                Administrador_Funcionario_id,
                Filial_id,
                Lote_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $data['status'],
            $data['finalidade'],
            $data['placa'],
            $data['grupo'],
            $data['quilometragem'],
            $data['administrador_funcionario_id'],
            $data['filial_id'],
            $data['lote_id'],
        ]);

        return (int) DB::getPdo()->lastInsertId();
    }

    public function atualizar(int $id, array $data): int
    {
        return DB::update("
            UPDATE Veiculo
            SET status = ?,
                finalidade = ?,
                placa = ?,
                grupo = ?,
                quilometragem = ?,
                Administrador_Funcionario_id = ?,
                Filial_id = ?,
                Lote_id = ?
            WHERE id = ?
        ", [
            $data['status'],
            $data['finalidade'],
            $data['placa'],
            $data['grupo'],
            $data['quilometragem'],
            $data['administrador_funcionario_id'],
            $data['filial_id'],
            $data['lote_id'],
            $id,
        ]);
    }

    public function remover(int $id): int
    {
        return DB::delete("
            DELETE FROM Veiculo
            WHERE id = ?
        ", [$id]);
    }
}
