<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-4 space-y-3 sm:space-y-0">
          <div class="flex items-center">
            <button
              @click="router.back()"
              class="mr-3 p-2 text-gray-600 hover:text-gray-900 rounded-md hover:bg-gray-100"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
              </svg>
            </button>
            <h1 class="text-lg sm:text-xl font-semibold text-gray-900 truncate">
              Detalhes do Pedido #{{ order?.order_number }}
            </h1>
          </div>
          
          <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
            <button
              @click="printOrder"
              class="bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center text-sm"
            >
              <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
              </svg>
              Imprimir
            </button>
            <button
              v-if="order?.status === 'pending' || order?.status === 'completed'"
              @click="cancelOrder"
              class="bg-red-600 text-white px-3 py-2 rounded-lg hover:bg-red-700 transition-colors flex items-center justify-center text-sm"
            >
              <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
              <span class="hidden sm:inline">Cancelar Pedido</span>
              <span class="sm:hidden">Cancelar</span>
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <svg class="animate-spin h-8 w-8 text-primary-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 0 01.61-1.276z" clip-rule="evenodd" />
        </svg>
        <p class="mt-2 text-gray-600">Carregando detalhes do pedido...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-red-800">{{ error }}</p>
          </div>
        </div>
      </div>

      <!-- Order Details -->
      <div v-else-if="order" class="space-y-4 sm:space-y-6">
        <!-- Order Summary -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:gap-6">
            <div>
              <h3 class="text-sm font-medium text-gray-500">Status</h3>
              <span
                :class="{
                  'bg-green-100 text-green-800': order.status === 'completed',
                  'bg-yellow-100 text-yellow-800': order.status === 'pending',
                  'bg-red-100 text-red-800': order.status === 'cancelled'
                }"
                class="inline-flex px-2 py-1 text-sm font-medium rounded-full mt-1"
              >
                {{ getStatusText(order.status) }}
              </span>
            </div>
            <div>
              <h3 class="text-sm font-medium text-gray-500">Data do Pedido</h3>
              <p class="text-sm text-gray-900 mt-1">{{ formatDate(order.created_at) }}</p>
            </div>
            <div>
              <h3 class="text-sm font-medium text-gray-500">Forma de Pagamento</h3>
              <p class="text-sm text-gray-900 mt-1">{{ getPaymentMethodText(order.payment_method) }}</p>
            </div>
            <div>
              <h3 class="text-sm font-medium text-gray-500">Vendedor</h3>
              <p class="text-sm text-gray-900 mt-1">{{ order.user?.name || 'N/A' }}</p>
            </div>
          </div>
        </div>

        <!-- Payment Methods -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Formas de Pagamento</h2>
          
          <!-- Multiple Payment Methods -->
          <div v-if="order.payment_method === 'multiple' && order.payment_methods" class="space-y-4">
            <div class="bg-blue-50 rounded-lg p-3 sm:p-4">
              <h3 class="text-sm font-medium text-blue-900 mb-3">Múltiplas Formas de Pagamento</h3>
              <div class="space-y-2">
                <div
                  v-for="(payment, index) in order.payment_methods"
                  :key="index"
                  class="flex flex-col sm:flex-row sm:justify-between sm:items-center p-3 bg-white rounded border space-y-2 sm:space-y-0"
                >
                  <div class="flex items-center">
                    <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-100 text-blue-800 text-xs font-medium rounded-full mr-3">
                      {{ index + 1 }}
                    </span>
                    <span class="text-sm font-medium text-gray-900">{{ getPaymentMethodText(payment.method) }}</span>
                  </div>
                  <span class="text-sm font-bold text-gray-900 ml-9 sm:ml-0">{{ formatPrice(payment.amount) }}</span>
                </div>
              </div>
              <div class="mt-3 pt-3 border-t border-blue-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-1 sm:space-y-0">
                  <span class="text-sm font-medium text-blue-900">Total dos Pagamentos:</span>
                  <span class="text-sm font-bold text-blue-900">
                    {{ formatPrice(order.payment_methods.reduce((sum, p) => sum + p.amount, 0)) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Single Payment Method -->
          <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6">
            <div>
              <h3 class="text-sm font-medium text-gray-500">Método de Pagamento</h3>
              <p class="text-sm text-gray-900 mt-1">{{ getPaymentMethodText(order.payment_method) }}</p>
            </div>
            <div>
              <h3 class="text-sm font-medium text-gray-500">Valor Pago</h3>
              <p class="text-sm text-gray-900 mt-1">{{ formatPrice(order.final_amount) }}</p>
            </div>
          </div>
          
          <!-- Cash Change Information -->
          <div v-if="order.payment_method === 'money' && order.cash_received && order.cash_received > order.final_amount" class="mt-4 bg-green-50 rounded-lg p-3 sm:p-4">
            <h3 class="text-sm font-medium text-green-900 mb-2">Informações do Pagamento em Dinheiro</h3>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4">
              <div>
                <span class="text-xs text-green-700">Valor Recebido:</span>
                <p class="text-sm font-medium text-green-900">{{ formatPrice(order.cash_received) }}</p>
              </div>
              <div>
                <span class="text-xs text-green-700">Valor do Pedido:</span>
                <p class="text-sm font-medium text-green-900">{{ formatPrice(order.final_amount) }}</p>
              </div>
              <div>
                <span class="text-xs text-green-700">Troco:</span>
                <p class="text-sm font-bold text-green-900">{{ formatPrice(order.cash_received - order.final_amount) }}</p>
              </div>
            </div>
          </div>
          
          <!-- Multiple Payment Methods with Cash Change -->
          <div v-if="order.payment_method === 'multiple' && order.payment_methods && hasCashPayment(order.payment_methods)" class="mt-4 bg-green-50 rounded-lg p-3 sm:p-4">
            <h3 class="text-sm font-medium text-green-900 mb-2">Informações de Troco</h3>
            <div v-for="(payment, index) in order.payment_methods" :key="index">
              <div v-if="payment.method === 'money' && payment.cash_received && payment.cash_received > payment.amount" class="flex flex-col sm:flex-row sm:justify-between sm:items-center p-2 bg-white rounded border mb-2 space-y-1 sm:space-y-0">
                <div>
                  <span class="text-xs text-green-700">Dinheiro - Recebido: {{ formatPrice(payment.cash_received) }}</span>
                </div>
                <div>
                  <span class="text-xs text-green-700">Troco: </span>
                  <span class="text-sm font-bold text-green-900">{{ formatPrice(payment.cash_received - payment.amount) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Customer Information -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Informações do Cliente</h2>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6">
            <div>
              <h3 class="text-sm font-medium text-gray-500">Nome</h3>
              <p class="text-sm text-gray-900 mt-1">{{ order.customer?.name || 'Cliente não identificado' }}</p>
            </div>
            <div v-if="order.customer?.email">
              <h3 class="text-sm font-medium text-gray-500">Email</h3>
              <p class="text-sm text-gray-900 mt-1">{{ order.customer.email }}</p>
            </div>
            <div v-if="order.customer?.phone">
              <h3 class="text-sm font-medium text-gray-500">Telefone</h3>
              <p class="text-sm text-gray-900 mt-1">{{ order.customer.phone }}</p>
            </div>
            <div v-if="order.customer?.cpf_cnpj">
              <h3 class="text-sm font-medium text-gray-500">CPF/CNPJ</h3>
              <p class="text-sm text-gray-900 mt-1">{{ order.customer.cpf_cnpj }}</p>
            </div>
          </div>
          
          <!-- Customer Credit Information -->
          <div v-if="order.customer && hasCustomerCredit(order.customer)" class="mt-6 bg-blue-50 rounded-lg p-3 sm:p-4">
            <h3 class="text-sm font-medium text-blue-900 mb-3 flex items-center">
              <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
              </svg>
              Informações de Crédito
            </h3>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4">
              <div>
                <span class="text-xs text-blue-700">Limite de Crédito:</span>
                <p class="text-sm font-medium text-blue-900">{{ formatPrice(order.customer.credit_limit) }}</p>
              </div>
              <div>
                <span class="text-xs text-blue-700">Crédito Utilizado:</span>
                <p class="text-sm font-medium text-blue-900">{{ formatPrice(order.customer.credit_used) }}</p>
              </div>
              <div>
                <span class="text-xs text-blue-700">Crédito Disponível:</span>
                <p class="text-sm font-medium" :class="getAvailableCredit(order.customer) > 0 ? 'text-green-900' : 'text-red-900'">
                  {{ formatPrice(getAvailableCredit(order.customer)) }}
                </p>
              </div>
            </div>
            <div v-if="getCreditUsagePercentage(order.customer) >= 80" class="mt-3 p-2 rounded" :class="getCreditAlertClass(order.customer)">
              <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span class="text-xs font-medium">
                  {{ getCreditAlertMessage(order.customer) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Items -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Itens do Pedido</h2>
          
          <!-- Mobile View -->
          <div class="block sm:hidden space-y-4">
            <div v-for="item in order.items" :key="item.id" class="border border-gray-200 rounded-lg p-3">
              <div class="flex justify-between items-start mb-2">
                <div class="flex-1 min-w-0">
                  <h4 class="text-sm font-medium text-gray-900 truncate">
                    {{ item.product?.name || 'Produto não encontrado' }}
                  </h4>
                  <p class="text-xs text-gray-500 mt-1">SKU: {{ item.product?.sku || 'N/A' }}</p>
                </div>
                <div class="text-right ml-3">
                  <p class="text-sm font-bold text-gray-900">{{ formatPrice(item.total_price) }}</p>
                </div>
              </div>
              
              <div class="flex justify-between items-center text-xs text-gray-600">
                <span>Qtd: {{ item.quantity }}</span>
                <span>Unit: {{ formatPrice(item.unit_price) }}</span>
              </div>
              
              <div v-if="item.product?.returnable" class="mt-2 text-xs text-orange-600 font-medium flex items-center">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                </svg>
                Produto Retornável
              </div>
              
              <div v-if="item.product?.returnable && item.product?.returnable_price" class="text-xs text-gray-500 mt-1">
                + {{ item.quantity }} vasilhame(s): {{ formatPrice(item.product.returnable_price * item.quantity) }}
              </div>
            </div>
          </div>
          
          <!-- Desktop View -->
          <div class="hidden sm:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Produto
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    SKU
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Quantidade
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Preço Unitário
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Total
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in order.items" :key="item.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                      <div class="text-sm text-gray-900 font-medium">
                        {{ item.product?.name || 'Produto não encontrado' }}
                      </div>
                      <div v-if="item.product?.returnable" class="text-xs text-orange-600 font-medium mt-1 flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                        Produto Retornável
                      </div>
                      <div v-if="item.product?.returnable && item.product?.returnable_price" class="text-xs text-gray-500 mt-1">
                        + {{ item.quantity }} vasilhame(s): {{ formatPrice(item.product.returnable_price * item.quantity) }}
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ item.product?.sku || 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ item.quantity }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">
                      {{ formatPrice(item.unit_price) }}
                    </div>
                    <div v-if="item.product?.returnable && item.product?.returnable_price" class="text-xs text-orange-600">
                      + {{ formatPrice(item.product.returnable_price) }} (vasilhame)
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 font-medium">
                      {{ formatPrice(item.total_price) }}
                    </div>
                    <div v-if="item.product?.returnable && item.product?.returnable_price" class="text-xs text-orange-600">
                      + {{ formatPrice(item.product.returnable_price * item.quantity) }}
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Returnable Products Summary -->
          <div v-if="hasReturnableProducts" class="mt-4 bg-orange-50 rounded-lg p-3 sm:p-4">
            <h3 class="text-sm font-medium text-orange-900 mb-2 flex items-center">
              <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
              </svg>
              Resumo de Produtos Retornáveis
            </h3>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4">
              <div>
                <span class="text-xs text-orange-700">Total de Vasilhames:</span>
                <p class="text-sm font-medium text-orange-900">{{ getTotalReturnableQuantity() }} unidades</p>
              </div>
              <div>
                <span class="text-xs text-orange-700">Valor dos Vasilhames:</span>
                <p class="text-sm font-medium text-orange-900">{{ formatPrice(getTotalReturnableValue()) }}</p>
              </div>
              <div>
                <span class="text-xs text-orange-700">Observação:</span>
                <p class="text-xs text-orange-700">Vasilhames devem ser devolvidos para reembolso</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Totals -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Resumo do Pedido</h2>
          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600">Subtotal:</span>
              <span class="text-sm text-gray-900 font-medium">{{ formatPrice(order.total_amount) }}</span>
            </div>
            <div v-if="order.discount_amount > 0" class="flex justify-between items-center">
              <span class="text-sm text-gray-600">Desconto:</span>
              <span class="text-sm text-red-600 font-medium">-{{ formatPrice(order.discount_amount) }}</span>
            </div>
            <div class="border-t border-gray-200 pt-3">
              <div class="flex justify-between items-center">
                <span class="text-base font-semibold text-gray-900">Total:</span>
                <span class="text-base font-semibold text-gray-900">{{ formatPrice(order.final_amount) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div v-if="order.notes" class="bg-white rounded-lg shadow p-4 sm:p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Observações</h2>
          <p class="text-sm text-gray-700 leading-relaxed">{{ order.notes }}</p>
        </div>
      </div>
    </main>

    <!-- Print Template (Hidden) -->
    <div id="print-template" class="hidden print:block print:p-8">
      <div v-if="order" class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
          <h1 class="text-2xl font-bold">PEDIDO DE VENDA</h1>
          <p class="text-lg">Pedido #{{ order.order_number }}</p>
          <p class="text-sm text-gray-600">{{ formatDate(order.created_at) }}</p>
        </div>
    
        <!-- Customer Info -->
        <div class="mb-6">
          <h2 class="text-lg font-semibold mb-2">Cliente:</h2>
          <p>{{ order.customer?.name || 'Cliente não identificado' }}</p>
          <p v-if="order.customer?.email">Email: {{ order.customer.email }}</p>
          <p v-if="order.customer?.phone">Telefone: {{ order.customer.phone }}</p>
          <p v-if="order.customer?.cpf_cnpj">CPF/CNPJ: {{ order.customer.cpf_cnpj }}</p>
          
          <!-- Customer Credit Information in Print -->
          <div v-if="order.customer && hasCustomerCredit(order.customer)" class="mt-4 p-3 border border-gray-300 rounded">
            <h3 class="font-semibold mb-2">Informações de Crédito:</h3>
            <div class="grid grid-cols-3 gap-4 text-sm">
              <div>
                <span class="font-medium">Limite:</span> {{ formatPrice(order.customer.credit_limit) }}
              </div>
              <div>
                <span class="font-medium">Utilizado:</span> {{ formatPrice(order.customer.credit_used) }}
              </div>
              <div>
                <span class="font-medium">Disponível:</span> {{ formatPrice(getAvailableCredit(order.customer)) }}
              </div>
            </div>
            <div v-if="getCreditUsagePercentage(order.customer) >= 80" class="mt-2 text-sm">
              <strong>⚠️ {{ getCreditAlertMessage(order.customer) }}</strong>
            </div>
          </div>
        </div>
    
        <!-- Items Table -->
        <table class="w-full border-collapse border border-gray-300 mb-6">
          <thead>
            <tr class="bg-gray-100">
              <th class="border border-gray-300 px-4 py-2 text-left">Produto</th>
              <th class="border border-gray-300 px-4 py-2 text-left">SKU</th>
              <th class="border border-gray-300 px-4 py-2 text-center">Qtd</th>
              <th class="border border-gray-300 px-4 py-2 text-right">Preço Unit.</th>
              <th class="border border-gray-300 px-4 py-2 text-right">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in order.items" :key="item.id">
              <td class="border border-gray-300 px-4 py-2">
                <div>
                  {{ item.product?.name || 'Produto não encontrado' }}
                  <div v-if="item.product?.returnable" class="text-xs text-gray-600 mt-1">
                    🔄 Produto Retornável
                  </div>
                </div>
              </td>
              <td class="border border-gray-300 px-4 py-2">{{ item.product?.sku || 'N/A' }}</td>
              <td class="border border-gray-300 px-4 py-2 text-center">{{ item.quantity }}</td>
              <td class="border border-gray-300 px-4 py-2 text-right">
                <div>{{ formatPrice(item.unit_price) }}</div>
                <div v-if="item.product?.returnable && item.product?.returnable_price" class="text-xs text-gray-600">
                  + {{ formatPrice(item.product.returnable_price) }} (vasilhame)
                </div>
              </td>
              <td class="border border-gray-300 px-4 py-2 text-right">
                <div>{{ formatPrice(item.total_price) }}</div>
                <div v-if="item.product?.returnable && item.product?.returnable_price" class="text-xs text-gray-600">
                  + {{ formatPrice(item.product.returnable_price * item.quantity) }}
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        
        <!-- Returnable Products Summary in Print -->
        <div v-if="hasReturnableProducts" class="mb-6 p-3 border border-gray-300 rounded">
          <h3 class="font-semibold mb-2">🔄 Resumo de Produtos Retornáveis:</h3>
          <div class="grid grid-cols-3 gap-4 text-sm">
            <div>
              <span class="font-medium">Total Vasilhames:</span> {{ getTotalReturnableQuantity() }} unidades
            </div>
            <div>
              <span class="font-medium">Valor Vasilhames:</span> {{ formatPrice(getTotalReturnableValue()) }}
            </div>
            <div>
              <span class="font-medium">Observação:</span> Devolver para reembolso
            </div>
          </div>
        </div>
    
        <!-- Totals -->
        <div class="text-right mb-6">
          <p>Subtotal: {{ formatPrice(order.total_amount) }}</p>
          <p v-if="order.discount_amount > 0">Desconto: -{{ formatPrice(order.discount_amount) }}</p>
          <p class="text-lg font-bold">Total: {{ formatPrice(order.final_amount) }}</p>
        </div>
    
        <!-- Payment Method Details -->
        <div class="mb-6">
          <h3 class="font-semibold mb-2">Forma(s) de Pagamento:</h3>
          
          <!-- Multiple Payment Methods in Print -->
          <div v-if="order.payment_method === 'multiple' && order.payment_methods" class="mb-4">
            <div class="border border-gray-300 rounded p-3">
              <h4 class="font-medium mb-2">Múltiplas Formas de Pagamento:</h4>
              <div v-for="(payment, index) in order.payment_methods" :key="index" class="flex justify-between py-1">
                <span>{{ index + 1 }}. {{ getPaymentMethodText(payment.method) }}</span>
                <span class="font-medium">{{ formatPrice(payment.amount) }}</span>
              </div>
              <div class="border-t border-gray-300 mt-2 pt-2 flex justify-between font-bold">
                <span>Total dos Pagamentos:</span>
                <span>{{ formatPrice(order.payment_methods.reduce((sum, p) => sum + p.amount, 0)) }}</span>
              </div>
            </div>
          </div>
          
          <!-- Single Payment Method in Print -->
          <div v-else>
            <p><strong>Método:</strong> {{ getPaymentMethodText(order.payment_method) }}</p>
            <p><strong>Valor Pago:</strong> {{ formatPrice(order.final_amount) }}</p>
          </div>
          
          <!-- Cash Change Information in Print -->
          <div v-if="order.payment_method === 'money' && order.cash_received && order.cash_received > order.final_amount" class="mt-3 p-2 border border-gray-300 rounded">
            <h4 class="font-medium mb-1">💰 Informações do Pagamento em Dinheiro:</h4>
            <div class="grid grid-cols-3 gap-4 text-sm">
              <div><span class="font-medium">Recebido:</span> {{ formatPrice(order.cash_received) }}</div>
              <div><span class="font-medium">Pedido:</span> {{ formatPrice(order.final_amount) }}</div>
              <div><span class="font-medium">Troco:</span> {{ formatPrice(order.cash_received - order.final_amount) }}</div>
            </div>
          </div>
          
          <!-- Multiple Payment Methods with Cash Change in Print -->
          <div v-if="order.payment_method === 'multiple' && order.payment_methods && hasCashPayment(order.payment_methods)" class="mt-3 p-2 border border-gray-300 rounded">
            <h4 class="font-medium mb-1">💰 Informações de Troco:</h4>
            <div v-for="(payment, index) in order.payment_methods" :key="index">
              <div v-if="payment.method === 'money' && payment.cash_received && payment.cash_received > payment.amount" class="text-sm py-1">
                <span>Dinheiro - Recebido: {{ formatPrice(payment.cash_received) }} | Troco: {{ formatPrice(payment.cash_received - payment.amount) }}</span>
              </div>
            </div>
          </div>
          
          <p class="mt-2"><strong>Status:</strong> {{ getStatusText(order.status) }}</p>
          <p><strong>Vendedor:</strong> {{ order.user?.name || 'N/A' }}</p>
        </div>
    
        <!-- Notes -->
        <div v-if="order.notes" class="mb-6">
          <h3 class="font-semibold">Observações:</h3>
          <p>{{ order.notes }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useOrdersStore } from '@/stores/orders'
import type { Order } from '@/stores/orders'

const router = useRouter()
const route = useRoute()
const ordersStore = useOrdersStore()

const order = ref<Order | null>(null)
const loading = ref(false)
const error = ref<string | null>(null)

const loadOrder = async () => {
  try {
    loading.value = true
    error.value = null
    
    const orderId = route.params.id as string
    const response = await ordersStore.fetchOrderById(orderId)
    order.value = response
  } catch (err: any) {
    error.value = err.message || 'Erro ao carregar detalhes do pedido'
  } finally {
    loading.value = false
  }
}

const printOrder = () => {
  // Mostrar template de impressão
  const printContent = document.getElementById('print-template')
  if (printContent) {
    printContent.classList.remove('hidden')
    window.print()
    printContent.classList.add('hidden')
  }
}

const cancelOrder = async () => {
  if (!order.value) return
  
  const confirmMessage = order.value.status === 'completed' 
    ? 'Tem certeza que deseja cancelar este pedido? O estoque será devolvido.'
    : 'Tem certeza que deseja cancelar este pedido?'
    
  if (confirm(confirmMessage)) {
    try {
      await ordersStore.cancelOrder(order.value.id)
      // Recarregar dados do pedido
      await loadOrder()
    } catch (err: any) {
      alert(err.message || 'Erro ao cancelar pedido')
    }
  }
}

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(price)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleString('pt-BR')
}

const getStatusText = (status: string) => {
  const statusMap = {
    pending: 'Pendente',
    completed: 'Concluído',
    cancelled: 'Cancelado'
  }
  return statusMap[status as keyof typeof statusMap] || status
}

 const getPaymentMethodText = (method: string) => {
   const methodMap = {
     money: 'Dinheiro',
     card: 'Cartão',
     pix: 'PIX',
     credit: 'Crediário'
   }
   return methodMap[method as keyof typeof methodMap] || method
 }
 
 const hasCashPayment = (paymentMethods: any[]) => {
   return paymentMethods.some(payment => payment.method === 'money' && payment.cash_received && payment.cash_received > payment.amount)
 }
 
 // Computed properties for returnable products
 const hasReturnableProducts = computed(() => {
   if (!order.value?.items) return false
   return order.value.items.some(item => item.product?.returnable)
 })
 
 const getTotalReturnableQuantity = () => {
   if (!order.value?.items) return 0
   return order.value.items.reduce((total, item) => {
     if (item.product?.returnable) {
       return total + item.quantity
     }
     return total
   }, 0)
 }
 
 const getTotalReturnableValue = () => {
   if (!order.value?.items) return 0
   return order.value.items.reduce((total, item) => {
     if (item.product?.returnable && item.product?.returnable_price) {
       return total + (item.product.returnable_price * item.quantity)
     }
     return total
   }, 0)
 }
 
 // Customer credit functions
 const hasCustomerCredit = (customer: any) => {
   return customer && (customer.credit_limit > 0 || customer.credit_used > 0)
 }
 
 const getAvailableCredit = (customer: any) => {
   if (!customer) return 0
   return (customer.credit_limit || 0) - (customer.credit_used || 0)
 }
 
 const getCreditUsagePercentage = (customer: any) => {
   if (!customer || !customer.credit_limit || customer.credit_limit === 0) return 0
   return ((customer.credit_used || 0) / customer.credit_limit) * 100
 }
 
 const getCreditAlertClass = (customer: any) => {
   const percentage = getCreditUsagePercentage(customer)
   if (percentage >= 95) return 'bg-red-100 border border-red-300'
   if (percentage >= 80) return 'bg-yellow-100 border border-yellow-300'
   return 'bg-blue-100 border border-blue-300'
 }
 
 const getCreditAlertMessage = (customer: any) => {
   const percentage = getCreditUsagePercentage(customer)
   if (percentage >= 95) return 'Limite de crédito quase esgotado!'
   if (percentage >= 80) return 'Atenção: Alto uso do limite de crédito'
   return 'Uso moderado do limite de crédito'
 }
onMounted(() => {
  loadOrder()
})
</script>

<style>
@media print {
  body * {
    visibility: hidden;
  }
  
  #print-template,
  #print-template * {
    visibility: visible;
  }
  
  #print-template {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}
</style>