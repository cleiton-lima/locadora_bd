<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AtendenteService
{
    public function listar(): array
    {
        return DB::select("
            SELECT Funcionario_id
            FROM Atendente
            ORDER BY Funcionario_id
        ");
    }

    public function buscarPorId(int $funcionarioId): ?object
    {
        return DB::selectOne("
            SELECT Funcionario_id
            FROM Atendente
            WHERE Funcionario_id = ?
        ", [$funcionarioId]);
    }

    public function funcionarioExiste(int $funcionarioId): bool
    {
        return DB::selectOne("
            SELECT Usuario_id FROM Funcionario WHERE Usuario_id = ?
        ", [$funcionarioId]) !== null;
    }

    public function criar(array $data): int
    {
        DB::insert("
            INSERT INTO Atendente (Funcionario_id)
            VALUES (?)
        ", [
            $data['funcionario_id'],
        ]);

        return (int) $data['funcionario_id'];
    }

    public function atualizar(int $funcionarioIdAtual, array $data): int
    {
        return DB::update("
            UPDATE Atendente
            SET Funcionario_id = ?
            WHERE Funcionario_id = ?
        ", [
            $data['funcionario_id'],
            $funcionarioIdAtual,
        ]);
    }

    public function remover(int $funcionarioId): int
    {
        return DB::delete("
            DELETE FROM Atendente
            WHERE Funcionario_id = ?
        ", [$funcionarioId]);
    }
}
