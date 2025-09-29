<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $suppliers = Supplier::active()
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'phone', 'cpf_cnpj', 'city', 'state']);

            return response()->json([
                'success' => true,
                'data' => $suppliers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar fornecedores: ' . $e->getMessage()
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
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'cpf_cnpj' => 'nullable|string|max:18|unique:suppliers,cpf_cnpj',
                'address' => 'nullable|string|max:500',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:2',
                'zip_code' => 'nullable|string|max:10',
                'contact_person' => 'nullable|string|max:255',
                'bank_name' => 'nullable|string|max:100',
                'bank_agency' => 'nullable|string|max:20',
                'bank_account' => 'nullable|string|max:30',
                'pix_key' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000',
                'active' => 'boolean'
            ]);

            $supplier = Supplier::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Fornecedor criado com sucesso!',
                'data' => $supplier
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
                'message' => 'Erro ao criar fornecedor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $supplier = Supplier::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $supplier
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fornecedor não encontrado'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $supplier = Supplier::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'cpf_cnpj' => 'nullable|string|max:18|unique:suppliers,cpf_cnpj,' . $id,
                'address' => 'nullable|string|max:500',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:2',
                'zip_code' => 'nullable|string|max:10',
                'contact_person' => 'nullable|string|max:255',
                'bank_name' => 'nullable|string|max:100',
                'bank_agency' => 'nullable|string|max:20',
                'bank_account' => 'nullable|string|max:30',
                'pix_key' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000',
                'active' => 'boolean'
            ]);

            $supplier->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Fornecedor atualizado com sucesso!',
                'data' => $supplier
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar fornecedor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $supplier = Supplier::findOrFail($id);
            
            // Soft delete - marca como inativo ao invés de deletar
            $supplier->update(['active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Fornecedor desativado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao desativar fornecedor: ' . $e->getMessage()
            ], 500);
        }
    }
}
