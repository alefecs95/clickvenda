import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export interface Customer {
  id: number
  name: string
  email?: string
  phone?: string
  cpf_cnpj?: string
}

export interface Vehicle {
  id: number
  customer_id?: number // Opcional - apenas para faturamento
  customer_name_at_time?: string // Nome do cliente no momento
  customer_phone_at_time?: string // Telefone do cliente no momento
  customer_email_at_time?: string // Email do cliente no momento
  plate: string // Obrigatório
  model: string // Obrigatório
  make?: string // Marca (compatibilidade com frontend)
  brand?: string // Marca (compatibilidade com backend)
  year?: string
  serial_number?: string
  chassis_number?: string
  engine_number?: string
  type: 'vehicle' | 'equipment' | 'veiculo' | 'equipamento' | 'maquina' | 'outro'
  color?: string
  fuel_type?: string
  mileage?: number
  notes?: string // Observações (compatibilidade com frontend)
  observations?: string // Observações (compatibilidade com backend)
  active: boolean
  created_at: string
  updated_at: string
  customer?: Customer
}

export interface VehicleFilters {
  customer_id?: number
  type?: string
  active?: boolean
  search?: string
  sort_by?: string
  sort_order?: 'asc' | 'desc'
  per_page?: number
}

export interface VehicleStatistics {
  total: number
  active: number
  inactive: number
  by_type: Record<string, number>
  by_customer: Array<{
    id: number
    identification: string
    customer_name: string
    service_count: number
  }>
}

export const useVehiclesStore = defineStore('vehicles', () => {
  const vehicles = ref<Vehicle[]>([])
  const activeVehicles = ref<Vehicle[]>([])
  const statistics = ref<VehicleStatistics | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Computed properties
  const typeLabels = computed(() => ({
    'veiculo': 'Veículo',
    'equipamento': 'Equipamento',
    'maquina': 'Máquina',
    'outro': 'Outro'
  }))

  const typeColors = computed(() => ({
    'veiculo': 'blue',
    'equipamento': 'green',
    'maquina': 'orange',
    'outro': 'gray'
  }))

  // Actions
  const fetchVehicles = async (filters: VehicleFilters = {}) => {
    try {
      loading.value = true
      error.value = null

      const params = new URLSearchParams()
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          params.append(key, value.toString())
        }
      })

      console.log('Fazendo requisição para /vehicles com parâmetros:', params.toString())
      const response = await api.get(`/vehicles?${params.toString()}`)
      console.log('Resposta da API:', response.data)
      
      if (response.data.success) {
        vehicles.value = response.data.data.data || response.data.data
        return {
          success: true,
          data: response.data.data,
          vehicles: response.data.data.data || response.data.data,
          pagination: response.data.data.meta || null
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao carregar veículos'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao carregar veículos'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao carregar veículos'
      }
    } finally {
      loading.value = false
    }
  }

  const fetchVehicle = async (id: number) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.get(`/vehicles/${id}`)
      
      if (response.data.success) {
        return {
          success: true,
          vehicle: response.data.data
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao carregar veículo'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao carregar veículo'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao carregar veículo'
      }
    } finally {
      loading.value = false
    }
  }

  const createVehicle = async (vehicleData: Partial<Vehicle>) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.post('/vehicles', vehicleData)
      
      if (response.data.success) {
        // Adicionar à lista local
        vehicles.value.unshift(response.data.data)
        return {
          success: true,
          vehicle: response.data.data,
          message: response.data.message || 'Veículo/Equipamento cadastrado com sucesso'
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao cadastrar veículo/equipamento'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao cadastrar veículo/equipamento'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao cadastrar veículo/equipamento'
      }
    } finally {
      loading.value = false
    }
  }

  const updateVehicle = async (id: number, vehicleData: Partial<Vehicle>) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.put(`/vehicles/${id}`, vehicleData)
      
      if (response.data.success) {
        // Atualizar na lista local
        const index = vehicles.value.findIndex(vehicle => vehicle.id === id)
        if (index !== -1) {
          vehicles.value[index] = response.data.data
        }
        return {
          success: true,
          vehicle: response.data.data,
          message: response.data.message || 'Veículo/Equipamento atualizado com sucesso'
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao atualizar veículo/equipamento'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao atualizar veículo/equipamento'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao atualizar veículo/equipamento'
      }
    } finally {
      loading.value = false
    }
  }

  const deleteVehicle = async (id: number) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.delete(`/vehicles/${id}`)
      
      if (response.data.success) {
        // Remover da lista local
        vehicles.value = vehicles.value.filter(vehicle => vehicle.id !== id)
        return {
          success: true,
          message: response.data.message || 'Veículo/Equipamento excluído com sucesso'
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao excluir veículo/equipamento'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao excluir veículo/equipamento'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao excluir veículo/equipamento'
      }
    } finally {
      loading.value = false
    }
  }

  const fetchActiveVehicles = async () => {
    try {
      loading.value = true
      error.value = null

      const response = await api.get('/vehicles/active')
      
      if (response.data.success) {
        activeVehicles.value = response.data.data
        return {
          success: true,
          vehicles: response.data.data
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao carregar veículos ativos'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao carregar veículos ativos'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao carregar veículos ativos'
      }
    } finally {
      loading.value = false
    }
  }

  const fetchVehiclesByCustomer = async (customerId: number) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.get(`/vehicles/by-customer?customer_id=${customerId}`)
      
      if (response.data.success) {
        return {
          success: true,
          vehicles: response.data.data
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao carregar veículos do cliente'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao carregar veículos do cliente'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao carregar veículos do cliente'
      }
    } finally {
      loading.value = false
    }
  }

  const fetchVehiclesByType = async (type: string) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.get(`/vehicles/by-type?type=${encodeURIComponent(type)}`)
      
      if (response.data.success) {
        return {
          success: true,
          vehicles: response.data.data
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao carregar veículos por tipo'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao carregar veículos por tipo'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao carregar veículos por tipo'
      }
    } finally {
      loading.value = false
    }
  }

  const fetchServiceHistory = async (vehicleId: number) => {
    try {
      loading.value = true
      error.value = null

      const response = await api.get(`/vehicles/${vehicleId}/service-history`)
      
      if (response.data.success) {
        return {
          success: true,
          serviceOrders: response.data.data.data || response.data.data,
          pagination: response.data.data.meta || null
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Erro ao carregar histórico de serviços'
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Erro ao carregar histórico de serviços'
      return {
        success: false,
        error: err.response?.data?.message || err.message || 'Erro ao carregar histórico de serviços'
      }
    } finally {
      loading.value = false
    }
  }

  const fetchStatistics = async () => {
    try {
      loading.value = true
      error.value = null

      const response = await api.get('/vehicles/statistics')
      
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

  const getTypeLabel = (type: string) => {
    return typeLabels.value[type as keyof typeof typeLabels.value] || type
  }

  const getTypeColor = (type: string) => {
    return typeColors.value[type as keyof typeof typeColors.value] || 'gray'
  }

  const getFullIdentification = (vehicle: Vehicle) => {
    const parts = []

    if (vehicle.plate) {
      parts.push(`Placa: ${vehicle.plate}`)
    }

    if (vehicle.brand && vehicle.model) {
      parts.push(`${vehicle.brand} ${vehicle.model}`)
    }

    if (vehicle.year) {
      parts.push(`Ano: ${vehicle.year}`)
    }

    if (vehicle.serial_number) {
      parts.push(`Série: ${vehicle.serial_number}`)
    }

    return parts.join(' - ') || `ID: ${vehicle.id}`
  }

  const getShortIdentification = (vehicle: Vehicle) => {
    if (vehicle.plate) {
      return vehicle.plate
    }

    if (vehicle.serial_number) {
      return vehicle.serial_number
    }

    if (vehicle.model) {
      return vehicle.model
    }

    return `ID: ${vehicle.id}`
  }

  return {
    // State
    vehicles,
    activeVehicles,
    statistics,
    loading,
    error,
    
    // Computed
    typeLabels,
    typeColors,
    
    // Actions
    fetchVehicles,
    fetchVehicle,
    createVehicle,
    updateVehicle,
    deleteVehicle,
    fetchActiveVehicles,
    fetchVehiclesByCustomer,
    fetchVehiclesByType,
    fetchServiceHistory,
    fetchStatistics,
    getTypeLabel,
    getTypeColor,
    getFullIdentification,
    getShortIdentification
  }
})
