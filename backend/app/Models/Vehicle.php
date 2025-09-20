<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', // Opcional - apenas para faturamento
        'customer_name_at_time', // Nome do cliente no momento
        'customer_phone_at_time', // Telefone do cliente no momento
        'customer_email_at_time', // Email do cliente no momento
        'plate',
        'model',
        'make', // Marca (compatibilidade com frontend)
        'brand', // Marca (compatibilidade com backend)
        'year',
        'serial_number',
        'chassis_number',
        'engine_number',
        'type',
        'color',
        'fuel_type',
        'mileage',
        'notes', // Observações (compatibilidade com frontend)
        'observations', // Observações (compatibilidade com backend)
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    // Relacionamentos
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class);
    }

    // Métodos auxiliares
    public function getTypeLabel(): string
    {
        return match($this->type) {
            'veiculo' => 'Veículo',
            'equipamento' => 'Equipamento',
            'maquina' => 'Máquina',
            'outro' => 'Outro',
            default => 'Desconhecido'
        };
    }

    public function getFullIdentification(): string
    {
        $parts = [];

        if ($this->plate) {
            $parts[] = "Placa: {$this->plate}";
        }

        if (($this->make || $this->brand) && $this->model) {
            $brand = $this->make ?? $this->brand;
            $parts[] = "{$brand} {$this->model}";
        }

        if ($this->year) {
            $parts[] = "Ano: {$this->year}";
        }

        if ($this->serial_number) {
            $parts[] = "Série: {$this->serial_number}";
        }

        return implode(' - ', $parts);
    }

    public function getShortIdentification(): string
    {
        if ($this->plate) {
            return $this->plate;
        }

        if ($this->serial_number) {
            return $this->serial_number;
        }

        if ($this->model) {
            return $this->model;
        }

        return "ID: {$this->id}";
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getServiceHistory(): HasMany
    {
        return $this->serviceOrders()
            ->orderBy('opening_date', 'desc')
            ->orderBy('created_at', 'desc');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('plate', 'like', "%{$search}%")
              ->orWhere('model', 'like', "%{$search}%")
              ->orWhere('make', 'like', "%{$search}%")
              ->orWhere('brand', 'like', "%{$search}%")
              ->orWhere('serial_number', 'like', "%{$search}%")
              ->orWhere('chassis_number', 'like', "%{$search}%");
        });
    }
}