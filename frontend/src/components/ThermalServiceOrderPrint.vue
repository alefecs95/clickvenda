<template>
  <div class="thermal-service-order-print" :class="templateClass">
    <!-- Modelo 58mm -->
    <div v-if="template === '58mm'" class="service-order-58mm">
      <div class="header">
        <div class="company-name">{{ orderData.company?.name || 'NOME LOJA' }}</div>
        <div v-if="orderData.company?.address" class="company-address">{{ orderData.company.address }}</div>
        <div v-if="orderData.company?.phone" class="company-phone">Tel: {{ orderData.company.phone }}</div>
      </div>
      
      <div class="divider"></div>
      
      <div class="order-title">
        <div class="title">ORDEM DE SERVIÇO</div>
        <div class="order-details">
          <div class="order-number">Nº {{ orderData.order_info?.number || orderData.order_number || orderData.id || '---' }}</div>
          <div class="order-date">{{ orderData.order_info?.opening_date || formatDate(new Date()) }}</div>
        </div>
      </div>
      
      <div class="divider"></div>
      
      <div class="customer-section">
        <div class="section-title">CLIENTE:</div>
        <div class="customer-name">{{ orderData.customer?.name || '---' }}</div>
        <div v-if="orderData.customer?.document" class="customer-document">CPF/CNPJ: {{ orderData.customer.document }}</div>
        <div v-if="orderData.customer?.phone" class="customer-phone">Tel: {{ orderData.customer.phone }}</div>
      </div>
      
      <div class="divider"></div>
      
      <div class="vehicle-section">
        <div class="section-title">VEÍCULO/EQUIPAMENTO:</div>
        <div class="vehicle-info">
          <div class="vehicle-model">{{ orderData.vehicle?.model || '---' }}</div>
          <div v-if="orderData.vehicle?.plate" class="vehicle-plate">Placa: {{ orderData.vehicle.plate }}</div>
          <div v-if="orderData.vehicle?.year" class="vehicle-year">Ano: {{ orderData.vehicle.year }}</div>
        </div>
      </div>
      
      <div class="divider"></div>
      
      <div class="problem-section">
        <div class="section-title">PROBLEMA RELATADO:</div>
        <div class="problem-text">{{ orderData.problem_description || 'Não informado' }}</div>
      </div>
      
      <div v-if="orderData.diagnosis" class="diagnosis-section">
        <div class="section-title">DIAGNÓSTICO:</div>
        <div class="diagnosis-text">{{ orderData.diagnosis }}</div>
      </div>
      
      <div v-if="orderData.items?.products?.length || orderData.items?.services?.length" class="items-section">
        <div class="section-title">ITENS E SERVIÇOS:</div>
        
        <!-- Produtos -->
        <div v-if="orderData.items?.products?.length" class="products">
         <!-- <div class="subsection-title">PRODUTOS:</div>-->
          <div class="items-table">
            <div class="table-header">
              <span class="col-product title1">ITENS</span>
              <span class="col-separator">|</span>
              <span class="col-unit-price">VALOR</span>
              <span class="col-separator">|</span>
              <span class="col-total">TOTAL</span>
            </div>
            <div v-for="item in orderData.items.products" :key="item.id" class="table-row">
              <span class="col-product">{{ item.quantity }} X {{ item.description }}</span>
              <span class="col-separator">|</span>
              <span class="col-unit-price">{{ formatPrice(item.unit_price) }}</span>
              <span class="col-separator">|</span>
              <span class="col-total">{{ formatPrice(item.total_price) }}</span>
            </div>
          </div>
        </div>
        
        <!-- Serviços -->
        <div v-if="orderData.items?.services?.length" class="services">
           <!-- <div class="subsection-title">SERVIÇOS:</div> -->
          <div class="items-table">
            <div class="table-header">
              <span class="col-product title1">SERVIÇOS</span>
              <span class="col-separator">|</span>
              <span class="col-unit-price">VALOR</span>
              <span class="col-separator">|</span>
              <span class="col-total">TOTAL</span>
            </div>
            <div v-for="item in orderData.items.services" :key="item.id" class="table-row">
              <span class="col-product">{{ item.quantity }} X {{ item.description }}</span>
              <span class="col-separator">|</span>
              <span class="col-unit-price">{{ formatPrice(item.unit_price) }}</span>
              <span class="col-separator">|</span>
              <span class="col-total">{{ formatPrice(item.total_price) }}</span>
            </div>
          </div>
        </div>
      </div>
      
      <div class="divider"></div>
      
      <div class="totals-section">
        <div class="totals-grid">
          <div v-if="orderData.totals?.products_total > 0" class="total-row">
            <span>Produtos:</span>
            <span>R$ {{ formatPrice(orderData.totals.products_total) }}</span>
          </div>
          <div v-if="orderData.totals?.services_total > 0" class="total-row">
            <span>Serviços:</span>
            <span>R$ {{ formatPrice(orderData.totals.services_total) }}</span>
          </div>
          <div class="total-row final-total">
            <span>TOTAL GERAL:</span>
            <span>R$ {{ formatPrice(orderData.totals?.final_amount || orderData.final_amount || 0) }}</span>
          </div>
        </div>
      </div>
      
      <div class="divider"></div>
      
      <div class="footer-section">
        <div class="status-info">
          <div class="status">Status: {{ orderData.order_info?.status || 'Concluída' }}</div>
          <div class="responsible">Responsável: {{ orderData.technical_responsible?.name || 'fran' }}</div>
        </div>
        
        <div class="warranty-info">
          <div class="warranty-title">GARANTIA:</div>
          <div class="warranty-products">
            Produtos: 30 dias
          </div>
          <div class="warranty-services">
            Serviços: 30 dias
          </div>
        </div>
        
        <div class="print-info">
          <div class="print-date">Impresso em: {{ formatDate(new Date()) }} {{ new Date().toLocaleTimeString('pt-BR') }}</div>
        </div>
      </div>
    </div>

    <!-- Modelo 80mm -->
    <div v-else-if="template === '80mm'" class="service-order-80mm">
      <div class="header">
        <div class="company-name">{{ orderData.company?.name || 'EMPRESA' }}</div>
        <div v-if="orderData.company?.address" class="company-address">{{ orderData.company.address }}</div>
        <div v-if="orderData.company?.phone" class="company-phone">Tel: {{ orderData.company.phone }}</div>
      </div>
      
      <div class="divider"></div>
      
      <div class="order-title">
        <div class="title">ORDEM DE SERVIÇO</div>
        <div class="order-details">
          <span class="order-number">Nº {{ orderData.order_info?.number || '---' }}</span>
          <span class="order-date">{{ orderData.order_info?.opening_date || formatDate(new Date()) }}</span>
        </div>
      </div>
      
      <div class="divider"></div>
      
      <div class="customer-section">
        <div class="section-title">CLIENTE:</div>
        <div class="customer-name">{{ orderData.customer?.name || '---' }}</div>
        <div v-if="orderData.customer?.document" class="customer-document">CPF/CNPJ: {{ orderData.customer.document }}</div>
        <div v-if="orderData.customer?.phone" class="customer-phone">Tel: {{ orderData.customer.phone }}</div>
      </div>
      
      <div class="divider"></div>
      
      <div class="vehicle-section">
        <div class="section-title">VEÍCULO/EQUIPAMENTO:</div>
        <div class="vehicle-info">
          <div class="vehicle-model">{{ orderData.vehicle?.model || '---' }}</div>
          <div v-if="orderData.vehicle?.plate" class="vehicle-plate">Placa: {{ orderData.vehicle.plate }}</div>
          <div v-if="orderData.vehicle?.year" class="vehicle-year">Ano: {{ orderData.vehicle.year }}</div>
        </div>
      </div>
      
      <div class="divider"></div>
      
      <div class="problem-section">
        <div class="section-title">PROBLEMA RELATADO:</div>
        <div class="problem-text">{{ orderData.problem_description || 'Não informado' }}</div>
      </div>
      
      <div v-if="orderData.diagnosis" class="diagnosis-section">
        <div class="section-title">DIAGNÓSTICO:</div>
        <div class="diagnosis-text">{{ orderData.diagnosis }}</div>
      </div>
      
      <div v-if="orderData.items?.products?.length || orderData.items?.services?.length" class="items-section">
        <div class="section-title">ITENS E SERVIÇOS:</div>
        
        <!-- Produtos -->
        <div v-if="orderData.items?.products?.length" class="products">
          <div class="subsection-title">PRODUTOS:</div>
          <div class="items-table">
            <div class="table-header">
              <span class="col-desc">DESCRIÇÃO</span>
              <span class="col-qty">QTD</span>
              <span class="col-price">VALOR</span>
              <span class="col-total">TOTAL</span>
            </div>
            <div v-for="item in orderData.items.products" :key="item.id" class="table-row">
              <span class="col-desc">{{ item.description }}</span>
              <span class="col-qty">{{ item.quantity }}</span>
              <span class="col-price">{{ formatPrice(item.unit_price) }}</span>
              <span class="col-total">{{ formatPrice(item.total_price) }}</span>
            </div>
          </div>
        </div>
        
        <!-- Serviços -->
        <div v-if="orderData.items?.services?.length" class="services">
          <div class="subsection-title">SERVIÇOS:</div>
          <div class="items-table">
            <div class="table-header">
              <span class="col-desc">DESCRIÇÃO</span>
              <span class="col-qty">QTD</span>
              <span class="col-price">VALOR</span>
              <span class="col-total">TOTAL</span>
            </div>
            <div v-for="item in orderData.items.services" :key="item.id" class="table-row">
              <span class="col-desc">{{ item.description }}</span>
              <span class="col-qty">{{ item.quantity }}</span>
              <span class="col-price">{{ formatPrice(item.unit_price) }}</span>
              <span class="col-total">{{ formatPrice(item.total_price) }}</span>
            </div>
          </div>
        </div>
      </div>
      
      <div class="divider"></div>
      
      <div class="totals-section">
        <div class="totals-grid">
          <div v-if="orderData.totals?.products_total > 0" class="total-row">
            <span>Produtos:</span>
            <span>R$ {{ formatPrice(orderData.totals.products_total) }}</span>
          </div>
          <div v-if="orderData.totals?.services_total > 0" class="total-row">
            <span>Serviços:</span>
            <span>R$ {{ formatPrice(orderData.totals.services_total) }}</span>
          </div>
          <div class="total-row final-total">
            <span>TOTAL GERAL:</span>
            <span>R$ {{ formatPrice(orderData.totals?.final_amount || orderData.final_amount || 0) }}</span>
          </div>
        </div>
      </div>
      
      <div class="divider"></div>
      
      <div class="footer-section">
        <div class="status-info">
          <div class="status">Status: {{ orderData.order_info?.status || 'Aberta' }}</div>
          <div class="responsible">Responsável: {{ orderData.technical_responsible?.name || '---' }}</div>
        </div>
        
        <div v-if="orderData.warranty" class="warranty-info">
          <div class="warranty-title">GARANTIA:</div>
          <div v-if="orderData.warranty.products_days" class="warranty-products">
            Produtos: {{ orderData.warranty.products_days }} dias
          </div>
          <div v-if="orderData.warranty.services_days" class="warranty-services">
            Serviços: {{ orderData.warranty.services_days }} dias
          </div>
        </div>
        
        <div class="print-info">
          Impresso em: {{ formatDateTime(new Date()) }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface OrderData {
  company?: {
    name: string
    address?: string
    phone?: string
  }
  order_info?: {
    number: string
    opening_date: string
    status: string
  }
  customer?: {
    name: string
    document?: string
    phone?: string
  }
  vehicle?: {
    model: string
    plate?: string
    year?: string
  }
  problem_description?: string
  diagnosis?: string
  items?: {
    products?: Array<{
      id: number
      description: string
      quantity: number
      unit_price: number
      total_price: number
    }>
    services?: Array<{
      id: number
      description: string
      quantity: number
      unit_price: number
      total_price: number
    }>
  }
  totals?: {
    products_total: number
    services_total: number
    final_total: number
  }
  technical_responsible?: {
    name: string
  }
  warranty?: {
    products_days?: number
    services_days?: number
  }
}

interface Props {
  template: '58mm' | '80mm'
  orderData: OrderData
}

const props = defineProps<Props>()

const templateClass = computed(() => {
  return `template-${props.template}`
})

// Converte valores diversos (string com moeda, null, undefined) em número seguro
const parseToNumber = (value: any): number => {
  if (value === null || value === undefined) return 0
  // Normaliza strings como "R$ 1.234,56" para "1234.56"
  let str = String(value)
  // Remove símbolos não numéricos exceto dígitos, vírgula, ponto e sinal
  str = str.replace(/[^0-9,.-]/g, '')
  // Se tem vírgula como decimal, remover pontos de milhar e trocar vírgula por ponto
  if (str.includes(',')) {
    str = str.replace(/\./g, '').replace(',', '.')
  }
  const n = parseFloat(str)
  return Number.isFinite(n) ? n : 0
}

const formatPrice = (price: any): string => {
  const n = parseToNumber(price)
  return n.toFixed(2).replace('.', ',')
}

const formatDate = (date: Date): string => {
  return date.toLocaleDateString('pt-BR')
}

const formatDateTime = (date: Date): string => {
  return `${date.toLocaleDateString('pt-BR')} ${date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })}`
}
</script>

<style scoped>
.thermal-service-order-print {
  font-family: 'Courier New', monospace;
  background: white;
  color: black;
  margin: 0 auto;
  padding-right: 2mm; /* Margem extra na direita para 58mm */
}

/* Estilos para 58mm */
.template-58mm .service-order-58mm {
  width: 50mm;
  max-width: 50mm;
  font-size: 14px;
  line-height: 1.3;
  padding: 0.1mm;
  margin: 0;
  box-sizing: border-box;
}

.template-58mm .header {
  text-align: center;
  margin-bottom: 2px;
}

.template-58mm .company-name {
  font-weight: bold;
  font-size: 16px;
  margin-bottom: 0px;
}

.template-58mm .company-address {
  font-size: 14px;
  margin-bottom: 0px;
}

.template-58mm .company-phone {
  font-size: 14px;
}

.template-58mm .divider {
  border-top: 1px solid #000;
  margin: 1px 0;
}

.template-58mm .order-title {
  text-align: center;
  margin-bottom: 2px;
}

.template-58mm .title {
  font-weight: bold;
  font-size: 15px;
  margin-bottom: 0px;
}

.template-58mm .order-details {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
}

.template-58mm .order-number,
.template-58mm .order-date {
  font-size: 14px;
  margin-bottom: 0px;
}

.template-58mm .customer-section,
.template-58mm .vehicle-section,
.template-58mm .problem-section,
.template-58mm .diagnosis-section {
  margin-bottom: 2px;
}

.template-58mm .section-title {
  font-weight: bold;
  font-size: 14px;
  margin-bottom: 1px;
}
.template-58mm .title1 {
  font-weight: bold;
  font-size: 12px;
  margin-bottom: 1px;
}

.template-58mm .customer-name,
.template-58mm .customer-document,
.template-58mm .customer-phone,
.template-58mm .vehicle-model,
.template-58mm .vehicle-plate,
.template-58mm .vehicle-year,
.template-58mm .problem-text,
.template-58mm .diagnosis-text {
  font-size: 14px;
  line-height: 1.2;
  margin-bottom: 0px;
}

.template-58mm .items-section {
  margin-bottom: 2px;
}

.template-58mm .subsection-title {
  font-weight: bold;
  font-size: 14px;
  margin-bottom: 1px;
}

.template-58mm .items-table {
  margin-bottom: 2px;
}

.template-58mm .table-header,
.template-58mm .table-row {
  display: flex;
  font-size: 12px;
  padding: 0px;
  line-height: 1.1;
}

.template-58mm .table-header {
  font-weight: bold;
  border-bottom: 1px solid #000;
  margin-bottom: 1px;
}

.template-58mm .col-separator {
  flex: 0.1;
  text-align: center;
  font-weight: bold;
  color: #333;
}

.template-58mm .col-product {
  flex: 2.5;
  text-align: left;
  padding-right: 2px;
}

.template-58mm .col-unit-price,
.template-58mm .col-total {
  flex: 0.8;
  text-align: right;
}

.template-58mm .totals-section {
  margin-bottom: 2px;
}

.template-58mm .totals-grid {
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.template-58mm .total-row {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
}

.template-58mm .final-total {
  font-weight: bold;
  font-size: 15px;
  border-top: 1px solid #000;
  padding-top: 1px;
  margin-top: 2px;
}

.template-58mm .footer-section {
  margin-top: 2px;
}

.template-58mm .status-info {
  margin-bottom: 2px;
}

.template-58mm .status,
.template-58mm .responsible {
  font-size: 14px;
  margin-bottom: 1px;
}

.template-58mm .warranty-info {
  margin-bottom: 2px;
}

.template-58mm .warranty-title {
  font-weight: bold;
  font-size: 14px;
  margin-bottom: 1px;
}

.template-58mm .warranty-products,
.template-58mm .warranty-services {
  font-size: 14px;
  margin-bottom: 0px;
}

.template-58mm .print-info {
  margin-top: 2px;
}

.template-58mm .print-date {
  font-size: 12px;
  text-align: center;
}

.template-58mm .company-phone {
  font-size: 16px; /* Aumentado para 16px (12pt) */
}

.template-58mm .divider {
  border-top: 1px solid #000;
  margin: 1px 0;
}

.template-58mm .order-title {
  text-align: center;
  margin-bottom: 2px;
}

.template-58mm .title {
  font-weight: bold;
  font-size: 17px; /* Aumentado para 17px */
  margin-bottom: 0px;
}

.template-58mm .order-number,
.template-58mm .order-date {
  font-size: 16px; /* Aumentado para 16px (12pt) */
  margin-bottom: 0px;
}

.template-58mm .customer-info,
.template-58mm .vehicle-info {
  margin-bottom: 2px;
  font-size: 16px; /* Aumentado para 16px (12pt) */
}

.template-58mm .customer-name,
.template-58mm .vehicle-model {
  font-weight: bold;
  margin-bottom: 0px;
}

.template-58mm .problem {
  margin-bottom: 2px;
}

.template-58mm .section-title {
  font-weight: bold;
  font-size: 16px; /* Aumentado para 16px (12pt) */
  margin-bottom: 1px;
}

.template-58mm .problem-text {
  font-size: 16px; /* Aumentado para 16px (12pt) */
  line-height: 1.2;
}

.template-58mm .items {
  margin-bottom: 3px;
}

.template-58mm .item {
  margin-bottom: 2px;
  font-size: 16px; /* Aumentado para 16px (12pt) */
}

.template-58mm .item-name {
  font-weight: bold;
  margin-bottom: 0px;
}

.template-58mm .item-details,
.template-58mm .item-total {
  font-size: 16px; /* Aumentado para 16px (12pt) */
}

.template-58mm .totals {
  text-align: center;
  margin-bottom: 6px;
}

.template-58mm .total {
  font-weight: bold;
  font-size: 17px; /* Aumentado para 17px */
}

.template-58mm .footer {
  text-align: center;
  font-size: 16px; /* Aumentado para 16px (12pt) */
}

.template-58mm .status,
.template-58mm .responsible {
  margin-bottom: 1px;
}

/* Estilos para 80mm */
.template-80mm .service-order-80mm {
  width: 76mm;
  max-width: 76mm;
  font-size: 16px; /* Aumentado para 16px (12pt) */
  line-height: 1.3;
  padding: 0.2mm;
  margin: 0;
  box-sizing: border-box;
}

.template-80mm .header {
  text-align: center;
  margin-bottom: 2px;
}

.template-80mm .company-name {
  font-weight: bold;
  font-size: 18px; /* Aumentado para 18px */
  margin-bottom: 0px;
}

.template-80mm .company-address,
.template-80mm .company-phone {
  font-size: 16px; /* Aumentado para 16px (12pt) */
  margin-bottom: 0px;
}

.template-80mm .divider {
  border-top: 1px solid #000;
  margin: 1px 0;
}

.template-80mm .order-title {
  text-align: center;
  margin-bottom: 2px;
}

.template-80mm .title {
  font-weight: bold;
  font-size: 17px; /* Aumentado para 17px */
  margin-bottom: 1px;
}

.template-80mm .order-details {
  display: flex;
  justify-content: space-between;
  font-size: 16px; /* Aumentado para 16px (12pt) */
}

.template-80mm .order-number {
  font-weight: bold;
}

.template-80mm .customer-section,
.template-80mm .vehicle-section,
.template-80mm .problem-section,
.template-80mm .diagnosis-section,
.template-80mm .items-section {
  margin-bottom: 2px;
}

.template-80mm .section-title {
  font-weight: bold;
  font-size: 16px; /* Aumentado para 16px (12pt) */
  margin-bottom: 1px;
  text-decoration: underline;
}

.template-80mm .subsection-title {
  font-weight: bold;
  font-size: 16px; /* Aumentado para 16px (12pt) */
  margin: 2px 0 1px 0;
}

.template-80mm .customer-name,
.template-80mm .vehicle-model {
  font-weight: bold;
  font-size: 16px; /* Aumentado para 16px (12pt) */
  margin-bottom: 0px;
}

.template-80mm .customer-document,
.template-80mm .customer-phone,
.template-80mm .vehicle-plate,
.template-80mm .vehicle-year {
  font-size: 16px; /* Aumentado para 16px (12pt) */
  margin-bottom: 0px;
}

.template-80mm .problem-text,
.template-80mm .diagnosis-text {
  font-size: 16px; /* Aumentado para 16px (12pt) */
  line-height: 1.2;
}

.template-80mm .items-table {
  margin-bottom: 2px;
}

.template-80mm .table-header,
.template-80mm .table-row {
  display: flex;
  font-size: 16px; /* Aumentado para 16px (12pt) */
  padding: 0px;
}

.template-80mm .table-header {
  font-weight: bold;
  border-bottom: 1px solid #000;
  margin-bottom: 1px;
}

.template-80mm .col-desc {
  flex: 2.5;
  text-align: left;
}

.template-80mm .col-qty {
  flex: 0.5;
  text-align: center;
}

.template-80mm .col-price,
.template-80mm .col-total {
  flex: 1;
  text-align: right;
}

.template-80mm .totals-section {
  margin-bottom: 4px;
}

.template-80mm .totals-grid {
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.template-80mm .total-row {
  display: flex;
  justify-content: space-between;
  font-size: 16px; /* Aumentado para 16px (12pt) */
}

.template-80mm .final-total {
  font-weight: bold;
  font-size: 17px; /* Aumentado para 17px */
  border-top: 1px solid #000;
  padding-top: 1px;
  margin-top: 2px;
}

.template-80mm .footer-section {
  margin-top: 4px;
}

.template-80mm .status-info {
  margin-bottom: 3px;
}

.template-80mm .status,
.template-80mm .responsible {
  font-size: 16px; /* Aumentado para 16px (12pt) */
  margin-bottom: 0px;
}

.template-80mm .warranty-info {
  margin-bottom: 3px;
}

.template-80mm .warranty-title {
  font-weight: bold;
  font-size: 16px; /* Aumentado para 16px (12pt) */
  margin-bottom: 1px;
}

.template-80mm .warranty-products,
.template-80mm .warranty-services {
  font-size: 16px; /* Aumentado para 16px (12pt) */
  margin-bottom: 0px;
}

.template-80mm .print-info {
  text-align: center;
  font-size: 16px; /* Aumentado para 16px (12pt) */
  color: #666;
  margin-top: 3px;
}

/* Estilos para impressão */
@media print {
  .thermal-service-order-print {
    margin: 0;
    padding: 0;
  }
  
  .template-58mm .service-order-58mm,
  .template-80mm .service-order-80mm {
    margin: 0;
    padding: 2mm;
  }
}

/* Regras @page específicas para cada template */
@media print {
  .template-58mm {
    @page {
      size: 58mm auto;
      margin: 0;
    }
  }
  
  .template-80mm {
    @page {
      size: 80mm auto;
      margin: 0;
    }
  }
}
</style>