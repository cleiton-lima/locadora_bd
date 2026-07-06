<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioService
{
    public function listar(): array
    {
        return DB::select("
            SELECT id, username, nome, email, ultimo_acesso, data_cadastro, ativo
            FROM Usuario
            ORDER BY nome
        ");
    }

    public function buscarPorId(int $id): ?object
    {
        return DB::selectOne("
            SELECT id, username, nome, email, ultimo_acesso, data_cadastro, ativo
            FROM Usuario
            WHERE id = ?
        ", [$id]);
    }

    public function usernameExiste(string $username, ?int $ignorarId = null): bool
    {
        if ($ignorarId !== null) {
            $registro = DB::selectOne("
                SELECT id FROM Usuario WHERE username = ? AND id != ?
            ", [$username, $ignorarId]);
        } else {
            $registro = DB::selectOne("
                SELECT id FROM Usuario WHERE username = ?
            ", [$username]);
        }

        return $registro !== null;
    }

    public function emailExiste(string $email, ?int $ignorarId = null): bool
    {
        if ($ignorarId !== null) {
            $registro = DB::selectOne("
                SELECT id FROM Usuario WHERE email = ? AND id != ?
            ", [$email, $ignorarId]);
        } else {
            $registro = DB::selectOne("
                SELECT id FROM Usuario WHERE email = ?
            ", [$email]);
        }

        return $registro !== null;
    }

    public function criar(array $data): int
    {
        $ativo = isset($data['ativo']) ? (int) $data['ativo'] : 1;

        DB::insert("
            INSERT INTO Usuario (
                username,
                nome,
                email,
                data_cadastro,
                ativo,
                senha_hash
            ) VALUES (?, ?, ?, NOW(), ?, ?)
        ", [
            $data['username'],
            $data['nome'],
            $data['email'],
            $ativo,
            Hash::make($data['senha']),
        ]);

        return (int) DB::getPdo()->lastInsertId();
    }

    public function atualizar(int $id, array $data): int
    {
        $ativo = isset($data['ativo']) ? (int) $data['ativo'] : 1;

        if (! empty($data['senha'])) {
            return DB::update("
                UPDATE Usuario
                SET username = ?, nome = ?, email = ?, ativo = ?, senha_hash = ?
                WHERE id = ?
            ", [
                $data['username'],
                $data['nome'],
                $data['email'],
                $ativo,
                Hash::make($data['senha']),
                $id,
            ]);
        }

        return DB::update("
            UPDATE Usuario
            SET username = ?, nome = ?, email = ?, ativo = ?
            WHERE id = ?
        ", [
            $data['username'],
            $data['nome'],
            $data['email'],
            $ativo,
            $id,
        ]);
    }

    public function remover(int $id): int
    {
        return DB::delete("
            DELETE FROM Usuario
            WHERE id = ?
        ", [$id]);
    }
}
