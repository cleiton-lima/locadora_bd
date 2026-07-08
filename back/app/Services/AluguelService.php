<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class AluguelService
{
    public function listar(): array
    {
        return DB::select("
            SELECT id, Atendente_entrega_id, Atendente_devolucao_id, Pessoa_Fisica_id,
                   status, valor, tipo, data_inicial, data_final, data_final_prevista,
                   Contrato_frota_id, Veiculo_id
            FROM Aluguel
            ORDER BY id
        ");
    }

    public function buscarPorId(int $id): ?object
    {
        return DB::selectOne("
            SELECT id, Atendente_entrega_id, Atendente_devolucao_id, Pessoa_Fisica_id,
                   status, valor, tipo, data_inicial, data_final, data_final_prevista,
                   Contrato_frota_id, Veiculo_id
            FROM Aluguel
            WHERE id = ?
        ", [$id]);
    }

    public function atendenteExiste(int $funcionarioId): bool
    {
        return DB::selectOne("
            SELECT Funcionario_id FROM Atendente WHERE Funcionario_id = ?
        ", [$funcionarioId]) !== null;
    }

    public function pessoaFisicaExiste(int $clienteId): bool
    {
        return DB::selectOne("
            SELECT Cliente_id FROM Pessoa_Fisica WHERE Cliente_id = ?
        ", [$clienteId]) !== null;
    }

    public function contratoFrotaExiste(int $id): bool
    {
        return DB::selectOne("
            SELECT id FROM Contrato_Frota WHERE id = ?
        ", [$id]) !== null;
    }

    public function veiculoExiste(int $id): bool
    {
        return DB::selectOne("
            SELECT id FROM Veiculo WHERE id = ?
        ", [$id]) !== null;
    }

    /**
     * Espelha a trigger trg_aluguel_valida_cnh
     * (docs/bdnormalizado/triggers.sql): garante uma mensagem 409 amigável
     * na API. A trigger continua sendo a garantia real no banco.
     */
    public function cnhValidaParaAluguel(int $pessoaFisicaId, string $dataInicial): bool
    {
        $cnh = DB::selectOne("
            SELECT c.data_validade
            FROM Pessoa_Fisica pf
            JOIN CNH c ON c.numero = pf.CNH_numero
            WHERE pf.Cliente_id = ?
        ", [$pessoaFisicaId]);

        if (! $cnh) {
            return false;
        }

        $validade = new DateTimeImmutable($cnh->data_validade);
        $inicio = new DateTimeImmutable($dataInicial);

        return $validade >= $inicio;
    }

    public function bloqueioVeiculoParaAluguel(int $veiculoId, bool $paraPessoaFisica): ?string
    {
        $veiculo = DB::selectOne("
            SELECT id, status
            FROM Veiculo
            WHERE id = ?
        ", [$veiculoId]);

        if (! $veiculo) {
            return 'Registro relacionado não encontrado.';
        }

        if ($veiculo->status === 'ALUGADO') {
            return 'Veículo indisponível para aluguel.';
        }

        $aluguelAtivo = DB::selectOne("
            SELECT id
            FROM Aluguel
            WHERE Veiculo_id = ?
              AND status = 'ATIVO'
            LIMIT 1
        ", [$veiculoId]);

        if ($aluguelAtivo) {
            return 'Veículo indisponível para aluguel.';
        }

        if ($paraPessoaFisica) {
            $contratoFrotaAtivo = DB::selectOne("
                SELECT id
                FROM Aluguel
                WHERE Veiculo_id = ?
                  AND status = 'ATIVO'
                  AND Contrato_frota_id IS NOT NULL
                LIMIT 1
            ", [$veiculoId]);

            if ($contratoFrotaAtivo) {
                return 'Veículo vinculado a contrato de frota ativo.';
            }
        }

        return null;
    }

    public function criar(array $data): int
    {
        if (DB::getDriverName() === 'mysql') {
            return $this->criarViaProcedure($data);
        }

        return DB::transaction(function () use ($data) {
            $veiculo = $this->buscarVeiculoParaPrecificacao((int) $data['veiculo_id']);
            $valor = $this->calcularValor($data, $veiculo);

            DB::insert("
                INSERT INTO Aluguel (
                    Atendente_entrega_id,
                    Atendente_devolucao_id,
                    Pessoa_Fisica_id,
                    status,
                    valor,
                    tipo,
                    data_inicial,
                    data_final,
                    data_final_prevista,
                    Contrato_frota_id,
                    Veiculo_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                $data['atendente_entrega_id'],
                $data['atendente_devolucao_id'] ?? null,
                $data['pessoa_fisica_id'] ?? null,
                $data['status'],
                $valor,
                $data['tipo'],
                $data['data_inicial'],
                $data['data_final'] ?? null,
                $data['data_final_prevista'],
                $data['contrato_frota_id'] ?? null,
                $data['veiculo_id'],
            ]);

            $id = (int) DB::getPdo()->lastInsertId();

            DB::update("
                UPDATE Veiculo
                SET status = 'ALUGADO'
                WHERE id = ?
            ", [$data['veiculo_id']]);

            return $id;
        });
    }

    /**
     * Caminho usado em produção (MySQL): delega o cálculo do valor e a
     * escrita atômica em Aluguel + Veiculo para sp_criar_aluguel
     * (docs/bdnormalizado/stored_procedures.sql). A procedure assume que
     * atendente/veículo/pessoa física/contrato de frota já foram validados
     * pelo Controller.
     */
    private function criarViaProcedure(array $data): int
    {
        DB::statement("CALL sp_criar_aluguel(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @novo_id, @valor)", [
            $data['atendente_entrega_id'],
            $data['atendente_devolucao_id'] ?? null,
            $data['pessoa_fisica_id'] ?? null,
            $data['status'],
            $data['tipo'],
            $data['data_inicial'],
            $data['data_final'] ?? null,
            $data['data_final_prevista'],
            $data['contrato_frota_id'] ?? null,
            $data['veiculo_id'],
        ]);

        return (int) DB::selectOne('SELECT @novo_id AS id')->id;
    }

    public function atualizar(int $id, array $data): int
    {
        return DB::update("
            UPDATE Aluguel
            SET Atendente_entrega_id = ?,
                Atendente_devolucao_id = ?,
                Pessoa_Fisica_id = ?,
                status = ?,
                valor = ?,
                tipo = ?,
                data_inicial = ?,
                data_final = ?,
                data_final_prevista = ?,
                Contrato_frota_id = ?,
                Veiculo_id = ?
            WHERE id = ?
        ", [
            $data['atendente_entrega_id'],
            $data['atendente_devolucao_id'] ?? null,
            $data['pessoa_fisica_id'] ?? null,
            $data['status'],
            $data['valor'],
            $data['tipo'],
            $data['data_inicial'],
            $data['data_final'] ?? null,
            $data['data_final_prevista'],
            $data['contrato_frota_id'] ?? null,
            $data['veiculo_id'],
            $id,
        ]);
    }

    public function remover(int $id): int
    {
        return DB::delete("
            DELETE FROM Aluguel
            WHERE id = ?
        ", [$id]);
    }

    public function devolver(int $id, array $data): int
    {
        if (DB::getDriverName() === 'mysql') {
            return $this->devolverViaProcedure($id, $data);
        }

        return DB::transaction(function () use ($id, $data) {
            $aluguel = DB::selectOne("
                SELECT id, Veiculo_id
                FROM Aluguel
                WHERE id = ?
            ", [$id]);

            if (! $aluguel) {
                return 0;
            }

            DB::update("
                UPDATE Aluguel
                SET status = 'FINALIZADO',
                    data_final = ?,
                    Atendente_devolucao_id = ?
                WHERE id = ?
            ", [
                $data['data_final'],
                $data['atendente_devolucao_id'],
                $id,
            ]);

            DB::update("
                UPDATE Veiculo
                SET status = 'DISPONIVEL'
                WHERE id = ?
            ", [$aluguel->Veiculo_id]);

            return 1;
        });
    }

    /**
     * Caminho usado em produção (MySQL): delega a devolução para
     * sp_devolver_aluguel (docs/bdnormalizado/stored_procedures.sql).
     */
    private function devolverViaProcedure(int $id, array $data): int
    {
        DB::statement('CALL sp_devolver_aluguel(?, ?, ?, @linhas)', [
            $id,
            $data['data_final'],
            $data['atendente_devolucao_id'],
        ]);

        return (int) DB::selectOne('SELECT @linhas AS linhas')->linhas;
    }

    private function buscarVeiculoParaPrecificacao(int $veiculoId): object
    {
        return DB::selectOne("
            SELECT id, grupo, finalidade, Filial_id
            FROM Veiculo
            WHERE id = ?
        ", [$veiculoId]);
    }

    private function calcularValor(array $data, object $veiculo): float
    {
        $inicio = new DateTimeImmutable($data['data_inicial']);
        $fimPrevisto = new DateTimeImmutable($data['data_final_prevista']);
        $segundos = max(1, $fimPrevisto->getTimestamp() - $inicio->getTimestamp());
        $horas = max(1, (int) ceil($segundos / 3600));

        if ($horas < 24) {
            $bases = ['A' => 25, 'B' => 35, 'C' => 45, 'D' => 60, 'E' => 80];
            $base = $bases[$veiculo->grupo] ?? 40;
            $disponiveis = $this->contarDisponiveis($veiculo->Filial_id, 'CURTA_DURACAO');

            return round($base * $horas * $this->multiplicadorEscassez($disponiveis), 2);
        }

        $dias = max(1, (int) ceil($horas / 24));
        $bases = ['A' => 120, 'B' => 160, 'C' => 220, 'D' => 300, 'E' => 420];
        $base = $bases[$veiculo->grupo] ?? 180;
        $disponiveis = $this->contarDisponiveis($veiculo->Filial_id, 'LONGA_DURACAO');

        return round(
            $base * $dias * $this->multiplicadorEscassez($disponiveis) * $this->multiplicadorDesconto($dias),
            2
        );
    }

    private function contarDisponiveis(int $filialId, string $finalidade): int
    {
        $resultado = DB::selectOne("
            SELECT COUNT(*) AS total
            FROM Veiculo
            WHERE Filial_id = ?
              AND finalidade = ?
              AND status = 'DISPONIVEL'
        ", [$filialId, $finalidade]);

        return (int) $resultado->total;
    }

    private function multiplicadorEscassez(int $disponiveis): float
    {
        return 1 + max(0, 5 - $disponiveis) * 0.10;
    }

    private function multiplicadorDesconto(int $dias): float
    {
        if ($dias >= 30) {
            return 0.80;
        }

        if ($dias >= 15) {
            return 0.85;
        }

        if ($dias >= 7) {
            return 0.90;
        }

        return 1.00;
    }
}
