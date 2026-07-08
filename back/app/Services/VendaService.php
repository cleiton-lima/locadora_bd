<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class VendaService
{
    public function listar(): array
    {
        return DB::select("
            SELECT id, valor, status, Veiculo_id, Pessoa_Fisica_id, Gerente_Comercial_Funcionario_id
            FROM Venda
            ORDER BY id
        ");
    }

    public function buscarPorId(int $id): ?object
    {
        return DB::selectOne("
            SELECT id, valor, status, Veiculo_id, Pessoa_Fisica_id, Gerente_Comercial_Funcionario_id
            FROM Venda
            WHERE id = ?
        ", [$id]);
    }

    public function veiculoExiste(int $veiculoId): bool
    {
        return DB::selectOne("
            SELECT id FROM Veiculo WHERE id = ?
        ", [$veiculoId]) !== null;
    }

    public function pessoaFisicaExiste(int $clienteId): bool
    {
        return DB::selectOne("
            SELECT Cliente_id FROM Pessoa_Fisica WHERE Cliente_id = ?
        ", [$clienteId]) !== null;
    }

    public function gerenteComercialExiste(int $funcionarioId): bool
    {
        return DB::selectOne("
            SELECT Funcionario_id FROM Gerente_Comercial WHERE Funcionario_id = ?
        ", [$funcionarioId]) !== null;
    }

    /**
     * Espelha a trigger trg_venda_bloqueia_veiculo_alugado
     * (docs/bdnormalizado/triggers.sql): garante uma mensagem 409 amigável
     * na API. A trigger continua sendo a garantia real no banco.
     */
    public function veiculoDisponivelParaVenda(int $veiculoId): bool
    {
        $veiculo = DB::selectOne("
            SELECT status FROM Veiculo WHERE id = ?
        ", [$veiculoId]);

        return $veiculo !== null && $veiculo->status !== 'ALUGADO';
    }

    public function criar(array $data): int
    {
        if (DB::getDriverName() === 'mysql') {
            return $this->criarViaProcedure($data);
        }

        return DB::transaction(function () use ($data) {
            DB::insert("
                INSERT INTO Venda (
                    valor,
                    status,
                    Veiculo_id,
                    Pessoa_Fisica_id,
                    Gerente_Comercial_Funcionario_id
                ) VALUES (?, ?, ?, ?, ?)
            ", [
                $data['valor'],
                $data['status'],
                $data['veiculo_id'],
                $data['pessoa_fisica_id'],
                $data['gerente_comercial_funcionario_id'],
            ]);

            $id = (int) DB::getPdo()->lastInsertId();

            DB::update("
                UPDATE Veiculo
                SET status = 'VENDIDO'
                WHERE id = ?
            ", [$data['veiculo_id']]);

            return $id;
        });
    }

    /**
     * Caminho usado em produção (MySQL): delega a escrita atômica em
     * Venda + Veiculo para sp_criar_venda
     * (docs/bdnormalizado/stored_procedures.sql).
     */
    private function criarViaProcedure(array $data): int
    {
        DB::statement('CALL sp_criar_venda(?, ?, ?, ?, ?, @novo_id)', [
            $data['valor'],
            $data['status'],
            $data['veiculo_id'],
            $data['pessoa_fisica_id'],
            $data['gerente_comercial_funcionario_id'],
        ]);

        return (int) DB::selectOne('SELECT @novo_id AS id')->id;
    }

    public function atualizar(int $id, array $data): int
    {
        return DB::update("
            UPDATE Venda
            SET valor = ?,
                status = ?,
                Veiculo_id = ?,
                Pessoa_Fisica_id = ?,
                Gerente_Comercial_Funcionario_id = ?
            WHERE id = ?
        ", [
            $data['valor'],
            $data['status'],
            $data['veiculo_id'],
            $data['pessoa_fisica_id'],
            $data['gerente_comercial_funcionario_id'],
            $id,
        ]);
    }

    public function remover(int $id): int
    {
        return DB::delete("
            DELETE FROM Venda
            WHERE id = ?
        ", [$id]);
    }
}
