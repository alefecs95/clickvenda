import { defineStore } from 'pinia'
import api from '@/services/api'
import { API_ENDPOINTS } from '@/config/api'

interface Product {
  id: number
  name: string
  description: string | null
  price: number
  stock_quantity: number
  sku: string | null
  barcode: string | null
  active: boolean
  available: boolean
  returnable: boolean
  returnable_price: number | null
  returnable_quantity: number
  parent_product_id: number | null
  stock_multiplier: number
  manages_stock: boolean
  minimum_stock: number
  // novos campos
  vasilhame_model_id?: number | null
  is_vasilhame_model?: boolean
  actual_stock?: number
  stock_info?: any
  parent_product?: Product
  child_products?: Product[]
  is_available_for_sale?: boolean
  has_minimum_stock?: boolean
  created_at: string
  updated_at: string
}

interface ProductsState {
  products: Product[]
  loading: boolean
  error: string | null
}

export const useProductsStore = defineStore('products', {
  state: (): ProductsState => ({
    products: [],
    loading: false,
    error: null
  }),

  getters: {
    getProducts: (state) => state.products,
    getProductById: (state) => (id: number) => state.products.find(p => p.id === id),
    getActiveProducts: (state) => state.products.filter(p => p.active),
    getAvailableProducts: (state) => state.products.filter(p => p.active && p.available && (p.is_available_for_sale !== false)),
    getReturnableProducts: (state) => state.products.filter(p => p.active && p.available && p.returnable && (p.is_available_for_sale !== false))
  },

  actions: {
    async fetchProducts() {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.get(API_ENDPOINTS.products)
        this.products = response.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Erro ao carregar produtos'
        console.error('Erro ao buscar produtos:', error)
      } finally {
        this.loading = false
      }
    },

    async createProduct(productData: Partial<Product>) {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.post(API_ENDPOINTS.products, productData)
        this.products.push(response.data)
        return { success: true, product: response.data }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Erro ao criar produto'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async updateProduct(id: number, productData: Partial<Product>) {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.put(`${API_ENDPOINTS.products}/${id}`, productData)
        const index = this.products.findIndex(p => p.id === id)
        if (index !== -1) {
          this.products[index] = response.data
        }
        return { success: true, product: response.data }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Erro ao atualizar produto'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async deleteProduct(id: number) {
      this.loading = true
      this.error = null
      
      try {
        await api.delete(`${API_ENDPOINTS.products}/${id}`)
        const index = this.products.findIndex(p => p.id === id)
        if (index !== -1) {
          this.products[index].active = false
        }
        return { success: true }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Erro ao deletar produto'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async searchProducts(query: string) {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.get(`${API_ENDPOINTS.productsSearch}?q=${encodeURIComponent(query)}`)
        return { success: true, products: response.data }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Erro ao buscar produtos'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async fetchAvailableProducts() {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.get(`${API_ENDPOINTS.products}/available`)
        return { success: true, products: response.data }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Erro ao carregar produtos disponíveis'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async fetchReturnableProducts() {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.get(`${API_ENDPOINTS.products}/returnables`)
        return { success: true, products: response.data }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Erro ao carregar produtos retornáveis'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async fetchParentProducts() {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.get(`${API_ENDPOINTS.products}/parents`)
        return { success: true, products: response.data }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Erro ao carregar produtos pai'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async addStock(productId: number, quantity: number) {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.post(`${API_ENDPOINTS.products}/${productId}/add-stock`, { quantity })
        
        if (response.data.success) {
          // Atualizar o produto na lista
          const index = this.products.findIndex(p => p.id === productId)
          if (index !== -1) {
            this.products[index] = response.data.product
          }
          return { success: true, product: response.data.product }
        } else {
          throw new Error(response.data.message || 'Erro ao adicionar estoque')
        }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Erro ao adicionar estoque'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async updateProductStock(productId: number, stockData: { new_quantity: number, reason: string, notes?: string }) {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.put(`${API_ENDPOINTS.products}/${productId}/stock`, stockData)
        
        if (response.data.success) {
          // Atualizar o produto na lista
          const index = this.products.findIndex(p => p.id === productId)
          if (index !== -1) {
            this.products[index] = response.data.product
          }
          return { success: true, product: response.data.product }
        } else {
          throw new Error(response.data.message || 'Erro ao atualizar estoque')
        }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Erro ao atualizar estoque'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async addStockMovement(productId: number, movementData: { type: string, quantity: number, reason: string, notes?: string }) {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.post(`${API_ENDPOINTS.products}/${productId}/stock-movement`, movementData)
        
        if (response.data.success) {
          // Atualizar o produto na lista
          const index = this.products.findIndex(p => p.id === productId)
          if (index !== -1) {
            this.products[index] = response.data.product
          }
          return { success: true, product: response.data.product }
        } else {
          throw new Error(response.data.message || 'Erro ao registrar movimento de estoque')
        }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Erro ao registrar movimento de estoque'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    }
  }
})
