<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ServicoService
{
    public function listar(): array
    {
        return DB::select("
            SELECT id, status, data_inicio, data_fim, custo, tipo,
                   Veiculo_id, Administrador_Funcionario_id, Oficina_id
            FROM Servico
            ORDER BY id
        ");
    }

    public function buscarPorId(int $id): ?object
    {
        return DB::selectOne("
            SELECT id, status, data_inicio, data_fim, custo, tipo,
                   Veiculo_id, Administrador_Funcionario_id, Oficina_id
            FROM Servico
            WHERE id = ?
        ", [$id]);
    }

    public function veiculoExiste(int $veiculoId): bool
    {
        return DB::selectOne("
            SELECT id FROM Veiculo WHERE id = ?
        ", [$veiculoId]) !== null;
    }

    public function administradorExiste(int $administradorFuncionarioId): bool
    {
        return DB::selectOne("
            SELECT Funcionario_id FROM Administrador WHERE Funcionario_id = ?
        ", [$administradorFuncionarioId]) !== null;
    }

    public function oficinaExiste(int $oficinaId): bool
    {
        return DB::selectOne("
            SELECT id FROM Oficina WHERE id = ?
        ", [$oficinaId]) !== null;
    }

    public function criar(array $data): int
    {
        DB::insert("
            INSERT INTO Servico (
                status,
                data_inicio,
                data_fim,
                custo,
                tipo,
                Veiculo_id,
                Administrador_Funcionario_id,
                Oficina_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $data['status'],
            $data['data_inicio'],
            $data['data_fim'] ?? null,
            $data['custo'] ?? null,
            $data['tipo'],
            $data['veiculo_id'],
            $data['administrador_funcionario_id'],
            $data['oficina_id'],
        ]);

        return (int) DB::getPdo()->lastInsertId();
    }

    public function atualizar(int $id, array $data): int
    {
        return DB::update("
            UPDATE Servico
            SET status = ?,
                data_inicio = ?,
                data_fim = ?,
                custo = ?,
                tipo = ?,
                Veiculo_id = ?,
                Administrador_Funcionario_id = ?,
                Oficina_id = ?
            WHERE id = ?
        ", [
            $data['status'],
            $data['data_inicio'],
            $data['data_fim'] ?? null,
            $data['custo'] ?? null,
            $data['tipo'],
            $data['veiculo_id'],
            $data['administrador_funcionario_id'],
            $data['oficina_id'],
            $id,
        ]);
    }

    public function remover(int $id): int
    {
        return DB::delete("
            DELETE FROM Servico
            WHERE id = ?
        ", [$id]);
    }
}
