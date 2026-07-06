<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class GerenteComercialService
{
    public function listar(): array
    {
        return DB::select("
            SELECT Funcionario_id
            FROM Gerente_Comercial
            ORDER BY Funcionario_id
        ");
    }

    public function buscarPorId(int $funcionarioId): ?object
    {
        return DB::selectOne("
            SELECT Funcionario_id
            FROM Gerente_Comercial
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
            INSERT INTO Gerente_Comercial (Funcionario_id)
            VALUES (?)
        ", [
            $data['funcionario_id'],
        ]);

        return (int) $data['funcionario_id'];
    }

    public function atualizar(int $funcionarioIdAtual, array $data): int
    {
        return DB::update("
            UPDATE Gerente_Comercial
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
            DELETE FROM Gerente_Comercial
            WHERE Funcionario_id = ?
        ", [$funcionarioId]);
    }
}
