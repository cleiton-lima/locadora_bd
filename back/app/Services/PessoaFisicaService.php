<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PessoaFisicaService
{
    public function listar(): array
    {
        return DB::select("
            SELECT Cliente_id, CPF, CNH, estado, categoria, data_emissao, data_validade
            FROM Pessoa_Fisica
            ORDER BY Cliente_id
        ");
    }

    public function buscarPorId(int $clienteId): ?object
    {
        return DB::selectOne("
            SELECT Cliente_id, CPF, CNH, estado, categoria, data_emissao, data_validade
            FROM Pessoa_Fisica
            WHERE Cliente_id = ?
        ", [$clienteId]);
    }

    public function usuarioExiste(int $clienteId): bool
    {
        return DB::selectOne("
            SELECT id FROM Usuario WHERE id = ?
        ", [$clienteId]) !== null;
    }

    public function cpfExiste(string $cpf, ?int $ignorarId = null): bool
    {
        if ($ignorarId !== null) {
            $registro = DB::selectOne("
                SELECT Cliente_id FROM Pessoa_Fisica WHERE CPF = ? AND Cliente_id != ?
            ", [$cpf, $ignorarId]);
        } else {
            $registro = DB::selectOne("
                SELECT Cliente_id FROM Pessoa_Fisica WHERE CPF = ?
            ", [$cpf]);
        }

        return $registro !== null;
    }

    public function cnhExiste(string $cnh, ?int $ignorarId = null): bool
    {
        if ($ignorarId !== null) {
            $registro = DB::selectOne("
                SELECT Cliente_id FROM Pessoa_Fisica WHERE CNH = ? AND Cliente_id != ?
            ", [$cnh, $ignorarId]);
        } else {
            $registro = DB::selectOne("
                SELECT Cliente_id FROM Pessoa_Fisica WHERE CNH = ?
            ", [$cnh]);
        }

        return $registro !== null;
    }

    public function criar(array $data): int
    {
        DB::insert("
            INSERT INTO Pessoa_Fisica (
                Cliente_id,
                CPF,
                CNH,
                estado,
                categoria,
                data_emissao,
                data_validade
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ", [
            $data['cliente_id'],
            $data['cpf'],
            $data['cnh'],
            $data['estado'],
            $data['categoria'],
            $data['data_emissao'],
            $data['data_validade'],
        ]);

        return (int) $data['cliente_id'];
    }

    public function atualizar(int $clienteId, array $data): int
    {
        return DB::update("
            UPDATE Pessoa_Fisica
            SET CPF = ?, CNH = ?, estado = ?, categoria = ?, data_emissao = ?, data_validade = ?
            WHERE Cliente_id = ?
        ", [
            $data['cpf'],
            $data['cnh'],
            $data['estado'],
            $data['categoria'],
            $data['data_emissao'],
            $data['data_validade'],
            $clienteId,
        ]);
    }

    public function remover(int $clienteId): int
    {
        return DB::delete("
            DELETE FROM Pessoa_Fisica
            WHERE Cliente_id = ?
        ", [$clienteId]);
    }
}
