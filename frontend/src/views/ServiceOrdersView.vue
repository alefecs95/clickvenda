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
            <h1 class="text-xl font-semibold text-gray-900">Ordens de Serviço</h1>
          </div>
          
          <button
            @click="router.push('/service-orders/new')"
            class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors"
          >
            Nova OS
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Statistics -->
      <div v-if="statistics" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
              <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total</p>
              <p class="text-2xl font-semibold text-gray-900">{{ statistics.total }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-yellow-100 rounded-lg">
              <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Em Andamento</p>
              <p class="text-2xl font-semibold text-gray-900">{{ statistics.em_andamento }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
              <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Concluídas</p>
              <p class="text-2xl font-semibold text-gray-900">{{ statistics.concluidas }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
              <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Receita Total</p>
              <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(statistics.receita_total) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select 
              v-model="filters.status" 
              @change="loadServiceOrders"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="">Todos</option>
              <option value="aberta">Aberta</option>
              <option value="em_andamento">Em Andamento</option>
              <option value="aguardando_aprovacao">Aguardando Aprovação</option>
              <option value="concluida">Concluída</option>
              <option value="cancelada">Cancelada</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
            <input 
              v-model="filters.opening_date_from"
              @change="loadServiceOrders"
              type="date" 
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
            <input 
              v-model="filters.opening_date_to"
              @change="loadServiceOrders"
              type="date" 
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
            <select 
              v-model="filters.customer_id" 
              @change="loadServiceOrders"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="">Todos</option>
              <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                {{ customer.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
            <input 
              v-model="filters.search"
              @input="debounceSearch"
              type="text" 
              placeholder="Número OS, cliente..."
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
          </div>
        </div>
      </div>

      <!-- Service Orders List -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div v-if="loading" class="p-8 text-center">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
          <p class="mt-2 text-gray-600">Carregando...</p>
        </div>

        <div v-else-if="serviceOrders.length === 0" class="p-8 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma ordem de serviço encontrada</h3>
          <p class="mt-1 text-sm text-gray-500">Comece criando uma nova ordem de serviço.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  OS
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Cliente
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Veículo/Equipamento
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Data Abertura
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Valor Total
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status Pagamento
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ações
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="serviceOrder in serviceOrders" :key="serviceOrder.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ serviceOrder.order_number }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ serviceOrder.customer?.name || 'N/A' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    {{ serviceOrder.vehicle ? getVehicleIdentification(serviceOrder.vehicle) : 'N/A' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    :class="getStatusBadgeClass(serviceOrder.status)"
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                  >
                    {{ getStatusLabel(serviceOrder.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(serviceOrder.opening_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatCurrency(serviceOrder.final_amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-col space-y-1">
                    <span 
                      :class="getPaymentStatusBadgeClass(serviceOrder)"
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    >
                      {{ getPaymentStatusLabel(serviceOrder) }}
                    </span>
                    <span v-if="serviceOrder.payment_method" class="text-xs text-gray-500">
                      {{ getPaymentMethodLabel(serviceOrder.payment_method) }}
                    </span>
                    <span v-if="serviceOrder.payment_method === 'credit'" class="text-xs" :class="isServiceOrderOverdue(serviceOrder) ? 'text-red-600 font-medium' : 'text-gray-500'">
                      Vencimento: {{ getDueDate(serviceOrder) }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button
                      @click="viewServiceOrder(serviceOrder.id)"
                      class="text-primary-600 hover:text-primary-900"
                    >
                      Ver
                    </button>
                    <button
                      v-if="canApprove(serviceOrder)"
                      @click="approveServiceOrder(serviceOrder.id)"
                      class="text-green-600 hover:text-green-900"
                    >
                      Aprovar
                    </button>
                    <button
                      v-if="canComplete(serviceOrder)"
                      @click="completeServiceOrder(serviceOrder.id)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      Concluir
                    </button>
                    <button
                      v-if="canConvert(serviceOrder)"
                      @click="convertServiceOrder(serviceOrder.id)"
                      class="text-purple-600 hover:text-purple-900"
                    >
                      Converter
                    </button>
                    <button
                      v-if="canCancel(serviceOrder)"
                      @click="cancelServiceOrder(serviceOrder.id)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Cancelar
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
          <div class="flex-1 flex justify-between sm:hidden">
            <button
              @click="loadServiceOrders(pagination.current_page - 1)"
              :disabled="!pagination.prev_page_url"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
            >
              Anterior
            </button>
            <button
              @click="loadServiceOrders(pagination.current_page + 1)"
              :disabled="!pagination.next_page_url"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
            >
              Próximo
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Mostrando {{ pagination.from }} até {{ pagination.to }} de {{ pagination.total }} resultados
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <button
                  @click="loadServiceOrders(pagination.current_page - 1)"
                  :disabled="!pagination.prev_page_url"
                  class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                >
                  Anterior
                </button>
                <button
                  @click="loadServiceOrders(pagination.current_page + 1)"
                  :disabled="!pagination.next_page_url"
                  class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                >
                  Próximo
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useServiceOrdersStore } from '@/stores/serviceOrders'
import { useCustomersStore } from '@/stores/customers'
import { useSettingsStore } from '@/stores/settings'
import { useVehiclesStore } from '@/stores/vehicles'

const router = useRouter()
const serviceOrdersStore = useServiceOrdersStore()
const customersStore = useCustomersStore()
const vehiclesStore = useVehiclesStore()
const settingsStore = useSettingsStore()

// State
const serviceOrders = computed(() => serviceOrdersStore.serviceOrders)
const statistics = computed(() => serviceOrdersStore.statistics)
const loading = computed(() => serviceOrdersStore.loading)
const customers = computed(() => customersStore.customers)
const pagination = ref(null)

const filters = ref({
  status: '',
  customer_id: '',
  opening_date_from: '',
  opening_date_to: '',
  search: ''
})

// Methods
const loadServiceOrders = async (page = 1) => {
  const result = await serviceOrdersStore.fetchServiceOrders({
    ...filters.value,
    page
  })
  
  if (result.success) {
    pagination.value = result.pagination
  }
}

const loadCustomers = async () => {
  await customersStore.fetchCustomers()
}

const loadStatistics = async () => {
  await serviceOrdersStore.fetchStatistics()
}

const debounceSearch = (() => {
  let timeout: NodeJS.Timeout
  return () => {
    clearTimeout(timeout)
    timeout = setTimeout(() => {
      loadServiceOrders()
    }, 500)
  }
})()

const viewServiceOrder = (id: number) => {
  router.push(`/service-orders/${id}`)
}

const approveServiceOrder = async (id: number) => {
  if (confirm('Deseja aprovar esta ordem de serviço?')) {
    const result = await serviceOrdersStore.approveServiceOrder(id)
    if (result.success) {
      loadServiceOrders()
      loadStatistics()
    } else {
      alert(result.error)
    }
  }
}

const completeServiceOrder = async (id: number) => {
  if (confirm('Deseja concluir esta ordem de serviço?')) {
    const result = await serviceOrdersStore.completeServiceOrder(id)
    if (result.success) {
      loadServiceOrders()
      loadStatistics()
    } else {
      alert(result.error)
    }
  }
}

const convertServiceOrder = (id: number) => {
  router.push(`/service-orders/${id}/convert`)
}

const cancelServiceOrder = async (id: number) => {
  if (confirm('Deseja cancelar esta ordem de serviço?')) {
    const result = await serviceOrdersStore.deleteServiceOrder(id)
    if (result.success) {
      loadServiceOrders()
      loadStatistics()
    } else {
      alert(result.error)
    }
  }
}

const getStatusLabel = (status: string) => {
  return serviceOrdersStore.getStatusLabel(status)
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

const getPaymentMethodLabel = (method: string) => {
  const methods: Record<string, string> = {
    'money': 'Dinheiro',
    'card': 'Cartão',
    'pix': 'PIX',
    'credit': 'A Prazo (Crédito)'
  }
  return methods[method] || method
}

const getPaymentStatusLabel = (serviceOrder: any) => {
  if (!serviceOrder.payment_method) {
    return 'Não Informado'
  }
  
  // Se é a prazo, nunca é "Pago" - sempre é A Prazo, Parcial ou Vencido
  if (serviceOrder.payment_method === 'credit') {
    // Se tem pagamentos registrados
    if (serviceOrder.total_paid !== undefined && serviceOrder.remaining_amount !== undefined) {
      if (serviceOrder.remaining_amount <= 0) {
        return 'Quitado' // OS a prazo quitada, mas não "Pago"
      } else if (serviceOrder.total_paid > 0) {
        return 'Parcial'
      } else {
        // Verificar se está vencido
        const isOverdue = isServiceOrderOverdue(serviceOrder)
        return isOverdue ? 'Vencido' : 'A Prazo'
      }
    }
    
    // Se não tem pagamentos registrados, verificar se está vencido
    const isOverdue = isServiceOrderOverdue(serviceOrder)
    return isOverdue ? 'Vencido' : 'A Prazo'
  }
  
  // Para outras formas (dinheiro, cartão, pix), considera pago
  return 'Pago'
}

// Função para verificar se OS a prazo está vencida
const isServiceOrderOverdue = (serviceOrder: any) => {
  if (serviceOrder.payment_method !== 'credit') return false
  
  const openingDate = new Date(serviceOrder.opening_date)
  const today = new Date()
  const defaultPaymentTerm = typeof settingsStore.defaultPaymentTerm === 'number' 
    ? settingsStore.defaultPaymentTerm 
    : parseInt(settingsStore.defaultPaymentTerm as string) || 30
  
  const dueDate = new Date(openingDate)
  dueDate.setDate(dueDate.getDate() + defaultPaymentTerm)
  
  return today > dueDate
}

// Função para calcular data de vencimento
const getDueDate = (serviceOrder: any) => {
  if (serviceOrder.payment_method !== 'credit') return ''
  
  const openingDate = new Date(serviceOrder.opening_date)
  const defaultPaymentTerm = typeof settingsStore.defaultPaymentTerm === 'number' 
    ? settingsStore.defaultPaymentTerm 
    : parseInt(settingsStore.defaultPaymentTerm as string) || 30
  
  const dueDate = new Date(openingDate)
  dueDate.setDate(dueDate.getDate() + defaultPaymentTerm)
  
  return formatDate(dueDate.toISOString())
}

const getPaymentStatusBadgeClass = (serviceOrder: any) => {
  const status = getPaymentStatusLabel(serviceOrder)
  
  const statusClasses = {
    'Pago': 'bg-green-100 text-green-800',
    'Quitado': 'bg-green-100 text-green-800',
    'Parcial': 'bg-yellow-100 text-yellow-800',
    'Pendente': 'bg-orange-100 text-orange-800',
    'Vencido': 'bg-red-100 text-red-800',
    'A Prazo': 'bg-blue-100 text-blue-800',
    'Não Informado': 'bg-gray-100 text-gray-800'
  }
  
  return statusClasses[status as keyof typeof statusClasses] || 'bg-gray-100 text-gray-800'
}

const getVehicleIdentification = (vehicle: any) => {
  return vehiclesStore.getShortIdentification(vehicle)
}

const canApprove = (serviceOrder: any) => {
  return serviceOrder.status === 'aguardando_aprovacao'
}

const canComplete = (serviceOrder: any) => {
  return ['aberta', 'em_andamento'].includes(serviceOrder.status)
}

const canConvert = (serviceOrder: any) => {
  return serviceOrder.billing_type === 'orcamento' && serviceOrder.customer_approved
}

const canCancel = (serviceOrder: any) => {
  return ['aberta', 'em_andamento', 'aguardando_aprovacao'].includes(serviceOrder.status)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('pt-BR')
}

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

// Lifecycle
onMounted(async () => {
  await settingsStore.loadSettings() // Carregar configurações para prazo padrão
  loadServiceOrders()
  loadCustomers()
  loadStatistics()
})
</script>
