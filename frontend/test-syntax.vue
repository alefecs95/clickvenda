<template>
  <div class="min-h-screen bg-gray-100 flex flex-col">
    <!-- Header Compacto -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-4 py-2">
      <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
          <div class="flex flex-col">
            <h1 class="text-lg font-bold text-gray-900 flex items-center">
              PONTO DE VENDA
            </h1>
          </div>
        </div>
      </div>
    </div>

    <!-- Layout Principal -->
    <div class="flex-1 flex" style="height: calc(100vh - 140px);">
      <!-- Carrinho - Lado Esquerdo (Fixo) -->
      <div class="w-1/3 bg-white border-r border-gray-200 flex flex-col h-full">
        <div class="bg-primary-600 text-white p-4">
          <h2 class="text-lg font-bold">CARRINHO DE COMPRAS</h2>
        </div>
      </div>

      <!-- Produtos - Lado Direito -->
      <div class="w-2/3 bg-white flex flex-col">
        <div class="p-4 border-b border-gray-200">
          <input type="text" placeholder="Buscar produtos..." class="w-full px-3 py-2 border rounded" />
        </div>
        <div class="flex-1 overflow-y-auto p-4">
          <div class="grid grid-cols-1 gap-3">
            <div class="border border-gray-200 rounded-lg p-3">
              <h3 class="font-semibold">Produto Teste</h3>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Rodapé Fixo -->
    <div class="bg-white border-t border-gray-200 px-4 py-3 shadow-lg">
      <div class="flex justify-between items-center">
        <div class="text-lg font-bold">
          TOTAL: R$ 0,00
        </div>
        <button class="bg-primary-600 text-white px-6 py-2 rounded">
          FINALIZAR
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Script vazio para teste
</script>
