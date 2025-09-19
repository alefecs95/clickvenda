<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class VasilhameMovimentacao extends Model
{
    use HasFactory;

    protected $table = 'vasilhame_movimentacoes';

    protected $fillable = [
        'cliente_id',
        'produto_id',
        'vasilhame_model_id',
        'pedido_id',
        'usuario_id',
        'tipo',
        'quantidade',
        'observacoes',
        'data_movimentacao'
    ];

    protected $casts = [
        'quantidade' => 'integer',
        'data_movimentacao' => 'datetime'
    ];

    /**
     * Cliente relacionado à movimentação
     */
    public function cliente()
    {
        return $this->belongsTo(Customer::class, 'cliente_id');
    }

    /**
     * Produto relacionado à movimentação
     */
    public function produto()
    {
        return $this->belongsTo(Product::class, 'produto_id');
    }

    /**
     * Modelo de vasilhame relacionado à movimentação
     */
    public function vasilhameModel()
    {
        return $this->belongsTo(VasilhameModel::class, 'vasilhame_model_id');
    }

    /**
     * Pedido relacionado (se aplicável)
     */
    public function pedido()
    {
        return $this->belongsTo(Order::class, 'pedido_id');
    }

    /**
     * Usuário que registrou a movimentação
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Scope para movimentações de entrada
     */
    public function scopeEntradas($query)
    {
        return $query->where('tipo', 'entrada');
    }

    /**
     * Scope para movimentações de saída
     */
    public function scopeSaidas($query)
    {
        return $query->where('tipo', 'saida');
    }

    /**
     * Scope para movimentações de um cliente específico
     */
    public function scopeDoCliente($query, $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    /**
     * Scope para movimentações de um produto específico
     */
    public function scopeDoProduto($query, $produtoId)
    {
        return $query->where('produto_id', $produtoId);
    }

    /**
     * Calcula o saldo atual de vasilhames de um cliente
     */
    public static function saldoCliente($clienteId)
    {
        $totalSaidas = self::saidas()->doCliente($clienteId)->sum('quantidade');
        $totalEntradas = self::entradas()->doCliente($clienteId)->sum('quantidade');
        
        return $totalSaidas - $totalEntradas;
    }
    
    /**
     * Calcula o saldo atual de vasilhames de um cliente por modelo
     */
    public static function saldoClientePorModelo($clienteId, $modeloId)
    {
        $totalSaidas = self::saidas()
            ->doCliente($clienteId)
            ->where('vasilhame_model_id', $modeloId)
            ->sum('quantidade');
            
        $totalEntradas = self::entradas()
            ->doCliente($clienteId)
            ->where('vasilhame_model_id', $modeloId)
            ->sum('quantidade');
            
        return $totalSaidas - $totalEntradas;
    }
    
    /**
     * Calcula o valor do débito de vasilhames de um cliente
     */
    public static function valorDebitoCliente($clienteId)
    {
        $saldoPorModelo = self::saldoClientePorModelos($clienteId);
        $valorTotal = 0;
        
        foreach ($saldoPorModelo as $saldo) {
            if ($saldo['saldo'] > 0 && $saldo['modelo']) {
                $valorTotal += $saldo['saldo'] * $saldo['modelo']->returnable_price;
            }
        }
        
        return $valorTotal;
    }
    
    /**
     * Calcula o saldo de vasilhames por modelo para um cliente
     */
    public static function saldoClientePorModelos($clienteId)
    {
        // Obter todos os modelos de vasilhame usados pelo cliente
        $modelosUsados = self::doCliente($clienteId)
            ->whereNotNull('vasilhame_model_id')
            ->distinct('vasilhame_model_id')
            ->pluck('vasilhame_model_id');
            
        $resultado = [];
        
        foreach ($modelosUsados as $modeloId) {
            $modelo = VasilhameModel::find($modeloId);
            $saldo = self::saldoClientePorModelo($clienteId, $modeloId);
            
            $resultado[] = [
                'modelo_id' => $modeloId,
                'modelo' => $modelo,
                'saldo' => $saldo
            ];
        }
        
        return $resultado;
    }


    /**
     * Obtém o histórico de movimentações de um cliente
     */
    public static function historicoCliente($clienteId, $limit = 50)
    {
        return self::with(['produto', 'usuario', 'pedido'])
            ->doCliente($clienteId)
            ->orderBy('data_movimentacao', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Registra uma nova movimentação de vasilhame
     */
    public static function registrarMovimentacao(array $dados)
    {
        $movimentacao = self::create([
            'cliente_id' => $dados['cliente_id'],
            'produto_id' => $dados['produto_id'],
            'vasilhame_model_id' => $dados['vasilhame_model_id'] ?? null,
            'pedido_id' => $dados['pedido_id'] ?? null,
            'usuario_id' => $dados['usuario_id'],
            'tipo' => $dados['tipo'],
            'quantidade' => $dados['quantidade'],
            'observacoes' => $dados['observacoes'] ?? null,
            'data_movimentacao' => $dados['data_movimentacao'] ?? now()
        ]);
        
        // Se for uma entrada (devolução), verificar se há pendências para atualizar
        if ($dados['tipo'] === 'entrada' && isset($dados['vasilhame_model_id'])) {
            self::atualizarPendenciasAposDevolucao(
                $dados['cliente_id'],
                $dados['vasilhame_model_id'],
                $dados['quantidade']
            );
        }
        
        return $movimentacao;
    }
    
    /**
     * Atualiza pendências após uma devolução de vasilhames
     */
    public static function atualizarPendenciasAposDevolucao($clienteId, $modeloId, $quantidadeDevolvida)
    {
        // Buscar pendências ativas deste cliente para este modelo
        $pendencias = PendenciaVasilhame::ativas()
            ->doCliente($clienteId)
            ->doModelo($modeloId)
            ->orderBy('created_at')
            ->get();
            
        $quantidadeRestante = $quantidadeDevolvida;
        
        foreach ($pendencias as $pendencia) {
            if ($quantidadeRestante <= 0) break;
            
            // Determinar quanto desta pendência pode ser resolvido
            $quantidadeParaResolver = min($quantidadeRestante, $pendencia->quantidade_pendente);
            
            // Atualizar a pendência
            $pendencia->atualizarAposDevolucao($quantidadeParaResolver);
            
            // Reduzir a quantidade restante
            $quantidadeRestante -= $quantidadeParaResolver;
        }
    }
    
    /**
     * Registra pendências de vasilhames após uma venda
     */
    public static function registrarPendenciasVenda($vendaId, $clienteId, array $itensVasilhame)
    {
        foreach ($itensVasilhame as $item) {
            // Verificar se há quantidade pendente
            if ($item['necessario'] > $item['entregue']) {
                PendenciaVasilhame::registrarPendencia([
                    'cliente_id' => $clienteId,
                    'vasilhame_model_id' => $item['modelo_id'],
                    'venda_id' => $vendaId,
                    'quantidade_necessaria' => $item['necessario'],
                    'quantidade_entregue' => $item['entregue'],
                    'observacoes' => 'Pendência gerada automaticamente na venda #' . $vendaId
                ]);
            }
        }
    }
}