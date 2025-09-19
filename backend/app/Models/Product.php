<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock_quantity',
        'minimum_stock',
        'sku',
        'barcode',
        'active',
        'available',
        'returnable',
        'returnable_price',
        'returnable_quantity',
        'parent_product_id',
        'stock_multiplier',
        'manages_stock',
        // added to flag vasilhame models explicitly
        'is_vasilhame_model',
        // FK para modelo de vasilhame
        'vasilhame_model_id'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'returnable_price' => 'decimal:2',
        'active' => 'boolean',
        'available' => 'boolean',
        'returnable' => 'boolean',
        'returnable_quantity' => 'integer',
        'stock_multiplier' => 'integer',
        'manages_stock' => 'boolean',
        // cast for the new flag
        'is_vasilhame_model' => 'boolean',
        // cast para o relacionamento
        'vasilhame_model_id' => 'integer',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    
    /**
     * Produto pai que gerencia o estoque
     */
    public function parentProduct()
    {
        return $this->belongsTo(Product::class, 'parent_product_id');
    }
    
    /**
     * Produtos filhos que compartilham este estoque
     */
    public function childProducts()
    {
        return $this->hasMany(Product::class, 'parent_product_id');
    }

    /**
     * Modelo de vasilhame associado (quando produto é retornável)
     */
    public function vasilhameModel()
    {
        return $this->belongsTo(VasilhameModel::class, 'vasilhame_model_id');
    }
    
    /**
     * Verifica se o produto está disponível para venda
     */
    public function isAvailableForSale(): bool
    {
        return $this->active && $this->available && $this->hasMinimumStock();
    }
    
    /**
     * Verifica se o produto tem estoque mínimo suficiente
     */
    public function hasMinimumStock(): bool
    {
        if (!$this->manages_stock && $this->parentProduct) {
            // Para produtos filhos, verifica se o pai tem estoque suficiente para formar pelo menos 1 unidade
            $parentStock = $this->parentProduct->stock_quantity;
            $requiredParentStock = $this->stock_multiplier;
            
            // Se tem estoque mínimo definido, considera também
            if ($this->minimum_stock > 0) {
                $requiredParentStock = max($requiredParentStock, $this->minimum_stock * $this->stock_multiplier);
            }
            
            return $parentStock >= $requiredParentStock;
        }
        
        // Para produtos pai, verifica normalmente
        $actualStock = $this->getActualStock();
        return $actualStock >= $this->minimum_stock;
    }
    
    /**
     * Obtém o estoque real do produto (considerando estoque compartilhado)
     */
    public function getActualStock(): int
    {
        if ($this->manages_stock) {
            // Produto pai - retorna seu próprio estoque
            return $this->stock_quantity;
        } else {
            // Produto filho - calcula baseado no estoque do pai
            if ($this->parentProduct) {
                return intval($this->parentProduct->stock_quantity / $this->stock_multiplier);
            }
            return $this->stock_quantity;
        }
    }
    
    /**
     * Atualiza o estoque considerando o multiplicador
     */
    public function updateSharedStock(int $quantity, string $operation = 'decrease'): bool
    {
        if ($this->manages_stock) {
            // Produto pai - atualiza diretamente
            $newStock = $operation === 'decrease' 
                ? $this->stock_quantity - $quantity
                : $this->stock_quantity + $quantity;
                
            $this->update(['stock_quantity' => max(0, $newStock)]);
        } else {
            // Produto filho - atualiza o estoque do pai considerando o multiplicador
            if ($this->parentProduct) {
                $adjustedQuantity = $quantity * $this->stock_multiplier;
                $newStock = $operation === 'decrease'
                    ? $this->parentProduct->stock_quantity - $adjustedQuantity
                    : $this->parentProduct->stock_quantity + $adjustedQuantity;
                    
                $this->parentProduct->update(['stock_quantity' => max(0, $newStock)]);
                
                // Atualiza também os estoques calculados dos produtos filhos
                $this->syncChildrenStocks();
            }
        }
        
        return true;
    }
    
    /**
     * Sincroniza os estoques dos produtos filhos baseado no pai
     */
    public function syncChildrenStocks(): void
    {
        if ($this->manages_stock) {
            foreach ($this->childProducts as $child) {
                $childStock = intval($this->stock_quantity / $child->stock_multiplier);
                $child->update(['stock_quantity' => $childStock]);
            }
        }
    }
    
    /**
     * Adiciona estoque considerando se é produto pai ou filho
     */
    public function addStock(int $quantity): bool
    {
        if ($this->manages_stock) {
            // Produto pai - adiciona diretamente e sincroniza filhos
            $this->increment('stock_quantity', $quantity);
            $this->syncChildrenStocks();
        } else {
            // Produto filho - adiciona no pai considerando multiplicador
            $this->updateSharedStock($quantity, 'increase');
        }
        
        return true;
    }
    
    /**
     * Calcula o preço total incluindo vassilhame se retornável
     */
    public function getTotalPriceWithReturnable(): float
    {
        $price = $this->price;
        
        if ($this->returnable && $this->returnable_price) {
            $price += ($this->returnable_price * $this->returnable_quantity);
        }
        
        return $price;
    }
    
    /**
     * Obtém informações sobre vassilhame
     */
    public function getReturnableInfo(): array
    {
        if (!$this->returnable) {
            return [];
        }
        
        return [
            'has_returnable' => true,
            'returnable_price' => $this->returnable_price,
            'returnable_quantity' => $this->returnable_quantity,
            'total_returnable_cost' => $this->returnable_price * $this->returnable_quantity
        ];
    }
    
    /**
     * Scope para produtos disponíveis
     */
    public function scopeAvailable($query)
    {
        return $query->where('active', true)->where('available', true);
    }
    
    /**
     * Scope para produtos retornáveis
     */
    public function scopeReturnable($query)
    {
        return $query->where('returnable', true);
    }
    
    /**
     * Scope para produtos que gerenciam estoque (produtos pai)
     */
    public function scopeParentProducts($query)
    {
        return $query->where('manages_stock', true);
    }
    
    /**
     * Scope para produtos filhos (que compartilham estoque)
     */
    public function scopeChildProducts($query)
    {
        return $query->where('manages_stock', false)->whereNotNull('parent_product_id');
    }
    
    /**
     * Verifica se o produto tem estoque suficiente para venda
     */
    public function hasStock(int $quantity): bool
    {
        return $this->getActualStock() >= $quantity;
    }
    
    /**
     * Obtém informações de estoque compartilhado
     */
    public function getStockInfo(): array
    {
        $info = [
            'manages_stock' => $this->manages_stock,
            'actual_stock' => $this->getActualStock(),
            'stored_stock' => $this->stock_quantity,
            'stock_multiplier' => $this->stock_multiplier,
        ];
        
        if (!$this->manages_stock && $this->parentProduct) {
            $info['parent_product'] = [
                'id' => $this->parentProduct->id,
                'name' => $this->parentProduct->name,
            ];
        }
        
        return $info;
    }
}
