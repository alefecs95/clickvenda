<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use App\Models\ServiceOrderPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ServiceOrderPaymentController extends Controller
{
    /**
     * Listar pagamentos de uma OS
     */
    public function index(Request $request, $serviceOrderId)
    {
        $serviceOrder = ServiceOrder::with(['payments.user'])->findOrFail($serviceOrderId);
        
        return response()->json([
            'service_order' => $serviceOrder,
            'payments' => $serviceOrder->payments,
            'total_paid' => $serviceOrder->totalPaid(),
            'remaining_amount' => $serviceOrder->remainingAmount(),
            'is_fully_paid' => $serviceOrder->isFullyPaid()
        ]);
    }

    /**
     * Registrar novo pagamento
     */
    public function store(Request $request, $serviceOrderId)
    {
        $serviceOrder = ServiceOrder::findOrFail($serviceOrderId);
        
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => [
                'required',
                Rule::in(['money', 'card', 'pix', 'credit'])
            ],
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'paid_at' => 'nullable|date'
        ]);

        // Verificar se o valor não excede o valor restante
        $remainingAmount = $serviceOrder->remainingAmount();
        if ($request->amount > $remainingAmount) {
            return response()->json([
                'message' => "O valor do pagamento (R$ " . number_format($request->amount, 2, ',', '.') . 
                           ") não pode ser maior que o valor restante (R$ " . number_format($remainingAmount, 2, ',', '.') . ")"
            ], 422);
        }

        DB::beginTransaction();
        
        try {
            $payment = ServiceOrderPayment::create([
                'service_order_id' => $serviceOrder->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_reference,
                'notes' => $request->notes,
                'user_id' => Auth::user()->id,
                'paid_at' => $request->paid_at ?? now()
            ]);

            // Atualizar status da OS se totalmente paga
            if ($serviceOrder->isFullyPaid()) {
                $serviceOrder->update(['status' => 'concluida']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Pagamento registrado com sucesso!',
                'payment' => $payment->load('user'),
                'service_order' => $serviceOrder->fresh(['payments'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Erro ao registrar pagamento da OS: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Erro ao registrar pagamento. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Atualizar pagamento
     */
    public function update(Request $request, $serviceOrderId, $paymentId)
    {
        $serviceOrder = ServiceOrder::findOrFail($serviceOrderId);
        $payment = $serviceOrder->payments()->findOrFail($paymentId);
        
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => [
                'required',
                Rule::in(['money', 'card', 'pix', 'credit'])
            ],
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'paid_at' => 'nullable|date'
        ]);

        // Verificar se o valor não excede o valor restante + valor atual do pagamento
        $remainingAmount = $serviceOrder->remainingAmount() + $payment->amount;
        if ($request->amount > $remainingAmount) {
            return response()->json([
                'message' => "O valor do pagamento (R$ " . number_format($request->amount, 2, ',', '.') . 
                           ") não pode ser maior que o valor restante (R$ " . number_format($remainingAmount, 2, ',', '.') . ")"
            ], 422);
        }

        DB::beginTransaction();
        
        try {
            $payment->update([
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_reference,
                'notes' => $request->notes,
                'paid_at' => $request->paid_at ?? $payment->paid_at
            ]);

            // Atualizar status da OS se totalmente paga
            if ($serviceOrder->isFullyPaid()) {
                $serviceOrder->update(['status' => 'concluida']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Pagamento atualizado com sucesso!',
                'payment' => $payment->fresh(['user']),
                'service_order' => $serviceOrder->fresh(['payments'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Erro ao atualizar pagamento da OS: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Erro ao atualizar pagamento. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Excluir pagamento
     */
    public function destroy($serviceOrderId, $paymentId)
    {
        $serviceOrder = ServiceOrder::findOrFail($serviceOrderId);
        $payment = $serviceOrder->payments()->findOrFail($paymentId);
        
        DB::beginTransaction();
        
        try {
            $payment->delete();

            // Reverter status da OS se não estiver mais totalmente paga
            if (!$serviceOrder->isFullyPaid() && $serviceOrder->status === 'concluida') {
                $serviceOrder->update(['status' => 'em_andamento']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Pagamento excluído com sucesso!',
                'service_order' => $serviceOrder->fresh(['payments'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Erro ao excluir pagamento da OS: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Erro ao excluir pagamento. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Relatórios de pagamentos
     */
    public function reports(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'payment_method' => 'nullable|string',
            'user_id' => 'nullable|integer|exists:users,id'
        ]);

        $query = ServiceOrderPayment::with(['serviceOrder', 'user']);

        // Filtros
        if ($request->start_date && $request->end_date) {
            $query->byPeriod($request->start_date, $request->end_date);
        }

        if ($request->payment_method) {
            $query->byPaymentMethod($request->payment_method);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $payments = $query->orderBy('paid_at', 'desc')->get();

        // Estatísticas
        $totalByMethod = $payments->groupBy('payment_method')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total' => $group->sum('amount')
            ];
        });

        $totalByUser = $payments->groupBy('user_id')->map(function ($group) {
            return [
                'user_name' => $group->first()->user->name,
                'count' => $group->count(),
                'total' => $group->sum('amount')
            ];
        });

        return response()->json([
            'payments' => $payments,
            'statistics' => [
                'total_payments' => $payments->count(),
                'total_amount' => $payments->sum('amount'),
                'by_method' => $totalByMethod,
                'by_user' => $totalByUser
            ]
        ]);
    }
}