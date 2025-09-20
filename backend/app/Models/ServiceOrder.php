<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', // Número sequencial da OS (padronizado com orders)
        'vehicle_id', // VINCULAÇÃO PRINCIPAL (obrigatório)
        'customer_id', // Apenas para faturamento (opcional)
        'technical_responsible_id',
        'created_by', // user_id (padronizado com orders)
        'opening_date',
        'expected_delivery_date', // Data de previsão de entrega
        'completion_date',
        'status',
        'problem_description',
        'diagnosis',
        'internal_observations', // Observações internas
        'customer_observations',
        'total_amount', // Valor total (padronizado com orders)
        'discount_amount', // Desconto (padronizado com orders)
        'final_amount', // Valor final (padronizado com orders)
        'payment_method', // Forma de pagamento (padronizado com orders)
        'payment_methods', // Múltiplas formas de pagamento (padronizado com orders)
        'notes' // Observações gerais (padronizado com orders)
    ];

    protected $casts = [
        'opening_date' => 'date',
        'expected_delivery_date' => 'date',
        'completion_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'payment_methods' => 'array' // Padronizado com orders
    ];

    // Relacionamentos
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function technicalResponsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technical_responsible_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceOrderItem::class);
    }

    public function receivablePayment(): HasOne
    {
        return $this->hasOne(ReceivablePayment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ServiceOrderPayment::class);
    }

    public function totalPaid(): float
    {
        return $this->payments()->sum('amount');
    }

    public function remainingAmount(): float
    {
        return $this->final_amount - $this->totalPaid();
    }

    public function isFullyPaid(): bool
    {
        return $this->remainingAmount() <= 0;
    }

    // Métodos auxiliares
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'aberta' => 'Aberta',
            'em_andamento' => 'Em Andamento',
            'aguardando_aprovacao' => 'Aguardando Aprovação',
            'concluida' => 'Concluída',
            'cancelada' => 'Cancelada',
            default => 'Desconhecido'
        };
    }

    public function getPaymentMethodLabel(): string
    {
        return match($this->payment_method) {
            'money' => 'Dinheiro',
            'card' => 'Cartão',
            'pix' => 'PIX',
            'credit' => 'A Prazo (Crédito)',
            'multiple' => 'Múltiplas Formas',
            default => 'Não informado'
        };
    }

    public function isApproved(): bool
    {
        return $this->approved;
    }

    public function isCompleted(): bool
    {
        return $this->status === 'concluida';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelada';
    }

    public function canBeCompleted(): bool
    {
        return in_array($this->status, ['aberta', 'em_andamento', 'aguardando_aprovacao']);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['aberta', 'em_andamento', 'aguardando_aprovacao']);
    }

    public function calculateTotals(): void
    {
        $items = $this->items;
        $totalValue = $items->sum('total_price');
        
        $this->total_amount = $totalValue;
        $this->final_amount = $totalValue - $this->discount_amount;
        
        $this->save();
    }

    public function approve(string $notes = null): bool
    {
        if ($this->status !== 'aguardando_aprovacao') {
            return false;
        }

        $this->update([
            'approved' => true,
            'approved_at' => now(),
            'approval_notes' => $notes,
            'status' => 'em_andamento'
        ]);

        return true;
    }

    public function complete(): bool
    {
        if (!$this->canBeCompleted()) {
            return false;
        }

        $this->update([
            'status' => 'concluida',
            'completion_date' => now()
        ]);

        return true;
    }

    public function cancel(): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        // Estornar produtos do estoque
        $this->items()
            ->where('item_type', 'product')
            ->where('stock_deducted', true)
            ->each(function ($item) {
                $product = $item->product;
                if ($product) {
                    $product->updateSharedStock($item->quantity, 'increase');
                }
            });

        $this->update([
            'status' => 'cancelada'
        ]);

        return true;
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByTechnicalResponsible($query, int $userId)
    {
        return $query->where('technical_responsible_id', $userId);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'aguardando_aprovacao');
    }

    public function scopeInProgress($query)
    {
        return $query->whereIn('status', ['aberta', 'em_andamento']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'concluida');
    }
}