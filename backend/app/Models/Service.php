<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category',
        'estimated_duration',
        'active',
        'notes'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'active' => 'boolean'
    ];

    // Relacionamentos
    public function serviceOrderItems(): HasMany
    {
        return $this->hasMany(ServiceOrderItem::class);
    }

    // Métodos auxiliares
    public function getFormattedPrice(): string
    {
        return 'R$ ' . number_format($this->price, 2, ',', '.');
    }

    public function getFormattedDuration(): string
    {
        if (!$this->estimated_duration) {
            return 'Não informado';
        }

        $hours = floor($this->estimated_duration / 60);
        $minutes = $this->estimated_duration % 60;

        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'min';
        }

        return $minutes . 'min';
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('category', 'like', "%{$search}%");
        });
    }
}