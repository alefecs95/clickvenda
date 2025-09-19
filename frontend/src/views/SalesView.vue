<template>
<div class="flex h-screen">
  <!-- Mobile Layout: App-like Structure -->
  <div class="lg:hidden flex flex-col h-full w-full bg-gray-50">
    <!-- Mobile Header: Search Bar -->
    <div class="bg-white border-b border-gray-200 p-4 shadow-sm">
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
          placeholder="Buscar produto..."
          class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent text-base"
          @input="handleSearch"
          @keydown.enter="handleSearchEnter"
          @keydown.down="handleArrowDown"
          @keydown.up="handleArrowUp"
        />
      </div>
    </div>

    <!-- Mobile Search Results Overlay -->
    <div v-if="searchQuery.trim() && filteredProducts.length > 0" class="absolute top-20 left-0 right-0 bottom-0 bg-white z-50 overflow-y-auto">
      <div class="p-4">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-bold text-gray-900">Produtos encontrados</h3>
          <button @click="searchQuery = ''" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <div class="space-y-3">
          <div
            v-for="(product, index) in filteredProducts"
            :key="product.id"
            :ref="'product-' + index"
            class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm cursor-pointer transition-all duration-200"
            :class="{ 
              'selected bg-primary-50 border-primary-300': selectedProductIndex === index,
              'disabled opacity-50 cursor-not-allowed': (product.actual_stock || product.stock_quantity) <= 0,
              'hover:bg-gray-50 hover:border-gray-300 active:bg-gray-100': (product.actual_stock || product.stock_quantity) > 0
            }"
            @click="(product.actual_stock || product.stock_quantity) > 0 ? selectProduct(product) : null"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1 min-w-0 pr-3">
                <h3 class="font-medium text-gray-900 truncate text-base leading-tight">
                  {{ product.name }}
                </h3>
                <div class="flex items-center gap-2 mt-1 text-xs text-gray-500">
                  <div class="flex items-center gap-1">
                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <span class="font-medium">{{ product.actual_stock || product.stock_quantity }}</span>
                  </div>
                  <span v-if="product.sku" class="text-gray-400">{{ product.sku }}</span>
                </div>
              </div>
              
              <div class="flex-shrink-0 text-right">
                <div class="font-bold text-gray-900 text-lg">
                  R$ {{ formatPrice(product.price) }}
                </div>
                
                <div v-if="(product.actual_stock || product.stock_quantity) <= 0" class="text-xs text-red-600 font-medium mt-1">
                  Sem Estoque
                </div>
                <div v-else class="text-xs text-primary-600 font-medium mt-1 flex items-center justify-end">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  <span>Adicionar</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Content Area -->
    <div class="flex-1 overflow-hidden flex flex-col">
      <!-- Cart Area - Expanded to fill available space -->
      <div class="flex-1 bg-white border-b border-gray-200 p-4 flex flex-col">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-lg font-bold text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293A1 1 0 004 16h16M7 13v4a2 2 0 002 2h6a2 2 0 002-2v-4m-8 2h4"></path>
            </svg>
            Carrinho
          </h3>
          <span class="bg-primary-100 text-primary-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
            {{ cart.length }} {{ cart.length === 1 ? 'item' : 'itens' }}
          </span>
        </div>

        <!-- Cart Items or Empty State -->
        <div v-if="cart.length === 0" class="flex-1 flex items-center justify-center">
          <div class="text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293A1 1 0 004 16h16M7 13v4a2 2 0 002 2h6a2 2 0 002-2v-4m-8 2h4"></path>
            </svg>
            <p class="text-gray-600 font-medium text-lg mb-2">Carrinho vazio</p>
            <p class="text-gray-500 text-sm">Pesquise e adicione produtos</p>
          </div>
        </div>

        <div v-else class="flex-1 overflow-y-auto">
          <div class="space-y-3">
            <div
              v-for="item in cart"
              :key="item.product.id"
              class="flex items-center justify-between bg-gray-50 p-4 rounded-lg"
            >
              <div class="flex-1 min-w-0">
                <h4 class="font-medium text-gray-900 text-base truncate">{{ item.product.name }}</h4>
                <p class="text-sm text-gray-500 mt-1">{{ item.quantity }}x R$ {{ formatPrice(item.product.price) }}</p>
              </div>
              <div class="flex items-center gap-3">
                <span class="text-lg font-bold text-primary-600">R$ {{ formatPrice(item.quantity * item.product.price) }}</span>
                <button
                  @click="removeFromCart(item.product.id)"
                  class="text-red-500 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 transition-colors"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Products Search Results - Hidden on Mobile -->
      <div class="hidden lg:block flex-1 overflow-y-auto p-4">
        <!-- CAIXA LIVRE State - Hidden on Mobile -->
        <div v-if="!searchQuery.trim() && cart.length === 0" class="hidden lg:flex items-center justify-center h-full">
          <div class="text-center">
            <div class="text-3xl font-bold text-green-600 mb-3">CAIXA LIVRE</div>
            <p class="text-gray-600 mb-2">Digite para pesquisar produtos</p>
            <p class="text-gray-500 text-sm">ou F7 para buscar por código de barras</p>
          </div>
        </div>

        <!-- No products found -->
        <div v-else-if="searchQuery.trim() && filteredProducts.length === 0" class="flex flex-col items-center justify-center h-64">
          <svg class="h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
          </svg>
          <p class="text-gray-600 text-lg">Nenhum produto encontrado</p>
          <p class="text-gray-500 text-sm">Tente buscar por outro termo</p>
        </div>

        <!-- Products List -->
        <div v-else-if="filteredProducts.length > 0" class="space-y-3">
          <div
            v-for="(product, index) in filteredProducts"
            :key="product.id"
            :ref="'product-' + index"
            class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm cursor-pointer transition-all duration-200"
            :class="{ 
              'selected bg-primary-50 border-primary-300': selectedProductIndex === index,
              'disabled opacity-50 cursor-not-allowed': (product.actual_stock || product.stock_quantity) <= 0,
              'hover:bg-gray-50 hover:border-gray-300 active:bg-gray-100': (product.actual_stock || product.stock_quantity) > 0
            }"
            @click="(product.actual_stock || product.stock_quantity) > 0 ? selectProduct(product) : null"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1 min-w-0 pr-3">
                <h3 class="font-medium text-gray-900 truncate text-base leading-tight">
                  {{ product.name }}
                </h3>
                <div class="flex items-center gap-2 mt-1 text-xs text-gray-500">
                  <div class="flex items-center gap-1">
                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <span class="font-medium">{{ product.actual_stock || product.stock_quantity }}</span>
                  </div>
                  <span v-if="product.sku" class="text-gray-400">{{ product.sku }}</span>
                </div>
              </div>
              
              <div class="flex-shrink-0 text-right">
                <div class="font-bold text-gray-900 text-lg">
                  R$ {{ formatPrice(product.price) }}
                </div>
                
                <div v-if="(product.actual_stock || product.stock_quantity) <= 0" class="text-xs text-red-600 font-medium mt-1">
                  Sem Estoque
                </div>
                <div v-else class="text-xs text-primary-600 font-medium mt-1 flex items-center justify-end">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  <span>Adicionar</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Bottom Bar: Totals and Action Buttons - Hidden on Mobile -->
    <div class="hidden lg:block bg-white border-t border-gray-200 p-4 shadow-lg">
      <!-- Totals -->
      <div class="flex justify-between items-center mb-4">
        <div class="flex items-center space-x-4">
          <div>
            <span class="text-gray-600 text-sm">Subtotal:</span>
            <span class="font-medium ml-1">R$ {{ formatPrice(subtotal) }}</span>
          </div>
          <div v-if="discount > 0">
            <span class="text-gray-600 text-sm">Desconto:</span>
            <span class="font-medium text-green-600 ml-1">-R$ {{ formatPrice(discount) }}</span>
          </div>
        </div>
        <div class="text-right">
          <div class="text-xs text-gray-500">TOTAL</div>
          <div class="text-xl font-bold text-primary-600">R$ {{ formatPrice(total) }}</div>
        </div>
      </div>
      
      <!-- Action Buttons -->
      <div class="flex gap-3">
        <button
          @click="showCheckoutModal = true"
          :disabled="cart.length === 0 || checkoutSubmitting"
          :class="[
            'flex-1 py-4 px-4 rounded-lg font-bold text-base transition-colors',
            cart.length > 0 && !checkoutSubmitting 
              ? 'bg-green-600 text-white hover:bg-green-700 active:bg-green-800' 
              : 'bg-gray-300 text-gray-500 cursor-not-allowed'
          ]"
        >
          {{ checkoutSubmitting ? 'Processando...' : 'FINALIZAR' }}
        </button>
        
        <button
          @click="handleCheckout('pending')"
          :disabled="cart.length === 0 || checkoutSubmitting"
          :class="[
            'flex-1 py-4 px-4 rounded-lg font-bold text-base transition-colors',
            cart.length > 0 && !checkoutSubmitting 
              ? 'bg-blue-600 text-white hover:bg-blue-700 active:bg-blue-800' 
              : 'bg-gray-300 text-gray-500 cursor-not-allowed'
          ]"
        >
          {{ checkoutSubmitting ? 'Salvando...' : 'SALVAR' }}
        </button>
      </div>
    </div>
  </div>

  <!-- Desktop: Lado Esquerdo - Produtos (70%) -->
  <div class="hidden lg:flex lg:w-[70%] bg-white border-r border-gray-200 flex-col h-full" id="products-section">
    <!-- Header de Busca -->
    <div class="p-3 border-b border-gray-200 bg-gray-50">
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
          placeholder="Buscar produto..."
          class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent text-base"
          @input="handleSearch"
          @keydown.enter="handleSearchEnter"
          @keydown.down="handleArrowDown"
          @keydown.up="handleArrowUp"
        />
      </div>
    </div>

    <!-- Lista de Produtos Desktop -->
        <div class="flex-1 overflow-y-auto p-3">
          <!-- Dashboard Principal - Quando não há busca E carrinho vazio -->
          <div v-if="!searchQuery.trim() && cart.length === 0" class="h-full flex items-center justify-center">
            <!-- Header do Dashboard -->
            <div class="text-center">
              <div class="text-4xl font-bold text-green-600 mb-4">CAIXA LIVRE</div>
              <p class="text-gray-600 text-lg mb-2">Digite para pesquisar produtos</p>
              <p class="text-gray-500 text-sm">ou F7 para buscar por código de barras</p>
            </div>
          </div>

      <!-- Nenhum produto encontrado na busca -->
      <div v-else-if="searchQuery.trim() && filteredProducts.length === 0" class="flex flex-col items-center justify-center h-64">
        <svg class="h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
        </svg>
        <p class="text-gray-600 text-lg">Nenhum produto encontrado</p>
        <p class="text-gray-500 text-sm">Tente buscar por outro termo</p>
      </div>

      <!-- Lista de produtos filtrados -->
      <div v-else-if="filteredProducts.length > 0" class="grid grid-cols-1 gap-2">
        <div
          v-for="(product, index) in filteredProducts"
          :key="product.id"
          :ref="'product-' + index"
          class="product-list-item p-2.5 md:p-3 border border-gray-200 rounded-lg cursor-pointer transition-all duration-200 touch-manipulation bg-white"
          :class="{ 
            'selected bg-primary-50 border-primary-300': selectedProductIndex === index,
            'disabled opacity-50 cursor-not-allowed': (product.actual_stock || product.stock_quantity) <= 0,
            'hover:bg-gray-50 hover:border-gray-300 active:bg-gray-100': (product.actual_stock || product.stock_quantity) > 0
          }"
          @click="(product.actual_stock || product.stock_quantity) > 0 ? selectProduct(product) : null"
          @keydown.enter="(product.actual_stock || product.stock_quantity) > 0 ? selectProduct(product) : null"
          tabindex="0"
        >
          <!-- Layout Desktop Otimizado -->
          <div class="flex items-center justify-between">
            <!-- Informações do produto -->
            <div class="flex-1 min-w-0 pr-3">
              <h3 class="product-list-name font-medium text-gray-900 truncate text-sm md:text-base leading-tight">
                {{ product.name }}
              </h3>
              <div class="product-list-details flex items-center gap-2 mt-1 text-xs text-gray-500">
                <div class="product-list-stock flex items-center gap-1">
                  <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                  </svg>
                  <span class="font-medium">{{ product.actual_stock || product.stock_quantity }}</span>
                </div>
                <span v-if="product.sku" class="product-list-sku hidden sm:inline text-gray-400">{{ product.sku }}</span>
                <span v-if="product.returnable" class="product-list-returnable text-green-600 font-medium">
                  + R$ {{ formatPrice(product.returnable_price) }}
                </span>
              </div>
            </div>
            
            <!-- Preço e status -->
            <div class="flex-shrink-0 text-right">
              <div class="product-list-price font-bold text-gray-900 text-base md:text-lg">
                R$ {{ formatPrice(product.price) }}
              </div>
              
              <div v-if="(product.actual_stock || product.stock_quantity) <= 0" class="text-xs text-red-600 font-medium mt-1">
                Sem Estoque
              </div>
              <div v-else class="text-xs text-primary-600 font-medium mt-1 flex items-center justify-end">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span class="hidden sm:inline">Adicionar</span>
                <span class="sm:hidden">Add</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Desktop: Lado Direito - Carrinho -->
  <div class="hidden lg:flex lg:w-[30%] bg-gray-50 flex-col h-full" id="cart-section">
    <!-- Header do Carrinho -->
    <div class="bg-primary-600 text-white p-3">
      <div class="flex justify-between items-center">
        <h2 class="text-base font-bold">CARRINHO</h2>
        <div class="flex items-center space-x-2">
          <span class="text-xs">{{ cart.length }} <span class="hidden sm:inline">itens</span></span>
          <button
            v-if="cart.length > 0"
            @click="clearCart"
            class="bg-primary-800 hover:bg-primary-900 px-2 py-1 rounded text-xs"
            title="Limpar Carrinho"
          >
            <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Lista de Itens do Carrinho -->
    <div class="flex-1 overflow-y-auto">
      <div v-if="cart.length === 0" class="flex flex-col items-center justify-center h-full text-gray-500 p-4">
        <svg class="w-12 h-12 md:w-16 md:h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m6 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
        </svg>
        <p class="text-base md:text-lg font-medium">Carrinho vazio</p>
        <p class="text-xs md:text-sm">Adicione produtos para continuar</p>
      </div>

      <div v-else class="divide-y divide-gray-200">
        <div
          v-for="(item, index) in cart"
          :key="item.product.id"
          class="p-2.5 hover:bg-gray-50 touch-manipulation bg-white"
          :class="{ 'bg-red-100': selectedCartItem === index }"
        >
          <div class="flex justify-between items-start mb-3">
            <div class="flex-1 pr-2">
              <h4 class="font-semibold text-gray-900 text-xs md:text-sm leading-tight">{{ item.product.name }}</h4>
              <div class="text-xs text-gray-600 mt-1">
                R$ {{ formatPrice(item.product.price) }} × {{ item.quantity }}
                <span v-if="item.product.returnable" class="text-orange-600 block md:inline md:ml-2">
                  + {{ item.quantity }} vasilhame(s)
                </span>
                <div v-if="item.quantity >= (item.product.actual_stock || item.product.stock_quantity)" class="text-red-600 text-xs font-medium mt-1 flex items-center">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                  </svg>
                  Máximo do estoque
                </div>
              </div>
            </div>
            <div class="text-right">
              <div class="font-bold text-primary-600 text-sm md:text-base">
                R$ {{ formatPrice(getItemTotal(item)) }}
              </div>
            </div>
          </div>
          
          <div class="flex justify-between items-center">
            <div class="flex items-center space-x-3">
              <button
                @click="updateQuantity(item.product.id, item.quantity - 1)"
                :disabled="item.quantity <= 1"
                class="w-8 h-8 md:w-6 md:h-6 bg-gray-200 hover:bg-gray-300 active:bg-gray-400 disabled:bg-gray-100 disabled:cursor-not-allowed rounded-full flex items-center justify-center text-sm md:text-xs touch-manipulation"
              >
                -
              </button>
              <span class="text-sm font-medium min-w-[20px] text-center">{{ item.quantity }}</span>
              <button
                @click="updateQuantity(item.product.id, item.quantity + 1)"
                :disabled="item.quantity >= (item.product.actual_stock || item.product.stock_quantity)"
                class="w-8 h-8 md:w-6 md:h-6 bg-gray-200 hover:bg-gray-300 active:bg-gray-400 disabled:bg-gray-100 disabled:cursor-not-allowed rounded-full flex items-center justify-center text-sm md:text-xs touch-manipulation"
              >
                +
              </button>
            </div>
            <button
              @click="removeFromCart(item.product.id)"
              class="text-primary-600 hover:text-primary-800 active:text-primary-900 text-xs md:text-xs py-2 px-3 md:py-1 md:px-2 touch-manipulation"
            >
              Remover
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
    

    <!-- Modal de Cliente -->
    <div v-if="showCustomerModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @mousedown.self="showCustomerModal = false; nextTick(() => searchInput?.focus())">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Selecionar Cliente</h3>
          
          <div class="mb-4">
            <input
              ref="customerSearchRef"
              v-model="customerSearchQuery"
              type="text"
              placeholder="Buscar cliente..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
            />
          </div>

          <div class="max-h-64 overflow-y-auto">
            <div
              v-for="customer in filteredCustomers"
              :key="customer.id"
              class="p-3 border-b border-gray-200 hover:bg-gray-50 cursor-pointer"
              @click="selectCustomer(customer)"
            >
              <div class="font-medium">{{ customer.name }}</div>
              <div v-if="customer.credit_limit > 0" class="text-sm text-blue-600">
                Crédito: R$ {{ formatPrice(customer.credit_limit - customer.credit_used) }}
              </div>
            </div>
          </div>
          
          <div class="mt-4 flex space-x-3">
            <button
              @click="selectCustomer(null)"
              class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400"
            >
              Sem Cliente
            </button>
            <button
              @click="showCustomerModal = false"
              class="flex-1 bg-primary-600 text-white py-2 px-4 rounded-md hover:bg-primary-700"
            >
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Checkout Modal -->
    <div v-if="showCheckoutModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-5 mx-auto p-6 border w-full max-w-4xl shadow-lg rounded-lg bg-white">
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
          
          <div>
            <div class="space-y-4">
              <!-- Customer Selection -->
              <div class="flex items-center gap-3">
                <label class="text-sm font-medium text-gray-700 flex items-center whitespace-nowrap">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                  Cliente:
                </label>
                <select
                  v-model="checkoutForm.customer_id"
                  @change="onCustomerChange"
                  class="flex-1 px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
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
              
              <!-- Payment Methods -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-4 flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                  </svg>
                  Formas de Pagamento
                </label>
                
                <!-- Toggle para múltiplos pagamentos -->
                <div class="mb-4">
                  <label class="flex items-center">
                    <input
                      type="checkbox"
                      v-model="multiplePayments"
                      class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                    />
                    <span class="ml-2 text-sm text-gray-600">Usar múltiplas formas de pagamento</span>
                  </label>
                </div>

                <!-- Pagamento único -->
                <div v-if="!multiplePayments">
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

                <!-- Múltiplos pagamentos -->
                <div v-else class="space-y-4">
                  <div v-for="(payment, index) in paymentMethods" :key="index" class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-3">
                      <h4 class="font-medium text-gray-900">Pagamento {{ index + 1 }}</h4>
                      <button
                        v-if="paymentMethods.length > 1"
                        @click="removePaymentMethod(index)"
                        type="button"
                        class="text-red-600 hover:text-red-800"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                      </button>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                      <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Forma</label>
                        <select
                          v-model="payment.method"
                          required
                          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                        >
                          <option value="money">Dinheiro</option>
                          <option value="card">Cartão</option>
                          <option value="pix">PIX</option>
                          <option v-if="selectedCustomer && selectedCustomer.credit_limit > 0" value="credit">A Prazo</option>
                        </select>
                      </div>
                      <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Valor (R$)</label>
                        <input
                          v-model="payment.amount"
                          type="number"
                          step="0.01"
                          min="0"
                          :max="total"
                          required
                          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                        />
                      </div>
                    </div>
                  </div>
                  
                  <div class="space-y-2">
                    <div class="flex justify-between items-center">
                      <button
                        @click="addPaymentMethod"
                        type="button"
                        class="text-primary-600 hover:text-primary-700 text-sm font-medium"
                      >
                        + Adicionar forma de pagamento
                      </button>
                    </div>
                    
                    <!-- Resumo de Pagamento -->
                    <div class="bg-gray-100 rounded-lg p-3">
                      <div class="flex justify-between text-sm mb-2">
                        <span class="text-gray-600">Total da venda:</span>
                        <span class="font-bold">R$ {{ formatPrice(total) }}</span>
                      </div>
                      <div class="flex justify-between text-sm mb-2">
                        <span class="text-gray-600">Total pago:</span>
                        <span class="font-bold" :class="totalPaid >= total ? 'text-green-600' : 'text-blue-600'">
                          R$ {{ formatPrice(totalPaid) }}
                        </span>
                      </div>
                      <div v-if="totalPaid < total" class="flex justify-between text-sm">
                        <span class="text-red-600 font-medium">Falta pagar:</span>
                        <span class="font-bold text-red-600">R$ {{ formatPrice(total - totalPaid) }}</span>
                      </div>
                      <div v-else-if="totalPaid > total" class="flex justify-between text-sm">
                        <span class="text-orange-600 font-medium">Troco:</span>
                        <span class="font-bold text-orange-600">R$ {{ formatPrice(totalPaid - total) }}</span>
                      </div>
                      <div v-else class="text-center text-sm">
                        <span class="text-green-600 font-bold">✓ Pagamento completo</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Seção de Vasilhames -->
              <div v-if="vasilhamesPorTipo.length > 0" class="border-t border-gray-200 pt-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                  </svg>
                  Controle de Vasilhames por Modelo
                </h4>
                
                <!-- Lista de modelos de vasilhames -->
                <div class="space-y-4">
                  <div 
                    v-for="tipo in vasilhamesPorTipo" 
                    :key="tipo.modeloId"
                    class="bg-orange-50 border border-orange-200 rounded-lg p-4"
                  >
                    <div class="flex items-center justify-between mb-3">
                      <h5 class="font-semibold text-orange-900 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        {{ tipo.nome }}
                      </h5>
                      <span class="text-sm font-medium text-orange-700 bg-orange-200 px-2 py-1 rounded">
                        Necessários: {{ tipo.necessarios }}
                      </span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                      <div>
                        <label class="block text-sm font-medium text-orange-900 mb-2">
                          Quantidade que o cliente possui:
                        </label>
                        <input
                          :value="tipo.informados"
                          @input="atualizarVasilhamesInformados(tipo.modeloId, Number(($event.target as HTMLInputElement)?.value || '0'))"
                          @keydown.enter="focusNextVasilhameInput($event)"
                          type="number"
                          min="0"
                          placeholder="0"
                          class="block w-full px-3 py-2 border border-orange-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        />
                      </div>
                      
                      <div class="text-center">
                        <span class="block text-sm font-medium text-orange-900 mb-2">Saldo:</span>
                        <span 
                          class="text-2xl font-bold px-3 py-1 rounded"
                          :class="(tipo.informados - tipo.necessarios) >= 0 ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100'"
                        >
                          {{ (tipo.informados - tipo.necessarios) >= 0 ? '+' : '' }}{{ tipo.informados - tipo.necessarios }}
                        </span>
                      </div>
                      
                      <div class="text-right">
                        <div v-if="(tipo.informados - tipo.necessarios) < 0" class="text-sm">
                          <div class="flex items-center justify-end text-red-600">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <span class="font-medium">Débito</span>
                          </div>
                          <span class="text-xs text-red-700">
                            {{ Math.abs(tipo.informados - tipo.necessarios) }} {{ tipo.nome.toLowerCase() }}(s) em falta
                          </span>
                        </div>
                        <div v-else-if="(tipo.informados - tipo.necessarios) > 0" class="text-sm">
                          <div class="flex items-center justify-end text-green-600">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="font-medium">Sobra</span>
                          </div>
                          <span class="text-xs text-green-700">
                            {{ tipo.informados - tipo.necessarios }} {{ tipo.nome.toLowerCase() }}(s) de sobra
                          </span>
                        </div>
                        <div v-else class="text-sm">
                          <div class="flex items-center justify-end text-blue-600">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="font-medium">Equilibrado</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Resumo geral -->
                <div v-if="vasilhamesPorTipo.length > 1" class="mt-4 p-4 bg-gray-100 border border-gray-300 rounded-lg">
                  <div class="flex justify-between items-center">
                    <span class="font-medium text-gray-900">Saldo Total Geral:</span>
                    <span 
                      class="text-xl font-bold"
                      :class="saldoVasilhames >= 0 ? 'text-green-600' : 'text-red-600'"
                    >
                      {{ saldoVasilhames >= 0 ? '+' : '' }}{{ saldoVasilhames }}
                    </span>
                  </div>
                  
                  <div v-if="tiposComDebito.length > 0" class="mt-3 p-3 bg-red-100 border border-red-300 rounded-md">
                    <div class="flex items-start">
                      <svg class="w-5 h-5 text-red-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                      </svg>
                      <div>
                        <span class="text-sm font-medium text-red-800">
                          Débitos que serão registrados:
                        </span>
                        <ul class="text-xs text-red-700 mt-1 space-y-1">
                          <li v-for="tipo in tiposComDebito" :key="tipo.modeloId">
                            • {{ Math.abs(tipo.informados - tipo.necessarios) }} {{ tipo.nome.toLowerCase() }}(s)
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
                  </div>
                </div>
              </div>
              
              <!-- Credit Validation para múltiplos pagamentos -->
              <div v-if="multiplePayments && creditAmount > 0 && selectedCustomer" class="p-4 rounded-lg" :class="canUseCreditForAmount ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <svg v-if="canUseCreditForAmount" class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <svg v-else class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm font-medium" :class="canUseCreditForAmount ? 'text-green-800' : 'text-red-800'">
                      {{ canUseCreditForAmount ? 'Crédito suficiente para o valor solicitado' : 'Crédito insuficiente para o valor solicitado' }}
                    </p>
                    <p class="text-xs mt-1" :class="canUseCreditForAmount ? 'text-green-600' : 'text-red-600'">
                      Valor no crédito: R$ {{ formatPrice(creditAmount) }} | Disponível: R$ {{ formatPrice(selectedCustomer.credit_limit - selectedCustomer.credit_used) }}
                    </p>
                  </div>
                </div>
              </div>
              
              <!-- Credit Validation para pagamento único -->
              <div v-else-if="!multiplePayments && checkoutForm.payment_method === 'credit' && selectedCustomer" class="p-4 rounded-lg" :class="canUseCredit ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
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

              <!-- Campo para valor entregue e cálculo de troco/restante -->
              <div v-if="!multiplePayments" class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                <label class="block text-sm font-medium text-gray-700 mb-3 flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  Valor Recebido
                </label>
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <input
                      ref="amountReceivedInput"
                      v-model="checkoutForm.amount_received"
                      type="number"
                      step="0.01"
                      min="0"
                      max="999999.99"
                      class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                      placeholder="0,00"
                      @input="formatAmountInput"
                    />
                  </div>
                  <div class="flex items-center justify-center">
                    <span class="text-sm font-medium" :class="changeDue >= 0 ? 'text-green-600' : 'text-red-600'">
                      {{ changeDue >= 0 ? `Troco: R$ ${formatPrice(changeDue)}` : `Falta: R$ ${formatPrice(-changeDue)}` }}
                    </span>
                  </div>
                </div>
                
                <!-- Sugestão para usar múltiplos pagamentos quando valor for insuficiente -->
                <div v-if="changeDue < 0" class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                  <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span class="text-sm text-yellow-800">Valor insuficiente</span>
                  </div>
                  <p class="text-xs text-yellow-700 mt-1">
                    Deseja usar múltiplas formas de pagamento para completar o valor?
                  </p>
                  <button
                    @click="enableMultiplePayments"
                    class="mt-2 px-3 py-1 bg-yellow-600 hover:bg-yellow-700 text-white text-xs font-medium rounded-md transition-colors"
                  >
                    Ativar Múltiplos Pagamentos
                  </button>
                </div>
                
                <p class="text-xs text-gray-500 mt-2">
                  Informe o valor recebido para calcular troco ou valor restante
                </p>
              </div>
              
              <!-- Ajuda de atalhos de teclado -->
              <div class="p-3 bg-gray-100 rounded-lg border border-gray-200">
                <div class="flex items-center justify-between flex-wrap gap-2 text-xs text-gray-600">
                  <div class="flex items-center">
                    <kbd class="px-1 py-0.5 bg-white border border-gray-300 rounded text-xs font-mono">F1-F4</kbd>
                    <span class="ml-1">Pagamento</span>
                  </div>
                  <div class="flex items-center">
                    <kbd class="px-1 py-0.5 bg-white border border-gray-300 rounded text-xs font-mono">F5</kbd>
                    <span class="ml-1">Valor entregue</span>
                  </div>
                  <div class="flex items-center">
                    <kbd class="px-1 py-0.5 bg-white border border-gray-300 rounded text-xs font-mono">F9</kbd>
                    <span class="ml-1">Finalizar</span>
                  </div>
                  <div class="flex items-center">
                    <kbd class="px-1 py-0.5 bg-white border border-gray-300 rounded text-xs font-mono">F10</kbd>
                    <span class="ml-1">Pendente</span>
                  </div>
                  <div class="flex items-center">
                    <kbd class="px-1 py-0.5 bg-white border border-gray-300 rounded text-xs font-mono">ESC</kbd>
                    <span class="ml-1">Fechar</span>
                  </div>
                </div>
              </div>
              
              <!-- Discount -->
              <div class="flex items-center gap-3">
                <label class="text-sm font-medium text-gray-700 flex items-center whitespace-nowrap">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                  </svg>
                  Desconto:
                </label>
                <input
                  v-model="checkoutForm.discount"
                  type="number"
                  step="0.01"
                  min="0"
                  :max="subtotal"
                  placeholder="R$ 0,00"
                  class="flex-1 px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
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
            
            <div class="mt-8 space-y-3">
              <!-- Botões principais -->
              <div class="hidden md:flex space-x-3">
              <button
                  type="button"
                  @click="handleCheckout('completed')"
                  :disabled="checkoutSubmitting || (multiplePayments && Math.abs(totalPaid - total) > 0.01) || (!multiplePayments && checkoutForm.payment_method === 'credit' && !canUseCredit) || (multiplePayments && !canUseCreditForAmount)"
                  class="flex-1 bg-primary-600 text-white py-4 px-6 rounded-lg hover:bg-primary-700 disabled:opacity-50 font-bold text-lg shadow-lg"
                >
                  <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  {{ checkoutSubmitting ? 'Processando...' : 'Finalizar Venda' }}
                </button>
                <button
                  type="button"
                  @click="handleCheckout('pending')"
                :disabled="checkoutSubmitting"
                  class="flex-1 bg-orange-600 text-white py-4 px-6 rounded-lg hover:bg-orange-700 disabled:opacity-50 font-bold text-lg shadow-lg"
              >
                  <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4m-1 1H8a1 1 0 00-1 1v8a2 2 0 002 2h6a2 2 0 002-2V9a1 1 0 00-1-1z"></path>
                  </svg>
                  {{ checkoutSubmitting ? 'Salvando...' : 'Salvar Pendente' }}
              </button>
              </div>
              
              <!-- Botão cancelar -->
              <button
                type="button"
                @click="showCheckoutModal = false"
                class="w-full bg-gray-300 text-gray-700 py-3 px-6 rounded-lg hover:bg-gray-400 font-medium text-lg"
              >
                Cancelar
              </button>
            </div>
          </div>
        
      
    

   

    <!-- Modal de Quantidade - Mobile Optimized -->
    <div v-if="showQuantityModal && selectedProduct" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @mousedown.self="closeQuantityModal">
      <div class="relative top-10 md:top-20 mx-auto p-4 md:p-6 border w-full max-w-sm md:max-w-md shadow-lg rounded-lg bg-white m-4">
        <div class="text-center">
          <!-- Título -->
          <h3 class="text-lg md:text-xl font-bold text-gray-900 mb-4">
            Adicionar ao Carrinho
          </h3>
          
          <!-- Informações do Produto -->
          <div class="bg-gray-50 rounded-lg p-3 md:p-4 mb-6 text-left">
            <h4 class="font-semibold text-gray-900 text-sm md:text-base">{{ selectedProduct.name }}</h4>
            <p v-if="selectedProduct.description" class="text-xs md:text-sm text-gray-600 mt-1">{{ selectedProduct.description }}</p>
            <div class="flex justify-between items-center mt-3">
              <span class="text-lg md:text-xl font-bold text-primary-600">R$ {{ formatPrice(selectedProduct.price) }}</span>
              <span class="text-xs md:text-sm text-green-600">
                Estoque: {{ selectedProduct.actual_stock || selectedProduct.stock_quantity }}
              </span>
            </div>
          </div>
          
          <!-- Input de Quantidade - Touch Optimized -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">
              Quantidade
            </label>
            <div class="flex items-center justify-center space-x-4">
              <button
                @click="decreaseQuantity"
                :disabled="quantityInput <= 1"
                class="w-12 h-12 md:w-10 md:h-10 bg-gray-200 hover:bg-gray-300 active:bg-gray-400 disabled:bg-gray-100 disabled:cursor-not-allowed rounded-full flex items-center justify-center touch-manipulation"
              >
                <svg class="w-6 h-6 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                </svg>
              </button>
              <input
                ref="quantityInputRef"
                v-model.number="quantityInput"
                type="number"
                min="1"
                :max="selectedProduct.actual_stock || selectedProduct.stock_quantity"
                class="w-24 md:w-20 text-center text-xl md:text-lg font-bold border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent py-3 md:py-2"
                @keydown.enter="addToCartWithQuantity"
                @input="validateQuantityInput"
              />
              <button
                @click="increaseQuantity"
                :disabled="quantityInput >= (selectedProduct.actual_stock || selectedProduct.stock_quantity)"
                class="w-12 h-12 md:w-10 md:h-10 bg-gray-200 hover:bg-gray-300 active:bg-gray-400 disabled:bg-gray-100 disabled:cursor-not-allowed rounded-full flex items-center justify-center touch-manipulation"
              >
                <svg class="w-6 h-6 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
              </button>
            </div>
            
            <!-- Aviso de estoque -->
            <div v-if="quantityInput > (selectedProduct.actual_stock || selectedProduct.stock_quantity)" class="mt-2 text-center">
              <div class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                Quantidade excede o estoque disponível ({{ selectedProduct.actual_stock || selectedProduct.stock_quantity }})
              </div>
            </div>
            
            <!-- Informação de estoque disponível -->
            <div class="mt-2 text-center text-sm text-gray-600">
              Estoque disponível: <span class="font-semibold">{{ selectedProduct.actual_stock || selectedProduct.stock_quantity }}</span>
            </div>
          </div>
          
          <!-- Botões - Touch Optimized -->
          <div class="flex space-x-3">
            <button
              @click="closeQuantityModal"
              class="flex-1 bg-gray-300 text-gray-700 py-4 md:py-3 px-4 rounded-lg font-bold hover:bg-gray-400 active:bg-gray-500 touch-manipulation text-base"
            >
              Cancelar
            </button>
            <button
              @click="addToCartWithQuantity"
              :disabled="quantityInput > (selectedProduct.actual_stock || selectedProduct.stock_quantity) || quantityInput < 1"
              :class="[
                'flex-1 py-4 md:py-3 px-4 rounded-lg font-bold touch-manipulation text-base',
                quantityInput > (selectedProduct.actual_stock || selectedProduct.stock_quantity) || quantityInput < 1
                  ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                  : 'bg-primary-600 text-white hover:bg-primary-700 active:bg-primary-800'
              ]"
            >
              Adicionar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Sucesso -->
    <div v-if="showSuccessModal && completedSale" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center p-4">
      <div class="relative w-full max-w-lg bg-white rounded-lg shadow-lg max-h-[90vh] overflow-y-auto">
        <!-- Botão X para fechar -->
        <button
          @click="closeSuccessModal"
          class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600 z-10"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
        
        <div class="p-4 text-center">
          <!-- Ícone de Sucesso -->
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-3">
            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
          </div>
          
          <!-- Título -->
          <h3 class="text-lg font-bold text-gray-900 mb-3">
            {{ completedSale.status === 'completed' ? 'Venda Finalizada!' : 'Venda Salva!' }}
          </h3>
          
          <!-- Informações da Venda -->
          <div class="bg-gray-50 rounded-lg p-3 mb-4 text-left">
            <div class="grid grid-cols-2 gap-3 text-sm">
              <div>
                <span class="text-gray-600">Pedido:</span>
                <span class="font-bold ml-2">#{{ completedSale.id }}</span>
              </div>
              <div>
                <span class="text-gray-600">Data:</span>
                <span class="font-bold ml-2">{{ completedSale.date.toLocaleDateString('pt-BR') }}</span>
              </div>
              <div>
                <span class="text-gray-600">Hora:</span>
                <span class="font-bold ml-2">{{ completedSale.date.toLocaleTimeString('pt-BR') }}</span>
              </div>
              <div>
                <span class="text-gray-600">Status:</span>
                <span class="font-bold ml-2" :class="completedSale.status === 'completed' ? 'text-green-600' : 'text-orange-600'">
                  {{ completedSale.status === 'completed' ? 'Finalizada' : 'Pendente' }}
                </span>
              </div>
            </div>
            
            <!-- Cliente -->
            <div v-if="completedSale.customer" class="mt-3 pt-3 border-t border-gray-200">
              <span class="text-gray-600">Cliente:</span>
              <span class="font-bold ml-2">{{ completedSale.customer.name }}</span>
            </div>
            
            <!-- Total -->
            <div class="mt-3 pt-3 border-t border-gray-200">
              <div class="flex justify-between items-center">
                <span class="text-base font-bold text-gray-900">Total:</span>
                <span class="text-xl font-bold text-primary-600">R$ {{ formatPrice(completedSale.total) }}</span>
              </div>
            </div>
            
            <!-- Formas de Pagamento -->
            <div class="mt-3 pt-3 border-t border-gray-200">
              <span class="text-gray-600 block mb-2">Pagamento:</span>
              <div v-if="completedSale.paymentMethods" class="space-y-1">
                <div v-for="(payment, index) in completedSale.paymentMethods" :key="index" class="flex justify-between text-sm">
                  <span>{{ getPaymentMethodText(payment.method) }}</span>
                  <span class="font-medium">R$ {{ formatPrice(payment.amount) }}</span>
                </div>
              </div>
              <div v-else>
                <span class="font-medium">{{ getPaymentMethodText(completedSale.paymentMethod) }}</span>
              </div>
            </div>
          </div>
          
          <!-- Rodapé Personalizado da Loja -->
          <div v-if="settingsStore.storeSettings.receipt_footer" class="mt-3 pt-3 border-t border-gray-200">
            <p class="text-xs text-gray-600 text-center italic">
              {{ settingsStore.storeSettings.receipt_footer }}
            </p>
            <div v-if="settingsStore.storeSettings.whatsapp" class="mt-1 text-xs text-gray-500 text-center">
              WhatsApp: {{ settingsStore.storeSettings.whatsapp }}
            </div>
          </div>
          
          <!-- Botões de Ação -->
          <div class="space-y-2">
            <div class="grid grid-cols-2 gap-2">
              <button
                @click="printSale"
                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg font-medium text-sm"
              >
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Imprimir
              </button>
              <button
                @click="sendWhatsApp"
                class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg font-medium text-sm"
              >
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                WhatsApp
              </button>
            </div>
            
            <button
              @click="closeSuccessModal"
              class="w-full bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg font-medium"
            >
              Nova Venda
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Bottom Navigation -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 md:hidden z-50" id="mobile-nav">
      <!-- Valores acima dos botões -->
      <div class="px-4 py-2 border-b border-gray-100">
        <div class="flex justify-between items-center text-sm">
          <div class="flex items-center space-x-4">
            <div>
              <span class="text-gray-600">Subtotal:</span>
              <span class="font-medium ml-1">R$ {{ formatPrice(subtotal) }}</span>
            </div>
            <div v-if="discount > 0">
              <span class="text-gray-600">Desconto:</span>
              <span class="font-medium text-green-600 ml-1">-R$ {{ formatPrice(discount) }}</span>
            </div>
          </div>
          <div class="text-right">
            <div class="text-xs text-gray-500">TOTAL</div>
            <div class="text-lg font-bold text-primary-600">R$ {{ formatPrice(total) }}</div>
          </div>
        </div>
      </div>
      
      <!-- Botões -->
      <div class="flex">
        <!-- Checkout / Finalizar -->
        <button
          @click="showCheckoutModal ? handleCheckout('completed') : (showCheckoutModal = true)"
          :disabled="cart.length === 0 || checkoutSubmitting"
          :class="[
            'flex-1 flex flex-col items-center justify-center py-3 px-2 touch-manipulation',
            cart.length > 0 && !checkoutSubmitting ? 'text-green-600 bg-green-50 hover:bg-green-100 active:bg-green-200' : 'text-gray-400 bg-gray-50'
          ]"
        >
          <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span class="text-xs font-medium">
            {{ checkoutSubmitting ? 'Processando...' : (showCheckoutModal ? 'Finalizar' : 'Checkout') }}
          </span>
        </button>
        
        <!-- Salvar Pedido -->
        <button
          @click="handleCheckout('pending')"
          :disabled="cart.length === 0 || checkoutSubmitting"
          :class="[
            'flex-1 flex flex-col items-center justify-center py-3 px-2 touch-manipulation',
            cart.length > 0 && !checkoutSubmitting ? 'text-blue-600 bg-blue-50 hover:bg-blue-100 active:bg-blue-200' : 'text-gray-400 bg-gray-50'
          ]"
        >
          <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 0V6a2 2 0 00-2-2H9a2 2 0 00-2 2v1m1 0h4m-4 0a1 1 0 011-1h2a1 1 0 011 1m-6 0V9a1 1 0 011-1h4a1 1 0 011 1v2.586a1 1 0 01-.293.707l-2 2a1 1 0 01-.414.293"></path>
          </svg>
          <span class="text-xs font-medium">{{ checkoutSubmitting ? 'Salvando...' : 'Salvar Pedido' }}</span>
        </button>
      </div>
    </div>

    <!-- Barra de Resumo Financeiro Fixa -->
    <div class="fixed bottom-12 left-0 right-0 bg-white border-t border-gray-200 px-4 py-3 shadow-lg z-40">
      <div class="flex justify-between items-center">
        <!-- Resumo Financeiro -->
        <div class="flex items-center space-x-6">
          <div class="text-sm">
            <span class="text-gray-600">Subtotal:</span>
            <span class="font-medium ml-2">R$ {{ formatPrice(subtotal) }}</span>
          </div>
          <div class="text-sm">
            <span class="text-gray-600">Desconto:</span>
            <span class="font-medium text-green-600 ml-2">-R$ {{ formatPrice(discount) }}</span>
          </div>
          <div class="text-xl font-bold">
            <span class="text-gray-900">TOTAL:</span>
            <span class="text-primary-600 ml-2 text-2xl">R$ {{ formatPrice(total) }}</span>
          </div>
          <div v-if="cart.length === 0" class="text-xs text-gray-500 italic">
            Carrinho vazio
          </div>
        </div>

        <!-- Botões de Ação - Desktop Only -->
        <div class="hidden md:flex items-center space-x-3">
          <button 
            @click="() => { showCustomerModal = true; nextTick(() => customerSearchRef?.focus()) }" 
            class="bg-gray-600 hover:bg-gray-700 active:bg-gray-800 text-white px-4 py-2 rounded font-bold text-sm flex items-center touch-manipulation" 
            title="Selecionar Cliente" 
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span>CLIENTE</span>
          </button>
          <button 
            @click="cart.length > 0 ? showCheckoutModal = true : null" 
            :class="cart.length > 0 ? 'bg-primary-600 hover:bg-primary-700 active:bg-primary-800' : 'bg-gray-400 cursor-not-allowed'" 
            class="text-white px-6 py-2 rounded font-bold text-sm flex items-center touch-manipulation" 
            :title="cart.length > 0 ? 'Finalizar Venda' : 'Adicione produtos ao carrinho'" 
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
            </svg>
            <span>{{ cart.length > 0 ? 'FINALIZAR' : 'FINALIZAR' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Barra Fixa do Rodapé - Hidden on Mobile -->
    <div class="hidden lg:block fixed bottom-0 left-0 right-0 bg-primary-600 text-white px-4 py-2 z-50 shadow-lg">
      <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
          <span class="font-bold">{{ settingsStore.storeSettings.name || 'Teste Loja 35' }}</span>
          <span class="text-sm">{{ currentDateTime }}</span>
        </div>
        <div class="flex items-center space-x-4 text-sm">
          <span>F6: Cliente</span>
          <span>F7: Buscar</span>
          <span>F8: Limpar</span>
          <span>F9: Finalizar</span>
        </div>
      </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { useProductsStore } from '@/stores/products'
import { useOrdersStore } from '@/stores/orders'
import { useCustomersStore, type Customer } from '@/stores/customers'
import { useSettingsStore } from '@/stores/settings'
import { useVasilhamesStore } from '@/stores/vasilhames'
const productsStore = useProductsStore()
const ordersStore = useOrdersStore()
const customersStore = useCustomersStore()
const settingsStore = useSettingsStore()
const vasilhamesStore = useVasilhamesStore()

interface Product {
  id: number
  name: string
  description?: string | null
  price: number
  stock_quantity: number
  actual_stock?: number
  sku?: string | null
  barcode?: string | null
  available: boolean
  returnable: boolean
  returnable_price?: number | null
  returnable_quantity?: number
  vasilhame_model_id?: number | null
  parent_product_id?: number | null
}

interface CartItem {
  product: Product
  quantity: number
}

const searchInput = ref<HTMLInputElement>()
const customerSearchRef = ref<HTMLInputElement>()
const quantityInputRef = ref<HTMLInputElement>()
const amountReceivedInput = ref<HTMLInputElement>()

// Mobile navigation
const mobileActiveTab = ref('products')

// Mobile navigation methods - removidas pois não são utilizadas no template

// Horário em tempo real
const currentDateTime = ref('')

const searchQuery = ref('')
const searching = ref(false)
const cart = ref<CartItem[]>([])
const showCheckoutModal = ref(false)
const showCustomerModal = ref(false)
const showSuccessModal = ref(false)
const checkoutSubmitting = ref(false)
const selectedCustomer = ref<Customer | null>(null)
const selectedCartItem = ref(-1)
const selectedProductIndex = ref(-1)
const customerSearchQuery = ref('')
const multiplePayments = ref(false)
const completedSale = ref<any>(null)

// Modal de quantidade
const showQuantityModal = ref(false)
const selectedProduct = ref<Product | null>(null)
const quantityInput = ref(1)

const checkoutForm = ref({
  customer_id: '',
  payment_method: 'money',
  discount: 0,
  notes: '',
  amount_received: 0
})

const paymentMethods = ref([
  { method: 'money', amount: 0 }
])

const customers = ref<Customer[]>([])

const subtotal = computed(() => {
  return cart.value.reduce((sum, item) => {
    const price = parseFloat(item.product.price.toString()) || 0
    const quantity = Number(item.quantity) || 0
    return sum + (price * quantity)
  }, 0)
})

// returnableTotal removido pois não é utilizado no template

// Cálculo de vasilhames necessários agrupados por modelo específico
const vasilhamesPorTipo = computed(() => {
  const grupos = new Map()
  
  cart.value.forEach(item => {
    if (item.product.returnable && item.product.returnable_quantity && item.product.vasilhame_model_id) {
      const modeloId = item.product.vasilhame_model_id
      const vasilhamesPorProduto = Number(item.product.returnable_quantity) || 0
      const quantity = Number(item.quantity) || 0
      const totalNecessario = vasilhamesPorProduto * quantity
      
      if (grupos.has(modeloId)) {
        const grupo = grupos.get(modeloId)
        grupo.necessarios += totalNecessario
      } else {
        // Buscar o nome correto do modelo no store de vasilhames
        const modelo = vasilhamesStore.modelos.find(m => m.id === modeloId)
        const nomeModelo = modelo ? modelo.name : `Modelo ${modeloId}`
        
        grupos.set(modeloId, {
          modeloId,
          nome: nomeModelo,
          necessarios: totalNecessario,
          informados: 0
        })
      }
    }
  })
  
  return Array.from(grupos.values())
})

// Vasilhames informados pelo cliente (para compatibilidade - será removido)
const vasilhamesInformados = ref(0)

// Função para atualizar vasilhames informados por tipo
const atualizarVasilhamesInformados = (modeloId: number, quantidade: number) => {
  const tipo = vasilhamesPorTipo.value.find(t => t.modeloId === modeloId)
  if (tipo) {
    tipo.informados = quantidade
  }
}

// Função para navegar entre campos de vasilhames com Enter
const focusNextVasilhameInput = (event: KeyboardEvent) => {
  const currentInput = event.target as HTMLInputElement
  const allVasilhameInputs = document.querySelectorAll('input[type="number"][placeholder="0"]')
  const currentIndex = Array.from(allVasilhameInputs).indexOf(currentInput)
  
  if (currentIndex >= 0 && currentIndex < allVasilhameInputs.length - 1) {
    const nextInput = allVasilhameInputs[currentIndex + 1] as HTMLInputElement
    nextInput.focus()
    nextInput.select()
  } else if (currentIndex === allVasilhameInputs.length - 1) {
    // Se for o último campo de vasilhame, focar no campo de desconto
    const discountInput = document.querySelector('input[type="number"][step="0.01"]') as HTMLInputElement
    if (discountInput) {
      discountInput.focus()
      discountInput.select()
    }
  }
}

// Saldo total de vasilhames
const saldoVasilhames = computed(() => {
  return vasilhamesPorTipo.value.reduce((sum, tipo) => {
    return sum + (tipo.informados - tipo.necessarios)
  }, 0)
})

// Verifica se há tipos com saldo negativo
const tiposComDebito = computed(() => {
  return vasilhamesPorTipo.value.filter(tipo => (tipo.informados - tipo.necessarios) < 0)
})

const discount = computed(() => {
  return Number(checkoutForm.value.discount) || 0
})

const total = computed(() => {
  return subtotal.value - discount.value
})

// Produtos filtrados em tempo real
const filteredProducts = computed(() => {
  console.log('SalesView: filteredProducts - Total de produtos no store:', productsStore.products.length)
  console.log('SalesView: filteredProducts - Query de busca:', searchQuery.value)
  
  // Se não há busca, retorna array vazio para não exibir produtos
  if (!searchQuery.value.trim()) {
    console.log('SalesView: filteredProducts - Sem busca, retornando array vazio')
    return []
  }
  
  const query = searchQuery.value.toLowerCase().trim()
  const filtered = productsStore.products.filter(product => 
    product.name.toLowerCase().includes(query) ||
    product.description?.toLowerCase().includes(query) ||
    product.sku?.toLowerCase().includes(query) ||
    product.barcode?.toLowerCase().includes(query)
  )
  
  console.log('SalesView: filteredProducts - Produtos filtrados:', filtered.length)
  return filtered
})

const totalPaid = computed(() => {
  return paymentMethods.value.reduce((sum, payment) => {
    return sum + (Number(payment.amount) || 0)
  }, 0)
})

const creditAmount = computed(() => {
  return paymentMethods.value
    .filter(payment => payment.method === 'credit')
    .reduce((sum, payment) => sum + (Number(payment.amount) || 0), 0)
})

const canUseCredit = computed(() => {
  if (!selectedCustomer.value || selectedCustomer.value.credit_limit <= 0) {
    return false
  }
  const availableCredit = selectedCustomer.value.credit_limit - selectedCustomer.value.credit_used
  
  // Verificar se já está com limite estourado
  if (selectedCustomer.value.credit_used > selectedCustomer.value.credit_limit) {
    return false // Limite já estourado, não pode usar mais crédito
  }
  
  return availableCredit >= total.value
})

const canUseCreditForAmount = computed(() => {
  if (!selectedCustomer.value || selectedCustomer.value.credit_limit <= 0 || creditAmount.value <= 0) {
    return true // Se não há crédito sendo usado, não há problema
  }
  const availableCredit = selectedCustomer.value.credit_limit - selectedCustomer.value.credit_used
  return availableCredit >= creditAmount.value
})

const changeDue = computed(() => {
  const amountReceived = Number(checkoutForm.value.amount_received) || 0
  if (amountReceived <= 0) return 0
  
  if (multiplePayments.value) {
    // Para múltiplos pagamentos, calcular troco baseado no total pago
    return amountReceived - totalPaid.value
  } else {
    // Para pagamento único, calcular troco baseado no total da venda
    return amountReceived - total.value
  }
})

const filteredCustomers = computed(() => {
  if (!customerSearchQuery.value) {
    return customers.value.slice(0, 10)
  }
  return customers.value.filter(customer => 
    customer.name.toLowerCase().includes(customerSearchQuery.value.toLowerCase())
  ).slice(0, 10)
})

const getCreditPercentage = (customer: Customer) => {
  if (!customer.credit_limit || customer.credit_limit <= 0) return 0
  return Math.min((customer.credit_used / customer.credit_limit) * 100, 100)
}

const formatPrice = (price: any) => {
  const numPrice = parseFloat(price) || 0
  return numPrice.toFixed(2).replace('.', ',')
}

// Função para atualizar horário em tempo real
const updateDateTime = () => {
  const now = new Date()
  const day = String(now.getDate()).padStart(2, '0')
  const month = String(now.getMonth() + 1).padStart(2, '0')
  const year = now.getFullYear()
  const hours = String(now.getHours()).padStart(2, '0')
  const minutes = String(now.getMinutes()).padStart(2, '0')
  const seconds = String(now.getSeconds()).padStart(2, '0')
  
  currentDateTime.value = `${day}/${month}/${year} - ${hours}:${minutes}:${seconds}`
}

const getItemTotal = (item: CartItem) => {
  const productPrice = parseFloat(item.product.price.toString()) || 0
  const quantity = Number(item.quantity) || 0
  return productPrice * quantity
}

const clearCart = () => {
  if (confirm('Tem certeza que deseja limpar o carrinho?')) {
    cart.value = []
  }
}

const addPaymentMethod = () => {
  const remainingAmount = total.value - totalPaid.value
  paymentMethods.value.push({
    method: 'money',
    amount: remainingAmount > 0 ? remainingAmount : 0
  })
}

const removePaymentMethod = (index: number) => {
  if (paymentMethods.value.length > 1) {
    paymentMethods.value.splice(index, 1)
  }
}

const enableMultiplePayments = () => {
  multiplePayments.value = true
  paymentMethods.value = [
    { method: 'money', amount: Number(checkoutForm.value.amount_received) || 0 }
  ]
  // Adicionar um segundo método de pagamento vazio
  paymentMethods.value.push({ method: 'money', amount: 0 })
}

const formatAmountInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  let value = target.value
  
  // Remover caracteres não numéricos exceto ponto e vírgula
  value = value.replace(/[^0-9.,]/g, '')
  
  // Substituir vírgula por ponto
  value = value.replace(',', '.')
  
  // Limitar a 2 casas decimais
  const parts = value.split('.')
  if (parts.length > 2) {
    value = parts[0] + '.' + parts[1]
  }
  if (parts[1] && parts[1].length > 2) {
    value = parts[0] + '.' + parts[1].substring(0, 2)
  }
  
  // Limitar valor máximo
  const numValue = parseFloat(value)
  if (numValue > 999999.99) {
    value = '999999.99'
  }
  
  target.value = value
  checkoutForm.value.amount_received = parseFloat(value) || 0
}

const resetPaymentMethods = () => {
  paymentMethods.value = [{ method: 'money', amount: 0 }]
  multiplePayments.value = false
}

const selectCustomer = (customer: Customer | null) => {
  selectedCustomer.value = customer
  checkoutForm.value.customer_id = customer ? customer.id.toString() : ''
  showCustomerModal.value = false
  customerSearchQuery.value = ''
  
  // Aplicar limite de crédito padrão se o cliente não tiver limite definido
  if (customer && customer.credit_limit === 0) {
    const defaultCreditLimit = typeof settingsStore.salesSettings.default_credit_limit === 'number' 
      ? settingsStore.salesSettings.default_credit_limit 
      : parseFloat(settingsStore.salesSettings.default_credit_limit as string) || 500
    
    // Atualizar o cliente localmente (não persiste no banco ainda)
    customer.credit_limit = defaultCreditLimit
  }
}

// Função para selecionar produto e abrir modal de quantidade
const selectProduct = (product: Product) => {
  if ((product.actual_stock || product.stock_quantity) <= 0) return
  
  selectedProduct.value = product
  quantityInput.value = 1
  showQuantityModal.value = true
}

// Watch para manter foco no campo de quantidade
watch(showQuantityModal, (newValue) => {
  if (newValue) {
    nextTick(() => {
      quantityInputRef.value?.focus()
      quantityInputRef.value?.select()
    })
  }
})

// Watch para manter foco no campo de valor recebido quando modal de checkout abrir
watch(showCheckoutModal, (newValue) => {
  if (newValue) {
    // Preencher automaticamente o campo valor recebido com o total da venda
    checkoutForm.value.amount_received = total.value
    
    nextTick(() => {
      amountReceivedInput.value?.focus()
      amountReceivedInput.value?.select()
    })
  }
})

// Função para adicionar produto ao carrinho com quantidade
const addToCartWithQuantity = () => {
  if (!selectedProduct.value) return
  
  const availableStock = selectedProduct.value.actual_stock || selectedProduct.value.stock_quantity || 0
  const requestedQuantity = quantityInput.value
  
  // Validar se a quantidade solicitada não excede o estoque disponível
  if (requestedQuantity > availableStock) {
    // Mostrar notificação mais elegante
    const notification = document.createElement('div')
    notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center'
    notification.innerHTML = `
      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
      </svg>
      Quantidade excede estoque (${availableStock}). Ajustando para o máximo.
    `
    document.body.appendChild(notification)
    setTimeout(() => notification.remove(), 3000)
    
    quantityInput.value = availableStock
    return
  }
  
  const existingItem = cart.value.find(item => item.product.id === selectedProduct.value!.id)
  
  if (existingItem) {
    // Verificar se adicionar a nova quantidade não excede o estoque total
    const totalQuantity = existingItem.quantity + requestedQuantity
    if (totalQuantity > availableStock) {
      const maxCanAdd = availableStock - existingItem.quantity
      if (maxCanAdd <= 0) {
        // Notificação de estoque insuficiente
        const notification = document.createElement('div')
        notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center'
        notification.innerHTML = `
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
          Estoque insuficiente! Disponível: ${availableStock}, no carrinho: ${existingItem.quantity}
        `
        document.body.appendChild(notification)
        setTimeout(() => notification.remove(), 3000)
        return
      } else {
        // Notificação de quantidade ajustada
        const notification = document.createElement('div')
        notification.className = 'fixed top-4 right-4 bg-orange-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center'
        notification.innerHTML = `
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
          Ajustando para ${maxCanAdd} unidades (máximo disponível)
        `
        document.body.appendChild(notification)
        setTimeout(() => notification.remove(), 3000)
        existingItem.quantity = availableStock
      }
    } else {
      existingItem.quantity = totalQuantity
    }
  } else {
    cart.value.push({
      product: selectedProduct.value,
      quantity: requestedQuantity
    })
  }
  
  // Fechar modal e resetar valores
  showQuantityModal.value = false
  selectedProduct.value = null
  quantityInput.value = 1
  
  // Limpar campo de pesquisa e retornar foco
  searchQuery.value = ''
  nextTick(() => {
    searchInput.value?.focus()
  })
}

// Função para fechar modal de quantidade
const closeQuantityModal = () => {
  showQuantityModal.value = false
  selectedProduct.value = null
  quantityInput.value = 1
}

// Funções auxiliares para controlar quantidade no modal
const decreaseQuantity = () => {
  if (quantityInput.value > 1) {
    quantityInput.value = Math.max(1, quantityInput.value - 1)
  }
}

const increaseQuantity = () => {
  if (selectedProduct.value) {
    const maxStock = selectedProduct.value.actual_stock || selectedProduct.value.stock_quantity || 0
    if (quantityInput.value < maxStock) {
      quantityInput.value = Math.min(maxStock, quantityInput.value + 1)
    }
  }
}

const validateQuantityInput = () => {
  if (selectedProduct.value) {
    const maxStock = selectedProduct.value.actual_stock || selectedProduct.value.stock_quantity || 0
    if (quantityInput.value > maxStock) {
      quantityInput.value = maxStock
    } else if (quantityInput.value < 1) {
      quantityInput.value = 1
    }
  }
}

const getPaymentMethodText = (method: string) => {
  const methods: Record<string, string> = {
    'money': 'Dinheiro',
    'card': 'Cartão',
    'pix': 'PIX',
    'credit': 'A Prazo (Crédito)',
    'multiple': 'Múltiplas Formas'
  }
  return methods[method] || method
}

const closeSuccessModal = () => {
  showSuccessModal.value = false
  completedSale.value = null
  // Focar na busca para nova venda
  nextTick(() => {
    searchInput.value?.focus()
  })
}

const printSale = () => {
  if (!completedSale.value) return
  
  const printTemplate = settingsStore.storeSettings.print_template || '80mm'
  const storeName = settingsStore.storeSettings.name || 'CLICKVENDA'
  const storeCnpj = settingsStore.storeSettings.cnpj || ''
  const storeAddress = settingsStore.storeSettings.address || ''
  const storeCity = settingsStore.storeSettings.city || ''
  const storeState = settingsStore.storeSettings.state || ''
  const receiptFooter = settingsStore.storeSettings.receipt_footer || 'Obrigado pela preferência!'
  
  let printContent = ''
  
  if (printTemplate === '80mm') {
    // Modelo 80mm - Impressora térmica padrão
    printContent = `
      <div style="font-family: 'Courier New', monospace; font-size: 12px; max-width: 80mm; margin: 0 auto; padding: 10px;">
        <div style="text-align: center; margin-bottom: 15px;">
          <div style="font-weight: bold; font-size: 14px;">${storeName}</div>
          ${storeCnpj ? `<div style="font-size: 10px;">${storeCnpj}</div>` : ''}
        </div>
        
        <div style="border-top: 1px solid #000; margin-bottom: 10px;"></div>
        
        <div style="margin-bottom: 10px;">
          <div><strong>Pedido:</strong> #${completedSale.value.id}</div>
          <div><strong>Data:</strong> ${completedSale.value.date.toLocaleDateString('pt-BR')}</div>
          <div><strong>Hora:</strong> ${completedSale.value.date.toLocaleTimeString('pt-BR')}</div>
        </div>
        
        ${completedSale.value.customer ? `
        <div style="margin-bottom: 10px;">
          <div><strong>Cliente:</strong> ${completedSale.value.customer.name}</div>
        </div>
        ` : ''}
        
        <div style="border-top: 1px solid #000; margin: 10px 0;"></div>
        
        <div style="margin-bottom: 10px;">
          <div style="font-weight: bold; margin-bottom: 5px;">Itens:</div>
          ${completedSale.value.items.map((item: any) => `
            <div style="margin-bottom: 3px;">
              ${item.product.name}<br>
              ${item.quantity} x R$ ${formatPrice(item.product.price)} = R$ ${formatPrice(item.product.price * item.quantity)}
            </div>
          `).join('')}
        </div>
        
        <div style="border-top: 1px solid #000; margin: 10px 0;"></div>
        
        <div style="margin-bottom: 10px;">
          <div style="font-weight: bold; font-size: 14px;">Total: R$ ${formatPrice(completedSale.value.total)}</div>
        </div>
        
        <div style="margin-bottom: 10px;">
          <div><strong>Pagamento:</strong></div>
          ${completedSale.value.paymentMethods ? 
            completedSale.value.paymentMethods.map((p: any) => `<div>${getPaymentMethodText(p.method)}: R$ ${formatPrice(p.amount)}</div>`).join('') :
            `<div>${getPaymentMethodText(completedSale.value.paymentMethod)}</div>`
          }
        </div>
        
        <div style="border-top: 1px solid #000; margin: 10px 0;"></div>
        
        <div style="text-align: center; margin-top: 15px; font-size: 10px;">
          <div>${receiptFooter}</div>
        </div>
      </div>
    `
  } else if (printTemplate === '58mm') {
    // Modelo 58mm - Impressora térmica pequena
    printContent = `
      <div style="font-family: 'Courier New', monospace; font-size: 10px; max-width: 58mm; margin: 0 auto; padding: 8px;">
        <div style="text-align: center; margin-bottom: 10px;">
          <div style="font-weight: bold; font-size: 12px;">${storeName}</div>
          ${storeCnpj ? `<div style="font-size: 8px;">${storeCnpj}</div>` : ''}
        </div>
        
        <div style="border-top: 1px solid #000; margin-bottom: 8px;"></div>
        
        <div style="margin-bottom: 8px; font-size: 9px;">
          <div>#${completedSale.value.id} - ${completedSale.value.date.toLocaleDateString('pt-BR')}</div>
          ${completedSale.value.customer ? `<div>${completedSale.value.customer.name}</div>` : ''}
        </div>
        
        <div style="border-top: 1px solid #000; margin: 8px 0;"></div>
        
        <div style="margin-bottom: 8px; font-size: 9px;">
          ${completedSale.value.items.map((item: any) => `
            <div style="margin-bottom: 2px;">
              ${item.product.name} x${item.quantity} R$${formatPrice(item.product.price * item.quantity)}
            </div>
          `).join('')}
        </div>
        
        <div style="border-top: 1px solid #000; margin: 8px 0;"></div>
        
        <div style="margin-bottom: 8px;">
          <div style="font-weight: bold; font-size: 11px;">Total: R$ ${formatPrice(completedSale.value.total)}</div>
        </div>
        
        <div style="text-align: center; margin-top: 10px; font-size: 8px;">
          <div>${receiptFooter}</div>
        </div>
      </div>
    `
  } else {
    // Modelo A4 - Impressora laser/jato de tinta
    printContent = `
      <div style="font-family: Arial, sans-serif; max-width: 210mm; margin: 0 auto; padding: 20px;">
        <div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 20px;">
          <h1 style="margin: 0; font-size: 24px;">${storeName}</h1>
          ${storeCnpj ? `<p style="margin: 5px 0; font-size: 14px;">CNPJ: ${storeCnpj}</p>` : ''}
          ${storeAddress ? `<p style="margin: 5px 0; font-size: 12px;">${storeAddress}</p>` : ''}
          ${storeCity && storeState ? `<p style="margin: 5px 0; font-size: 12px;">${storeCity}, ${storeState}</p>` : ''}
        </div>
        
        <div style="margin-bottom: 20px;">
          <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
            <span><strong>Pedido:</strong> #${completedSale.value.id}</span>
            <span><strong>Data:</strong> ${completedSale.value.date.toLocaleDateString('pt-BR')} ${completedSale.value.date.toLocaleTimeString('pt-BR')}</span>
          </div>
          ${completedSale.value.customer ? `<div><strong>Cliente:</strong> ${completedSale.value.customer.name}</div>` : ''}
        </div>
        
        <div style="border-top: 1px solid #000; padding-top: 15px; margin-bottom: 20px;">
          <table style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr style="background-color: #f5f5f5;">
                <th style="text-align: left; padding: 8px; border-bottom: 1px solid #000;">Item</th>
                <th style="text-align: center; padding: 8px; border-bottom: 1px solid #000;">Qtd</th>
                <th style="text-align: right; padding: 8px; border-bottom: 1px solid #000;">Valor Unit.</th>
                <th style="text-align: right; padding: 8px; border-bottom: 1px solid #000;">Total</th>
              </tr>
            </thead>
            <tbody>
              ${completedSale.value.items.map((item: any) => `
                <tr>
                  <td style="padding: 8px; border-bottom: 1px solid #ccc;">${item.product.name}</td>
                  <td style="text-align: center; padding: 8px; border-bottom: 1px solid #ccc;">${item.quantity}</td>
                  <td style="text-align: right; padding: 8px; border-bottom: 1px solid #ccc;">R$ ${formatPrice(item.product.price)}</td>
                  <td style="text-align: right; padding: 8px; border-bottom: 1px solid #ccc;">R$ ${formatPrice(item.product.price * item.quantity)}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
        
        <div style="border-top: 2px solid #000; padding-top: 15px; margin-bottom: 20px;">
          <div style="text-align: center;">
            <div style="font-size: 18px; font-weight: bold;">Total: R$ ${formatPrice(completedSale.value.total)}</div>
          </div>
        </div>
        
        <div style="margin-bottom: 20px;">
          <div><strong>Forma de Pagamento:</strong></div>
          ${completedSale.value.paymentMethods ? 
            completedSale.value.paymentMethods.map((p: any) => `<div>${getPaymentMethodText(p.method)}: R$ ${formatPrice(p.amount)}</div>`).join('') :
            `<div>${getPaymentMethodText(completedSale.value.paymentMethod)}</div>`
          }
        </div>
        
        <div style="text-align: center; margin-top: 30px; font-size: 12px; color: #666;">
          <div>${receiptFooter}</div>
        </div>
      </div>
    `
  }
  
  const printWindow = window.open('', '_blank')
  if (printWindow) {
    printWindow.document.write(`
      <html>
        <head>
          <title>Comprovante de Venda #${completedSale.value.id}</title>
          <style>
            @media print {
              body { margin: 0; }
            }
          </style>
        </head>
        <body>
          ${printContent}
        </body>
      </html>
    `)
    printWindow.document.close()
    printWindow.print()
  }
}

const sendWhatsApp = () => {
  if (!completedSale.value) return
  
  const customer = completedSale.value.customer
  if (!customer || !customer.phone) {
    alert('Cliente não possui telefone cadastrado para envio via WhatsApp.')
    return
  }
  
  // Usar template personalizado se disponível, senão usar template padrão
  const template = settingsStore.storeSettings.whatsapp_message_template || `🛒 *CLICKVENDA - Comprovante de Venda*

📋 *Pedido:* #{PEDIDO_ID}
📅 *Data:* {DATA}
🕒 *Hora:* {HORA}

👤 *Cliente:* {CLIENTE_NOME}

📦 *Itens:*
{ITENS_LISTA}

💰 *Total: R$ {TOTAL}*

💳 *Pagamento:*
{FORMAS_PAGAMENTO}

✅ *Status:* {STATUS}

Obrigado pela preferência! 🙏`
  
  // Preparar dados para substituição
  const itemsList = completedSale.value.items.map((item: any) => 
    `• ${item.product.name}\n  ${item.quantity} x R$ ${formatPrice(item.product.price)} = R$ ${formatPrice(item.product.price * item.quantity)}`
  ).join('\n')
  
  const paymentMethods = completedSale.value.paymentMethods ? 
    completedSale.value.paymentMethods.map((p: any) => `• ${getPaymentMethodText(p.method)}: R$ ${formatPrice(p.amount)}`).join('\n') :
    `• ${getPaymentMethodText(completedSale.value.paymentMethod)}`
  
  // Substituir variáveis no template
  const message = template
    .replace('{PEDIDO_ID}', completedSale.value.id)
    .replace('{DATA}', completedSale.value.date.toLocaleDateString('pt-BR'))
    .replace('{HORA}', completedSale.value.date.toLocaleTimeString('pt-BR'))
    .replace('{CLIENTE_NOME}', customer.name)
    .replace('{ITENS_LISTA}', itemsList)
    .replace('{TOTAL}', formatPrice(completedSale.value.total))
    .replace('{FORMAS_PAGAMENTO}', paymentMethods)
    .replace('{STATUS}', completedSale.value.status === 'completed' ? 'Finalizada' : 'Pendente')

  const phone = customer.phone.replace(/\D/g, '')
  const whatsappUrl = `https://wa.me/55${phone}?text=${encodeURIComponent(message)}`
  window.open(whatsappUrl, '_blank')
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

const handleSearchEnter = () => {
  if (filteredProducts.value.length > 0 && selectedProductIndex.value >= 0) {
    // Se há um produto selecionado, adiciona ao carrinho
    const selectedProduct = filteredProducts.value[selectedProductIndex.value]
    if (selectedProduct && (selectedProduct.actual_stock || selectedProduct.stock_quantity) > 0) {
      selectProduct(selectedProduct)
    }
  } else if (filteredProducts.value.length > 0) {
    // Se não há seleção mas há produtos, foca no primeiro
    selectedProductIndex.value = 0
    const selectedProduct = filteredProducts.value[0]
    if (selectedProduct && (selectedProduct.actual_stock || selectedProduct.stock_quantity) > 0) {
      selectProduct(selectedProduct)
    }
  }
}

const handleArrowDown = () => {
  if (filteredProducts.value.length === 0) return
  
  if (selectedProductIndex.value < filteredProducts.value.length - 1) {
    selectedProductIndex.value++
  } else {
    selectedProductIndex.value = 0 // Volta para o primeiro
  }
  
  // Rola a lista para mostrar o item selecionado
  scrollToSelectedProduct()
}

const handleArrowUp = () => {
  if (filteredProducts.value.length === 0) return
  
  if (selectedProductIndex.value > 0) {
    selectedProductIndex.value--
  } else {
    selectedProductIndex.value = filteredProducts.value.length - 1 // Vai para o último
  }
  
  // Rola a lista para mostrar o item selecionado
  scrollToSelectedProduct()
}

const scrollToSelectedProduct = () => {
  nextTick(() => {
    const element = document.querySelector(`[ref="product-${selectedProductIndex.value}"]`) as HTMLElement
    if (element) {
      element.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
    }
  })
}

// Watch for changes in total to update credit validation
watch(total, () => {
  if (checkoutForm.value.payment_method === 'credit' && !canUseCredit.value) {
    checkoutForm.value.payment_method = 'money'
  }
})

// Watch para atualizar valores dos pagamentos quando total muda
watch(total, (newTotal) => {
  if (multiplePayments.value && paymentMethods.value.length === 1 && paymentMethods.value[0].amount === 0) {
    paymentMethods.value[0].amount = newTotal
  }
})

// Watch para resetar múltiplos pagamentos quando desabilitado
watch(multiplePayments, (newValue) => {
  if (!newValue) {
    resetPaymentMethods()
  } else {
    paymentMethods.value = [{ method: 'money', amount: total.value }]
  }
})

const handleSearch = async () => {
  // A busca agora é feita em tempo real via computed property filteredProducts
  // Não precisamos mais fazer chamadas à API para cada digitação
  searching.value = false
}

// Função addToCart removida - agora usamos selectProduct + modal de quantidade

const updateQuantity = (productId: number, newQuantity: number) => {
  const item = cart.value.find(item => item.product.id === productId)
  if (item) {
    if (newQuantity <= 0) {
      removeFromCart(productId)
    } else {
      const availableStock = item.product.actual_stock || item.product.stock_quantity || 0
      if (newQuantity > availableStock) {
        // Limitar ao estoque disponível e mostrar aviso
        const notification = document.createElement('div')
        notification.className = 'fixed top-4 right-4 bg-orange-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center'
        notification.innerHTML = `
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
          Ajustando para ${availableStock} unidades (máximo disponível)
        `
        document.body.appendChild(notification)
        setTimeout(() => notification.remove(), 3000)
        item.quantity = availableStock
      } else {
        item.quantity = newQuantity
      }
    }
  }
}

const removeFromCart = (productId: number) => {
  cart.value = cart.value.filter(item => item.product.id !== productId)
}

const handleCheckout = async (status: 'pending' | 'completed' = 'completed') => {
  if (cart.value.length === 0) return

  // Validação adicional de crédito apenas para vendas finalizadas
  if (status === 'completed') {
    if (multiplePayments.value) {
      // Validar se pagamento está completo
      if (Math.abs(totalPaid.value - total.value) > 0.01) {
        alert('O valor total pago deve ser igual ao valor da venda.')
        return
      }
      // Validar crédito se estiver sendo usado
      if (creditAmount.value > 0 && !canUseCreditForAmount.value) {
        alert('Crédito insuficiente para o valor solicitado. Ajuste o valor do crédito.')
        return
      }
    } else {
      // Validação para pagamento único
      if (checkoutForm.value.payment_method === 'credit' && !canUseCredit.value) {
        alert('Crédito insuficiente para esta venda. Escolha outra forma de pagamento.')
        return
      }
      
      // Validação para pagamento em dinheiro - verificar se valor entregue é suficiente
      if (checkoutForm.value.payment_method === 'money') {
        const amountReceived = Number(checkoutForm.value.amount_received) || 0
        if (amountReceived < total.value) {
          alert('O valor entregue pelo cliente deve ser igual ou maior que o total da venda.')
          return
        }
      }
    }
  }

  checkoutSubmitting.value = true
  
  try {
    const orderData: any = {
      customer_id: selectedCustomer.value ? selectedCustomer.value.id : null,
      payment_method: (multiplePayments.value ? 'multiple' : checkoutForm.value.payment_method) as 'money' | 'card' | 'pix' | 'credit' | 'multiple',
      payment_methods: multiplePayments.value ? paymentMethods.value : undefined,
      discount_amount: discount.value,
      notes: checkoutForm.value.notes,
      status: status,
      items: cart.value.map(item => {
        const unitPrice = parseFloat(item.product.price.toString()) || 0
        const quantity = Number(item.quantity) || 0
        return {
        product_id: item.product.id,
          quantity: quantity,
          unit_price: unitPrice
        }
      })
    }

    // Adicionar informações de troco se pagamento for em dinheiro
    if (!multiplePayments.value && checkoutForm.value.payment_method === 'money') {
      const amountReceived = Number(checkoutForm.value.amount_received) || 0
      orderData.amount_received = amountReceived
      orderData.change_due = Math.max(0, amountReceived - total.value)
    }

    console.log('Criando pedido:', orderData)
    console.log('Customer ID:', orderData.customer_id, typeof orderData.customer_id)
    console.log('Payment method:', orderData.payment_method)
    console.log('Items:', orderData.items)
    
    const result = await ordersStore.createOrder(orderData)
    
    if (result.success) {
      // Salvar valores antes de limpar o carrinho
      const saleTotal = total.value
      const paymentMethod = checkoutForm.value.payment_method
      
      // Se foi venda a prazo finalizada, atualizar crédito usado do cliente
      if (status === 'completed' && selectedCustomer.value) {
        let creditUsed = 0
        
        if (multiplePayments.value) {
          // Somar apenas os valores pagos a prazo
          creditUsed = paymentMethods.value
            .filter(p => p.method === 'credit')
            .reduce((sum, p) => sum + (Number(p.amount) || 0), 0)
        } else if (checkoutForm.value.payment_method === 'credit') {
          creditUsed = saleTotal
        }
        
        if (creditUsed > 0) {
          await customersStore.adjustCredit(
            selectedCustomer.value.id,
            creditUsed,
            'charge',
            `Venda #${result.data?.id || 'N/A'}`
          )
        }
      }
      
      // Registrar débitos de vasilhames por tipo sempre que há produtos retornáveis
      console.log('=== DEBUG VASILHAMES ===')
      console.log('Status:', status)
      console.log('Cliente selecionado:', selectedCustomer.value)
      console.log('Vasilhames por tipo:', vasilhamesPorTipo.value)
      console.log('Condições:', {
        statusCompleted: status === 'completed',
        hasCustomer: !!selectedCustomer.value,
        hasVasilhames: vasilhamesPorTipo.value.length > 0
      })
      
      if (status === 'completed' && selectedCustomer.value && vasilhamesPorTipo.value.length > 0) {
        console.log('Entrando no bloco de registro de vasilhames...')
        try {
          // Registrar cada tipo de vasilhame separadamente
          for (const tipo of vasilhamesPorTipo.value) {
            // Calcular apenas o débito real (necessário - informado pelo cliente)
            const debitoQuantidade = Math.max(0, tipo.necessarios - tipo.informados)
            
            console.log(`Processando tipo: ${tipo.nome}, Modelo ID: ${tipo.modeloId}, Necessários: ${tipo.necessarios}, Informados: ${tipo.informados}, Débito: ${debitoQuantidade}`)
            
            if (debitoQuantidade > 0) {
              console.log(`Chamando registrarSaida para cliente ${selectedCustomer.value.id}...`)
              
              // Encontrar um produto retornável que use este modelo de vasilhame
              const produtoRetornavel = cart.value.find(item => 
                item.product.returnable && 
                item.product.vasilhame_model_id === tipo.modeloId
              )
              
              if (produtoRetornavel) {
                await vasilhamesStore.registrarSaida(
                  selectedCustomer.value.id,
                  produtoRetornavel.product.id, // ID do produto retornável
                  debitoQuantidade,
                  `Débito ${tipo.nome.toLowerCase()} - Venda #${result.data?.id || 'N/A'}`,
                  tipo.modeloId, // ID do modelo de vasilhame
                  result.data?.id // ID do pedido
                )
                
                console.log(`✅ Registrado débito de ${debitoQuantidade} ${tipo.nome.toLowerCase()}(s) para cliente ${selectedCustomer.value.name}`)
              } else {
                console.log(`❌ Produto retornável não encontrado para modelo ${tipo.modeloId}`)
              }
            } else {
              console.log(`❌ Quantidade zero, não registrando débito para ${tipo.nome}`)
            }
          }
        } catch (error) {
          console.error('❌ Erro ao registrar débitos de vasilhames por tipo:', error)
          // Não bloquear a venda por erro no registro de vasilhames
        }
      } else {
        console.log('❌ Condições não atendidas para registro de vasilhames')
      }
      console.log('=== FIM DEBUG VASILHAMES ===')
      
      
      // Preparar dados da venda para o modal de sucesso
      completedSale.value = {
        id: result.data?.id,
        total: saleTotal,
        paymentMethod: paymentMethod,
        paymentMethods: multiplePayments.value ? [...paymentMethods.value] : null,
        customer: selectedCustomer.value,
        status: status,
        items: [...cart.value],
        date: new Date()
      }
      
      showCheckoutModal.value = false
      showSuccessModal.value = true
      
      // Limpar dados após mostrar modal
      cart.value = []
      selectedCustomer.value = null
      vasilhamesInformados.value = 0 // Resetar vasilhames informados (compatibilidade)
      
      // Resetar vasilhames informados por tipo
      vasilhamesPorTipo.value.forEach(tipo => {
        tipo.informados = 0
      })
      
      resetPaymentMethods()
      checkoutForm.value = {
        customer_id: '',
        payment_method: 'money',
        discount: 0,
        notes: '',
        amount_received: 0
      }
      
      // Atualizar estoque dos produtos e clientes
      await Promise.all([
        productsStore.fetchProducts(),
        customersStore.fetchCustomers()
      ])
    } else {
      alert('Erro ao finalizar venda: ' + (result.error || 'Erro desconhecido'))
    }
    
  } catch (error: any) {
    console.error('Erro ao finalizar venda:', error)
    console.error('Response data:', error.response?.data)
    console.error('Status:', error.response?.status)
    
    let errorMessage = 'Erro ao finalizar venda. Tente novamente.'
    
    if (error.response?.status === 422) {
      const errors = error.response.data?.errors
      if (errors) {
        // Mostrar todos os erros de validação
        const errorMessages = []
        for (const [field, messages] of Object.entries(errors)) {
          const fieldMessages = Array.isArray(messages) ? messages : [messages]
          errorMessages.push(`${field}: ${fieldMessages.join(', ')}`)
        }
        errorMessage = `Erros de validação:\n${errorMessages.join('\n')}`
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

// Keyboard shortcuts
const handleKeydown = (event: KeyboardEvent) => {
  // Atalhos específicos quando o modal de checkout está aberto
  if (showCheckoutModal.value) {
    // F1-F4 - Selecionar forma de pagamento
    if ((event.key === 'F1' || event.key === 'F2' || event.key === 'F3' || event.key === 'F4') && !multiplePayments.value) {
      event.preventDefault()
      const paymentMethods = ['money', 'card', 'pix', 'credit']
      let index = -1
      
      switch(event.key) {
        case 'F1': index = 0; break // Dinheiro
        case 'F2': index = 1; break // Cartão
        case 'F3': index = 2; break // PIX
        case 'F4': index = 3; break // Crédito
      }
      
      if (index >= 0 && index < paymentMethods.length) {
        // Verificar se crédito está disponível
        if (paymentMethods[index] === 'credit' && (!selectedCustomer.value || selectedCustomer.value.credit_limit <= 0)) {
          return
        }
        checkoutForm.value.payment_method = paymentMethods[index]
      }
    }
    
    // F5 - Focar no campo de valor recebido
    if (event.key === 'F5') {
      event.preventDefault()
      if (!multiplePayments.value) {
        nextTick(() => {
          amountReceivedInput.value?.focus()
          amountReceivedInput.value?.select()
        })
      }
    }
    
    // F9 - Finalizar venda
    if (event.key === 'F9') {
      event.preventDefault()
      handleCheckout('completed')
    }
    
    // F10 - Salvar como pendente
    if (event.key === 'F10') {
      event.preventDefault()
      handleCheckout('pending')
    }
    
    // ESC - Fechar modal
    if (event.key === 'Escape') {
      event.preventDefault()
      showCheckoutModal.value = false
      nextTick(() => searchInput.value?.focus())
      return
    }
  }
  
  // Atalhos gerais (quando modal de checkout não está aberto)
  if (!showCheckoutModal.value) {
    // F6 - Selecionar cliente
    if (event.key === 'F6') {
      event.preventDefault()
      showCustomerModal.value = true
      // Focar no campo de busca de cliente após abrir o modal
      nextTick(() => {
        customerSearchRef.value?.focus()
      })
    }
    
    // F7 - Focar na busca de produtos
    if (event.key === 'F7') {
      event.preventDefault()
      searchInput.value?.focus()
    }
    
    // F8 - Limpar carrinho
    if (event.key === 'F8') {
      event.preventDefault()
      if (cart.value.length > 0) {
        clearCart()
      }
    }
    
    // F9 - Finalizar venda
    if (event.key === 'F9') {
      event.preventDefault()
      if (cart.value.length > 0) {
        showCheckoutModal.value = true
        // O preenchimento automático será feito pelo watch do showCheckoutModal
      }
    }
  }
  
  // ESC - Fechar modais (geral)
  if (event.key === 'Escape') {
    if (showCheckoutModal.value) {
      showCheckoutModal.value = false
      nextTick(() => searchInput.value?.focus())
    }
    if (showCustomerModal.value) {
      showCustomerModal.value = false
      nextTick(() => searchInput.value?.focus())
    }
    if (showQuantityModal.value) {
      showQuantityModal.value = false
      nextTick(() => searchInput.value?.focus())
    }
  }
}

onMounted(async () => {
  console.log('SalesView: Iniciando carregamento...')
  
  await productsStore.fetchProducts()
  console.log('SalesView: Produtos carregados:', productsStore.products.length)
  
  await customersStore.fetchCustomers()
  console.log('SalesView: Clientes carregados:', customersStore.customers.length)
  
  await settingsStore.loadSettings()
  customers.value = customersStore.customers
  
  // Carregar modelos de vasilhames para separação por tipo
  try {
    await vasilhamesStore.carregarModelos()
    console.log('SalesView: Modelos de vasilhames carregados:', vasilhamesStore.modelos.length)
  } catch (error) {
    console.error('SalesView: Erro ao carregar modelos de vasilhames:', error)
  }

  // Inicializar horário em tempo real
  updateDateTime()
  const interval = setInterval(updateDateTime, 1000)
  
  // Salvar referência do interval para limpeza
  ;(window as any).dateTimeInterval = interval
  
  // Adicionar event listeners para atalhos de teclado
  document.addEventListener('keydown', handleKeydown)
  
  // Focar na busca ao carregar apenas se não houver modais abertos
  nextTick(() => {
    if (!showQuantityModal.value && !showCustomerModal.value && !showCheckoutModal.value) {
      searchInput.value?.focus()
    }
  })
})

// Cleanup
import { onUnmounted } from 'vue'
onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
  // Limpar interval do horário
  if ((window as any).dateTimeInterval) {
    clearInterval((window as any).dateTimeInterval)
  }
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Estilos para layout de lista - Mobile Optimized */
.product-list-item {
  /* Removido: estilos conflitantes serão aplicados via classes Tailwind */
}

.product-list-item:hover {
  /* Removido: hover será aplicado via classes Tailwind */
}

.product-list-item.selected {
  /* Removido: seleção será aplicada via classes Tailwind */
}

.product-list-item.disabled {
  /* Removido: disabled será aplicado via classes Tailwind */
}

.product-list-info {
  flex: 1;
  min-width: 0;
}

.product-list-name {
  font-weight: 600;
  color: #111827;
  font-size: 14px;
  margin-bottom: 4px;
  line-height: 1.3;
}

.product-list-details {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 12px;
  color: #6b7280;
}

.product-list-price {
  font-weight: 700;
  color: #2563eb;
  font-size: 14px;
  white-space: nowrap;
}

.product-list-stock {
  display: flex;
  align-items: center;
  gap: 4px;
  font-weight: 500;
}

.product-list-sku {
  font-family: ui-monospace, monospace;
  color: #9ca3af;
}

.product-list-returnable {
  color: #ea580c;
  font-weight: 600;
  font-size: 11px;
}

/* Mobile styles */
@media (max-width: 768px) {
  .mobile-hidden {
    display: none !important;
  }
  
  /* Add padding bottom for mobile navigation and desktop financial bar */
  body {
    padding-bottom: 140px; /* Aumentado para acomodar as duas barras fixas */
  }
  
  /* Touch-friendly buttons */
  .product-list-item {
    min-height: 70px;
    padding: 16px 12px;
    border-radius: 8px;
    margin-bottom: 8px;
  }
  
  .product-list-item button {
    min-height: 44px;
    min-width: 44px;
    font-size: 14px;
    font-weight: 600;
  }
  
  /* Improve product grid for small screens */
  .product-list-name {
    font-size: 14px !important;
    line-height: 1.2 !important;
    margin-bottom: 6px !important;
  }
  
  .product-list-details {
    flex-wrap: wrap;
    gap: 8px !important;
    font-size: 11px !important;
  }
  
  .product-list-price {
    font-size: 15px !important;
    font-weight: 700 !important;
  }
  
  /* Mobile cart optimization */
  .cart-item {
    border-radius: 8px;
    margin-bottom: 4px;
  }
  
  /* Mobile search optimization */
  .search-input {
    font-size: 16px; /* Prevents zoom on iOS */
  }
}

/* Layout responsivo para lista */
@media (max-width: 1024px) {
  .product-list-item {
    padding: 10px 12px;
    gap: 12px;
  }
  
  .product-list-name {
    font-size: 13px;
  }
  
  .product-list-price {
    font-size: 13px;
  }
}
</style>
