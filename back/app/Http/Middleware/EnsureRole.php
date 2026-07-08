<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$rolesPermitidas): Response
    {
        $roles = (array) $request->attributes->get('auth_roles', []);

        if (empty(array_intersect($roles, $rolesPermitidas))) {
            return response()->json(['message' => 'Acesso negado para este perfil.'], 403);
        }

        return $next($request);
    }
}
