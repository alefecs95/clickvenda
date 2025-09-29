<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PayablePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'purchase_order_id',
        'service_order_id',
        'total_payable_amount',
        'paid_amount',
        'remaining_amount',
        'due_date',
        'status',
        'payment_history',
        'notes',
        'category',
        'description'
    ];

    protected $casts = [
        'total_payable_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'due_date' => 'date',
        'payment_history' => 'array'
    ];

    // Relacionamentos
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(Order::class, 'purchase_order_id'); // Usando Order para compras
    }
    
    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class);
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

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
                    ->orWhere(function($q) {
                        $q->whereIn('status', ['pending', 'partial'])
                          ->where('due_date', '<', now());
                    });
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    // Mutators e Accessors
    public function setTotalPayableAmountAttribute($value)
    {
        $this->attributes['total_payable_amount'] = $value;
        $this->updateRemainingAmount();
    }

    public function setPaidAmountAttribute($value)
    {
        $this->attributes['paid_amount'] = $value;
        $this->updateRemainingAmount();
        $this->updateStatus();
    }

    // Métodos auxiliares
    private function updateRemainingAmount()
    {
        if (isset($this->attributes['total_payable_amount']) && isset($this->attributes['paid_amount'])) {
            $this->attributes['remaining_amount'] = $this->attributes['total_payable_amount'] - $this->attributes['paid_amount'];
        }
    }

    private function updateStatus()
    {
        if (!isset($this->attributes['paid_amount']) || !isset($this->attributes['total_payable_amount'])) {
            return;
        }

        $paidAmount = $this->attributes['paid_amount'];
        $totalAmount = $this->attributes['total_payable_amount'];

        if ($paidAmount >= $totalAmount) {
            $this->attributes['status'] = 'paid';
        } elseif ($paidAmount > 0) {
            $this->attributes['status'] = 'partial';
        } else {
            // Verificar se está vencida
            $dueDate = isset($this->attributes['due_date']) ? Carbon::parse($this->attributes['due_date']) : null;
            if ($dueDate && $dueDate->isPast()) {
                $this->attributes['status'] = 'overdue';
            } else {
                $this->attributes['status'] = 'pending';
            }
        }
    }

    public function addPayment($amount, $method = 'money', $notes = null)
    {
        $paymentHistory = $this->payment_history ?? [];
        
        $paymentHistory[] = [
            'amount' => $amount,
            'method' => $method,
            'date' => now()->toDateTimeString(),
            'notes' => $notes
        ];

        $this->payment_history = $paymentHistory;
        $this->paid_amount = ($this->paid_amount ?? 0) + $amount;
        
        $this->save();
        
        return $this;
    }

    public function getIsOverdueAttribute()
    {
        if (in_array($this->status, ['paid'])) {
            return false;
        }
        
        return $this->due_date && Carbon::parse($this->due_date)->isPast();
    }

    public function getPaymentPercentageAttribute()
    {
        if ($this->total_payable_amount <= 0) {
            return 0;
        }
        
        return ($this->paid_amount / $this->total_payable_amount) * 100;
    }
}