<template>
  <AppLayout class="max-w-8xl mx-auto">
    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
        <div class="flex flex-col space-y-4 lg:flex-row lg:justify-between lg:items-center lg:space-y-0">
          <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center">
              <svg class="w-5 h-5 sm:w-6 sm:h-6 mr-2 sm:mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
              Contas a Pagar
            </h1>
            <p class="text-gray-600 mt-1 text-sm sm:text-base">Gerencie pagamentos a fornecedores</p>
          </div>
          
          <!-- Resumo Financeiro -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-6">
            <div class="text-center p-3 sm:p-4 bg-red-50 rounded-lg border border-red-200">
              <div class="text-2xl sm:text-3xl font-bold text-red-600">R$ {{ formatPrice(statistics.amount_overdue) }}</div>
              <div class="text-xs sm:text-sm text-red-700 font-medium">Contas Vencidas</div>
              <div class="text-xs text-red-600 mt-1">{{ statistics.total_overdue }} contas</div>
            </div>
            <div class="text-center p-3 sm:p-4 bg-orange-50 rounded-lg border border-orange-200">
              <div class="text-2xl sm:text-3xl font-bold text-orange-600">R$ {{ formatPrice(statistics.amount_pending) }}</div>
              <div class="text-xs sm:text-sm text-orange-700 font-medium">A Vencer</div>
              <div class="text-xs text-orange-600 mt-1">{{ statistics.total_pending }} contas</div>
            </div>
            <div class="text-center p-3 sm:p-4 bg-green-50 rounded-lg border border-green-200">
              <div class="text-2xl sm:text-3xl font-bold text-green-600">R$ {{ formatPrice(statistics.amount_paid) }}</div>
              <div class="text-xs sm:text-sm text-green-700 font-medium">Pagas</div>
              <div class="text-xs text-green-600 mt-1">{{ statistics.total_paid }} contas</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filtros e Ações -->
      <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
        <div class="flex flex-col space-y-4 lg:flex-row lg:justify-between lg:items-center lg:space-y-0">
          <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
            <!-- Filtro por Status -->
            <select v-model="filters.status" @change="fetchPayables" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
              <option value="">Todos os Status</option>
              <option value="pendente">Pendente</option>
              <option value="vencido">Vencido</option>
              <option value="parcial">Parcial</option>
              <option value="pago">Pago</option>
            </select>

            <!-- Filtro por Fornecedor -->
            <input
              v-model="filters.supplier_name"
              @input="debounceSearch"
              type="text"
              placeholder="Buscar por fornecedor..."
              class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
            />

            <!-- Filtro por Data -->
            <input
              v-model="filters.due_date_from"
              @change="fetchPayables"
              type="date"
              class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
            <input
              v-model="filters.due_date_to"
              @change="fetchPayables"
              type="date"
              class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>

          <div class="flex space-x-2">
            <button
              @click="openCreateModal"
              class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md font-medium flex items-center"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              Nova Conta
            </button>
          </div>
        </div>
      </div>

      <!-- Lista de Contas a Pagar -->
      <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div v-if="loading" class="p-8 text-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mx-auto"></div>
          <p class="mt-2 text-gray-600">Carregando contas a pagar...</p>
        </div>

        <div v-else-if="payables.length === 0" class="p-8 text-center">
          <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          <p class="text-gray-600">Nenhuma conta a pagar encontrada</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fornecedor</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimento</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="payable in payables" :key="payable.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ payable.supplier_name || 'Fornecedor não informado' }}</div>
                    <div class="text-sm text-gray-500">{{ payable.supplier_document || '-' }}</div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">R$ {{ formatPrice(payable.total_payable_amount) }}</div>
                  <div v-if="payable.paid_amount > 0" class="text-xs text-green-600">
                    Pago: R$ {{ formatPrice(payable.paid_amount) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ formatDate(payable.due_date) }}</div>
                  <div v-if="isOverdue(payable.due_date)" class="text-xs text-red-600">
                    {{ getDaysOverdue(payable.due_date) }} dias em atraso
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(payable.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                    {{ getStatusLabel(payable.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ payable.category || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end space-x-2">
                    <button
                      @click="openPaymentHistoryModal(payable)"
                      v-if="hasPaymentHistory(payable)"
                      class="text-blue-600 hover:text-blue-900"
                      title="Ver Histórico de Pagamentos"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                      </svg>
                    </button>
                    <button
                      @click="openPaymentModal(payable)"
                      v-if="payable.status !== 'pago'"
                      class="text-green-600 hover:text-green-900"
                      title="Adicionar Pagamento"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                      </svg>
                    </button>
                    <button
                      @click="openEditModal(payable)"
                      class="text-indigo-600 hover:text-indigo-900"
                      title="Editar"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                      </svg>
                    </button>
                    <button
                      @click="deletePayable(payable)"
                      class="text-red-600 hover:text-red-900"
                      title="Excluir"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal de Criação/Edição -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            {{ editingPayable ? 'Editar Conta a Pagar' : 'Nova Conta a Pagar' }}
          </h3>
          
          <form @submit.prevent="savePayable" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Fornecedor</label>
              <div class="flex space-x-2">
                <select
                  v-model="payableForm.supplier_id"
                  @change="onSupplierChange"
                  class="flex-1 mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                  <option value="">Selecione um fornecedor</option>
                  <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                    {{ supplier.name }} - {{ supplier.cpf_cnpj }}
                  </option>
                </select>
                <button
                  type="button"
                  @click="openSupplierModal"
                  class="mt-1 px-3 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md"
                  title="Adicionar novo fornecedor"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Campos para fornecedor manual (quando não selecionado da lista) -->
            <div v-if="!payableForm.supplier_id" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Nome do Fornecedor</label>
                <input
                  v-model="payableForm.supplier_name"
                  type="text"
                  required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Documento</label>
                <input
                  v-model="payableForm.supplier_document"
                  type="text"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Valor</label>
              <input
                v-model="payableForm.total_payable_amount"
                type="number"
                step="0.01"
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Data de Vencimento</label>
              <input
                v-model="payableForm.due_date"
                type="date"
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Categoria</label>
              <input
                v-model="payableForm.category"
                type="text"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Descrição</label>
              <textarea
                v-model="payableForm.description"
                rows="3"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              ></textarea>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
              <button
                type="button"
                @click="closeCreateModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="loading"
                class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-md disabled:opacity-50"
              >
                {{ editingPayable ? 'Atualizar' : 'Criar' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal de Pagamento -->
    <div v-if="showPaymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Adicionar Pagamento</h3>
          
          <form @submit.prevent="addPayment" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Valor do Pagamento</label>
              <input
                v-model="paymentForm.amount"
                type="number"
                step="0.01"
                :max="selectedPayable?.remaining_amount"
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
              <p class="text-xs text-gray-500 mt-1">
                Valor restante: R$ {{ formatPrice(selectedPayable?.remaining_amount || 0) }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Data do Pagamento</label>
              <input
                v-model="paymentForm.payment_date"
                type="date"
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Método de Pagamento</label>
              <select
                v-model="paymentForm.payment_method"
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              >
                <option value="">Selecione...</option>
                <option value="dinheiro">Dinheiro</option>
                <option value="pix">PIX</option>
                <option value="transferencia">Transferência</option>
                <option value="boleto">Boleto</option>
                <option value="cartao_debito">Cartão de Débito</option>
                <option value="cartao_credito">Cartão de Crédito</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Observações</label>
              <textarea
                v-model="paymentForm.notes"
                rows="3"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              ></textarea>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
              <button
                type="button"
                @click="closePaymentModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="loading"
                class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md disabled:opacity-50"
              >
                Adicionar Pagamento
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal de Histórico de Pagamentos -->
    <div v-if="showPaymentHistoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex justify-between items-center mb-6">
            <div>
              <h3 class="text-lg font-medium text-gray-900">Histórico de Pagamentos</h3>
              <p class="text-sm text-gray-600 mt-1">
                {{ selectedPayable?.supplier_name }} - R$ {{ formatPrice(selectedPayable?.total_payable_amount || 0) }}
              </p>
            </div>
            <button
              @click="closePaymentHistoryModal"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="text-center">
                <div class="text-2xl font-bold text-gray-900">R$ {{ formatPrice(selectedPayable?.total_payable_amount || 0) }}</div>
                <div class="text-sm text-gray-600">Valor Total</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-green-600">R$ {{ formatPrice(selectedPayable?.paid_amount || 0) }}</div>
                <div class="text-sm text-gray-600">Valor Pago</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-orange-600">R$ {{ formatPrice(selectedPayable?.remaining_amount || 0) }}</div>
                <div class="text-sm text-gray-600">Valor Restante</div>
              </div>
            </div>
          </div>

          <div v-if="paymentHistory.length === 0" class="text-center py-8">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="text-gray-600">Nenhum pagamento registrado</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observações</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(payment, index) in paymentHistory" :key="index" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDateTime(payment.date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-green-600">R$ {{ formatPrice(payment.amount) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                      {{ getPaymentMethodLabel(payment.method) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900">
                    {{ payment.notes || '-' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="flex justify-end pt-6">
            <button
              @click="closePaymentHistoryModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md"
            >
              Fechar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Fornecedor -->
    <SupplierModal 
      :show="showSupplierModal" 
      @close="closeSupplierModal"
      @saved="onSupplierSaved"
    />
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { usePayablesStore, type PayablePayment } from '@/stores/payables'
import { useSuppliersStore, type Supplier } from '@/stores/suppliers'
import AppLayout from '@/components/AppLayout.vue'
import SupplierModal from '@/components/SupplierModal.vue'

const payablesStore = usePayablesStore()
const suppliersStore = useSuppliersStore()

// Reactive data
const showCreateModal = ref(false)
const showSupplierModal = ref(false)
const showPaymentModal = ref(false)
const showPaymentHistoryModal = ref(false)
const editingPayable = ref<PayablePayment | null>(null)
const selectedPayable = ref<PayablePayment | null>(null)
const paymentHistory = ref<any[]>([])

const filters = ref({
  status: '',
  supplier_name: '',
  due_date_from: '',
  due_date_to: ''
})

const payableForm = ref({
  supplier_id: null,
  supplier_name: '',
  supplier_document: '',
  total_payable_amount: 0,
  due_date: '',
  category: '',
  description: ''
})

const paymentForm = ref({
  amount: 0,
  payment_date: new Date().toISOString().split('T')[0],
  payment_method: '',
  notes: ''
})

// Computed
const payables = computed(() => payablesStore.payables)
const statistics = computed(() => payablesStore.statistics)
const loading = computed(() => payablesStore.loading)
const suppliers = computed(() => suppliersStore.activeSuppliers)

// Methods
const formatPrice = (value: number | null | undefined) => {
  if (value === null || value === undefined || isNaN(value)) {
    return '0,00'
  }
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('pt-BR')
}

const formatDateForInput = (date: string) => {
  if (!date) return ''
  const d = new Date(date)
  return d.toISOString().split('T')[0]
}

const isOverdue = (dueDate: string) => {
  return new Date(dueDate) < new Date()
}

const getDaysOverdue = (dueDate: string) => {
  const today = new Date()
  const due = new Date(dueDate)
  const diffTime = today.getTime() - due.getTime()
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
}

const getStatusClass = (status: string) => {
  const classes = {
    'pendente': 'bg-yellow-100 text-yellow-800',
    'vencido': 'bg-red-100 text-red-800',
    'parcial': 'bg-blue-100 text-blue-800',
    'pago': 'bg-green-100 text-green-800'
  }
  return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800'
}

const getStatusLabel = (status: string) => {
  const labels = {
    'pendente': 'Pendente',
    'vencido': 'Vencido',
    'parcial': 'Parcial',
    'pago': 'Pago'
  }
  return labels[status as keyof typeof labels] || status
}

const fetchPayables = async () => {
  try {
    await payablesStore.fetchPayables(filters.value)
  } catch (error) {
    console.error('Erro ao carregar contas a pagar:', error)
  }
}

const fetchStatistics = async () => {
  try {
    await payablesStore.fetchStatistics()
  } catch (error) {
    console.error('Erro ao carregar estatísticas:', error)
  }
}

const fetchSuppliers = async () => {
  try {
    await suppliersStore.fetchSuppliers()
  } catch (error) {
    console.error('Erro ao carregar fornecedores:', error)
  }
}

let searchTimeout: NodeJS.Timeout
const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchPayables()
  }, 500)
}

const openCreateModal = () => {
  editingPayable.value = null
  payableForm.value = {
    supplier_id: null,
    supplier_name: '',
    supplier_document: '',
    total_payable_amount: 0,
    due_date: '',
    category: '',
    description: ''
  }
  showCreateModal.value = true
}

const openSupplierModal = () => {
  showSupplierModal.value = true
}

const closeSupplierModal = () => {
  showSupplierModal.value = false
}

const onSupplierSaved = (supplier: Supplier) => {
  // Atualizar o select de fornecedores
  payableForm.value.supplier_id = supplier.id
  payableForm.value.supplier_name = supplier.name
  payableForm.value.supplier_document = supplier.cpf_cnpj
}

const onSupplierChange = () => {
  const selectedSupplier = suppliers.value.find(s => s.id === payableForm.value.supplier_id)
  if (selectedSupplier) {
    payableForm.value.supplier_name = selectedSupplier.name
    payableForm.value.supplier_document = selectedSupplier.cpf_cnpj
  } else {
    payableForm.value.supplier_name = ''
    payableForm.value.supplier_document = ''
  }
}

const openEditModal = (payable: PayablePayment) => {
  editingPayable.value = payable
  payableForm.value = {
    supplier_id: payable.supplier_id,
    supplier_name: payable.supplier_name,
    supplier_document: payable.supplier_document,
    total_payable_amount: payable.total_payable_amount,
    due_date: formatDateForInput(payable.due_date),
    category: payable.category || '',
    description: payable.description || ''
  }
  showCreateModal.value = true
}

const closeCreateModal = () => {
  showCreateModal.value = false
  editingPayable.value = null
}

const savePayable = async () => {
  try {
    if (editingPayable.value) {
      await payablesStore.updatePayable(editingPayable.value.id, payableForm.value)
    } else {
      await payablesStore.createPayable(payableForm.value)
    }
    closeCreateModal()
    await fetchStatistics()
  } catch (error) {
    console.error('Erro ao salvar conta a pagar:', error)
  }
}

const openPaymentModal = (payable: PayablePayment) => {
  selectedPayable.value = payable
  paymentForm.value = {
    amount: payable.remaining_amount,
    payment_date: new Date().toISOString().split('T')[0],
    payment_method: '',
    notes: ''
  }
  showPaymentModal.value = true
}

const closePaymentModal = () => {
  showPaymentModal.value = false
  selectedPayable.value = null
}

const addPayment = async () => {
  if (!selectedPayable.value) return
  
  try {
    await payablesStore.addPayment(selectedPayable.value.id, paymentForm.value)
    closePaymentModal()
    await fetchStatistics()
  } catch (error) {
    console.error('Erro ao adicionar pagamento:', error)
  }
}

const deletePayable = async (payable: PayablePayment) => {
  if (confirm('Tem certeza que deseja excluir esta conta a pagar?')) {
    try {
      await payablesStore.deletePayable(payable.id)
      await fetchStatistics()
    } catch (error) {
      console.error('Erro ao excluir conta a pagar:', error)
    }
  }
}

// Payment History Modal Functions
const openPaymentHistoryModal = (payable: PayablePayment) => {
  selectedPayable.value = payable
  
  // Parse payment history safely using the helper function
  try {
    if (payable.payment_history) {
      let history
      if (typeof payable.payment_history === 'string') {
        history = JSON.parse(payable.payment_history)
      } else if (typeof payable.payment_history === 'object') {
        history = payable.payment_history
      } else {
        history = []
      }
      
      paymentHistory.value = Array.isArray(history) ? history : []
    } else {
      paymentHistory.value = []
    }
  } catch (error) {
    console.error('Erro ao parsear histórico de pagamentos:', error)
    paymentHistory.value = []
  }
  
  showPaymentHistoryModal.value = true
}

const closePaymentHistoryModal = () => {
  showPaymentHistoryModal.value = false
  selectedPayable.value = null
  paymentHistory.value = []
}

// Helper functions for payment history
const hasPaymentHistory = (payable: PayablePayment) => {
  if (!payable.payment_history) return false
  
  try {
    // Handle case where payment_history might be an object or string
    let history
    if (typeof payable.payment_history === 'string') {
      history = JSON.parse(payable.payment_history)
    } else if (typeof payable.payment_history === 'object') {
      history = payable.payment_history
    } else {
      return false
    }
    
    return Array.isArray(history) && history.length > 0
  } catch (error) {
    console.error('Erro ao verificar histórico de pagamentos:', error)
    return false
  }
}

const formatDateTime = (dateTime: string) => {
  if (!dateTime) return '-'
  
  try {
    const date = new Date(dateTime)
    return date.toLocaleString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch (error) {
    return dateTime
  }
}

const getPaymentMethodLabel = (method: string) => {
  const methods = {
    'dinheiro': 'Dinheiro',
    'pix': 'PIX',
    'transferencia': 'Transferência',
    'boleto': 'Boleto',
    'cartao_debito': 'Cartão de Débito',
    'cartao_credito': 'Cartão de Crédito'
  }
  return methods[method as keyof typeof methods] || method
}

// Lifecycle
onMounted(async () => {
  await fetchPayables()
  await fetchStatistics()
  await fetchSuppliers()
})
</script>