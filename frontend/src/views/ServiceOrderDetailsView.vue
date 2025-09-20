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
              Aprovar
            </button>
            <button
              v-if="serviceOrder && canComplete(serviceOrder)"
              @click="completeServiceOrder"
              class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
            >
              Concluir
            </button>
            <button
              v-if="serviceOrder && canConvert(serviceOrder)"
              @click="convertServiceOrder"
              class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors"
            >
              Converter em Pedido
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
              <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(serviceOrder.total_products) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Total Serviços</label>
              <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(serviceOrder.total_services) }}</p>
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
              <select
                v-model="paymentForm.payment_method"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
              >
                <option value="">Selecione...</option>
                <option value="money">Dinheiro</option>
                <option value="card">Cartão</option>
                <option value="pix">PIX</option>
                <option value="credit">A Prazo (Crédito)</option>
              </select>
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

const paymentForm = ref({
  amount: 0,
  payment_method: 'dinheiro',
  payment_reference: '',
  notes: ''
})

// Computed
const users = computed(() => usersStore.users)

// Methods
const loadServiceOrder = async () => {
  loading.value = true
  const result = await serviceOrdersStore.fetchServiceOrder(Number(route.params.id))
  if (result.success) {
    serviceOrder.value = result.serviceOrder
    
    // Carregar pagamentos da OS
    try {
      await serviceOrderPaymentsStore.fetchPayments(Number(route.params.id))
    } catch (error) {
      console.error('Erro ao carregar pagamentos:', error)
    }
  } else {
    alert(result.error)
    router.push('/service-orders')
  }
  loading.value = false
}

const startEditingTechnicalResponsible = () => {
  editingTechnicalResponsible.value = true
  editingTechnicalResponsibleData.value = serviceOrder.value.technical_responsible_id
}

const cancelEditingTechnicalResponsible = () => {
  editingTechnicalResponsible.value = false
  editingTechnicalResponsibleData.value = ''
}

const saveTechnicalResponsible = async () => {
  if (!editingTechnicalResponsibleData.value) {
    alert('Selecione um técnico responsável')
    return
  }

  const result = await serviceOrdersStore.updateServiceOrder(serviceOrder.value.id, {
    technical_responsible_id: editingTechnicalResponsibleData.value
  })

  if (result.success) {
    editingTechnicalResponsible.value = false
    editingTechnicalResponsibleData.value = ''
    await loadServiceOrder()
    alert('Técnico responsável atualizado com sucesso!')
  } else {
    alert(result.error || 'Erro ao atualizar técnico responsável')
  }
}

const openPaymentModal = () => {
  if (!serviceOrder.value) {
    alert('Erro: OS não carregada')
    return
  }

  paymentForm.value = {
    amount: serviceOrder.value.remaining_amount || 0,
    payment_method: 'dinheiro',
    payment_reference: '',
    notes: ''
  }
  showPaymentModal.value = true
}

const closePaymentModal = () => {
  showPaymentModal.value = false
  paymentForm.value = {
    amount: 0,
    payment_method: 'dinheiro',
    payment_reference: '',
    notes: ''
  }
}

const getMaxPaymentAmount = () => {
  return serviceOrder.value?.remaining_amount || 0
}

const registerPayment = async () => {
  if (!serviceOrder.value) return

  const paymentAmount = Number(paymentForm.value.amount)
  const remainingAmount = serviceOrder.value.remaining_amount || 0

  // Validações no frontend
  if (paymentAmount <= 0) {
    alert('O valor do pagamento deve ser maior que zero.')
    return
  }

  if (paymentAmount > remainingAmount) {
    alert(`O valor do pagamento não pode ser maior que o valor restante (R$ ${formatCurrency(remainingAmount)}).`)
    return
  }

  paymentSubmitting.value = true
  try {
    const result = await serviceOrderPaymentsStore.createPayment(
      serviceOrder.value.id,
      {
        amount: paymentAmount,
        payment_method: paymentForm.value.payment_method,
        payment_reference: paymentForm.value.payment_reference,
        notes: paymentForm.value.notes
      }
    )

    if (result) {
      closePaymentModal()
      await loadServiceOrder()
      alert('Pagamento registrado com sucesso!')
    }
  } catch (error: any) {
    console.error('Erro ao registrar pagamento:', error)
    alert(error.message || 'Erro ao registrar pagamento')
  } finally {
    paymentSubmitting.value = false
  }
}

const approveServiceOrder = async () => {
  if (confirm('Deseja aprovar esta ordem de serviço?')) {
    const result = await serviceOrdersStore.approveServiceOrder(Number(route.params.id))
    if (result.success) {
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

const convertServiceOrder = () => {
  router.push(`/service-orders/${route.params.id}/convert`)
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

const canComplete = (serviceOrder: any) => {
  return serviceOrder && ['aberta', 'em_andamento'].includes(serviceOrder.status)
}

const canConvert = (serviceOrder: any) => {
  return serviceOrder?.billing_type === 'orcamento' && serviceOrder?.customer_approved
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

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

// Lifecycle
onMounted(async () => {
  await usersStore.fetchUsers()
  loadServiceOrder()
})
</script>
