import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export interface OrderItem {
  id: number
  order_id: number
  product_id: number
  quantity: number
  unit_price: number
  total_price: number
  product?: Product
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

export interface Customer {
  id: number
  name: string
  email?: string
  phone?: string
  cpf_cnpj?: string
}

export interface User {
  id: number
  name: string
  email: string
}

export interface Order {
  id: number
  customer_id: number
  user_id: number
  order_number: string
  total_amount: number
  discount_amount: number
  final_amount: number
  payment_method: 'money' | 'card' | 'pix' | 'credit'
  status: 'pending' | 'completed' | 'cancelled'
  notes?: string
  created_at: string
  updated_at: string
  customer?: Customer
  user?: User
  items?: OrderItem[]
}

export interface OrderFilters {
  status?: string
  customer_id?: number
  date_from?: string
  date_to?: string
  search?: string
  sort_by?: string
  sort_order?: 'asc' | 'desc'
  per_page?: number
  page?: string
}

export interface OrderStatistics {
  total_orders: number
  total_revenue: number
  pending_orders: number
  completed_orders: number
  cancelled_orders: number
  average_order_value: number
}

export interface PaginationInfo {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export const useOrdersStore = defineStore('orders', () => {
  const orders = ref<Order[]>([])
  const currentOrder = ref<Order | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const pagination = ref<PaginationInfo | null>(null)
  const statistics = ref<OrderStatistics | null>(null)

  // Computed
  const pendingOrders = computed(() => 
    orders.value.filter(order => order.status === 'pending')
  )

  const completedOrders = computed(() => 
    orders.value.filter(order => order.status === 'completed')
  )

  const cancelledOrders = computed(() => 
    orders.value.filter(order => order.status === 'cancelled')
  )

  const totalOrders = computed(() => pagination.value?.total || 0)

  const totalRevenue = computed(() => 
    completedOrders.value.reduce((sum, order) => sum + order.final_amount, 0)
  )

  // Actions
  const fetchOrders = async (filters: OrderFilters = {}) => {
    try {
      loading.value = true
      error.value = null

      const params = new URLSearchParams()
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          params.append(key, value.toString())
        }
      })

      const response = await api.get(`/orders?${params.toString()}`)
      
      if (response.data.success) {
        orders.value = response.data.data
        pagination.value = response.data.pagination
        return {
          success: true,
          orders: response.data.data,
          pagination: response.data.pagination
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao carregar pedidos'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao carregar pedidos'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao carregar pedidos'
      }
    } finally {
      loading.value = false
    }
  }

  const fetchOrder = async (id: number) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.get(`/orders/${id}`)
      
      if (response.data.success) {
        currentOrder.value = response.data.data
        return response.data.data
      } else {
        throw new Error(response.data.message || 'Erro ao carregar pedido')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao carregar pedido'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchOrderById = async (id: string) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.get(`/orders/${id}`)
      
      if (response.data.success) {
        return response.data.data
      } else {
        throw new Error(response.data.message || 'Erro ao carregar pedido')
      }
    } catch (err: any) {
      const errorMessage = err.response?.data?.message || err.message || 'Erro ao carregar pedido'
      error.value = errorMessage
      throw new Error(errorMessage)
    } finally {
      loading.value = false
    }
  }

  const createOrder = async (orderData: {
    customer_id: number
    items: Array<{
      product_id: number
      quantity: number
      unit_price: number
    }>
    discount_amount?: number
    payment_method: 'money' | 'card' | 'pix' | 'credit' | 'multiple'
    payment_methods?: Array<{
      method: string
      amount: number
    }>
    notes?: string
    status?: 'pending' | 'completed'
  }) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.post('/orders', orderData)
      
      if (response.data.success) {
        const newOrder = response.data.data
        orders.value.unshift(newOrder)
        if (pagination.value) {
          pagination.value.total += 1
        }
        return {
          success: true,
          data: newOrder
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao criar pedido'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao criar pedido'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao criar pedido'
      }
    } finally {
      loading.value = false
    }
  }

  const updateOrder = async (id: number, orderData: Partial<Order>) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.put(`/orders/${id}`, orderData)
      
      if (response.data.success) {
        const updatedOrder = response.data.data
        
        // Atualizar na lista
        const index = orders.value.findIndex(o => o.id === id)
        if (index !== -1) {
          orders.value[index] = updatedOrder
        }

        // Atualizar pedido atual se for o mesmo
        if (currentOrder.value?.id === id) {
          currentOrder.value = updatedOrder
        }

        return updatedOrder
      } else {
        throw new Error(response.data.message || 'Erro ao atualizar pedido')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao atualizar pedido'
      throw err
    } finally {
      loading.value = false
    }
  }

  const cancelOrder = async (id: number) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.post(`/orders/${id}/cancel`)
      
      if (response.data.success) {
        // Atualizar na lista
        const index = orders.value.findIndex(o => o.id === id)
        if (index !== -1) {
          orders.value[index].status = 'cancelled'
        }

        // Atualizar pedido atual se for o mesmo
        if (currentOrder.value?.id === id) {
          currentOrder.value.status = 'cancelled'
        }

        return true
      } else {
        throw new Error(response.data.message || 'Erro ao cancelar pedido')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao cancelar pedido'
      throw err
    } finally {
      loading.value = false
    }
  }

  const completeOrder = async (id: number) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.post(`/orders/${id}/complete`)
      
      if (response.data.success) {
        // Atualizar na lista
        const index = orders.value.findIndex(o => o.id === id)
        if (index !== -1) {
          orders.value[index].status = 'completed'
        }

        // Atualizar pedido atual se for o mesmo
        if (currentOrder.value?.id === id) {
          currentOrder.value.status = 'completed'
        }

        return true
      } else {
        throw new Error(response.data.message || 'Erro ao concluir pedido')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao concluir pedido'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteOrder = async (id: number) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.delete(`/orders/${id}`)
      
      if (response.data.success) {
        // Remover da lista
        orders.value = orders.value.filter(o => o.id !== id)
        
        // Limpar pedido atual se for o mesmo
        if (currentOrder.value?.id === id) {
          currentOrder.value = null
        }

        if (pagination.value) {
          pagination.value.total -= 1
        }
      } else {
        throw new Error(response.data.message || 'Erro ao excluir pedido')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao excluir pedido'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchStatistics = async (dateFrom?: string, dateTo?: string) => {
    try {
      const params = new URLSearchParams()
      if (dateFrom) params.append('date_from', dateFrom)
      if (dateTo) params.append('date_to', dateTo)

      // Debug: Log dos parâmetros sendo enviados
      console.log('Fetching statistics with params:', {
        dateFrom,
        dateTo,
        url: `/orders/statistics?${params.toString()}`
      })

      const response = await api.get(`/orders/statistics?${params.toString()}`)
      
      // Debug: Log da resposta
      console.log('Statistics response:', response.data)
      
      if (response.data.success) {
        statistics.value = response.data.data
        return response.data.data
      } else {
        throw new Error(response.data.message || 'Erro ao carregar estatísticas')
      }
    } catch (err: any) {
      console.error('Erro ao carregar estatísticas:', err)
      throw err
    }
  }

  const clearError = () => {
    error.value = null
  }

  const clearCurrentOrder = () => {
    currentOrder.value = null
  }

  const getStatusText = (status: string) => {
    const statusMap = {
      pending: 'Pendente',
      completed: 'Concluído',
      cancelled: 'Cancelado'
    }
    return statusMap[status as keyof typeof statusMap] || status
  }

  const getPaymentMethodText = (method: string) => {
    const methodMap = {
      money: 'Dinheiro',
      card: 'Cartão',
      pix: 'PIX'
    }
    return methodMap[method as keyof typeof methodMap] || method
  }

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    }).format(price)
  }

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  }

  return {
    // State
    orders,
    currentOrder,
    loading,
    error,
    pagination,
    statistics,

    // Computed
    pendingOrders,
    completedOrders,
    cancelledOrders,
    totalOrders,
    totalRevenue,

    // Actions
    fetchOrders,
    fetchOrder,
    fetchOrderById,
    createOrder,
    updateOrder,
    cancelOrder,
    completeOrder,
    deleteOrder,
    fetchStatistics,
    clearError,
    clearCurrentOrder,

    // Utils
    getStatusText,
    getPaymentMethodText,
    formatPrice,
    formatDate
  }
})
