import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export interface StoreSettings {
  name: string
  cnpj: string
  phone: string
  email: string
  address: string
  city: string
  state: string
  zip_code: string
  receipt_footer: string
  whatsapp: string
  print_template: '80mm' | '58mm' | 'a4' | string
  whatsapp_message_template: string
}

export interface SalesSettings {
  default_payment_term: number | string
  default_credit_limit: number | string
  credit_alert_percentage: number | string
  auto_send_reminders: boolean | string
}

export interface SystemSettings {
  require_customer_for_credit: boolean | string
  block_negative_stock: boolean | string
  auto_backup: boolean | string
  theme: 'light' | 'dark' | 'auto' | string
  language: string
  items_per_page: number | string
  auto_print_receipts: boolean | string
}

export const useSettingsStore = defineStore('settings', () => {

  const storeSettings = ref<StoreSettings>({
    name: '',
    cnpj: '',
    phone: '',
    email: '',
    address: '',
    city: '',
    state: '',
    zip_code: '',
    receipt_footer: '',
    whatsapp: '',
    print_template: '80mm',
    whatsapp_message_template: ''
  })
  const salesSettings = ref<SalesSettings>({
    default_payment_term: '',
    default_credit_limit: '',
    credit_alert_percentage: '',
    auto_send_reminders: ''
  })

  const systemSettings = ref<SystemSettings>({
    require_customer_for_credit: '',
    block_negative_stock: '',
    auto_backup: '',
    theme: '',
    language: '',
    items_per_page: '',
    auto_print_receipts: ''
  })

  // Computed properties
  const creditAlertThreshold = computed(() => salesSettings.value.credit_alert_percentage)
  const defaultPaymentTerm = computed(() => salesSettings.value.default_payment_term)
  const defaultCreditLimit = computed(() => salesSettings.value.default_credit_limit)

  // Funções para carregar configurações
  const loadSettings = async () => {
    try {
      console.log('Carregando configurações do servidor...')
      const api = (await import('@/services/api')).default
      
      // Verificar se há token
      const token = localStorage.getItem('token')
      if (!token) {
        console.warn('Nenhum token encontrado, carregando do localStorage...')
        loadFromLocalStorage()
        return
      }
      
      // Carregar configurações do banco de dados
      const [storeResponse, salesResponse, systemResponse] = await Promise.all([
        api.get('/settings/category/store'),
        api.get('/settings/category/sales'),
        api.get('/settings/category/system')
      ])

      console.log('Respostas da API:', { storeResponse, salesResponse, systemResponse })

      let hasData = false

      if (storeResponse.data.success) {
        const storeData = storeResponse.data.data
        console.log('Dados da loja recebidos:', storeData)
        console.log('store.name:', storeData['store.name'])
        console.log('store.phone:', storeData['store.phone'])
        
        // Atualizar propriedades individualmente para manter a reatividade
        storeSettings.value.name = storeData['store.name'] || ''
        storeSettings.value.cnpj = storeData['store.cnpj'] || ''
        storeSettings.value.phone = storeData['store.phone'] || ''
        storeSettings.value.email = storeData['store.email'] || ''
        storeSettings.value.address = storeData['store.address'] || ''
        storeSettings.value.city = storeData['store.city'] || ''
        storeSettings.value.state = storeData['store.state'] || ''
        storeSettings.value.zip_code = storeData['store.zip_code'] || ''
        storeSettings.value.receipt_footer = storeData['store.receipt_footer'] || ''
        storeSettings.value.whatsapp = storeData['store.whatsapp'] || ''
        storeSettings.value.print_template = storeData['store.print_template'] || '80mm'
        storeSettings.value.whatsapp_message_template = storeData['store.whatsapp_message_template'] || ''
        console.log('Configurações da loja atualizadas:', storeSettings.value)
        console.log('storeSettings.value.name:', storeSettings.value.name)
        console.log('storeSettings.value.phone:', storeSettings.value.phone)
        hasData = true
      } else {
        console.warn('Erro ao carregar configurações da loja:', storeResponse.data.message)
      }

      if (salesResponse.data.success) {
        const salesData = salesResponse.data.data
        console.log('Dados de vendas recebidos:', salesData)
        salesSettings.value.default_payment_term = salesData['sales.default_payment_term'] || 30
        salesSettings.value.default_credit_limit = salesData['sales.default_credit_limit'] || 500.00
        salesSettings.value.credit_alert_percentage = salesData['sales.credit_alert_percentage'] || 80
        salesSettings.value.auto_send_reminders = salesData['sales.auto_send_reminders'] ?? false
        console.log('Configurações de vendas atualizadas:', salesSettings.value)
        hasData = true
      }

      if (systemResponse.data.success) {
        const systemData = systemResponse.data.data
        console.log('Dados do sistema recebidos:', systemData)
        systemSettings.value.require_customer_for_credit = systemData['system.require_customer_for_credit'] ?? true
        systemSettings.value.block_negative_stock = systemData['system.block_negative_stock'] ?? true
        systemSettings.value.auto_backup = systemData['system.auto_backup'] ?? true
        systemSettings.value.theme = systemData['system.theme'] || 'light'
        systemSettings.value.language = systemData['system.language'] || 'pt-BR'
        systemSettings.value.items_per_page = systemData['system.items_per_page'] || 25
        systemSettings.value.auto_print_receipts = systemData['system.auto_print_receipts'] ?? false
        console.log('Configurações do sistema atualizadas:', systemSettings.value)
        hasData = true
      }

      // Se não conseguiu carregar dados da API, tentar localStorage
      if (!hasData) {
        console.log('Nenhum dado carregado da API, tentando localStorage...')
        loadFromLocalStorage()
      }

    } catch (error: any) {
      console.error('Erro ao carregar configurações do servidor:', error)
      console.error('Detalhes do erro:', error.response?.data)
      
      // Fallback para localStorage se a API falhar
      loadFromLocalStorage()
    }
  }

  // Função auxiliar para carregar do localStorage
  const loadFromLocalStorage = () => {
    try {
      console.log('Tentando carregar do localStorage...')
      const savedStoreSettings = localStorage.getItem('store_settings')
      const savedSalesSettings = localStorage.getItem('sales_settings')
      const savedSystemSettings = localStorage.getItem('system_settings')

      if (savedStoreSettings) {
        const parsed = JSON.parse(savedStoreSettings)
        storeSettings.value = { ...storeSettings.value, ...parsed }
        console.log('Configurações da loja carregadas do localStorage:', parsed)
      }
      
      if (savedSalesSettings) {
        const parsed = JSON.parse(savedSalesSettings)
        salesSettings.value = { ...salesSettings.value, ...parsed }
        console.log('Configurações de vendas carregadas do localStorage:', parsed)
      }
      
      if (savedSystemSettings) {
        const parsed = JSON.parse(savedSystemSettings)
        systemSettings.value = { ...systemSettings.value, ...parsed }
        console.log('Configurações do sistema carregadas do localStorage:', parsed)
      }
    } catch (localError: any) {
      console.error('Erro ao carregar configurações do localStorage:', localError)
    }
  }

  // Funções para salvar configurações
  const saveStoreSettings = async (settings: Partial<StoreSettings>) => {
    try {
      storeSettings.value = { ...storeSettings.value, ...settings }
      
      const api = (await import('@/services/api')).default
      
      // Preparar dados para envio com prefixo 'store.'
      const storeData = Object.keys(settings).reduce((acc, key) => {
        acc[`store.${key}`] = settings[key as keyof StoreSettings]
        return acc
      }, {} as Record<string, any>)
      
      await api.post('/settings/multiple', {
        settings: storeData
      })
      
      // Também salvar no localStorage como backup
      localStorage.setItem('store_settings', JSON.stringify(storeSettings.value))
    } catch (error) {
      console.error('Erro ao salvar configurações da loja:', error)
      // Fallback para localStorage
      localStorage.setItem('store_settings', JSON.stringify(storeSettings.value))
      throw error
    }
  }

  const saveSalesSettings = async (settings: Partial<SalesSettings>) => {
    try {
      salesSettings.value = { ...salesSettings.value, ...settings }
      
      const api = (await import('@/services/api')).default
      
      // Preparar dados para envio com prefixo 'sales.'
      const salesData = Object.keys(settings).reduce((acc, key) => {
        acc[`sales.${key}`] = settings[key as keyof SalesSettings]
        return acc
      }, {} as Record<string, any>)
      
      await api.post('/settings/multiple', {
        settings: salesData
      })
      
      // Também salvar no localStorage como backup
      localStorage.setItem('sales_settings', JSON.stringify(salesSettings.value))
    } catch (error) {
      console.error('Erro ao salvar configurações de vendas:', error)
      // Fallback para localStorage
      localStorage.setItem('sales_settings', JSON.stringify(salesSettings.value))
      throw error
    }
  }

  const saveSystemSettings = async (settings: Partial<SystemSettings>) => {
    try {
      systemSettings.value = { ...systemSettings.value, ...settings }
      
      const api = (await import('@/services/api')).default
      
      // Preparar dados para envio com prefixo 'system.'
      const systemData = Object.keys(settings).reduce((acc, key) => {
        acc[`system.${key}`] = settings[key as keyof SystemSettings]
        return acc
      }, {} as Record<string, any>)
      
      await api.post('/settings/multiple', {
        settings: systemData
      })
      
      // Também salvar no localStorage como backup
      localStorage.setItem('system_settings', JSON.stringify(systemSettings.value))
    } catch (error) {
      console.error('Erro ao salvar configurações do sistema:', error)
      // Fallback para localStorage
      localStorage.setItem('system_settings', JSON.stringify(systemSettings.value))
      throw error
    }
  }

  const saveAllSettings = async () => {
    try {
      const api = (await import('@/services/api')).default
      
      // Preparar todos os dados
      const allSettings = {
        ...Object.keys(storeSettings.value).reduce((acc, key) => {
          acc[`store.${key}`] = storeSettings.value[key as keyof StoreSettings]
          return acc
        }, {} as Record<string, any>),
        ...Object.keys(salesSettings.value).reduce((acc, key) => {
          acc[`sales.${key}`] = salesSettings.value[key as keyof SalesSettings]
          return acc
        }, {} as Record<string, any>),
        ...Object.keys(systemSettings.value).reduce((acc, key) => {
          acc[`system.${key}`] = systemSettings.value[key as keyof SystemSettings]
          return acc
        }, {} as Record<string, any>)
      }
      
      console.log('Enviando configurações:', allSettings)
      
      const response = await api.post('/settings/multiple', {
        settings: allSettings
      })
      
      console.log('Resposta da API:', response.data)
      
      // Também salvar no localStorage como backup
      localStorage.setItem('store_settings', JSON.stringify(storeSettings.value))
      localStorage.setItem('sales_settings', JSON.stringify(salesSettings.value))
      localStorage.setItem('system_settings', JSON.stringify(systemSettings.value))
    } catch (error: any) {
      console.error('Erro ao salvar todas as configurações:', error)
      console.error('Detalhes do erro:', error.response?.data)
      // Fallback para localStorage
      localStorage.setItem('store_settings', JSON.stringify(storeSettings.value))
      localStorage.setItem('sales_settings', JSON.stringify(salesSettings.value))
      localStorage.setItem('system_settings', JSON.stringify(systemSettings.value))
      throw error
    }
  }

  // Função para calcular data de vencimento
  const calculateDueDate = (saleDate: Date = new Date()) => {
    const dueDate = new Date(saleDate)
    const paymentTerm = typeof salesSettings.value.default_payment_term === 'number' 
      ? salesSettings.value.default_payment_term 
      : parseInt(salesSettings.value.default_payment_term as string) || 30
    dueDate.setDate(dueDate.getDate() + paymentTerm)
    return dueDate
  }

  // Função para verificar se cliente está em risco
  const isCustomerAtRisk = (creditUsed: number, creditLimit: number) => {
    if (!creditLimit || creditLimit <= 0) return false
    const usagePercentage = (creditUsed / creditLimit) * 100
    const alertPercentage = typeof salesSettings.value.credit_alert_percentage === 'number' 
      ? salesSettings.value.credit_alert_percentage 
      : parseFloat(salesSettings.value.credit_alert_percentage as string) || 80
    return usagePercentage >= alertPercentage
  }

  // Função para gerar comprovante com dados da loja
  const generateReceiptHeader = () => {
    return {
      storeName: storeSettings.value.name,
      cnpj: storeSettings.value.cnpj,
      address: storeSettings.value.address,
      city: storeSettings.value.city,
      state: storeSettings.value.state,
      zipCode: storeSettings.value.zip_code,
      phone: storeSettings.value.phone,
      email: storeSettings.value.email,
      footer: storeSettings.value.receipt_footer
    }
  }

  return {
    // State
    storeSettings,
    salesSettings,
    systemSettings,

    // Computed
    creditAlertThreshold,
    defaultPaymentTerm,
    defaultCreditLimit,

    // Actions
    loadSettings,
    loadFromLocalStorage,
    saveStoreSettings,
    saveSalesSettings,
    saveSystemSettings,
    saveAllSettings,
    calculateDueDate,
    isCustomerAtRisk,
    generateReceiptHeader
  }
})
