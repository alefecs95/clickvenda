import { defineStore } from 'pinia'
import api from '@/services/api'

export interface ServiceOrderPayment {
  id: number
  service_order_id: number
  amount: number
  payment_method: string
  payment_reference?: string
  notes?: string
  user_id: number
  paid_at: string
  created_at: string
  updated_at: string
  user?: {
    id: number
    name: string
  }
  payment_method_label?: string
}

export interface PaymentStatistics {
  total_payments: number
  total_amount: number
  by_method: Record<string, {
    count: number
    total: number
  }>
  by_user: Record<string, {
    user_name: string
    count: number
    total: number
  }>
}

export const useServiceOrderPaymentsStore = defineStore('serviceOrderPayments', {
  state: () => ({
    payments: [] as ServiceOrderPayment[],
    loading: false,
    error: null as string | null,
    statistics: null as PaymentStatistics | null
  }),

  getters: {
    totalPaid: (state) => state.payments.reduce((sum, payment) => sum + payment.amount, 0),
    
    paymentsByMethod: (state) => {
      const grouped = state.payments.reduce((acc, payment) => {
        if (!acc[payment.payment_method]) {
          acc[payment.payment_method] = []
        }
        acc[payment.payment_method].push(payment)
        return acc
      }, {} as Record<string, ServiceOrderPayment[]>)
      
      return Object.keys(grouped).map(method => ({
        method,
        label: getPaymentMethodLabel(method),
        count: grouped[method].length,
        total: grouped[method].reduce((sum, p) => sum + p.amount, 0)
      }))
    },

    recentPayments: (state) => state.payments
      .sort((a, b) => new Date(b.paid_at).getTime() - new Date(a.paid_at).getTime())
      .slice(0, 10)
  },

  actions: {
    async fetchPayments(serviceOrderId: number) {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.get(`/service-orders/${serviceOrderId}/payments`)
        this.payments = response.data.payments
        return response.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Erro ao carregar pagamentos'
        throw error
      } finally {
        this.loading = false
      }
    },

    async createPayment(serviceOrderId: number, paymentData: Partial<ServiceOrderPayment>) {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.post(`/service-orders/${serviceOrderId}/payments`, paymentData)
        const newPayment = response.data.payment
        
        // Adicionar o novo pagamento à lista
        this.payments.push(newPayment)
        
        return response.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Erro ao registrar pagamento'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updatePayment(serviceOrderId: number, paymentId: number, paymentData: Partial<ServiceOrderPayment>) {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.put(`/service-orders/${serviceOrderId}/payments/${paymentId}`, paymentData)
        const updatedPayment = response.data.payment
        
        // Atualizar o pagamento na lista
        const index = this.payments.findIndex(p => p.id === paymentId)
        if (index !== -1) {
          this.payments[index] = updatedPayment
        }
        
        return response.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Erro ao atualizar pagamento'
        throw error
      } finally {
        this.loading = false
      }
    },

    async deletePayment(serviceOrderId: number, paymentId: number) {
      this.loading = true
      this.error = null
      
      try {
        await api.delete(`/service-orders/${serviceOrderId}/payments/${paymentId}`)
        
        // Remover o pagamento da lista
        this.payments = this.payments.filter(p => p.id !== paymentId)
        
        return true
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Erro ao excluir pagamento'
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchReports(filters: {
      start_date?: string
      end_date?: string
      payment_method?: string
      user_id?: number
    } = {}) {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.get('/service-order-payments/reports', { params: filters })
        this.statistics = response.data.statistics
        return response.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Erro ao carregar relatórios'
        throw error
      } finally {
        this.loading = false
      }
    },

    clearPayments() {
      this.payments = []
      this.statistics = null
      this.error = null
    }
  }
})

// Função auxiliar para obter label do método de pagamento
function getPaymentMethodLabel(method: string): string {
  const labels: Record<string, string> = {
    'money': 'Dinheiro',
    'card': 'Cartão',
    'pix': 'PIX',
    'credit': 'A Prazo (Crédito)'
  }
  
  return labels[method] || method
}
