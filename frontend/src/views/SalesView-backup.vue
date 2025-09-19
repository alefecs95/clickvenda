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
            <!-- Atalhos de Teclado -->
            <div class="text-xs text-gray-500 mt-1 flex items-center space-x-3">
              <span class="bg-gray-100 px-2 py-0.5 rounded">F6: Cliente</span>
              <span class="bg-gray-100 px-2 py-0.5 rounded">F7: Buscar</span>
              <span class="bg-gray-100 px-2 py-0.5 rounded">F8: Limpar</span>
              <span class="bg-gray-100 px-2 py-0.5 rounded">F9: Finalizar</span>
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
      <!-- Produtos - Lado Esquerdo -->
      <div class="w-2/3 bg-white border-r border-gray-200 flex flex-col h-full">
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
              placeholder="Pressione F7 para buscar por código de barras, nome ou SKU..."
              class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent text-lg"
              @input="handleSearch"
              @keydown.enter="handleSearchEnter"
              @keydown.down="handleArrowDown"
              @keydown.up="handleArrowUp"
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

          <div v-else class="space-y-2">
            <div
              v-for="(product, index) in filteredProducts"
              :key="product.id"
              :ref="'product-' + index"
              class="product-list-item"
              :class="{ 
                'selected': selectedProductIndex === index,
                'disabled': (product.actual_stock || product.stock_quantity) <= 0
              }"
              @click="(product.actual_stock || product.stock_quantity) > 0 ? selectProduct(product) : null"
              @keydown.enter="(product.actual_stock || product.stock_quantity) > 0 ? selectProduct(product) : null"
              tabindex="0"
            >
              <!-- Informações do produto -->
              <div class="product-list-info">
                <h3 class="product-list-name">
                  {{ product.name }}
                </h3>
                <div class="product-list-details">
                  <div class="product-list-stock">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <span>{{ product.actual_stock || product.stock_quantity }}</span>
                  </div>
                  <span v-if="product.sku" class="product-list-sku">{{ product.sku }}</span>
                  <span v-if="product.returnable" class="product-list-returnable">
                    + R$ {{ formatPrice(product.returnable_price) }}
                  </span>
                </div>
              </div>
              
              <!-- Preço e ações -->
              <div class="text-right">
                <div class="product-list-price">
                  R$ {{ formatPrice(product.price) }}
                </div>
                
                <div v-if="(product.actual_stock || product.stock_quantity) <= 0" class="text-xs text-red-600 font-medium mt-1">
                  Sem Estoque
                </div>
                <div v-else class="text-xs text-primary-600 font-medium mt-1">
                  Adicionar
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

      <!-- Carrinho - Lado Direito -->
      <div class="w-1/3 bg-white flex flex-col h-full">
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
                    class="w-6 h-6 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center text-xs"
                  >
                    -
                  </button>
                  <span class="text-sm font-medium">{{ item.quantity }}</span>
                  <button
                    @click="updateQuantity(item.product.id, item.quantity + 1)"
                    class="w-6 h-6 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center text-xs"
                  >
                    +
                  </button>
                </div>
                <button
                  @click="removeFromCart(item.product.id)"
                  class="text-red-600 hover:text-red-800 text-xs"
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
    <div v-if="showCustomerModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @mousedown.self="showCustomerModal = false; nextTick(() => searchInput.value?.focus())">
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
          
          <div>
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
                  Controle de Vasilhames por Tipo
                </h4>
                
                <!-- Lista de tipos de vasilhames -->
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
                          @input="atualizarVasilhamesInformados(tipo.modeloId, Number($event.target.value))"
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
                            {{ Math.abs(tipo.informados - tipo.necessarios) }} {{ tipo.nome.toLowerCase() }}(s)
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
                            {{ tipo.informados - tipo.necessarios }} {{ tipo.nome.toLowerCase() }}(s)
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
                <div class="flex items-center mb-2">
                  <svg class="w-4 h-4 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  <span class="text-sm font-medium text-gray-700">Atalhos de Teclado</span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs text-gray-600">
                  <div class="flex items-center">
                    <kbd class="px-1 py-0.5 bg-white border border-gray-300 rounded text-xs font-mono">F1-F4</kbd>
                    <span class="ml-1">Forma pagamento</span>
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
                    <span class="ml-1">Salvar pendente</span>
                  </div>
                  <div class="flex items-center">
                    <kbd class="px-1 py-0.5 bg-white border border-gray-300 rounded text-xs font-mono">ESC</kbd>
                    <span class="ml-1">Fechar</span>
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
            
            <div class="mt-8 space-y-3">
              <!-- Botões principais -->
              <div class="flex space-x-3">
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
            @click="() => { showCustomerModal = true; nextTick(() => customerSearchRef.value?.focus()) }"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded font-bold text-sm flex items-center"
            title="Selecionar Cliente (F6)"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            CLIENTE (F6)
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

    <!-- Modal de Quantidade -->
    <div v-if="showQuantityModal && selectedProduct" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @mousedown.self="closeQuantityModal">
      <div class="relative top-20 mx-auto p-6 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="text-center">
          <!-- Título -->
          <h3 class="text-lg font-bold text-gray-900 mb-4">
            Adicionar ao Carrinho
          </h3>
          
          <!-- Informações do Produto -->
          <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
            <h4 class="font-semibold text-gray-900">{{ selectedProduct.name }}</h4>
            <p v-if="selectedProduct.description" class="text-sm text-gray-600 mt-1">{{ selectedProduct.description }}</p>
            <div class="flex justify-between items-center mt-3">
              <span class="text-lg font-bold text-primary-600">R$ {{ formatPrice(selectedProduct.price) }}</span>
              <span class="text-sm text-green-600">
                Estoque: {{ selectedProduct.actual_stock || selectedProduct.stock_quantity }}
              </span>
            </div>
          </div>
          
          <!-- Input de Quantidade -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Quantidade
            </label>
            <div class="flex items-center justify-center space-x-4">
              <button
                @click="quantityInput = Math.max(1, quantityInput - 1)"
                class="w-10 h-10 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                </svg>
              </button>
              <input
                ref="quantityInputRef"
                v-model.number="quantityInput"
                type="number"
                min="1"
                :max="selectedProduct.actual_stock || selectedProduct.stock_quantity"
                class="w-20 text-center text-lg font-bold border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                @keydown.enter="addToCartWithQuantity"
              />
              <button
                @click="quantityInput = Math.min(selectedProduct.actual_stock || selectedProduct.stock_quantity, quantityInput + 1)"
                class="w-10 h-10 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
              </button>
            </div>
          </div>
          
          <!-- Botões -->
          <div class="flex space-x-3">
            <button
              @click="closeQuantityModal"
              class="flex-1 bg-gray-300 text-gray-700 py-3 px-4 rounded-lg font-bold hover:bg-gray-400"
            >
              Cancelar
            </button>
            <button
              @click="addToCartWithQuantity"
              class="flex-1 bg-primary-600 text-white py-3 px-4 rounded-lg font-bold hover:bg-primary-700"
            >
              Adicionar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Sucesso -->
    <div v-if="showSuccessModal && completedSale" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-10 mx-auto p-6 border w-full max-w-lg shadow-lg rounded-lg bg-white">
        <div class="text-center">
          <!-- Ícone de Sucesso -->
          <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
          </div>
          
          <!-- Título -->
          <h3 class="text-xl font-bold text-gray-900 mb-2">
            {{ completedSale.status === 'completed' ? 'Venda Finalizada!' : 'Venda Salva!' }}
          </h3>
          
          <!-- Informações da Venda -->
          <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
            <div class="grid grid-cols-2 gap-4 text-sm">
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
            <div v-if="completedSale.customer" class="mt-4 pt-4 border-t border-gray-200">
              <span class="text-gray-600">Cliente:</span>
              <span class="font-bold ml-2">{{ completedSale.customer.name }}</span>
            </div>
            
            <!-- Total -->
            <div class="mt-4 pt-4 border-t border-gray-200">
              <div class="flex justify-between items-center">
                <span class="text-lg font-bold text-gray-900">Total:</span>
                <span class="text-2xl font-bold text-primary-600">R$ {{ formatPrice(completedSale.total) }}</span>
              </div>
            </div>
            
            <!-- Formas de Pagamento -->
            <div class="mt-4 pt-4 border-t border-gray-200">
              <span class="text-gray-600 block mb-2">Pagamento:</span>
              <div v-if="completedSale.paymentMethods" class="space-y-2">
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
          <div v-if="settingsStore.storeSettings.receipt_footer" class="mt-4 pt-4 border-t border-gray-200">
            <p class="text-xs text-gray-600 text-center italic">
              {{ settingsStore.storeSettings.receipt_footer }}
            </p>
            <div v-if="settingsStore.storeSettings.whatsapp" class="mt-2 text-xs text-gray-500 text-center">
              WhatsApp: {{ settingsStore.storeSettings.whatsapp }}
            </div>
          </div>
          
          <!-- Botões de Ação -->
          <div class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
              <button
                @click="printSale"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-bold text-sm"
              >
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Imprimir
              </button>
              <button
                @click="sendWhatsApp"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-bold text-sm"
              >
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                WhatsApp
              </button>
            </div>
            
            <button
              @click="closeSuccessModal"
              class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-3 rounded-lg font-bold text-lg"
            >
              <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
              Fechar e Nova Venda
            </button>
          </div>
        </div>
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
  parent_product_id?: number | null
}

interface CartItem {
  product: Product
  quantity: number
}

const searchInput = ref<HTMLInputElement>()
const quantityInputRef = ref<HTMLInputElement>()
const amountReceivedInput = ref<HTMLInputElement>()
const searchQuery = ref('')
const searching = ref(false)
const searchResults = ref<Product[]>([])
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

const returnableTotal = computed(() => {
  return cart.value.reduce((sum, item) => {
    if (item.product.returnable && item.product.returnable_price) {
      const returnablePrice = parseFloat(item.product.returnable_price.toString()) || 0
      const quantity = Number(item.quantity) || 0
      return sum + (returnablePrice * quantity)
    }
    return sum
  }, 0)
})

// Cálculo de vasilhames necessários agrupados por tipo/modelo
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
        grupos.set(modeloId, {
          modeloId,
          nome: item.product.name.split(' ')[0], // Pega primeira palavra como tipo (ex: "Cerveja", "Gás", "Água")
          necessarios: totalNecessario,
          informados: 0
        })
      }
    }
  })
  
  return Array.from(grupos.values())
})

// Total de vasilhames necessários (para compatibilidade)
const vasilhamesNecessarios = computed(() => {
  return vasilhamesPorTipo.value.reduce((sum, tipo) => sum + tipo.necessarios, 0)
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
  return subtotal.value + returnableTotal.value - discount.value
})

// Produtos filtrados em tempo real
const filteredProducts = computed(() => {
  console.log('SalesView: filteredProducts - Total de produtos no store:', productsStore.products.length)
  console.log('SalesView: filteredProducts - Query de busca:', searchQuery.value)
  
  if (!searchQuery.value.trim()) {
    console.log('SalesView: filteredProducts - Retornando todos os produtos:', productsStore.products.length)
    return productsStore.products
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

const getItemTotal = (item: CartItem) => {
  const productPrice = parseFloat(item.product.price.toString()) || 0
  const returnablePrice = item.product.returnable ? (parseFloat(item.product.returnable_price?.toString() || '0') || 0) : 0
  const quantity = Number(item.quantity) || 0
  return (productPrice + returnablePrice) * quantity
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
  
  const existingItem = cart.value.find(item => item.product.id === selectedProduct.value!.id)
  
  if (existingItem) {
    existingItem.quantity += quantityInput.value
  } else {
    cart.value.push({
      product: selectedProduct.value,
      quantity: quantityInput.value
    })
  }
  
  // Fechar modal e resetar valores
  showQuantityModal.value = false
  selectedProduct.value = null
  quantityInput.value = 1
}

// Função para fechar modal de quantidade
const closeQuantityModal = () => {
  showQuantityModal.value = false
  selectedProduct.value = null
  quantityInput.value = 1
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

// Função para verificar se cliente está em risco de crédito
const isCustomerAtRisk = (customer: Customer) => {
  if (!customer || customer.credit_limit <= 0) return false
  
  const creditUsedPercentage = (customer.credit_used / customer.credit_limit) * 100
  const alertPercentage = typeof settingsStore.salesSettings.credit_alert_percentage === 'number' 
    ? settingsStore.salesSettings.credit_alert_percentage 
    : parseFloat(settingsStore.salesSettings.credit_alert_percentage as string) || 80
    
  return creditUsedPercentage >= alertPercentage
}

// Função para obter a classe CSS do alerta de crédito
const getCreditAlertClass = (customer: Customer) => {
  if (!customer || customer.credit_limit <= 0) return 'text-blue-700'
  
  if (isCustomerAtRisk(customer)) {
    return 'text-red-700 font-bold'
  }
  
  return 'text-blue-700'
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
  
  // Criar conteúdo para impressão
  const printContent = `
    <div style="font-family: Arial, sans-serif; max-width: 300px; margin: 0 auto;">
      <div style="text-align: center; margin-bottom: 20px;">
        <h2>CLICKVENDA</h2>
        <p>Comprovante de Venda</p>
      </div>
      
      <div style="margin-bottom: 15px;">
        <strong>Pedido:</strong> #${completedSale.value.id}<br>
        <strong>Data:</strong> ${completedSale.value.date.toLocaleDateString('pt-BR')}<br>
        <strong>Hora:</strong> ${completedSale.value.date.toLocaleTimeString('pt-BR')}
      </div>
      
      ${completedSale.value.customer ? `
      <div style="margin-bottom: 15px;">
        <strong>Cliente:</strong> ${completedSale.value.customer.name}
      </div>
      ` : ''}
      
      <div style="border-top: 1px solid #ccc; padding-top: 10px; margin-bottom: 15px;">
        <strong>Itens:</strong><br>
        ${completedSale.value.items.map((item: any) => `
          ${item.product.name}<br>
          ${item.quantity} x R$ ${formatPrice(item.product.price)} = R$ ${formatPrice(item.product.price * item.quantity)}
        `).join('<br>')}
      </div>
      
      <div style="border-top: 1px solid #ccc; padding-top: 10px; margin-bottom: 15px;">
        <strong>Total: R$ ${formatPrice(completedSale.value.total)}</strong>
      </div>
      
      <div style="margin-bottom: 15px;">
        <strong>Pagamento:</strong><br>
        ${completedSale.value.paymentMethods ? 
          completedSale.value.paymentMethods.map((p: any) => `${getPaymentMethodText(p.method)}: R$ ${formatPrice(p.amount)}`).join('<br>') :
          getPaymentMethodText(completedSale.value.paymentMethod)
        }
      </div>
      
      <div style="text-align: center; margin-top: 20px; font-size: 12px;">
        <p>Obrigado pela preferência!</p>
      </div>
    </div>
  `
  
  const printWindow = window.open('', '_blank')
  if (printWindow) {
    printWindow.document.write(printContent)
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
  
  const message = `🛒 *CLICKVENDA - Comprovante de Venda*

📋 *Pedido:* #${completedSale.value.id}
📅 *Data:* ${completedSale.value.date.toLocaleDateString('pt-BR')}
🕒 *Hora:* ${completedSale.value.date.toLocaleTimeString('pt-BR')}

👤 *Cliente:* ${customer.name}

📦 *Itens:*
${completedSale.value.items.map((item: any) => 
  `• ${item.product.name}\n  ${item.quantity} x R$ ${formatPrice(item.product.price)} = R$ ${formatPrice(item.product.price * item.quantity)}`
).join('\n')}

💰 *Total: R$ ${formatPrice(completedSale.value.total)}*

💳 *Pagamento:*
${completedSale.value.paymentMethods ? 
  completedSale.value.paymentMethods.map((p: any) => `• ${getPaymentMethodText(p.method)}: R$ ${formatPrice(p.amount)}`).join('\n') :
  `• ${getPaymentMethodText(completedSale.value.paymentMethod)}`
}

✅ *Status:* ${completedSale.value.status === 'completed' ? 'Finalizada' : 'Pendente'}

Obrigado pela preferência! 🙏`

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

const focusFirstProduct = () => {
  if (searchResults.value.length > 0) {
    selectedProductIndex.value = 0
    nextTick(() => {
      const element = document.querySelector('[ref="product-0"]') as HTMLElement
      element?.focus()
    })
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
    } else if (newQuantity <= (item.product.actual_stock || item.product.stock_quantity)) {
      item.quantity = newQuantity
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
      
      // Registrar débitos de vasilhames por tipo se necessário
      if (status === 'completed' && selectedCustomer.value && tiposComDebito.value.length > 0) {
        try {
          // Registrar cada tipo de vasilhame separadamente
          for (const tipo of tiposComDebito.value) {
            const debitoQuantidade = Math.abs(tipo.informados - tipo.necessarios)
            
            await vasilhamesStore.registrarSaida(
              selectedCustomer.value.id,
              tipo.modeloId, // ID do modelo específico de vasilhame
              debitoQuantidade,
              `Débito ${tipo.nome.toLowerCase()} - Venda #${result.data?.id || 'N/A'}`
            )
            
            console.log(`Registrado débito de ${debitoQuantidade} ${tipo.nome.toLowerCase()}(s) para cliente ${selectedCustomer.value.name}`)
          }
        } catch (error) {
          console.error('Erro ao registrar débitos de vasilhames por tipo:', error)
          // Não bloquear a venda por erro no registro de vasilhames
        }
      }
      
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
        notes: ''
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

/* Estilos para layout de lista */
.product-list-item {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  margin-bottom: 8px;
  background: white;
  transition: all 0.2s ease;
  gap: 16px;
}

.product-list-item:hover {
  border-color: #3b82f6;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.product-list-item.selected {
  border-color: #3b82f6;
  background-color: #eff6ff;
  box-shadow: 0 2px 4px 0 rgba(59, 130, 246, 0.3);
}

.product-list-item.disabled {
  opacity: 0.5;
  background-color: #f9fafb;
  cursor: not-allowed;
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
