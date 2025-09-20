<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Service::query();

            // Filtros
            if ($request->filled('active')) {
                $query->where('active', $request->boolean('active'));
            }

            if ($request->filled('category')) {
                $query->where('category', $request->get('category'));
            }

            if ($request->filled('search')) {
                $query->search($request->get('search'));
            }

            // Ordenação
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginação
            $perPage = $request->get('per_page', 15);
            $services = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $services
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao listar serviços: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar serviços',
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
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'price' => 'required|numeric|min:0',
                'category' => 'nullable|string|max:100',
                'estimated_duration' => 'nullable|integer|min:0',
                'active' => 'boolean',
                'notes' => 'nullable|string|max:500'
            ]);

            $service = Service::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Serviço criado com sucesso',
                'data' => $service
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Erro ao criar serviço: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar serviço',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $service
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar serviço: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar serviço',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'price' => 'sometimes|required|numeric|min:0',
                'category' => 'nullable|string|max:100',
                'estimated_duration' => 'nullable|integer|min:0',
                'active' => 'boolean',
                'notes' => 'nullable|string|max:500'
            ]);

            $service->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Serviço atualizado com sucesso',
                'data' => $service
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar serviço: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar serviço',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service): JsonResponse
    {
        try {
            // Verificar se o serviço está sendo usado em alguma OS
            $usageCount = $service->serviceOrderItems()->count();
            if ($usageCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Este serviço está sendo usado em {$usageCount} ordem(ns) de serviço e não pode ser excluído"
                ], 400);
            }

            $service->delete();

            return response()->json([
                'success' => true,
                'message' => 'Serviço excluído com sucesso'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao excluir serviço: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir serviço',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar serviços ativos
     */
    public function active(): JsonResponse
    {
        try {
            $services = Service::active()
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $services
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar serviços ativos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar serviços ativos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar categorias de serviços
     */
    public function categories(): JsonResponse
    {
        try {
            $categories = Service::select('category')
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->distinct()
                ->orderBy('category')
                ->pluck('category');

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar categorias de serviços: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar categorias',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar serviços por categoria
     */
    public function byCategory(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'category' => 'required|string'
            ]);

            $services = Service::byCategory($validated['category'])
                ->active()
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $services
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar serviços por categoria: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar serviços',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estatísticas dos serviços
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total' => Service::count(),
                'active' => Service::where('active', true)->count(),
                'inactive' => Service::where('active', false)->count(),
                'categories' => Service::select('category')
                    ->whereNotNull('category')
                    ->where('category', '!=', '')
                    ->distinct()
                    ->count(),
                'average_price' => Service::where('active', true)->avg('price'),
                'most_used' => Service::withCount('serviceOrderItems')
                    ->orderBy('service_order_items_count', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($service) {
                        return [
                            'id' => $service->id,
                            'name' => $service->name,
                            'usage_count' => $service->service_order_items_count
                        ];
                    })
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar estatísticas dos serviços: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar estatísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}