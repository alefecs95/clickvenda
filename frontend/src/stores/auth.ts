import { defineStore } from 'pinia'
import api from '@/services/api'
import { API_ENDPOINTS } from '@/config/api'

interface User {
  id: number
  name: string
  username: string
}

interface AuthState {
  user: User | null
  token: string | null
  isAuthenticated: boolean
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
    token: localStorage.getItem('token'),
    isAuthenticated: !!localStorage.getItem('token')
  }),

  getters: {
    getUser: (state) => state.user,
    getToken: (state) => state.token,
    isLoggedIn: (state) => state.isAuthenticated
  },

  actions: {
    async login(username: string, password: string) {
      try {
        const response = await api.post(API_ENDPOINTS.login, {
          username,
          password
        })

        const { user, token } = response.data
        
        this.user = user
        this.token = token
        this.isAuthenticated = true
        
        localStorage.setItem('token', token)
        
        return { success: true }
      } catch (error: any) {
        return { 
          success: false, 
          error: error.response?.data?.message || 'Erro ao fazer login' 
        }
      }
    },

    async register(name: string, password: string, password_confirmation: string, roles?: number[]) {
      try {
        const requestData: any = {
          name,
          password,
          password_confirmation
        }
        
        if (roles && roles.length > 0) {
          requestData.roles = roles
        }
        
        const response = await api.post(API_ENDPOINTS.register, requestData)

        const { user, token } = response.data
        
        this.user = user
        this.token = token
        this.isAuthenticated = true
        
        localStorage.setItem('token', token)
        
        return { success: true }
      } catch (error: any) {
        return { 
          success: false, 
          error: error.response?.data?.message || 'Erro ao fazer registro' 
        }
      }
    },

    async logout() {
      try {
        if (this.token) {
          await api.post(API_ENDPOINTS.logout)
        }
      } catch (error) {
        console.error('Erro ao fazer logout:', error)
      } finally {
        this.user = null
        this.token = null
        this.isAuthenticated = false
        
        localStorage.removeItem('token')
      }
    },

    async fetchUser() {
      try {
        const response = await api.get(API_ENDPOINTS.me)
        this.user = response.data
        return { success: true }
      } catch (error: any) {
        this.logout()
        return { 
          success: false, 
          error: error.response?.data?.message || 'Erro ao buscar usuário' 
        }
      }
    },

    initializeAuth() {
      if (this.token) {
        this.fetchUser()
      }
    }
  }
})
