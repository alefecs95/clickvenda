<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
          Novo Fornecedor
        </h3>
        
        <form @submit.prevent="saveSupplier" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Nome *</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              placeholder="Nome do fornecedor"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">CPF/CNPJ *</label>
            <input
              v-model="form.cpf_cnpj"
              type="text"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              placeholder="000.000.000-00 ou 00.000.000/0000-00"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input
              v-model="form.email"
              type="email"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              placeholder="email@exemplo.com"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Telefone</label>
            <input
              v-model="form.phone"
              type="text"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              placeholder="(00) 00000-0000"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Endereço</label>
            <input
              v-model="form.address"
              type="text"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              placeholder="Rua, número, bairro"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-gray-700">Cidade</label>
              <input
                v-model="form.city"
                type="text"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                placeholder="Cidade"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Estado</label>
              <input
                v-model="form.state"
                type="text"
                maxlength="2"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                placeholder="UF"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">CEP</label>
            <input
              v-model="form.zip_code"
              type="text"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              placeholder="00000-000"
            />
          </div>

          <div class="flex items-center">
            <input
              v-model="form.active"
              type="checkbox"
              id="active"
              class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
            />
            <label for="active" class="ml-2 block text-sm text-gray-900">
              Fornecedor ativo
            </label>
          </div>

          <div v-if="error" class="text-red-600 text-sm">
            {{ error }}
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-md disabled:opacity-50"
            >
              <span v-if="loading">Salvando...</span>
              <span v-else>Salvar</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { useSuppliersStore } from '@/stores/suppliers'

interface Props {
  show: boolean
}

interface Emits {
  (e: 'close'): void
  (e: 'saved', supplier: any): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const suppliersStore = useSuppliersStore()

const form = ref({
  name: '',
  cpf_cnpj: '',
  email: '',
  phone: '',
  address: '',
  city: '',
  state: '',
  zip_code: '',
  active: true
})

const loading = ref(false)
const error = ref<string | null>(null)

const resetForm = () => {
  form.value = {
    name: '',
    cpf_cnpj: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    state: '',
    zip_code: '',
    active: true
  }
  error.value = null
}

const closeModal = () => {
  resetForm()
  emit('close')
}

const saveSupplier = async () => {
  loading.value = true
  error.value = null
  
  try {
    const response = await suppliersStore.createSupplier(form.value)
    emit('saved', response.data)
    closeModal()
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Erro ao salvar fornecedor'
  } finally {
    loading.value = false
  }
}

// Reset form when modal is closed
watch(() => props.show, (newValue) => {
  if (!newValue) {
    resetForm()
  }
})
</script>