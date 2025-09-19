<?php

namespace App\Http\Controllers;

use App\Models\ReceivablePayment;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ReceivablePaymentController extends Controller
{
    /**
     * Display a listing of receivable payments.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ReceivablePayment::with([
                'order.items.product', 
                'order.customer', 
                'order.user',
                'customer'
            ]);

            // Sempre filtrar apenas contas não pagas (pending, partial, overdue)
            $query->whereIn('status', ['pending', 'partial', 'overdue']);

            // Filtros
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->get('customer_id'));
            }

            if ($request->filled('due_date_from')) {
                $query->whereDate('due_date', '>=', $request->get('due_date_from'));
            }

            if ($request->filled('due_date_to')) {
                $query->whereDate('due_date', '<=', $request->get('due_date_to'));
            }

            // Ordenação: vencidas primeiro, depois por data de vencimento
            $query->orderByRaw("CASE WHEN due_date < CURDATE() THEN 0 ELSE 1 END")
                  ->orderBy('due_date', 'asc');

            $perPage = $request->get('per_page', 15);
            $receivables = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $receivables->items(),
                'pagination' => [
                    'current_page' => $receivables->currentPage(),
                    'last_page' => $receivables->lastPage(),
                    'per_page' => $receivables->perPage(),
                    'total' => $receivables->total()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar contas a receber',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display receivables by customer.
     */
    public function byCustomer(Customer $customer): JsonResponse
    {
        try {
            $receivables = ReceivablePayment::with([
                    'order.items.product', 
                    'order.customer', 
                    'order.user',
                    'customer'
                ])
                ->where('customer_id', $customer->id)
                ->orderBy('due_date', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $receivables
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar contas do cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add payment to a receivable.
     */
    public function addPayment(ReceivablePayment $receivablePayment, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'method' => 'required|in:money,card,pix',
                'notes' => 'nullable|string|max:500'
            ]);

            if ($validated['amount'] > $receivablePayment->remaining_amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Valor do pagamento não pode ser maior que o valor restante'
                ], 422);
            }

            $success = $receivablePayment->addPayment(
                $validated['amount'],
                $validated['method'],
                $validated['notes'] ?? null
            );

            if ($success) {
                // Se foi pago completamente, liberar o crédito do cliente
                if ($receivablePayment->status === 'paid') {
                    $customer = $receivablePayment->customer;
                    $customer->credit_used -= $receivablePayment->total_receivable_amount;
                    $customer->save();
                }

                $receivablePayment->load(['order', 'customer']);

                return response()->json([
                    'success' => true,
                    'message' => 'Pagamento registrado com sucesso',
                    'data' => $receivablePayment
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao registrar pagamento'
                ], 500);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar pagamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics for receivables.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_pending' => ReceivablePayment::whereIn('status', ['pending', 'partial'])->sum('remaining_amount'),
                'total_overdue' => ReceivablePayment::overdue()->sum('remaining_amount'),
                'total_paid' => ReceivablePayment::paid()->sum('total_receivable_amount'),
                'customers_at_risk' => Customer::whereRaw('(credit_used / credit_limit) * 100 >= 80')->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar estatísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}