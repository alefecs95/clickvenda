<?php

namespace App\Http\Controllers;

use App\Models\PayablePayment;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PayablePaymentController extends Controller
{
    /**
     * Display a listing of payable payments.
     */
    public function index(Request $request)
    {
        try {
            $query = PayablePayment::with(['supplier', 'purchaseOrder', 'serviceOrder']);

            // Filtros
            if ($request->has('supplier_id') && $request->supplier_id) {
                $query->where('supplier_id', $request->supplier_id);
            }

            if ($request->has('status') && $request->status) {
                if ($request->status === 'overdue') {
                    $query->overdue();
                } else {
                    $query->where('status', $request->status);
                }
            } else {
                // Por padrão, mostrar apenas contas pendentes e parciais
                $query->whereIn('status', ['pending', 'partial', 'overdue']);
            }

            if ($request->has('due_date_from') && $request->due_date_from) {
                $query->where('due_date', '>=', $request->due_date_from);
            }

            if ($request->has('due_date_to') && $request->due_date_to) {
                $query->where('due_date', '<=', $request->due_date_to);
            }

            if ($request->has('category') && $request->category) {
                $query->where('category', $request->category);
            }

            // Ordenação
            $query->orderBy('due_date', 'asc')
                  ->orderBy('created_at', 'desc');

            // Paginação
            $perPage = $request->get('per_page', 15);
            $payables = $query->paginate($perPage);

            // Transform the data to include supplier information
            $transformedData = $payables->getCollection()->map(function ($payable) {
                $payableArray = $payable->toArray();
                
                // Add supplier information directly to the payable object
                if ($payable->supplier) {
                    $payableArray['supplier_name'] = $payable->supplier->name;
                    $payableArray['supplier_document'] = $payable->supplier->cpf_cnpj;
                } else {
                    $payableArray['supplier_name'] = 'Fornecedor não informado';
                    $payableArray['supplier_document'] = '-';
                }
                
                return $payableArray;
            });

            return response()->json([
                'success' => true,
                'data' => $transformedData,
                'pagination' => [
                    'current_page' => $payables->currentPage(),
                    'last_page' => $payables->lastPage(),
                    'per_page' => $payables->perPage(),
                    'total' => $payables->total()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar contas a pagar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created payable payment.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'nullable|exists:suppliers,id',
            'supplier_name' => 'nullable|string|max:255',
            'supplier_document' => 'nullable|string|max:20',
            'purchase_order_id' => 'nullable|exists:orders,id',
            'service_order_id' => 'nullable|exists:service_orders,id',
            'total_payable_amount' => 'required|numeric|min:0.01',
            'due_date' => 'required|date',
            'category' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $supplierId = $request->supplier_id;
            
            // Se não foi fornecido supplier_id mas foi fornecido supplier_name, 
            // busca ou cria o fornecedor
            if (!$supplierId && $request->supplier_name) {
                $supplier = \App\Models\Supplier::firstOrCreate(
                    ['name' => $request->supplier_name],
                    [
                        'name' => $request->supplier_name,
                        'cpf_cnpj' => $request->supplier_document ?? '',
                        'email' => null,
                        'phone' => null,
                        'address' => null,
                        'city' => null,
                        'state' => null,
                        'zip_code' => null,
                        'active' => true
                    ]
                );
                $supplierId = $supplier->id;
            }
            $payable = PayablePayment::create([
                'supplier_id' => $supplierId,
                'purchase_order_id' => $request->purchase_order_id,
                'service_order_id' => $request->service_order_id,
                'total_payable_amount' => $request->total_payable_amount,
                'paid_amount' => 0,
                'remaining_amount' => $request->total_payable_amount,
                'due_date' => $request->due_date,
                'status' => 'pending',
                'category' => $request->category,
                'description' => $request->description,
                'notes' => $request->notes
            ]);

            $payable->load(['supplier', 'purchaseOrder', 'serviceOrder']);

            return response()->json([
                'success' => true,
                'message' => 'Conta a pagar criada com sucesso',
                'data' => $payable
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar conta a pagar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified payable payment.
     */
    public function show($id)
    {
        try {
            $payable = PayablePayment::with(['supplier', 'purchaseOrder', 'serviceOrder'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $payable
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Conta a pagar não encontrada',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified payable payment.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'nullable|exists:suppliers,id',
            'supplier_name' => 'nullable|string|max:255',
            'supplier_document' => 'nullable|string|max:20',
            'total_payable_amount' => 'sometimes|numeric|min:0.01',
            'due_date' => 'sometimes|date',
            'category' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:500',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $payable = PayablePayment::findOrFail($id);
            
            $supplierId = $request->supplier_id;
            
            // Se não foi fornecido supplier_id mas foi fornecido supplier_name, 
            // busca ou cria o fornecedor
            if (!$supplierId && $request->supplier_name) {
                $supplier = \App\Models\Supplier::firstOrCreate(
                    ['name' => $request->supplier_name],
                    [
                        'name' => $request->supplier_name,
                        'cpf_cnpj' => $request->supplier_document ?? '',
                        'email' => null,
                        'phone' => null,
                        'address' => null,
                        'city' => null,
                        'state' => null,
                        'zip_code' => null,
                        'active' => true
                    ]
                );
                $supplierId = $supplier->id;
            }
            
            $updateData = $request->only([
                'total_payable_amount', 'due_date', 
                'category', 'description', 'notes'
            ]);
            
            if ($supplierId) {
                $updateData['supplier_id'] = $supplierId;
            }
            
            $payable->update($updateData);

            $payable->load(['supplier', 'purchaseOrder', 'serviceOrder']);

            return response()->json([
                'success' => true,
                'message' => 'Conta a pagar atualizada com sucesso',
                'data' => $payable
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar conta a pagar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified payable payment.
     */
    public function destroy($id)
    {
        try {
            $payable = PayablePayment::findOrFail($id);
            
            // Verificar se já tem pagamentos
            if ($payable->paid_amount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível excluir uma conta que já possui pagamentos'
                ], 422);
            }

            $payable->delete();

            return response()->json([
                'success' => true,
                'message' => 'Conta a pagar excluída com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir conta a pagar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add payment to a payable.
     */
    public function addPayment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string|in:money,card,pix,transfer,check',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $payable = PayablePayment::findOrFail($id);
            
            // Validar se o valor não excede o restante
            if ($request->amount > $payable->remaining_amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'O valor do pagamento não pode ser maior que o valor restante'
                ], 422);
            }

            $payable->addPayment($request->amount, $request->method, $request->notes);
            $payable->load(['supplier', 'purchaseOrder', 'serviceOrder']);

            return response()->json([
                'success' => true,
                'message' => 'Pagamento registrado com sucesso',
                'data' => $payable
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar pagamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payables statistics.
     */
    public function statistics()
    {
        try {
            $totalPending = PayablePayment::whereIn('status', ['pending', 'partial'])->sum('remaining_amount');
            $totalOverdue = PayablePayment::overdue()->sum('remaining_amount');
            $totalPaid = PayablePayment::where('status', 'paid')->sum('total_payable_amount');
            
            $countPending = PayablePayment::where('status', 'pending')->count();
            $countOverdue = PayablePayment::overdue()->count();
            $countPaid = PayablePayment::where('status', 'paid')->count();
            $countPartial = PayablePayment::where('status', 'partial')->count();
            
            $amountPartial = PayablePayment::where('status', 'partial')->sum('remaining_amount');
            
            // Próximos vencimentos (próximos 7 dias)
            $upcomingDue = PayablePayment::whereIn('status', ['pending', 'partial'])
                ->whereBetween('due_date', [now(), now()->addDays(7)])
                ->sum('remaining_amount');

            return response()->json([
                'success' => true,
                'data' => [
                    'amount_pending' => $totalPending,
                    'amount_overdue' => $totalOverdue,
                    'amount_paid' => $totalPaid,
                    'total_pending' => $countPending,
                    'total_overdue' => $countOverdue,
                    'total_paid' => $countPaid,
                    'total_partial' => $countPartial,
                    'amount_partial' => $amountPartial,
                    'upcoming_due' => $upcomingDue
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar estatísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payables by supplier.
     */
    public function bySupplier($supplierId)
    {
        try {
            $payables = PayablePayment::with(['supplier', 'purchaseOrder', 'serviceOrder'])
                ->where('supplier_id', $supplierId)
                ->whereIn('status', ['pending', 'partial', 'overdue'])
                ->orderBy('due_date', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $payables
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar contas do fornecedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}