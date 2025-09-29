<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Layout com Sidebar (exceto PDV) -->
    <div v-if="!isPDVPage" class="flex">
      <!-- Sidebar -->
      <AppSidebar v-model="sidebarCollapsed" />
      
      <!-- Conteúdo Principal -->
      <div 
        class="flex-1 flex flex-col min-h-screen transition-all duration-300 ease-in-out"
        :class="[
          sidebarCollapsed ? 'ml-16' : 'ml-64'
        ]"
      >
        <!-- Header com toggle do sidebar -->
        <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <button
                @click="toggleSidebar"
                class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors lg:hidden"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
              </button>
              <h1 class="text-xl font-semibold text-gray-900">
                {{ getPageTitle() }}
              </h1>
            </div>
            
            <!-- User Menu -->
            <div class="flex items-center space-x-4">
              <div class="relative">
                <button
                  @click="toggleUserMenu"
                  class="flex items-center space-x-2 text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                  <div class="h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center">
                    <svg class="h-5 w-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                  </div>
                  <span class="hidden sm:inline">{{ authStore.user?.name || 'Usuário' }}</span>
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </button>
                
                <!-- Dropdown do Usuário -->
                <div
                  v-if="showUserMenu"
                  class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200"
                >
                  <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    Meu Perfil
                  </a>
                  <router-link to="/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    Configurações
                  </router-link>
                  <hr class="my-1">
                  <button
                    @click="logout"
                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  >
                    Sair
                  </button>
                </div>
              </div>
            </div>
          </div>
        </header>
        
        <!-- Conteúdo da Página -->
        <main class="flex-1 p-6">
          <slot />
        </main>
        
        <!-- Rodapé -->
        <AppFooter />
      </div>
    </div>
    
    <!-- Layout PDV (sem sidebar) -->
    <div v-else class="flex flex-col min-h-screen">
      <!-- Navegação Principal para PDV -->
      <AppNavigation />
      
      <!-- Conteúdo Principal PDV -->
      <main class="flex-1">
        <slot />
      </main>
      
      <!-- Rodapé PDV -->
      <AppFooter />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppSidebar from './AppSidebar.vue'
import AppNavigation from './AppNavigation.vue'
import AppFooter from './AppFooter.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

// State
const sidebarCollapsed = ref(false)
const showUserMenu = ref(false)

// Computed
const isPDVPage = computed(() => {
  return route.name === 'sales' || route.path === '/sales'
})

// Methods
const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value
}

const getPageTitle = () => {
  const titles: Record<string, string> = {
    'dashboard': 'Dashboard',
    'products': 'Produtos',
    'vasilhames': 'Vasilhames',
    'customers': 'Clientes',
    'vehicles': 'Veículos',
    'orders': 'Pedidos',
    'service-orders': 'Ordens de Serviço',
    'receivables': 'Contas a Receber',
    'financial': 'Financeiro',
    'reports': 'Relatórios',
    'users': 'Usuários',
    'settings': 'Configurações'
  }
  
  return titles[route.name as string] || 'ClickVenda'
}

const logout = async () => {
  try {
    await authStore.logout()
    router.push('/login')
  } catch (error) {
    console.error('Erro ao fazer logout:', error)
  }
}

// Close user menu when clicking outside
const handleClickOutside = (event: Event) => {
  const target = event.target as HTMLElement
  if (!target.closest('.relative')) {
    showUserMenu.value = false
  }
}

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
