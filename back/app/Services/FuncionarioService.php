<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class FuncionarioService
{
    public function listar(): array
    {
        return DB::select("
            SELECT f.Usuario_id, f.Filial_id, u.nome AS usuario_nome, u.email AS usuario_email
            FROM Funcionario f
            INNER JOIN Usuario u ON u.id = f.Usuario_id
            ORDER BY u.nome
        ");
    }

    public function buscarPorId(int $usuarioId): ?object
    {
        return DB::selectOne("
            SELECT f.Usuario_id, f.Filial_id, u.nome AS usuario_nome, u.email AS usuario_email
            FROM Funcionario f
            INNER JOIN Usuario u ON u.id = f.Usuario_id
            WHERE f.Usuario_id = ?
        ", [$usuarioId]);
    }

    public function usuarioExiste(int $usuarioId): bool
    {
        return DB::selectOne("
            SELECT id FROM Usuario WHERE id = ?
        ", [$usuarioId]) !== null;
    }

    public function filialExiste(int $filialId): bool
    {
        return DB::selectOne("
            SELECT id FROM Filial WHERE id = ?
        ", [$filialId]) !== null;
    }

    public function criar(array $data): int
    {
        DB::insert("
            INSERT INTO Funcionario (
                Usuario_id,
                Filial_id
            ) VALUES (?, ?)
        ", [
            $data['usuario_id'],
            $data['filial_id'],
        ]);

        return (int) $data['usuario_id'];
    }

    public function atualizar(int $usuarioId, array $data): int
    {
        return DB::update("
            UPDATE Funcionario
            SET Filial_id = ?
            WHERE Usuario_id = ?
        ", [
            $data['filial_id'],
            $usuarioId,
        ]);
    }

    public function remover(int $usuarioId): int
    {
        return DB::delete("
            DELETE FROM Funcionario
            WHERE Usuario_id = ?
        ", [$usuarioId]);
    }
}
