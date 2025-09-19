<template>
  <footer class="bg-white border-t border-gray-200 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
        <!-- Direitos Reservados -->
        <div class="text-center md:text-left">
          <p class="text-sm text-gray-600">
            © {{ currentYear }} ClickVenda. Todos os direitos reservados.
          </p>
        </div>
        
        <!-- Créditos do Desenvolvedor -->
        <div class="text-center md:text-right">
          <p class="text-sm text-gray-600">
            Solução desenvolvida por 
            <span class="font-semibold text-primary-600">Alefe Carvalho</span>
          </p>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const currentYear = computed(() => new Date().getFullYear())
</script>