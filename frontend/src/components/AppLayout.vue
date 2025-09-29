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
