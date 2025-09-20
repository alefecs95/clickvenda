<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div>
            <h1 class="text-xl font-semibold text-gray-900">Relatórios de Pagamentos - OS</h1>
            <p class="text-sm text-gray-600">Análise de pagamentos por período, forma de pagamento e usuário</p>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Filtros -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Filtros</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data Inicial</label>
            <input
              v-model="filters.start_date"
              type="date"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data Final</label>
            <input
              v-model="filters.end_date"
              type="date"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pagamento</label>
            <select
              v-model="filters.payment_method"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="">Todas</option>
              <option value="dinheiro">Dinheiro</option>
              <option value="cartao_debito">Cartão de Débito</option>
              <option value="cartao_credito">Cartão de Crédito</option>
              <option value="pix">PIX</option>
              <option value="transferencia">Transferência</option>
            </select>
          </div>
          
          <div class="flex items-end">
            <button
              @click="loadReports"
              :disabled="loading"
              class="w-full bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 disabled:opacity-50"
            >
              {{ loading ? 'Carregando...' : 'Filtrar' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
        <span class="ml-2 text-gray-600">Carregando relatórios...</span>
      </div>

      <!-- Relatórios -->
      <div v-else-if="statistics" class="space-y-6">
        <!-- Resumo Geral -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Resumo Geral</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-blue-50 p-4 rounded-lg">
              <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                  <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-blue-600">Total de Pagamentos</p>
                  <p class="text-2xl font-semibold text-blue-900">{{ statistics.total_payments }}</p>
                </div>
              </div>
            </div>
            
            <div class="bg-green-50 p-4 rounded-lg">
              <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                  <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-green-600">Valor Total</p>
                  <p class="text-2xl font-semibold text-green-900">{{ formatCurrency(statistics.total_amount) }}</p>
                </div>
              </div>
            </div>
            
            <div class="bg-purple-50 p-4 rounded-lg">
              <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                  <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-purple-600">Ticket Médio</p>
                  <p class="text-2xl font-semibold text-purple-900">
                    {{ formatCurrency(statistics.total_payments > 0 ? statistics.total_amount / statistics.total_payments : 0) }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Por Forma de Pagamento -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Por Forma de Pagamento</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="(data, method) in statistics.by_method"
              :key="method"
              class="border border-gray-200 rounded-lg p-4"
            >
              <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-900">{{ getPaymentMethodLabel(method) }}</h3>
                <span class="text-xs text-gray-500">{{ data.count }} pagamentos</span>
              </div>
              <p class="text-lg font-semibold text-primary-600">{{ formatCurrency(data.total) }}</p>
              <div class="mt-2">
                <div class="bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-primary-600 h-2 rounded-full"
                    :style="{ width: `${(data.total / statistics.total_amount) * 100}%` }"
                  ></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                  {{ ((data.total / statistics.total_amount) * 100).toFixed(1) }}% do total
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Por Usuário -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Por Usuário</h2>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Usuário
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Pagamentos
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Valor Total
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Ticket Médio
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(data, userId) in statistics.by_user" :key="userId">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ data.user_name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ data.count }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatCurrency(data.total) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatCurrency(data.total / data.count) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Lista de Pagamentos -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Pagamentos Recentes</h2>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    OS
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Valor
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Forma de Pagamento
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Usuário
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Data
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="payment in recentPayments" :key="payment.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    OS #{{ payment.serviceOrder?.order_number }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatCurrency(payment.amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ getPaymentMethodLabel(payment.payment_method) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ payment.user?.name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDateTime(payment.paid_at) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useServiceOrderPaymentsStore } from '@/stores/serviceOrderPayments'

const serviceOrderPaymentsStore = useServiceOrderPaymentsStore()

const loading = ref(false)
const statistics = ref(null)
const recentPayments = ref([])

const filters = ref({
  start_date: '',
  end_date: '',
  payment_method: '',
  user_id: null
})

// Computed
const hasFilters = computed(() => {
  return filters.value.start_date || filters.value.end_date || filters.value.payment_method
})

// Methods
const loadReports = async () => {
  loading.value = true
  
  try {
    const result = await serviceOrderPaymentsStore.fetchReports(filters.value)
    statistics.value = result.statistics
    recentPayments.value = result.payments.slice(0, 20) // Últimos 20 pagamentos
  } catch (error) {
    console.error('Erro ao carregar relatórios:', error)
    alert('Erro ao carregar relatórios')
  } finally {
    loading.value = false
  }
}

const getPaymentMethodLabel = (method: string): string => {
  const labels: Record<string, string> = {
    'dinheiro': 'Dinheiro',
    'cartao_debito': 'Cartão de Débito',
    'cartao_credito': 'Cartão de Crédito',
    'pix': 'PIX',
    'transferencia': 'Transferência',
    'aprazo': 'A Prazo'
  }
  
  return labels[method] || method
}

const formatCurrency = (value: number): string => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

const formatDateTime = (date: string): string => {
  return new Intl.DateTimeFormat('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date(date))
}

// Lifecycle
onMounted(() => {
  // Definir período padrão (último mês)
  const today = new Date()
  const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate())
  
  filters.value.start_date = lastMonth.toISOString().split('T')[0]
  filters.value.end_date = today.toISOString().split('T')[0]
  
  loadReports()
})
</script>
