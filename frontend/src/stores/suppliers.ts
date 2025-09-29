import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export interface Supplier {
  id: number
  name: string
  cpf_cnpj: string
  email?: string
  phone?: string
  address?: string
  city?: string
  state?: string
  zip_code?: string
  active: boolean
  created_at: string
  updated_at: string
}

export const useSuppliersStore = defineStore('suppliers', () => {
  const suppliers = ref<Supplier[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Computed
  const activeSuppliers = computed(() => 
    suppliers.value.filter(s => s.active)
  )

  // Actions
  const fetchSuppliers = async () => {
    loading.value = true
    error.value = null
    
    try {
      const response = await api.get('/suppliers')
      suppliers.value = response.data.data
      return response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar fornecedores'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createSupplier = async (supplierData: Partial<Supplier>) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await api.post('/suppliers', supplierData)
      suppliers.value.unshift(response.data.data)
      return response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao criar fornecedor'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateSupplier = async (id: number, supplierData: Partial<Supplier>) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await api.put(`/suppliers/${id}`, supplierData)
      const index = suppliers.value.findIndex(s => s.id === id)
      if (index !== -1) {
        suppliers.value[index] = response.data.data
      }
      return response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao atualizar fornecedor'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteSupplier = async (id: number) => {
    loading.value = true
    error.value = null
    
    try {
      await api.delete(`/suppliers/${id}`)
      suppliers.value = suppliers.value.filter(s => s.id !== id)
      return { success: true }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao excluir fornecedor'
      throw err
    } finally {
      loading.value = false
    }
  }

  const clearError = () => {
    error.value = null
  }

  const $reset = () => {
    suppliers.value = []
    loading.value = false
    error.value = null
  }

  return {
    // State
    suppliers,
    loading,
    error,
    
    // Computed
    activeSuppliers,
    
    // Actions
    fetchSuppliers,
    createSupplier,
    updateSupplier,
    deleteSupplier,
    clearError,
    $reset
  }
})