<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\AuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthenticate
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization', '');

        if (! str_starts_with($header, 'Bearer ')) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        $token = substr($header, 7);
        $payload = $this->authService->validarAccessToken($token);

        if (! $payload) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        $request->attributes->set('auth_usuario_id', (int) $payload->sub);
        $request->attributes->set('auth_roles', (array) $payload->roles);

        return $next($request);
    }
}
