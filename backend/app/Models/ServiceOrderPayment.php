<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrderPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_order_id',
        'amount',
        'payment_method',
        'payment_reference',
        'notes',
        'user_id',
        'paid_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime'
    ];

    /**
     * Relacionamento com ServiceOrder
     */
    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    /**
     * Relacionamento com User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obter label do método de pagamento
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'money' => 'Dinheiro',
            'card' => 'Cartão',
            'pix' => 'PIX',
            'credit' => 'A Prazo (Crédito)',
            default => $this->payment_method
        };
    }

    /**
     * Scope para filtrar por método de pagamento
     */
    public function scopeByPaymentMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope para filtrar por período
     */
    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('paid_at', [$startDate, $endDate]);
    }
}