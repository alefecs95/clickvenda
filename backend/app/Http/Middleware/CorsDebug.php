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
        
        // Verificar se é uma requisição OPTIONS (preflight)
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
        } else {
            $response = $next($request);
        }
        
        // Adicionar headers de CORS para desenvolvimento
        $origin = $request->header('Origin');
        $allowedOrigins = [
            'http://localhost:5173',
            'http://localhost:3000',
            'http://127.0.0.1:5173',
            'http://127.0.0.1:3000',
            'https://alefecarvalhosilva1758230435000.0771034.meusitehostgator.com.br'
        ];
        
        if (in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        } else {
            $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:5173');
        }
        
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400');
        
        \Log::info('CORS Debug - Response Headers:', $response->headers->all());
        
        return $response;
    }
}
