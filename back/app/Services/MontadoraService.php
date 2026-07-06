<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class MontadoraService
{
    public function listar(): array
    {
        return DB::select("
            SELECT id, nome
            FROM Montadora
            ORDER BY nome
        ");
    }

    public function buscarPorId(int $id): ?object
    {
        return DB::selectOne("
            SELECT id, nome
            FROM Montadora
            WHERE id = ?
        ", [$id]);
    }

    public function criar(array $data): int
    {
        DB::insert("
            INSERT INTO Montadora (nome)
            VALUES (?)
        ", [
            $data['nome'],
        ]);

        return (int) DB::getPdo()->lastInsertId();
    }

    public function atualizar(int $id, array $data): int
    {
        return DB::update("
            UPDATE Montadora
            SET nome = ?
            WHERE id = ?
        ", [
            $data['nome'],
            $id,
        ]);
    }

    public function remover(int $id): int
    {
        return DB::delete("
            DELETE FROM Montadora
            WHERE id = ?
        ", [$id]);
    }
}
