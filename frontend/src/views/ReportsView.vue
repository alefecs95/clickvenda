<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Cabeçalho -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Relatórios Financeiros</h1>
          <p class="text-gray-600">Acompanhe o desempenho do seu negócio</p>
        </div>
        
        <!-- Filtros de Período -->
        <div class="flex items-center space-x-4">
          <div class="flex items-center space-x-2">
            <label class="text-sm font-medium text-gray-700">Período:</label>
            <select 
              v-model="selectedPeriod" 
              @change="loadReports"
              class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="today">Hoje</option>
              <option value="week">Esta Semana</option>
              <option value="month">Este Mês</option>
              <option value="quarter">Este Trimestre</option>
              <option value="year">Este Ano</option>
              <option value="custom">Personalizado</option>
            </select>
          </div>
          
          <!-- Datas Personalizadas -->
          <div v-if="selectedPeriod === 'custom'" class="flex items-center space-x-2">
            <input 
              v-model="customDateFrom"
              type="date"
              class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
            <span class="text-gray-500">até</span>
            <input 
              v-model="customDateTo"
              type="date"
              class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
            <button 
              @click="loadReports"
              class="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 text-sm"
            >
              Aplicar
            </button>
          </div>
        </div>
      </div>

      <!-- Cards de Resumo -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total de Vendas -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Total de Vendas + Serviços</dt>
                <dd class="text-lg font-medium text-gray-900">R$ {{ formatCurrency(summary.totalSales) }}</dd>
              </dl>
            </div>
          </div>
        </div>

        <!-- Lucro Total -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Lucro Total</dt>
                <dd class="text-lg font-medium text-gray-900">R$ {{ formatCurrency(summary.totalProfit) }}</dd>
              </dl>
            </div>
          </div>
        </div>

        <!-- Margem de Lucro -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Margem de Lucro</dt>
                <dd class="text-lg font-medium text-gray-900">{{ summary.profitMargin }}%</dd>
              </dl>
            </div>
          </div>
        </div>

        <!-- Número de Vendas -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Número de Pedidos</dt>
                <dd class="text-lg font-medium text-gray-900">{{ summary.totalOrders }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <!-- Gráficos e Tabelas -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Vendas por Período -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Vendas + Serviços por Período</h3>
          <div class="h-64">
            <SalesChart 
              v-if="chartData.labels.length > 0"
              :data="chartData"
              type="line"
              title="Vendas + Serviços por Período"
            />
            <div v-else class="h-full flex items-center justify-center bg-gray-50 rounded-lg">
              <div class="text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <p class="mt-2 text-sm text-gray-500">Gráfico de vendas por período</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Produtos Mais Vendidos -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Produtos Mais Vendidos</h3>
          <div class="space-y-3">
            <div v-for="product in topProducts" :key="product.id" class="flex items-center justify-between">
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900">{{ product.name }}</p>
                <p class="text-xs text-gray-500">{{ product.quantity }} unidades vendidas</p>
              </div>
              <div class="text-right">
                <p class="text-sm font-medium text-gray-900">R$ {{ formatCurrency(product.total) }}</p>
                <p class="text-xs text-gray-500">{{ product.percentage }}% do total</p>
              </div>
            </div>
            <div v-if="topProducts.length === 0" class="text-center py-8">
              <p class="text-gray-500">Nenhum produto vendido no período</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabela de Vendas Recentes -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Vendas Recentes</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Pedido
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tipo
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Cliente
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Data
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Total
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Lucro
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Margem
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="order in recentOrders" :key="order.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  #{{ order.order_number || order.service_number }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="order.type === 'venda'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Venda
                  </span>
                  <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    Serviço
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ order.customer?.name || 'Cliente não identificado' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(order.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  R$ {{ formatCurrency(order.type === 'servico' ? order.total_amount : order.final_amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  R$ {{ formatCurrency(order.profit) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ order.profit_margin }}%
                </td>
              </tr>
              <tr v-if="recentOrders.length === 0">
                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                  Nenhuma venda ou serviço encontrado no período
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6">
          <div class="flex items-center space-x-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600"></div>
            <span class="text-gray-700">Carregando relatórios...</span>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import SalesChart from '@/components/SalesChart.vue'
import { useOrdersStore } from '@/stores/orders'
import { useProductsStore } from '@/stores/products'
import { useServiceOrdersStore } from '@/stores/serviceOrders'

const ordersStore = useOrdersStore()
const productsStore = useProductsStore()
const serviceOrdersStore = useServiceOrdersStore()

// Estado
const loading = ref(false)
const selectedPeriod = ref('month')
const customDateFrom = ref('')
const customDateTo = ref('')
const recentOrders = ref<any[]>([])
const topProducts = ref<any[]>([])
const chartData = ref({
  labels: [],
  datasets: [{
    label: 'Vendas',
    data: [],
    backgroundColor: 'rgba(59, 130, 246, 0.1)',
    borderColor: 'rgb(59, 130, 246)',
    borderWidth: 2,
    tension: 0.4
  }]
})

// Resumo financeiro
const summary = ref({
  totalSales: 0,
  totalProfit: 0,
  profitMargin: 0,
  totalOrders: 0
})

// Computed
const dateRange = computed(() => {
  const today = new Date()
  let dateFrom = new Date()
  let dateTo = new Date()

  switch (selectedPeriod.value) {
    case 'today':
      dateFrom = new Date(today.getFullYear(), today.getMonth(), today.getDate())
      dateTo = new Date(today.getFullYear(), today.getMonth(), today.getDate(), 23, 59, 59)
      break
    case 'week':
      const dayOfWeek = today.getDay()
      dateFrom = new Date(today.getTime() - (dayOfWeek * 24 * 60 * 60 * 1000))
      dateFrom.setHours(0, 0, 0, 0)
      dateTo = new Date(today.getTime() + ((6 - dayOfWeek) * 24 * 60 * 60 * 1000))
      dateTo.setHours(23, 59, 59, 999)
      break
    case 'month':
      dateFrom = new Date(today.getFullYear(), today.getMonth(), 1)
      dateTo = new Date(today.getFullYear(), today.getMonth() + 1, 0, 23, 59, 59)
      break
    case 'quarter':
      const quarter = Math.floor(today.getMonth() / 3)
      dateFrom = new Date(today.getFullYear(), quarter * 3, 1)
      dateTo = new Date(today.getFullYear(), quarter * 3 + 3, 0, 23, 59, 59)
      break
    case 'year':
      dateFrom = new Date(today.getFullYear(), 0, 1)
      dateTo = new Date(today.getFullYear(), 11, 31, 23, 59, 59)
      break
    case 'custom':
      if (customDateFrom.value && customDateTo.value) {
        dateFrom = new Date(customDateFrom.value)
        dateTo = new Date(customDateTo.value)
        dateTo.setHours(23, 59, 59, 999)
      }
      break
  }

  return {
    from: dateFrom.toISOString().split('T')[0],
    to: dateTo.toISOString().split('T')[0]
  }
})

// Métodos
const formatCurrency = (value: number) => {
  // Verificar se o valor é válido
  if (value === null || value === undefined || isNaN(value)) {
    return '0,00'
  }
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value)
}

const formatDate = (dateString: string) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('pt-BR')
}

const calculateProfit = (order: any) => {
  if (!order || !order.final_amount) return 0
  
  let totalCost = 0
  let totalSale = parseFloat(order.final_amount) || 0

  if (order.items && Array.isArray(order.items)) {
    order.items.forEach((item: any) => {
      if (item.product && item.product.purchase_price && item.quantity) {
        const purchasePrice = parseFloat(item.product.purchase_price) || 0
        const quantity = parseInt(item.quantity) || 0
        totalCost += purchasePrice * quantity
      }
    })
  }

  return totalSale - totalCost
}

const calculateProfitMargin = (profit: number, sale: number) => {
  if (!sale || sale === 0 || isNaN(profit) || isNaN(sale)) return 0
  const margin = (profit / sale) * 100
  return isNaN(margin) ? 0 : Math.round(margin * 100) / 100 // Arredondar para 2 casas decimais
}

const generateChartData = (orders: any[]) => {
  const salesByPeriod = new Map()
  const servicesByPeriod = new Map()
  
  // Determinar o formato de agrupamento baseado no período selecionado
  const groupBy = selectedPeriod.value === 'today' ? 'hour' :
                  selectedPeriod.value === 'week' ? 'day' :
                  selectedPeriod.value === 'month' ? 'day' :
                  selectedPeriod.value === 'quarter' ? 'week' :
                  selectedPeriod.value === 'year' ? 'month' : 'day'

  orders.forEach((order: any) => {
    if (!order.created_at) return
    
    const orderDate = new Date(order.created_at)
    let key = ''
    
    switch (groupBy) {
      case 'hour':
        key = `${orderDate.getHours()}:00`
        break
      case 'day':
        key = orderDate.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' })
        break
      case 'week':
        const weekStart = new Date(orderDate)
        weekStart.setDate(orderDate.getDate() - orderDate.getDay())
        key = `Semana ${weekStart.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' })}`
        break
      case 'month':
        key = orderDate.toLocaleDateString('pt-BR', { month: 'short', year: 'numeric' })
        break
    }
    
    // Separar vendas e serviços
    if (order.type === 'servico') {
      if (!servicesByPeriod.has(key)) {
        servicesByPeriod.set(key, 0)
      }
      const amount = parseFloat(order.total_amount) || 0
      servicesByPeriod.set(key, servicesByPeriod.get(key) + amount)
    } else {
      if (!salesByPeriod.has(key)) {
        salesByPeriod.set(key, 0)
      }
      const amount = parseFloat(order.final_amount) || 0
      salesByPeriod.set(key, salesByPeriod.get(key) + amount)
    }
  })

  // Obter todas as chaves únicas e ordená-las
  const allKeys = new Set([...salesByPeriod.keys(), ...servicesByPeriod.keys()])
  const sortedKeys = Array.from(allKeys).sort((a, b) => a.localeCompare(b))

  // Criar arrays de dados para cada categoria
  const salesData = sortedKeys.map(key => salesByPeriod.get(key) || 0)
  const servicesData = sortedKeys.map(key => servicesByPeriod.get(key) || 0)

  chartData.value = {
    labels: sortedKeys,
    datasets: [
      {
        label: 'Vendas (R$)',
        data: salesData,
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        borderColor: 'rgb(59, 130, 246)',
        borderWidth: 2,
        tension: 0.4
      },
      {
        label: 'Serviços (R$)',
        data: servicesData,
        backgroundColor: 'rgba(34, 197, 94, 0.1)',
        borderColor: 'rgb(34, 197, 94)',
        borderWidth: 2,
        tension: 0.4
      }
    ]
  }
}

const loadReports = async () => {
  loading.value = true
  
  try {
    // Buscar pedidos do período
    const salesFilters = {
      date_from: dateRange.value.from,
      date_to: dateRange.value.to,
      status: 'completed',
      per_page: 1000
    }

    const serviceFilters = {
      date_from: dateRange.value.from,
      date_to: dateRange.value.to,
      status: 'concluida',
      per_page: 1000
    }

    console.log('=== DEBUG REPORTS ===')
    console.log('Filtros de vendas aplicados:', salesFilters)
    console.log('Filtros de serviços aplicados:', serviceFilters)
    
    // Buscar pedidos de vendas e ordens de serviço em paralelo
    await Promise.all([
      ordersStore.fetchOrders(salesFilters),
      serviceOrdersStore.fetchServiceOrders(serviceFilters)
    ])
    
    const orders = ordersStore.orders || []
    const serviceOrders = serviceOrdersStore.serviceOrders || []
    
    console.log('Pedidos de vendas carregados:', orders.length)
    console.log('Ordens de serviço carregadas:', serviceOrders.length)
    console.log('Primeiros 3 pedidos de vendas:', orders.slice(0, 3))
    console.log('Primeiras 3 ordens de serviço:', serviceOrders.slice(0, 3))

    // Calcular resumo
    let totalSales = 0
    let totalProfit = 0
    let totalOrders = orders.length + serviceOrders.length

    const productSales = new Map()

    // Processar pedidos de vendas
    orders.forEach((order: any) => {
      if (!order) return
      
      const orderAmount = parseFloat(order.final_amount) || 0
      totalSales += orderAmount
      
      const profit = calculateProfit(order)
      totalProfit += profit
      
      // Adicionar dados de lucro ao pedido
      order.profit = profit
      order.profit_margin = calculateProfitMargin(profit, orderAmount)

      // Contar produtos vendidos
      if (order.items && Array.isArray(order.items)) {
        order.items.forEach((item: any) => {
          if (item.product && item.product.id) {
            const productId = item.product.id
            if (!productSales.has(productId)) {
              productSales.set(productId, {
                id: productId,
                name: item.product.name || 'Produto sem nome',
                quantity: 0,
                total: 0
              })
            }
            const productData = productSales.get(productId)
            productData.quantity += parseInt(item.quantity) || 0
            productData.total += parseFloat(item.total_price) || 0
          }
        })
      }
    })

    // Processar ordens de serviço
    serviceOrders.forEach((serviceOrder: any) => {
      if (!serviceOrder) return
      
      const serviceAmount = parseFloat(serviceOrder.total_amount) || 0
      totalSales += serviceAmount
      
      // Para ordens de serviço, assumimos uma margem de lucro padrão de 70%
      const serviceProfit = serviceAmount * 0.7
      totalProfit += serviceProfit
      
      // Adicionar dados de lucro à ordem de serviço
      serviceOrder.profit = serviceProfit
      serviceOrder.profit_margin = 70
    })

    // Atualizar resumo com validação
    summary.value = {
      totalSales: isNaN(totalSales) ? 0 : totalSales,
      totalProfit: isNaN(totalProfit) ? 0 : totalProfit,
      profitMargin: calculateProfitMargin(totalProfit, totalSales),
      totalOrders: totalOrders || 0
    }

    // Top produtos
    const sortedProducts = Array.from(productSales.values())
      .sort((a, b) => (b.total || 0) - (a.total || 0))
      .slice(0, 5)
      .map(product => ({
        ...product,
        percentage: totalSales > 0 ? Math.round(((product.total || 0) / totalSales) * 100) : 0
      }))

    topProducts.value = sortedProducts

    // Pedidos recentes (últimos 10) - incluindo vendas e serviços
    const allOrders = [
      ...orders.map((order: any) => ({ ...order, type: 'venda' })),
      ...serviceOrders.map((serviceOrder: any) => ({ ...serviceOrder, type: 'servico' }))
    ]
    
    recentOrders.value = allOrders
      .filter((order: any) => order && order.created_at)
      .sort((a: any, b: any) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
      .slice(0, 10)

    // Gerar dados do gráfico (incluindo vendas e serviços)
    generateChartData(allOrders)

  } catch (error) {
    console.error('Erro ao carregar relatórios:', error)
    // Resetar valores em caso de erro
    summary.value = {
      totalSales: 0,
      totalProfit: 0,
      profitMargin: 0,
      totalOrders: 0
    }
    topProducts.value = []
    recentOrders.value = []
    chartData.value = {
      labels: [],
      datasets: [{
        label: 'Vendas',
        data: [],
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        borderColor: 'rgb(59, 130, 246)',
        borderWidth: 2,
        tension: 0.4
      }]
    }
  } finally {
    loading.value = false
  }
}

// Lifecycle
onMounted(() => {
  loadReports()
})
</script>