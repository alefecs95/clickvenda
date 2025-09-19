<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'cpf_cnpj',
        'address',
        'active',
        'credit_limit',
        'credit_used',
        'credit_limit_updated_at',
        'credit_notes'
    ];

    protected $casts = [
        'active' => 'boolean',
        'credit_limit' => 'decimal:2',
        'credit_used' => 'decimal:2',
        'credit_limit_updated_at' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    
    public function vasilhameMovimentacoes()
    {
        return $this->hasMany(VasilhameMovimentacao::class, 'cliente_id');
    }
    
    /**
     * Calcula o crédito disponível
     */
    public function getAvailableCredit(): float
    {
        return $this->credit_limit - $this->credit_used;
    }
    
    /**
     * Verifica se tem crédito suficiente para um valor
     */
    public function hasCreditFor(float $amount): bool
    {
        return $this->getAvailableCredit() >= $amount;
    }
    
    /**
     * Utiliza crédito (aumenta o saldo devedor)
     */
    public function useCredit(float $amount): bool
    {
        if (!$this->hasCreditFor($amount)) {
            return false;
        }
        
        $this->increment('credit_used', $amount);
        return true;
    }
    
    /**
     * Libera crédito (diminui o saldo devedor)
     */
    public function releaseCredit(float $amount): bool
    {
        $newCreditUsed = max(0, $this->credit_used - $amount);
        $this->update(['credit_used' => $newCreditUsed]);
        return true;
    }
    
    /**
     * Atualiza o limite de crédito
     */
    public function updateCreditLimit(float $newLimit, string $notes = null): bool
    {
        $this->update([
            'credit_limit' => $newLimit,
            'credit_limit_updated_at' => now(),
            'credit_notes' => $notes
        ]);
        
        return true;
    }
    
    /**
     * Obtém informações completas do crédito
     */
    public function getCreditInfo(): array
    {
        return [
            'credit_limit' => $this->credit_limit,
            'credit_used' => $this->credit_used,
            'credit_available' => $this->getAvailableCredit(),
            'credit_percentage_used' => $this->credit_limit > 0 
                ? round(($this->credit_used / $this->credit_limit) * 100, 2)
                : 0,
            'has_credit_limit' => $this->credit_limit > 0,
            'credit_status' => $this->getCreditStatus(),
            'credit_notes' => $this->credit_notes,
            'credit_limit_updated_at' => $this->credit_limit_updated_at
        ];
    }
    
    /**
     * Obtém o status do crédito
     */
    public function getCreditStatus(): string
    {
        if ($this->credit_limit <= 0) {
            return 'sem_limite';
        }
        
        $percentage = ($this->credit_used / $this->credit_limit) * 100;
        
        if ($percentage >= 100) {
            return 'limite_excedido';
        } elseif ($percentage >= 90) {
            return 'limite_critico';
        } elseif ($percentage >= 70) {
            return 'limite_alto';
        } elseif ($percentage >= 50) {
            return 'limite_medio';
        } else {
            return 'limite_baixo';
        }
    }
    
    /**
     * Scope para clientes com limite de crédito
     */
    public function scopeWithCreditLimit($query)
    {
        return $query->where('credit_limit', '>', 0);
    }
    
    /**
     * Scope para clientes com crédito disponível
     */
    public function scopeWithAvailableCredit($query)
    {
        return $query->whereRaw('credit_limit > credit_used');
    }
}
