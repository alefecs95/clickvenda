<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_order_id',
        'item_type',
        'product_id',
        'service_id',
        'description',
        'quantity',
        'unit_price',
        'total_price',
        'stock_removed',
        'stock_removed_at',
        'observations'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'stock_removed' => 'boolean',
        'stock_removed_at' => 'datetime'
    ];

    // Relacionamentos
    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    // Métodos auxiliares
    public function isProduct(): bool
    {
        return $this->item_type === 'product';
    }

    public function isService(): bool
    {
        return $this->item_type === 'service';
    }

    public function removeFromStock(): bool
    {
        if (!$this->isProduct() || $this->stock_removed) {
            return false;
        }

        $product = $this->product;
        if (!$product) {
            return false;
        }

        // Verificar se há estoque suficiente
        $availableStock = $product->getActualStock();
        if ($availableStock < $this->quantity) {
            return false;
        }

        // Remover do estoque
        $product->updateSharedStock($this->quantity, 'decrease');

        $this->update([
            'stock_removed' => true,
            'stock_removed_at' => now()
        ]);

        return true;
    }

    public function returnToStock(): bool
    {
        if (!$this->isProduct() || !$this->stock_removed) {
            return false;
        }

        $product = $this->product;
        if (!$product) {
            return false;
        }

        // Retornar ao estoque
        $product->updateSharedStock($this->quantity, 'increase');

        $this->update([
            'stock_removed' => false,
            'stock_removed_at' => null
        ]);

        return true;
    }

    public function calculateTotalPrice(): void
    {
        $this->total_price = $this->quantity * $this->unit_price;
        $this->save();
    }

    // Scopes
    public function scopeProducts($query)
    {
        return $query->where('item_type', 'product');
    }

    public function scopeServices($query)
    {
        return $query->where('item_type', 'service');
    }

    public function scopeWithStockRemoved($query)
    {
        return $query->where('stock_removed', true);
    }
}