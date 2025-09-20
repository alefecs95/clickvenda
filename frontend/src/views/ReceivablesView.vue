<template>
  <AppLayout class="max-w-8xl mx-auto">
    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
        <div class="flex flex-col space-y-4 lg:flex-row lg:justify-between lg:items-center lg:space-y-0">
          <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center">
              <svg class="w-5 h-5 sm:w-6 sm:h-6 mr-2 sm:mr-3 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
              </svg>
              Contas a Receber
            </h1>
            <p class="text-gray-600 mt-1 text-sm sm:text-base">Gerencie vendas a prazo e recebimentos</p>
          </div>
          
          <!-- Resumo Financeiro -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-6">
            <div class="text-center p-3 sm:p-4 bg-red-50 rounded-lg border border-red-200">
              <div class="text-2xl sm:text-3xl font-bold text-red-600">R$ {{ formatPrice(totalOverdue) }}</div>
              <div class="text-xs sm:text-sm text-red-700 font-medium">Contas Vencidas</div>
              <div class="text-xs text-red-600 mt-1">Precisam de atenção urgente</div>
            </div>
            <div class="text-center p-3 sm:p-4 bg-orange-50 rounded-lg border border-orange-200">
              <div class="text-2xl sm:text-3xl font-bold text-orange-600">R$ {{ formatPrice(totalPending) }}</div>
              <div class="text-xs sm:text-sm text-orange-700 font-medium">A Vencer</div>
              <div class="text-xs text-orange-600 mt-1">Dentro do prazo</div>
            </div>
            <div class="text-center p-3 sm:p-4 bg-purple-50 rounded-lg border border-purple-200">
              <div class="text-2xl sm:text-3xl font-bold text-purple-600">{{ customersAtRisk }}</div>
              <div class="text-xs sm:text-sm text-purple-700 font-medium">Clientes em Risco</div>
              <div class="text-xs text-purple-600 mt-1">Limite de crédito alto</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Alertas de Risco -->
      <div v-if="highRiskCustomers.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4 sm:p-6">
        <div class="flex items-center mb-4">
          <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
          <h3 class="text-base sm:text-lg font-semibold text-red-800"> Clientes com Alto Risco de Crédito</h3>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
          <div
            v-for="customer in highRiskCustomers"
            :key="customer.id"
            class="bg-white border border-red-200 rounded-lg p-4"
          >
            <div class="flex justify-between items-start mb-2">
              <div>
                <h4 class="font-semibold text-gray-900">{{ customer.name }}</h4>
                <div class="text-xs text-gray-600">{{ customer.phone || 'Sem telefone' }}</div>
              </div>
              <span class="text-xs font-bold px-2 py-1 rounded-full" :class="getCreditRiskClass(customer)">
                {{ getCreditUsagePercentage(customer) }}%
              </span>
            </div>
            
            <div class="space-y-1 text-xs">
              <div class="flex justify-between">
                <span class="text-gray-600">Limite:</span>
                <span class="font-medium">R$ {{ formatPrice(customer.credit_limit) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Usado:</span>
                <span class="font-medium text-red-600">R$ {{ formatPrice(customer.credit_used) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Disponível:</span>
                <span class="font-medium" :class="customer.credit_limit - customer.credit_used > 0 ? 'text-green-600' : 'text-red-600'">
                  R$ {{ formatPrice(customer.credit_limit - customer.credit_used) }}
                </span>
              </div>
            </div>
            
            <!-- Barra de Progresso -->
            <div class="mt-3">
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div 
                  class="h-2 rounded-full transition-all duration-300"
                  :class="getCreditProgressClass(customer)"
                  :style="{ width: `${Math.min(getCreditUsagePercentage(customer), 100)}%` }"
                ></div>
              </div>
            </div>
            
            <!-- Ações Rápidas -->
            <div class="mt-3 flex space-x-2">
              <button
                @click="viewCustomerReceivables(customer)"
                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded text-xs font-medium"
              >
                Ver Contas
              </button>
              <button
                @click="sendCreditAlert(customer)"
                class="flex-1 bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs font-medium"
              >
                Alertar Cliente
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Filtros -->
      <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Filtrar por Cliente</label>
            <select
              v-model="filters.customer_id"
              @change="fetchReceivables"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-sm"
            >
              <option value="">Todos os clientes</option>
              <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                {{ customer.name }}
              </option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Período da Venda</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
              <input
                v-model="filters.date_from"
                @change="fetchReceivables"
                type="date"
                placeholder="Data inicial"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
              />
              <input
                v-model="filters.date_to"
                @change="fetchReceivables"
                type="date"
                placeholder="Data final"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Lista de Contas a Receber -->
      <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
          <div class="flex flex-col space-y-2 sm:flex-row sm:justify-between sm:items-center sm:space-y-0">
            <h2 class="text-base sm:text-lg font-semibold text-gray-900">
              Contas em Aberto
              <span class="ml-2 text-sm text-gray-500">({{ receivables.length }} registros)</span>
            </h2>
            <div class="text-xs text-gray-500">
              <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                Apenas contas pendentes e parciais são exibidas
              </span>
            </div>
          </div>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-12">
          <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>

        <div v-else-if="receivables.length === 0" class="text-center py-12">
          <svg class="h-12 w-12 text-green-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <h3 class="text-lg font-medium text-gray-900 mb-2">🎉 Todas as contas estão quitadas!</h3>
          <p class="text-gray-600">Não há contas pendentes ou parciais no momento.</p>
          <p class="text-sm text-gray-500 mt-2">Para ver histórico de contas pagas, acesse a página de Pedidos.</p>
        </div>

        <div v-else>
          <!-- Versão Mobile - Cards -->
          <div class="block sm:hidden">
            <div
              v-for="receivable in receivables"
              :key="receivable.id"
              class="border-b border-gray-200 p-4 hover:bg-gray-50"
            >
              <div class="flex justify-between items-start mb-3">
                <div class="flex-1">
                  <div class="font-medium text-gray-900">{{ receivable.customer?.name || 'Cliente não identificado' }}</div>
                  <button
                    @click="openOrderDetails(receivable.order || receivable.serviceOrder)"
                    class="text-xs font-medium text-primary-600 hover:text-primary-800 hover:underline"
                  >
                    #{{ getOrderNumber(receivable) }}
                  </button>
                </div>
                <div class="text-right">
                  <div class="text-sm font-bold text-red-600">R$ {{ formatPrice(receivable.remaining_amount || 0) }}</div>
                  <div class="text-xs text-gray-500">restante</div>
                </div>
              </div>
              
              <div class="grid grid-cols-2 gap-3 text-xs">
                <div>
                  <span class="text-gray-500">Data:</span>
                  <div class="font-medium">{{ formatDate(receivable.created_at) }}</div>
                </div>
                <div>
                  <span class="text-gray-500">Total:</span>
                  <div class="font-medium">R$ {{ formatPrice(receivable.total_receivable_amount || 0) }}</div>
                </div>
                <div>
                  <span class="text-gray-500">Vencimento:</span>
                  <div class="font-medium" :class="isOverdue(receivable) ? 'text-red-600' : 'text-gray-900'">
                    {{ formatDate(receivable.due_date || receivable.created_at) }}
                  </div>
                  <div class="text-xs" :class="isOverdue(receivable) ? 'text-red-600 font-medium' : 'text-gray-500'">
                    {{ isOverdue(receivable) ? `${getDaysOverdue(receivable)}d atraso` : 'No prazo' }}
                  </div>
                </div>
                <div>
                  <span class="text-gray-500">Status:</span>
                  <div>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="getStatusClass(receivable.status)">
                      {{ getStatusText(receivable.status) }}
                    </span>
                  </div>
                </div>
              </div>
              
              <!-- Ações Mobile -->
              <div class="flex justify-end space-x-2 mt-3">
                <button
                  @click="viewDetails(receivable)"
                  class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded"
                  title="Ver detalhes"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                  </svg>
                </button>
                <button
                  v-if="['pending', 'partial'].includes(receivable.status)"
                  @click="openPaymentModal(receivable)"
                  class="p-2 text-green-600 hover:text-green-900 hover:bg-green-50 rounded"
                  title="Registrar pagamento"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                  </svg>
                </button>
                <button
                  @click="sendReminder(receivable)"
                  class="p-2 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded"
                  title="Enviar lembrete via WhatsApp"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Versão Desktop - Tabela -->
          <div class="hidden sm:block">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/5">
                  Cliente
                </th>
                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                  Data
                </th>
                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                  Total
                </th>
                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                  Crédito
                </th>
                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                  Restante
                </th>
                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                  Vencimento
                </th>
                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">
                  Risco
                </th>
                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">
                  Status
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                  Ações
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="receivable in receivables"
                :key="receivable.id"
                class="hover:bg-gray-50"
              >
                <td class="px-4 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ receivable.customer?.name || 'Cliente não identificado' }}</div>
                  <div class="text-xs text-gray-500">
                    <button
                      @click="openOrderDetails(receivable.order || receivable.serviceOrder)"
                      class="font-medium text-primary-600 hover:text-primary-800 hover:underline"
                    >
                      #{{ getOrderNumber(receivable) }}
                    </button>
                    <span v-if="receivable.customer?.phone" class="ml-2">• {{ receivable.customer.phone }}</span>
                  </div>
                </td>
                
                <td class="px-3 py-4 text-center">
                  <div class="text-sm text-gray-900">{{ formatDate(receivable.created_at) }}</div>
                  <div class="text-xs text-gray-500">{{ formatTime(receivable.created_at) }}</div>
                </td>
                
                <td class="px-3 py-4 text-center">
                  <div class="text-sm font-medium text-gray-900">R$ {{ formatPrice(getOrderAmount(receivable)) }}</div>
                  <div v-if="getOrderDiscount(receivable) > 0" class="text-xs text-green-600">
                    -R$ {{ formatPrice(getOrderDiscount(receivable)) }}
                  </div>
                </td>
                
                <td class="px-3 py-4 text-center">
                  <div class="text-sm font-medium text-primary-600">R$ {{ formatPrice(receivable.total_receivable_amount || 0) }}</div>
                </td>
                
                <td class="px-3 py-4 text-center">
                  <div class="text-sm font-medium" :class="(receivable.remaining_amount || 0) > 0 ? 'text-red-600' : 'text-green-600'">
                    R$ {{ formatPrice(receivable.remaining_amount || 0) }}
                  </div>
                  <div v-if="receivable.status === 'partial'" class="text-xs text-blue-600 font-medium">
                    {{ Math.round(((receivable.paid_amount || 0) / (receivable.total_receivable_amount || 1)) * 100) }}% pago
                  </div>
                </td>
                
                <td class="px-3 py-4 text-center">
                  <div class="text-sm text-gray-900">{{ formatDate(receivable.due_date || receivable.created_at) }}</div>
                  <div class="text-xs" :class="isOverdue(receivable) ? 'text-red-600 font-medium' : 'text-gray-500'">
                    {{ isOverdue(receivable) ? `${getDaysOverdue(receivable)}d atraso` : 'No prazo' }}
                  </div>
                </td>
                
                <td class="px-3 py-4 text-center">
                  <div v-if="receivable.customer" class="flex flex-col items-center">
                    <div class="w-full bg-gray-200 rounded-full h-2 mb-1">
                      <div 
                        class="h-2 rounded-full transition-all duration-300"
                        :class="getCreditProgressClass(receivable.customer)"
                        :style="{ width: `${Math.min(getCreditUsagePercentage(receivable.customer), 100)}%` }"
                      ></div>
                    </div>
                    <span class="text-xs font-medium" :class="getCreditRiskTextClass(receivable.customer)">
                      {{ getCreditUsagePercentage(receivable.customer) }}%
                    </span>
                  </div>
                  <div v-else class="text-xs text-gray-400">N/A</div>
                </td>
                
                <td class="px-3 py-4 text-center">
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" :class="getStatusClass(receivable)">
                    {{ getStatusText(receivable) }}
                  </span>
                </td>
                
                <td class="px-4 py-4">
                  <div class="flex justify-center space-x-1">
                    <button
                      @click="viewDetails(receivable)"
                      class="p-1 text-primary-600 hover:text-primary-900 hover:bg-primary-50 rounded"
                      title="Ver detalhes"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                    </button>
                    
                    <button
                      v-if="['pending', 'partial'].includes(receivable.status)"
                      @click="openPaymentModal(receivable)"
                      class="p-1 text-green-600 hover:text-green-900 hover:bg-green-50 rounded"
                      title="Registrar pagamento"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                      </svg>
                    </button>
                    
                    <button
                      @click="sendReminder(receivable)"
                      class="p-1 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded"
                      title="Enviar lembrete via WhatsApp"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Detalhes -->
    <div v-if="showDetailsModal && selectedReceivable" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-4 sm:top-10 mx-auto p-4 sm:p-6 border w-full max-w-2xl shadow-lg rounded-lg bg-white m-4">
        <div class="flex justify-between items-center mb-4 sm:mb-6">
          <h3 class="text-lg sm:text-xl font-bold text-gray-900">Detalhes da Conta a Receber</h3>
          <button
            @click="showDetailsModal = false"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg class="h-5 h-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
          <!-- Informações Gerais -->
          <div class="space-y-4">
            <div>
              <h4 class="font-semibold text-gray-900 mb-2">Informações da Venda</h4>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-gray-600">{{ selectedReceivable.serviceOrder ? 'OS:' : 'Pedido:' }}</span>
                  <button
                    @click="openOrderDetails(selectedReceivable.order || selectedReceivable.serviceOrder)"
                    class="font-medium text-primary-600 hover:text-primary-800 hover:underline"
                  >
                    #{{ getOrderNumber(selectedReceivable) }}
                  </button>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">{{ selectedReceivable.serviceOrder ? 'Data da OS:' : 'Data da venda:' }}</span>
                  <span class="font-medium">{{ formatDate(selectedReceivable.created_at) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Vencimento:</span>
                  <span class="font-medium" :class="isOverdue(selectedReceivable) ? 'text-red-600' : 'text-gray-900'">
                    {{ formatDate(selectedReceivable.due_date || selectedReceivable.created_at) }}
                  </span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Cliente:</span>
                  <span class="font-medium">{{ selectedReceivable.customer?.name || 'Não identificado' }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Status:</span>
                  <span class="font-medium" :class="getStatusTextClass(selectedReceivable)">
                    {{ getStatusText(selectedReceivable) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Valores -->
            <div>
              <h4 class="font-semibold text-gray-900 mb-2">Valores</h4>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-gray-600">{{ selectedReceivable.serviceOrder ? 'Total da OS:' : 'Total da venda:' }}</span>
                  <span class="font-medium">R$ {{ formatPrice(getOrderAmount(selectedReceivable)) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Valor no crédito:</span>
                  <span class="font-medium text-primary-600">R$ {{ formatPrice(selectedReceivable.total_receivable_amount || 0) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Já pago:</span>
                  <span class="font-medium text-green-600">R$ {{ formatPrice(selectedReceivable.paid_amount || 0) }}</span>
                </div>
                <div class="flex justify-between border-t pt-2">
                  <span class="text-gray-900 font-semibold">Valor restante:</span>
                  <span class="font-bold text-lg text-red-600">R$ {{ formatPrice(selectedReceivable.remaining_amount || 0) }}</span>
                </div>
                <div v-if="selectedReceivable.payment_history?.length > 0" class="border-t pt-2">
                  <span class="text-gray-600 text-xs font-medium">Histórico de pagamentos:</span>
                  <div class="mt-2 space-y-2">
                    <div v-for="(payment, index) in selectedReceivable.payment_history" :key="index" class="bg-gray-50 rounded p-2">
                      <div class="flex justify-between text-xs mb-1">
                        <span class="font-medium">{{ formatDate(payment.date) }} - {{ getPaymentMethodText(payment.method) }}</span>
                        <span class="font-bold text-green-600">R$ {{ formatPrice(payment.amount) }}</span>
                      </div>
                      <div v-if="payment.notes" class="text-xs text-gray-600 italic">
                        "{{ payment.notes }}"
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Formas de Pagamento -->
          <div class="space-y-4">
            <div>
              <h4 class="font-semibold text-gray-900 mb-2">Formas de Pagamento</h4>
              <div v-if="selectedReceivable.payment_methods" class="space-y-2">
                <div
                  v-for="(payment, index) in selectedReceivable.payment_methods"
                  :key="index"
                  class="flex justify-between items-center p-3 bg-gray-50 rounded-lg"
                >
                  <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    <span class="text-sm font-medium">{{ getPaymentMethodText(payment.method) }}</span>
                  </div>
                  <span class="text-sm font-bold" :class="payment.method === 'credit' ? 'text-primary-600' : 'text-gray-900'">
                    R$ {{ formatPrice(payment.amount) }}
                  </span>
                </div>
              </div>
              <div v-else class="p-3 bg-gray-50 rounded-lg">
                <div class="flex justify-between items-center">
                  <span class="text-sm font-medium">{{ getPaymentMethodText(selectedReceivable.payment_method) }}</span>
                  <span class="text-sm font-bold text-primary-600">R$ {{ formatPrice(selectedReceivable.final_amount) }}</span>
                </div>
              </div>
            </div>

            <!-- Itens da Venda -->
            <div>
              <h4 class="font-semibold text-gray-900 mb-2">Itens da Venda</h4>
              <div class="space-y-2 max-h-48 overflow-y-auto">
                <div
                  v-for="item in selectedReceivable.items"
                  :key="item.id"
                  class="flex justify-between items-center p-2 bg-gray-50 rounded"
                >
                  <div>
                    <div class="text-sm font-medium">{{ item.product?.name }}</div>
                    <div class="text-xs text-gray-500">{{ item.quantity }} × R$ {{ formatPrice(item.unit_price) }}</div>
                  </div>
                  <div class="text-sm font-medium">R$ {{ formatPrice(item.total_price) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Ações do Modal -->
        <div class="mt-6 flex justify-end space-x-3">
          <button
            v-if="['pending', 'partial'].includes(selectedReceivable.status)"
            @click="openPaymentModal(selectedReceivable); showDetailsModal = false"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium"
          >
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
            </svg>
            Registrar Pagamento
          </button>
          
          <button
            @click="sendReminder(selectedReceivable)"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium"
          >
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            Enviar Lembrete
          </button>
          
          <button
            @click="showDetailsModal = false"
            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md font-medium"
          >
            Fechar
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de Pagamento Parcial -->
    <div v-if="showPaymentModal && selectedReceivable" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-4 sm:top-20 mx-auto p-4 sm:p-6 border w-full max-w-md shadow-lg rounded-lg bg-white m-4">
        <div class="flex justify-between items-center mb-4 sm:mb-6">
          <h3 class="text-lg sm:text-xl font-bold text-gray-900">Registrar Pagamento</h3>
          <button
            @click="showPaymentModal = false"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg class="h-5 h-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-blue-50 rounded-lg">
          <div class="text-sm space-y-1">
            <div class="flex justify-between">
              <span class="text-gray-600">Cliente:</span>
              <span class="font-medium">{{ selectedReceivable.customer?.name }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">{{ selectedReceivable.serviceOrder ? 'OS:' : 'Pedido:' }}</span>
              <span class="font-medium">#{{ getOrderNumber(selectedReceivable) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Valor total a receber:</span>
              <span class="font-medium">R$ {{ formatPrice(selectedReceivable.total_receivable_amount || getCreditAmount(selectedReceivable)) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Já pago:</span>
              <span class="font-medium text-green-600">R$ {{ formatPrice(selectedReceivable.paid_amount || 0) }}</span>
            </div>
            <div class="flex justify-between border-t pt-2">
              <span class="text-gray-900 font-semibold">Valor restante:</span>
              <span class="font-bold text-red-600">R$ {{ formatPrice(Math.max(0, (selectedReceivable.remaining_amount || selectedReceivable.total_receivable_amount || getCreditAmount(selectedReceivable)))) }}</span>
            </div>
          </div>
        </div>

        <form @submit.prevent="registerPayment">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Valor do Pagamento (R$)</label>
              <input
                v-model="paymentForm.amount"
                type="number"
                step="0.01"
                min="0.01"
                :max="Math.max(0.01, selectedReceivable.remaining_amount || selectedReceivable.total_receivable_amount || getCreditAmount(selectedReceivable))"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Forma de Pagamento</label>
              <select
                v-model="paymentForm.method"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
              >
                <option value="money">Dinheiro</option>
                <option value="card">Cartão</option>
                <option value="pix">PIX</option>
              </select>
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
              @click="showPaymentModal = false"
              class="flex-1 sm:flex-none bg-gray-300 text-gray-700 py-3 px-4 rounded-md hover:bg-gray-400 font-medium"
            >
              Cancelar
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal de Detalhes do Pedido -->
    <div v-if="showOrderModal && selectedOrder" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-4 sm:top-10 mx-auto p-4 sm:p-6 border w-full max-w-4xl shadow-lg rounded-lg bg-white m-4">
        <div class="flex justify-between items-center mb-4 sm:mb-6">
          <h3 class="text-lg sm:text-xl font-bold text-gray-900">
            {{ selectedOrder.order_number ? 'Detalhes da Venda' : 'Detalhes da OS' }} 
            #{{ selectedOrder.order_number || selectedOrder.os_number }}
          </h3>
          <button
            @click="showOrderModal = false"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
          <!-- Informações da Venda -->
          <div class="space-y-4">
            <div class="bg-gray-50 rounded-lg p-4">
              <h4 class="font-semibold text-gray-900 mb-3">Informações Gerais</h4>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-gray-600">Número do Pedido:</span>
                  <span class="font-medium">#{{ selectedOrder.order_number }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Data da Venda:</span>
                  <span class="font-medium">{{ formatDate(selectedOrder.created_at) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Hora:</span>
                  <span class="font-medium">{{ formatTime(selectedOrder.created_at) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Cliente:</span>
                  <span class="font-medium">{{ selectedOrder.customer?.name || 'Cliente não identificado' }}</span>
                </div>
                <div v-if="selectedOrder.customer?.phone" class="flex justify-between">
                  <span class="text-gray-600">Telefone:</span>
                  <span class="font-medium">{{ selectedOrder.customer.phone }}</span>
                </div>
                <div v-if="selectedOrder.customer?.email" class="flex justify-between">
                  <span class="text-gray-600">Email:</span>
                  <span class="font-medium">{{ selectedOrder.customer.email }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Vendedor:</span>
                  <span class="font-medium">{{ selectedOrder.user?.name || 'Sistema' }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Status do Pedido:</span>
                  <span class="font-medium" :class="selectedOrder.status === 'completed' ? 'text-green-600' : 'text-orange-600'">
                    {{ selectedOrder.status === 'completed' ? 'Finalizado' : 'Pendente' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Formas de Pagamento da Venda -->
            <div class="bg-blue-50 rounded-lg p-4">
              <h4 class="font-semibold text-gray-900 mb-3">Formas de Pagamento</h4>
              <div v-if="selectedOrder.payment_methods" class="space-y-2">
                <div
                  v-for="(payment, index) in selectedOrder.payment_methods"
                  :key="index"
                  class="flex justify-between items-center p-2 bg-white rounded"
                >
                  <span class="text-sm font-medium">{{ getPaymentMethodText(payment.method) }}</span>
                  <span class="text-sm font-bold" :class="payment.method === 'credit' ? 'text-primary-600' : 'text-gray-900'">
                    R$ {{ formatPrice(payment.amount) }}
                  </span>
                </div>
              </div>
              <div v-else class="p-2 bg-white rounded">
                <div class="flex justify-between">
                  <span class="text-sm font-medium">{{ getPaymentMethodText(selectedOrder.payment_method) }}</span>
                  <span class="text-sm font-bold text-primary-600">R$ {{ formatPrice(selectedOrder.final_amount) }}</span>
                </div>
              </div>
            </div>

            <!-- Observações da Venda -->
            <div v-if="selectedOrder.notes" class="bg-yellow-50 rounded-lg p-4">
              <h4 class="font-semibold text-gray-900 mb-2">Observações da Venda</h4>
              <p class="text-sm text-gray-700 italic">"{{ selectedOrder.notes }}"</p>
            </div>
          </div>

          <!-- Itens da Venda -->
          <div class="space-y-4">
            <div class="bg-gray-50 rounded-lg p-4">
              <h4 class="font-semibold text-gray-900 mb-3">
                Itens da Venda 
                <span class="text-sm text-gray-500">({{ selectedOrder.items?.length || 0 }} itens)</span>
              </h4>
              
              <div v-if="!selectedOrder.items || selectedOrder.items.length === 0" class="text-center py-4">
                <svg class="h-8 w-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <p class="text-sm text-gray-500">Nenhum item encontrado</p>
              </div>
              
              <div v-else class="space-y-3 max-h-64 overflow-y-auto">
                <div
                  v-for="item in selectedOrder.items"
                  :key="item.id"
                  class="flex justify-between items-start p-3 bg-white rounded border hover:shadow-sm transition-shadow"
                >
                  <div class="flex-1">
                    <h5 class="font-semibold text-gray-900">{{ item.product?.name || `Produto ID: ${item.product_id}` }}</h5>
                    <div class="text-xs text-gray-600 mt-1 space-x-2">
                      <span v-if="item.product?.sku">SKU: {{ item.product.sku }}</span>
                      <span v-if="item.product?.returnable" class="text-orange-600 font-medium">• Retornável</span>
                      <span v-if="item.product?.available === false" class="text-red-600 font-medium">• Indisponível</span>
                    </div>
                    <div class="text-sm text-gray-700 mt-2 bg-gray-50 rounded px-2 py-1">
                      <span class="font-medium">{{ item.quantity }}</span> 
                      <span class="text-gray-500">×</span> 
                      <span class="font-medium">R$ {{ formatPrice(item.unit_price) }}</span>
                      <span class="text-gray-500">=</span>
                      <span class="font-bold text-primary-600">R$ {{ formatPrice(item.total_price) }}</span>
                    </div>
                  </div>
                  <div class="text-right ml-4">
                    <div class="font-bold text-lg text-gray-900">R$ {{ formatPrice(item.total_price) }}</div>
                    <div v-if="item.product?.returnable && item.product?.returnable_price" class="text-xs text-orange-600 mt-1">
                      + R$ {{ formatPrice(item.product.returnable_price * item.quantity) }}
                      <br><span class="text-gray-500">({{ item.quantity }} vasilhames)</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Resumo Financeiro da Venda -->
            <div class="bg-green-50 rounded-lg p-4">
              <h4 class="font-semibold text-gray-900 mb-3">Resumo Financeiro</h4>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-gray-600">Subtotal:</span>
                  <span class="font-medium">R$ {{ formatPrice(selectedOrder.total_amount) }}</span>
                </div>
                <div v-if="selectedOrder.discount_amount > 0" class="flex justify-between">
                  <span class="text-gray-600">Desconto:</span>
                  <span class="font-medium text-green-600">-R$ {{ formatPrice(selectedOrder.discount_amount) }}</span>
                </div>
                <div class="flex justify-between border-t pt-2">
                  <span class="font-semibold text-gray-900">Total Final:</span>
                  <span class="font-bold text-lg">R$ {{ formatPrice(selectedOrder.final_amount) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Botões de Ação -->
        <div class="mt-6 flex justify-end space-x-3">
          <button
            @click="printOrderDetails"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium"
          >
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Imprimir
          </button>
          <button
            @click="showOrderModal = false"
            class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium"
          >
            Fechar
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useCustomersStore, type Customer } from '@/stores/customers'
import { useSettingsStore } from '@/stores/settings'
import { useReceivablesStore } from '@/stores/receivables'
import AppLayout from '@/components/AppLayout.vue'

const customersStore = useCustomersStore()
const settingsStore = useSettingsStore()
const receivablesStore = useReceivablesStore()


const loading = computed(() => receivablesStore.loading)
const receivables = computed(() => receivablesStore.receivables)
const customers = ref<Customer[]>([])
const showDetailsModal = ref(false)
const showPaymentModal = ref(false)
const showOrderModal = ref(false)
const selectedReceivable = ref<any>(null)
const selectedOrder = ref<any>(null)
const paymentSubmitting = ref(false)

const filters = ref({
  customer_id: '',
  date_from: '',
  date_to: ''
})

const paymentForm = ref({
  amount: 0,
  method: 'money',
  notes: ''
})

// Computed properties para resumo financeiro
const totalOverdue = computed(() => {
  return receivablesStore.statistics?.total_overdue || receivablesStore.totalOverdue || 0
})

const totalPending = computed(() => {
  return receivablesStore.statistics?.total_pending || receivablesStore.totalPending || 0
})


// Clientes com alto risco de crédito (baseado nas configurações)
const highRiskCustomers = computed(() => {
  const alertThreshold = settingsStore.creditAlertThreshold
  return customers.value.filter(customer => {
    if (!customer.credit_limit || customer.credit_limit <= 0) return false
    const usagePercentage = getCreditUsagePercentage(customer)
    return usagePercentage >= alertThreshold
  }).sort((a, b) => getCreditUsagePercentage(b) - getCreditUsagePercentage(a))
})

const customersAtRisk = computed(() => {
  return highRiskCustomers.value.length
})

const formatPrice = (price: any) => {
  const numPrice = parseFloat(price) || 0
  return numPrice.toFixed(2).replace('.', ',')
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-BR')
}

const formatTime = (dateString: string) => {
  return new Date(dateString).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
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

const getOrderNumber = (receivable: any) => {
  if (receivable.order) {
    return receivable.order.order_number || 'N/A'
  } else if (receivable.serviceOrder) {
    return receivable.serviceOrder.order_number || 'N/A'
  }
  return 'N/A'
}

const getOrderAmount = (receivable: any) => {
  if (receivable.order) {
    return receivable.order.final_amount || 0
  } else if (receivable.serviceOrder) {
    return receivable.serviceOrder.final_amount || 0
  }
  return 0
}

const getOrderDiscount = (receivable: any) => {
  if (receivable.order) {
    return receivable.order.discount_amount || 0
  } else if (receivable.serviceOrder) {
    return receivable.serviceOrder.discount_amount || 0
  }
  return 0
}

const getCreditAmount = (receivable: any) => {
  // Se é um ReceivablePayment, usar total_receivable_amount
  if (receivable.total_receivable_amount !== undefined) {
    return receivable.total_receivable_amount
  }
  
  // Fallback para Order (compatibilidade)
  if (receivable.payment_methods) {
    return receivable.payment_methods
      .filter((p: any) => p.method === 'credit')
      .reduce((sum: number, p: any) => sum + (Number(p.amount) || 0), 0)
  } else if (receivable.payment_method === 'credit') {
    return receivable.final_amount
  }
  return 0
}


const isOverdue = (receivable: any) => {
  // Para ReceivablePayment, usar due_date
  if (receivable.due_date) {
    const dueDate = new Date(receivable.due_date)
    const today = new Date()
    return dueDate < today && ['pending', 'partial'].includes(receivable.status)
  }
  
  // Fallback para Order (compatibilidade)
  const paymentTerm = settingsStore.defaultPaymentTerm
  const createdDate = new Date(receivable.created_at)
  const today = new Date()
  const diffDays = Math.floor((today.getTime() - createdDate.getTime()) / (1000 * 60 * 60 * 24))
  return diffDays > paymentTerm && receivable.status === 'pending'
}

const getDaysOverdue = (receivable: any) => {
  if (!isOverdue(receivable)) return 0
  
  if (receivable.due_date) {
    const dueDate = new Date(receivable.due_date)
    const today = new Date()
    return Math.floor((today.getTime() - dueDate.getTime()) / (1000 * 60 * 60 * 24))
  }
  
  // Fallback para Order
  const paymentTerm = settingsStore.defaultPaymentTerm
  const createdDate = new Date(receivable.created_at)
  const today = new Date()
  const diffDays = Math.floor((today.getTime() - createdDate.getTime()) / (1000 * 60 * 60 * 24))
  return Math.max(0, diffDays - paymentTerm)
}

const getStatusText = (receivable: any) => {
  if (receivable.status === 'paid') return 'Pago'
  if (receivable.status === 'partial') return 'Parcial'
  if (isOverdue(receivable)) return 'Em Atraso'
  return 'Pendente'
}

const getStatusClass = (receivable: any) => {
  if (receivable.status === 'paid') return 'bg-green-100 text-green-800'
  if (receivable.status === 'partial') return 'bg-blue-100 text-blue-800'
  if (isOverdue(receivable)) return 'bg-red-100 text-red-800'
  return 'bg-orange-100 text-orange-800'
}

const getStatusTextClass = (receivable: any) => {
  if (receivable.status === 'paid') return 'text-green-600'
  if (receivable.status === 'partial') return 'text-blue-600'
  if (isOverdue(receivable)) return 'text-red-600'
  return 'text-orange-600'
}

// Funções para análise de risco de crédito
const getCreditUsagePercentage = (customer: any) => {
  if (!customer.credit_limit || customer.credit_limit <= 0) return 0
  return Math.round((customer.credit_used / customer.credit_limit) * 100)
}

const getCreditRiskClass = (customer: any) => {
  const percentage = getCreditUsagePercentage(customer)
  if (percentage >= 95) return 'bg-red-500 text-white'
  if (percentage >= 90) return 'bg-red-100 text-red-800'
  if (percentage >= 80) return 'bg-orange-100 text-orange-800'
  return 'bg-yellow-100 text-yellow-800'
}

const getCreditRiskTextClass = (customer: any) => {
  const percentage = getCreditUsagePercentage(customer)
  if (percentage >= 95) return 'text-red-600 font-bold'
  if (percentage >= 90) return 'text-red-600'
  if (percentage >= 80) return 'text-orange-600'
  return 'text-yellow-600'
}

const getCreditProgressClass = (customer: any) => {
  const percentage = getCreditUsagePercentage(customer)
  if (percentage >= 95) return 'bg-red-600'
  if (percentage >= 90) return 'bg-red-500'
  if (percentage >= 80) return 'bg-orange-500'
  if (percentage >= 60) return 'bg-yellow-500'
  return 'bg-green-500'
}

const fetchReceivables = async () => {
  try {
    // Buscar todas as contas pendentes e parciais
    await receivablesStore.fetchReceivables({
      customer_id: filters.value.customer_id ? parseInt(filters.value.customer_id) : undefined,
      due_date_from: filters.value.date_from || undefined,
      due_date_to: filters.value.date_to || undefined,
      per_page: 100
    })
  } catch (error) {
    console.error('Erro ao buscar contas a receber:', error)
  }
}

const viewDetails = (receivable: any) => {
  selectedReceivable.value = receivable
  showDetailsModal.value = true
}

const openOrderDetails = (order: any) => {
  if (!order) {
    alert('Detalhes do pedido não disponíveis.')
    return
  }
  
  console.log('Order details:', order)
  console.log('Order items:', order.items)
  console.log('Order customer:', order.customer)
  console.log('Order user:', order.user)
  
  selectedOrder.value = order
  showOrderModal.value = true
}

const printOrderDetails = () => {
  if (!selectedOrder.value) return
  
  const order = selectedOrder.value
  const storeInfo = settingsStore.storeSettings
  
  // Criar conteúdo para impressão
  const printContent = `
    <html>
      <head>
        <title>Detalhes da Venda #${order.order_number}</title>
        <style>
          body { 
            font-family: Arial, sans-serif; 
            max-width: 800px; 
            margin: 0 auto; 
            padding: 20px;
            color: #333;
          }
          .header { 
            text-align: center; 
            border-bottom: 2px solid #333; 
            padding-bottom: 20px; 
            margin-bottom: 20px; 
          }
          .store-name { 
            font-size: 24px; 
            font-weight: bold; 
            margin-bottom: 5px; 
          }
          .store-info { 
            font-size: 12px; 
            color: #666; 
          }
          .section { 
            margin-bottom: 20px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            padding: 15px; 
          }
          .section-title { 
            font-size: 16px; 
            font-weight: bold; 
            margin-bottom: 10px; 
            color: #2563eb; 
          }
          .info-row { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 5px; 
            padding: 2px 0; 
          }
          .info-label { 
            font-weight: bold; 
            color: #666; 
          }
          .item { 
            border-bottom: 1px solid #eee; 
            padding: 10px 0; 
          }
          .item:last-child { 
            border-bottom: none; 
          }
          .item-name { 
            font-weight: bold; 
            margin-bottom: 5px; 
          }
          .item-details { 
            font-size: 12px; 
            color: #666; 
          }
          .item-calc { 
            background: #f5f5f5; 
            padding: 5px; 
            border-radius: 3px; 
            margin-top: 5px; 
          }
          .total-section { 
            background: #f0f9ff; 
            border: 2px solid #2563eb; 
          }
          .total-value { 
            font-size: 20px; 
            font-weight: bold; 
            color: #2563eb; 
          }
          .footer { 
            text-align: center; 
            margin-top: 30px; 
            padding-top: 20px; 
            border-top: 1px solid #ddd; 
            font-size: 12px; 
            color: #666; 
          }
          @media print {
            body { margin: 0; padding: 10px; }
            .no-print { display: none; }
          }
        </style>
      </head>
      <body>
        <!-- Cabeçalho da Loja -->
        <div class="header">
          <div class="store-name">${storeInfo.name || 'CLICKVENDA'}</div>
          <div class="store-info">
            ${storeInfo.cnpj ? `CNPJ: ${storeInfo.cnpj}<br>` : ''}
            ${storeInfo.address ? `${storeInfo.address}<br>` : ''}
            ${storeInfo.city && storeInfo.state ? `${storeInfo.city}, ${storeInfo.state}` : ''}
            ${storeInfo.zip_code ? ` - CEP: ${storeInfo.zip_code}` : ''}<br>
            ${storeInfo.phone ? `Tel: ${storeInfo.phone}` : ''}
            ${storeInfo.email ? ` | Email: ${storeInfo.email}` : ''}
          </div>
        </div>

        <!-- Informações do Pedido -->
        <div class="section">
          <div class="section-title">Informações da Venda</div>
          <div class="info-row">
            <span class="info-label">Número do Pedido:</span>
            <span>#${order.order_number}</span>
          </div>
          <div class="info-row">
            <span class="info-label">Data da Venda:</span>
            <span>${formatDate(order.created_at)} às ${formatTime(order.created_at)}</span>
          </div>
          <div class="info-row">
            <span class="info-label">Cliente:</span>
            <span>${order.customer?.name || 'Cliente não identificado'}</span>
          </div>
          ${order.customer?.phone ? `
          <div class="info-row">
            <span class="info-label">Telefone:</span>
            <span>${order.customer.phone}</span>
          </div>
          ` : ''}
          ${order.customer?.email ? `
          <div class="info-row">
            <span class="info-label">Email:</span>
            <span>${order.customer.email}</span>
          </div>
          ` : ''}
          <div class="info-row">
            <span class="info-label">Vendedor:</span>
            <span>${order.user?.name || 'Sistema'}</span>
          </div>
          <div class="info-row">
            <span class="info-label">Status:</span>
            <span>${order.status === 'completed' ? 'Finalizado' : 'Pendente'}</span>
          </div>
        </div>

        <!-- Itens da Venda -->
        <div class="section">
          <div class="section-title">Itens da Venda (${order.items?.length || 0} itens)</div>
          ${order.items?.map((item: any) => `
            <div class="item">
              <div class="item-name">${item.product?.name || `Produto ID: ${item.product_id}`}</div>
              <div class="item-details">
                ${item.product?.sku ? `SKU: ${item.product.sku}` : ''}
                ${item.product?.returnable ? ' • Retornável' : ''}
              </div>
              <div class="item-calc">
                ${item.quantity} × R$ ${formatPrice(item.unit_price)} = <strong>R$ ${formatPrice(item.total_price)}</strong>
                ${item.product?.returnable && item.product?.returnable_price ? `<br>+ ${item.quantity} vasilhames: R$ ${formatPrice(item.product.returnable_price * item.quantity)}` : ''}
              </div>
            </div>
          `).join('') || '<p>Nenhum item encontrado</p>'}
        </div>

        <!-- Formas de Pagamento -->
        <div class="section">
          <div class="section-title">Formas de Pagamento</div>
          ${order.payment_methods ? 
            order.payment_methods.map((payment: any) => `
              <div class="info-row">
                <span class="info-label">${getPaymentMethodText(payment.method)}:</span>
                <span>R$ ${formatPrice(payment.amount)}</span>
              </div>
            `).join('') :
            `<div class="info-row">
              <span class="info-label">${getPaymentMethodText(order.payment_method)}:</span>
              <span>R$ ${formatPrice(order.final_amount)}</span>
            </div>`
          }
        </div>

        <!-- Resumo Financeiro -->
        <div class="section total-section">
          <div class="section-title">Resumo Financeiro</div>
          <div class="info-row">
            <span class="info-label">Subtotal:</span>
            <span>R$ ${formatPrice(order.total_amount)}</span>
          </div>
          ${order.discount_amount > 0 ? `
          <div class="info-row">
            <span class="info-label">Desconto:</span>
            <span style="color: #059669;">-R$ ${formatPrice(order.discount_amount)}</span>
          </div>
          ` : ''}
          <div class="info-row" style="border-top: 1px solid #ddd; padding-top: 10px; margin-top: 10px;">
            <span class="info-label">TOTAL FINAL:</span>
            <span class="total-value">R$ ${formatPrice(order.final_amount)}</span>
          </div>
        </div>

        ${order.notes ? `
        <!-- Observações -->
        <div class="section">
          <div class="section-title">Observações</div>
          <p style="font-style: italic;">"${order.notes}"</p>
        </div>
        ` : ''}

        <!-- Rodapé -->
        <div class="footer">
          <p>Documento gerado em ${new Date().toLocaleDateString('pt-BR')} às ${new Date().toLocaleTimeString('pt-BR')}</p>
          <p>${storeInfo.receipt_footer || 'Obrigado pela preferência!'}</p>
        </div>
      </body>
    </html>
  `
  
  const printWindow = window.open('', '_blank')
  if (printWindow) {
    printWindow.document.write(printContent)
    printWindow.document.close()
    printWindow.focus()
    printWindow.print()
  }
}

const openPaymentModal = (receivable: any) => {
  selectedReceivable.value = receivable
  
  // Calcular valor restante corretamente
  let remainingAmount = 0
  if (receivable.remaining_amount !== undefined) {
    // É um ReceivablePayment
    remainingAmount = receivable.remaining_amount
  } else {
    // É um Order, calcular manualmente
    const totalCredit = getCreditAmount(receivable)
    const paidAmount = receivable.paid_amount || 0
    remainingAmount = Math.max(0, totalCredit - paidAmount)
  }
  
  paymentForm.value = {
    amount: Math.max(0.01, remainingAmount),
    method: 'money',
    notes: ''
  }
  showPaymentModal.value = true
}

const registerPayment = async () => {
  if (!selectedReceivable.value) return

  const paymentAmount = Number(paymentForm.value.amount)
  const totalCredit = getCreditAmount(selectedReceivable.value)
  const alreadyPaid = selectedReceivable.value.paid_amount || 0
  const remainingAmount = Math.max(0, totalCredit - alreadyPaid)

  // Validações no frontend
  if (paymentAmount <= 0) {
    alert('O valor do pagamento deve ser maior que zero.')
    return
  }

  if (paymentAmount > remainingAmount) {
    alert(`O valor do pagamento não pode ser maior que o valor restante (R$ ${formatPrice(remainingAmount)}).`)
    return
  }

  paymentSubmitting.value = true
  try {
    let result
    
    if (selectedReceivable.value.serviceOrder) {
      // Registrar pagamento para OS
      const response = await api.post(`/service-orders/${selectedReceivable.value.serviceOrder.id}/payments`, {
        amount: paymentAmount,
        payment_method: paymentForm.value.method,
        notes: paymentForm.value.notes
      })
      result = { success: response.data.success, data: response.data.data }
    } else {
      // Registrar pagamento para venda (método original)
      result = await receivablesStore.addPayment(
        selectedReceivable.value.id,
        {
          amount: paymentAmount,
          method: paymentForm.value.method,
          notes: paymentForm.value.notes
        }
      )
    }

    if (result.success) {
      showPaymentModal.value = false
      showDetailsModal.value = false
      await fetchReceivables()
      await customersStore.fetchCustomers()
      
      const isFullyPaid = result.data?.status === 'paid' || result.data?.is_fully_paid
      alert(isFullyPaid ? 
        'Pagamento registrado! Conta quitada completamente.' : 
        `Pagamento de R$ ${formatPrice(paymentAmount)} registrado com sucesso.`
      )
    } else {
      alert('Erro ao registrar pagamento: ' + (result.error || 'Erro desconhecido'))
    }
  } catch (error) {
    console.error('Erro ao registrar pagamento:', error)
    alert('Erro ao registrar pagamento. Tente novamente.')
  } finally {
    paymentSubmitting.value = false
  }
}


const viewCustomerReceivables = (customer: any) => {
  filters.value.customer_id = customer.id.toString()
  fetchReceivables()
}

const sendCreditAlert = (customer: any) => {
  if (!customer.phone) {
    alert('Cliente não possui telefone cadastrado para envio de alerta.')
    return
  }

  const usagePercentage = getCreditUsagePercentage(customer)
  const availableCredit = customer.credit_limit - customer.credit_used
  
  const message = `🚨 *ALERTA DE CRÉDITO - CLICKVENDA*

Olá ${customer.name}! 👋

⚠️ *Seu limite de crédito está quase esgotado!*

📊 *Situação atual:*
💳 Limite total: R$ ${formatPrice(customer.credit_limit)}
💰 Valor usado: R$ ${formatPrice(customer.credit_used)} (${usagePercentage}%)
💵 Disponível: R$ ${formatPrice(availableCredit)}

${usagePercentage >= 95 ? '🔴 *CRÍTICO:* Limite quase esgotado!' : 
  usagePercentage >= 90 ? '🟠 *ATENÇÃO:* Limite próximo do esgotamento!' :
  '🟡 *AVISO:* Monitore seu uso de crédito.'}

Entre em contato conosco para:
• Quitar contas pendentes
• Solicitar aumento de limite
• Esclarecer dúvidas

Obrigado! 🙏`

  const phone = customer.phone.replace(/\D/g, '')
  const whatsappUrl = `https://wa.me/55${phone}?text=${encodeURIComponent(message)}`
  window.open(whatsappUrl, '_blank')
}

const sendReminder = (receivable: any) => {
  const customer = receivable.customer
  if (!customer || !customer.phone) {
    alert('Cliente não possui telefone cadastrado para envio de lembrete.')
    return
  }

  const creditAmount = getCreditAmount(receivable)
  const message = `🔔 *LEMBRETE - CLICKVENDA*

Olá ${customer.name}! 👋

📋 *${receivable.serviceOrder ? 'OS' : 'Pedido'}:* #${getOrderNumber(receivable)}
📅 *Data da ${receivable.serviceOrder ? 'OS' : 'compra'}:* ${formatDate(receivable.created_at)}
💰 *Valor a receber:* R$ ${formatPrice(creditAmount)}

${isOverdue(receivable) ? '🚨 *Esta conta está em atraso.* Por favor, entre em contato conosco para regularizar.' : '⏰ *Lembrete amigável* sobre sua conta pendente.'}

Entre em contato conosco para quitar ou tirar dúvidas.

Obrigado! 🙏`

  const phone = customer.phone.replace(/\D/g, '')
  const whatsappUrl = `https://wa.me/55${phone}?text=${encodeURIComponent(message)}`
  window.open(whatsappUrl, '_blank')
}

onMounted(async () => {
  // Carregar configurações primeiro
  settingsStore.loadSettings()
  
  await customersStore.fetchCustomers()
  customers.value = customersStore.customers
  
  // Carregar estatísticas das contas a receber
  await receivablesStore.fetchStatistics()
  
  await fetchReceivables()
})
</script>
