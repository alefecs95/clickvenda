<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')->get();
        
        return response()->json($roles);
    }

    /**
     * Get public roles for registration form.
     */
    public function publicRoles(): JsonResponse
    {
        $roles = Role::where('active', true)
                    ->select('id', 'name', 'display_name', 'description')
                    ->get();
        
        return response()->json($roles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'active' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $roleData = $request->except(['permissions']);
        $roleData['active'] = $request->get('active', true);

        $role = Role::create($roleData);

        // Atribuir permissões se fornecidas
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        $role->load('permissions');

        return response()->json([
            'message' => 'Role criada com sucesso',
            'role' => $role
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role): JsonResponse
    {
        $role->load('permissions', 'users');
        
        return response()->json($role);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->ignore($role->id)
            ],
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'active' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $roleData = $request->except(['permissions']);
        $role->update($roleData);

        // Atualizar permissões se fornecidas
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        $role->load('permissions');

        return response()->json([
            'message' => 'Role atualizada com sucesso',
            'role' => $role
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role): JsonResponse
    {
        // Verificar se a role não está sendo usada por usuários
        if ($role->users()->count() > 0) {
            return response()->json([
                'message' => 'Não é possível excluir uma role que está sendo usada por usuários'
            ], 422);
        }

        // Não permitir excluir roles padrão do sistema
        $systemRoles = ['admin', 'manager', 'seller', 'operator'];
        if (in_array($role->name, $systemRoles)) {
            return response()->json([
                'message' => 'Não é possível excluir roles padrão do sistema'
            ], 422);
        }

        $role->delete();

        return response()->json([
            'message' => 'Role excluída com sucesso'
        ]);
    }

    /**
     * Atribuir permissões a uma role
     */
    public function assignPermissions(Request $request, Role $role): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $role->permissions()->sync($request->permissions);
        $role->load('permissions');

        return response()->json([
            'message' => 'Permissões atribuídas com sucesso',
            'role' => $role
        ]);
    }

    /**
     * Listar usuários de uma role
     */
    public function users(Role $role): JsonResponse
    {
        $users = $role->users()->get();
        
        return response()->json($users);
    }

    /**
     * Ativar/Desativar role
     */
    public function toggleStatus(Role $role): JsonResponse
    {
        $role->update(['active' => !$role->active]);

        return response()->json([
            'message' => $role->active ? 'Role ativada com sucesso' : 'Role desativada com sucesso',
            'role' => $role
        ]);
    }
}