<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Para APIs, sempre retorna null (não redireciona)
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }
        
        // Para web, redireciona para login (se a rota existir)
        return route('login', [], false);
    }
}
