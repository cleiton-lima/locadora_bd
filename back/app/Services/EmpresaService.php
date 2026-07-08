<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class EmpresaService
{
    public function listar(): array
    {
        return DB::select("
            SELECT Usuario_id, CNPJ
            FROM Empresa
            ORDER BY Usuario_id
        ");
    }

    public function buscarPorId(int $usuarioId): ?object
    {
        return DB::selectOne("
            SELECT Usuario_id, CNPJ
            FROM Empresa
            WHERE Usuario_id = ?
        ", [$usuarioId]);
    }

    public function usuarioExiste(int $usuarioId): bool
    {
        return DB::selectOne("
            SELECT id FROM Usuario WHERE id = ?
        ", [$usuarioId]) !== null;
    }

    public function cnpjExiste(string $cnpj, ?int $ignorarId = null): bool
    {
        if ($ignorarId !== null) {
            $registro = DB::selectOne("
                SELECT Usuario_id FROM Empresa WHERE CNPJ = ? AND Usuario_id != ?
            ", [$cnpj, $ignorarId]);
        } else {
            $registro = DB::selectOne("
                SELECT Usuario_id FROM Empresa WHERE CNPJ = ?
            ", [$cnpj]);
        }

        return $registro !== null;
    }

    public function criar(array $data): int
    {
        DB::insert("
            INSERT INTO Empresa (Usuario_id, CNPJ)
            VALUES (?, ?)
        ", [
            $data['usuario_id'],
            $data['cnpj'],
        ]);

        return (int) $data['usuario_id'];
    }

    public function atualizar(int $usuarioId, array $data): int
    {
        return DB::update("
            UPDATE Empresa
            SET CNPJ = ?
            WHERE Usuario_id = ?
        ", [
            $data['cnpj'],
            $usuarioId,
        ]);
    }

    public function remover(int $usuarioId): int
    {
        return DB::delete("
            DELETE FROM Empresa
            WHERE Usuario_id = ?
        ", [$usuarioId]);
    }
}
