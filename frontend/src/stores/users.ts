import { defineStore } from 'pinia'
import api from '@/services/api'
import { API_ENDPOINTS } from '@/config/api'

interface Role {
  id: number
  name: string
  display_name: string
  description?: string
  permissions?: Permission[]
}

interface Permission {
  id: number
  name: string
  display_name: string
  description?: string
  active: boolean
}

interface User {
  id: number
  name: string
  cpf?: string
  is_admin: boolean
  status: 'active' | 'inactive'
  terms_accepted: boolean
  roles?: Role[]
  created_at: string
  updated_at: string
}

interface UsersState {
  users: User[]
  roles: Role[]
  permissions: Permission[]
  loading: boolean
  error: string | null
}

export const useUsersStore = defineStore('users', {
  state: (): UsersState => ({
    users: [],
    roles: [],
    permissions: [],
    loading: false,
    error: null
  }),

  getters: {
    getUsers: (state) => state.users,
    getRoles: (state) => state.roles,
    getPermissions: (state) => state.permissions,
    isLoading: (state) => state.loading,
    getError: (state) => state.error,
    
    getUserById: (state) => (id: number) => {
      return state.users.find(user => user.id === id)
    },
    
    getActiveUsers: (state) => {
      return state.users.filter(user => user.status === 'active')
    },
    
    getAdminUsers: (state) => {
      return state.users.filter(user => user.is_admin)
    }
  },

  actions: {
    setLoading(loading: boolean) {
      this.loading = loading
    },

    setError(error: string | null) {
      this.error = error
    },

    async fetchUsers() {
      this.setLoading(true)
      this.setError(null)
      
      try {
        const response = await api.get('/users')
        this.users = response.data.data || response.data
        return { success: true }
      } catch (error: any) {
        const errorMessage = error.response?.data?.message || 'Erro ao carregar usuários'
        this.setError(errorMessage)
        return { success: false, error: errorMessage }
      } finally {
        this.setLoading(false)
      }
    },

    async fetchRoles() {
      try {
        const response = await api.get('/roles')
        this.roles = response.data.data || response.data
        return { success: true }
      } catch (error: any) {
        const errorMessage = error.response?.data?.message || 'Erro ao carregar roles'
        this.setError(errorMessage)
        return { success: false, error: errorMessage }
      }
    },

    async fetchPermissions() {
      try {
        const response = await api.get('/permissions')
        this.permissions = response.data.data || response.data
        return { success: true }
      } catch (error: any) {
        const errorMessage = error.response?.data?.message || 'Erro ao carregar permissões'
        this.setError(errorMessage)
        return { success: false, error: errorMessage }
      }
    },

    async createUser(userData: Partial<User>) {
      this.setLoading(true)
      this.setError(null)
      
      try {
        const response = await api.post('/users', userData)
        const newUser = response.data.user || response.data.data || response.data
        this.users.push(newUser)
        return { success: true, data: newUser }
      } catch (error: any) {
        const errorMessage = error.response?.data?.message || 'Erro ao criar usuário'
        const validationErrors = error.response?.data?.errors || null
        this.setError(errorMessage)
        return { 
          success: false, 
          error: errorMessage,
          validationErrors: validationErrors
        }
      } finally {
        this.setLoading(false)
      }
    },

    async updateUser(id: number, userData: Partial<User>) {
      this.setLoading(true)
      this.setError(null)
      
      try {
        const response = await api.put(`/users/${id}`, userData)
        const updatedUser = response.data.data || response.data
        
        const index = this.users.findIndex(user => user.id === id)
        if (index !== -1) {
          this.users[index] = updatedUser
        }
        
        return { success: true, data: updatedUser }
      } catch (error: any) {
        const errorMessage = error.response?.data?.message || 'Erro ao atualizar usuário'
        this.setError(errorMessage)
        return { success: false, error: errorMessage }
      } finally {
        this.setLoading(false)
      }
    },

    async deleteUser(id: number) {
      this.setLoading(true)
      this.setError(null)
      
      try {
        await api.delete(`/users/${id}`)
        this.users = this.users.filter(user => user.id !== id)
        return { success: true }
      } catch (error: any) {
        const errorMessage = error.response?.data?.message || 'Erro ao excluir usuário'
        this.setError(errorMessage)
        return { success: false, error: errorMessage }
      } finally {
        this.setLoading(false)
      }
    },

    async assignRoles(userId: number, roleIds: number[]) {
      this.setLoading(true)
      this.setError(null)
      
      try {
        const response = await api.post(`/users/${userId}/assign-roles`, {
          role_ids: roleIds
        })
        
        // Atualizar o usuário na lista local
        const userIndex = this.users.findIndex(user => user.id === userId)
        if (userIndex !== -1) {
          const updatedUser = response.data.data || response.data
          this.users[userIndex] = updatedUser
        }
        
        return { success: true }
      } catch (error: any) {
        const errorMessage = error.response?.data?.message || 'Erro ao atribuir roles'
        this.setError(errorMessage)
        return { success: false, error: errorMessage }
      } finally {
        this.setLoading(false)
      }
    },

    async checkUserPermissions(userId: number) {
      try {
        const response = await api.get(`/users/${userId}/permissions`)
        return { success: true, data: response.data }
      } catch (error: any) {
        const errorMessage = error.response?.data?.message || 'Erro ao verificar permissões'
        this.setError(errorMessage)
        return { success: false, error: errorMessage }
      }
    },

    // Métodos auxiliares
    clearError() {
      this.error = null
    },

    resetState() {
      this.users = []
      this.roles = []
      this.permissions = []
      this.loading = false
      this.error = null
    }
  }
})

export type { User, Role, Permission }