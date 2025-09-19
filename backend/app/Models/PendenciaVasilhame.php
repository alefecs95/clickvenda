<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendenciaVasilhame extends Model
{
    use HasFactory;

    protected $table = 'pendencia_vasilhames';

    protected $fillable = [
        'cliente_id',
        'vasilhame_model_id',
        'venda_id',
        'quantidade_pendente',
        'quantidade_necessaria',
        'quantidade_entregue',
        'observacoes',
        'resolvida',
        'data_resolucao'
    ];

    protected $casts = [
        'quantidade_pendente' => 'integer',
        'quantidade_necessaria' => 'integer',
        'quantidade_entregue' => 'integer',
        'resolvida' => 'boolean',
        'data_resolucao' => 'datetime'
    ];

    /**
     * Cliente relacionado à pendência
     */
    public function cliente()
    {
        return $this->belongsTo(Customer::class, 'cliente_id');
    }

    /**
     * Modelo de vasilhame relacionado
     */
    public function vasilhameModel()
    {
        return $this->belongsTo(VasilhameModel::class, 'vasilhame_model_id');
    }

    /**
     * Venda relacionada
     */
    public function venda()
    {
        return $this->belongsTo(Order::class, 'venda_id');
    }

    /**
     * Scope para pendências não resolvidas
     */
    public function scopeAtivas($query)
    {
        return $query->where('resolvida', false);
    }

    /**
     * Scope para pendências resolvidas
     */
    public function scopeResolvidas($query)
    {
        return $query->where('resolvida', true);
    }

    /**
     * Scope para pendências de um cliente específico
     */
    public function scopeDoCliente($query, $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    /**
     * Scope para pendências de um modelo específico
     */
    public function scopeDoModelo($query, $modeloId)
    {
        return $query->where('vasilhame_model_id', $modeloId);
    }

    /**
     * Registra uma nova pendência de vasilhame
     */
    public static function registrarPendencia(array $dados)
    {
        return self::create([
            'cliente_id' => $dados['cliente_id'],
            'vasilhame_model_id' => $dados['vasilhame_model_id'],
            'venda_id' => $dados['venda_id'] ?? null,
            'quantidade_necessaria' => $dados['quantidade_necessaria'],
            'quantidade_entregue' => $dados['quantidade_entregue'],
            'quantidade_pendente' => $dados['quantidade_necessaria'] - $dados['quantidade_entregue'],
            'observacoes' => $dados['observacoes'] ?? null,
            'resolvida' => false
        ]);
    }

    /**
     * Resolve uma pendência de vasilhame
     */
    public function resolver()
    {
        $this->update([
            'resolvida' => true,
            'data_resolucao' => now(),
            'quantidade_pendente' => 0
        ]);
    }

    /**
     * Atualiza a quantidade pendente após uma devolução parcial
     */
    public function atualizarAposDevolucao($quantidadeDevolvida)
    {
        $novaPendencia = $this->quantidade_pendente - $quantidadeDevolvida;
        
        if ($novaPendencia <= 0) {
            $this->resolver();
        } else {
            $this->update([
                'quantidade_pendente' => $novaPendencia,
                'quantidade_entregue' => $this->quantidade_entregue + $quantidadeDevolvida
            ]);
        }
    }
}