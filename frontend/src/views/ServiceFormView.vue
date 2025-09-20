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
              {{ isEditing ? 'Editar Serviço' : 'Novo Serviço' }}
            </h1>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <form @submit.prevent="saveService" class="bg-white rounded-lg shadow p-6">
        <div class="space-y-6">
          <!-- Nome -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
              placeholder="Nome do serviço"
            />
          </div>

          <!-- Descrição -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
              placeholder="Descrição do serviço"
            ></textarea>
          </div>

          <!-- Preço -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Preço *</label>
            <input
              v-model.number="form.price"
              type="number"
              step="0.01"
              min="0"
              required
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
              placeholder="0.00"
            />
          </div>

          <!-- Categoria -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
            <input
              v-model="form.category"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
              placeholder="Ex: Manutenção, Instalação, Reparo"
            />
          </div>

          <!-- Status -->
          <div>
            <label class="flex items-center">
              <input
                v-model="form.active"
                type="checkbox"
                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
              />
              <span class="ml-2 text-sm text-gray-700">Serviço ativo</span>
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
import { useServicesStore } from '@/stores/services'

const router = useRouter()
const route = useRoute()
const servicesStore = useServicesStore()

const loading = ref(false)
const service = ref(null)

const form = ref({
  name: '',
  description: '',
  price: 0,
  category: '',
  active: true
})

const isEditing = computed(() => !!route.params.id)

// Methods
const loadService = async () => {
  if (!isEditing.value) return
  
  loading.value = true
  const result = await servicesStore.fetchService(Number(route.params.id))
  
  if (result.success) {
    service.value = result.service
    form.value = {
      name: service.value.name,
      description: service.value.description || '',
      price: service.value.price,
      category: service.value.category || '',
      active: service.value.active
    }
  } else {
    alert(result.error)
    router.push('/services')
  }
  loading.value = false
}

const saveService = async () => {
  loading.value = true
  
  try {
    let result
    if (isEditing.value) {
      result = await servicesStore.updateService(Number(route.params.id), form.value)
    } else {
      result = await servicesStore.createService(form.value)
    }
    
    if (result.success) {
      router.push('/services')
    } else {
      alert(result.error)
    }
  } finally {
    loading.value = false
  }
}

// Lifecycle
onMounted(() => {
  loadService()
})
</script>
