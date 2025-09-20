<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
        <p class="mt-4 text-lg text-gray-600">Carregando veículo...</p>
      </div>
    </div>

    <!-- Content -->
    <div v-else-if="vehicle">
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
              <h1 class="text-xl font-semibold text-gray-900">
                {{ vehicle?.make }} {{ vehicle?.model }}
              </h1>
              <p class="text-sm text-gray-600">{{ vehicle?.customer?.name }}</p>
            </div>
          </div>
          
          <div class="flex space-x-2">
            <button
              @click="router.push(`/vehicles/${vehicle?.id}/edit`)"
              class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors"
            >
              Editar
            </button>
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

      <div v-else-if="vehicle" class="space-y-6">
        <!-- Informações Básicas -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Informações Básicas</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
              <p class="text-sm text-gray-900">{{ vehicle.customer?.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
              <span 
                :class="vehicle.type === 'veiculo' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'"
                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
              >
                {{ getTypeLabel(vehicle.type) }}
              </span>
            </div>
            <div v-if="vehicle.plate">
              <label class="block text-sm font-medium text-gray-700 mb-1">Placa</label>
              <p class="text-sm text-gray-900">{{ vehicle.plate }}</p>
            </div>
            <div v-if="vehicle.chassis_number">
              <label class="block text-sm font-medium text-gray-700 mb-1">Número do Chassi</label>
              <p class="text-sm text-gray-900 font-mono">{{ vehicle.chassis_number }}</p>
            </div>
            <div v-if="vehicle.engine_number">
              <label class="block text-sm font-medium text-gray-700 mb-1">Número do Motor</label>
              <p class="text-sm text-gray-900 font-mono">{{ vehicle.engine_number }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <span 
                :class="vehicle.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
              >
                {{ vehicle.active ? 'Ativo' : 'Inativo' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Detalhes do Veículo -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Detalhes do Veículo</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div v-if="vehicle.make">
              <label class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
              <p class="text-sm text-gray-900">{{ vehicle.make }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
              <p class="text-sm text-gray-900">{{ vehicle.model }}</p>
            </div>
            <div v-if="vehicle.year">
              <label class="block text-sm font-medium text-gray-700 mb-1">Ano</label>
              <p class="text-sm text-gray-900">{{ vehicle.year }}</p>
            </div>
            <div v-if="vehicle.color">
              <label class="block text-sm font-medium text-gray-700 mb-1">Cor</label>
              <p class="text-sm text-gray-900">{{ vehicle.color }}</p>
            </div>
          </div>
        </div>

        <!-- Observações -->
        <div v-if="vehicle.notes" class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Observações</h2>
          <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ vehicle.notes }}</p>
        </div>

        <!-- Histórico de Serviços -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Histórico de Serviços</h2>
          
          <div v-if="historyLoading" class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
            <p class="mt-2 text-sm text-gray-500">Carregando histórico...</p>
          </div>
          
          <div v-else-if="!serviceHistory || serviceHistory.length === 0" class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum serviço encontrado</h3>
            <p class="mt-1 text-sm text-gray-500">Este veículo ainda não possui histórico de serviços.</p>
          </div>

          <div v-else-if="serviceHistory && serviceHistory.length > 0" class="space-y-4">
            <div 
              v-for="service in serviceHistory" 
              :key="service.id" 
              class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50"
            >
              <div class="flex justify-between items-start">
                <div class="flex-1">
                  <div class="flex items-center space-x-2">
                    <h3 class="text-sm font-medium text-gray-900">{{ service.order_number }}</h3>
                    <span 
                      :class="getStatusBadgeClass(service.status)"
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    >
                      {{ getStatusLabel(service.status) }}
                    </span>
                  </div>
                  <p class="text-sm text-gray-600 mt-1">{{ service.problem_description }}</p>
                  <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                    <span>{{ formatDate(service.opening_date) }}</span>
                    <span v-if="service.technical_responsible?.name">
                      Técnico: {{ service.technical_responsible.name }}
                    </span>
                    <span v-if="service.final_amount">
                      Valor: {{ formatCurrency(service.final_amount) }}
                    </span>
                  </div>
                </div>
                <button
                  @click="router.push(`/service-orders/${service.id}`)"
                  class="text-primary-600 hover:text-primary-900 text-sm font-medium"
                >
                  Ver Detalhes
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
    </div>

    <!-- Error State -->
    <div v-else class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Veículo não encontrado</h3>
        <p class="mt-1 text-sm text-gray-500">O veículo solicitado não foi encontrado.</p>
        <div class="mt-6">
          <button
            @click="router.push('/vehicles')"
            class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors"
          >
            Voltar para Veículos
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useVehiclesStore } from '@/stores/vehicles'
import { useServiceOrdersStore } from '@/stores/serviceOrders'

const router = useRouter()
const route = useRoute()
const vehiclesStore = useVehiclesStore()
const serviceOrdersStore = useServiceOrdersStore()

const vehicle = ref(null)
const serviceHistory = ref([])
const loading = ref(false)
const historyLoading = ref(false)

// Função para obter o label do tipo
const getTypeLabel = (type: string): string => {
  const typeLabels: Record<string, string> = {
    'veiculo': 'Veículo',
    'equipamento': 'Equipamento',
    'maquina': 'Máquina',
    'outro': 'Outro'
  }
  return typeLabels[type] || 'Desconhecido'
}

// Methods
const loadVehicle = async () => {
  loading.value = true
  const result = await vehiclesStore.fetchVehicle(Number(route.params.id))
  
  if (result.success) {
    vehicle.value = result.vehicle
    await loadServiceHistory()
  } else {
    alert(result.error)
    router.push('/vehicles')
  }
  loading.value = false
}

const loadServiceHistory = async () => {
  historyLoading.value = true
  const result = await vehiclesStore.fetchServiceHistory(Number(route.params.id))
  if (result.success) {
    serviceHistory.value = result.serviceOrders || []
  }
  historyLoading.value = false
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
onMounted(() => {
  loadVehicle()
})
</script>
