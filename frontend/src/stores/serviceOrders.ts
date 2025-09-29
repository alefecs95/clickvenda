import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export interface ServiceOrderItem {
  id: number
  service_order_id: number
  item_type: 'product' | 'service'
  product_id?: number
  service_id?: number
  description: string
  quantity: number
  unit_price: number
  total_price: number
  stock_removed: boolean
  stock_removed_at?: string
  observations?: string
  product?: Product
  service?: Service
}

export interface Product {
  id: number
  name: string
  price: number
  stock_quantity: number
  sku: string
  barcode?: string
  active: boolean
}

export interface Service {
  id: number
  name: string
  description?: string
  price: number
  category?: string
  estimated_duration?: number
  active: boolean
  notes?: string
}

export interface Customer {
  id: number
  name: string
  email?: string
  phone?: string
  cpf_cnpj?: string
}

export interface Vehicle {
  id: number
  customer_id: number
  plate?: string
  model?: string
  brand?: string
  year?: string
  serial_number?: string
  chassis_number?: string
  engine_number?: string
  type: 'veiculo' | 'equipamento' | 'maquina' | 'outro'
  color?: string
  fuel_type?: string
  mileage?: number
  observations?: string
  active: boolean
  customer?: Customer
}

export interface User {
  id: number
  name: string
  username: string
}

export interface ServiceOrder {
  id: number
  order_number: string
  customer_id: number
  vehicle_id?: number
  technical_responsible_id: number
  created_by: number
  opening_date: string
  expected_delivery_date?: string
  completion_date?: string
  status: 'aberta' | 'em_andamento' | 'aguardando_aprovacao' | 'concluida' | 'cancelada'
  problem_description: string
  diagnosis?: string
  internal_observations?: string
  customer_observations?: string
  total_products: number
  total_services: number
  total_amount: number
  discount_amount: number
  final_amount: number
  billing_type: 'avista' | 'aprazo' | 'orcamento'
  order_id?: number
  customer_approved: boolean
  approved_at?: string
  approval_notes?: string
  attachments?: any[]
  whatsapp_notification_sent: boolean
  whatsapp_sent_at?: string
  created_at: string
  updated_at: string
  customer?: Customer
  vehicle?: Vehicle
  technical_responsible?: User
  created_by_user?: User
  items?: ServiceOrderItem[]
  order?: any
  receivable_payment?: any
  warranty_products_days?: number
  warranty_services_days?: number
}

export interface ServiceOrderFilters {
  status?: string
  customer_id?: number
  technical_responsible_id?: number
  opening_date_from?: string
  opening_date_to?: string
  search?: string
  sort_by?: string
  sort_order?: 'asc' | 'desc'
  per_page?: number
}

export interface ServiceOrderStatistics {
  total: number
  abertas: number
  em_andamento: number
  aguardando_aprovacao: number
  concluidas: number
  canceladas: number
  receita_total: number
  receita_mes: number
}

export const useServiceOrdersStore = defineStore('serviceOrders', () => {
  const serviceOrders = ref<ServiceOrder[]>([])
  const statistics = ref<ServiceOrderStatistics | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Computed properties
  const statusLabels = computed(() => ({
    'aberta': 'Aberta',
    'em_andamento': 'Em Andamento',
    'aguardando_aprovacao': 'Aguardando Aprovação',
    'concluida': 'Concluída',
    'cancelada': 'Cancelada'
  }))

  const billingTypeLabels = computed(() => ({
    'avista': 'À Vista',
    'aprazo': 'A Prazo',
    'orcamento': 'Orçamento'
  }))

  const statusColors = computed(() => ({
    'aberta': 'blue',
    'em_andamento': 'yellow',
    'aguardando_aprovacao': 'orange',
    'concluida': 'green',
    'cancelada': 'red'
  }))

  // Actions
  const fetchServiceOrders = async (filters: ServiceOrderFilters = {}) => {
    try {
      loading.value = true
      error.value = null

      const params = new URLSearchParams()
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          params.append(key, value.toString())
        }
      })

      const response = await api.get(`/service-orders?${params.toString()}`)
      
      if (response.data.success) {
        serviceOrders.value = response.data.data.data || response.data.data
        return {
          success: true,
          serviceOrders: response.data.data.data || response.data.data,
          pagination: response.data.data.meta || null
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao carregar ordens de serviço'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao carregar ordens de serviço'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao carregar ordens de serviço'
      }
    } finally {
      loading.value = false
    }
  }

  const fetchServiceOrder = async (id: number) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.get(`/service-orders/${id}`)
      
      if (response.data.success) {
        return {
          success: true,
          serviceOrder: response.data.data
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao carregar ordem de serviço'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao carregar ordem de serviço'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao carregar ordem de serviço'
      }
    } finally {
      loading.value = false
    }
  }

  const createServiceOrder = async (serviceOrderData: Partial<ServiceOrder>) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.post('/service-orders/', serviceOrderData)
      
      if (response.data.success) {
        // Adicionar à lista local
        serviceOrders.value.unshift(response.data.data)
        return {
          success: true,
          serviceOrder: response.data.data,
          message: response.data.message || 'Ordem de serviço criada com sucesso'
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao criar ordem de serviço'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao criar ordem de serviço'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao criar ordem de serviço'
      }
    } finally {
      loading.value = false
    }
  }

  const updateServiceOrder = async (id: number, serviceOrderData: Partial<ServiceOrder>) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.put(`/service-orders/${id}`, serviceOrderData)
      
      if (response.data.success) {
        // Atualizar na lista local
        const index = serviceOrders.value.findIndex(os => os.id === id)
        if (index !== -1) {
          serviceOrders.value[index] = response.data.data
        }
        return {
          success: true,
          serviceOrder: response.data.data,
          message: response.data.message || 'Ordem de serviço atualizada com sucesso'
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao atualizar ordem de serviço'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao atualizar ordem de serviço'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao atualizar ordem de serviço'
      }
    } finally {
      loading.value = false
    }
  }

  const deleteServiceOrder = async (id: number) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.delete(`/service-orders/${id}`)
      
      if (response.data.success) {
        // Remover da lista local
        serviceOrders.value = serviceOrders.value.filter(os => os.id !== id)
        return {
          success: true,
          message: response.data.message || 'Ordem de serviço cancelada com sucesso'
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao cancelar ordem de serviço'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao cancelar ordem de serviço'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao cancelar ordem de serviço'
      }
    } finally {
      loading.value = false
    }
  }

  const approveServiceOrder = async (id: number, approvalNotes?: string) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.post(`/service-orders/${id}/approve`, {
        approval_notes: approvalNotes
      })
      
      if (response.data.success) {
        // Atualizar na lista local
        const index = serviceOrders.value.findIndex(os => os.id === id)
        if (index !== -1) {
          serviceOrders.value[index] = response.data.data
        }
        return {
          success: true,
          serviceOrder: response.data.data,
          message: response.data.message || 'Ordem de serviço aprovada com sucesso'
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao aprovar ordem de serviço'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao aprovar ordem de serviço'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao aprovar ordem de serviço'
      }
    } finally {
      loading.value = false
    }
  }

  const approveCustomerServiceOrder = async (id: number, approvalNotes?: string) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.post(`/service-orders/${id}/approve-customer`, {
        approval_notes: approvalNotes
      })
      
      if (response.data.success) {
        // Atualizar na lista local
        const index = serviceOrders.value.findIndex(os => os.id === id)
        if (index !== -1) {
          serviceOrders.value[index] = response.data.data
        }
        return {
          success: true,
          serviceOrder: response.data.data,
          message: response.data.message || 'Orçamento aprovado pelo cliente com sucesso'
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao aprovar orçamento pelo cliente'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao aprovar orçamento pelo cliente'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao aprovar orçamento pelo cliente'
      }
    } finally {
      loading.value = false
    }
  }

  const completeServiceOrder = async (id: number) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.post(`/service-orders/${id}/complete`)
      
      if (response.data.success) {
        // Atualizar na lista local
        const index = serviceOrders.value.findIndex(os => os.id === id)
        if (index !== -1) {
          serviceOrders.value[index] = response.data.data
        }
        return {
          success: true,
          serviceOrder: response.data.data,
          message: response.data.message || 'Ordem de serviço concluída com sucesso'
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao concluir ordem de serviço'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao concluir ordem de serviço'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao concluir ordem de serviço'
      }
    } finally {
      loading.value = false
    }
  }

  const fetchStatistics = async () => {
    try {
      loading.value = true
      error.value = null

      const response = await api.get('/service-orders/statistics')
      
      if (response.data.success) {
        statistics.value = response.data.data
        return {
          success: true,
          statistics: response.data.data
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao carregar estatísticas'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao carregar estatísticas'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao carregar estatísticas'
      }
    } finally {
      loading.value = false
    }
  }

  const getStatusLabel = (status: string) => {
    return statusLabels.value[status as keyof typeof statusLabels.value] || status
  }

  const getBillingTypeLabel = (billingType: string) => {
    return billingTypeLabels.value[billingType as keyof typeof billingTypeLabels.value] || billingType
  }

  const getStatusColor = (status: string) => {
    return statusColors.value[status as keyof typeof statusColors.value] || 'gray'
  }

  return {
    // State
    serviceOrders,
    statistics,
    loading,
    error,
    
    // Computed
    statusLabels,
    billingTypeLabels,
    statusColors,
    
    // Actions
    fetchServiceOrders,
    fetchServiceOrder,
    createServiceOrder,
    updateServiceOrder,
    deleteServiceOrder,
    approveServiceOrder,
    approveCustomerServiceOrder,
    completeServiceOrder,
    fetchStatistics,
    getStatusLabel,
    getBillingTypeLabel,
    getStatusColor
  }
})
