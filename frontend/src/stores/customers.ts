import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export interface Customer {
  id: number
  name: string
  email?: string
  phone?: string
  cpf_cnpj?: string
  address?: string
  active: boolean
  credit_limit: number
  credit_used: number
  credit_limit_updated_at?: string
  credit_notes?: string
  credit_info?: {
    credit_limit: number
    credit_used: number
    credit_available: number
    credit_percentage_used: number
    has_credit_limit: boolean
    credit_status: string
    credit_notes?: string
    credit_limit_updated_at?: string
  }
  created_at: string
  updated_at: string
  orders?: any[]
}

export interface CustomerFilters {
  search?: string
  active?: boolean
  sort_by?: string
  sort_order?: 'asc' | 'desc'
  per_page?: number
  page?: string | number
}

export interface PaginationInfo {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export const useCustomersStore = defineStore('customers', () => {
  const customers = ref<Customer[]>([])
  const currentCustomer = ref<Customer | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const pagination = ref<PaginationInfo | null>(null)

  // Computed
  const activeCustomers = computed(() => 
    customers.value.filter(customer => customer.active)
  )

  const totalCustomers = computed(() => pagination.value?.total || 0)

  // Actions
  const fetchCustomers = async (filters: CustomerFilters = {}) => {
    try {
      loading.value = true
      error.value = null

      const params = new URLSearchParams()
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          params.append(key, value.toString())
        }
      })

      const response = await api.get(`/customers?${params.toString()}`)
      
      if (response.data.success) {
        customers.value = response.data.data
        pagination.value = response.data.pagination
      } else {
        throw new Error(response.data.message || 'Erro ao carregar clientes')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao carregar clientes'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchCustomer = async (id: number) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.get(`/customers/${id}`)
      
      if (response.data.success) {
        currentCustomer.value = response.data.data
        return response.data.data
      } else {
        throw new Error(response.data.message || 'Erro ao carregar cliente')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao carregar cliente'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createCustomer = async (customerData: Partial<Customer>) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.post('/customers', customerData)
      
      if (response.data.success) {
        const newCustomer = response.data.data
        customers.value.unshift(newCustomer)
        if (pagination.value) {
          pagination.value.total += 1
        }
        return newCustomer
      } else {
        throw new Error(response.data.message || 'Erro ao criar cliente')
      }
    } catch (err: any) {
      if (err.response?.data?.errors) {
        // Formatando erros de validação para exibição
        const validationErrors = Object.values(err.response.data.errors).flat();
        error.value = validationErrors.join(', ');
      } else {
        error.value = err.response?.data?.message || err.message || 'Erro ao criar cliente';
      }
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateCustomer = async (id: number, customerData: Partial<Customer>) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.put(`/customers/${id}`, customerData)
      
      if (response.data.success) {
        const updatedCustomer = response.data.data
        
        // Atualizar na lista
        const index = customers.value.findIndex(c => c.id === id)
        if (index !== -1) {
          customers.value[index] = updatedCustomer
        }

        // Atualizar cliente atual se for o mesmo
        if (currentCustomer.value?.id === id) {
          currentCustomer.value = updatedCustomer
        }

        return updatedCustomer
      } else {
        throw new Error(response.data.message || 'Erro ao atualizar cliente')
      }
    } catch (err: any) {
      if (err.response?.data?.errors) {
        // Formatando erros de validação para exibição
        const validationErrors = Object.values(err.response.data.errors).flat();
        error.value = validationErrors.join(', ');
      } else {
        error.value = err.response?.data?.message || err.message || 'Erro ao atualizar cliente';
      }
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteCustomer = async (id: number) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.delete(`/customers/${id}`)
      
      if (response.data.success) {
        // Remover da lista
        customers.value = customers.value.filter(c => c.id !== id)
        
        // Limpar cliente atual se for o mesmo
        if (currentCustomer.value?.id === id) {
          currentCustomer.value = null
        }

        if (pagination.value) {
          pagination.value.total -= 1
        }
      } else {
        throw new Error(response.data.message || 'Erro ao excluir cliente')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao excluir cliente'
      throw err
    } finally {
      loading.value = false
    }
  }

  const searchCustomers = async (query: string) => {
    try {
      if (query.length < 2) {
        return []
      }

      const response = await api.get(`/customers/search?q=${encodeURIComponent(query)}`)
      
      if (response.data.success) {
        return response.data.data
      } else {
        throw new Error(response.data.message || 'Erro na busca')
      }
    } catch (err: any) {
      console.error('Erro na busca de clientes:', err)
      return []
    }
  }

  const clearError = () => {
    error.value = null
  }

  const clearCurrentCustomer = () => {
    currentCustomer.value = null
  }

  // Métodos para gestão de crédito
  const updateCreditLimit = async (customerId: number, creditLimit: number, creditNotes?: string) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.put(`/customers/${customerId}/credit-limit`, {
        credit_limit: creditLimit,
        credit_notes: creditNotes
      })

      if (response.data.success) {
        // Atualizar o cliente na lista
        const index = customers.value.findIndex(c => c.id === customerId)
        if (index !== -1) {
          customers.value[index] = response.data.data.customer
        }
        
        // Atualizar cliente atual se for o mesmo
        if (currentCustomer.value?.id === customerId) {
          currentCustomer.value = response.data.data.customer
        }

        return { success: true, data: response.data.data }
      } else {
        throw new Error(response.data.message || 'Erro ao atualizar limite')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao atualizar limite de crédito'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  const adjustCredit = async (customerId: number, amount: number, type: 'payment' | 'adjustment' | 'charge', notes?: string) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.post(`/customers/${customerId}/adjust-credit`, {
        amount,
        type,
        notes
      })

      if (response.data.success) {
        // Atualizar o cliente na lista
        const index = customers.value.findIndex(c => c.id === customerId)
        if (index !== -1) {
          customers.value[index] = response.data.data.customer
        }
        
        // Atualizar cliente atual se for o mesmo
        if (currentCustomer.value?.id === customerId) {
          currentCustomer.value = response.data.data.customer
        }

        return { success: true, data: response.data.data }
      } else {
        throw new Error(response.data.message || 'Erro ao ajustar crédito')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao ajustar crédito'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  const fetchCustomersWithCredit = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await api.get('/customers/with-credit-limit')

      if (response.data.success) {
        return { success: true, data: response.data.data }
      } else {
        throw new Error(response.data.message || 'Erro ao buscar clientes com crédito')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao buscar clientes com crédito'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  const getCreditInfo = async (customerId: number) => {
    loading.value = true
    error.value = null

    try {
      const response = await api.get(`/customers/${customerId}/credit-info`)

      if (response.data.success) {
        return { success: true, data: response.data.data }
      } else {
        throw new Error(response.data.message || 'Erro ao buscar informações de crédito')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao buscar informações de crédito'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  return {
    // State
    customers,
    currentCustomer,
    loading,
    error,
    pagination,

    // Computed
    activeCustomers,
    totalCustomers,

    // Actions
    fetchCustomers,
    fetchCustomer,
    createCustomer,
    updateCustomer,
    deleteCustomer,
    searchCustomers,
    clearError,
    clearCurrentCustomer,
    
    // Credit management
    updateCreditLimit,
    adjustCredit,
    fetchCustomersWithCredit,
    getCreditInfo
  }
})
