<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PessoaFisicaService
{
    public function listar(): array
    {
        return DB::select("
            SELECT pf.Cliente_id,
                   pf.CPF,
                   pf.CNH_numero,
                   c.estado AS cnh_estado,
                   c.categoria AS cnh_categoria,
                   c.data_emissao AS cnh_data_emissao,
                   c.data_validade AS cnh_data_validade
            FROM Pessoa_Fisica pf
            INNER JOIN CNH c ON c.numero = pf.CNH_numero
            ORDER BY pf.Cliente_id
        ");
    }

    public function buscarPorId(int $clienteId): ?object
    {
        return DB::selectOne("
            SELECT pf.Cliente_id,
                   pf.CPF,
                   pf.CNH_numero,
                   c.estado AS cnh_estado,
                   c.categoria AS cnh_categoria,
                   c.data_emissao AS cnh_data_emissao,
                   c.data_validade AS cnh_data_validade
            FROM Pessoa_Fisica pf
            INNER JOIN CNH c ON c.numero = pf.CNH_numero
            WHERE pf.Cliente_id = ?
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

    public function cnhExiste(string $cnhNumero, ?int $ignorarId = null): bool
    {
        if ($ignorarId !== null) {
            $registro = DB::selectOne("
                SELECT Cliente_id FROM Pessoa_Fisica WHERE CNH_numero = ? AND Cliente_id != ?
            ", [$cnhNumero, $ignorarId]);
        } else {
            $registro = DB::selectOne("
                SELECT Cliente_id FROM Pessoa_Fisica WHERE CNH_numero = ?
            ", [$cnhNumero]);
        }

        return $registro !== null;
    }

    public function cnhCadastroExiste(string $cnhNumero): bool
    {
        return DB::selectOne("
            SELECT numero FROM CNH WHERE numero = ?
        ", [$cnhNumero]) !== null;
    }

    public function criar(array $data): int
    {
        DB::insert("
            INSERT INTO Pessoa_Fisica (
                Cliente_id,
                CPF,
                CNH_numero
            ) VALUES (?, ?, ?)
        ", [
            $data['cliente_id'],
            $data['cpf'],
            $data['cnh_numero'],
        ]);

        return (int) $data['cliente_id'];
    }

    public function atualizar(int $clienteId, array $data): int
    {
        return DB::update("
            UPDATE Pessoa_Fisica
            SET CPF = ?, CNH_numero = ?
            WHERE Cliente_id = ?
        ", [
            $data['cpf'],
            $data['cnh_numero'],
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
