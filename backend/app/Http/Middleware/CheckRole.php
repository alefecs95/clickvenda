<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        // Verificar se o usuário está autenticado
        if (!$user) {
            return response()->json([
                'message' => 'Não autenticado'
            ], 401);
        }

        // Verificar se é admin (bypass de roles)
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Verificar se o usuário tem a role específica
        if (!$user->hasRole($role)) {
            return response()->json([
                'message' => 'Acesso negado. Role necessária: ' . $role
            ], 403);
        }

        return $next($request);
    }
}
