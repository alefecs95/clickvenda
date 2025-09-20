import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export interface ReceivablePayment {
  id: number
  order_id?: number
  service_order_id?: number
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
  order?: any
  serviceOrder?: any
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

      // Buscar tanto vendas quanto OS a prazo
      const [salesResponse, serviceOrdersResponse] = await Promise.all([
        api.get(`/receivables?${params.toString()}`),
        api.get(`/service-orders?${params.toString()}&payment_method=credit&per_page=100`)
      ])
      
      let allReceivables: ReceivablePayment[] = []

      // Processar vendas a prazo
      if (salesResponse.data.success) {
        allReceivables = [...salesResponse.data.data]
      }

      // Processar OS a prazo que NÃO têm ReceivablePayment criado
      if (serviceOrdersResponse.data.success) {
        const serviceOrders = serviceOrdersResponse.data.data.data || serviceOrdersResponse.data.data || []
        
        // Filtrar apenas OS a prazo que NÃO têm ReceivablePayment
        const serviceOrdersWithoutReceivable = serviceOrders.filter((so: any) => {
          // Verificar se já existe ReceivablePayment para esta OS
          const hasReceivablePayment = allReceivables.some(r => r.service_order_id === so.id)
          return !hasReceivablePayment && so.payment_method === 'credit'
        })
        
        const serviceOrderReceivables = serviceOrdersWithoutReceivable.map((so: any) => ({
          id: so.id + 100000, // ID único para OS (evitar conflito com vendas)
          service_order_id: so.id,
          customer_id: so.customer_id,
          total_receivable_amount: so.final_amount,
          paid_amount: so.total_paid || 0,
          remaining_amount: so.remaining_amount || so.final_amount,
          due_date: so.expected_delivery_date || so.opening_date,
          status: so.is_fully_paid ? 'paid' : (so.total_paid > 0 ? 'partial' : 'pending'),
          payment_history: so.payments?.map((p: any) => ({
            amount: p.amount,
            method: p.payment_method,
            date: p.created_at,
            notes: p.notes
          })) || [],
          notes: `OS #${so.order_number} - ${so.problem_description}`,
          serviceOrder: so,
          customer: so.customer,
          created_at: so.created_at,
          updated_at: so.updated_at
        }))
        
        allReceivables = [...allReceivables, ...serviceOrderReceivables]
      }

      // Ordenar por data de criação (mais recente primeiro)
      allReceivables.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())

      receivables.value = allReceivables
      return {
        success: true,
        receivables: allReceivables
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
