<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\Product;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Order;
use App\Models\ReceivablePayment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ServiceOrderController extends Controller
{
    /**
     * Gerar dados para impressão da ordem de serviço
     */
    public function printOrder(ServiceOrder $serviceOrder): JsonResponse
    {
        try {
            // Carregar todos os relacionamentos necessários
            $serviceOrder->load([
                'customer',
                'vehicle',
                'technicalResponsible',
                'createdBy',
                'items.product',
                'items.service',
                'payments'
            ]);

            // Calcular totais
            $totalPaid = $serviceOrder->payments()->sum('amount');
            $remainingAmount = $serviceOrder->final_amount - $totalPaid;

            // Preparar dados para impressão
            $printData = [
                'order_info' => [
                    'number' => $serviceOrder->os_number,
                    'opening_date' => $serviceOrder->opening_date?->format('d/m/Y'),
                    'expected_delivery_date' => $serviceOrder->expected_delivery_date?->format('d/m/Y'),
                    'completion_date' => $serviceOrder->completion_date?->format('d/m/Y'),
                    'status' => $serviceOrder->getStatusLabel(),
                ],
                'customer' => [
                    'name' => $serviceOrder->customer?->name ?? $serviceOrder->vehicle?->customer_name_at_time ?? 'Cliente não informado',
                    'phone' => $serviceOrder->customer?->phone ?? $serviceOrder->vehicle?->customer_phone_at_time ?? '',
                    'email' => $serviceOrder->customer?->email ?? $serviceOrder->vehicle?->customer_email_at_time ?? '',
                    'document' => $serviceOrder->customer?->document ?? '',
                    'address' => $serviceOrder->customer?->address ?? '',
                    'city' => $serviceOrder->customer?->city ?? '',
                    'state' => $serviceOrder->customer?->state ?? '',
                    'zip_code' => $serviceOrder->customer?->zip_code ?? '',
                ],
                'vehicle' => [
                    'plate' => $serviceOrder->vehicle?->plate ?? '',
                    'model' => $serviceOrder->vehicle?->model ?? '',
                    'brand' => $serviceOrder->vehicle?->brand ?? $serviceOrder->vehicle?->make ?? '',
                    'year' => $serviceOrder->vehicle?->year ?? '',
                    'color' => $serviceOrder->vehicle?->color ?? '',
                    'mileage' => $serviceOrder->vehicle_mileage ?? $serviceOrder->vehicle?->mileage ?? '',
                    'chassis' => $serviceOrder->vehicle?->chassis_number ?? '',
                ],
                'technical_responsible' => [
                    'name' => $serviceOrder->technicalResponsible?->name ?? 'Não informado',
                ],
                'created_by' => [
                    'name' => $serviceOrder->createdBy?->name ?? 'Sistema',
                ],
                'description' => [
                    'problem' => $serviceOrder->problem_description ?? '',
                    'diagnosis' => $serviceOrder->diagnosis ?? '',
                    'internal_observations' => $serviceOrder->internal_observations ?? '',
                    'customer_observations' => $serviceOrder->customer_observations ?? '',
                    'notes' => $serviceOrder->notes ?? '',
                ],
                'items' => [
                    'products' => $serviceOrder->items->where('item_type', 'product')->map(function ($item) {
                        return [
                            'code' => $item->product?->code ?? '',
                            'description' => $item->description ?? $item->product?->name ?? '',
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'total_price' => $item->total_price,
                        ];
                    })->values(),
                    'services' => $serviceOrder->items->where('item_type', 'service')->map(function ($item) {
                        return [
                            'code' => $item->service?->code ?? '',
                            'description' => $item->description ?? $item->service?->name ?? '',
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'total_price' => $item->total_price,
                        ];
                    })->values(),
                ],
                'totals' => [
                    'total_amount' => $serviceOrder->total_amount,
                    'discount_amount' => $serviceOrder->discount_amount ?? 0,
                    'final_amount' => $serviceOrder->final_amount,
                    'total_paid' => $totalPaid,
                    'remaining_amount' => $remainingAmount,
                ],
                'payments' => $serviceOrder->payments->map(function ($payment) {
                    return [
                        'date' => $payment->paid_at?->format('d/m/Y'),
                        'method' => $payment->getPaymentMethodLabelAttribute(),
                        'amount' => $payment->amount,
                        'reference' => $payment->payment_reference ?? '',
                    ];
                })->values(),
                'warranty' => [
                    'products_days' => $serviceOrder->warranty_products_days ?? 90, // Usar valor do banco ou padrão
                    'services_days' => $serviceOrder->warranty_services_days ?? 30, // Usar valor do banco ou padrão
                    'products_km' => $serviceOrder->warranty_products_km ?? null, // Quilometragem para produtos
                    'services_km' => $serviceOrder->warranty_services_km ?? null, // Quilometragem para serviços
                ],
                'company' => [
                    'name' => \App\Models\Setting::get('store.name', 'ClickVenda'),
                    'address' => \App\Models\Setting::get('store.address', 'Endereço da empresa'),
                    'phone' => 'Telefones: ' . \App\Models\Setting::get('store.phone', 'telefone da empresa'),
                    'email' => 'E-mail: ' . \App\Models\Setting::get('store.email', 'email@empresa.com'),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $printData
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao gerar dados para impressão da OS: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar dados para impressão'
            ], 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ServiceOrder::with([
                'customer',
                'vehicle',
                'technicalResponsible',
                'createdBy',
                'items.product',
                'items.service'
            ]);

            // Filtros
            if ($request->filled('status')) {
                $query->where('status', $request->get('status'));
            }

            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->get('customer_id'));
            }

            if ($request->filled('technical_responsible_id')) {
                $query->where('technical_responsible_id', $request->get('technical_responsible_id'));
            }

            if ($request->filled('opening_date_from')) {
                $query->where('opening_date', '>=', $request->get('opening_date_from'));
            }

            if ($request->filled('opening_date_to')) {
                $query->where('opening_date', '<=', $request->get('opening_date_to'));
            }

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('os_number', 'like', "%{$search}%")
                      ->orWhere('problem_description', 'like', "%{$search}%")
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
            $serviceOrders = $query->paginate($perPage);

            // Calcular valores de pagamento para cada OS
            $serviceOrders->getCollection()->transform(function ($serviceOrder) {
                $totalPaid = $serviceOrder->payments()->sum('amount');
                $remainingAmount = $serviceOrder->final_amount - $totalPaid;
                $isFullyPaid = $remainingAmount <= 0;

                // Adicionar atributos calculados
                $serviceOrder->setAttribute('total_paid', $totalPaid);
                $serviceOrder->setAttribute('remaining_amount', $remainingAmount);
                $serviceOrder->setAttribute('is_fully_paid', $isFullyPaid);

                return $serviceOrder;
            });

            return response()->json([
                'success' => true,
                'data' => $serviceOrders
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao listar ordens de serviço: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar ordens de serviço',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Log inicial para confirmar que a requisição chegou
            \Log::info('=== REQUISIÇÃO POST RECEBIDA NO STORE ===');
            \Log::info('Method: ' . $request->method());
            \Log::info('URL: ' . $request->fullUrl());
            
            // Log dos dados recebidos
            \Log::info('=== DADOS RECEBIDOS NO BACKEND ===', $request->all());
            \Log::info('Campos de garantia recebidos:', [
                'warranty_products_km' => $request->input('warranty_products_km'),
                'warranty_services_km' => $request->input('warranty_services_km'),
                'warranty_products_days' => $request->input('warranty_products_days'),
                'warranty_services_days' => $request->input('warranty_services_days')
            ]);
            $validated = $request->validate([
                'customer_id' => 'nullable|exists:customers,id',
                'vehicle_id' => 'required|exists:vehicles,id',
                'technical_responsible_id' => 'required|exists:users,id',
                'opening_date' => 'required|date',
                'expected_delivery_date' => 'nullable|date|after_or_equal:opening_date',
                'problem_description' => 'required|string|max:1000',
                'diagnosis' => 'nullable|string|max:1000',
                'internal_observations' => 'nullable|string|max:1000',
                'customer_observations' => 'nullable|string|max:1000',
                'payment_method' => 'nullable|in:money,card,pix,credit,multiple',
                'payment_methods' => 'nullable|array',
                'billing_type' => 'nullable|in:avista,aprazo,orcamento',
                'notes' => 'nullable|string|max:1000',
                'warranty_products_days' => 'nullable|integer|min:0',
                'warranty_services_days' => 'nullable|integer|min:0',
                'warranty_products_km' => 'nullable|integer|min:0',
                'warranty_services_km' => 'nullable|integer|min:0',
                'vehicle_mileage' => 'nullable|integer|min:0',
                'items' => 'required|array|min:1',
                'items.*.item_type' => 'required|in:product,service',
                'items.*.product_id' => 'required_if:items.*.item_type,product|nullable|exists:products,id',
                'items.*.service_id' => 'required_if:items.*.item_type,service|nullable|exists:services,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.observations' => 'nullable|string|max:500',
                'discount_amount' => 'nullable|numeric|min:0'
            ]);

            return DB::transaction(function () use ($validated) {
                // Verificar estoque para produtos
                foreach ($validated['items'] as $item) {
                    if ($item['item_type'] === 'product') {
                        $product = Product::find($item['product_id']);
                        $availableStock = $product->getActualStock();
                        if ($availableStock < $item['quantity']) {
                            throw new \Exception("Produto {$product->name} não possui estoque suficiente. Disponível: {$availableStock}");
                        }
                    }
                }

                // Gerar número da OS
                $osNumber = $this->generateOSNumber();

                // Criar OS
            // Verificar crédito do cliente se for OS a prazo (não aplicável para orçamentos)
            if (isset($validated['payment_method']) && $validated['payment_method'] === 'credit' && $validated['customer_id'] && ($validated['billing_type'] ?? 'avista') !== 'orcamento') {
                $customer = Customer::find($validated['customer_id']);
                if (!$customer) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cliente não encontrado'
                    ], 404);
                }
                
                // Calcular valor total dos itens
                $totalValue = 0;
                foreach ($validated['items'] as $item) {
                    $totalValue += $item['quantity'] * $item['unit_price'];
                }
                $totalValue -= $validated['discount_amount'] ?? 0;
                
                // Verificar se cliente tem crédito suficiente
                if (!$customer->hasCreditFor($totalValue)) {
                    $availableCredit = $customer->getAvailableCredit();
                    return response()->json([
                        'success' => false,
                        'message' => "Cliente não possui crédito suficiente. Crédito disponível: R$ " . number_format($availableCredit, 2, ',', '.') . ". Valor necessário: R$ " . number_format($totalValue, 2, ',', '.')
                    ], 422);
                }
            }

            $serviceOrder = ServiceOrder::create([
                'order_number' => $osNumber, // Número da OS
                'customer_id' => $validated['customer_id'] ?? null,
                'vehicle_id' => $validated['vehicle_id'],
                'technical_responsible_id' => $validated['technical_responsible_id'],
                'created_by' => Auth::user()->id, // user_id padronizado
                'opening_date' => $validated['opening_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'problem_description' => $validated['problem_description'],
                'diagnosis' => $validated['diagnosis'] ?? null,
                'internal_observations' => $validated['internal_observations'] ?? null,
                'customer_observations' => $validated['customer_observations'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null, // Removido valor padrão
                'payment_methods' => $validated['payment_methods'] ?? null, // Padronizado com orders
                'billing_type' => $validated['billing_type'] ?? 'avista', // Padrão à vista
                'notes' => $validated['notes'] ?? null, // Padronizado com orders
                'warranty_products_days' => $validated['warranty_products_days'] ?? null,
                'warranty_services_days' => $validated['warranty_services_days'] ?? null,
                'warranty_products_km' => $validated['warranty_products_km'] ?? null,
                'warranty_services_km' => $validated['warranty_services_km'] ?? null,
                'vehicle_mileage' => $validated['vehicle_mileage'] ?? null,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'status' => 'aberta' // Simplificado - sem billing_type
            ]);

                // Criar itens e atualizar estoque
                foreach ($validated['items'] as $item) {
                    $itemData = [
                        'service_order_id' => $serviceOrder->id,
                        'item_type' => $item['item_type'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['quantity'] * $item['unit_price'],
                        'observations' => $item['observations'] ?? null
                    ];

                    if ($item['item_type'] === 'product') {
                        $itemData['product_id'] = $item['product_id'];
                        $product = Product::find($item['product_id']);
                        $itemData['description'] = $product->name;
                        
                        // Remover do estoque
                        $product->updateSharedStock($item['quantity'], 'decrease');
                        $itemData['stock_removed'] = true;
                        $itemData['stock_removed_at'] = now();
                    } else {
                        $itemData['service_id'] = $item['service_id'];
                        $service = Service::find($item['service_id']);
                        $itemData['description'] = $service->name;
                    }

                    ServiceOrderItem::create($itemData);
                }

                // Calcular totais
                $serviceOrder->calculateTotals();

                // Criar ReceivablePayment se for OS a prazo (não aplicável para orçamentos)
                if (isset($validated['payment_method']) && $validated['payment_method'] === 'credit' && $validated['customer_id'] && ($validated['billing_type'] ?? 'avista') !== 'orcamento') {
                    $customer = Customer::find($validated['customer_id']);
                    
                    // Usar crédito do cliente
                    $customer->useCredit($serviceOrder->final_amount);
                    
                    // Obter prazo padrão das configurações
                    $defaultPaymentTerm = \DB::table('settings')
                        ->where('key', 'sales.default_payment_term')
                        ->value('value') ?? 30;
                    
                    // Criar conta a receber
                    ReceivablePayment::create([
                        'service_order_id' => $serviceOrder->id,
                        'customer_id' => $validated['customer_id'],
                        'total_receivable_amount' => $serviceOrder->final_amount,
                        'paid_amount' => 0,
                        'remaining_amount' => $serviceOrder->final_amount,
                        'due_date' => $validated['expected_delivery_date'] ?? now()->addDays($defaultPaymentTerm),
                        'status' => 'pending',
                        'notes' => "OS #{$serviceOrder->os_number} - {$serviceOrder->problem_description}"
                    ]);
                }

                // Carregar relacionamentos
                $serviceOrder->load([
                    'customer',
                    'vehicle',
                    'technicalResponsible',
                    'createdBy',
                    'items.product',
                    'items.service'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Ordem de serviço criada com sucesso',
                    'data' => $serviceOrder
                ], 201);

            });

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erro de validação ao criar OS:', $e->errors());
            \Log::error('Dados recebidos que falharam na validação:', $request->all());
            
            $errors = $e->errors();
            $message = 'Dados inválidos';
            
            // Mensagem específica para erro de data
            if (isset($errors['expected_delivery_date'])) {
                $message = 'A data de previsão de entrega deve ser maior ou igual à data de abertura';
            }
            
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $errors
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Erro ao criar ordem de serviço: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar ordem de serviço',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceOrder $serviceOrder): JsonResponse
    {
        try {
            $serviceOrder->load([
                'customer',
                'vehicle',
                'technicalResponsible',
                'createdBy',
                'items.product',
                'items.service',
                'order',
                'receivablePayment',
                'payments.user'
            ]);

            // Calcular valores de pagamento
            $totalPaid = $serviceOrder->payments()->sum('amount');
            $remainingAmount = $serviceOrder->final_amount - $totalPaid;
            $isFullyPaid = $remainingAmount <= 0;

            // Adicionar atributos calculados
            $serviceOrder->setAttribute('total_paid', $totalPaid);
            $serviceOrder->setAttribute('remaining_amount', $remainingAmount);
            $serviceOrder->setAttribute('is_fully_paid', $isFullyPaid);

            return response()->json([
                'success' => true,
                'data' => $serviceOrder
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar ordem de serviço: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar ordem de serviço',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServiceOrder $serviceOrder): JsonResponse
    {
        try {
            \Log::info('Dados recebidos para atualizar OS:', $request->all());
            \Log::info('Campos de garantia recebidos:', [
                'warranty_products_km' => $request->warranty_products_km,
                'warranty_services_km' => $request->warranty_services_km,
                'warranty_products_days' => $request->warranty_products_days,
                'warranty_services_days' => $request->warranty_services_days
            ]);
            $validated = $request->validate([
                'vehicle_id' => 'nullable|exists:vehicles,id',
                'technical_responsible_id' => 'nullable|exists:users,id',
                'expected_delivery_date' => 'nullable|date|after_or_equal:opening_date',
                'problem_description' => 'nullable|string|max:1000',
                'diagnosis' => 'nullable|string|max:1000',
                'internal_observations' => 'nullable|string|max:1000',
                'customer_observations' => 'nullable|string|max:1000',
                'discount_amount' => 'nullable|numeric|min:0',
                'attachments' => 'nullable|array',
                'warranty_products_days' => 'nullable|integer|min:0',
                'warranty_services_days' => 'nullable|integer|min:0',
                'warranty_products_km' => 'nullable|integer|min:0',
                'warranty_services_km' => 'nullable|integer|min:0'
            ]);

            $serviceOrder->update($validated);
            $serviceOrder->calculateTotals();

            $serviceOrder->load([
                'customer',
                'vehicle',
                'technicalResponsible',
                'createdBy',
                'items.product',
                'items.service'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ordem de serviço atualizada com sucesso',
                'data' => $serviceOrder
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar ordem de serviço: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar ordem de serviço',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceOrder $serviceOrder): JsonResponse
    {
        try {
            if (!$serviceOrder->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta ordem de serviço não pode ser cancelada'
                ], 400);
            }

            $serviceOrder->cancel();

            return response()->json([
                'success' => true,
                'message' => 'Ordem de serviço cancelada com sucesso'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao cancelar ordem de serviço: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar ordem de serviço',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aprovar ordem de serviço
     */
    public function approve(Request $request, ServiceOrder $serviceOrder): JsonResponse
    {
        try {
            $validated = $request->validate([
                'approval_notes' => 'nullable|string|max:500'
            ]);

            if ($serviceOrder->approve($validated['approval_notes'] ?? null)) {
                $serviceOrder->load([
                    'customer',
                    'vehicle',
                    'technicalResponsible',
                    'createdBy',
                    'items.product',
                    'items.service'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Ordem de serviço aprovada com sucesso',
                    'data' => $serviceOrder
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Esta ordem de serviço não pode ser aprovada'
            ], 400);

        } catch (\Exception $e) {
            \Log::error('Erro ao aprovar ordem de serviço: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao aprovar ordem de serviço',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Concluir ordem de serviço
     */
    public function complete(ServiceOrder $serviceOrder): JsonResponse
    {
        try {
            if ($serviceOrder->complete()) {
                $serviceOrder->load([
                    'customer',
                    'vehicle',
                    'technicalResponsible',
                    'createdBy',
                    'items.product',
                    'items.service'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Ordem de serviço concluída com sucesso',
                    'data' => $serviceOrder
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Esta ordem de serviço não pode ser concluída'
            ], 400);

        } catch (\Exception $e) {
            \Log::error('Erro ao concluir ordem de serviço: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao concluir ordem de serviço',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aprovar orçamento pelo cliente
     */
    public function approveCustomer(Request $request, ServiceOrder $serviceOrder): JsonResponse
    {
        try {
            $validated = $request->validate([
                'approval_notes' => 'nullable|string|max:500'
            ]);

            if ($serviceOrder->approveByCustomer($validated['approval_notes'] ?? null)) {
                $serviceOrder->load([
                    'customer',
                    'vehicle',
                    'technicalResponsible',
                    'createdBy',
                    'items.product',
                    'items.service'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Orçamento aprovado pelo cliente com sucesso',
                    'data' => $serviceOrder
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Este orçamento não pode ser aprovado pelo cliente'
            ], 400);

        } catch (\Exception $e) {
            \Log::error('Erro ao aprovar orçamento pelo cliente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao aprovar orçamento pelo cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Estatísticas das OS
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total' => ServiceOrder::count(),
                'abertas' => ServiceOrder::where('status', 'aberta')->count(),
                'em_andamento' => ServiceOrder::where('status', 'em_andamento')->count(),
                'aguardando_aprovacao' => ServiceOrder::where('status', 'aguardando_aprovacao')->count(),
                'concluidas' => ServiceOrder::where('status', 'concluida')->count(),
                'canceladas' => ServiceOrder::where('status', 'cancelada')->count(),
                'receita_total' => ServiceOrder::where('status', 'concluida')->sum('final_amount'),
                'receita_mes' => ServiceOrder::where('status', 'concluida')
                    ->whereMonth('completion_date', now()->month)
                    ->whereYear('completion_date', now()->year)
                    ->sum('final_amount')
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar estatísticas das OS: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar estatísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gerar número da OS
     */
    private function generateOSNumber(): string
    {
        $lastOS = ServiceOrder::orderBy('id', 'desc')->first();
        $nextNumber = $lastOS ? $lastOS->id + 1 : 1;
        return 'OS' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Gerar número do pedido
     */
    private function generateOrderNumber(): string
    {
        $lastOrder = Order::orderBy('id', 'desc')->first();
        $nextNumber = $lastOrder ? $lastOrder->id + 1 : 1;
        return 'PED' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Calcular valor no crédito
     */
    private function calculateCreditAmount(array $paymentData): float
    {
        if ($paymentData['payment_method'] === 'credit') {
            return $paymentData['final_amount'] ?? 0;
        }

        if ($paymentData['payment_method'] === 'multiple' && isset($paymentData['payment_methods'])) {
            $creditAmount = 0;
            foreach ($paymentData['payment_methods'] as $payment) {
                if ($payment['method'] === 'credit') {
                    $creditAmount += $payment['amount'];
                }
            }
            return $creditAmount;
        }

        return 0;
    }
}