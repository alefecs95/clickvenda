import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export interface Service {
  id: number
  name: string
  description?: string
  price: number
  category?: string
  estimated_duration?: number
  active: boolean
  notes?: string
  created_at: string
  updated_at: string
}

export interface ServiceFilters {
  active?: boolean
  category?: string
  search?: string
  sort_by?: string
  sort_order?: 'asc' | 'desc'
  per_page?: number
}

export interface ServiceStatistics {
  total: number
  active: number
  inactive: number
  categories: number
  average_price: number
  most_used: Array<{
    id: number
    name: string
    usage_count: number
  }>
}

export const useServicesStore = defineStore('services', () => {
  const services = ref<Service[]>([])
  const activeServices = ref<Service[]>([])
  const categories = ref<string[]>([])
  const statistics = ref<ServiceStatistics | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Actions
  const fetchServices = async (filters: ServiceFilters = {}) => {
    try {
      loading.value = true
      error.value = null

      const params = new URLSearchParams()
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          params.append(key, value.toString())
        }
      })

      const response = await api.get(`/services?${params.toString()}`)
      
      if (response.data.success) {
        services.value = response.data.data.data || response.data.data
        return {
          success: true,
          services: response.data.data.data || response.data.data,
          pagination: response.data.data.meta || null
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao carregar serviços'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao carregar serviços'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao carregar serviços'
      }
    } finally {
      loading.value = false
    }
  }

  const fetchService = async (id: number) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.get(`/services/${id}`)
      
      if (response.data.success) {
        return {
          success: true,
          service: response.data.data
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao carregar serviço'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao carregar serviço'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao carregar serviço'
      }
    } finally {
      loading.value = false
    }
  }

  const createService = async (serviceData: Partial<Service>) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.post('/services', serviceData)
      
      if (response.data.success) {
        // Adicionar à lista local
        services.value.unshift(response.data.data)
        return {
          success: true,
          service: response.data.data,
          message: response.data.message || 'Serviço criado com sucesso'
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao criar serviço'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao criar serviço'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao criar serviço'
      }
    } finally {
      loading.value = false
    }
  }

  const updateService = async (id: number, serviceData: Partial<Service>) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.put(`/services/${id}`, serviceData)
      
      if (response.data.success) {
        // Atualizar na lista local
        const index = services.value.findIndex(service => service.id === id)
        if (index !== -1) {
          services.value[index] = response.data.data
        }
        return {
          success: true,
          service: response.data.data,
          message: response.data.message || 'Serviço atualizado com sucesso'
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao atualizar serviço'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao atualizar serviço'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao atualizar serviço'
      }
    } finally {
      loading.value = false
    }
  }

  const deleteService = async (id: number) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.delete(`/services/${id}`)
      
      if (response.data.success) {
        // Remover da lista local
        services.value = services.value.filter(service => service.id !== id)
        return {
          success: true,
          message: response.data.message || 'Serviço excluído com sucesso'
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao excluir serviço'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao excluir serviço'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao excluir serviço'
      }
    } finally {
      loading.value = false
    }
  }

  const fetchActiveServices = async () => {
    try {
      loading.value = true
      error.value = null

      const response = await api.get('/services/active')
      
      if (response.data.success) {
        activeServices.value = response.data.data
        return {
          success: true,
          services: response.data.data
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao carregar serviços ativos'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao carregar serviços ativos'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao carregar serviços ativos'
      }
    } finally {
      loading.value = false
    }
  }

  const fetchCategories = async () => {
    try {
      loading.value = true
      error.value = null

      const response = await api.get('/services/categories')
      
      if (response.data.success) {
        categories.value = response.data.data
        return {
          success: true,
          categories: response.data.data
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao carregar categorias'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao carregar categorias'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao carregar categorias'
      }
    } finally {
      loading.value = false
    }
  }

  const fetchServicesByCategory = async (category: string) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.get(`/services/by-category?category=${encodeURIComponent(category)}`)
      
      if (response.data.success) {
        return {
          success: true,
          services: response.data.data
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao carregar serviços por categoria'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao carregar serviços por categoria'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao carregar serviços por categoria'
      }
    } finally {
      loading.value = false
    }
  }

  const fetchStatistics = async () => {
    try {
      loading.value = true
      error.value = null

      const response = await api.get('/services/statistics')
      
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

  const getFormattedPrice = (price: number) => {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    }).format(price)
  }

  const getFormattedDuration = (duration?: number) => {
    if (!duration) return 'Não informado'
    
    const hours = Math.floor(duration / 60)
    const minutes = duration % 60
    
    if (hours > 0) {
      return `${hours}h ${minutes}min`
    }
    
    return `${minutes}min`
  }

  return {
    // State
    services,
    activeServices,
    categories,
    statistics,
    loading,
    error,
    
    // Actions
    fetchServices,
    fetchService,
    createService,
    updateService,
    deleteService,
    fetchActiveServices,
    fetchCategories,
    fetchServicesByCategory,
    fetchStatistics,
    getFormattedPrice,
    getFormattedDuration
  }
})
