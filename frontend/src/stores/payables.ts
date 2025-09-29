import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export interface PayablePayment {
  id: number
  supplier_name: string
  supplier_document: string
  order_id?: number
  service_order_id?: number
  original_amount: number
  paid_amount: number
  remaining_amount: number
  due_date: string
  status: 'pendente' | 'pago' | 'vencido' | 'parcial'
  payment_history: Array<{
    amount: number
    payment_date: string
    payment_method: string
    notes?: string
  }>
  notes?: string
  category?: string
  description?: string
  created_at: string
  updated_at: string
}

export interface PayableStatistics {
  total_pending: number
  total_paid: number
  total_overdue: number
  total_partial: number
  amount_pending: number
  amount_paid: number
  amount_overdue: number
  amount_partial: number
}

export const usePayablesStore = defineStore('payables', () => {
  const payables = ref<PayablePayment[]>([])
  const statistics = ref<PayableStatistics>({
    total_pending: 0,
    total_paid: 0,
    total_overdue: 0,
    total_partial: 0,
    amount_pending: 0,
    amount_paid: 0,
    amount_overdue: 0,
    amount_partial: 0
  })
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Computed
  const pendingPayables = computed(() => 
    payables.value.filter(p => p.status === 'pendente')
  )

  const overduePayables = computed(() => 
    payables.value.filter(p => p.status === 'vencido')
  )

  const paidPayables = computed(() => 
    payables.value.filter(p => p.status === 'pago')
  )

  const partialPayables = computed(() => 
    payables.value.filter(p => p.status === 'parcial')
  )

  // Actions
  async function fetchPayables(filters: any = {}) {
    loading.value = true
    error.value = null
    
    try {
      const response = await api.get('/payables', { params: filters })
      payables.value = response.data.data
      return response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar contas a pagar'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchStatistics() {
    try {
      const response = await api.get('/payables/statistics')
      statistics.value = response.data.data
      return response.data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar estatísticas'
      throw err
    }
  }

  async function createPayable(payableData: Partial<PayablePayment>) {
    loading.value = true
    error.value = null
    
    try {
      const response = await api.post('/payables', payableData)
      payables.value.unshift(response.data.data)
      return response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao criar conta a pagar'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updatePayable(id: number, payableData: Partial<PayablePayment>) {
    loading.value = true
    error.value = null
    
    try {
      const response = await api.put(`/payables/${id}`, payableData)
      const index = payables.value.findIndex(p => p.id === id)
      if (index !== -1) {
        payables.value[index] = response.data.data
      }
      return response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao atualizar conta a pagar'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deletePayable(id: number) {
    loading.value = true
    error.value = null
    
    try {
      await api.delete(`/payables/${id}`)
      payables.value = payables.value.filter(p => p.id !== id)
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao excluir conta a pagar'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function addPayment(id: number, paymentData: {
    amount: number
    payment_date: string
    payment_method: string
    notes?: string
  }) {
    loading.value = true
    error.value = null
    
    try {
      // Mapear payment_method para method que o backend espera
      const backendPaymentData = {
        amount: paymentData.amount,
        method: paymentData.payment_method,
        notes: paymentData.notes
      }
      
      const response = await api.post(`/payables/${id}/payment`, backendPaymentData)
      const index = payables.value.findIndex(p => p.id === id)
      if (index !== -1) {
        payables.value[index] = response.data.data
      }
      return response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao adicionar pagamento'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchBySupplier(supplierName: string) {
    loading.value = true
    error.value = null
    
    try {
      const response = await api.get(`/payables/supplier/${supplierName}`)
      return response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar contas do fornecedor'
      throw err
    } finally {
      loading.value = false
    }
  }

  function clearError() {
    error.value = null
  }

  function $reset() {
    payables.value = []
    statistics.value = {
      total_pending: 0,
      total_paid: 0,
      total_overdue: 0,
      total_partial: 0,
      amount_pending: 0,
      amount_paid: 0,
      amount_overdue: 0,
      amount_partial: 0
    }
    loading.value = false
    error.value = null
  }

  return {
    // State
    payables,
    statistics,
    loading,
    error,
    
    // Computed
    pendingPayables,
    overduePayables,
    paidPayables,
    partialPayables,
    
    // Actions
    fetchPayables,
    fetchStatistics,
    createPayable,
    updatePayable,
    deletePayable,
    addPayment,
    fetchBySupplier,
    clearError,
    $reset
  }
})