<?php

namespace App\Http\Controllers;

use App\Models\VasilhameMovimentacao;
use App\Models\Customer;
use App\Models\Product;
use App\Models\VasilhameModel;
use App\Models\PendenciaVasilhame;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VasilhamesController extends Controller
{
    /**
     * Lista todos os clientes com seus débitos de vasilhames
     */
    public function index(Request $request)
    {
        $filtro = $request->input('filtro', 'todos');
        $busca = $request->input('busca', '');

        $query = Customer::with(['vasilhameMovimentacoes' => function($q) {
            $q->orderBy('data_movimentacao', 'desc');
        }]);

        // Aplicar filtro de busca
        if (!empty($busca)) {
            $query->where(function($q) use ($busca) {
                $q->where('name', 'like', "%$busca%")
                  ->orWhere('cpf_cnpj', 'like', "%$busca%");
            });
        }

        $clientes = $query->get()->map(function($cliente) {
            $saldo = VasilhameMovimentacao::saldoCliente($cliente->id);
            $saldoPorModelos = VasilhameMovimentacao::saldoClientePorModelos($cliente->id);
            $ultimaMovimentacao = $cliente->vasilhameMovimentacoes->first();

            // Filtrar apenas modelos com saldo positivo (débito)
            $debitosPorModelo = collect($saldoPorModelos)->filter(function($item) {
                return $item['saldo'] > 0;
            })->map(function($item) {
                return [
                    'modelo_id' => $item['modelo_id'],
                    'modelo_nome' => $item['modelo'] ? $item['modelo']->name : 'Modelo não encontrado',
                    'modelo_preco' => $item['modelo'] ? $item['modelo']->returnable_price : 0,
                    'quantidade_devida' => $item['saldo']
                ];
            })->values();

            return [
                'id' => $cliente->id,
                'nome' => $cliente->name,
                'documento' => $cliente->cpf_cnpj,
                'totalVasilhames' => $saldo,
                'debitosPorModelo' => $debitosPorModelo,
                'ultimaMovimentacao' => $ultimaMovimentacao ? $ultimaMovimentacao->data_movimentacao : null,
                'status' => $saldo > 0 ? 'em_debito' : 'regular'
            ];
        });

        // Aplicar filtro de status
        if ($filtro === 'com_debito') {
            $clientes = $clientes->filter(fn($cliente) => $cliente['totalVasilhames'] > 0);
        } elseif ($filtro === 'sem_debito') {
            $clientes = $clientes->filter(fn($cliente) => $cliente['totalVasilhames'] <= 0);
        }

        return response()->json([
            'clientes' => $clientes->values(),
            'estatisticas' => $this->obterEstatisticas()
        ]);
    }

    /**
     * Obtém estatísticas gerais dos vasilhames
     */
    private function obterEstatisticas()
    {
        $totalClientes = Customer::count();
        
        $clientesComDebito = Customer::get()->filter(function($cliente) {
            return VasilhameMovimentacao::saldoCliente($cliente->id) > 0;
        })->count();

        $totalVasilhames = DB::table('vasilhame_movimentacoes')
            ->selectRaw('SUM(CASE WHEN tipo = ? THEN quantidade ELSE -quantidade END) as saldo', ['saida'])
            ->value('saldo') ?? 0;

        return [
            'totalClientes' => $totalClientes,
            'clientesComDebito' => $clientesComDebito,
            'totalVasilhames' => $totalVasilhames
        ];
    }

    /**
     * Registra uma devolução de vasilhames
     */
    public function registrarDevolucao(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:customers,id',
            'quantidade' => 'required|integer|min:1',
            'observacoes' => 'nullable|string|max:500',
            'vasilhame_model_id' => 'required|exists:vasilhame_models,id'
        ]);

        try {
            DB::beginTransaction();

            $cliente = Customer::findOrFail($request->cliente_id);
            $vasilhameModel = VasilhameModel::findOrFail($request->vasilhame_model_id);
            
            // Verificar saldo atual do cliente para este modelo específico
            $saldoAtual = VasilhameMovimentacao::saldoClientePorModelo($cliente->id, $vasilhameModel->id);
            
            if ($request->quantidade > $saldoAtual) {
                return response()->json([
                    'error' => 'Quantidade excede o débito do cliente para este modelo de vasilhame'
                ], 422);
            }

            // Registrar movimentação de entrada (devolução)
            VasilhameMovimentacao::registrarMovimentacao([
                'cliente_id' => $cliente->id,
                'produto_id' => null, // Não precisa de produto para controle de vasilhames
                'vasilhame_model_id' => $vasilhameModel->id,
                'usuario_id' => Auth::user() ? Auth::user()->id : 1,
                'tipo' => 'entrada',
                'quantidade' => $request->quantidade,
                'observacoes' => $request->observacoes
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Devolução registrada com sucesso',
                'novoSaldo' => VasilhameMovimentacao::saldoCliente($cliente->id),
                'novoSaldoModelo' => VasilhameMovimentacao::saldoClientePorModelo($cliente->id, $vasilhameModel->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => 'Erro ao registrar devolução: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registra uma saída de vasilhames (venda)
     */
    public function registrarSaida(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:customers,id',
            'produto_id' => 'required|exists:products,id',
            'vasilhame_model_id' => 'nullable|exists:vasilhame_models,id',
            'quantidade' => 'required|integer|min:1',
            'pedido_id' => 'nullable|exists:orders,id',
            'observacoes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $cliente = Customer::findOrFail($request->cliente_id);
            $produto = Product::findOrFail($request->produto_id);

            if (!$produto->returnable) {
                return response()->json([
                    'error' => 'Produto não é retornável'
                ], 422);
            }

            // Registrar movimentação de saída
            VasilhameMovimentacao::registrarMovimentacao([
                'cliente_id' => $cliente->id,
                'produto_id' => $produto->id,
                'vasilhame_model_id' => $request->vasilhame_model_id,
                'pedido_id' => $request->pedido_id,
                'usuario_id' => Auth::user() ? Auth::user()->id : 1,
                'tipo' => 'saida',
                'quantidade' => $request->quantidade,
                'observacoes' => $request->observacoes
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Saída de vasilhames registrada com sucesso',
                'novoSaldo' => VasilhameMovimentacao::saldoCliente($cliente->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => 'Erro ao registrar saída: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtém o histórico de movimentações de um cliente
     */
    public function historicoCliente($clienteId)
    {
        $cliente = Customer::findOrFail($clienteId);
        
        $historico = VasilhameMovimentacao::historicoCliente($clienteId);
        
        return response()->json([
            'cliente' => $cliente,
            'historico' => $historico,
            'saldoAtual' => VasilhameMovimentacao::saldoCliente($clienteId),
            'valorDebito' => VasilhameMovimentacao::valorDebitoCliente($clienteId)
        ]);
    }

    /**
     * Obtém relatório completo de vasilhames
     */
    public function relatorio()
    {
        $estatisticas = $this->obterEstatisticas();
        
        $topClientesDebito = Customer::get()
            ->map(function($cliente) {
                return [
                    'cliente' => $cliente,
                    'saldo' => VasilhameMovimentacao::saldoCliente($cliente->id),
                    'valorDebito' => VasilhameMovimentacao::valorDebitoCliente($cliente->id)
                ];
            })
            ->filter(fn($item) => $item['saldo'] > 0)
            ->sortByDesc('valorDebito')
            ->take(10)
            ->values();

        $movimentacoesRecentes = VasilhameMovimentacao::with(['cliente', 'produto', 'usuario'])
            ->orderBy('data_movimentacao', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'estatisticas' => $estatisticas,
            'topClientesDebito' => $topClientesDebito,
            'movimentacoesRecentes' => $movimentacoesRecentes,
            'dataGeracao' => now()->toISOString()
        ]);
    }

    /**
     * Obtém produtos retornáveis disponíveis
     */
    public function produtosRetornaveis()
    {
        // Redirecionar para usar modelos de vasilhame em vez de produtos
        return $this->modelos();
    }

    /**
     * Obtém modelos de vasilhames cadastrados
     */
    public function modelos()
    {
        $modelos = VasilhameModel::available()
            ->get(['id', 'name', 'description', 'returnable_price', 'returnable_quantity', 'stock_quantity', 'minimum_stock', 'available']);

        return response()->json($modelos);
    }

    /**
     * Cria um novo modelo de vasilhame
     */
    public function criarModelo(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'returnable_price' => 'required|numeric|min:0',
            'returnable_quantity' => 'required|integer|min:1',
            'stock_quantity' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'available' => 'boolean'
        ]);

        try {
            $modelo = VasilhameModel::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Modelo criado com sucesso',
                'modelo' => $modelo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao criar modelo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualiza um modelo de vasilhame
     */
    public function atualizarModelo(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'returnable_price' => 'required|numeric|min:0',
            'returnable_quantity' => 'required|integer|min:1',
            'stock_quantity' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'available' => 'boolean'
        ]);

        try {
            $modelo = VasilhameModel::findOrFail($id);
            $modelo->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Modelo atualizado com sucesso',
                'modelo' => $modelo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao atualizar modelo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exclui um modelo de vasilhame
     */
    public function excluirModelo($id)
    {
        try {
            $modelo = VasilhameModel::findOrFail($id);
            
            // Verificar se existem produtos vinculados a este modelo
            $produtosVinculados = Product::where('vasilhame_model_id', $id)->count();
            
            if ($produtosVinculados > 0) {
                return response()->json([
                    'error' => 'Não é possível excluir o modelo pois existem produtos vinculados a ele'
                ], 422);
            }

            $modelo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Modelo excluído com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao excluir modelo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Lista todas as pendências de vasilhames
     */
    public function pendencias(Request $request)
    {
        try {
            $query = PendenciaVasilhame::with(['cliente', 'vasilhameModel', 'venda']);
            
            // Filtros
            if ($request->filled('cliente_id')) {
                $query->where('cliente_id', $request->get('cliente_id'));
            }
            
            if ($request->filled('modelo_id')) {
                $query->where('vasilhame_model_id', $request->get('modelo_id'));
            }
            
            if ($request->filled('resolvida')) {
                $query->where('resolvida', $request->get('resolvida') === 'true');
            } else {
                // Por padrão, mostrar apenas pendências não resolvidas
                $query->where('resolvida', false);
            }
            
            // Ordenação
            $query->orderBy('created_at', 'desc');
            
            $pendencias = $query->get();
            
            return response()->json([
                'success' => true,
                'data' => $pendencias
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar pendências',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Lista pendências de vasilhames por cliente
     */
    public function pendenciasPorCliente($clienteId)
    {
        try {
            $cliente = Customer::findOrFail($clienteId);
            
            $pendencias = PendenciaVasilhame::with(['vasilhameModel', 'venda'])
                ->where('cliente_id', $clienteId)
                ->where('resolvida', false)
                ->orderBy('created_at', 'desc')
                ->get();
                
            // Agrupar pendências por modelo de vasilhame
            $pendenciasPorModelo = [];
            foreach ($pendencias as $pendencia) {
                $modeloId = $pendencia->vasilhame_model_id;
                
                if (!isset($pendenciasPorModelo[$modeloId])) {
                    $pendenciasPorModelo[$modeloId] = [
                        'modelo' => $pendencia->vasilhameModel,
                        'quantidade_total' => 0,
                        'pendencias' => []
                    ];
                }
                
                $pendenciasPorModelo[$modeloId]['quantidade_total'] += $pendencia->quantidade_pendente;
                $pendenciasPorModelo[$modeloId]['pendencias'][] = $pendencia;
            }
            
            return response()->json([
                'success' => true,
                'cliente' => $cliente,
                'pendencias_por_modelo' => array_values($pendenciasPorModelo)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar pendências do cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Registra devolução de pendência específica
     */
    public function resolverPendencia(Request $request, $pendenciaId)
    {
        $request->validate([
            'quantidade' => 'required|integer|min:1',
            'observacoes' => 'nullable|string|max:500'
        ]);
        
        try {
            DB::beginTransaction();
            
            $pendencia = PendenciaVasilhame::with(['cliente', 'vasilhameModel'])->findOrFail($pendenciaId);
            
            if ($pendencia->resolvida) {
                return response()->json([
                    'error' => 'Esta pendência já foi resolvida'
                ], 422);
            }
            
            if ($request->quantidade > $pendencia->quantidade_pendente) {
                return response()->json([
                    'error' => 'Quantidade excede o débito pendente'
                ], 422);
            }
            
            // Registrar movimentação de entrada (devolução)
            VasilhameMovimentacao::registrarMovimentacao([
                'cliente_id' => $pendencia->cliente_id,
                'produto_id' => $pendencia->vasilhame_model_id, // Usar ID do modelo como produto temporariamente
                'vasilhame_model_id' => $pendencia->vasilhame_model_id,
                'pedido_id' => $pendencia->venda_id,
                'usuario_id' => Auth::user() ? Auth::user()->id : 1,
                'tipo' => 'entrada',
                'quantidade' => $request->quantidade,
                'observacoes' => $request->observacoes ?? 'Devolução de pendência #' . $pendenciaId
            ]);
            
            // Atualizar pendência
            $pendencia->atualizarAposDevolucao($request->quantidade);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Devolução registrada com sucesso',
                'pendencia' => $pendencia->fresh()
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Pendência não encontrada'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao resolver pendência',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}