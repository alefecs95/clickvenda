<template>
  <div class="min-h-screen bg-gray-100 flex flex-col">
    <!-- Header Compacto -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-4 py-2">
      <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
          <div class="flex flex-col">
            <h1 class="text-lg font-bold text-gray-900 flex items-center">
              <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
              </svg>
              PONTO DE VENDA
            </h1>
            <div class="text-xs text-gray-500 flex items-center space-x-2">
              <span>{{ new Date().toLocaleDateString('pt-BR') }} - {{ new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }) }}</span>
            </div>
          </div>
        </div>
        <div class="flex items-center space-x-4">
          <div class="text-xs text-gray-600">
            <span class="font-medium">{{ cart.length }}</span> itens
          </div>
          <div v-if="cart.length > 0" class="text-xl font-bold text-primary-600">
            R$ {{ formatPrice(total) }}
          </div>
        </div>
      </div>
    </div>

    <!-- Layout Principal -->
    <div class="flex-1 flex" style="height: calc(100vh - 140px);">
      <!-- Produtos - Lado Esquerdo -->
      <div class="w-2/3 bg-white border-r border-gray-200 flex flex-col h-full">
        <!-- Header de Busca -->
        <div class="p-4 border-b border-gray-200">
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <input
              ref="searchInput"
              v-model="searchQuery"
              type="text"
              placeholder="Buscar por código de barras, nome ou SKU... (F4)"
              class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent text-lg"
              @input="handleSearch"
              @keydown.enter="focusFirstProduct"
            />
          </div>
        </div>

        <!-- Lista de Produtos -->
        <div class="flex-1 overflow-y-auto p-4">
          <div v-if="filteredProducts.length === 0" class="flex flex-col items-center justify-center h-64">
            <svg class="h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <p class="text-gray-600 text-lg">Nenhum produto encontrado</p>
            <p class="text-gray-500 text-sm">Tente buscar por outro termo</p>
          </div>

          <div v-else class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-3">
            <div
              v-for="(product, index) in filteredProducts"
              :key="product.id"
              :ref="'product-' + index"
              class="border border-gray-200 rounded-lg p-3 transition-all group cursor-pointer hover:border-primary-300 hover:shadow-md"
              @click="selectProduct(product)"
              @keydown.enter="selectProduct(product)"
              tabindex="0"
            >
              <div class="flex justify-between items-start mb-2">
                <h3 class="font-semibold text-gray-900 text-sm group-hover:text-primary-600 transition-colors line-clamp-2">
                  {{ product.name }}
                </h3>
                <div class="text-right ml-2">
                  <span class="text-lg font-bold text-primary-600">R$ {{ formatPrice(product.price) }}</span>
                  <div v-if="product.returnable" class="text-xs text-orange-600 font-medium">
                    + R$ {{ formatPrice(product.returnable_price) }}
                  </div>
                </div>
              </div>
              
              <div class="flex justify-between items-center text-xs text-gray-600 mb-2">
                <div class="flex items-center space-x-1">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                  </svg>
                  <span class="font-medium">{{ product.actual_stock || product.stock_quantity }}</span>
                </div>
                <div class="flex space-x-1">
                  <span v-if="(product.actual_stock || product.stock_quantity) <= 0" class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">
                    Sem Estoque
                  </span>
                  <span v-else-if="!product.available" class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">
                    Indisponível
                  </span>
                  <span v-if="product.returnable" class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded-full">
                    Retornável
                  </span>
                  <span v-if="product.parent_product_id" class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                    Variação
                  </span>
                </div>
              </div>
              
              <div class="text-center pt-2 border-t border-gray-100">
                <div class="flex items-center justify-center text-primary-600 font-medium text-xs group-hover:text-primary-700">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  Adicionar ao carrinho
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Carrinho - Lado Direito -->
      <div class="w-1/3 bg-white flex flex-col h-full">
        <!-- Header do Carrinho -->
        <div class="bg-primary-600 text-white p-4">
          <div class="flex justify-between items-center">
            <h2 class="text-lg font-bold">CARRINHO DE COMPRAS</h2>
            <div class="flex items-center space-x-2">
              <span class="text-sm">{{ cart.length }} itens</span>
              <button
                v-if="cart.length > 0"
                @click="clearCart"
                class="bg-red-500 hover:bg-red-600 px-2 py-1 rounded text-xs"
                title="Limpar Carrinho (F8)"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Lista de Itens do Carrinho -->
        <div class="flex-1 overflow-y-auto">
          <div v-if="cart.length === 0" class="flex flex-col items-center justify-center h-full text-gray-500">
            <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m6 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
            </svg>
            <p class="text-lg font-medium">Carrinho vazio</p>
            <p class="text-sm">Adicione produtos para continuar</p>
          </div>

          <div v-else class="divide-y divide-gray-200">
            <div
              v-for="(item, index) in cart"
              :key="item.product.id"
              class="p-4 hover:bg-gray-50"
              :class="{ 'bg-blue-50': selectedCartItem === index }"
            >
              <div class="flex justify-between items-start mb-2">
                <div class="flex-1">
                  <h4 class="font-semibold text-gray-900 text-sm">{{ item.product.name }}</h4>
                  <div class="text-xs text-gray-600 mt-1">
                    R$ {{ formatPrice(item.product.price) }} × {{ item.quantity }}
                    <span v-if="item.product.returnable" class="text-orange-600 ml-2">
                      + {{ item.quantity }} vasilhame(s)
                    </span>
                  </div>
                </div>
                <div class="text-right">
                  <div class="font-bold text-primary-600">
                    R$ {{ formatPrice(getItemTotal(item)) }}
                  </div>
                </div>
              </div>
              
              <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                  <button
                    @click="updateQuantity(item.product.id, item.quantity - 1)"
                    :disabled="item.quantity <= 1"
                    class="w-8 h-8 rounded bg-gray-200 text-gray-600 hover:bg-gray-300 disabled:opacity-50 flex items-center justify-center font-bold"
                  >
                    -
                  </button>
                  <span class="w-12 text-center font-bold">{{ item.quantity }}</span>
                  <button
                    @click="updateQuantity(item.product.id, item.quantity + 1)"
                    :disabled="item.quantity >= (item.product.actual_stock || item.product.stock_quantity)"
                    class="w-8 h-8 rounded bg-gray-200 text-gray-600 hover:bg-gray-300 disabled:opacity-50 flex items-center justify-center font-bold"
                  >
                    +
                  </button>
                </div>
                <button
                  @click="removeFromCart(item.product.id)"
                  class="text-red-600 hover:text-red-800 p-1"
                  title="Remover item (Delete)"
                >
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Rodapé Fixo com Total e Atalhos -->
    <div class="bg-white border-t border-gray-200 px-4 py-3 shadow-lg">
      <div class="flex justify-between items-center">
        <!-- Resumo Financeiro -->
        <div class="flex items-center space-x-6">
          <div class="text-sm">
            <span class="text-gray-600">Subtotal:</span>
            <span class="font-medium ml-2">R$ {{ formatPrice(subtotal) }}</span>
          </div>
          <div v-if="returnableTotal > 0" class="text-sm">
            <span class="text-gray-600">Vasilhames:</span>
            <span class="font-medium text-orange-600 ml-2">+R$ {{ formatPrice(returnableTotal) }}</span>
          </div>
          <div class="text-sm">
            <span class="text-gray-600">Desconto:</span>
            <span class="font-medium text-green-600 ml-2">-R$ {{ formatPrice(discount) }}</span>
          </div>
          <div class="text-lg font-bold">
            <span class="text-gray-900">TOTAL:</span>
            <span class="text-primary-600 ml-2">R$ {{ formatPrice(total) }}</span>
          </div>
          <div v-if="cart.length === 0" class="text-xs text-gray-500 italic">
            Carrinho vazio
          </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex items-center space-x-3">
          <button
            @click="showCustomerModal = true"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded font-bold text-sm flex items-center"
            title="Selecionar Cliente (F2)"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            CLIENTE (F2)
          </button>
          <button
            @click="cart.length > 0 ? showCheckoutModal = true : null"
            :class="cart.length > 0 ? 'bg-primary-600 hover:bg-primary-700' : 'bg-gray-400 cursor-not-allowed'"
            class="text-white px-6 py-2 rounded font-bold text-sm flex items-center"
            :title="cart.length > 0 ? 'Finalizar Venda (F9)' : 'Adicione produtos ao carrinho'"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
            </svg>
            {{ cart.length > 0 ? 'FINALIZAR (F9)' : 'FINALIZAR' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { useProductsStore } from '@/stores/products'
import { useOrdersStore } from '@/stores/orders'
import { useCustomersStore, type Customer } from '@/stores/customers'
import { useSettingsStore } from '@/stores/settings'

const productsStore = useProductsStore()
const ordersStore = useOrdersStore()
const customersStore = useCustomersStore()
const settingsStore = useSettingsStore()

interface Product {
  id: number
  name: string
  description?: string | null
  price: number
  stock_quantity: number
  actual_stock?: number
  sku?: string | null
  barcode?: string | null
  available: boolean
  returnable: boolean
  returnable_price?: number | null
  parent_product_id?: number | null
}

interface CartItem {
  product: Product
  quantity: number
}

const searchInput = ref<HTMLInputElement>()
const searchQuery = ref('')
const searching = ref(false)
const searchResults = ref<Product[]>([])
const cart = ref<CartItem[]>([])
const showCheckoutModal = ref(false)
const showCustomerModal = ref(false)
const showSuccessModal = ref(false)
const checkoutSubmitting = ref(false)
const selectedCustomer = ref<Customer | null>(null)
const selectedCartItem = ref(-1)
const selectedProductIndex = ref(-1)
const customerSearchQuery = ref('')
const multiplePayments = ref(false)
const completedSale = ref<any>(null)

// Modal de quantidade
const showQuantityModal = ref(false)
const selectedProduct = ref<Product | null>(null)
const quantityInput = ref(1)

const checkoutForm = ref({
  customer_id: '',
  payment_method: 'money',
  discount: 0,
  notes: ''
})

const paymentMethods = ref([
  { method: 'money', amount: 0 }
])

const customers = ref<Customer[]>([])

const subtotal = computed(() => {
  return cart.value.reduce((sum, item) => {
    const price = parseFloat(item.product.price.toString()) || 0
    const quantity = Number(item.quantity) || 0
    return sum + (price * quantity)
  }, 0)
})

const returnableTotal = computed(() => {
  return cart.value.reduce((sum, item) => {
    if (item.product.returnable && item.product.returnable_price) {
      const returnablePrice = parseFloat(item.product.returnable_price.toString()) || 0
      const quantity = Number(item.quantity) || 0
      return sum + (returnablePrice * quantity)
    }
    return sum
  }, 0)
})

const discount = computed(() => {
  return Number(checkoutForm.value.discount) || 0
})

const total = computed(() => {
  return subtotal.value + returnableTotal.value - discount.value
})

// Produtos filtrados em tempo real
const filteredProducts = computed(() => {
  if (!searchQuery.value.trim()) {
    return productsStore.products
  }
  
  const query = searchQuery.value.toLowerCase().trim()
  return productsStore.products.filter(product => 
    product.name.toLowerCase().includes(query) ||
    product.description?.toLowerCase().includes(query) ||
    product.sku?.toLowerCase().includes(query) ||
    product.barcode?.toLowerCase().includes(query)
  )
})

const formatPrice = (price: any) => {
  const numPrice = parseFloat(price) || 0
  return numPrice.toFixed(2).replace('.', ',')
}

const getItemTotal = (item: CartItem) => {
  const productPrice = parseFloat(item.product.price.toString()) || 0
  const returnablePrice = item.product.returnable ? (parseFloat(item.product.returnable_price?.toString() || '0') || 0) : 0
  const quantity = Number(item.quantity) || 0
  return (productPrice + returnablePrice) * quantity
}

const clearCart = () => {
  if (confirm('Tem certeza que deseja limpar o carrinho?')) {
    cart.value = []
  }
}

const selectCustomer = (customer: Customer | null) => {
  selectedCustomer.value = customer
  checkoutForm.value.customer_id = customer ? customer.id.toString() : ''
  showCustomerModal.value = false
  customerSearchQuery.value = ''
}

const selectProduct = (product: Product) => {
  if ((product.actual_stock || product.stock_quantity) <= 0) return
  
  selectedProduct.value = product
  quantityInput.value = 1
  showQuantityModal.value = true
}

const addToCartWithQuantity = () => {
  if (!selectedProduct.value) return
  
  const existingItem = cart.value.find(item => item.product.id === selectedProduct.value!.id)
  
  if (existingItem) {
    existingItem.quantity += quantityInput.value
  } else {
    cart.value.push({
      product: selectedProduct.value,
      quantity: quantityInput.value
    })
  }
  
  showQuantityModal.value = false
  selectedProduct.value = null
  quantityInput.value = 1
  
  nextTick(() => {
    searchInput.value?.focus()
  })
}

const closeQuantityModal = () => {
  showQuantityModal.value = false
  selectedProduct.value = null
  quantityInput.value = 1
  
  nextTick(() => {
    searchInput.value?.focus()
  })
}

const updateQuantity = (productId: number, newQuantity: number) => {
  const item = cart.value.find(item => item.product.id === productId)
  if (item) {
    if (newQuantity <= 0) {
      removeFromCart(productId)
    } else if (newQuantity <= (item.product.actual_stock || item.product.stock_quantity)) {
      item.quantity = newQuantity
    }
  }
}

const removeFromCart = (productId: number) => {
  cart.value = cart.value.filter(item => item.product.id !== productId)
}

const handleSearch = async () => {
  searching.value = false
}

const focusFirstProduct = () => {
  if (searchResults.value.length > 0) {
    selectedProductIndex.value = 0
    nextTick(() => {
      const element = document.querySelector('[ref="product-0"]') as HTMLElement
      element?.focus()
    })
  }
}

onMounted(async () => {
  await productsStore.fetchProducts()
  await customersStore.fetchCustomers()
  await settingsStore.loadSettings()
  customers.value = customersStore.customers
  
  nextTick(() => {
    searchInput.value?.focus()
  })
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>

