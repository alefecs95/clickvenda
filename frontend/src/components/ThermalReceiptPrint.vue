<template>
  <div class="thermal-receipt-print" :class="templateClass">
    <!-- Modelo 58mm -->
    <div v-if="template === '58mm'" class="receipt-58mm">
      <div class="header">
        <div class="store-name">{{ storeInfo.name || 'LOJA' }}</div>
        <div v-if="storeInfo.cnpj" class="store-cnpj">{{ storeInfo.cnpj }}</div>
        <div v-if="storeInfo.address" class="store-address">{{ storeInfo.address }}</div>
        <div v-if="storeInfo.phone" class="store-phone">{{ storeInfo.phone }}</div>
      </div>
      
      <div class="divider"></div>
      
      <div class="order-info">
        <div class="order-number">#{{ saleData.id }}</div>
        <div class="order-date">{{ formatDate(saleData.date) }}</div>
        <div v-if="saleData.customer" class="customer-name">{{ saleData.customer.name }}</div>
      </div>
      
      <div class="divider"></div>
      
      <div class="items">
        <div v-for="item in saleData.items" :key="item.id" class="item">
          <div class="item-name">{{ item.product.name }}</div>
          <div class="item-details">
            {{ item.quantity }}x R${{ formatPrice(item.product.price) }} = R${{ formatPrice(item.product.price * item.quantity) }}
          </div>
        </div>
      </div>
      
      <div class="divider"></div>
      
      <div class="total">
        <div class="total-label">TOTAL:</div>
        <div class="total-value">R$ {{ formatPrice(saleData.total) }}</div>
      </div>
      
      <div v-if="saleData.paymentMethods || saleData.paymentMethod" class="payment">
        <div class="payment-title">PAGAMENTO:</div>
        <div v-if="saleData.paymentMethods">
          <div v-for="payment in saleData.paymentMethods" :key="payment.method" class="payment-method">
            {{ getPaymentMethodText(payment.method) }}: R$ {{ formatPrice(payment.amount) }}
          </div>
        </div>
        <div v-else class="payment-method">
          {{ getPaymentMethodText(saleData.paymentMethod) }}
        </div>
      </div>
      
      <div class="divider"></div>
      
      <div class="footer">
        {{ storeInfo.footer || 'Obrigado pela preferência!' }}
      </div>
    </div>

    <!-- Modelo 80mm -->
    <div v-else-if="template === '80mm'" class="receipt-80mm">
      <div class="header">
        <div class="store-name">{{ storeInfo.name || 'LOJA' }}</div>
        <div v-if="storeInfo.cnpj" class="store-cnpj">CNPJ: {{ storeInfo.cnpj }}</div>
        <div v-if="storeInfo.address" class="store-address">{{ storeInfo.address }}</div>
        <div v-if="storeInfo.city && storeInfo.state" class="store-location">
          {{ storeInfo.city }}, {{ storeInfo.state }}
        </div>
        <div v-if="storeInfo.phone" class="store-phone">Tel: {{ storeInfo.phone }}</div>
      </div>
      
      <div class="divider"></div>
      
      <div class="order-info">
        <div class="order-header">
          <span class="order-number">Pedido: #{{ saleData.id }}</span>
          <span class="order-date">{{ formatDateTime(saleData.date) }}</span>
        </div>
        <div v-if="saleData.customer" class="customer-info">
          <strong>Cliente:</strong> {{ saleData.customer.name }}
        </div>
      </div>
      
      <div class="divider"></div>
      
      <div class="items-table">
        <div class="table-header">
          <span class="col-item">ITEM</span>
          <span class="col-qty">QTD</span>
          <span class="col-price">VALOR</span>
          <span class="col-total">TOTAL</span>
        </div>
        <div v-for="item in saleData.items" :key="item.id" class="table-row">
          <span class="col-item">{{ item.product.name }}</span>
          <span class="col-qty">{{ item.quantity }}</span>
          <span class="col-price">{{ formatPrice(item.product.price) }}</span>
          <span class="col-total">{{ formatPrice(item.product.price * item.quantity) }}</span>
        </div>
      </div>
      
      <div class="divider"></div>
      
      <div class="totals">
        <div class="subtotal">
          <span>Subtotal:</span>
          <span>R$ {{ formatPrice(saleData.subtotal || saleData.total) }}</span>
        </div>
        <div v-if="saleData.discount" class="discount">
          <span>Desconto:</span>
          <span>-R$ {{ formatPrice(saleData.discount) }}</span>
        </div>
        <div class="total">
          <span class="total-label">TOTAL:</span>
          <span class="total-value">R$ {{ formatPrice(saleData.total) }}</span>
        </div>
      </div>
      
      <div v-if="saleData.paymentMethods || saleData.paymentMethod" class="payment">
        <div class="payment-title">FORMA DE PAGAMENTO:</div>
        <div v-if="saleData.paymentMethods">
          <div v-for="payment in saleData.paymentMethods" :key="payment.method" class="payment-method">
            <span>{{ getPaymentMethodText(payment.method) }}:</span>
            <span>R$ {{ formatPrice(payment.amount) }}</span>
          </div>
        </div>
        <div v-else class="payment-method">
          <span>{{ getPaymentMethodText(saleData.paymentMethod) }}:</span>
          <span>R$ {{ formatPrice(saleData.total) }}</span>
        </div>
      </div>
      
      <div class="divider"></div>
      
      <div class="footer">
        <div class="footer-text">{{ storeInfo.footer || 'Obrigado pela preferência!' }}</div>
        <div class="print-date">{{ formatDateTime(new Date()) }}</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface StoreInfo {
  name: string
  cnpj?: string
  address?: string
  city?: string
  state?: string
  phone?: string
  footer?: string
}

interface SaleData {
  id: number
  date: Date
  total: number
  subtotal?: number
  discount?: number
  customer?: {
    name: string
  }
  items: Array<{
    id: number
    quantity: number
    product: {
      name: string
      price: number
    }
  }>
  paymentMethod?: string
  paymentMethods?: Array<{
    method: string
    amount: number
  }>
}

interface Props {
  template: '58mm' | '80mm'
  storeInfo: StoreInfo
  saleData: SaleData
}

const props = defineProps<Props>()

const templateClass = computed(() => {
  return `template-${props.template}`
})

const formatPrice = (price: number): string => {
  return price.toFixed(2).replace('.', ',')
}

const formatDate = (date: Date): string => {
  return date.toLocaleDateString('pt-BR')
}

const formatDateTime = (date: Date): string => {
  return `${date.toLocaleDateString('pt-BR')} ${date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })}`
}

const getPaymentMethodText = (method: string): string => {
  const methods: Record<string, string> = {
    'money': 'Dinheiro',
    'card': 'Cartão',
    'debit': 'Cartão Débito',
    'credit': 'Cartão Crédito',
    'pix': 'PIX',
    'transfer': 'Transferência',
    'check': 'Cheque',
    'credit_sale': 'Venda a Prazo'
  }
  return methods[method] || method
}
</script>

<style scoped>
.thermal-receipt-print {
  font-family: 'Courier New', monospace;
  background: white;
  color: black;
  margin: 0 auto;
}

/* Estilos para 58mm */
.template-58mm .receipt-58mm {
  width: 58mm;
  max-width: 58mm;
  font-size: 9px;
  line-height: 1.2;
  padding: 4mm;
}

.template-58mm .header {
  text-align: center;
  margin-bottom: 8px;
}

.template-58mm .store-name {
  font-weight: bold;
  font-size: 11px;
  margin-bottom: 2px;
}

.template-58mm .store-cnpj,
.template-58mm .store-address,
.template-58mm .store-phone {
  font-size: 8px;
  margin-bottom: 1px;
}

.template-58mm .divider {
  border-top: 1px solid #000;
  margin: 6px 0;
}

.template-58mm .order-info {
  margin-bottom: 6px;
  font-size: 8px;
}

.template-58mm .order-number {
  font-weight: bold;
}

.template-58mm .items {
  margin-bottom: 6px;
}

.template-58mm .item {
  margin-bottom: 3px;
  font-size: 8px;
}

.template-58mm .item-name {
  font-weight: bold;
  margin-bottom: 1px;
}

.template-58mm .item-details {
  font-size: 7px;
}

.template-58mm .total {
  text-align: center;
  margin-bottom: 6px;
}

.template-58mm .total-label {
  font-weight: bold;
  font-size: 10px;
}

.template-58mm .total-value {
  font-weight: bold;
  font-size: 11px;
}

.template-58mm .payment {
  margin-bottom: 6px;
  font-size: 8px;
}

.template-58mm .payment-title {
  font-weight: bold;
  margin-bottom: 2px;
}

.template-58mm .footer {
  text-align: center;
  font-size: 7px;
  margin-top: 8px;
}

/* Estilos para 80mm */
.template-80mm .receipt-80mm {
  width: 80mm;
  max-width: 80mm;
  font-size: 10px;
  line-height: 1.3;
  padding: 5mm;
}

.template-80mm .header {
  text-align: center;
  margin-bottom: 10px;
}

.template-80mm .store-name {
  font-weight: bold;
  font-size: 14px;
  margin-bottom: 3px;
}

.template-80mm .store-cnpj,
.template-80mm .store-address,
.template-80mm .store-location,
.template-80mm .store-phone {
  font-size: 9px;
  margin-bottom: 2px;
}

.template-80mm .divider {
  border-top: 1px solid #000;
  margin: 8px 0;
}

.template-80mm .order-info {
  margin-bottom: 8px;
}

.template-80mm .order-header {
  display: flex;
  justify-content: space-between;
  font-size: 9px;
  margin-bottom: 3px;
}

.template-80mm .order-number {
  font-weight: bold;
}

.template-80mm .customer-info {
  font-size: 9px;
}

.template-80mm .items-table {
  margin-bottom: 8px;
}

.template-80mm .table-header,
.template-80mm .table-row {
  display: flex;
  font-size: 8px;
  padding: 2px 0;
}

.template-80mm .table-header {
  font-weight: bold;
  border-bottom: 1px solid #000;
  margin-bottom: 3px;
}

.template-80mm .col-item {
  flex: 2;
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

.template-80mm .totals {
  margin-bottom: 8px;
}

.template-80mm .subtotal,
.template-80mm .discount,
.template-80mm .total {
  display: flex;
  justify-content: space-between;
  font-size: 9px;
  margin-bottom: 2px;
}

.template-80mm .total {
  font-weight: bold;
  font-size: 11px;
  border-top: 1px solid #000;
  padding-top: 3px;
  margin-top: 5px;
}

.template-80mm .payment {
  margin-bottom: 8px;
}

.template-80mm .payment-title {
  font-weight: bold;
  font-size: 9px;
  margin-bottom: 3px;
}

.template-80mm .payment-method {
  display: flex;
  justify-content: space-between;
  font-size: 9px;
  margin-bottom: 1px;
}

.template-80mm .footer {
  text-align: center;
  margin-top: 10px;
}

.template-80mm .footer-text {
  font-size: 9px;
  margin-bottom: 3px;
}

.template-80mm .print-date {
  font-size: 7px;
  color: #666;
}

/* Estilos para impressão */
@media print {
  .thermal-receipt-print {
    margin: 0;
    padding: 0;
  }
  
  .template-58mm .receipt-58mm,
  .template-80mm .receipt-80mm {
    margin: 0;
    padding: 2mm;
  }
}
</style>