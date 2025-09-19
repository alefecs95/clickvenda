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
            <h1 class="text-xl font-semibold text-gray-900">Pedidos</h1>
          </div>
          
          <button
            @click="router.push('/sales')"
            class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors"
          >
            Nova Venda
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select 
              v-model="filters.status" 
              @change="loadOrders"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="">Todos</option>
              <option value="pending">Pendente</option>
              <option value="completed">Concluído</option>
              <option value="cancelled">Cancelado</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
            <input 
              type="date" 
              v-model="filters.date_from"
              @change="loadOrders"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
            <input 
              type="date" 
              v-model="filters.date_to"
              @change="loadOrders"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
            <input 
              type="text" 
              v-model="filters.search"
              @input="debounceSearch"
              placeholder="Número ou cliente..."
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
          </div>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="ordersStore.error" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-red-800">{{ ordersStore.error }}</p>
          </div>
          <div class="ml-auto pl-3">
            <button @click="ordersStore.clearError()" class="text-red-400 hover:text-red-600">
              <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Orders List -->
      <div v-if="ordersStore.loading" class="text-center py-12">
        <svg class="animate-spin h-8 w-8 text-primary-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="mt-2 text-gray-600">Carregando pedidos...</p>
      </div>

      <div v-else-if="ordersStore.orders.length === 0" class="text-center py-12">
        <svg class="h-12 w-12 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p class="mt-2 text-gray-600">Nenhum pedido encontrado</p>
        <button
          @click="router.push('/sales')"
          class="mt-4 bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors"
        >
          Criar Primeiro Pedido
        </button>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="order in ordersStore.orders"
          :key="order.id"
          class="bg-white rounded-lg shadow p-6"
        >
          <div class="flex justify-between items-start mb-4">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Pedido #{{ order.order_number }}</h3>
              <p class="text-sm text-gray-600">{{ order.customer?.name || 'Cliente não identificado' }}</p>
              <p class="text-xs text-gray-500">{{ ordersStore.formatDate(order.created_at) }}</p>
            </div>
            <div class="text-right">
              <p class="text-lg font-semibold text-gray-900">{{ ordersStore.formatPrice(order.final_amount) }}</p>
              <span
                :class="{
                  'bg-green-100 text-green-800': order.status === 'completed',
                  'bg-yellow-100 text-yellow-800': order.status === 'pending',
                  'bg-red-100 text-red-800': order.status === 'cancelled'
                }"
                class="inline-flex px-2 py-1 text-xs font-medium rounded-full"
              >
                {{ ordersStore.getStatusText(order.status) }}
              </span>
            </div>
          </div>
          
          <div class="border-t border-gray-200 pt-4">
            <div class="flex justify-between items-center">
              <div class="text-sm text-gray-600">
                <span class="font-medium">Forma de Pagamento:</span> {{ ordersStore.getPaymentMethodText(order.payment_method) }}
              </div>
              <div class="flex space-x-2">
                <button
                  @click="viewOrder(order)"
                  class="text-primary-600 hover:text-primary-900 text-sm font-medium"
                >
                  Ver Detalhes
                </button>
                <button
                  @click="printOrder(order)"
                  class="text-blue-600 hover:text-blue-900 text-sm font-medium"
                >
                  Imprimir
                </button>
                <button
                  v-if="order.status === 'pending'"
                  @click="completeOrder(order.id)"
                  class="text-green-600 hover:text-green-900 text-sm font-medium"
                >
                  Concluir
                </button>
                <button
                  v-if="order.status === 'pending'"
                  @click="cancelOrder(order.id)"
                  class="text-red-600 hover:text-red-900 text-sm font-medium"
                >
                  Cancelar
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="ordersStore.pagination && ordersStore.pagination.last_page > 1" class="bg-white rounded-lg shadow p-4">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Mostrando {{ (ordersStore.pagination.current_page - 1) * ordersStore.pagination.per_page + 1 }} 
              a {{ Math.min(ordersStore.pagination.current_page * ordersStore.pagination.per_page, ordersStore.pagination.total) }} 
              de {{ ordersStore.pagination.total }} resultados
            </div>
            <div class="flex space-x-2">
              <button
                @click="changePage(ordersStore.pagination.current_page - 1)"
                :disabled="ordersStore.pagination.current_page === 1"
                class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
              >
                Anterior
              </button>
              <span class="px-3 py-1 text-sm text-gray-700">
                {{ ordersStore.pagination.current_page }} de {{ ordersStore.pagination.last_page }}
              </span>
              <button
                @click="changePage(ordersStore.pagination.current_page + 1)"
                :disabled="ordersStore.pagination.current_page === ordersStore.pagination.last_page"
                class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
              >
                Próxima
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useOrdersStore } from '@/stores/orders'

const router = useRouter()
const ordersStore = useOrdersStore()

const filters = reactive({
  status: '',
  date_from: '',
  date_to: '',
  search: '',
  sort_by: 'created_at',
  sort_order: 'desc' as 'asc' | 'desc',
  per_page: 15
})

let searchTimeout: NodeJS.Timeout | null = null

const loadOrders = async () => {
  try {
    await ordersStore.fetchOrders(filters)
  } catch (error) {
    console.error('Erro ao carregar pedidos:', error)
  }
}

const debounceSearch = () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
  searchTimeout = setTimeout(() => {
    loadOrders()
  }, 500)
}

const changePage = async (page: number) => {
  if (page >= 1 && page <= (ordersStore.pagination?.last_page || 1)) {
    await ordersStore.fetchOrders({ ...filters, page: page.toString() })
  }
}

const viewOrder = (order: any) => {
  router.push(`/orders/${order.id}`)
}

const printOrder = (order: any) => {
  // Abrir página de detalhes em nova aba para impressão
  const detailsUrl = router.resolve(`/orders/${order.id}`).href
  const printWindow = window.open(detailsUrl, '_blank')
  
  if (printWindow) {
    printWindow.addEventListener('load', () => {
      setTimeout(() => {
        printWindow.print()
      }, 1000)
    })
  }
}

const cancelOrder = async (id: number) => {
  if (confirm('Tem certeza que deseja cancelar este pedido?')) {
    try {
      await ordersStore.cancelOrder(id)
      // Recarregar pedidos para atualizar a lista
      await loadOrders()
    } catch (error) {
      console.error('Erro ao cancelar pedido:', error)
    }
  }
}

const completeOrder = async (id: number) => {
  if (confirm('Tem certeza que deseja concluir este pedido?')) {
    try {
      await ordersStore.completeOrder(id)
      // Recarregar pedidos para atualizar a lista
      await loadOrders()
    } catch (error) {
      console.error('Erro ao concluir pedido:', error)
    }
  }
}

onMounted(() => {
  loadOrders()
})
</script>
