<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        // Retorna produtos ativos, incluindo relacionamentos de estoque compartilhado
        // Exclui modelos de vasilhame da listagem principal
        $products = Product::where('active', true)
            ->where(function($q){
                $q->whereNull('is_vasilhame_model')
                  ->orWhere('is_vasilhame_model', false);
            })
            ->with(['parentProduct', 'childProducts'])
            ->get()
            ->map(function($product) {
                $productArray = $product->toArray();
                $productArray['actual_stock'] = $product->getActualStock();
                $productArray['stock_info'] = $product->getStockInfo();
                $productArray['is_available_for_sale'] = $product->isAvailableForSale();
                $productArray['has_minimum_stock'] = $product->hasMinimumStock();
                return $productArray;
            });
            
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'minimum_stock' => 'nullable|integer|min:0',
            'sku' => 'nullable|string|unique:products,sku',
            'barcode' => 'nullable|string|unique:products,barcode',
            'available' => 'boolean',
            'returnable' => 'boolean',
            'returnable_price' => 'nullable|numeric|min:0|required_if:returnable,true',
            'returnable_quantity' => 'integer|min:1',
            'vasilhame_model_id' => 'nullable|exists:vasilhame_models,id|required_if:returnable,true',
            'parent_product_id' => 'nullable|exists:products,id',
            'stock_multiplier' => 'integer|min:1',
            'manages_stock' => 'boolean',
            'is_vasilhame_model' => 'sometimes|boolean'
        ]);

        $data = $request->all();
        
        // Se é produto filho, não gerencia estoque e não precisa de stock_quantity inicial
        if (!empty($data['parent_product_id'])) {
            $data['manages_stock'] = false;
            $data['stock_quantity'] = 0; // Será calculado automaticamente
        } else {
            // Para produtos pai, stock_quantity é obrigatório
            if (!isset($data['stock_quantity']) || $data['stock_quantity'] === null) {
                $data['stock_quantity'] = 0;
            }
        }

        $product = Product::create($data);
        
        // Se é produto filho, atualizar estoque baseado no pai
        if ($product->parent_product_id) {
            $product->refresh();
            $actualStock = $product->getActualStock();
            $product->update(['stock_quantity' => $actualStock]);
        }
        
        // Se é produto pai e tem estoque, sincronizar produtos filhos
        if ($product->manages_stock && $product->stock_quantity > 0) {
            $product->syncChildrenStocks();
        }

        return response()->json($product->load(['parentProduct', 'childProducts', 'vasilhameModel']), 201);
    }

    public function show(Product $product)
    {
        return response()->json($product);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'minimum_stock' => 'nullable|integer|min:0',
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|unique:products,barcode,' . $product->id,
            'available' => 'boolean',
            'returnable' => 'boolean',
            'returnable_price' => 'nullable|numeric|min:0|required_if:returnable,true',
            'returnable_quantity' => 'integer|min:1',
            'vasilhame_model_id' => 'nullable|exists:vasilhame_models,id|required_if:returnable,true',
            'parent_product_id' => 'nullable|exists:products,id',
            'stock_multiplier' => 'integer|min:1',
            'manages_stock' => 'boolean',
            'is_vasilhame_model' => 'sometimes|boolean'
        ]);

        $data = $request->all();
        
        // Se é produto filho, não gerencia estoque
        if (!empty($data['parent_product_id'])) {
            $data['manages_stock'] = false;
            // Para produtos filhos, não atualizar stock_quantity diretamente
            unset($data['stock_quantity']);
        } else {
            // Para produtos pai, garantir que stock_quantity seja definido
            if (!isset($data['stock_quantity']) || $data['stock_quantity'] === null) {
                $data['stock_quantity'] = $product->stock_quantity ?? 0;
            }
        }

        $product->update($data);
        
        // Se é produto filho, recalcular estoque baseado no pai
        if ($product->parent_product_id) {
            $actualStock = $product->getActualStock();
            $product->update(['stock_quantity' => $actualStock]);
        }
        
        return response()->json($product);
    }

    public function destroy(Product $product)
    {
        $product->update(['active' => false]);
        return response()->json(['message' => 'Produto desativado com sucesso']);
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        
        $products = Product::where('active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->get();

        return response()->json($products);
    }
    
    /**
     * Retorna apenas produtos disponíveis para venda
     */
    public function available()
    {
        $products = Product::available()
            ->where('stock_quantity', '>', 0)
            ->where(function($q){
                $q->whereNull('is_vasilhame_model')
                  ->orWhere('is_vasilhame_model', false);
            })
            ->get();
            
        return response()->json($products);
    }
    
    /**
     * Retorna produtos retornáveis (vassilhames) que são modelos
     */
    public function returnables()
    {
        $products = Product::returnable()
            ->available()
            ->where('is_vasilhame_model', true)
            ->get(['id','name','returnable_price','returnable_quantity']);
            
        return response()->json($products);
    }
    
    /**
     * Retorna apenas produtos que gerenciam estoque (produtos pai)
     */
    public function parentProducts()
    {
        $products = Product::parentProducts()
            ->where('active', true)
            ->get();
            
        return response()->json($products);
    }
    
    /**
     * Adiciona estoque a um produto (considerando estoque compartilhado)
     */
    public function addStock(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);
        
        $product->addStock($request->quantity);
        
        return response()->json([
            'success' => true,
            'message' => 'Estoque adicionado com sucesso',
            'product' => $product->fresh()->load(['parentProduct', 'childProducts']),
            'stock_info' => $product->getStockInfo()
        ]);
    }

    /**
     * Atualiza o estoque de um produto para uma quantidade específica
     */
    public function updateStock(Request $request, Product $product)
    {
        $request->validate([
            'new_quantity' => 'required|integer|min:0',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000'
        ]);
        
        $oldStock = $product->getActualStock();
        
        if ($product->manages_stock) {
            // Produto pai - atualiza diretamente
            $product->update(['stock_quantity' => $request->new_quantity]);
            $product->syncChildrenStocks();
        } else {
            // Produto filho - calcula e atualiza o pai
            if ($product->parentProduct) {
                $newParentStock = $request->new_quantity * $product->stock_multiplier;
                $product->parentProduct->update(['stock_quantity' => $newParentStock]);
                $product->parentProduct->syncChildrenStocks();
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Estoque atualizado com sucesso',
            'product' => $product->fresh()->load(['parentProduct', 'childProducts']),
            'old_stock' => $oldStock,
            'new_stock' => $product->fresh()->getActualStock(),
            'stock_info' => $product->getStockInfo()
        ]);
    }

    /**
     * Registra um movimento de estoque (entrada ou saída)
     */
    public function addStockMovement(Request $request, Product $product)
    {
        $request->validate([
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000'
        ]);
        
        $oldStock = $product->getActualStock();
        
        if ($request->type === 'in') {
            $product->addStock($request->quantity);
        } else {
            // Saída de estoque
            $product->updateSharedStock($request->quantity, 'decrease');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Movimento de estoque registrado com sucesso',
            'product' => $product->fresh()->load(['parentProduct', 'childProducts']),
            'movement' => [
                'type' => $request->type,
                'quantity' => $request->quantity,
                'reason' => $request->reason,
                'notes' => $request->notes
            ],
            'old_stock' => $oldStock,
            'new_stock' => $product->fresh()->getActualStock(),
            'stock_info' => $product->getStockInfo()
        ]);
    }
}
