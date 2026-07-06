<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class EmpresaService
{
    public function listar(): array
    {
        return DB::select("
            SELECT Cliente_id, CNPJ
            FROM Empresa
            ORDER BY Cliente_id
        ");
    }

    public function buscarPorId(int $clienteId): ?object
    {
        return DB::selectOne("
            SELECT Cliente_id, CNPJ
            FROM Empresa
            WHERE Cliente_id = ?
        ", [$clienteId]);
    }

    public function usuarioExiste(int $clienteId): bool
    {
        return DB::selectOne("
            SELECT id FROM Usuario WHERE id = ?
        ", [$clienteId]) !== null;
    }

    public function cnpjExiste(string $cnpj, ?int $ignorarId = null): bool
    {
        if ($ignorarId !== null) {
            $registro = DB::selectOne("
                SELECT Cliente_id FROM Empresa WHERE CNPJ = ? AND Cliente_id != ?
            ", [$cnpj, $ignorarId]);
        } else {
            $registro = DB::selectOne("
                SELECT Cliente_id FROM Empresa WHERE CNPJ = ?
            ", [$cnpj]);
        }

        return $registro !== null;
    }

    public function criar(array $data): int
    {
        DB::insert("
            INSERT INTO Empresa (Cliente_id, CNPJ)
            VALUES (?, ?)
        ", [
            $data['cliente_id'],
            $data['cnpj'],
        ]);

        return (int) $data['cliente_id'];
    }

    public function atualizar(int $clienteId, array $data): int
    {
        return DB::update("
            UPDATE Empresa
            SET CNPJ = ?
            WHERE Cliente_id = ?
        ", [
            $data['cnpj'],
            $clienteId,
        ]);
    }

    public function remover(int $clienteId): int
    {
        return DB::delete("
            DELETE FROM Empresa
            WHERE Cliente_id = ?
        ", [$clienteId]);
    }
}
