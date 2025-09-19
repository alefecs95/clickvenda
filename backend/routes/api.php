<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\VasilhamesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;

// Rotas públicas
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/roles/public', [RoleController::class, 'publicRoles']);

// Rotas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Produtos
    Route::get('/products/search', [ProductController::class, 'search']);
    Route::get('/products/available', [ProductController::class, 'available']);
    Route::get('/products/returnables', [ProductController::class, 'returnables']);
    Route::get('/products/parents', [ProductController::class, 'parentProducts']);
    Route::post('/products/{product}/add-stock', [ProductController::class, 'addStock']);
    Route::put('/products/{product}/stock', [ProductController::class, 'updateStock']);
    Route::post('/products/{product}/stock-movement', [ProductController::class, 'addStockMovement']);
    Route::apiResource('products', ProductController::class);
    
    // Clientes
    Route::get('/customers/search', [CustomerController::class, 'search']);
    Route::get('/customers/with-credit-limit', [CustomerController::class, 'withCreditLimit']);
    Route::get('/customers/{customer}/credit-info', [CustomerController::class, 'getCreditInfo']);
    Route::put('/customers/{customer}/credit-limit', [CustomerController::class, 'updateCreditLimit']);
    Route::post('/customers/{customer}/adjust-credit', [CustomerController::class, 'adjustCredit']);
    Route::apiResource('customers', CustomerController::class);
    
    // Rotas para contas a receber
    Route::prefix('receivables')->group(function () {
        Route::get('/', [App\Http\Controllers\ReceivablePaymentController::class, 'index']);
        Route::get('/statistics', [App\Http\Controllers\ReceivablePaymentController::class, 'statistics']);
        Route::get('/customer/{customer}', [App\Http\Controllers\ReceivablePaymentController::class, 'byCustomer']);
        Route::post('/{receivablePayment}/payment', [App\Http\Controllers\ReceivablePaymentController::class, 'addPayment']);
    });
    
    // Pedidos
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::post('/orders/{order}/complete', [OrderController::class, 'complete']);
    Route::get('/orders/statistics', [OrderController::class, 'statistics']);
    Route::get('/orders/statistics-test', [OrderController::class, 'statistics']); // Endpoint de teste sem auth
    Route::apiResource('orders', OrderController::class)->middleware('check.stock');
    
    // Usuários e Permissões - Rotas administrativas
    Route::middleware('role:admin')->group(function () {
        // Usuários
        Route::get('/users/roles', [UserController::class, 'getRoles']);
        Route::post('/users/{user}/assign-roles', [UserController::class, 'assignRoles']);
        Route::get('/users/{user}/permissions', [UserController::class, 'checkPermissions']);
        Route::apiResource('users', UserController::class);
        
        // Roles - CRUD completo
        Route::get('/roles/{role}/users', [RoleController::class, 'users']);
        Route::get('/roles/{role}/permissions', [RoleController::class, 'permissions']);
        Route::post('/roles/{role}/assign-permissions', [RoleController::class, 'assignPermissions']);
        Route::post('/roles/{role}/toggle-status', [RoleController::class, 'toggleStatus']);
        Route::apiResource('roles', RoleController::class);
        
        // Permissions - CRUD completo
        Route::get('/permissions/modules', [PermissionController::class, 'modules']);
        Route::get('/permissions/module/{module}', [PermissionController::class, 'byModule']);
        Route::get('/permissions/{permission}/roles', [PermissionController::class, 'roles']);
        Route::post('/permissions/{permission}/assign-to-roles', [PermissionController::class, 'assignToRoles']);
        Route::post('/permissions/{permission}/toggle-status', [PermissionController::class, 'toggleStatus']);
        Route::apiResource('permissions', PermissionController::class);
    });
    
    // Configurações
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::get('/settings/category/{category}', [SettingsController::class, 'getByCategory']);
    Route::get('/settings/{key}', [SettingsController::class, 'show']);
    Route::post('/settings', [SettingsController::class, 'store']);
    Route::post('/settings/multiple', [SettingsController::class, 'storeMultiple']);
    Route::delete('/settings/{key}', [SettingsController::class, 'destroy']);
    
    // Vasilhames
    Route::prefix('vasilhames')->group(function () {
        Route::get('/', [VasilhamesController::class, 'index']);
        Route::get('/relatorio', [VasilhamesController::class, 'relatorio']);
        Route::get('/produtos', [VasilhamesController::class, 'produtosRetornaveis']);
        Route::get('/cliente/{clienteId}/historico', [VasilhamesController::class, 'historicoCliente']);
        Route::post('/devolucao', [VasilhamesController::class, 'registrarDevolucao']);
        Route::post('/saida', [VasilhamesController::class, 'registrarSaida']);
        
        // Rotas para gerenciar modelos de vasilhame
        Route::get('/modelos', [VasilhamesController::class, 'modelos']);
        Route::post('/modelos', [VasilhamesController::class, 'criarModelo']);
        Route::put('/modelos/{id}', [VasilhamesController::class, 'atualizarModelo']);
        Route::delete('/modelos/{id}', [VasilhamesController::class, 'excluirModelo']);
        
        // Novas rotas para pendências
        Route::get('/pendencias', [VasilhamesController::class, 'pendencias']);
        Route::get('/pendencias/cliente/{clienteId}', [VasilhamesController::class, 'pendenciasPorCliente']);
        Route::post('/pendencias/{pendenciaId}/resolver', [VasilhamesController::class, 'resolverPendencia']);
    });
});
