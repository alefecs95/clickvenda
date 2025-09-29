<template>
  <div class="service-order-print-view">
    <!-- Botões de ação (não aparecem na impressão) -->
    <div class="print-actions" v-if="!isPrinting">
      <button @click="printOrder" class="btn btn-primary">
        <i class="fas fa-print"></i> Imprimir
      </button>
      <button @click="goBack" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Voltar
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading">
      <p>Carregando ordem de serviço...</p>
    </div>

    <!-- Error -->
    <div v-if="error" class="error">
      <p>Erro ao carregar ordem de serviço: {{ error }}</p>
    </div>

    <!-- Componente de impressão -->
    <ServiceOrderPrint 
      v-if="orderData && !loading" 
      :order-data="orderData" 
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ServiceOrderPrint from '@/components/ServiceOrderPrint.vue'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()

const orderData = ref(null)
const loading = ref(true)
const error = ref(null)
const isPrinting = ref(false)

const loadOrderData = async () => {
  try {
    loading.value = true
    error.value = null
    
    const serviceOrderId = route.params.id
    console.log('Carregando dados da OS:', serviceOrderId)
    
    const response = await api.get(`/service-orders/${serviceOrderId}/print`)
    console.log('Resposta da API:', response)
    console.log('Dados recebidos:', response.data)
    
    // A API retorna { success: true, data: {...} }
    if (response.data.success && response.data.data) {
      orderData.value = response.data.data
      console.log('Dados processados:', orderData.value)
    } else {
      orderData.value = response.data
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Erro ao carregar dados da ordem de serviço'
    console.error('Erro ao carregar ordem de serviço:', err)
    console.error('Detalhes do erro:', err.response)
  } finally {
    loading.value = false
  }
}

const printOrder = () => {
  isPrinting.value = true
  
  // Esconder botões de ação
  const actions = document.querySelector('.print-actions')
  if (actions) {
    actions.style.display = 'none'
  }
  
  // Imprimir
  window.print()
  
  // Restaurar botões após impressão
  setTimeout(() => {
    if (actions) {
      actions.style.display = 'flex'
    }
    isPrinting.value = false
  }, 1000)
}

const goBack = () => {
  router.go(-1)
}

onMounted(() => {
  loadOrderData()
})
</script>

<style scoped>
.service-order-print-view {
  min-height: 100vh;
  background-color: #f5f5f5;
}

.print-actions {
  position: fixed;
  top: 20px;
  right: 20px;
  display: flex;
  gap: 10px;
  z-index: 1000;
  background: white;
  padding: 10px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: background-color 0.2s;
}

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-primary:hover {
  background-color: #0056b3;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background-color: #545b62;
}

.loading, .error {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 50vh;
  font-size: 16px;
}

.error {
  color: #dc3545;
}

/* Estilos para impressão */
@media print {
  .print-actions {
    display: none !important;
  }
  
  .service-order-print-view {
    background: white;
  }
  
  body {
    margin: 0;
    padding: 0;
  }
}
</style>