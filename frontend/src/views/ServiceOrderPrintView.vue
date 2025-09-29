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

    <!-- Componente de impressão A4 -->
    <ServiceOrderPrint 
      v-if="orderData && !loading && printTemplate === 'a4'" 
      :order-data="orderData" 
    />

    <!-- Componente de impressão Térmica -->
    <ThermalServiceOrderPrint 
      v-if="orderData && !loading && (printTemplate === '58mm' || printTemplate === '80mm')" 
      :order-data="orderData"
      :template="printTemplate"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ServiceOrderPrint from '@/components/ServiceOrderPrint.vue'
import ThermalServiceOrderPrint from '@/components/ThermalServiceOrderPrint.vue'
import { useSettingsStore } from '@/stores/settings'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()
const settingsStore = useSettingsStore()

const orderData = ref(null)
const loading = ref(true)
const error = ref(null)
const isPrinting = ref(false)

// Normaliza o valor do template vindo das configurações/servidor
const normalizeTemplate = (val) => {
  if (!val) return 'a4'
  const s = String(val).toLowerCase()
  if (s.includes('80')) return '80mm'
  if (s.includes('58')) return '58mm'
  if (s.includes('a4')) return 'a4'
  return 'a4'
}

// Obter template de impressão das configurações (normalizado)
const printTemplate = computed(() => normalizeTemplate(settingsStore.systemSettings?.service_order_print_template))

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
  
  // Usar função print com configuração térmica
  print()
  
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

const print = () => {
  // Configurar página para impressão térmica
  if (printTemplate.value === '58mm' || printTemplate.value === '80mm') {
    // Adicionar classe específica ao body para aplicar regras @page
    document.body.classList.add(`template-${printTemplate.value}`)
    
    // Configurar CSS específico para impressão térmica
    const style = document.createElement('style')
    style.textContent = `
      @media print {
        @page {
          size: ${printTemplate.value} auto;
          margin: 0;
        }
        
        body {
          margin: 0 !important;
          padding: 0 !important;
        }
        
        .thermal-service-order-print {
          width: ${printTemplate.value};
          max-width: ${printTemplate.value};
        }
      }
    `
    document.head.appendChild(style)
    
    // Aguardar um momento para aplicar os estilos
    setTimeout(() => {
      window.print()
      
      // Limpar após impressão
      setTimeout(() => {
        document.body.classList.remove(`template-${printTemplate.value}`)
        document.head.removeChild(style)
      }, 1000)
    }, 100)
  } else {
    window.print()
  }
}

onMounted(async () => {
  // Garantir que as configurações estejam carregadas antes de decidir o template
  try {
    await settingsStore.loadSettings()
  } catch (e) {
    // O store já tenta carregar do localStorage em caso de falha
  }
  await loadOrderData()
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