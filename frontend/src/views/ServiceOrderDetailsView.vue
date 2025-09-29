<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center">
            <button
              @click="router.back()"
              class="mr-4 p-2 text-gray-600 hover:text-gray-900"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
              </svg>
            </button>
            <div>
              <h1 class="text-xl font-semibold text-gray-900">{{ serviceOrder?.order_number }}</h1>
              <p class="text-sm text-gray-600">{{ serviceOrder?.customer?.name }}</p>
            </div>
          </div>
          
          <div class="flex space-x-2">
            <button
              v-if="serviceOrder && serviceOrder.remaining_amount > 0"
              @click="openPaymentModal"
              class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors"
            >
              Registrar Pagamento
            </button>
            <button
              v-if="serviceOrder && canApprove(serviceOrder)"
              @click="approveServiceOrder"
              class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors"
            >
              Aprovar e Iniciar Serviço
            </button>
            <button
              v-if="serviceOrder && canApproveCustomer(serviceOrder)"
              @click="approveCustomerServiceOrder"
              class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition-colors"
            >
              Aprovar Orçamento (Cliente)
            </button>
            <button
              v-if="serviceOrder && canComplete(serviceOrder)"
              @click="completeServiceOrder"
              class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
            >
              Concluir
            </button>
            <button
              v-if="serviceOrder && canCancel(serviceOrder)"
              @click="cancelServiceOrder"
              class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors"
            >
              Cancelar
            </button>
            <button
              @click="editServiceOrder"
              class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors"
            >
              Editar
            </button>
            <button
              @click="printPage"
              class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors"
            >
              Imprimir
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
        <span class="ml-2 text-gray-600">Carregando...</span>
      </div>
      
      <!-- Content -->
      <div v-else-if="serviceOrder" class="space-y-6">
        <!-- Status e Informações Básicas -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex justify-between items-start mb-4">
            <div>
              <h2 class="text-lg font-medium text-gray-900">Informações da OS</h2>
            </div>
            <span 
              :class="getStatusBadgeClass(serviceOrder.status)"
              class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
            >
              {{ getStatusLabel(serviceOrder.status) }}
            </span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Número da OS</label>
              <p class="text-sm text-gray-900">{{ serviceOrder.order_number }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Data de Abertura</label>
              <p class="text-sm text-gray-900">{{ formatDate(serviceOrder.opening_date) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Previsão de Entrega</label>
              <p class="text-sm text-gray-900">
                {{ serviceOrder.expected_delivery_date ? formatDate(serviceOrder.expected_delivery_date) : 'Não informada' }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
              <p class="text-sm text-gray-900">{{ serviceOrder.customer?.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Veículo/Equipamento</label>
              <p class="text-sm text-gray-900">
                {{ serviceOrder.vehicle ? getVehicleIdentification(serviceOrder.vehicle) : 'Não informado' }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Responsável Técnico</label>
              <div v-if="!editingTechnicalResponsible" class="flex items-center justify-between">
                <p class="text-sm text-gray-900">{{ serviceOrder.technical_responsible?.name }}</p>
                <button
                  @click="startEditingTechnicalResponsible"
                  class="ml-2 text-blue-600 hover:text-blue-800 text-sm"
                >
                  Editar
                </button>
              </div>
              <div v-else class="flex items-center space-x-2">
                <select 
                  v-model="editingTechnicalResponsibleData" 
                  class="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                  <option value="">Selecione um técnico</option>
                  <option v-for="user in users" :key="user.id" :value="user.id">
                    {{ user.name }}
                  </option>
                </select>
                <button
                  @click="saveTechnicalResponsible"
                  class="bg-green-600 text-white px-3 py-2 rounded-md hover:bg-green-700 transition-colors"
                >
                  Salvar
                </button>
                <button
                  @click="cancelEditingTechnicalResponsible"
                  class="bg-gray-600 text-white px-3 py-2 rounded-md hover:bg-gray-700 transition-colors"
                >
                  Cancelar
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Descrições -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Descrições</h2>
          
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Descrição do Problema</label>
              <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ serviceOrder.problem_description }}</p>
            </div>

            <div v-if="serviceOrder.diagnosis">
              <label class="block text-sm font-medium text-gray-700 mb-1">Diagnóstico</label>
              <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ serviceOrder.diagnosis }}</p>
            </div>

            <div v-if="serviceOrder.internal_observations">
              <label class="block text-sm font-medium text-gray-700 mb-1">Observações Internas</label>
              <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ serviceOrder.internal_observations }}</p>
            </div>

            <div v-if="serviceOrder.customer_observations">
              <label class="block text-sm font-medium text-gray-700 mb-1">Observações do Cliente</label>
              <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ serviceOrder.customer_observations }}</p>
            </div>
          </div>
        </div>

        <!-- Itens da OS -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Itens da OS</h2>
          
          <div v-if="serviceOrder.items?.length === 0" class="text-center py-8 text-gray-500">
            Nenhum item adicionado
          </div>

          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tipo
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Descrição
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
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Estoque
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in serviceOrder.items" :key="item.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      :class="item.item_type === 'product' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    >
                      {{ item.item_type === 'product' ? 'Produto' : 'Serviço' }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-900">{{ item.description }}</div>
                    <div v-if="item.observations" class="text-sm text-gray-500">{{ item.observations }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ item.quantity }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatCurrency(item.unit_price) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatCurrency(item.total_price) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      v-if="item.item_type === 'product'"
                      :class="item.stock_removed ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    >
                      {{ item.stock_removed ? 'Removido' : 'Pendente' }}
                    </span>
                    <span v-else class="text-gray-400">-</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Resumo Financeiro -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Resumo Financeiro</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Total Produtos</label>
              <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(getProductsTotal()) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Total Serviços</label>
              <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(getServicesTotal()) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Desconto</label>
              <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(serviceOrder.discount_amount) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Valor Final</label>
              <p class="text-2xl font-semibold text-primary-600">{{ formatCurrency(serviceOrder.final_amount) }}</p>
            </div>
          </div>

          <div class="mt-4 pt-4 border-t border-gray-200">
            <div v-if="serviceOrder.payment_method" class="flex justify-between items-center">
              <span class="text-sm font-medium text-gray-700">Forma de Pagamento</span>
              <span class="text-sm text-gray-900">{{ getPaymentMethodLabel(serviceOrder.payment_method) }}</span>
            </div>
            
            <!-- Informações de Pagamentos -->
            <div v-if="serviceOrderPaymentsStore.payments.length > 0" class="mt-4 pt-4 border-t border-gray-200">
              <h4 class="text-sm font-medium text-gray-700 mb-3">Pagamentos Registrados</h4>
              <div class="space-y-2">
                <div v-for="payment in serviceOrderPaymentsStore.payments" :key="payment.id" 
                     class="flex justify-between items-center p-2 bg-gray-50 rounded">
                  <div>
                    <span class="text-sm font-medium text-gray-900">{{ formatCurrency(payment.amount) }}</span>
                    <span class="text-xs text-gray-600 ml-2">{{ getPaymentMethodLabel(payment.payment_method) }}</span>
                  </div>
                  <span class="text-xs text-gray-500">{{ formatDate(payment.paid_at) }}</span>
                </div>
              </div>
              
              <div class="mt-3 pt-3 border-t border-gray-200">
                <div class="flex justify-between items-center">
                  <span class="text-sm font-medium text-gray-700">Total Pago:</span>
                  <span class="text-sm font-semibold text-green-600">{{ formatCurrency(serviceOrder.total_paid || 0) }}</span>
                </div>
                <div class="flex justify-between items-center mt-1">
                  <span class="text-sm font-medium text-gray-700">Valor Restante:</span>
                  <span class="text-sm font-semibold" :class="serviceOrder.remaining_amount > 0 ? 'text-red-600' : 'text-green-600'">
                    {{ formatCurrency(serviceOrder.remaining_amount || 0) }}
                  </span>
                </div>
              </div>
            </div>
            
            <div v-if="serviceOrder.customer_approved" class="flex justify-between items-center mt-2">
              <span class="text-sm font-medium text-gray-700">Aprovado pelo Cliente</span>
              <span class="text-sm text-green-600">Sim</span>
            </div>
            <div v-if="serviceOrder.approved_at" class="flex justify-between items-center mt-2">
              <span class="text-sm font-medium text-gray-700">Data de Aprovação</span>
              <span class="text-sm text-gray-900">{{ formatDateTime(serviceOrder.approved_at) }}</span>
            </div>
            <div v-if="serviceOrder.completion_date" class="flex justify-between items-center mt-2">
              <span class="text-sm font-medium text-gray-700">Data de Conclusão</span>
              <span class="text-sm text-gray-900">{{ formatDate(serviceOrder.completion_date) }}</span>
            </div>
          </div>
        </div>

        <!-- Pedido Gerado -->
        <div v-if="serviceOrder.order" class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Pedido Gerado</h2>
          
          <div class="flex justify-between items-center">
            <div>
              <p class="text-sm text-gray-700">Pedido #{{ serviceOrder.order.order_number }}</p>
              <p class="text-sm text-gray-500">Criado em {{ formatDateTime(serviceOrder.order.created_at) }}</p>
            </div>
            <button
              @click="viewOrder(serviceOrder.order.id)"
              class="text-primary-600 hover:text-primary-900 text-sm font-medium"
            >
              Ver Pedido
            </button>
          </div>
        </div>
      </div>
      
      <!-- Error State -->
      <div v-else class="text-center py-12">
        <p class="text-gray-600">Erro ao carregar a ordem de serviço</p>
      </div>
    </main>

    <!-- Modal de Pagamento -->
    <div v-if="showPaymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-4 sm:top-20 mx-auto p-4 sm:p-6 border w-full max-w-lg shadow-lg rounded-lg bg-white m-4">
        <div class="flex justify-between items-center mb-4 sm:mb-6">
          <h3 class="text-lg sm:text-xl font-bold text-gray-900">Registrar Pagamento - OS #{{ serviceOrder?.order_number }}</h3>
          <button
            @click="closePaymentModal"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg class="h-5 h-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Resumo dos Valores -->
        <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-blue-50 rounded-lg">
          <div class="flex justify-between items-center">
            <span class="text-sm font-medium text-gray-700">Valor Total:</span>
            <span class="text-lg font-bold text-blue-600">{{ formatCurrency(serviceOrder?.final_amount || 0) }}</span>
          </div>
          <div v-if="serviceOrder?.total_paid > 0" class="flex justify-between items-center mt-2">
            <span class="text-sm font-medium text-gray-700">Valor Pago:</span>
            <span class="text-lg font-bold text-green-600">{{ formatCurrency(serviceOrder?.total_paid || 0) }}</span>
          </div>
          <div class="flex justify-between items-center mt-2">
            <span class="text-sm font-medium text-gray-700">Valor Restante:</span>
            <span class="text-lg font-bold text-red-600">{{ formatCurrency(serviceOrder?.remaining_amount || 0) }}</span>
          </div>
        </div>

        <!-- Formulário de Pagamento -->
        <form @submit.prevent="registerPayment">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Valor do Pagamento (R$)</label>
              <input
                v-model="paymentForm.amount"
                type="number"
                step="0.01"
                min="0.01"
                :max="getMaxPaymentAmount()"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                placeholder="0,00"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Forma de Pagamento</label>
              
              <!-- Checkbox para múltiplas formas de pagamento -->
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
                  v-model="paymentForm.payment_method"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                >
                  <option value="">Selecione...</option>
                  <option value="money">💵 Dinheiro</option>
                  <option value="card">💳 Cartão</option>
                  <option value="pix">📱 PIX</option>
                  <option value="credit" v-if="canUseCredit">💰 A Prazo (Crédito)</option>
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
                        <option value="">Selecione...</option>
                        <option value="money">💵 Dinheiro</option>
                        <option value="card">💳 Cartão</option>
                        <option value="pix">📱 PIX</option>
                        <option v-if="canUseCredit" value="credit">💰 A Prazo</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-700 mb-1">Valor (R$)</label>
                      <input
                        v-model="payment.amount"
                        type="number"
                        step="0.01"
                        min="0"
                        :max="getMaxPaymentAmount()"
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
                      <span class="text-gray-600">Valor máximo:</span>
                      <span class="font-bold">{{ formatCurrency(getMaxPaymentAmount()) }}</span>
                    </div>
                    <div class="flex justify-between text-sm mb-2">
                      <span class="text-gray-600">Total a pagar:</span>
                      <span class="font-bold" :class="totalMultiplePayments >= getMaxPaymentAmount() ? 'text-green-600' : 'text-blue-600'">
                        {{ formatCurrency(totalMultiplePayments) }}
                      </span>
                    </div>
                    <div v-if="totalMultiplePayments < getMaxPaymentAmount()" class="flex justify-between text-sm">
                      <span class="text-red-600 font-medium">Falta pagar:</span>
                      <span class="font-bold text-red-600">{{ formatCurrency(getMaxPaymentAmount() - totalMultiplePayments) }}</span>
                    </div>
                    <div v-else-if="totalMultiplePayments > getMaxPaymentAmount()" class="flex justify-between text-sm">
                      <span class="text-orange-600 font-medium">Excesso:</span>
                      <span class="font-bold text-orange-600">{{ formatCurrency(totalMultiplePayments - getMaxPaymentAmount()) }}</span>
                    </div>
                    <div v-else class="text-center text-sm">
                      <span class="text-green-600 font-bold">✓ Valor correto</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Informações de crédito quando A Prazo for selecionado -->
            <div v-if="paymentForm.payment_method === 'credit' && serviceOrder?.customer" class="bg-blue-50 p-3 rounded-md">
              <h4 class="text-sm font-medium text-blue-800 mb-2">Informações de Crédito</h4>
              <div class="text-sm text-blue-700 space-y-1">
                <div>Limite: {{ formatCurrency(serviceOrder.customer.credit_limit) }}</div>
                <div>Usado: {{ formatCurrency(serviceOrder.customer.credit_used) }}</div>
                <div>Disponível: {{ formatCurrency(getAvailableCredit()) }}</div>
              </div>
              <div v-if="!hasEnoughCredit" class="mt-2 text-sm text-red-600 font-medium">
                ⚠️ Crédito insuficiente para este pagamento!
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Referência do Pagamento</label>
              <input
                v-model="paymentForm.payment_reference"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                placeholder="Ex: Número do cartão, chave PIX, etc."
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
              <textarea
                v-model="paymentForm.notes"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                placeholder="Observações sobre o pagamento..."
              ></textarea>
            </div>
          </div>

          <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
            <button
              type="submit"
              :disabled="paymentSubmitting"
              class="flex-1 bg-primary-600 text-white py-3 px-4 rounded-md hover:bg-primary-700 disabled:opacity-50 font-medium"
            >
              {{ paymentSubmitting ? 'Registrando...' : 'Registrar Pagamento' }}
            </button>
            <button
              type="button"
              @click="closePaymentModal"
              class="flex-1 sm:flex-none bg-gray-300 text-gray-700 py-3 px-4 rounded-md hover:bg-gray-400 font-medium"
            >
              Cancelar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useServiceOrdersStore } from '@/stores/serviceOrders'
import { useServiceOrderPaymentsStore } from '@/stores/serviceOrderPayments'
import { useVehiclesStore } from '@/stores/vehicles'
import { useUsersStore } from '@/stores/users'

const router = useRouter()
const route = useRoute()
const serviceOrdersStore = useServiceOrdersStore()
const serviceOrderPaymentsStore = useServiceOrderPaymentsStore()
const vehiclesStore = useVehiclesStore()
const usersStore = useUsersStore()

const serviceOrder = ref(null)
const loading = ref(false)
const editingTechnicalResponsible = ref(false)
const editingTechnicalResponsibleData = ref('')
const showPaymentModal = ref(false)
const paymentSubmitting = ref(false)

// Sistema de múltiplas formas de pagamento
const multiplePayments = ref(false)
const paymentMethods = ref([{ method: '', amount: 0 }])

const paymentForm = ref({
  amount: 0,
  payment_method: 'dinheiro',
  payment_reference: '',
  notes: ''
})

// Computed
const users = computed(() => usersStore.users)

// Computed properties para validação de crédito
const canUseCredit = computed(() => {
  const customer = (serviceOrder.value as any)?.customer
  return customer && safeNumber(customer.credit_limit) > 0
})

const hasEnoughCredit = computed(() => {
  if (!canUseCredit.value) return false
  const available = getAvailableCredit()
  const amount = safeNumber(paymentForm.value.amount)
  return available >= amount
})

const getAvailableCredit = (): number => {
  const customer = (serviceOrder.value as any)?.customer
  if (!customer) return 0
  const limit = safeNumber(customer.credit_limit)
  const used = safeNumber(customer.credit_used)
  return Math.max(0, limit - used)
}

// Computed properties para múltiplas formas de pagamento
const totalMultiplePayments = computed(() => {
  return paymentMethods.value.reduce((sum, payment) => {
    return sum + (parseFloat(payment.amount) || 0)
  }, 0)
})

// Funções para múltiplas formas de pagamento
const addPaymentMethod = () => {
  paymentMethods.value.push({ method: '', amount: 0 })
}

const removePaymentMethod = (index) => {
  if (paymentMethods.value.length > 1) {
    paymentMethods.value.splice(index, 1)
  }
}

// Helpers numéricos e totais seguros
const safeNumber = (value: any): number => {
  if (value === null || value === undefined) return 0
  if (typeof value === 'number') return Number.isFinite(value) ? value : 0
  if (typeof value === 'string') {
    const s = value.trim()
    if (s === '') return 0
    let normalized = s
    // Formato brasileiro com milhares e vírgula decimal (ex: 1.234,56)
    if (/^-?\d{1,3}(\.\d{3})*,\d+$/.test(s)) {
      normalized = s.replace(/\./g, '').replace(',', '.')
    } else if (s.includes(',') && !s.includes('.')) {
      // Apenas vírgula como decimal
      normalized = s.replace(',', '.')
    } else {
      // Remove caracteres não numéricos mantendo ponto e sinal
      normalized = s.replace(/[^\d.\-]/g, '')
    }
    const num = parseFloat(normalized)
    return Number.isFinite(num) ? num : 0
  }
  const num = Number(value)
  return Number.isFinite(num) ? num : 0
}

const getProductsTotal = (): number => {
  const order: any = serviceOrder.value as any
  const items = order?.items
  if (Array.isArray(items) && items.length > 0) {
    return items
      .filter((i: any) => i?.item_type === 'product')
      .reduce((sum: number, i: any) => {
        const total = safeNumber(i?.total_price)
        if (total > 0) return sum + total
        return sum + safeNumber(i?.unit_price) * safeNumber(i?.quantity)
      }, 0)
  }
  return safeNumber(order?.total_products)
}

const getServicesTotal = (): number => {
  const order: any = serviceOrder.value as any
  const items = order?.items
  if (Array.isArray(items) && items.length > 0) {
    return items
      .filter((i: any) => i?.item_type === 'service')
      .reduce((sum: number, i: any) => {
        const total = safeNumber(i?.total_price)
        if (total > 0) return sum + total
        return sum + safeNumber(i?.unit_price) * safeNumber(i?.quantity)
      }, 0)
  }
  return safeNumber(order?.total_services)
}

const approveServiceOrder = async () => {
  if (confirm('Deseja aprovar e iniciar esta ordem de serviço?')) {
    const result = await serviceOrdersStore.approveServiceOrder(Number(route.params.id))
    if (result.success) {
      loadServiceOrder()
    } else {
      alert(result.error)
    }
  }
}

const approveCustomerServiceOrder = async () => {
  const notes = prompt('Observações sobre a aprovação do orçamento (opcional):')
  if (notes !== null) { // null significa que o usuário cancelou
    const result = await serviceOrdersStore.approveCustomerServiceOrder(Number(route.params.id), notes || undefined)
    if (result.success) {
      alert('Orçamento aprovado pelo cliente com sucesso!')
      loadServiceOrder()
    } else {
      alert(result.error)
    }
  }
}

const completeServiceOrder = async () => {
  if (confirm('Deseja concluir esta ordem de serviço?')) {
    const result = await serviceOrdersStore.completeServiceOrder(Number(route.params.id))
    if (result.success) {
      loadServiceOrder()
    } else {
      alert(result.error)
    }
  }
}

const cancelServiceOrder = async () => {
  if (confirm('Deseja cancelar esta ordem de serviço?')) {
    const result = await serviceOrdersStore.deleteServiceOrder(Number(route.params.id))
    if (result.success) {
      router.push('/service-orders')
    } else {
      alert(result.error)
    }
  }
}

const editServiceOrder = () => {
  router.push(`/service-orders/${route.params.id}/edit`)
}

const viewOrder = (orderId: number) => {
  router.push(`/orders/${orderId}`)
}

const getStatusLabel = (status: string) => {
  return serviceOrdersStore.getStatusLabel(status)
}


const getPaymentMethodLabel = (method: string) => {
  switch (method) {
    case 'money': return 'Dinheiro'
    case 'card': return 'Cartão'
    case 'pix': return 'PIX'
    case 'credit': return 'A Prazo (Crédito)'
    default: return method
  }
}

const getStatusBadgeClass = (status: string) => {
  const color = serviceOrdersStore.getStatusColor(status)
  const colorMap = {
    'blue': 'bg-blue-100 text-blue-800',
    'yellow': 'bg-yellow-100 text-yellow-800',
    'orange': 'bg-orange-100 text-orange-800',
    'green': 'bg-green-100 text-green-800',
    'red': 'bg-red-100 text-red-800',
    'gray': 'bg-gray-100 text-gray-800'
  }
  return colorMap[color as keyof typeof colorMap] || 'bg-gray-100 text-gray-800'
}

const getVehicleIdentification = (vehicle: any) => {
  return vehiclesStore.getShortIdentification(vehicle)
}

const canApprove = (serviceOrder: any) => {
  return serviceOrder?.status === 'aguardando_aprovacao'
}

const canApproveCustomer = (serviceOrder: any) => {
  return serviceOrder?.billing_type === 'orcamento' && !serviceOrder?.customer_approved
}

const canComplete = (serviceOrder: any) => {
  return serviceOrder && ['aberta', 'em_andamento'].includes(serviceOrder.status)
}

const canCancel = (serviceOrder: any) => {
  return serviceOrder && ['aberta', 'em_andamento', 'aguardando_aprovacao'].includes(serviceOrder.status)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('pt-BR')
}

const formatDateTime = (date: string) => {
  return new Date(date).toLocaleString('pt-BR')
}

const formatCurrency = (value: any) => {
  const num = safeNumber(value)
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(num)
}

// Lifecycle
onMounted(async () => {
  await usersStore.fetchUsers()
  loadServiceOrder()
})

const loadServiceOrder = async () => {
  loading.value = true
  try {
    const id = Number(route.params.id)
    // Limpar pagamentos anteriores para evitar dados defasados
    if (serviceOrderPaymentsStore.clearPayments) {
      serviceOrderPaymentsStore.clearPayments()
    }

    const result = await serviceOrdersStore.fetchServiceOrder(id)
    if (result.success) {
      serviceOrder.value = result.serviceOrder
      // Carregar pagamentos relacionados
      try {
        await serviceOrderPaymentsStore.fetchPayments(id)
      } catch (e) {
        // Silenciar erro de pagamentos para não bloquear a tela
        console.warn('Falha ao carregar pagamentos da OS', e)
      }
    } else {
      alert(result.error)
      router.push('/service-orders')
    }
  } finally {
    loading.value = false
  }
}

// Edição do responsável técnico
const startEditingTechnicalResponsible = () => {
  editingTechnicalResponsibleData.value = (serviceOrder.value as any)?.technical_responsible_id || ''
  editingTechnicalResponsible.value = true
}

const saveTechnicalResponsible = async () => {
  if (!editingTechnicalResponsibleData.value) {
    alert('Selecione um técnico responsável')
    return
  }
  const id = Number(route.params.id)
  const payload = { technical_responsible_id: editingTechnicalResponsibleData.value as any }
  const result = await serviceOrdersStore.updateServiceOrder(id, payload)
  if (result.success) {
    editingTechnicalResponsible.value = false
    await loadServiceOrder()
  } else {
    alert(result.error)
  }
}

const cancelEditingTechnicalResponsible = () => {
  editingTechnicalResponsible.value = false
  editingTechnicalResponsibleData.value = ''
}

// Modal de pagamento
const openPaymentModal = () => {
  // Valor padrão: restante a pagar
  paymentForm.value.amount = getMaxPaymentAmount()
  paymentForm.value.payment_method = 'money'
  paymentForm.value.payment_reference = ''
  paymentForm.value.notes = ''
  showPaymentModal.value = true
}

const closePaymentModal = () => {
  if (!paymentSubmitting.value) {
    showPaymentModal.value = false
  }
}

const getMaxPaymentAmount = (): number => {
  const so: any = serviceOrder.value as any
  const remaining = safeNumber(so?.remaining_amount)
  if (remaining > 0) return remaining
  // Fallback se backend não fornecer remaining_amount
  const finalAmount = safeNumber(so?.final_amount)
  const totalPaid = safeNumber(so?.total_paid)
  const calcRemaining = Math.max(0, finalAmount - totalPaid)
  return calcRemaining
}

const registerPayment = async () => {
  const id = Number(route.params.id)
  
  if (multiplePayments.value) {
    // Validação para múltiplas formas de pagamento
    if (paymentMethods.value.length === 0) {
      alert('Adicione pelo menos uma forma de pagamento')
      return
    }

    for (let i = 0; i < paymentMethods.value.length; i++) {
      const payment = paymentMethods.value[i]
      if (!payment.method) {
        alert(`Selecione a forma de pagamento ${i + 1}`)
        return
      }
      if (!payment.amount || parseFloat(payment.amount) <= 0) {
        alert(`Informe um valor válido para o pagamento ${i + 1}`)
        return
      }
      
      // Validação de crédito para pagamentos a prazo
      if (payment.method === 'credit') {
        const available = getAvailableCredit()
        if (parseFloat(payment.amount) > available) {
          alert(`Crédito insuficiente para o pagamento ${i + 1}. Crédito disponível: ${formatCurrency(available)}`)
          return
        }
      }
    }

    paymentSubmitting.value = true
    try {
      // Registrar cada pagamento individualmente
      for (const payment of paymentMethods.value) {
        await serviceOrderPaymentsStore.createPayment(id, {
          amount: parseFloat(payment.amount),
          payment_method: payment.method,
          payment_reference: paymentForm.value.payment_reference || undefined,
          notes: paymentForm.value.notes || undefined
        })
      }
      
      // Recarregar dados da OS e fechar modal
      await loadServiceOrder()
      showPaymentModal.value = false
      
      // Reset do formulário
      multiplePayments.value = false
      paymentMethods.value = [{ method: '', amount: 0 }]
    } catch (e: any) {
      alert(e?.response?.data?.message || e?.message || 'Erro ao registrar pagamentos')
    } finally {
      paymentSubmitting.value = false
    }
  } else {
    // Lógica original para pagamento único
    const amount = safeNumber(paymentForm.value.amount)
    if (amount <= 0) {
      alert('Informe um valor de pagamento válido')
      return
    }

    // Validação de crédito para pagamentos a prazo
    if (paymentForm.value.payment_method === 'credit' && !hasEnoughCredit.value) {
      alert('Crédito insuficiente para este pagamento. Escolha outra forma de pagamento.')
      return
    }

    paymentSubmitting.value = true
    try {
      await serviceOrderPaymentsStore.createPayment(id, {
        amount,
        payment_method: (paymentForm.value as any).payment_method,
        payment_reference: paymentForm.value.payment_reference || undefined,
        notes: paymentForm.value.notes || undefined
      })
      // Recarregar dados da OS (totais pagos/restante) e lista de pagamentos
      await loadServiceOrder()
      showPaymentModal.value = false
    } catch (e: any) {
      alert(e?.response?.data?.message || e?.message || 'Erro ao registrar pagamento')
    } finally {
      paymentSubmitting.value = false
    }
  }
}

const printPage = () => {
  const id = route.params.id as string | number
  const routeData = router.resolve({ name: 'service-order-print', params: { id } })
  const href = routeData.href
  const win = window.open(href, '_blank')
  if (!win) {
    // Fallback caso o navegador bloqueie popup
    router.push(href)
  }
}

</script>
