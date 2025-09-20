<template>
  <AppLayout>
    <!-- Header da Página -->
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-600">Visão geral do seu negócio</p>
      </div>
      
      <button
        @click="refreshData"
        :disabled="loading"
        class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors disabled:opacity-50 flex items-center space-x-2"
      >
        <svg v-if="loading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        <span>{{ loading ? 'Atualizando...' : 'Atualizar' }}</span>
      </button>
    </div>
      <!-- Date Range Filter -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex items-center space-x-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
            <input 
              type="date" 
              v-model="dateFrom"
              @change="loadStatistics"
              class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
            <input 
              type="date" 
              v-model="dateTo"
              @change="loadStatistics"
              class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
          </div>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-red-800">{{ error }}</p>
          </div>
          <div class="ml-auto pl-3">
            <button @click="clearError" class="text-red-400 hover:text-red-600">
              <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div v-if="loading" class="text-center py-12">
        <svg class="animate-spin h-8 w-8 text-primary-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="mt-2 text-gray-600">Carregando estatísticas...</p>
      </div>

      <div v-else class="space-y-6 mb-8">
        <!-- Cards de Estatísticas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <!-- Total Orders -->
        <div class="bg-white rounded-lg shadow p-4 min-h-[120px] flex flex-col items-center justify-center text-center">
          <div class="mb-3">
            <svg class="h-8 w-8 text-blue-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 mb-1">Total Pedidos</p>
            <p class="text-xl font-bold text-gray-900">{{ statistics?.total_orders ?? '--' }}</p>
          </div>
        </div>

        <!-- Total Service Orders -->
        <div class="bg-white rounded-lg shadow p-4 min-h-[120px] flex flex-col items-center justify-center text-center">
          <div class="mb-3">
            <svg class="h-8 w-8 text-purple-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 mb-1">Total OS</p>
            <p class="text-xl font-bold text-gray-900">{{ serviceOrderStatistics?.total ?? '--' }}</p>
          </div>
        </div>

        <!-- Total Revenue -->
        <div class="bg-white rounded-lg shadow p-4 min-h-[120px] flex flex-col items-center justify-center text-center">
          <div class="mb-3">
            <svg class="h-8 w-8 text-green-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
            </svg>
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 mb-1">Receita Total</p>
            <p class="text-lg font-bold text-gray-900">{{ totalRevenue > 0 ? formatPrice(totalRevenue) : 'R$ 0,00' }}</p>
          </div>
        </div>

        <!-- Average Order Value -->
        <div class="bg-white rounded-lg shadow p-4 min-h-[120px] flex flex-col items-center justify-center text-center">
          <div class="mb-3">
            <svg class="h-8 w-8 text-yellow-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 mb-1">Ticket Médio</p>
            <p class="text-lg font-bold text-gray-900">{{ statistics?.average_order_value ? formatPrice(statistics.average_order_value) : 'R$ 0,00' }}</p>
          </div>
        </div>

        <!-- Pending Orders -->
        <div class="bg-white rounded-lg shadow p-4 min-h-[120px] flex flex-col items-center justify-center text-center">
          <div class="mb-3">
            <svg class="h-8 w-8 text-orange-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 mb-1">Pedidos Pendentes</p>
            <p class="text-xl font-bold text-gray-900">{{ statistics?.pending_orders ?? '--' }}</p>
          </div>
        </div>

        <!-- OS Em Andamento -->
        <div class="bg-white rounded-lg shadow p-4 min-h-[120px] flex flex-col items-center justify-center text-center">
          <div class="mb-3">
            <svg class="h-8 w-8 text-red-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
          </div>
          <div>
            <p class="text-xs font-medium text-gray-500 mb-1">OS Em Andamento</p>
            <p class="text-xl font-bold text-gray-900">{{ serviceOrderStatistics?.em_andamento ?? '--' }}</p>
          </div>
        </div>
        </div>

        <!-- Alerta de Clientes em Risco -->
        <div v-if="customersAtRisk.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-6">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
              <svg class="w-6 h-6 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
              </svg>
              <h3 class="text-lg font-semibold text-red-800">⚠️ Clientes com Crédito em Risco</h3>
            </div>
            <router-link
              to="/receivables"
              class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
            >
              Ver Contas a Receber
            </router-link>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div
              v-for="customer in customersAtRisk.slice(0, 4)"
              :key="customer.id"
              class="bg-white border border-red-200 rounded-lg p-4"
            >
              <div class="flex justify-between items-center mb-2">
                <h4 class="font-semibold text-gray-900 text-sm">{{ customer.name }}</h4>
                <span class="text-xs font-bold px-2 py-1 rounded-full bg-red-100 text-red-800">
                  {{ Math.round((customer.credit_used / customer.credit_limit) * 100) }}%
                </span>
              </div>
              <div class="text-xs text-gray-600 mb-2">
                R$ {{ formatPrice(customer.credit_used) }} / R$ {{ formatPrice(customer.credit_limit) }}
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div 
                  class="bg-red-500 h-2 rounded-full"
                  :style="{ width: `${Math.min((customer.credit_used / customer.credit_limit) * 100, 100)}%` }"
                ></div>
              </div>
            </div>
          </div>
          
          <div v-if="customersAtRisk.length > 4" class="mt-4 text-center">
            <router-link
              to="/receivables"
              class="text-red-600 hover:text-red-800 text-sm font-medium"
            >
              Ver todos os {{ customersAtRisk.length }} clientes em risco →
            </router-link>
          </div>
        </div>
      </div>

      <!-- Status Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Order Status Chart -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Status dos Pedidos</h3>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                <span class="text-sm text-gray-700">Concluídos</span>
              </div>
              <span class="text-sm font-medium text-gray-900">{{ statistics?.completed_orders || 0 }}</span>
            </div>
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                <span class="text-sm text-gray-700">Pendentes</span>
              </div>
              <span class="text-sm font-medium text-gray-900">{{ statistics?.pending_orders || 0 }}</span>
            </div>
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
                <span class="text-sm text-gray-700">Cancelados</span>
              </div>
              <span class="text-sm font-medium text-gray-900">{{ statistics?.cancelled_orders || 0 }}</span>
            </div>
          </div>
        </div>

        <!-- Service Order Status Chart -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Status das OS</h3>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                <span class="text-sm text-gray-700">Concluídas</span>
              </div>
              <span class="text-sm font-medium text-gray-900">{{ serviceOrderStatistics?.concluidas || 0 }}</span>
            </div>
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                <span class="text-sm text-gray-700">Em Andamento</span>
              </div>
              <span class="text-sm font-medium text-gray-900">{{ serviceOrderStatistics?.em_andamento || 0 }}</span>
            </div>
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                <span class="text-sm text-gray-700">Abertas</span>
              </div>
              <span class="text-sm font-medium text-gray-900">{{ serviceOrderStatistics?.abertas || 0 }}</span>
            </div>
          </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Atividades Recentes</h3>
          <div v-if="recentActivities.length === 0" class="text-center py-8">
            <p class="text-gray-500">Nenhuma atividade recente</p>
          </div>
          <div v-else class="space-y-3 max-h-64 overflow-y-auto">
            <div 
              v-for="activity in recentActivities" 
              :key="activity.id"
              class="flex items-start space-x-3 p-2 hover:bg-gray-50 rounded"
            >
              <div class="flex-shrink-0 mt-1">
                <div :class="activity.type === 'order' ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-600'" class="w-6 h-6 rounded-full flex items-center justify-center">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path v-if="activity.type === 'order'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">{{ activity.title }}</p>
                <p class="text-xs text-gray-500">{{ activity.description }}</p>
                <p class="text-xs text-gray-400">{{ formatActivityTime(activity.created_at) }}</p>
              </div>
              <div class="text-right">
                <p class="text-sm font-medium text-gray-900">{{ formatPrice(activity.amount) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Orders and Service Orders -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Pedidos Recentes</h3>
            <router-link to="/orders" class="text-sm text-primary-600 hover:text-primary-800">Ver todos</router-link>
          </div>
          <div v-if="recentOrders.length === 0" class="text-center py-8">
            <p class="text-gray-500">Nenhum pedido recente</p>
          </div>
          <div v-else class="space-y-3">
            <div 
              v-for="order in recentOrders" 
              :key="order.id"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
            >
              <div>
                <p class="text-sm font-medium text-gray-900">#{{ order.order_number }}</p>
                <p class="text-xs text-gray-500">{{ order.customer?.name || 'Cliente não informado' }}</p>
              </div>
              <div class="text-right">
                <p class="text-sm font-medium text-gray-900">{{ formatPrice(order.final_amount) }}</p>
                <span
                  :class="{
                    'bg-green-100 text-green-800': order.status === 'completed',
                    'bg-yellow-100 text-yellow-800': order.status === 'pending',
                    'bg-red-100 text-red-800': order.status === 'cancelled'
                  }"
                  class="inline-flex px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ getStatusText(order.status) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Service Orders -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">OS Recentes</h3>
            <router-link to="/service-orders" class="text-sm text-primary-600 hover:text-primary-800">Ver todas</router-link>
          </div>
          <div v-if="recentServiceOrders.length === 0" class="text-center py-8">
            <p class="text-gray-500">Nenhuma OS recente</p>
          </div>
          <div v-else class="space-y-3">
            <div 
              v-for="serviceOrder in recentServiceOrders" 
              :key="serviceOrder.id"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
            >
              <div>
                <p class="text-sm font-medium text-gray-900">#{{ serviceOrder.order_number }}</p>
                <p class="text-xs text-gray-500">{{ serviceOrder.customer?.name || serviceOrder.vehicle?.plate || 'Cliente não informado' }}</p>
              </div>
              <div class="text-right">
                <p class="text-sm font-medium text-gray-900">{{ formatPrice(serviceOrder.final_amount) }}</p>
                <span
                  :class="{
                    'bg-green-100 text-green-800': serviceOrder.status === 'concluida',
                    'bg-yellow-100 text-yellow-800': serviceOrder.status === 'em_andamento',
                    'bg-blue-100 text-blue-800': serviceOrder.status === 'aberta'
                  }"
                  class="inline-flex px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ getServiceOrderStatusText(serviceOrder.status) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Ações Rápidas</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
          <button
            @click="router.push('/sales')"
            class="flex items-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <svg class="h-6 w-6 text-primary-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            <div class="text-left">
              <p class="text-sm font-medium text-gray-900">Nova Venda</p>
              <p class="text-xs text-gray-500">Criar pedido</p>
            </div>
          </button>

          <button
            @click="router.push('/service-orders/new')"
            class="flex items-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <svg class="h-6 w-6 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <div class="text-left">
              <p class="text-sm font-medium text-gray-900">Nova OS</p>
              <p class="text-xs text-gray-500">Ordem de serviço</p>
            </div>
          </button>

          <button
            @click="router.push('/products')"
            class="flex items-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <svg class="h-6 w-6 text-primary-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <div class="text-left">
              <p class="text-sm font-medium text-gray-900">Produtos</p>
              <p class="text-xs text-gray-500">Gerenciar</p>
            </div>
          </button>

          <button
            @click="router.push('/customers')"
            class="flex items-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <svg class="h-6 w-6 text-primary-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <div class="text-left">
              <p class="text-sm font-medium text-gray-900">Clientes</p>
              <p class="text-xs text-gray-500">Gerenciar</p>
            </div>
          </button>

          <button
            @click="router.push('/orders')"
            class="flex items-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <svg class="h-6 w-6 text-primary-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <div class="text-left">
              <p class="text-sm font-medium text-gray-900">Pedidos</p>
              <p class="text-xs text-gray-500">Ver todos</p>
            </div>
          </button>

          <button
            @click="router.push('/receivables')"
            class="flex items-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <svg class="h-6 w-6 text-primary-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
            </svg>
            <div class="text-left">
              <p class="text-sm font-medium text-gray-900">Contas</p>
              <p class="text-xs text-gray-500">A receber</p>
            </div>
          </button>
        </div>
      </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useOrdersStore } from '@/stores/orders'
import { useServiceOrdersStore } from '@/stores/serviceOrders'
import { useCustomersStore } from '@/stores/customers'
import { useProductsStore } from '@/stores/products'
import { useSettingsStore } from '@/stores/settings'
import AppLayout from '@/components/AppLayout.vue'

const router = useRouter()
const ordersStore = useOrdersStore()
const serviceOrdersStore = useServiceOrdersStore()
const customersStore = useCustomersStore()
const productsStore = useProductsStore()
const settingsStore = useSettingsStore()

const loading = ref(false)
const error = ref<string | null>(null)
const dateFrom = ref('')
const dateTo = ref('')

const statistics = computed(() => ordersStore.statistics)
const serviceOrderStatistics = computed(() => serviceOrdersStore.statistics)

// Receita total (vendas + OS)
const totalRevenue = computed(() => {
  const ordersRevenue = Number(statistics.value?.total_revenue) || 0
  const serviceOrdersRevenue = Number(serviceOrderStatistics.value?.receita_total) || 0
  return ordersRevenue + serviceOrdersRevenue
})

// Clientes com alto risco de crédito (>80% do limite usado)
const customersAtRisk = computed(() => {
  return customersStore.customers.filter(customer => {
    if (!customer.credit_limit || customer.credit_limit <= 0) return false
    const usagePercentage = (customer.credit_used / customer.credit_limit) * 100
    return usagePercentage >= 80
  }).sort((a, b) => {
    const aPercentage = (a.credit_used / a.credit_limit) * 100
    const bPercentage = (b.credit_used / b.credit_limit) * 100
    return bPercentage - aPercentage
  })
})

const recentOrders = computed(() => ordersStore.orders.slice(0, 5))
const recentServiceOrders = computed(() => serviceOrdersStore.serviceOrders.slice(0, 5))

// Atividades recentes combinadas (pedidos + OS)
const recentActivities = computed(() => {
  const activities = []
  
  // Adicionar pedidos recentes
  if (recentOrders.value && Array.isArray(recentOrders.value)) {
    recentOrders.value.forEach(order => {
      activities.push({
        id: `order-${order.id}`,
        type: 'order',
        title: `Pedido #${order.order_number || 'N/A'}`,
        description: order.customer?.name || 'Cliente não informado',
        amount: order.final_amount || 0,
        created_at: order.created_at || new Date().toISOString()
      })
    })
  }
  
  // Adicionar OS recentes
  if (recentServiceOrders.value && Array.isArray(recentServiceOrders.value)) {
    recentServiceOrders.value.forEach(serviceOrder => {
      activities.push({
        id: `service-order-${serviceOrder.id}`,
        type: 'service-order',
        title: `OS #${serviceOrder.order_number || 'N/A'}`,
        description: serviceOrder.customer?.name || serviceOrder.vehicle?.plate || 'Cliente não informado',
        amount: serviceOrder.final_amount || 0,
        created_at: serviceOrder.created_at || new Date().toISOString()
      })
    })
  }
  
  // Ordenar por data de criação (mais recente primeiro)
  return activities.sort((a, b) => {
    const dateA = new Date(a.created_at).getTime()
    const dateB = new Date(b.created_at).getTime()
    return dateB - dateA
  }).slice(0, 10)
})

const loadStatistics = async () => {
  try {
    loading.value = true
    error.value = null
    
    // Debug: Verificar token
    const token = localStorage.getItem('token')
    console.log('Token disponível:', !!token)
    console.log('Datas sendo enviadas:', { dateFrom: dateFrom.value, dateTo: dateTo.value })
    
    await Promise.all([
      ordersStore.fetchStatistics(dateFrom.value, dateTo.value),
      serviceOrdersStore.fetchStatistics(),
      ordersStore.fetchOrders({ per_page: 5 }),
      serviceOrdersStore.fetchServiceOrders({ per_page: 5 })
    ])
  } catch (err: any) {
    console.error('Erro ao carregar estatísticas:', err)
    error.value = err.message || 'Erro ao carregar estatísticas'
  } finally {
    loading.value = false
  }
}

const refreshData = async () => {
  await Promise.all([
    loadStatistics(),
    customersStore.fetchCustomers(),
    productsStore.fetchProducts(),
    settingsStore.loadSettings()
  ])
}

const clearError = () => {
  error.value = null
}

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(price)
}

const getStatusText = (status: string) => {
  const statusMap = {
    pending: 'Pendente',
    completed: 'Concluído',
    cancelled: 'Cancelado'
  }
  return statusMap[status as keyof typeof statusMap] || status
}

const getServiceOrderStatusText = (status: string) => {
  const statusMap = {
    aberta: 'Aberta',
    em_andamento: 'Em Andamento',
    concluida: 'Concluída',
    cancelada: 'Cancelada'
  }
  return statusMap[status as keyof typeof statusMap] || status
}

const formatRelativeTime = (dateString: string) => {
  if (!dateString) return 'Data inválida'
  
  const date = new Date(dateString)
  const now = new Date()
  const diffInMinutes = Math.floor((now.getTime() - date.getTime()) / (1000 * 60))
  
  if (diffInMinutes < 1) return 'Agora'
  if (diffInMinutes < 60) return `${diffInMinutes}m atrás`
  
  const diffInHours = Math.floor(diffInMinutes / 60)
  if (diffInHours < 24) return `${diffInHours}h atrás`
  
  const diffInDays = Math.floor(diffInHours / 24)
  if (diffInDays < 7) return `${diffInDays}d atrás`
  
  return date.toLocaleDateString('pt-BR')
}

const formatActivityTime = (dateString: string) => {
  try {
    if (!dateString) return 'Data não disponível'
    return formatRelativeTime(dateString)
  } catch (error) {
    console.error('Erro ao formatar data:', error)
    return 'Data inválida'
  }
}

onMounted(() => {
  // Definir período padrão (mês atual)
  const now = new Date()
  const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1)
  
  dateFrom.value = startOfMonth.toISOString().split('T')[0]
  dateTo.value = now.toISOString().split('T')[0]
  
  refreshData()
})
</script>
