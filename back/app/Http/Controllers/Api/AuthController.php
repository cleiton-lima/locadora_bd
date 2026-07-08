<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Services\AuthService;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly RoleService $roleService,
    ) {
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $usuario = $this->authService->autenticar($data['login'], $data['senha']);

        if (! $usuario) {
            return response()->json(['message' => 'Credenciais inválidas.'], 401);
        }

        $roles = $this->roleService->rolesDoUsuario((int) $usuario->id);
        $tokens = $this->authService->gerarParDeTokens((int) $usuario->id, $roles);

        return response()->json([
            ...$tokens,
            'usuario' => [
                'id' => $usuario->id,
                'username' => $usuario->username,
                'nome' => $usuario->nome,
                'email' => $usuario->email,
            ],
            'roles' => $roles,
        ]);
    }

    public function refresh(RefreshTokenRequest $request)
    {
        $tokens = $this->authService->renovar($request->validated('refresh_token'));

        if (! $tokens) {
            return response()->json(['message' => 'Refresh token inválido ou expirado.'], 401);
        }

        return response()->json($tokens);
    }

    public function logout(RefreshTokenRequest $request)
    {
        $this->authService->revogar($request->validated('refresh_token'));

        return response()->json(['message' => 'Sessão encerrada com sucesso.']);
    }

    public function me(Request $request)
    {
        $usuarioId = (int) $request->attributes->get('auth_usuario_id');
        $usuario = $this->authService->buscarUsuario($usuarioId);

        if (! $usuario) {
            return response()->json(['message' => 'Registro não encontrado.'], 404);
        }

        return response()->json([
            'usuario' => $usuario,
            'roles' => $request->attributes->get('auth_roles', []),
        ]);
    }

    public function googleRedirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Callback do OAuth do Google. Loga (ou cria) o Usuario via SQL puro em
     * AuthService::loginOuCriarComGoogle() e redireciona para o front com os
     * tokens na querystring (fluxo stateless, sem cookie cross-domain).
     */
    public function googleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $usuario = $this->authService->loginOuCriarComGoogle(
            (string) $googleUser->getId(),
            (string) $googleUser->getEmail(),
            (string) $googleUser->getName(),
        );

        $roles = $this->roleService->rolesDoUsuario((int) $usuario->id);
        $tokens = $this->authService->gerarParDeTokens((int) $usuario->id, $roles);

        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');

        return redirect(sprintf(
            '%s/auth/callback?%s',
            $frontendUrl,
            http_build_query($tokens),
        ));
    }
}
