<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ReceivablePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'customer_id',
        'total_receivable_amount',
        'paid_amount',
        'remaining_amount',
        'due_date',
        'status',
        'payment_history',
        'notes'
    ];

    protected $casts = [
        'total_receivable_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'due_date' => 'date',
        'payment_history' => 'array',
    ];

    // Relacionamentos
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Métodos
    public function addPayment(float $amount, string $method = 'money', string $notes = null): bool
    {
        if ($amount <= 0 || $amount > $this->remaining_amount) {
            return false;
        }

        $this->paid_amount += $amount;
        $this->remaining_amount -= $amount;

        // Adicionar ao histórico
        $history = $this->payment_history ?? [];
        $history[] = [
            'amount' => $amount,
            'method' => $method,
            'date' => now()->toISOString(),
            'notes' => $notes
        ];
        $this->payment_history = $history;

        // Atualizar status
        if ($this->remaining_amount <= 0.01) {
            $this->status = 'paid';
        } else {
            $this->status = 'partial';
        }

        return $this->save();
    }

    public function isOverdue(): bool
    {
        return $this->due_date->isPast() && in_array($this->status, ['pending', 'partial']);
    }

    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return $this->due_date->diffInDays(now());
    }

    public function getPaymentPercentage(): float
    {
        if ($this->total_receivable_amount <= 0) {
            return 0;
        }
        return ($this->paid_amount / $this->total_receivable_amount) * 100;
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereIn('status', ['pending', 'partial']);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }
}