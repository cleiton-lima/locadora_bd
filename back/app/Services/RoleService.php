<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\Roles;
use Illuminate\Support\Facades\DB;

class RoleService
{
    /**
     * Deriva as roles do usuário a partir das tabelas de ator que já
     * existem no schema (Administrador/Atendente/Gerente_Comercial via
     * Funcionario, Pessoa_Fisica, Empresa). Não existe coluna "role":
     * a role é um fato derivado de qual(is) tabela(s) o Usuario.id aparece.
     *
     * @return list<string>
     */
    public function rolesDoUsuario(int $usuarioId): array
    {
        $roles = [];

        $funcionario = DB::selectOne("
            SELECT Usuario_id FROM Funcionario WHERE Usuario_id = ?
        ", [$usuarioId]);

        if ($funcionario) {
            if (DB::selectOne('SELECT Funcionario_id FROM Administrador WHERE Funcionario_id = ?', [$usuarioId])) {
                $roles[] = Roles::ADMINISTRADOR;
            }

            if (DB::selectOne('SELECT Funcionario_id FROM Atendente WHERE Funcionario_id = ?', [$usuarioId])) {
                $roles[] = Roles::ATENDENTE;
            }

            if (DB::selectOne('SELECT Funcionario_id FROM Gerente_Comercial WHERE Funcionario_id = ?', [$usuarioId])) {
                $roles[] = Roles::GERENTE_COMERCIAL;
            }
        }

        if (DB::selectOne('SELECT Cliente_id FROM Pessoa_Fisica WHERE Cliente_id = ?', [$usuarioId])) {
            $roles[] = Roles::CLIENTE_PF;
        }

        if (DB::selectOne('SELECT Usuario_id FROM Empresa WHERE Usuario_id = ?', [$usuarioId])) {
            $roles[] = Roles::EMPRESA;
        }

        return $roles;
    }
}
