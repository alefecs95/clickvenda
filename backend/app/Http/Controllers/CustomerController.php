<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Customer::query();

            // Filtros
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('cpf_cnpj', 'like', "%{$search}%");
                });
            }

            if ($request->has('active')) {
                $query->where('active', $request->boolean('active'));
            }

            // Ordenação
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginação
            $perPage = $request->get('per_page', 15);
            $customers = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $customers->items(),
                'pagination' => [
                    'current_page' => $customers->currentPage(),
                    'last_page' => $customers->lastPage(),
                    'per_page' => $customers->perPage(),
                    'total' => $customers->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar clientes',
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
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255|unique:customers,email',
                'phone' => 'nullable|string|max:20',
                'cpf_cnpj' => 'nullable|string|max:18|unique:customers,cpf_cnpj',
                'address' => 'nullable|string|max:500',
                'active' => 'boolean',
                'credit_limit' => 'nullable|numeric|min:0',
                'credit_notes' => 'nullable|string|max:1000'
            ]);

            $customer = Customer::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Cliente criado com sucesso',
                'data' => $customer
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar cliente',
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
            $customer = Customer::with(['orders' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(5);
            }])->findOrFail($id);
            
            // Adicionar informações de crédito
            $customerArray = $customer->toArray();
            $customerArray['credit_info'] = $customer->getCreditInfo();

            return response()->json([
                'success' => true,
                'data' => $customerArray
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar cliente',
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
            $customer = Customer::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'nullable|email|max:255|unique:customers,email,' . $id,
                'phone' => 'nullable|string|max:20',
                'cpf_cnpj' => 'nullable|string|max:18|unique:customers,cpf_cnpj,' . $id,
                'address' => 'nullable|string|max:500',
                'active' => 'boolean',
                'credit_limit' => 'nullable|numeric|min:0',
                'credit_notes' => 'nullable|string|max:1000'
            ]);

            $customer->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Cliente atualizado com sucesso',
                'data' => $customer
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente não encontrado'
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
                'message' => 'Erro ao atualizar cliente',
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
            $customer = Customer::findOrFail($id);

            // Verificar se o cliente tem pedidos
            if ($customer->orders()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível excluir um cliente que possui pedidos'
                ], 400);
            }

            $customer->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cliente excluído com sucesso'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search customers by name, email, phone or CPF/CNPJ
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'q' => 'required|string|min:2'
            ]);

            $query = $request->get('q');
            $customers = Customer::where('active', true)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%")
                      ->orWhere('phone', 'like', "%{$query}%")
                      ->orWhere('cpf_cnpj', 'like', "%{$query}%");
                })
                ->limit(10)
                ->get(['id', 'name', 'email', 'phone', 'cpf_cnpj']);

            return response()->json([
                'success' => true,
                'data' => $customers
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Termo de busca inválido',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro na busca',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update customer credit limit
     */
    public function updateCreditLimit(Request $request, string $id): JsonResponse
    {
        try {
            $customer = Customer::findOrFail($id);
            
            $validated = $request->validate([
                'credit_limit' => 'required|numeric|min:0',
                'credit_notes' => 'nullable|string|max:1000'
            ]);
            
            $customer->updateCreditLimit(
                $validated['credit_limit'], 
                $validated['credit_notes'] ?? null
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Limite de crédito atualizado com sucesso',
                'data' => [
                    'customer' => $customer->fresh(),
                    'credit_info' => $customer->getCreditInfo()
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente não encontrado'
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
                'message' => 'Erro ao atualizar limite de crédito',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get customer credit information
     */
    public function getCreditInfo(string $id): JsonResponse
    {
        try {
            $customer = Customer::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $customer->getCreditInfo()
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar informações de crédito',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get customers with credit limit
     */
    public function withCreditLimit(Request $request): JsonResponse
    {
        try {
            $customers = Customer::withCreditLimit()
                ->with('orders')
                ->get()
                ->map(function ($customer) {
                    $customerArray = $customer->toArray();
                    $customerArray['credit_info'] = $customer->getCreditInfo();
                    return $customerArray;
                });
            
            return response()->json([
                'success' => true,
                'data' => $customers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar clientes com limite de crédito',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Manually adjust customer credit (for payments/adjustments)
     */
    public function adjustCredit(Request $request, string $id): JsonResponse
    {
        try {
            $customer = Customer::findOrFail($id);
            
            $validated = $request->validate([
                'amount' => 'required|numeric',
                'type' => 'required|in:payment,adjustment,charge',
                'notes' => 'nullable|string|max:500'
            ]);
            
            $amount = abs($validated['amount']);
            $success = false;
            
            switch ($validated['type']) {
                case 'payment':
                    // Pagamento - diminui saldo devedor
                    $success = $customer->releaseCredit($amount);
                    break;
                case 'adjustment':
                    // Ajuste - pode ser positivo ou negativo
                    if ($validated['amount'] >= 0) {
                        $success = $customer->releaseCredit($amount);
                    } else {
                        $success = $customer->useCredit($amount);
                    }
                    break;
                case 'charge':
                    // Cobrança - aumenta saldo devedor
                    $success = $customer->useCredit($amount);
                    break;
            }
            
            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível realizar o ajuste de crédito'
                ], 400);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Crédito ajustado com sucesso',
                'data' => [
                    'customer' => $customer->fresh(),
                    'credit_info' => $customer->getCreditInfo()
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente não encontrado'
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
                'message' => 'Erro ao ajustar crédito',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
