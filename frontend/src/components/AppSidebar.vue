<template>
  <div class="flex">
    <!-- Sidebar -->
    <div 
      :class="[
        'bg-white shadow-lg border-r border-gray-200 transition-all duration-300 ease-in-out flex flex-col',
        isCollapsed ? 'w-16' : 'w-64'
      ]"
      class="h-screen fixed left-0 top-0 z-40"
    >
      <!-- Header com Logo e Toggle -->
      <div class="flex items-center justify-between p-4 border-b border-gray-200">
        <div v-if="!isCollapsed" class="flex items-center">
          <router-link to="/dashboard" class="text-xl font-bold text-primary-600 hover:text-primary-700">
            ClickVenda
          </router-link>
        </div>
        <button
          @click="toggleSidebar"
          class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors"
          :title="isCollapsed ? 'Expandir menu' : 'Recolher menu'"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path 
              stroke-linecap="round" 
              stroke-linejoin="round" 
              stroke-width="2" 
              :d="isCollapsed ? 'M9 5l7 7-7 7' : 'M15 19l-7-7 7-7'"
            />
          </svg>
        </button>
      </div>

      <!-- Navigation Menu -->
      <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
        <!-- Dashboard -->
        <router-link
          to="/dashboard"
          class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors group"
          :class="[
            $route.name === 'dashboard' 
              ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-700' 
              : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600'
          ]"
          :title="isCollapsed ? 'Dashboard' : ''"
        >
          <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v0M8 5a2 2 0 012-2h4a2 2 0 012 2v0M8 5h8"/>
          </svg>
          <span v-if="!isCollapsed" class="ml-3">Dashboard</span>
        </router-link>

        <!-- Separador -->
        <div v-if="!isCollapsed" class="border-t border-gray-200 my-4"></div>

        <!-- Nova Venda -->
        <router-link
          to="/sales"
          class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors group bg-green-50 hover:bg-green-100 text-green-700 hover:text-green-800 border border-green-200"
          :title="isCollapsed ? 'Nova Venda' : ''"
        >
          <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
          </svg>
          <span v-if="!isCollapsed" class="ml-3 font-semibold">Nova Venda</span>
        </router-link>

        <!-- Separador -->
        <div v-if="!isCollapsed" class="border-t border-gray-200 my-4"></div>

        <!-- Produtos -->
        <router-link
          to="/products"
          class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors group"
          :class="[
            $route.name === 'products' 
              ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-700' 
              : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600'
          ]"
          :title="isCollapsed ? 'Produtos' : ''"
        >
          <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
          </svg>
          <span v-if="!isCollapsed" class="ml-3">Produtos</span>
        </router-link>

        <!-- Vasilhames -->
        <router-link
          to="/vasilhames"
          class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors group"
          :class="[
            $route.name === 'vasilhames' 
              ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-700' 
              : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600'
          ]"
          :title="isCollapsed ? 'Vasilhames' : ''"
        >
          <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
          </svg>
          <span v-if="!isCollapsed" class="ml-3">Vasilhames</span>
        </router-link>

        <!-- Clientes -->
        <router-link
          to="/customers"
          class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors group"
          :class="[
            $route.name === 'customers' 
              ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-700' 
              : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600'
          ]"
          :title="isCollapsed ? 'Clientes' : ''"
        >
          <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
          <span v-if="!isCollapsed" class="ml-3">Clientes</span>
        </router-link>

        <!-- Veículos -->
        <router-link
          to="/vehicles"
          class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors group"
          :class="[
            $route.name === 'vehicles' 
              ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-700' 
              : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600'
          ]"
          :title="isCollapsed ? 'Veículos' : ''"
        >
          <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span v-if="!isCollapsed" class="ml-3">Veículos</span>
        </router-link>

        <!-- Pedidos -->
        <router-link
          to="/orders"
          class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors group"
          :class="[
            $route.name === 'orders' 
              ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-700' 
              : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600'
          ]"
          :title="isCollapsed ? 'Pedidos' : ''"
        >
          <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <span v-if="!isCollapsed" class="ml-3">Pedidos</span>
        </router-link>

        <!-- Ordens de Serviço -->
        <router-link
          to="/service-orders"
          class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors group"
          :class="[
            $route.name === 'service-orders' 
              ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-700' 
              : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600'
          ]"
          :title="isCollapsed ? 'Ordens de Serviço' : ''"
        >
          <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
          </svg>
          <span v-if="!isCollapsed" class="ml-3">Ordens de Serviço</span>
        </router-link>

        <!-- Contas a Receber -->
        <router-link
          to="/receivables"
          class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors group"
          :class="[
            $route.name === 'receivables' 
              ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-700' 
              : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600'
          ]"
          :title="isCollapsed ? 'Contas a Receber' : ''"
        >
          <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
          </svg>
          <span v-if="!isCollapsed" class="ml-3">Contas a Receber</span>
        </router-link>

        <!-- Financeiro -->
        <router-link
          to="/financial"
          class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors group"
          :class="[
            $route.name === 'financial' 
              ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-700' 
              : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600'
          ]"
          :title="isCollapsed ? 'Financeiro' : ''"
        >
          <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span v-if="!isCollapsed" class="ml-3">Financeiro</span>
        </router-link>

        <!-- Relatórios -->
        <router-link
          to="/reports"
          class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors group"
          :class="[
            $route.name === 'reports' 
              ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-700' 
              : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600'
          ]"
          :title="isCollapsed ? 'Relatórios' : ''"
        >
          <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
          <span v-if="!isCollapsed" class="ml-3">Relatórios</span>
        </router-link>

        <!-- Usuários -->
        <router-link
          to="/users"
          class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors group"
          :class="[
            $route.name === 'users' 
              ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-700' 
              : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600'
          ]"
          :title="isCollapsed ? 'Usuários' : ''"
        >
          <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
          </svg>
          <span v-if="!isCollapsed" class="ml-3">Usuários</span>
        </router-link>

        <!-- Separador -->
        <div v-if="!isCollapsed" class="border-t border-gray-200 my-4"></div>

        <!-- Configurações -->
        <router-link
          to="/settings"
          class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors group"
          :class="[
            $route.name === 'settings' 
              ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-700' 
              : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600'
          ]"
          :title="isCollapsed ? 'Configurações' : ''"
        >
          <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          <span v-if="!isCollapsed" class="ml-3">Configurações</span>
        </router-link>
      </nav>

      <!-- Footer com informações do usuário -->
      <div class="border-t border-gray-200 p-4">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center">
              <svg class="h-5 w-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </div>
          </div>
          <div v-if="!isCollapsed" class="ml-3 flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">
              {{ authStore.user?.name || 'Usuário' }}
            </p>
            <p class="text-xs text-gray-500 truncate">
              {{ authStore.user?.email || 'email@exemplo.com' }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Overlay para mobile -->
    <div 
      v-if="showMobileOverlay" 
      @click="closeMobileSidebar"
      class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden"
    ></div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

// Props
interface Props {
  modelValue?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: false
})

// Emits
const emit = defineEmits<{
  'update:modelValue': [value: boolean]
}>()

// State
const isCollapsed = ref(false)
const showMobileOverlay = ref(false)

// Computed
const isMobile = ref(false)

// Methods
const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value
  emit('update:modelValue', isCollapsed.value)
}

const closeMobileSidebar = () => {
  if (isMobile.value) {
    showMobileOverlay.value = false
  }
}

const checkScreenSize = () => {
  isMobile.value = window.innerWidth < 1024
  if (isMobile.value) {
    isCollapsed.value = true
  }
}

// Lifecycle
onMounted(() => {
  checkScreenSize()
  window.addEventListener('resize', checkScreenSize)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkScreenSize)
})
</script>

<style scoped>
/* Animações suaves para transições */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

/* Scrollbar personalizada */
nav::-webkit-scrollbar {
  width: 4px;
}

nav::-webkit-scrollbar-track {
  background: transparent;
}

nav::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 2px;
}

nav::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>