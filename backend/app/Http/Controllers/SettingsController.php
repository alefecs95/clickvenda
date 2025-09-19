<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    /**
     * Busca todas as configurações
     */
    public function index(): JsonResponse
    {
        try {
            $settings = Setting::all()->mapWithKeys(function ($setting) {
                return [$setting->key => Setting::get($setting->key)];
            });

            return response()->json([
                'success' => true,
                'data' => $settings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar configurações',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Busca configurações por categoria
     */
    public function getByCategory(string $category): JsonResponse
    {
        try {
            $settings = Setting::getByPrefix($category);

            return response()->json([
                'success' => true,
                'data' => $settings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar configurações',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Salva uma configuração específica
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'key' => 'required|string',
                'value' => 'required',
                'type' => 'nullable|string|in:string,number,boolean,json',
                'description' => 'nullable|string'
            ]);

            $setting = Setting::set(
                $request->key,
                $request->value,
                $request->type ?? 'string',
                $request->description
            );

            return response()->json([
                'success' => true,
                'message' => 'Configuração salva com sucesso',
                'data' => $setting
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar configuração',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Salva múltiplas configurações
     */
    public function storeMultiple(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'settings' => 'required|array',
                'prefix' => 'nullable|string'
            ]);

            \Log::info('Dados recebidos para salvar configurações:', $request->all());

            Setting::setMultiple($request->settings, $request->prefix);

            \Log::info('Configurações salvas com sucesso');

            return response()->json([
                'success' => true,
                'message' => 'Configurações salvas com sucesso'
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao salvar configurações:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar configurações',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Busca uma configuração específica
     */
    public function show(string $key): JsonResponse
    {
        try {
            $value = Setting::get($key);

            return response()->json([
                'success' => true,
                'data' => [
                    'key' => $key,
                    'value' => $value
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar configuração',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove uma configuração
     */
    public function destroy(string $key): JsonResponse
    {
        try {
            Setting::where('key', $key)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Configuração removida com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover configuração',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
