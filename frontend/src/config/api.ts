// Configuração da API
const isDevelopment = import.meta.env.DEV
const defaultApiUrl = isDevelopment 
  ? 'http://localhost:8000/api' 
  : 'https://alefecarvalhosilva1758230435000.0771034.meusitehostgator.com.br/api'

export const API_CONFIG = {
  baseURL: import.meta.env.VITE_API_URL || defaultApiUrl,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
}

// URLs específicas da API
export const API_ENDPOINTS = {
  // Autenticação
  login: '/login',
  register: '/register',
  logout: '/logout',
  me: '/me',
  
  // Produtos
  products: '/products',
  productsSearch: '/products/search',
  
  // Clientes
  customers: '/customers',
  
  // Pedidos
  orders: '/orders'
}
