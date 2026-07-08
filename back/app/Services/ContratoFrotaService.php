<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ContratoFrotaService
{
    public function listar(): array
    {
        return DB::select("
            SELECT id, Gerente_Comercial_id, Empresa_id, data_inicio, data_final, quantidade_veiculos
            FROM Contrato_Frota
            ORDER BY id
        ");
    }

    public function buscarPorId(int $id): ?object
    {
        return DB::selectOne("
            SELECT id, Gerente_Comercial_id, Empresa_id, data_inicio, data_final, quantidade_veiculos
            FROM Contrato_Frota
            WHERE id = ?
        ", [$id]);
    }

    public function gerenteComercialExiste(int $gerenteComercialId): bool
    {
        return DB::selectOne("
            SELECT Funcionario_id FROM Gerente_Comercial WHERE Funcionario_id = ?
        ", [$gerenteComercialId]) !== null;
    }

    public function empresaExiste(int $empresaId): bool
    {
        return DB::selectOne("
            SELECT Usuario_id FROM Empresa WHERE Usuario_id = ?
        ", [$empresaId]) !== null;
    }

    public function criar(array $data): int
    {
        DB::insert("
            INSERT INTO Contrato_Frota (
                Gerente_Comercial_id,
                Empresa_id,
                data_inicio,
                data_final,
                quantidade_veiculos
            ) VALUES (?, ?, ?, ?, ?)
        ", [
            $data['gerente_comercial_id'],
            $data['empresa_id'],
            $data['data_inicio'],
            $data['data_final'],
            $data['quantidade_veiculos'],
        ]);

        return (int) DB::getPdo()->lastInsertId();
    }

    public function atualizar(int $id, array $data): int
    {
        return DB::update("
            UPDATE Contrato_Frota
            SET Gerente_Comercial_id = ?,
                Empresa_id = ?,
                data_inicio = ?,
                data_final = ?,
                quantidade_veiculos = ?
            WHERE id = ?
        ", [
            $data['gerente_comercial_id'],
            $data['empresa_id'],
            $data['data_inicio'],
            $data['data_final'],
            $data['quantidade_veiculos'],
            $id,
        ]);
    }

    public function remover(int $id): int
    {
        return DB::delete("
            DELETE FROM Contrato_Frota
            WHERE id = ?
        ", [$id]);
    }

    /**
     * Encerra o contrato: define data_final, finaliza os Aluguel ATIVO
     * vinculados e devolve os respectivos Veiculo para 'DISPONIVEL'.
     * Retorna a quantidade de veículos liberados.
     */
    public function encerrar(int $id, array $data): int
    {
        if (DB::getDriverName() === 'mysql') {
            return $this->encerrarViaProcedure($id, $data);
        }

        return DB::transaction(function () use ($id, $data) {
            $veiculos = DB::select("
                SELECT Veiculo_id
                FROM Aluguel
                WHERE Contrato_frota_id = ?
                  AND status = 'ATIVO'
            ", [$id]);

            foreach ($veiculos as $veiculo) {
                DB::update("
                    UPDATE Veiculo
                    SET status = 'DISPONIVEL'
                    WHERE id = ?
                ", [$veiculo->Veiculo_id]);
            }

            DB::update("
                UPDATE Aluguel
                SET status = 'FINALIZADO',
                    data_final = ?
                WHERE Contrato_frota_id = ?
                  AND status = 'ATIVO'
            ", [$data['data_final'], $id]);

            DB::update("
                UPDATE Contrato_Frota
                SET data_final = ?
                WHERE id = ?
            ", [$data['data_final'], $id]);

            return count($veiculos);
        });
    }

    /**
     * Caminho usado em produção (MySQL): delega para
     * sp_encerrar_contrato_frota (docs/bdnormalizado/stored_procedures.sql).
     */
    private function encerrarViaProcedure(int $id, array $data): int
    {
        DB::statement('CALL sp_encerrar_contrato_frota(?, ?, @liberados)', [
            $id,
            $data['data_final'],
        ]);

        return (int) DB::selectOne('SELECT @liberados AS liberados')->liberados;
    }
}
