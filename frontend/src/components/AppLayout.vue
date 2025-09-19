<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Navegação Principal -->
    <AppNavigation />
    
    <!-- Conteúdo Principal -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1">
      <slot />
    </main>
    
    <!-- Rodapé -->
    <AppFooter />
  </div>
</template>

<script setup lang="ts">
import AppNavigation from './AppNavigation.vue'
import AppFooter from './AppFooter.vue'
</script>
