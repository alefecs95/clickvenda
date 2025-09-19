<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // <-- Adicione esta linha


class CorsDebug
{
    public function handle(Request $request, Closure $next)
    {
        // Log da requisição
        \Log::info('CORS Debug - Request Headers:', $request->headers->all());
        Log::info('CORS Debug - Origem', ['origin' => $request->header('Origin')]);        
        $response = $next($request);
        
        // Adicionar headers de CORS
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        
        \Log::info('CORS Debug - Response Headers:', $response->headers->all());
        
        return $response;
    }
}
