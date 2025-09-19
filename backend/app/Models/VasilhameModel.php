<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VasilhameModel extends Model
{
    use HasFactory;

    protected $table = 'vasilhame_models';

    protected $fillable = [
        'name',
        'description',
        'returnable_price',
        'returnable_quantity',
        'stock_quantity',
        'minimum_stock',
        'available'
    ];

    protected $casts = [
        'returnable_price' => 'decimal:2',
        'returnable_quantity' => 'integer',
        'stock_quantity' => 'integer',
        'minimum_stock' => 'integer',
        'available' => 'boolean'
    ];

    /**
     * Produtos que usam este modelo de vasilhame
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Scope para modelos disponíveis
     */
    public function scopeAvailable($query)
    {
        return $query->where('available', true);
    }

    /**
     * Verifica se o estoque está abaixo do mínimo
     */
    public function getStockBelowMinimumAttribute(): bool
    {
        return $this->stock_quantity < $this->minimum_stock;
    }

    /**
     * Formata o preço para exibição
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->returnable_price, 2, ',', '.');
    }
}