<template>
  <div class="min-h-screen bg-gray-100 flex flex-col">
    <!-- Header Compacto -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-4 py-2">
      <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
          <h1 class="text-lg font-bold text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
            </svg>
            PONTO DE VENDA
          </h1>
          <div class="text-xs text-gray-500">
            {{ new Date().toLocaleDateString('pt-BR') }} - {{ new Date().toLocaleTimeString('pt-BR') }}
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
    <div class="flex-1 flex">
      <!-- Carrinho - Lado Esquerdo -->
      <div class="w-1/2 bg-white border-r border-gray-200 flex flex-col">
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

        <!-- Resumo do Carrinho -->
        <div v-if="cart.length > 0" class="border-t border-gray-200 p-4 bg-gray-50">
          <!-- Informações do Cliente -->
          <div v-if="selectedCustomer" class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium text-blue-900 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                {{ selectedCustomer.name }}
              </span>
            </div>
            <div v-if="selectedCustomer.credit_limit > 0" class="text-xs text-blue-700">
              Crédito disponível: R$ {{ formatPrice(selectedCustomer.credit_limit - selectedCustomer.credit_used) }}
            </div>
          </div>

          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span>Subtotal:</span>
              <span class="font-medium">R$ {{ formatPrice(subtotal) }}</span>
            </div>
            <div v-if="returnableTotal > 0" class="flex justify-between">
              <span>Vasilhames:</span>
              <span class="font-medium text-orange-600">+R$ {{ formatPrice(returnableTotal) }}</span>
            </div>
            <div class="flex justify-between">
              <span>Desconto:</span>
              <span class="font-medium text-green-600">-R$ {{ formatPrice(discount) }}</span>
            </div>
            <div class="border-t border-gray-300 pt-2">
              <div class="flex justify-between text-lg font-bold">
                <span>TOTAL:</span>
                <span class="text-primary-600">R$ {{ formatPrice(total) }}</span>
              </div>
            </div>
          </div>

          <!-- Botões de Ação -->
          <div class="mt-4 grid grid-cols-2 gap-2">
            <button
              @click="showCheckoutModal = true"
              :disabled="cart.length === 0"
              class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-3 rounded font-bold text-sm disabled:opacity-50"
              title="Finalizar Venda (F9)"
            >
              <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
              </svg>
              FINALIZAR (F9)
            </button>
            <button
              @click="showCustomerModal = true"
              class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-3 rounded font-bold text-sm"
              title="Selecionar Cliente (F2)"
            >
              <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
              CLIENTE (F2)
            </button>
          </div>
        </div>
      </div>

      <!-- Produtos - Lado Direito -->
      <div class="w-1/2 bg-white flex flex-col">
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
        </div>

        <!-- Lista de Produtos -->
        <div class="flex-1 overflow-y-auto p-4">
          <div v-if="searching" class="flex flex-col items-center justify-center h-64">
            <svg class="animate-spin h-8 w-8 text-primary-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-gray-600">Buscando produtos...</p>
          </div>

          <div v-else-if="searchResults.length === 0 && searchQuery" class="flex flex-col items-center justify-center h-64">
            <svg class="h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <p class="text-gray-600 text-lg">Nenhum produto encontrado</p>
            <p class="text-gray-500 text-sm">Tente buscar por outro termo</p>
          </div>

          <div v-else-if="searchResults.length === 0" class="flex flex-col items-center justify-center h-64">
            <svg class="h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <p class="text-gray-600 text-lg">Digite para buscar produtos</p>
            <p class="text-gray-500 text-sm">Use o código de barras, nome ou SKU</p>
          </div>

          <div v-else class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-3">
            <div
              v-for="(product, index) in searchResults"
              :key="product.id"
              :ref="'product-' + index"
              class="border border-gray-200 rounded-lg p-3 hover:border-primary-300 hover:shadow-md transition-all cursor-pointer group"
              :class="{ 'ring-2 ring-primary-500 bg-primary-50': selectedProductIndex === index }"
              @click="addToCart(product)"
              @keydown.enter="addToCart(product)"
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
                <span v-if="product.sku" class="font-mono text-gray-400">{{ product.sku }}</span>
              </div>

              <div class="flex flex-wrap gap-1 mb-2">
                <span v-if="!product.available" class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">
                  Indisponível
                </span>
                <span v-if="product.returnable" class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded-full">
                  Retornável
                </span>
                <span v-if="product.parent_product_id" class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                  Variação
                </span>
              </div>
              
              <div class="text-center pt-2 border-t border-gray-100">
                <div class="flex items-center justify-center text-primary-600 font-medium text-xs group-hover:text-primary-700">
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

            <!-- Products Grid -->
            <div v-if="searching" class="text-center py-8">
              <svg class="animate-spin h-6 w-6 text-primary-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <p class="mt-2 text-gray-600">Buscando produtos...</p>
            </div>

            <div v-else-if="searchResults.length === 0 && searchQuery" class="text-center py-8">
              <svg class="h-12 w-12 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
              </svg>
              <p class="mt-2 text-gray-600">Nenhum produto encontrado</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div
                v-for="product in searchResults"
                :key="product.id"
                class="border border-gray-200 rounded-lg p-4 hover:border-primary-300 hover:shadow-md transition-all cursor-pointer group"
                @click="addToCart(product)"
              >
                <div class="flex justify-between items-start mb-3">
                  <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 transition-colors">{{ product.name }}</h3>
                  <div class="text-right">
                    <span class="text-xl font-bold text-primary-600">R$ {{ formatPrice(product.price) }}</span>
                    <div v-if="product.returnable" class="text-xs text-orange-600 font-medium">
                      + R$ {{ formatPrice(product.returnable_price) }} (vasilhame)
                    </div>
                  </div>
                </div>
                
                <p v-if="product.description" class="text-sm text-gray-600 mb-3 line-clamp-2">{{ product.description }}</p>
                
                <div class="flex justify-between items-center">
                  <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500 flex items-center">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                      </svg>
                      Estoque: <span class="font-medium">{{ product.actual_stock || product.stock_quantity }}</span>
                    </span>
                    <span v-if="!product.available" class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">
                      Indisponível
                    </span>
                    <span v-if="product.returnable" class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded-full">
                      Retornável
                    </span>
                  </div>
                  <span v-if="product.sku" class="text-xs text-gray-400 font-mono">{{ product.sku }}</span>
                </div>
                
                <div class="mt-3 pt-3 border-t border-gray-100">
                  <div class="flex items-center justify-center text-primary-600 font-medium text-sm group-hover:text-primary-700">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Adicionar ao carrinho
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Cart Section -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow-lg p-6 sticky top-8">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-xl font-bold text-gray-900 flex items-center">
              <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m6 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
              </svg>
              Carrinho
            </h2>
              <div v-if="cart.length > 0" class="bg-primary-100 text-primary-800 text-xs font-bold px-2 py-1 rounded-full">
                {{ cart.length }} {{ cart.length === 1 ? 'item' : 'itens' }}
              </div>
            </div>
            
            <div v-if="cart.length === 0" class="text-center py-12">
              <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m6 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                </svg>
              </div>
              <p class="text-lg font-medium text-gray-600 mb-2">Carrinho vazio</p>
              <p class="text-sm text-gray-500">Adicione produtos para continuar</p>
            </div>

            <div v-else>
              <!-- Cart Items -->
              <div class="space-y-3 mb-6 max-h-96 overflow-y-auto">
                <div
                  v-for="item in cart"
                  :key="item.product.id"
                  class="flex justify-between items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                >
                  <div class="flex-1">
                    <h4 class="font-semibold text-gray-900 mb-1">{{ item.product.name }}</h4>
                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                      <span>R$ {{ formatPrice(item.product.price) }}</span>
                      <span>×</span>
                      <span class="font-medium">{{ item.quantity }}</span>
                      <span>=</span>
                      <span class="font-bold text-primary-600">R$ {{ formatPrice(item.product.price * item.quantity) }}</span>
                    </div>
                    <div v-if="item.product.returnable" class="text-xs text-orange-600 mt-1">
                      + {{ item.quantity }} vasilhame(s): R$ {{ formatPrice(item.product.returnable_price * item.quantity) }}
                    </div>
                  </div>
                  <div class="flex items-center space-x-2">
                    <button
                      @click="updateQuantity(item.product.id, item.quantity - 1)"
                      :disabled="item.quantity <= 1"
                      class="w-8 h-8 rounded-full bg-gray-200 text-gray-600 hover:bg-gray-300 disabled:opacity-50 flex items-center justify-center font-bold"
                    >
                      -
                    </button>
                    <span class="w-8 text-center font-bold">{{ item.quantity }}</span>
                    <button
                      @click="updateQuantity(item.product.id, item.quantity + 1)"
                      :disabled="item.quantity >= (item.product.actual_stock || item.product.stock_quantity)"
                      class="w-8 h-8 rounded-full bg-gray-200 text-gray-600 hover:bg-gray-300 disabled:opacity-50 flex items-center justify-center font-bold"
                    >
                      +
                    </button>
                    <button
                      @click="removeFromCart(item.product.id)"
                      class="text-red-600 hover:text-red-800 p-1"
                      title="Remover item"
                    >
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Cart Summary -->
              <div class="border-t border-gray-200 pt-4 space-y-3">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Subtotal:</span>
                  <span class="font-medium">R$ {{ formatPrice(subtotal) }}</span>
                </div>
                
                <div v-if="returnableTotal > 0" class="flex justify-between text-sm">
                  <span class="text-gray-600">Vasilhames:</span>
                  <span class="font-medium text-orange-600">+R$ {{ formatPrice(returnableTotal) }}</span>
                </div>
                
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Desconto:</span>
                  <span class="font-medium text-green-600">-R$ {{ formatPrice(discount) }}</span>
                </div>
                
                <div class="border-t border-gray-200 pt-3">
                  <div class="flex justify-between">
                    <span class="text-lg font-bold text-gray-900">Total:</span>
                    <span class="text-xl font-bold text-primary-600">R$ {{ formatPrice(total) }}</span>
                  </div>
                </div>
              </div>

              <!-- Customer Credit Info -->
              <div v-if="selectedCustomer && selectedCustomer.credit_limit > 0" class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-medium text-blue-900 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    Crédito Disponível
                  </span>
                  <span class="text-sm font-bold text-blue-600">R$ {{ formatPrice(selectedCustomer.credit_limit - selectedCustomer.credit_used) }}</span>
                </div>
                <div class="w-full bg-blue-200 rounded-full h-2">
                  <div 
                    class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                    :style="{ width: `${getCreditPercentage(selectedCustomer)}%` }"
                  ></div>
                </div>
                <div class="flex justify-between text-xs text-blue-700 mt-1">
                  <span>Usado: R$ {{ formatPrice(selectedCustomer.credit_used) }}</span>
                  <span>Limite: R$ {{ formatPrice(selectedCustomer.credit_limit) }}</span>
                </div>
              </div>

              <!-- Checkout Button -->
              <button
                @click="showCheckoutModal = true"
                class="w-full mt-6 bg-primary-600 text-white py-4 px-4 rounded-lg hover:bg-primary-700 transition-colors font-bold text-lg shadow-lg"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
                Finalizar Venda
              </button>
            </div>
          </div>
        </div>
      </div>

    <!-- Checkout Modal -->
    <div v-if="showCheckoutModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-10 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900 flex items-center">
              <svg class="w-6 h-6 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
              </svg>
              Finalizar Venda
            </h3>
            <button
              @click="showCheckoutModal = false"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <form @submit.prevent="handleCheckout">
            <div class="space-y-6">
              <!-- Customer Selection -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                  Cliente (opcional)
                </label>
                <select
                  v-model="checkoutForm.customer_id"
                  @change="onCustomerChange"
                  class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-lg"
                >
                  <option value="">Cliente não identificado</option>
                  <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                    {{ customer.name }}
                  </option>
                </select>
              </div>
              
              <!-- Customer Credit Info -->
              <div v-if="selectedCustomer && selectedCustomer.credit_limit > 0" class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-medium text-blue-900 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    Crédito Disponível
                  </span>
                  <span class="text-lg font-bold text-blue-600">R$ {{ formatPrice(selectedCustomer.credit_limit - selectedCustomer.credit_used) }}</span>
                </div>
                <div class="w-full bg-blue-200 rounded-full h-2 mb-2">
                  <div 
                    class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                    :style="{ width: `${getCreditPercentage(selectedCustomer)}%` }"
                  ></div>
                </div>
                <div class="flex justify-between text-xs text-blue-700">
                  <span>Usado: R$ {{ formatPrice(selectedCustomer.credit_used) }}</span>
                  <span>Limite: R$ {{ formatPrice(selectedCustomer.credit_limit) }}</span>
                </div>
              </div>
              
              <!-- Payment Method -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                  </svg>
                  Forma de Pagamento
                </label>
                <select
                  v-model="checkoutForm.payment_method"
                  required
                  class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-lg"
                >
                  <option value="money">Dinheiro</option>
                  <option value="card">Cartão</option>
                  <option value="pix">PIX</option>
                  <option v-if="selectedCustomer && selectedCustomer.credit_limit > 0" value="credit">A Prazo (Crédito)</option>
                </select>
              </div>
              
              <!-- Credit Validation -->
              <div v-if="checkoutForm.payment_method === 'credit' && selectedCustomer" class="p-4 rounded-lg" :class="canUseCredit ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <svg v-if="canUseCredit" class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <svg v-else class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm font-medium" :class="canUseCredit ? 'text-green-800' : 'text-red-800'">
                      {{ canUseCredit ? 'Crédito suficiente para esta venda' : 'Crédito insuficiente para esta venda' }}
                    </p>
                    <p class="text-xs mt-1" :class="canUseCredit ? 'text-green-600' : 'text-red-600'">
                      {{ canUseCredit ? `Sobrará R$ ${formatPrice(selectedCustomer.credit_limit - selectedCustomer.credit_used - total)}` : `Faltam R$ ${formatPrice(total - (selectedCustomer.credit_limit - selectedCustomer.credit_used))}` }}
                    </p>
                  </div>
                </div>
              </div>
              
              <!-- Discount -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                  </svg>
                  Desconto (R$)
                </label>
                <input
                  v-model="checkoutForm.discount"
                  type="number"
                  step="0.01"
                  min="0"
                  :max="subtotal"
                  class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-lg"
                />
              </div>
              
              <!-- Notes -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                  Observações
                </label>
                <textarea
                  v-model="checkoutForm.notes"
                  rows="3"
                  class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  placeholder="Observações sobre a venda..."
                ></textarea>
              </div>
            </div>
            
            <div class="mt-8 flex space-x-3">
              <button
                type="submit"
                :disabled="checkoutSubmitting || (checkoutForm.payment_method === 'credit' && !canUseCredit)"
                class="flex-1 bg-primary-600 text-white py-4 px-6 rounded-lg hover:bg-primary-700 disabled:opacity-50 font-bold text-lg shadow-lg"
              >
                {{ checkoutSubmitting ? 'Processando...' : 'Confirmar Venda' }}
              </button>
              <button
                type="button"
                @click="showCheckoutModal = false"
                class="flex-1 bg-gray-300 text-gray-700 py-4 px-6 rounded-lg hover:bg-gray-400 font-bold text-lg"
              >
                Cancelar
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useProductsStore } from '@/stores/products'
import { useOrdersStore } from '@/stores/orders'
import { useCustomersStore, type Customer } from '@/stores/customers'
import AppLayout from '@/components/AppLayout.vue'

const router = useRouter()
const productsStore = useProductsStore()
const ordersStore = useOrdersStore()
const customersStore = useCustomersStore()

interface CartItem {
  product: any
  quantity: number
}

const searchQuery = ref('')
const searching = ref(false)
const searchResults = ref<any[]>([])
const cart = ref<CartItem[]>([])
const showCheckoutModal = ref(false)
const checkoutSubmitting = ref(false)
const selectedCustomer = ref<Customer | null>(null)

const checkoutForm = ref({
  customer_id: '',
  payment_method: 'money',
  discount: 0,
  notes: ''
})

const customers = ref<Customer[]>([])

const subtotal = computed(() => {
  return cart.value.reduce((sum, item) => {
    const price = parseFloat(item.product.price) || 0
    const quantity = Number(item.quantity) || 0
    return sum + (price * quantity)
  }, 0)
})

const returnableTotal = computed(() => {
  return cart.value.reduce((sum, item) => {
    if (item.product.returnable && item.product.returnable_price) {
      const returnablePrice = parseFloat(item.product.returnable_price) || 0
      const quantity = Number(item.quantity) || 0
      return sum + (returnablePrice * quantity)
    }
    return sum
  }, 0)
})

const discount = computed(() => {
  return Number(checkoutForm.value.discount) || 0
})

const total = computed(() => {
  return subtotal.value + returnableTotal.value - discount.value
})

const canUseCredit = computed(() => {
  if (!selectedCustomer.value || selectedCustomer.value.credit_limit <= 0) {
    return false
  }
  const availableCredit = selectedCustomer.value.credit_limit - selectedCustomer.value.credit_used
  return availableCredit >= total.value
})

const getCreditPercentage = (customer: Customer) => {
  if (!customer.credit_limit || customer.credit_limit <= 0) return 0
  return Math.min((customer.credit_used / customer.credit_limit) * 100, 100)
}

const formatPrice = (price: any) => {
  const numPrice = parseFloat(price) || 0
  return numPrice.toFixed(2).replace('.', ',')
}

const onCustomerChange = () => {
  const customerId = checkoutForm.value.customer_id
  if (customerId) {
    selectedCustomer.value = customers.value.find(c => c.id === parseInt(customerId)) || null
  } else {
    selectedCustomer.value = null
  }
  
  // Reset payment method if credit is not available
  if (checkoutForm.value.payment_method === 'credit' && !canUseCredit.value) {
    checkoutForm.value.payment_method = 'money'
  }
}

// Watch for changes in total to update credit validation
watch(total, () => {
  if (checkoutForm.value.payment_method === 'credit' && !canUseCredit.value) {
    checkoutForm.value.payment_method = 'money'
  }
})

const handleSearch = async () => {
  if (!searchQuery.value.trim()) {
    searchResults.value = []
    return
  }

  searching.value = true
  try {
    const result = await productsStore.searchProducts(searchQuery.value)
    if (result.success) {
      searchResults.value = result.products
    }
  } catch (error) {
    console.error('Erro ao buscar produtos:', error)
  } finally {
    searching.value = false
  }
}

const addToCart = (product: any) => {
  const existingItem = cart.value.find(item => item.product.id === product.id)
  
  if (existingItem) {
    if (existingItem.quantity < product.stock_quantity) {
      existingItem.quantity++
    }
  } else {
    cart.value.push({
      product,
      quantity: 1
    })
  }
}

const updateQuantity = (productId: number, newQuantity: number) => {
  const item = cart.value.find(item => item.product.id === productId)
  if (item) {
    if (newQuantity <= 0) {
      removeFromCart(productId)
    } else if (newQuantity <= item.product.stock_quantity) {
      item.quantity = newQuantity
    }
  }
}

const removeFromCart = (productId: number) => {
  cart.value = cart.value.filter(item => item.product.id !== productId)
}

const handleCheckout = async () => {
  if (cart.value.length === 0) return

  // Validação de crédito
  if (checkoutForm.value.payment_method === 'credit' && !canUseCredit.value) {
    alert('Crédito insuficiente para esta venda. Escolha outra forma de pagamento.')
    return
  }

  checkoutSubmitting.value = true
  
  try {
    const orderData = {
      customer_id: checkoutForm.value.customer_id ? parseInt(checkoutForm.value.customer_id) : 0,
      payment_method: checkoutForm.value.payment_method as 'money' | 'card' | 'pix' | 'credit',
      discount_amount: discount.value,
      notes: checkoutForm.value.notes,
      items: cart.value.map(item => {
        const unitPrice = parseFloat(item.product.price) || 0
        const quantity = Number(item.quantity) || 0
        return {
          product_id: item.product.id,
          quantity: quantity,
          unit_price: unitPrice
        }
      })
    }

    console.log('Criando pedido:', orderData)
    
    const result = await ordersStore.createOrder(orderData)
    
    if (result.success) {
      // Se for venda a prazo, atualizar crédito usado do cliente
      if (checkoutForm.value.payment_method === 'credit' && selectedCustomer.value) {
        await customersStore.adjustCredit(
          selectedCustomer.value.id,
          total.value,
          'charge',
          `Venda #${result.data?.id || 'N/A'}`
        )
      }
      
      showCheckoutModal.value = false
      cart.value = []
      selectedCustomer.value = null
      checkoutForm.value = {
        customer_id: '',
        payment_method: 'money',
        discount: 0,
        notes: ''
      }
      
      // Atualizar estoque dos produtos e clientes
      await Promise.all([
        productsStore.fetchProducts(),
        customersStore.fetchCustomers()
      ])
      
      const paymentMethodText = {
        'money': 'Dinheiro',
        'card': 'Cartão',
        'pix': 'PIX',
        'credit': 'A Prazo (Crédito)'
      }[checkoutForm.value.payment_method] || 'Desconhecido'
      
      alert(`Venda finalizada com sucesso!\nTotal: R$ ${formatPrice(total.value)}\nForma de pagamento: ${paymentMethodText}`)
      router.push('/orders')
    } else {
      alert('Erro ao finalizar venda: ' + (result.error || 'Erro desconhecido'))
    }
    
  } catch (error: any) {
    console.error('Erro ao finalizar venda:', error)
    
    let errorMessage = 'Erro ao finalizar venda. Tente novamente.'
    
    if (error.response?.status === 422) {
      const errors = error.response.data?.errors
      if (errors) {
        const firstError = Object.values(errors)[0]
        errorMessage = Array.isArray(firstError) ? firstError[0] : firstError
      } else {
        errorMessage = error.response.data?.message || 'Dados inválidos. Verifique as informações e tente novamente.'
      }
    } else if (error.response?.status === 500) {
      errorMessage = 'Erro interno do servidor. Tente novamente em alguns minutos.'
    } else if (error.message) {
      errorMessage = error.message
    }
    
    alert(errorMessage)
  } finally {
    checkoutSubmitting.value = false
  }
}

onMounted(async () => {
  await productsStore.fetchProducts()
  await customersStore.fetchCustomers()
  customers.value = customersStore.customers
})
</script>
