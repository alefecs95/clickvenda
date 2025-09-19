<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Product;
use Symfony\Component\HttpFoundation\Response;

class CheckStock
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar se é uma requisição para criar pedido
        if ($request->isMethod('POST') && $request->routeIs('orders.store')) {
            $items = $request->input('items', []);
            
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                
                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Produto não encontrado',
                        'error' => "Produto com ID {$item['product_id']} não existe"
                    ], 404);
                }
                
                if ($product->stock_quantity < $item['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Estoque insuficiente',
                        'error' => "Produto {$product->name} não possui estoque suficiente. Disponível: {$product->stock_quantity}, Solicitado: {$item['quantity']}"
                    ], 400);
                }
                
                if (!$product->active) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Produto inativo',
                        'error' => "Produto {$product->name} está inativo e não pode ser vendido"
                    ], 400);
                }
            }
        }

        return $next($request);
    }
}
