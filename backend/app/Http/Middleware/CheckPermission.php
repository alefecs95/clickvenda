<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        // Verificar se o usuário está autenticado
        if (!$user) {
            return response()->json([
                'message' => 'Não autenticado'
            ], 401);
        }

        // Verificar se é admin (bypass de permissões)
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Verificar se o usuário tem a permissão específica
        if (!$user->hasPermission($permission)) {
            return response()->json([
                'message' => 'Acesso negado. Permissão necessária: ' . $permission
            ], 403);
        }

        return $next($request);
    }
}
