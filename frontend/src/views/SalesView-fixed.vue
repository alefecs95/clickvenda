<template>
  <div class="min-h-screen bg-gray-100 flex flex-col">
    <!-- Header Compacto -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-4 py-2">
      <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
          <div class="flex flex-col">
            <h1 class="text-lg font-bold text-gray-900 flex items-center">
              <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
              </svg>
              {{ settingsStore.storeSettings.name || 'PONTO DE VENDA' }}
            </h1>
            <div class="text-xs text-gray-500 flex items-center space-x-2">
              <span>{{ new Date().toLocaleDateString('pt-BR') }} - {{ new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }) }}</span>
              <span v-if="settingsStore.storeSettings.phone" class="text-gray-400">•</span>
              <span v-if="settingsStore.storeSettings.phone">{{ settingsStore.storeSettings.phone }}</span>
            </div>
          </div>
        </div>
        <div class="flex items-center space-x-4">
          <div class="text-xs text-gray-600">
            <span class="font-medium">{{ cart.length }}</span> itens
          </div>
          <div v-if="cart.length > 0" class="text-xl font-bold text-primary-600">
            R$ {{ formatPrice(total) }}
          </div>
        </div>
      </div>
    </div>

    <!-- Layout Principal -->
    <div class="flex-1 flex" style="height: calc(100vh - 140px);">
      <!-- Carrinho - Lado Esquerdo (Fixo) -->
      <div class="w-1/3 bg-white border-r border-gray-200 flex flex-col h-full">
        <!-- Header do Carrinho -->
        <div class="bg-primary-600 text-white p-4">
          <div class="flex justify-between items-center">
            <h2 class="text-lg font-bold">CARRINHO DE COMPRAS</h2>
            <div class="flex items-center space-x-2">
              <span class="text-sm">{{ cart.length }} itens</span>
              <button
                v-if="cart.length > 0"
                @click="clearCart"
                class="bg-red-500 hover:bg-red-600 px-2 py-1 rounded text-xs"
                title="Limpar Carrinho (F8)"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Lista de Itens do Carrinho -->
        <div class="flex-1 overflow-y-auto">
          <div v-if="cart.length === 0" class="flex flex-col items-center justify-center h-full text-gray-500">
            <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m6 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
            </svg>
            <p class="text-lg font-medium">Carrinho vazio</p>
            <p class="text-sm">Adicione produtos para continuar</p>
          </div>

          <div v-else class="divide-y divide-gray-200">
            <div
              v-for="(item, index) in cart"
              :key="item.product.id"
              class="p-4 hover:bg-gray-50"
              :class="{ 'bg-blue-50': selectedCartItem === index }"
            >
              <div class="flex justify-between items-start mb-2">
                <div class="flex-1">
                  <h4 class="font-semibold text-gray-900 text-sm">{{ item.product.name }}</h4>
                  <div class="text-xs text-gray-600 mt-1">
                    R$ {{ formatPrice(item.product.price) }} × {{ item.quantity }}
                    <span v-if="item.product.returnable" class="text-orange-600 ml-2">
                      + {{ item.quantity }} vasilhame(s)
                    </span>
                  </div>
                </div>
                <div class="text-right">
                  <div class="font-bold text-primary-600">
                    R$ {{ formatPrice(getItemTotal(item)) }}
                  </div>
                </div>
              </div>
              
              <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                  <button
                    @click="updateQuantity(item.product.id, item.quantity - 1)"
                    :disabled="item.quantity <= 1"
                    class="w-8 h-8 rounded bg-gray-200 text-gray-600 hover:bg-gray-300 disabled:opacity-50 flex items-center justify-center font-bold"
                  >
                    -
                  </button>
                  <span class="w-12 text-center font-bold">{{ item.quantity }}</span>
                  <button
                    @click="updateQuantity(item.product.id, item.quantity + 1)"
                    :disabled="item.quantity >= (item.product.actual_stock || item.product.stock_quantity)"
                    class="w-8 h-8 rounded bg-gray-200 text-gray-600 hover:bg-gray-300 disabled:opacity-50 flex items-center justify-center font-bold"
                  >
                    +
                  </button>
                </div>
                <button
                  @click="removeFromCart(item.product.id)"
                  class="text-red-600 hover:text-red-800 p-1"
                  title="Remover item (Delete)"
                >
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Resumo do Carrinho (apenas itens) -->
        <div class="space-y-2 text-sm">
          <div class="text-xs text-gray-500 mb-2">
            {{ cart.length }} item(s) no carrinho
          </div>
        </div>
      </div>

      <!-- Produtos - Lado Direito -->
      <div class="w-2/3 bg-white flex flex-col">
        <!-- Header de Busca -->
        <div class="p-4 border-b border-gray-200">
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <input
              ref="searchInput"
              v-model="searchQuery"
              type="text"
              placeholder="Buscar por código de barras, nome ou SKU... (F4)"
              class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent text-lg"
              @input="handleSearch"
              @keydown.enter="focusFirstProduct"
            />
          </div>
        </div>

        <!-- Lista de Produtos -->
        <div class="flex-1 overflow-y-auto p-4">
          <div v-if="filteredProducts.length === 0" class="flex flex-col items-center justify-center h-64">
            <svg class="h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <p class="text-gray-600 text-lg">Nenhum produto encontrado</p>
            <p class="text-gray-500 text-sm">Tente buscar por outro termo</p>
          </div>

          <div v-else class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-3">
            <div
              v-for="(product, index) in filteredProducts"
              :key="product.id"
              :ref="'product-' + index"
              class="border border-gray-200 rounded-lg p-3 transition-all group"
              :class="{ 
                'ring-2 ring-primary-500 bg-primary-50': selectedProductIndex === index,
                'cursor-pointer hover:border-primary-300 hover:shadow-md': product.is_available_for_sale !== false && (product.actual_stock || product.stock_quantity) > 0,
                'opacity-50 cursor-not-allowed bg-gray-50': product.is_available_for_sale === false || (product.actual_stock || product.stock_quantity) <= 0
              }"
              @click="(product.is_available_for_sale !== false && (product.actual_stock || product.stock_quantity) > 0) ? selectProduct(product) : null"
              @keydown.enter="(product.is_available_for_sale !== false && (product.actual_stock || product.stock_quantity) > 0) ? selectProduct(product) : null"
              tabindex="0"
            >
              <div class="flex justify-between items-start mb-2">
                <h3 class="font-semibold text-gray-900 text-sm group-hover:text-primary-600 transition-colors line-clamp-2">
                  {{ product.name }}
                </h3>
                <div class="text-right ml-2">
                  <span class="text-lg font-bold text-primary-600">R$ {{ formatPrice(product.price) }}</span>
                  <div v-if="product.returnable" class="text-xs text-orange-600 font-medium">
                    + R$ {{ formatPrice(product.returnable_price) }}
                  </div>
                </div>
              </div>
              
              <div class="flex justify-between items-center text-xs text-gray-600 mb-2">
                <div class="flex items-center space-x-1">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                  </svg>
                  <span class="font-medium">{{ product.actual_stock || product.stock_quantity }}</span>
                </div>
                <div class="flex space-x-1">
                  <span v-if="product.is_available_for_sale !== false && product.available" class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                    Disponível
                  </span>
                  <span v-else-if="product.is_available_for_sale === false" class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">
                    Indisponível
                  </span>
                  <span v-else-if="!product.available" class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">
                    Indisponível
                  </span>
                  <span v-if="product.returnable" class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded-full">
                    Retornável
                  </span>
                  <span v-if="product.parent_product_id" class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                    Variação
                  </span>
                </div>
              </div>
              
              <div class="text-center pt-2 border-t border-gray-100">
                <div v-if="(product.actual_stock || product.stock_quantity) <= 0" class="flex items-center justify-center text-red-500 font-medium text-xs">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                  </svg>
                  Produto indisponível
                </div>
                <div v-else class="flex items-center justify-center text-primary-600 font-medium text-xs group-hover:text-primary-700">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  Adicionar ao carrinho
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Rodapé Fixo com Total e Atalhos -->
    <div class="bg-white border-t border-gray-200 px-4 py-3 shadow-lg">
      <div class="flex justify-between items-center">
        <!-- Resumo Financeiro -->
        <div class="flex items-center space-x-6">
          <div class="text-sm">
            <span class="text-gray-600">Subtotal:</span>
            <span class="font-medium ml-2">R$ {{ formatPrice(subtotal) }}</span>
          </div>
          <div v-if="returnableTotal > 0" class="text-sm">
            <span class="text-gray-600">Vasilhames:</span>
            <span class="font-medium text-orange-600 ml-2">+R$ {{ formatPrice(returnableTotal) }}</span>
          </div>
          <div class="text-sm">
            <span class="text-gray-600">Desconto:</span>
            <span class="font-medium text-green-600 ml-2">-R$ {{ formatPrice(discount) }}</span>
          </div>
          <div class="text-lg font-bold">
            <span class="text-gray-900">TOTAL:</span>
            <span class="text-primary-600 ml-2">R$ {{ formatPrice(total) }}</span>
          </div>
          <div v-if="cart.length === 0" class="text-xs text-gray-500 italic">
            Carrinho vazio
          </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex items-center space-x-3">
          <button
            @click="showCustomerModal = true"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded font-bold text-sm flex items-center"
            title="Selecionar Cliente (F2)"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            CLIENTE (F2)
          </button>
          <button
            @click="cart.length > 0 ? showCheckoutModal = true : null"
            :class="cart.length > 0 ? 'bg-primary-600 hover:bg-primary-700' : 'bg-gray-400 cursor-not-allowed'"
            class="text-white px-6 py-2 rounded font-bold text-sm flex items-center"
            :title="cart.length > 0 ? 'Finalizar Venda (F9)' : 'Adicione produtos ao carrinho'"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
            </svg>
            {{ cart.length > 0 ? 'FINALIZAR (F9)' : 'FINALIZAR' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Script será copiado do arquivo original
</script>
