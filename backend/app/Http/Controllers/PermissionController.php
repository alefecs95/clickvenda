<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $permissions = Permission::with('roles')->get();
        
        return response()->json($permissions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:permissions',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'module' => 'nullable|string|max:100',
            'active' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $permissionData = $request->except(['roles']);
        $permissionData['active'] = $request->get('active', true);

        $permission = Permission::create($permissionData);

        // Atribuir a roles se fornecidas
        if ($request->has('roles')) {
            $permission->roles()->sync($request->roles);
        }

        $permission->load('roles');

        return response()->json([
            'message' => 'Permissão criada com sucesso',
            'permission' => $permission
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission): JsonResponse
    {
        $permission->load('roles');
        
        return response()->json($permission);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions')->ignore($permission->id)
            ],
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'module' => 'nullable|string|max:100',
            'active' => 'boolean',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $permissionData = $request->except(['roles']);
        $permission->update($permissionData);

        // Atualizar roles se fornecidas
        if ($request->has('roles')) {
            $permission->roles()->sync($request->roles);
        }

        $permission->load('roles');

        return response()->json([
            'message' => 'Permissão atualizada com sucesso',
            'permission' => $permission
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission): JsonResponse
    {
        // Verificar se a permissão não está sendo usada por roles
        if ($permission->roles()->count() > 0) {
            return response()->json([
                'message' => 'Não é possível excluir uma permissão que está sendo usada por roles'
            ], 422);
        }

        $permission->delete();

        return response()->json([
            'message' => 'Permissão excluída com sucesso'
        ]);
    }

    /**
     * Listar permissões por módulo
     */
    public function byModule(string $module): JsonResponse
    {
        $permissions = Permission::byModule($module)->with('roles')->get();
        
        return response()->json($permissions);
    }

    /**
     * Listar todos os módulos disponíveis
     */
    public function modules(): JsonResponse
    {
        $modules = Permission::select('module')
            ->whereNotNull('module')
            ->distinct()
            ->pluck('module');
        
        return response()->json($modules);
    }

    /**
     * Ativar/Desativar permissão
     */
    public function toggleStatus(Permission $permission): JsonResponse
    {
        $permission->update(['active' => !$permission->active]);

        return response()->json([
            'message' => $permission->active ? 'Permissão ativada com sucesso' : 'Permissão desativada com sucesso',
            'permission' => $permission
        ]);
    }

    /**
     * Listar roles que têm uma permissão específica
     */
    public function roles(Permission $permission): JsonResponse
    {
        $roles = $permission->roles()->get();
        
        return response()->json($roles);
    }

    /**
     * Atribuir permissão a múltiplas roles
     */
    public function assignToRoles(Request $request, Permission $permission): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $permission->roles()->sync($request->roles);
        $permission->load('roles');

        return response()->json([
            'message' => 'Permissão atribuída às roles com sucesso',
            'permission' => $permission
        ]);
    }
}