<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function __construct(private readonly RoleService $roleService)
    {
    }

    public function autenticar(string $login, string $senha): ?object
    {
        $usuario = DB::selectOne("
            SELECT id, username, nome, email, ativo, senha_hash
            FROM Usuario
            WHERE (username = ? OR email = ?) AND ativo = 1
        ", [$login, $login]);

        if (! $usuario || ! Hash::check($senha, $usuario->senha_hash)) {
            return null;
        }

        DB::update('UPDATE Usuario SET ultimo_acesso = ? WHERE id = ?', [
            (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            $usuario->id,
        ]);

        return $usuario;
    }

    public function buscarUsuario(int $usuarioId): ?object
    {
        return DB::selectOne("
            SELECT id, username, nome, email, ultimo_acesso, data_cadastro, ativo
            FROM Usuario
            WHERE id = ?
        ", [$usuarioId]);
    }

    /**
     * @param  list<string>  $roles
     * @return array{access_token: string, refresh_token: string, expires_in: int}
     */
    public function gerarParDeTokens(int $usuarioId, array $roles): array
    {
        $ttl = (int) config('jwt.ttl');
        $now = new DateTimeImmutable();

        $accessToken = JWT::encode([
            'sub' => $usuarioId,
            'roles' => $roles,
            'iat' => $now->getTimestamp(),
            'exp' => $now->getTimestamp() + $ttl,
        ], config('jwt.secret'), 'HS256');

        $refreshToken = $this->criarRefreshToken($usuarioId);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => $ttl,
        ];
    }

    /**
     * Decodifica e valida um access token. Retorna null se inválido/expirado.
     */
    public function validarAccessToken(string $token): ?object
    {
        try {
            return JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Valida um refresh token, revoga-o e emite um novo par (rotação).
     * Retorna null se o token for inválido, expirado ou já revogado.
     *
     * @return array{access_token: string, refresh_token: string, expires_in: int}|null
     */
    public function renovar(string $refreshToken): ?array
    {
        $hash = hash('sha256', $refreshToken);

        $registro = DB::selectOne("
            SELECT id, Usuario_id
            FROM Refresh_Token
            WHERE token_hash = ?
              AND revoked_at IS NULL
              AND expires_at > ?
        ", [$hash, (new DateTimeImmutable())->format('Y-m-d H:i:s')]);

        if (! $registro) {
            return null;
        }

        DB::update('UPDATE Refresh_Token SET revoked_at = ? WHERE id = ?', [
            (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            $registro->id,
        ]);

        $roles = $this->roleService->rolesDoUsuario((int) $registro->Usuario_id);

        return $this->gerarParDeTokens((int) $registro->Usuario_id, $roles);
    }

    public function revogar(string $refreshToken): void
    {
        $hash = hash('sha256', $refreshToken);

        DB::update("
            UPDATE Refresh_Token
            SET revoked_at = ?
            WHERE token_hash = ? AND revoked_at IS NULL
        ", [(new DateTimeImmutable())->format('Y-m-d H:i:s'), $hash]);
    }

    private function criarRefreshToken(int $usuarioId): string
    {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $ttl = (int) config('jwt.refresh_ttl');
        $agora = new DateTimeImmutable();

        DB::insert("
            INSERT INTO Refresh_Token (Usuario_id, token_hash, expires_at, created_at)
            VALUES (?, ?, ?, ?)
        ", [
            $usuarioId,
            $hash,
            $agora->modify("+{$ttl} seconds")->format('Y-m-d H:i:s'),
            $agora->format('Y-m-d H:i:s'),
        ]);

        return $token;
    }

    /**
     * Autentica (ou cria) um Usuario a partir de um login Google já validado
     * pelo Socialite. Não usa Eloquent — só SQL puro via DB facade.
     */
    public function loginOuCriarComGoogle(string $googleId, string $email, string $nome): object
    {
        $vinculo = DB::selectOne('SELECT Usuario_id FROM Usuario_google WHERE google_id = ?', [$googleId]);

        if ($vinculo) {
            return $this->buscarUsuario((int) $vinculo->Usuario_id);
        }

        $usuarioExistente = DB::selectOne('SELECT id FROM Usuario WHERE email = ?', [$email]);

        if ($usuarioExistente) {
            DB::insert('INSERT INTO Usuario_google (Usuario_id, google_id) VALUES (?, ?)', [
                $usuarioExistente->id,
                $googleId,
            ]);

            return $this->buscarUsuario((int) $usuarioExistente->id);
        }

        return DB::transaction(function () use ($googleId, $email, $nome) {
            $username = $this->gerarUsernameUnico($email);
            $senhaAleatoria = Hash::make(Str::random(40));

            DB::insert("
                INSERT INTO Usuario (username, nome, email, data_cadastro, ativo, senha_hash)
                VALUES (?, ?, ?, ?, 1, ?)
            ", [$username, $nome, $email, (new DateTimeImmutable())->format('Y-m-d H:i:s'), $senhaAleatoria]);

            $usuarioId = (int) DB::getPdo()->lastInsertId();

            DB::insert('INSERT INTO Usuario_google (Usuario_id, google_id) VALUES (?, ?)', [
                $usuarioId,
                $googleId,
            ]);

            return $this->buscarUsuario($usuarioId);
        });
    }

    private function gerarUsernameUnico(string $email): string
    {
        $base = Str::slug(Str::before($email, '@'), '_');
        $username = $base;
        $sufixo = 1;

        while (DB::selectOne('SELECT id FROM Usuario WHERE username = ?', [$username]) !== null) {
            $username = $base.'_'.$sufixo;
            $sufixo++;
        }

        return $username;
    }
}
