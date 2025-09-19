<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with('roles');

        // Filtros
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->get('role'));
            });
        }

        // Paginação
        $perPage = $request->get('per_page', 15);
        $users = $query->paginate($perPage);

        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Log dos dados recebidos para debug
        \Log::info('Dados recebidos para criação de usuário:', $request->all());
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'cpf' => 'nullable|string|max:14|unique:users',
            'is_admin' => 'boolean',
            'status' => 'nullable|in:ok,active,inactive',
            'terms_accepted' => 'boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id'
        ]);

        if ($validator->fails()) {
            \Log::error('Validação falhou:', $validator->errors()->toArray());
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userData = $request->except(['password_confirmation', 'roles']);
            $userData['password'] = Hash::make($request->password);

            \Log::info('Dados processados para criação:', $userData);

            $user = User::create($userData);

            // Atribuir roles se fornecidas
            if ($request->has('roles') && is_array($request->roles)) {
                $user->roles()->sync($request->roles);
            }

            $user->load('roles');

            \Log::info('Usuário criado com sucesso:', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'Usuário criado com sucesso',
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Erro ao criar usuário:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): JsonResponse
    {
        $user->load('roles.permissions');
        
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'cpf' => [
                'nullable',
                'string',
                'max:14',
                Rule::unique('users')->ignore($user->id)
            ],
            'is_admin' => 'boolean',
            'status' => 'nullable|in:ok,active,inactive',
            'terms_accepted' => 'boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $userData = $request->except(['password_confirmation', 'roles']);
        
        // Atualizar senha apenas se fornecida
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        } else {
            unset($userData['password']);
        }

        $user->update($userData);

        // Atualizar roles se fornecidas
        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        }

        $user->load('roles');

        return response()->json([
            'message' => 'Usuário atualizado com sucesso',
            'user' => $user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        // Não permitir deletar o próprio usuário
        if (auth()->id() === $user->id) {
            return response()->json([
                'message' => 'Você não pode deletar seu próprio usuário'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'Usuário deletado com sucesso'
        ]);
    }

    /**
     * Atribuir roles a um usuário
     */
    public function assignRoles(Request $request, User $user): JsonResponse
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

        $user->roles()->sync($request->roles);
        $user->load('roles');

        return response()->json([
            'message' => 'Roles atribuídas com sucesso',
            'user' => $user
        ]);
    }

    /**
     * Listar todas as roles disponíveis
     */
    public function getRoles(): JsonResponse
    {
        $roles = Role::with('permissions')->get();
        
        return response()->json($roles);
    }

    /**
     * Verificar permissões do usuário
     */
    public function checkPermissions(User $user): JsonResponse
    {
        $permissions = $user->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id')
            ->values();

        return response()->json([
            'user_id' => $user->id,
            'permissions' => $permissions
        ]);
    }
}
