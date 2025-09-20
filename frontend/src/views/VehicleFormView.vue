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
            <h1 class="text-xl font-semibold text-gray-900">
              {{ isEditing ? 'Editar Veículo' : 'Novo Veículo' }}
            </h1>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-2 py-8">
      <form @submit.prevent="saveVehicle" class="bg-white rounded-lg shadow p-6">
        <div class="space-y-6">
          <!-- Cliente (Opcional - apenas para faturamento) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cliente (Opcional - apenas para faturamento)</label>
            <select
              v-model="form.customer_id"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="">Selecione um cliente (opcional)</option>
              <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                {{ customer.name }}
              </option>
            </select>
          </div>

          <!-- Informações do Cliente no Momento -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Cliente no Momento</label>
              <input
                v-model="form.customer_name_at_time"
                type="text"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                placeholder="Nome do cliente no momento"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Telefone do Cliente no Momento</label>
              <input
                v-model="form.customer_phone_at_time"
                type="text"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                placeholder="Telefone do cliente no momento"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email do Cliente no Momento</label>
              <input
                v-model="form.customer_email_at_time"
                type="email"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                placeholder="Email do cliente no momento"
              />
            </div>
          </div>

          <!-- Tipo -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
            <select
              v-model="form.type"
              required
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="vehicle">Veículo</option>
              <option value="equipment">Equipamento</option>
            </select>
          </div>

          <!-- Placa -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Placa *</label>
            <input
              v-model="form.plate"
              @input="formatPlate"
              type="text"
              required
              maxlength="8"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
              placeholder="ABC-1234"
            />
            <p class="text-xs text-gray-500 mt-1">Formato: XXX-XXXX</p>
          </div>

          <!-- Marca -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
            <input
              v-model="form.make"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
              placeholder="Ex: Toyota, Honda, Ford"
            />
          </div>

          <!-- Modelo -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Modelo *</label>
            <input
              v-model="form.model"
              type="text"
              required
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
              placeholder="Ex: Corolla, Civic, Focus"
            />
          </div>

          <!-- Ano -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ano</label>
            <input
              v-model="form.year"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
              placeholder="2020"
            />
          </div>

          <!-- Cor -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cor</label>
            <input
              v-model="form.color"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
              placeholder="Ex: Branco, Preto, Prata"
            />
          </div>

          <!-- Número do Chassi -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Número do Chassi</label>
            <input
              v-model="form.chassis_number"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
              placeholder="Número do chassi"
            />
          </div>

          <!-- Número do Motor -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Número do Motor</label>
            <input
              v-model="form.engine_number"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
              placeholder="Número do motor"
            />
          </div>

          <!-- Observações -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
            <textarea
              v-model="form.notes"
              rows="3"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
              placeholder="Observações adicionais sobre o veículo/equipamento"
            ></textarea>
          </div>

          <!-- Status -->
          <div>
            <label class="flex items-center">
              <input
                v-model="form.active"
                type="checkbox"
                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
              />
              <span class="ml-2 text-sm text-gray-700">Veículo ativo</span>
            </label>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-4 mt-8">
          <button
            type="button"
            @click="router.back()"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 disabled:opacity-50 transition-colors"
          >
            {{ loading ? 'Salvando...' : (isEditing ? 'Atualizar' : 'Criar') }}
          </button>
        </div>
      </form>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useVehiclesStore } from '@/stores/vehicles'
import { useCustomersStore } from '@/stores/customers'

const router = useRouter()
const route = useRoute()
const vehiclesStore = useVehiclesStore()
const customersStore = useCustomersStore()

const loading = ref(false)
const vehicle = ref(null)

const form = ref({
  customer_id: '', // Opcional - apenas para faturamento
  customer_name_at_time: '', // Nome do cliente no momento
  customer_phone_at_time: '', // Telefone do cliente no momento
  customer_email_at_time: '', // Email do cliente no momento
  type: 'vehicle',
  plate: '', // Obrigatório
  make: '',
  model: '', // Obrigatório
  year: '',
  color: '',
  chassis_number: '',
  engine_number: '',
  notes: '',
  active: true
})

const isEditing = computed(() => !!route.params.id)

// Computed
const customers = computed(() => customersStore.customers)

// Função para mapear tipos do backend para frontend
const mapBackendTypeToFrontend = (backendType: string): string => {
  const typeMap: Record<string, string> = {
    'veiculo': 'vehicle',
    'equipamento': 'equipment',
    'maquina': 'equipment', // Máquinas são tratadas como equipamentos no frontend
    'outro': 'equipment'    // Outros são tratados como equipamentos no frontend
  }
  return typeMap[backendType] || 'vehicle'
}

// Methods
const formatPlate = (event: Event) => {
  const target = event.target as HTMLInputElement
  let value = target.value.replace(/\D/g, '') // Remove tudo que não é dígito
  
  if (value.length > 3) {
    value = value.substring(0, 3) + '-' + value.substring(3, 7)
  }
  
  form.value.plate = value.toUpperCase()
}

const loadData = async () => {
  await customersStore.fetchCustomers()
  
  if (isEditing.value) {
    loading.value = true
    const result = await vehiclesStore.fetchVehicle(Number(route.params.id))
    
    if (result.success) {
      vehicle.value = result.vehicle
      form.value = {
        customer_id: vehicle.value.customer_id || '', // Opcional
        customer_name_at_time: vehicle.value.customer_name_at_time || '',
        customer_phone_at_time: vehicle.value.customer_phone_at_time || '',
        customer_email_at_time: vehicle.value.customer_email_at_time || '',
        type: mapBackendTypeToFrontend(vehicle.value.type), // Mapear tipo do backend para frontend
        plate: vehicle.value.plate || '',
        make: vehicle.value.make || '',
        model: vehicle.value.model,
        year: vehicle.value.year || '',
        color: vehicle.value.color || '',
        chassis_number: vehicle.value.chassis_number || '',
        engine_number: vehicle.value.engine_number || '',
        notes: vehicle.value.notes || '',
        active: vehicle.value.active
      }
    } else {
      alert(result.error)
      router.push('/vehicles')
    }
    loading.value = false
  }
}

const saveVehicle = async () => {
  loading.value = true
  
  try {
    // Limpar dados vazios
    const cleanData = { ...form.value }
    Object.keys(cleanData).forEach(key => {
      if (cleanData[key] === '' || cleanData[key] === null) {
        cleanData[key] = null
      }
    })
    
    console.log('Dados do formulário:', cleanData)
    let result
    if (isEditing.value) {
      result = await vehiclesStore.updateVehicle(Number(route.params.id), cleanData)
    } else {
      result = await vehiclesStore.createVehicle(cleanData)
    }
    
    if (result.success) {
      router.push('/vehicles')
    } else {
      alert(result.error)
    }
  } finally {
    loading.value = false
  }
}

// Lifecycle
onMounted(() => {
  loadData()
})
</script>
