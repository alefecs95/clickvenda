<template>
  <div class="service-order-print" v-if="orderData">
    <!-- Cabeçalho da Empresa -->
    <div class="header">
      <div class="company-info">
        <h1 class="company-name">{{ orderData.company?.name || 'Laravel' }}</h1>
        <div class="company-details">
          <p>{{ orderData.company?.address || 'Endereço da empresa' }}</p>
          <p>{{ orderData.company?.phone || 'Telefones: telefone da empresa' }}</p>
          <p>{{ orderData.company?.email || 'E-mail: email@empresa.com' }}</p>
        </div>
      </div>
      <div class="order-title">
        <h2>Ordem de Serviço</h2>
        <p class="order-number">Nº {{ orderData.order_info?.number || '---' }}</p>
        <p class="order-date">Data: {{ orderData.order_info?.opening_date || new Date().toLocaleDateString('pt-BR') }}</p>
      </div>
    </div>

    <!-- Informações do Cliente -->
    <div class="section">
      <div class="section-row">
        <div class="field">
          <label>Cliente:</label>
          <span>{{ orderData.customer?.name || '---' }}</span>
        </div>
        <div class="field">
          <label>CPF/CNPJ:</label>
          <span>{{ orderData.customer?.document || '---' }}</span>
        </div>
        <div class="field">
          <label>Celular:</label>
          <span>{{ orderData.customer?.phone || '---' }}</span>
        </div>
      </div>
      <div class="section-row">
        <div class="field full-width">
          <label>Endereço:</label>
          <span>{{ formatAddress(orderData.customer) }}</span>
        </div>
      </div>
      <div class="section-row">
        <div class="field vehicle-field">
          <label>Veículo:</label>
          <span>{{ orderData.vehicle?.brand || '---' }} {{ orderData.vehicle?.model || '' }}</span>
        </div>
        <div class="field">
          <label>Placa:</label>
          <span>{{ orderData.vehicle?.plate || '---' }}</span>
        </div>
        <div class="field">
          <label>Km Atual:</label>
          <span>{{ orderData.vehicle?.mileage ? formatNumber(orderData.vehicle.mileage) : '---' }}</span>
        </div>
        <div class="field">
          <label>Ano:</label>
          <span>{{ orderData.vehicle?.year || '---' }}</span>
        </div>
      </div>
      <div class="section-row">
        <div class="field chassis-field">
          <label>Chassi:</label>
          <span>{{ orderData.vehicle?.chassis || '---' }}</span>
        </div>
      </div>
    </div>

    <!-- Produtos -->
    <div class="section">
      <h3>Produtos</h3>
      <table class="items-table">
        <thead>
          <tr>
            <th>Código</th>
            <th>Referência</th>
            <th>Descrição</th>
            <th>Qtd</th>
            <th>Vr. Unitário</th>
            <th>Vr. Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in (orderData.items?.products || [])" :key="product.code">
            <td>{{ product.code }}</td>
            <td>{{ product.code }}</td>
            <td>{{ product.description }}</td>
            <td>{{ product.quantity }}</td>
            <td>{{ formatCurrency(product.unit_price) }}</td>
            <td>{{ formatCurrency(product.total_price) }}</td>
          </tr>
          <tr v-if="!orderData.items?.products || orderData.items.products.length === 0">
            <td colspan="6" class="no-items">Nenhum produto</td>
          </tr>
        </tbody>
      </table>
      <div class="subtotal">
        <strong>Produtos: {{ formatCurrency(getProductsTotal()) }}</strong>
      </div>
    </div>

    <!-- Serviços -->
    <div class="section">
      <h3>Serviços</h3>
      <table class="items-table">
        <thead>
          <tr>
            <th>Código</th>
            <th>Referência</th>
            <th>Descrição</th>
            <th>Qtd</th>
            <th>Vr. Unitário</th>
            <th>Vr. Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="service in (orderData.items?.services || [])" :key="service.code">
            <td>{{ service.code }}</td>
            <td>{{ service.code }}</td>
            <td>{{ service.description }}</td>
            <td>{{ service.quantity }}</td>
            <td>{{ formatCurrency(service.unit_price) }}</td>
            <td>{{ formatCurrency(service.total_price) }}</td>
          </tr>
          <tr v-if="!orderData.items?.services || orderData.items.services.length === 0">
            <td colspan="6" class="no-items">Nenhum serviço</td>
          </tr>
        </tbody>
      </table>
      <div class="subtotal">
        <strong>Serviços: {{ formatCurrency(getServicesTotal()) }}</strong>
      </div>
    </div>

    <!-- Totais -->
    <div class="section totals-section">
      <div class="totals-grid">
        <div class="total-row">
          <span>Total Venda:</span>
          <span>{{ formatCurrency(orderData.totals?.final_amount || 0) }}</span>
        </div>
      </div>
    </div>

    <!-- Pagamentos -->
    <div class="section">
      <h3>Pagamentos</h3>
      <table class="payments-table">
        <thead>
          <tr>
            <th>Data</th>
            <th>Forma de Pagamento</th>
            <th>$ Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="payment in (orderData.payments || [])" :key="payment.date + payment.method">
            <td>{{ payment.date }}</td>
            <td>{{ payment.method }}</td>
            <td>{{ formatCurrency(payment.amount) }}</td>
          </tr>
          <tr v-if="!orderData.payments || orderData.payments.length === 0">
            <td colspan="5" class="no-items">Nenhum pagamento registrado</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Técnico Responsável -->
    <div class="section">
      <h3>Técnico Responsável</h3>
      <div class="consultant-info">
        <p><strong>Responsável:</strong> {{ orderData.technical_responsible?.name || '---' }}</p>
      </div>
    </div>

    <!-- Garantias -->
    <div class="section warranty-section">
      <h3>Garantias</h3>
      <div class="warranty-info">
        <div class="warranty-item">
          <strong>Produtos:</strong> 
          <span v-if="orderData.warranty?.products_days && orderData.warranty?.products_km && orderData.warranty.products_km > 0">
            {{ orderData.warranty.products_days }} dias ou {{ orderData.warranty.products_km }} km
          </span>
          <span v-else-if="orderData.warranty?.products_days && (!orderData.warranty?.products_km || orderData.warranty.products_km <= 0)">
            {{ orderData.warranty.products_days }} dias
          </span>
          <span v-else-if="orderData.warranty?.products_km && orderData.warranty.products_km > 0 && (!orderData.warranty?.products_days || orderData.warranty.products_days <= 0)">
            {{ orderData.warranty.products_km }} km
          </span>
          <span v-else>90 dias</span>
          de garantia
        </div>
        <div class="warranty-item">
          <strong>Serviços:</strong> 
          <span v-if="orderData.warranty?.services_days && orderData.warranty?.services_km && orderData.warranty.services_km > 0">
            {{ orderData.warranty.services_days }} dias ou {{ orderData.warranty.services_km }} km
          </span>
          <span v-else-if="orderData.warranty?.services_days && (!orderData.warranty?.services_km || orderData.warranty.services_km <= 0)">
            {{ orderData.warranty.services_days }} dias
          </span>
          <span v-else-if="orderData.warranty?.services_km && orderData.warranty.services_km > 0 && (!orderData.warranty?.services_days || orderData.warranty.services_days <= 0)">
            {{ orderData.warranty.services_km }} km
          </span>
          <span v-else>30 dias</span>
          de garantia
        </div>
        <div class="warranty-terms">
          <p><strong>Condições da Garantia:</strong></p>
          <ul>
            <li>A garantia é válida apenas para defeitos de fabricação ou mão de obra</li>
            <li>Não cobre danos causados por mau uso, acidentes ou desgaste natural</li>
            <li>Para acionamento da garantia, apresentar esta ordem de serviço</li>
            <li>A garantia não cobre peças de desgaste natural (filtros, correias, etc.)</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Observações -->
    <div class="section">
      <h3>Observações</h3>
      <div class="observations">
        <div v-if="orderData.description?.problem" class="observation-item">
          <strong>Problema Relatado:</strong> {{ orderData.description.problem }}
        </div>
        <div v-if="orderData.description?.diagnosis" class="observation-item">
          <strong>Diagnóstico:</strong> {{ orderData.description.diagnosis }}
        </div>
        <div v-if="orderData.description?.notes" class="observation-item">
          <strong>Observações:</strong> {{ orderData.description.notes }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  orderData: {
    type: Object,
    required: true
  }
})

const formatCurrency = (value) => {
  // Verificar se o valor é válido
  if (value === null || value === undefined || isNaN(value)) return 'R$ 0,00'
  
  const numericValue = parseFloat(value)
  if (isNaN(numericValue)) return 'R$ 0,00'
  
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(numericValue)
}

const formatAddress = (customer) => {
  if (!customer) return '---'
  const parts = [
    customer.address,
    customer.city,
    customer.state,
    customer.zip_code
  ].filter(Boolean)
  return parts.length > 0 ? parts.join(', ') : '---'
}

const formatNumber = (value) => {
  if (value === null || value === undefined || isNaN(value)) return '---'
  
  const numericValue = parseFloat(value)
  if (isNaN(numericValue)) return '---'
  
  return new Intl.NumberFormat('pt-BR').format(numericValue)
}

const getProductsTotal = () => {
  if (!props.orderData?.items?.products || !Array.isArray(props.orderData.items.products)) return 0
  
  const total = props.orderData.items.products.reduce((total, item) => {
    const itemTotal = parseFloat(item.total_price || 0)
    return total + (isNaN(itemTotal) ? 0 : itemTotal)
  }, 0)
  
  return isNaN(total) ? 0 : total
}

const getServicesTotal = () => {
  if (!props.orderData?.items?.services || !Array.isArray(props.orderData.items.services)) return 0
  
  const total = props.orderData.items.services.reduce((total, item) => {
    const itemTotal = parseFloat(item.total_price || 0)
    return total + (isNaN(itemTotal) ? 0 : itemTotal)
  }, 0)
  
  return isNaN(total) ? 0 : total
}
</script>

<style scoped>
.service-order-print {
  width: 210mm;
  max-width: 210mm;
  font-family: Arial, sans-serif;
  font-size: 10px;
  line-height: 1.2;
  color: #000;
  background: white;
  padding: 10mm;
  margin: 0 auto;
  box-sizing: border-box;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 8px;
  padding-bottom: 6px;
  border-bottom: 2px solid #000;
}

.company-info {
  flex: 1;
}

.company-name {
  font-size: 16px;
  font-weight: bold;
  margin: 0 0 4px 0;
}

.company-details p {
  margin: 1px 0;
  font-size: 9px;
}

.order-title {
  text-align: right;
}

.order-title h2 {
  font-size: 16px;
  margin: 0 0 3px 0;
  font-weight: bold;
}

.order-number, .order-date {
  margin: 1px 0;
  font-size: 10px;
}

.section {
  margin-bottom: 8px;
  border-radius: 0;
}

.section h3 {
  background: #f0f0f0;
  padding: 4px 6px;
  margin: 0 0 4px 0;
  font-size: 11px;
  font-weight: bold;
  border: 1px solid #000;
  border-radius: 0;
  color: #000;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.section-row {
  display: flex;
  gap: 10px;
  margin-bottom: 3px;
  align-items: center;
}

.field {
  display: flex;
  align-items: center;
  gap: 3px;
  min-width: 120px;
}

.field.full-width {
  flex: 1;
  min-width: auto;
}

.chassis-field {
  flex: 1;
  min-width: auto;
  max-width: 100%;
}

.chassis-field span {
  word-break: break-all;
  overflow-wrap: break-word;
  white-space: normal;
  line-height: 1.1;
}

.field label {
  font-weight: bold;
  white-space: nowrap;
  font-size: 9px;
}

.field span {
  border-bottom: 1px solid #000;
  padding: 1px 3px;
  min-height: 12px;
  flex: 1;
  font-size: 9px;
}

.items-table, .payments-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 4px;
  border-radius: 0;
  overflow: visible;
  box-shadow: none;
}

.items-table th, .items-table td,
.payments-table th, .payments-table td {
  border: 1px solid #000;
  padding: 3px 4px;
  text-align: left;
  font-size: 9px;
}

.items-table th, .payments-table th {
  background: #f0f0f0;
  font-weight: bold;
  font-size: 9px;
  color: #000;
  text-transform: uppercase;
  letter-spacing: 0.2px;
}

.items-table tbody tr:nth-child(even) {
  background-color: #f8f8f8;
}

.items-table tbody tr:hover {
  background-color: transparent;
}

.payments-table tbody tr:nth-child(even) {
  background-color: #f8f8f8;
}

.items-table td, .payments-table td {
  font-size: 9px;
}

.items-table th:nth-child(4),
.items-table td:nth-child(4),
.items-table th:nth-child(5),
.items-table td:nth-child(5),
.items-table th:nth-child(6),
.items-table td:nth-child(6) {
  text-align: right;
  width: 60px;
}

.no-items {
  text-align: center;
  font-style: italic;
  color: #666;
  font-size: 9px;
}

.subtotal {
  text-align: right;
  margin-top: 3px;
  padding: 3px 6px;
  background: #f0f0f0;
  border: 1px solid #000;
  border-radius: 0;
  font-weight: bold;
  color: #000;
  font-size: 9px;
}

.totals-section {
  border: 2px solid #000;
  padding: 6px;
  background: #f8f8f8;
  border-radius: 0;
  box-shadow: none;
}

.totals-grid {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.total-row {
  display: flex;
  justify-content: space-between;
  font-size: 11px;
  font-weight: bold;
  color: #000;
  padding: 2px 0;
  border-bottom: 1px solid #ccc;
}

.total-row:last-child {
  border-bottom: none;
  font-size: 12px;
  color: #000;
}

.consultant-info p {
  margin: 2px 0;
  font-size: 9px;
}

/* Estilos para seção de garantias - compacta para P&B */
.warranty-section {
  border: 1px solid #000;
  padding: 6px;
  background-color: #f8f8f8;
}

.warranty-info {
  margin-top: 4px;
}

.warranty-item {
  margin: 3px 0;
  padding: 2px;
  background-color: #fff;
  border-left: 2px solid #000;
  font-size: 9px;
}

.warranty-terms {
  margin-top: 6px;
  padding: 4px;
  background-color: #fff;
  border: 1px solid #000;
}

.warranty-terms p {
  margin: 0 0 3px 0;
  font-weight: bold;
  font-size: 9px;
}

.warranty-terms ul {
  margin: 0;
  padding-left: 12px;
}

.warranty-terms li {
  margin: 1px 0;
  font-size: 8px;
  line-height: 1.2;
}

.observations {
  padding: 4px;
  border: 1px solid #000;
  background-color: #f8f8f8;
}

.observation-item {
  margin-bottom: 3px;
  font-size: 9px;
}

/* Estilos para impressão */
@media print {
  .service-order-print {
    width: auto;
    margin: 0;
    padding: 15mm;
    box-shadow: none;
  }
  
  .section {
    page-break-inside: avoid;
    margin-bottom: 15px;
  }
  
  .items-table {
    page-break-inside: auto;
  }
  
  .items-table tr {
    page-break-inside: avoid;
  }
  
  .warranty-section {
    page-break-inside: avoid;
  }
  
  /* Remove efeitos visuais na impressão */
  .items-table, .payments-table {
    box-shadow: none;
  }
  
  .totals-section {
    box-shadow: none;
  }
  
  .items-table tbody tr:hover {
    background-color: transparent;
  }
}

@media screen {
  .service-order-print {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    margin: 20px auto;
    border-radius: 8px;
    overflow: hidden;
  }
}
</style>