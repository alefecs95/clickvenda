<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    /**
     * Test method to check if API is working
     */
    public function test(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'API funcionando',
            'vehicles_count' => Vehicle::count()
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            \Log::info('Buscando veículos com filtros:', $request->all());
            
            $query = Vehicle::with('customer');

            // Filtros
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->get('customer_id'));
            }

            if ($request->filled('type')) {
                $query->where('type', $request->get('type'));
            }

            if ($request->filled('active')) {
                $query->where('active', $request->boolean('active'));
            }

            if ($request->filled('search')) {
                $query->search($request->get('search'));
            }

            // Ordenação
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginação
            $perPage = $request->get('per_page', 15);
            $vehicles = $query->paginate($perPage);

            \Log::info('Veículos encontrados:', ['count' => $vehicles->count(), 'total' => $vehicles->total()]);

            return response()->json([
                'success' => true,
                'data' => $vehicles
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao listar veículos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar veículos',
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
            $validated = $request->validate([
                'customer_id' => 'nullable|integer|exists:customers,id', // Opcional - apenas para faturamento
                'customer_name_at_time' => 'nullable|string|max:255', // Nome do cliente no momento
                'customer_phone_at_time' => 'nullable|string|max:20', // Telefone do cliente no momento
                'customer_email_at_time' => 'nullable|string|max:255', // Email do cliente no momento
                'plate' => 'required|string|max:8|unique:vehicles,plate',
                'model' => 'required|string|max:100',
                'make' => 'nullable|string|max:100', // Aceita 'make' do frontend
                'brand' => 'nullable|string|max:100', // Mantém compatibilidade
                'year' => 'nullable|string|max:4',
                'serial_number' => 'nullable|string|max:100',
                'chassis_number' => 'nullable|string|max:100',
                'engine_number' => 'nullable|string|max:100',
                'type' => 'required|in:vehicle,equipment,veiculo,equipamento,maquina,outro', // Aceita ambos os formatos
                'color' => 'nullable|string|max:50',
                'fuel_type' => 'nullable|string|max:50',
                'mileage' => 'nullable|integer|min:0',
                'notes' => 'nullable|string|max:1000', // Aceita 'notes' do frontend
                'observations' => 'nullable|string|max:1000', // Mantém compatibilidade
                'active' => 'boolean'
            ]);

            // Mapear campos do frontend para o formato do banco
            $vehicleData = [
                'plate' => $validated['plate'],
                'model' => $validated['model'],
                'make' => $validated['make'] ?? $validated['brand'] ?? null, // Prioriza 'make', depois 'brand'
                'year' => $validated['year'],
                'type' => $this->mapVehicleType($validated['type']), // Mapear tipo
                'active' => $validated['active'] ?? true
            ];

            // Adicionar campos opcionais apenas se não forem nulos
            if (!empty($validated['customer_id'])) {
                $vehicleData['customer_id'] = $validated['customer_id'];
            }
            if (!empty($validated['customer_name_at_time'])) {
                $vehicleData['customer_name_at_time'] = $validated['customer_name_at_time'];
            }
            if (!empty($validated['customer_phone_at_time'])) {
                $vehicleData['customer_phone_at_time'] = $validated['customer_phone_at_time'];
            }
            if (!empty($validated['customer_email_at_time'])) {
                $vehicleData['customer_email_at_time'] = $validated['customer_email_at_time'];
            }
            if (!empty($validated['chassis_number'])) {
                $vehicleData['chassis_number'] = $validated['chassis_number'];
            }
            if (!empty($validated['engine_number'])) {
                $vehicleData['engine_number'] = $validated['engine_number'];
            }
            if (!empty($validated['color'])) {
                $vehicleData['color'] = $validated['color'];
            }
            if (!empty($validated['notes']) || !empty($validated['observations'])) {
                $vehicleData['notes'] = $validated['notes'] ?? $validated['observations'];
            }

            $vehicle = Vehicle::create($vehicleData);
            $vehicle->load('customer');

            return response()->json([
                'success' => true,
                'message' => 'Veículo/Equipamento cadastrado com sucesso',
                'data' => $vehicle
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Erro ao criar veículo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cadastrar veículo/equipamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mapear tipo de veículo do frontend para o banco
     */
    private function mapVehicleType($type)
    {
        $typeMap = [
            'vehicle' => 'veiculo',
            'equipment' => 'equipamento',
            'veiculo' => 'veiculo',
            'equipamento' => 'equipamento',
            'maquina' => 'maquina',
            'outro' => 'outro'
        ];

        return $typeMap[$type] ?? 'veiculo';
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        try {
            \Log::info('Tentando buscar veículo com ID: ' . $id);
            
            $vehicle = Vehicle::findOrFail($id);
            
            $vehicle->load([
                'customer',
                'serviceOrders' => function ($query) {
                    $query->orderBy('opening_date', 'desc')->limit(10);
                }
            ]);

            return response()->json([
                'success' => true,
                'data' => $vehicle
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar veículo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar veículo/equipamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            \Log::info('Dados recebidos para atualização:', $request->all());
            
            $validated = $request->validate([
                'customer_id' => 'sometimes|nullable|integer|exists:customers,id',
                'customer_name_at_time' => 'sometimes|nullable|string|max:255',
                'customer_phone_at_time' => 'sometimes|nullable|string|max:20',
                'customer_email_at_time' => 'sometimes|nullable|string|max:255',
                'plate' => 'sometimes|nullable|string|max:8|unique:vehicles,plate,' . $id,
                'model' => 'sometimes|nullable|string|max:100',
                'make' => 'sometimes|nullable|string|max:100',
                'brand' => 'sometimes|nullable|string|max:100',
                'year' => 'sometimes|nullable|string|max:4',
                'serial_number' => 'sometimes|nullable|string|max:100',
                'chassis_number' => 'sometimes|nullable|string|max:100',
                'engine_number' => 'sometimes|nullable|string|max:100',
                'type' => 'sometimes|nullable|in:vehicle,equipment,veiculo,equipamento,maquina,outro',
                'color' => 'sometimes|nullable|string|max:50',
                'fuel_type' => 'sometimes|nullable|string|max:50',
                'mileage' => 'sometimes|nullable|integer|min:0',
                'notes' => 'sometimes|nullable|string|max:1000',
                'observations' => 'sometimes|nullable|string|max:1000',
                'active' => 'sometimes|boolean'
            ]);

            $vehicle = Vehicle::findOrFail($id);
            
            // Mapear campos do frontend para o formato do banco
            $vehicleData = [
                'customer_id' => $validated['customer_id'] ?? $vehicle->customer_id,
                'customer_name_at_time' => $validated['customer_name_at_time'] ?? $vehicle->customer_name_at_time,
                'customer_phone_at_time' => $validated['customer_phone_at_time'] ?? $vehicle->customer_phone_at_time,
                'customer_email_at_time' => $validated['customer_email_at_time'] ?? $vehicle->customer_email_at_time,
                'plate' => $validated['plate'] ?? $vehicle->plate,
                'model' => $validated['model'] ?? $vehicle->model,
                'make' => $validated['make'] ?? $validated['brand'] ?? $vehicle->make, // Prioriza 'make', depois 'brand', depois valor atual
                'year' => $validated['year'],
                'chassis_number' => $validated['chassis_number'],
                'engine_number' => $validated['engine_number'],
                'type' => $this->mapVehicleType($validated['type'] ?? $vehicle->type), // Mapear tipo
                'color' => $validated['color'],
                'notes' => $validated['notes'] ?? $validated['observations'] ?? $vehicle->notes, // Prioriza 'notes', depois 'observations', depois valor atual
                'active' => $validated['active'] ?? $vehicle->active
            ];

            $vehicle->update($vehicleData);
            $vehicle->load('customer');

            return response()->json([
                'success' => true,
                'message' => 'Veículo/Equipamento atualizado com sucesso',
                'data' => $vehicle
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar veículo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar veículo/equipamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $vehicle = Vehicle::findOrFail($id);
            
            // Verificar se o veículo está sendo usado em alguma OS
            $usageCount = $vehicle->serviceOrders()->count();
            if ($usageCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Este veículo/equipamento está sendo usado em {$usageCount} ordem(ns) de serviço e não pode ser excluído"
                ], 400);
            }

            $vehicle->delete();

            return response()->json([
                'success' => true,
                'message' => 'Veículo/Equipamento excluído com sucesso'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao excluir veículo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir veículo/equipamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar veículos ativos
     */
    public function active(): JsonResponse
    {
        try {
            $vehicles = Vehicle::active()
                ->with('customer')
                ->orderBy('plate')
                ->orderBy('model')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $vehicles
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar veículos ativos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar veículos ativos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar veículos por cliente
     */
    public function byCustomer(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id'
            ]);

            $vehicles = Vehicle::byCustomer($validated['customer_id'])
                ->active()
                ->orderBy('plate')
                ->orderBy('model')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $vehicles
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar veículos por cliente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar veículos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar veículos por tipo
     */
    public function byType(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:veiculo,equipamento,maquina,outro'
            ]);

            $vehicles = Vehicle::byType($validated['type'])
                ->active()
                ->with('customer')
                ->orderBy('plate')
                ->orderBy('model')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $vehicles
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar veículos por tipo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar veículos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Histórico de serviços do veículo
     */
    public function serviceHistory(Vehicle $vehicle): JsonResponse
    {
        try {
            $serviceOrders = $vehicle->getServiceHistory()
                ->with([
                    'customer',
                    'technicalResponsible',
                    'items.product',
                    'items.service'
                ])
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $serviceOrders
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar histórico de serviços: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar histórico de serviços',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estatísticas dos veículos
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total' => Vehicle::count(),
                'active' => Vehicle::where('active', true)->count(),
                'inactive' => Vehicle::where('active', false)->count(),
                'by_type' => Vehicle::selectRaw('type, COUNT(*) as count')
                    ->groupBy('type')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [$item->type => $item->count];
                    }),
                'by_customer' => Vehicle::withCount('serviceOrders')
                    ->orderBy('service_orders_count', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($vehicle) {
                        return [
                            'id' => $vehicle->id,
                            'identification' => $vehicle->getShortIdentification(),
                            'customer_name' => $vehicle->customer->name,
                            'service_count' => $vehicle->service_orders_count
                        ];
                    })
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar estatísticas dos veículos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar estatísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}