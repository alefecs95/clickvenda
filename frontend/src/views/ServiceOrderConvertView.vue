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
              <h1 class="text-xl font-semibold text-gray-900">Converter OS em Pedido</h1>
              <p class="text-sm text-gray-600">{{ serviceOrder?.order_number }} - {{ serviceOrder?.customer?.name }}</p>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="loading" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
        <p class="mt-2 text-gray-600">Carregando...</p>
      </div>

      <div v-else-if="serviceOrder" class="space-y-6">
        <!-- Resumo da OS -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Resumo da Ordem de Serviço</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Número da OS</label>
              <p class="text-sm text-gray-900">{{ serviceOrder.order_number }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
              <p class="text-sm text-gray-900">{{ serviceOrder.customer?.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Valor Total</label>
              <p class="text-2xl font-semibold text-primary-600">{{ formatCurrency(serviceOrder.final_amount) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <span 
                :class="getStatusBadgeClass(serviceOrder.status)"
                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
              >
                {{ getStatusLabel(serviceOrder.status) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Formulário de Conversão -->
        <form @submit.prevent="convertOrder" class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Dados do Pedido</h2>
          
          <div class="space-y-6">
            <!-- Método de Pagamento -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Método de Pagamento *</label>
              <select 
                v-model="form.payment_method" 
                required
                @change="onPaymentMethodChange"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
              >
                <option value="">Selecione um método</option>
                <option value="money">Dinheiro</option>
                <option value="card">Cartão</option>
                <option value="pix">PIX</option>
                <option value="credit">Crédito</option>
                <option value="multiple">Múltiplos</option>
              </select>
            </div>

            <!-- Múltiplos Métodos de Pagamento -->
            <div v-if="form.payment_method === 'multiple'" class="space-y-4">
              <h3 class="text-md font-medium text-gray-900">Múltiplos Métodos de Pagamento</h3>
              
              <div v-for="(payment, index) in form.payment_methods" :key="index" class="flex space-x-4">
                <div class="flex-1">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Método</label>
                  <select 
                    v-model="payment.method"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="money">Dinheiro</option>
                    <option value="card">Cartão</option>
                    <option value="pix">PIX</option>
                    <option value="credit">Crédito</option>
                  </select>
                </div>
                <div class="flex-1">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Valor</label>
                  <input 
                    v-model.number="payment.amount"
                    type="number" 
                    step="0.01"
                    min="0"
                    :max="remainingAmount"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                </div>
                <div class="flex items-end">
                  <button
                    type="button"
                    @click="removePaymentMethod(index)"
                    class="bg-red-600 text-white px-3 py-2 rounded-md hover:bg-red-700 transition-colors"
                  >
                    Remover
                  </button>
                </div>
              </div>
              
              <button
                type="button"
                @click="addPaymentMethod"
                class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors"
              >
                Adicionar Método
              </button>
            </div>

            <!-- Observações -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
              <textarea 
                v-model="form.notes"
                rows="3"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                placeholder="Observações adicionais para o pedido..."
              ></textarea>
            </div>

            <!-- Resumo do Valor -->
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="flex justify-between items-center">
                <span class="text-lg font-medium text-gray-900">Valor Total:</span>
                <span class="text-2xl font-bold text-primary-600">{{ formatCurrency(serviceOrder.final_amount) }}</span>
              </div>
              <div v-if="form.payment_method === 'multiple'" class="mt-2">
                <div class="flex justify-between items-center text-sm text-gray-600">
                  <span>Total dos métodos:</span>
                  <span>{{ formatCurrency(totalPaymentMethods) }}</span>
                </div>
                <div v-if="totalPaymentMethods !== serviceOrder.final_amount" class="flex justify-between items-center text-sm text-red-600">
                  <span>Diferença:</span>
                  <span>{{ formatCurrency(serviceOrder.final_amount - totalPaymentMethods) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-4 mt-6">
            <button
              type="button"
              @click="router.back()"
              class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="loading || !canConvert"
              class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 disabled:opacity-50 transition-colors"
            >
              {{ loading ? 'Convertendo...' : 'Converter em Pedido' }}
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useServiceOrdersStore } from '@/stores/serviceOrders'

const router = useRouter()
const route = useRoute()
const serviceOrdersStore = useServiceOrdersStore()

const serviceOrder = ref(null)
const loading = ref(false)

const form = ref({
  payment_method: '',
  payment_methods: [],
  notes: ''
})

// Computed
const totalPaymentMethods = computed(() => {
  return form.value.payment_methods.reduce((total, payment) => total + (payment.amount || 0), 0)
})

const remainingAmount = computed(() => {
  return serviceOrder.value?.final_amount || 0
})

const canConvert = computed(() => {
  if (form.value.payment_method === 'multiple') {
    return Math.abs(totalPaymentMethods.value - remainingAmount.value) < 0.01
  }
  return form.value.payment_method !== ''
})

// Methods
const loadServiceOrder = async () => {
  loading.value = true
  const result = await serviceOrdersStore.fetchServiceOrder(Number(route.params.id))
  if (result.success) {
    serviceOrder.value = result.serviceOrder
  } else {
    alert(result.error)
    router.push('/service-orders')
  }
  loading.value = false
}

const onPaymentMethodChange = () => {
  if (form.value.payment_method === 'multiple') {
    form.value.payment_methods = [
      { method: 'money', amount: 0 }
    ]
  } else {
    form.value.payment_methods = []
  }
}

const addPaymentMethod = () => {
  form.value.payment_methods.push({ method: 'money', amount: 0 })
}

const removePaymentMethod = (index: number) => {
  form.value.payment_methods.splice(index, 1)
}

const convertOrder = async () => {
  if (!canConvert.value) {
    alert('Verifique os dados do pagamento')
    return
  }

  loading.value = true
  
  try {
    const result = await serviceOrdersStore.convertToOrder(Number(route.params.id), form.value)
    
    if (result.success) {
      alert('OS convertida em pedido com sucesso!')
      router.push('/service-orders')
    } else {
      alert(result.error)
    }
  } finally {
    loading.value = false
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

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

// Lifecycle
onMounted(() => {
  loadServiceOrder()
})
</script>
