import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export interface ReceivablePayment {
  id: number
  order_id: number
  customer_id: number
  total_receivable_amount: number
  paid_amount: number
  remaining_amount: number
  due_date: string
  status: 'pending' | 'partial' | 'paid' | 'overdue'
  payment_history: Array<{
    amount: number
    method: string
    date: string
    notes?: string
  }>
  notes?: string
  order: any
  customer: any
  created_at: string
  updated_at: string
}

export interface ReceivableFilters {
  customer_id?: number
  status?: string
  due_date_from?: string
  due_date_to?: string
  per_page?: number
}

export const useReceivablesStore = defineStore('receivables', () => {
  const receivables = ref<ReceivablePayment[]>([])
  const statistics = ref<any>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Computed properties
  const totalPending = computed(() => {
    return receivables.value
      .filter(r => ['pending', 'partial'].includes(r.status))
      .reduce((sum, r) => sum + r.remaining_amount, 0)
  })

  const totalOverdue = computed(() => {
    return receivables.value
      .filter(r => r.status === 'overdue')
      .reduce((sum, r) => sum + r.remaining_amount, 0)
  })

  const totalPaid = computed(() => {
    return receivables.value
      .filter(r => r.status === 'paid')
      .reduce((sum, r) => sum + r.total_receivable_amount, 0)
  })

  // Actions
  const fetchReceivables = async (filters: ReceivableFilters = {}) => {
    try {
      loading.value = true
      error.value = null

      const params = new URLSearchParams()
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          params.append(key, value.toString())
        }
      })

      const response = await api.get(`/receivables?${params.toString()}`)
      
      if (response.data.success) {
        receivables.value = response.data.data
        return {
          success: true,
          receivables: response.data.data
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao carregar contas a receber'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao carregar contas a receber'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao carregar contas a receber'
      }
    } finally {
      loading.value = false
    }
  }

  const fetchCustomerReceivables = async (customerId: number) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.get(`/receivables/customer/${customerId}`)
      
      if (response.data.success) {
        return {
          success: true,
          receivables: response.data.data
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao carregar contas do cliente'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao carregar contas do cliente'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao carregar contas do cliente'
      }
    } finally {
      loading.value = false
    }
  }

  const addPayment = async (receivableId: number, paymentData: {
    amount: number
    method: string
    notes?: string
  }) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.post(`/receivables/${receivableId}/payment`, paymentData)
      
      if (response.data.success) {
        // Atualizar a conta a receber na lista
        const index = receivables.value.findIndex(r => r.id === receivableId)
        if (index !== -1) {
          receivables.value[index] = response.data.data
        }

        return {
          success: true,
          data: response.data.data
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao registrar pagamento'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao registrar pagamento'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao registrar pagamento'
      }
    } finally {
      loading.value = false
    }
  }

  const fetchStatistics = async () => {
    try {
      const response = await api.get('/receivables/statistics')
      
      if (response.data.success) {
        statistics.value = response.data.data
        return {
          success: true,
          data: response.data.data
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao carregar estatísticas'
        }
      }
    } catch (err: any) {
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao carregar estatísticas'
      }
    }
  }

  const clearError = () => {
    error.value = null
  }

  return {
    // State
    receivables,
    statistics,
    loading,
    error,

    // Computed
    totalPending,
    totalOverdue,
    totalPaid,

    // Actions
    fetchReceivables,
    fetchCustomerReceivables,
    addPayment,
    fetchStatistics,
    clearError
  }
})
