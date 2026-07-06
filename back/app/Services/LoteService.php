<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class LoteService
{
    public function listar(): array
    {
        return DB::select("
            SELECT id, Gerente_Comercial_Funcionario_id, Montadora_id, preco_total, quantidade_veiculos
            FROM Lote
            ORDER BY id
        ");
    }

    public function buscarPorId(int $id): ?object
    {
        return DB::selectOne("
            SELECT id, Gerente_Comercial_Funcionario_id, Montadora_id, preco_total, quantidade_veiculos
            FROM Lote
            WHERE id = ?
        ", [$id]);
    }

    public function gerenteComercialExiste(int $funcionarioId): bool
    {
        return DB::selectOne("
            SELECT Funcionario_id FROM Gerente_Comercial WHERE Funcionario_id = ?
        ", [$funcionarioId]) !== null;
    }

    public function montadoraExiste(int $montadoraId): bool
    {
        return DB::selectOne("
            SELECT id FROM Montadora WHERE id = ?
        ", [$montadoraId]) !== null;
    }

    public function criar(array $data): int
    {
        DB::insert("
            INSERT INTO Lote (
                Gerente_Comercial_Funcionario_id,
                Montadora_id,
                preco_total,
                quantidade_veiculos
            ) VALUES (?, ?, ?, ?)
        ", [
            $data['gerente_comercial_funcionario_id'],
            $data['montadora_id'],
            $data['preco_total'],
            $data['quantidade_veiculos'],
        ]);

        return (int) DB::getPdo()->lastInsertId();
    }

    public function atualizar(int $id, array $data): int
    {
        return DB::update("
            UPDATE Lote
            SET Gerente_Comercial_Funcionario_id = ?,
                Montadora_id = ?,
                preco_total = ?,
                quantidade_veiculos = ?
            WHERE id = ?
        ", [
            $data['gerente_comercial_funcionario_id'],
            $data['montadora_id'],
            $data['preco_total'],
            $data['quantidade_veiculos'],
            $id,
        ]);
    }

    public function remover(int $id): int
    {
        return DB::delete("
            DELETE FROM Lote
            WHERE id = ?
        ", [$id]);
    }
}
