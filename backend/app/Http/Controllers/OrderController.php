<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\ReceivablePayment;
use App\Models\VasilhameMovimentacao;
use App\Models\PendenciaVasilhame;
use App\Models\VasilhameModel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Order::with(['customer', 'user', 'items.product']);

            // Filtros
            if ($request->filled('status')) {
                $query->where('status', $request->get('status'));
            }

            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->get('customer_id'));
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->get('date_from'));
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->get('date_to'));
            }

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhereHas('customer', function ($customerQuery) use ($search) {
                          $customerQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Ordenação
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginação
            $perPage = $request->get('per_page', 15);
            $orders = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $orders->items(),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar pedidos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Log dos dados recebidos para debug
            \Log::info('Dados recebidos para criar pedido:', $request->all());
            
            // Primeiro, converter customer_id 0 para null antes da validação
            $requestData = $request->all();
            if (isset($requestData['customer_id']) && $requestData['customer_id'] === 0) {
                $requestData['customer_id'] = null;
            }
            
            \Log::info('Dados após conversão customer_id:', $requestData);

            $validated = validator($requestData, [
                'customer_id' => 'nullable|integer|exists:customers,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'discount_amount' => 'nullable|numeric|min:0',
                'payment_method' => 'required|in:money,card,pix,credit,multiple',
                'payment_methods' => 'nullable|array',
                'payment_methods.*.method' => 'required_with:payment_methods|in:money,card,pix,credit',
                'payment_methods.*.amount' => 'required_with:payment_methods|numeric|min:0',
                'notes' => 'nullable|string|max:500',
                'status' => 'sometimes|in:pending,completed,cancelled',
                'vasilhames' => 'nullable|array',
                'vasilhames.*.modelo_id' => 'required_with:vasilhames|exists:vasilhame_models,id',
                'vasilhames.*.necessarios' => 'required_with:vasilhames|integer|min:0',
                'vasilhames.*.informados' => 'required_with:vasilhames|integer|min:0'
            ])->validate();

            return DB::transaction(function () use ($validated) {
                // Verificar estoque
                foreach ($validated['items'] as $item) {
                    $product = Product::find($item['product_id']);
                    $availableStock = $product->getActualStock();
                    if ($availableStock < $item['quantity']) {
                        throw new \Exception("Produto {$product->name} não possui estoque suficiente. Disponível: {$availableStock}");
                    }
                }

                // Gerar número do pedido
                $orderNumber = $this->generateOrderNumber();

                // Calcular totais
                $totalAmount = 0;
                foreach ($validated['items'] as $item) {
                    $totalAmount += $item['quantity'] * $item['unit_price'];
                }

                $discountAmount = $validated['discount_amount'] ?? 0;
                $finalAmount = $totalAmount - $discountAmount;

                // Criar pedido
                $order = Order::create([
                    'customer_id' => $validated['customer_id'],
                    'user_id' => Auth::user() ? Auth::user()->id : 1, // Usar o ID numérico do usuário
                    'order_number' => $orderNumber,
                    'total_amount' => $totalAmount,
                    'discount_amount' => $discountAmount,
                    'final_amount' => $finalAmount,
                    'payment_method' => $validated['payment_method'],
                    'payment_methods' => $validated['payment_methods'] ?? null,
                    'status' => $validated['status'] ?? 'pending',
                    'notes' => $validated['notes'] ?? null
                ]);

                // Criar itens do pedido e atualizar estoque
                foreach ($validated['items'] as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['quantity'] * $item['unit_price']
                    ]);

                    // Atualizar estoque considerando estoque compartilhado
                    $product = Product::find($item['product_id']);
                    $product->updateSharedStock($item['quantity'], 'decrease');
                }

                // Verificar se há valor no crédito para criar conta a receber
                $creditAmount = 0;
                if ($validated['payment_method'] === 'credit') {
                    $creditAmount = $finalAmount;
                    
                    // Validar se o cliente tem crédito suficiente
                    if ($validated['customer_id']) {
                        $customer = Customer::find($validated['customer_id']);
                        if (!$customer->hasCreditFor($creditAmount)) {
                            throw new \Exception("Cliente não possui crédito suficiente. Disponível: R$ " . number_format($customer->getAvailableCredit(), 2, ',', '.') . ", Necessário: R$ " . number_format($creditAmount, 2, ',', '.'));
                        }
                    }
                } elseif ($validated['payment_method'] === 'multiple' && isset($validated['payment_methods'])) {
                    foreach ($validated['payment_methods'] as $payment) {
                        if ($payment['method'] === 'credit') {
                            $creditAmount += $payment['amount'];
                        }
                    }
                    
                    // Validar se o cliente tem crédito suficiente para múltiplos pagamentos
                    if ($creditAmount > 0 && $validated['customer_id']) {
                        $customer = Customer::find($validated['customer_id']);
                        if (!$customer->hasCreditFor($creditAmount)) {
                            throw new \Exception("Cliente não possui crédito suficiente. Disponível: R$ " . number_format($customer->getAvailableCredit(), 2, ',', '.') . ", Necessário: R$ " . number_format($creditAmount, 2, ',', '.'));
                        }
                    }
                }

                // Se há valor no crédito, criar conta a receber
                if ($creditAmount > 0 && $validated['customer_id']) {
                    $dueDate = now()->addDays(30); // TODO: Usar configuração da loja
                    
                    ReceivablePayment::create([
                        'order_id' => $order->id,
                        'customer_id' => $validated['customer_id'],
                        'total_receivable_amount' => $creditAmount,
                        'paid_amount' => 0,
                        'remaining_amount' => $creditAmount,
                        'due_date' => $dueDate,
                        'status' => 'pending'
                    ]);
                }

                // Registrar pendências de vasilhames se houver
                if (isset($validated['vasilhames']) && !empty($validated['vasilhames']) && $validated['customer_id']) {
                    $itensVasilhame = [];
                    
                    foreach ($validated['vasilhames'] as $vasilhame) {
                        // Verificar se há pendência (necessários > informados)
                        if ($vasilhame['necessarios'] > $vasilhame['informados']) {
                            $itensVasilhame[] = [
                                'modelo_id' => $vasilhame['modelo_id'],
                                'necessario' => $vasilhame['necessarios'],
                                'entregue' => $vasilhame['informados']
                            ];
                        }
                    }
                    
                    if (!empty($itensVasilhame)) {
                        // Registrar pendências
                        VasilhameMovimentacao::registrarPendenciasVenda(
                            $order->id,
                            $validated['customer_id'],
                            $itensVasilhame
                        );
                        
                        // Registrar movimentações de saída para cada tipo de vasilhame
                        foreach ($itensVasilhame as $item) {
                            $modelo = VasilhameModel::find($item['modelo_id']);
                            
                            if ($modelo) {
                                // Registrar movimentação de saída
                                VasilhameMovimentacao::registrarMovimentacao([
                                    'cliente_id' => $validated['customer_id'],
                                    'produto_id' => $modelo->id, // Usar ID do modelo como produto temporariamente
                                    'vasilhame_model_id' => $modelo->id,
                                    'pedido_id' => $order->id,
                                    'usuario_id' => Auth::user() ? Auth::user()->id : 1,
                                    'tipo' => 'saida',
                                    'quantidade' => $item['necessario'],
                                    'observacoes' => 'Saída automática na venda #' . $order->id
                                ]);
                                
                                // Se houve entrega de vasilhames, registrar entrada
                                if ($item['entregue'] > 0) {
                                    VasilhameMovimentacao::registrarMovimentacao([
                                        'cliente_id' => $validated['customer_id'],
                                        'produto_id' => $modelo->id,
                                        'vasilhame_model_id' => $modelo->id,
                                        'pedido_id' => $order->id,
                                        'usuario_id' => Auth::user() ? Auth::user()->id : 1,
                                        'tipo' => 'entrada',
                                        'quantidade' => $item['entregue'],
                                        'observacoes' => 'Entrada na venda #' . $order->id
                                    ]);
                                }
                            }
                        }
                    }
                }

                $order->load(['customer', 'user', 'items.product', 'receivablePayment']);

                return response()->json([
                    'success' => true,
                    'message' => 'Pedido criado com sucesso',
                    'data' => $order
                ], 201);
            });
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Log do erro para debug
            \Log::error('Erro ao criar pedido: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $order = Order::with(['customer', 'user', 'items.product'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $order
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $order = Order::with('items')->findOrFail($id);

            // Só permite atualizar pedidos pendentes
            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Só é possível editar pedidos pendentes'
                ], 400);
            }

            $validated = $request->validate([
                'status' => 'sometimes|in:pending,completed,cancelled',
                'notes' => 'nullable|string|max:500'
            ]);

            $order->update($validated);

            $order->load(['customer', 'user', 'items.product']);

            return response()->json([
                'success' => true,
                'message' => 'Pedido atualizado com sucesso',
                'data' => $order
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido não encontrado'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $order = Order::with('items')->findOrFail($id);

            // Só permite cancelar pedidos pendentes
            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Só é possível cancelar pedidos pendentes'
                ], 400);
            }

            return DB::transaction(function () use ($order) {
                // Restaurar estoque considerando estoque compartilhado
                foreach ($order->items as $item) {
                    $product = Product::find($item->product_id);
                    $product->updateSharedStock($item->quantity, 'increase');
                }

                // Excluir itens do pedido
                $order->items()->delete();

                // Excluir pedido
                $order->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Pedido cancelado com sucesso'
                ]);
            });
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel order
     */
    public function cancel(string $id): JsonResponse
    {
        try {
            $order = Order::with('items')->findOrFail($id);

            if ($order->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pedido já está cancelado'
                ], 400);
            }

            return DB::transaction(function () use ($order) {
                // Restaurar estoque se o pedido estava pendente ou concluído
                if (in_array($order->status, ['pending', 'completed'])) {
                    foreach ($order->items as $item) {
                        $product = Product::find($item->product_id);
                        $product->updateSharedStock($item->quantity, 'increase');
                    }
                }

                $order->update(['status' => 'cancelled']);

                return response()->json([
                    'success' => true,
                    'message' => 'Pedido cancelado com sucesso'
                ]);
            });
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete order
     */
    public function complete(string $id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);

            if ($order->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pedido já está concluído'
                ], 400);
            }

            if ($order->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível concluir um pedido cancelado'
                ], 400);
            }

            $order->update(['status' => 'completed']);

            return response()->json([
                'success' => true,
                'message' => 'Pedido concluído com sucesso'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao concluir pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber(): string
    {
        $prefix = date('Ymd');
        $lastOrder = Order::where('order_number', 'like', $prefix . '%')
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get order statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $dateFrom = $request->get('date_from', now()->startOfMonth());
            $dateTo = $request->get('date_to', now()->endOfMonth());

            // Debug: Log dos parâmetros recebidos
            \Log::info('Statistics request parameters:', [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'all_params' => $request->all()
            ]);

            // Converter strings de data para Carbon se necessário
            if (is_string($dateFrom)) {
                $dateFrom = \Carbon\Carbon::parse($dateFrom)->startOfDay()->setTimezone('America/Sao_Paulo');
            }
            if (is_string($dateTo)) {
                $dateTo = \Carbon\Carbon::parse($dateTo)->endOfDay()->setTimezone('America/Sao_Paulo');
            }

            \Log::info('Parsed dates:', [
                'date_from_parsed' => $dateFrom,
                'date_to_parsed' => $dateTo
            ]);

            // Verificar se há pedidos no período
            $totalOrdersInPeriod = Order::whereBetween('created_at', [$dateFrom, $dateTo])->count();
            \Log::info('Total orders in period:', ['count' => $totalOrdersInPeriod]);

            $stats = [
                'total_orders' => $totalOrdersInPeriod,
                'total_revenue' => Order::whereBetween('created_at', [$dateFrom, $dateTo])
                    ->where('status', 'completed')
                    ->sum('final_amount'),
                'pending_orders' => Order::whereBetween('created_at', [$dateFrom, $dateTo])
                    ->where('status', 'pending')
                    ->count(),
                'completed_orders' => Order::whereBetween('created_at', [$dateFrom, $dateTo])
                    ->where('status', 'completed')
                    ->count(),
                'cancelled_orders' => Order::whereBetween('created_at', [$dateFrom, $dateTo])
                    ->where('status', 'cancelled')
                    ->count(),
                'average_order_value' => Order::whereBetween('created_at', [$dateFrom, $dateTo])
                    ->where('status', 'completed')
                    ->avg('final_amount') ?? 0,
            ];

            \Log::info('Statistics calculated:', $stats);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in statistics:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar estatísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
