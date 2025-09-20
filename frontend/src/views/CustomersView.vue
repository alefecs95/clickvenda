<template>
  <AppLayout>
    <!-- Header da Página -->
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Clientes</h1>
        <p class="text-gray-600">Gerencie seus clientes</p>
      </div>
      
      <button
        @click="showCreateModal = true"
        class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors flex items-center space-x-2"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <span>Novo Cliente</span>
      </button>
    </div>
      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select 
              v-model="filters.active" 
              @change="loadCustomers"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="">Todos</option>
              <option :value="true">Ativo</option>
              <option :value="false">Inativo</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
            <input 
              type="text" 
              v-model="filters.search"
              @input="debounceSearch"
              placeholder="Nome, email, telefone..."
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ordenar por</label>
            <select 
              v-model="filters.sort_by" 
              @change="loadCustomers"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <option value="created_at">Data de criação</option>
              <option value="name">Nome</option>
              <option value="email">Email</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="customersStore.error" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-red-800">{{ customersStore.error }}</p>
          </div>
          <div class="ml-auto pl-3">
            <button @click="customersStore.clearError()" class="text-red-400 hover:text-red-600">
              <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Customers List -->
      <div v-if="customersStore.loading" class="text-center py-12">
        <svg class="animate-spin h-8 w-8 text-primary-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="mt-2 text-gray-600">Carregando clientes...</p>
      </div>

      <div v-else-if="customersStore.customers.length === 0" class="text-center py-12">
        <svg class="h-12 w-12 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <p class="mt-2 text-gray-600">Nenhum cliente encontrado</p>
        <button
          @click="showCreateModal = true"
          class="mt-4 bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors"
        >
          Criar Primeiro Cliente
        </button>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="customer in customersStore.customers"
          :key="customer.id"
          class="bg-white rounded-lg shadow p-6"
        >
          <div class="flex justify-between items-start mb-4">
            <div class="flex-1">
              <h3 class="text-lg font-semibold text-gray-900">{{ customer.name }}</h3>
              <p v-if="customer.email" class="text-sm text-gray-600">{{ customer.email }}</p>
              <p v-if="customer.phone" class="text-sm text-gray-600">{{ customer.phone }}</p>
              <p v-if="customer.cpf_cnpj" class="text-xs text-gray-500">{{ customer.cpf_cnpj }}</p>
            </div>
            <div class="ml-4">
              <span
                :class="{
                  'bg-green-100 text-green-800': customer.active,
                  'bg-red-100 text-red-800': !customer.active
                }"
                class="inline-flex px-2 py-1 text-xs font-medium rounded-full"
              >
                {{ customer.active ? 'Ativo' : 'Inativo' }}
              </span>
            </div>
          </div>
          
          <div v-if="customer.address" class="mb-4">
            <p class="text-sm text-gray-600">{{ customer.address }}</p>
          </div>
          
          <!-- Informações de Crédito -->
          <div v-if="customer.credit_limit && customer.credit_limit > 0" class="mb-4 p-3 bg-gray-50 rounded-lg">
            <div class="flex justify-between items-start mb-2">
              <span class="text-xs font-medium text-gray-700">Limite de Crédito</span>
              <span 
                :class="getCreditStatusColor(customer)"
                class="inline-flex px-2 py-1 text-xs font-medium rounded-full"
              >
                {{ getCreditStatusLabel(customer) }}
              </span>
            </div>
            
            <div class="space-y-1">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Limite:</span>
                <span class="font-medium">R$ {{ formatPrice(customer.credit_limit) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Usado:</span>
                <span class="font-medium text-red-600">R$ {{ formatPrice(customer.credit_used) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Disponível:</span>
                <span class="font-medium text-green-600">R$ {{ formatPrice(customer.credit_limit - customer.credit_used) }}</span>
              </div>
            </div>
            
            <!-- Barra de Progresso -->
            <div class="mt-2">
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div 
                  :class="getCreditProgressColor(customer)"
                  class="h-2 rounded-full transition-all duration-300"
                  :style="`width: ${getCreditPercentage(customer)}%`"
                ></div>
              </div>
              <div class="text-xs text-gray-500 mt-1">
                {{ getCreditPercentage(customer) }}% utilizado
              </div>
            </div>
          </div>
          
          <div class="border-t border-gray-200 pt-4">
            <div class="flex justify-between items-center">
              <div class="text-xs text-gray-500">
                Criado em {{ formatDate(customer.created_at) }}
              </div>
              <div class="flex space-x-2">
                <button
                  @click="viewCustomerHistory(customer)"
                  class="text-purple-600 hover:text-purple-900 text-sm font-medium"
                  title="Ver histórico completo"
                >
                  Histórico
                </button>
                <button
                  @click="editCustomer(customer)"
                  class="text-primary-600 hover:text-primary-900 text-sm font-medium"
                >
                  Editar
                </button>
                <button
                  @click="manageCreditLimit(customer)"
                  class="text-blue-600 hover:text-blue-900 text-sm font-medium"
                >
                  Crédito
                </button>
                <button
                  @click="toggleCustomerStatus(customer)"
                  :class="{
                    'text-red-600 hover:text-red-900': customer.active,
                    'text-green-600 hover:text-green-900': !customer.active
                  }"
                  class="text-sm font-medium"
                >
                  {{ customer.active ? 'Desativar' : 'Ativar' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="customersStore.pagination && customersStore.pagination.last_page > 1" class="bg-white rounded-lg shadow p-4 mt-6">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Mostrando {{ customersStore.pagination.current_page }} de {{ customersStore.pagination.last_page }} páginas
          </div>
          <div class="flex space-x-2">
            <button
              @click="changePage(customersStore.pagination.current_page - 1)"
              :disabled="customersStore.pagination.current_page <= 1"
              class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50 disabled:opacity-50"
            >
              Anterior
            </button>
            <button
              v-for="page in Math.min(5, customersStore.pagination.last_page)"
              :key="page"
              @click="changePage(page)"
              :class="{
                'bg-primary-600 text-white': page === customersStore.pagination.current_page,
                'bg-white text-gray-700 hover:bg-gray-50': page !== customersStore.pagination.current_page
              }"
              class="px-3 py-1 border border-gray-300 rounded text-sm"
            >
              {{ page }}
            </button>
            <button
              @click="changePage(customersStore.pagination.current_page + 1)"
              :disabled="customersStore.pagination.current_page >= customersStore.pagination.last_page"
              class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50 disabled:opacity-50"
            >
              Próxima
            </button>
          </div>
        </div>
      </div>

    <!-- Modal de Criação/Edição -->
    <div v-if="showCreateModal || editingCustomer" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            {{ editingCustomer ? 'Editar Cliente' : 'Novo Cliente' }}
          </h3>
          
          <form @submit.prevent="saveCustomer">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                <input 
                  v-model="customerForm.name"
                  type="text" 
                  required
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input 
                  v-model="customerForm.email"
                  type="email" 
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                <input 
                  v-model="customerForm.phone"
                  type="text" 
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">CPF/CNPJ</label>
                <input 
                  v-model="customerForm.cpf_cnpj"
                  type="text" 
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Endereço</label>
                <textarea 
                  v-model="customerForm.address"
                  rows="3"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                ></textarea>
              </div>
              
              <!-- Campos de Crédito -->
              <div class="border-t border-gray-200 pt-4">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Limite de Crédito</h4>
                
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Limite (R$)</label>
                    <input 
                      v-model="customerForm.credit_limit"
                      type="number" 
                      step="0.01"
                      min="0"
                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                      placeholder="0,00"
                    >
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <div class="mt-2 text-sm text-gray-600">
                      <span v-if="!customerForm.credit_limit || customerForm.credit_limit <= 0">
                        Pagamento à vista
                      </span>
                      <span v-else class="text-green-600">
                        Crédito habilitado
                      </span>
                    </div>
                  </div>
                </div>
                
                <div class="mt-3">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Observações sobre crédito</label>
                  <textarea 
                    v-model="customerForm.credit_notes"
                    rows="2"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Ex: Cliente VIP, Limite aprovado pela gerência..."
                  ></textarea>
                </div>
              </div>
              
              <div class="flex items-center">
                <input 
                  v-model="customerForm.active"
                  type="checkbox" 
                  id="active"
                  class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                >
                <label for="active" class="ml-2 block text-sm text-gray-900">
                  Cliente ativo
                </label>
              </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-400"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="customersStore.loading"
                class="px-4 py-2 bg-primary-600 text-white rounded-md text-sm font-medium hover:bg-primary-700 disabled:opacity-50"
              >
                {{ customersStore.loading ? 'Salvando...' : (editingCustomer ? 'Atualizar' : 'Criar') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal de Gestão de Crédito -->
    <div v-if="showCreditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Gerenciar Crédito - {{ selectedCustomer?.name }}
          </h3>
          
          <!-- Informações Atuais -->
          <div v-if="selectedCustomer" class="mb-6 p-4 bg-gray-50 rounded-lg">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Situação Atual</h4>
            <div class="space-y-1 text-sm">
              <div class="flex justify-between">
                <span>Limite:</span>
                <span class="font-medium">R$ {{ formatPrice(selectedCustomer.credit_limit) }}</span>
              </div>
              <div class="flex justify-between">
                <span>Usado:</span>
                <span class="font-medium text-red-600">R$ {{ formatPrice(selectedCustomer.credit_used) }}</span>
              </div>
              <div class="flex justify-between">
                <span>Disponível:</span>
                <span class="font-medium text-green-600">R$ {{ formatPrice(selectedCustomer.credit_limit - selectedCustomer.credit_used) }}</span>
              </div>
            </div>
          </div>

          <!-- Abas -->
          <div class="border-b border-gray-200 mb-4">
            <nav class="-mb-px flex space-x-8">
              <button
                @click="creditTab = 'limit'"
                :class="{
                  'border-primary-500 text-primary-600': creditTab === 'limit',
                  'border-transparent text-gray-500 hover:text-gray-700': creditTab !== 'limit'
                }"
                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
              >
                Alterar Limite
              </button>
              <button
                @click="creditTab = 'adjust'"
                :class="{
                  'border-primary-500 text-primary-600': creditTab === 'adjust',
                  'border-transparent text-gray-500 hover:text-gray-700': creditTab !== 'adjust'
                }"
                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
              >
                Ajustar Saldo
              </button>
            </nav>
          </div>

          <!-- Aba Alterar Limite -->
          <div v-if="creditTab === 'limit'">
            <form @submit.prevent="updateCreditLimit">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Novo Limite (R$)</label>
                  <input 
                    v-model="creditForm.limit"
                    type="number" 
                    step="0.01"
                    min="0"
                    required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="0,00"
                  >
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                  <textarea 
                    v-model="creditForm.notes"
                    rows="3"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Motivo da alteração..."
                  ></textarea>
                </div>
              </div>
              
              <div class="mt-6 flex space-x-3">
                <button
                  type="submit"
                  :disabled="customersStore.loading"
                  class="flex-1 bg-primary-600 text-white py-2 px-4 rounded-md hover:bg-primary-700 disabled:opacity-50 text-sm font-medium"
                >
                  {{ customersStore.loading ? 'Atualizando...' : 'Atualizar Limite' }}
                </button>
                <button
                  type="button"
                  @click="closeCreditModal"
                  class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400 text-sm font-medium"
                >
                  Cancelar
                </button>
              </div>
            </form>
          </div>

          <!-- Aba Ajustar Saldo -->
          <div v-if="creditTab === 'adjust'">
            <form @submit.prevent="adjustCredit">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Ajuste</label>
                  <select 
                    v-model="creditForm.type"
                    required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="payment">Pagamento (reduz saldo devedor)</option>
                    <option value="charge">Cobrança (aumenta saldo devedor)</option>
                    <option value="adjustment">Ajuste manual</option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Valor (R$)</label>
                  <input 
                    v-model="creditForm.amount"
                    type="number" 
                    step="0.01"
                    min="0.01"
                    required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="0,00"
                  >
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                  <textarea 
                    v-model="creditForm.notes"
                    rows="3"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Descrição do ajuste..."
                  ></textarea>
                </div>
              </div>
              
              <div class="mt-6 flex space-x-3">
                <button
                  type="submit"
                  :disabled="customersStore.loading"
                  class="flex-1 bg-primary-600 text-white py-2 px-4 rounded-md hover:bg-primary-700 disabled:opacity-50 text-sm font-medium"
                >
                  {{ customersStore.loading ? 'Processando...' : 'Processar Ajuste' }}
                </button>
                <button
                  type="button"
                  @click="closeCreditModal"
                  class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400 text-sm font-medium"
                >
                  Cancelar
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Histórico do Cliente -->
    <div v-if="showHistoryModal && selectedCustomer" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-10 mx-auto p-6 border w-full max-w-6xl shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-bold text-gray-900">
            Histórico Completo - {{ selectedCustomer.name }}
          </h3>
          <button
            @click="showHistoryModal = false"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <div v-if="historyLoading" class="flex justify-center items-center py-12">
          <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span class="ml-2 text-gray-600">Carregando histórico...</span>
        </div>

        <div v-else-if="customerHistory" class="space-y-6">
          <!-- Resumo do Cliente -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 rounded-lg p-4 text-center">
              <div class="text-2xl font-bold text-blue-600">{{ customerHistory.statistics.total_orders }}</div>
              <div class="text-sm text-blue-700">Pedidos de Venda</div>
            </div>
            <div class="bg-purple-50 rounded-lg p-4 text-center">
              <div class="text-2xl font-bold text-purple-600">{{ customerHistory.statistics.total_service_orders }}</div>
              <div class="text-sm text-purple-700">Ordens de Serviço</div>
            </div>
            <div class="bg-green-50 rounded-lg p-4 text-center">
              <div class="text-2xl font-bold text-green-600">R$ {{ formatPrice(customerHistory.statistics.total_spent + customerHistory.statistics.total_service_spent) }}</div>
              <div class="text-sm text-green-700">Total Gasto</div>
            </div>
            <div class="bg-orange-50 rounded-lg p-4 text-center">
              <div class="text-2xl font-bold text-orange-600">R$ {{ formatPrice(customerHistory.statistics.total_credit_used) }}</div>
              <div class="text-sm text-orange-700">Crédito Utilizado</div>
            </div>
          </div>

          <!-- Tabs -->
          <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
              <button
                @click="historyTab = 'orders'"
                :class="historyTab === 'orders' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="py-2 px-1 border-b-2 font-medium text-sm"
              >
                Pedidos ({{ customerHistory.orders.length }})
              </button>
              <button
                @click="historyTab = 'service-orders'"
                :class="historyTab === 'service-orders' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="py-2 px-1 border-b-2 font-medium text-sm"
              >
                OS ({{ customerHistory.serviceOrders.length }})
              </button>
              <button
                @click="historyTab = 'receivables'"
                :class="historyTab === 'receivables' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="py-2 px-1 border-b-2 font-medium text-sm"
              >
                Contas a Receber ({{ customerHistory.receivables.length }})
              </button>
            </nav>
          </div>

          <!-- Conteúdo das Tabs -->
          <div class="mt-6">
            <!-- Tab de Pedidos -->
            <div v-if="historyTab === 'orders'" class="space-y-4">
              <div v-if="customerHistory.orders.length === 0" class="text-center py-8">
                <svg class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-gray-600">Nenhum pedido encontrado</p>
              </div>
              
              <div v-else class="space-y-3">
                <div
                  v-for="order in customerHistory.orders"
                  :key="order.id"
                  class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                >
                  <div class="flex justify-between items-start mb-2">
                    <div>
                      <h4 class="font-semibold text-gray-900">#{{ order.order_number }}</h4>
                      <p class="text-sm text-gray-600">{{ formatDate(order.created_at) }} às {{ formatTime(order.created_at) }}</p>
                    </div>
                    <div class="text-right">
                      <div class="font-bold text-lg">R$ {{ formatPrice(order.final_amount) }}</div>
                      <span class="text-xs px-2 py-1 rounded-full" :class="getOrderStatusClass(order)">
                        {{ getOrderStatusText(order) }}
                      </span>
                    </div>
                  </div>
                  
                  <div class="text-sm text-gray-600">
                    <div class="flex items-center justify-between">
                      <div>
                        <span class="font-medium">Pagamento:</span> 
                        <span class="ml-1 px-2 py-1 text-xs rounded-full" :class="getPaymentMethodBadgeClass(order.payment_method)">
                          {{ getPaymentMethodText(order.payment_method) }}
                        </span>
                      </div>
                      <div v-if="order.discount_amount > 0" class="text-green-600 text-xs">
                        Desconto: R$ {{ formatPrice(order.discount_amount) }}
                      </div>
                    </div>
                  </div>
                  
                  <div v-if="order.notes" class="mt-2 text-xs text-gray-500 italic bg-gray-50 rounded p-2">
                    "{{ order.notes }}"
                  </div>
                </div>
              </div>
            </div>

            <!-- Tab de OS -->
            <div v-if="historyTab === 'service-orders'" class="space-y-4">
              <div v-if="customerHistory.serviceOrders.length === 0" class="text-center py-8">
                <svg class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-gray-600">Nenhuma OS encontrada</p>
              </div>
              
              <div v-else class="space-y-3">
                <div
                  v-for="serviceOrder in customerHistory.serviceOrders"
                  :key="serviceOrder.id"
                  class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                >
                  <div class="flex justify-between items-start mb-2">
                    <div>
                      <h4 class="font-semibold text-gray-900">OS #{{ serviceOrder.order_number }}</h4>
                      <p class="text-sm text-gray-600">{{ formatDate(serviceOrder.opening_date) }} às {{ formatTime(serviceOrder.opening_date) }}</p>
                      <p v-if="serviceOrder.vehicle" class="text-xs text-gray-500">
                        Veículo: {{ serviceOrder.vehicle.plate }} - {{ serviceOrder.vehicle.make }} {{ serviceOrder.vehicle.model }}
                      </p>
                    </div>
                    <div class="text-right">
                      <div class="font-bold text-lg">R$ {{ formatPrice(serviceOrder.final_amount) }}</div>
                      <span class="text-xs px-2 py-1 rounded-full" :class="getServiceOrderStatusClass(serviceOrder)">
                        {{ getServiceOrderStatusText(serviceOrder) }}
                      </span>
                    </div>
                  </div>
                  
                  <div class="text-sm text-gray-600">
                    <div class="flex items-center justify-between">
                      <div>
                        <span class="font-medium">Pagamento:</span> 
                        <span class="ml-1 px-2 py-1 text-xs rounded-full" :class="getPaymentMethodBadgeClass(serviceOrder.payment_method)">
                          {{ getPaymentMethodText(serviceOrder.payment_method) }}
                        </span>
                      </div>
                      <div v-if="serviceOrder.discount_amount > 0" class="text-green-600 text-xs">
                        Desconto: R$ {{ formatPrice(serviceOrder.discount_amount) }}
                      </div>
                    </div>
                  </div>
                  
                  <div v-if="serviceOrder.problem_description" class="mt-2 text-xs text-gray-500 italic bg-gray-50 rounded p-2">
                    <strong>Problema:</strong> {{ serviceOrder.problem_description }}
                  </div>
                  
                  <!-- Status de Pagamento -->
                  <div v-if="serviceOrder.total_paid !== undefined" class="mt-2 text-xs">
                    <div class="flex justify-between items-center">
                      <span class="text-gray-600">Status Pagamento:</span>
                      <span class="px-2 py-1 rounded-full text-xs font-semibold" :class="getServiceOrderPaymentStatusClass(serviceOrder)">
                        {{ getServiceOrderPaymentStatusText(serviceOrder) }}
                      </span>
                    </div>
                    <div v-if="serviceOrder.total_paid > 0" class="mt-1 text-xs text-gray-600">
                      Pago: R$ {{ formatPrice(serviceOrder.total_paid) }} | Restante: R$ {{ formatPrice(serviceOrder.remaining_amount) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tab de Contas a Receber -->
            <div v-if="historyTab === 'receivables'" class="space-y-4">
              <div v-if="customerHistory.receivables.length === 0" class="text-center py-8">
                <svg class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
                <p class="text-gray-600">Nenhuma conta a receber encontrada</p>
              </div>
              
              <div v-else class="space-y-3">
                <div
                  v-for="receivable in customerHistory.receivables"
                  :key="receivable.id"
                  class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                >
                  <div class="flex justify-between items-start mb-2">
                    <div>
                      <h4 class="font-semibold text-gray-900">#{{ receivable.order?.order_number || 'N/A' }}</h4>
                      <p class="text-sm text-gray-600">{{ formatDate(receivable.created_at) }}</p>
                      <p class="text-xs text-gray-500">Vencimento: {{ formatDate(receivable.due_date) }}</p>
                    </div>
                    <div class="text-right">
                      <div class="font-bold text-lg text-primary-600">R$ {{ formatPrice(receivable.total_receivable_amount) }}</div>
                      <div class="text-sm">
                        <span class="text-green-600">Pago: R$ {{ formatPrice(receivable.paid_amount) }}</span>
                      </div>
                      <div class="text-sm">
                        <span class="text-red-600">Restante: R$ {{ formatPrice(receivable.remaining_amount) }}</span>
                      </div>
                      <span class="text-xs px-2 py-1 rounded-full mt-1 inline-block" :class="getReceivableStatusClass(receivable)">
                        {{ getReceivableStatusText(receivable) }}
                      </span>
                    </div>
                  </div>
                  
                  <!-- Histórico de Pagamentos -->
                  <div v-if="receivable.payment_history?.length > 0" class="mt-3 pt-3 border-t border-gray-200">
                    <h5 class="text-xs font-medium text-gray-700 mb-2">Histórico de Pagamentos:</h5>
                    <div class="space-y-1">
                      <div v-for="(payment, index) in receivable.payment_history" :key="index" class="flex justify-between items-center text-xs bg-gray-50 rounded p-2">
                        <div class="flex items-center space-x-2">
                          <span>{{ formatDate(payment.date) }}</span>
                          <span class="px-2 py-1 rounded-full" :class="getPaymentMethodBadgeClass(payment.method)">
                            {{ getPaymentMethodText(payment.method) }}
                          </span>
                        </div>
                        <span class="font-medium text-green-600">R$ {{ formatPrice(payment.amount) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Botão Fechar -->
        <div class="mt-6 flex justify-end">
          <button
            @click="showHistoryModal = false"
            class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium"
          >
            Fechar
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import { useCustomersStore, type Customer } from '@/stores/customers'
import AppLayout from '@/components/AppLayout.vue'
const customersStore = useCustomersStore()

const showCreateModal = ref(false)
const editingCustomer = ref<Customer | null>(null)

const filters = reactive({
  search: '',
  active: undefined as boolean | undefined,
  sort_by: 'created_at',
  sort_order: 'desc' as 'asc' | 'desc',
  per_page: 15
})

const customerForm = reactive({
  name: '',
  email: '',
  phone: '',
  cpf_cnpj: '',
  address: '',
  active: true,
  credit_limit: 0,
  credit_notes: ''
})

// Variáveis para gestão de crédito
const showCreditModal = ref(false)
const selectedCustomer = ref<Customer | null>(null)
const creditTab = ref<'limit' | 'adjust'>('limit')

const creditForm = reactive({
  limit: 0,
  amount: 0,
  type: 'payment' as 'payment' | 'charge' | 'adjustment',
  notes: ''
})

// Variáveis para histórico do cliente
const showHistoryModal = ref(false)
const customerHistory = ref<any>(null)
const historyLoading = ref(false)
const historyTab = ref('orders')

let searchTimeout: ReturnType<typeof setTimeout> | null = null

const loadCustomers = async () => {
  try {
    await customersStore.fetchCustomers(filters)
  } catch (error) {
    console.error('Erro ao carregar clientes:', error)
  }
}

const debounceSearch = () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
  searchTimeout = setTimeout(() => {
    loadCustomers()
  }, 500)
}

const changePage = async (page: number) => {
  if (page >= 1 && page <= (customersStore.pagination?.last_page || 1)) {
    await customersStore.fetchCustomers({ ...filters, page })
  }
}

const editCustomer = (customer: Customer) => {
  editingCustomer.value = customer
  Object.assign(customerForm, {
    ...customer,
    credit_limit: customer.credit_limit || 0,
    credit_notes: customer.credit_notes || ''
  })
  showCreateModal.value = true
}

const toggleCustomerStatus = async (customer: Customer) => {
  const action = customer.active ? 'desativar' : 'ativar'
  if (confirm(`Tem certeza que deseja ${action} este cliente?`)) {
    try {
      await customersStore.updateCustomer(customer.id, { active: !customer.active })
      await loadCustomers()
    } catch (error) {
      console.error('Erro ao alterar status do cliente:', error)
    }
  }
}

const saveCustomer = async () => {
  try {
    // Preparar dados com tipos corretos
    const formData = {
      ...customerForm,
      credit_limit: typeof customerForm.credit_limit === 'string' ? parseFloat(customerForm.credit_limit) || 0 : customerForm.credit_limit,
      active: Boolean(customerForm.active)
    };
    
    if (editingCustomer.value) {
      await customersStore.updateCustomer(editingCustomer.value.id, formData)
    } else {
      await customersStore.createCustomer(formData)
    }
    closeModal()
    await loadCustomers()
  } catch (error) {
    console.error('Erro ao salvar cliente:', error)
    // Não fecha o modal em caso de erro para o usuário poder corrigir
    // Exibe um alerta para garantir que o usuário veja a mensagem de erro
    if (customersStore.error) {
      alert(`Erro ao salvar cliente: ${customersStore.error}`)
    }
  }
}

const closeModal = () => {
  showCreateModal.value = false
  editingCustomer.value = null
  Object.assign(customerForm, {
    name: '',
    email: '',
    phone: '',
    cpf_cnpj: '',
    address: '',
    active: true,
    credit_limit: 0,
    credit_notes: ''
  })
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const formatPrice = (price: any) => {
  const numPrice = parseFloat(price) || 0
  return numPrice.toFixed(2).replace('.', ',')
}

// Funções de gestão de crédito
const viewCustomerHistory = async (customer: Customer) => {
  selectedCustomer.value = customer
  historyLoading.value = true
  showHistoryModal.value = true
  
  try {
    // Usar o serviço de API configurado
    const api = (await import('@/services/api')).default
    
    // Buscar histórico completo do cliente
    const [ordersResponse, receivablesResponse, serviceOrdersResponse] = await Promise.all([
      api.get(`/orders?customer_id=${customer.id}&per_page=100`),
      api.get(`/receivables/customer/${customer.id}`),
      api.get(`/service-orders?customer_id=${customer.id}&per_page=100`)
    ])
    
    // Garantir que orders seja sempre um array
    const orders = ordersResponse.data.success 
      ? (Array.isArray(ordersResponse.data.data) ? ordersResponse.data.data : [])
      : []
    
    // Garantir que receivables seja sempre um array  
    const receivables = receivablesResponse.data.success 
      ? (Array.isArray(receivablesResponse.data.data) ? receivablesResponse.data.data : [])
      : []
    
    // Debug: verificar estrutura da resposta de service-orders
    console.log('Service Orders Response:', serviceOrdersResponse.data)
    console.log('Service Orders Response Success:', serviceOrdersResponse.data.success)
    console.log('Service Orders Response Data:', serviceOrdersResponse.data.data)
    
    // Garantir que serviceOrders seja sempre um array (mesma lógica do store)
    const serviceOrders = serviceOrdersResponse.data.success 
      ? (serviceOrdersResponse.data.data.data || serviceOrdersResponse.data.data || [])
      : []
    
    // Debug: mostrar dados recebidos
    console.log('Orders recebidos:', orders)
    console.log('Receivables recebidos:', receivables)
    console.log('Service Orders processados:', serviceOrders)
    
    customerHistory.value = {
      customer: customer,
      orders: orders,
      serviceOrders: serviceOrders,
      receivables: receivables,
      statistics: {
        total_orders: orders.length,
        total_service_orders: serviceOrders.length,
        total_spent: orders.reduce((sum: number, order: any) => {
          // Tentar diferentes campos de valor
          const amount = order.final_amount || order.subtotal || order.total_amount || order.total || 0
          const parsedAmount = parseFloat(amount) || 0
          console.log(`Pedido ${order.order_number}: valor = ${amount}, parsed = ${parsedAmount}`)
          return sum + parsedAmount
        }, 0),
        total_service_spent: serviceOrders.reduce((sum: number, os: any) => {
          const amount = os.final_amount || os.total_amount || 0
          const parsedAmount = parseFloat(amount) || 0
          console.log(`OS ${os.order_number}: valor = ${amount}, parsed = ${parsedAmount}`)
          return sum + parsedAmount
        }, 0),
        total_credit_used: receivables.reduce((sum: number, r: any) => {
          return sum + (parseFloat(r.total_receivable_amount) || 0)
        }, 0),
        total_pending: receivables
          .filter((r: any) => ['pending', 'partial'].includes(r.status))
          .reduce((sum: number, r: any) => {
            return sum + (parseFloat(r.remaining_amount) || 0)
          }, 0)
      }
    }
  } catch (error) {
    console.error('Erro ao carregar histórico do cliente:', error)
    
    let errorMessage = 'Erro ao carregar histórico do cliente.'
    if (error instanceof Error) {
      if (error.message.includes('Failed to fetch') || error.message.includes('Network Error')) {
        errorMessage = 'Erro de conexão. Verifique se o servidor está rodando.'
      } else if (error.message.includes('401')) {
        errorMessage = 'Sessão expirada. Faça login novamente.'
      } else {
        errorMessage = error.message
      }
    }
    
    alert(errorMessage)
    showHistoryModal.value = false
  } finally {
    historyLoading.value = false
  }
}

const manageCreditLimit = (customer: Customer) => {
  selectedCustomer.value = customer
  creditForm.limit = customer.credit_limit || 0
  creditForm.notes = ''
  creditForm.amount = 0
  creditForm.type = 'payment'
  creditTab.value = 'limit'
  showCreditModal.value = true
}

const closeCreditModal = () => {
  showCreditModal.value = false
  selectedCustomer.value = null
  Object.assign(creditForm, {
    limit: 0,
    amount: 0,
    type: 'payment',
    notes: ''
  })
}

const updateCreditLimit = async () => {
  if (!selectedCustomer.value) return
  
  try {
    const result = await customersStore.updateCreditLimit(
      selectedCustomer.value.id,
      creditForm.limit,
      creditForm.notes
    )
    
    if (result.success) {
      closeCreditModal()
      await loadCustomers()
      alert('Limite de crédito atualizado com sucesso!')
    } else {
      alert('Erro ao atualizar limite: ' + result.error)
    }
  } catch (error) {
    console.error('Erro ao atualizar limite:', error)
    alert('Erro ao atualizar limite de crédito')
  }
}

const adjustCredit = async () => {
  if (!selectedCustomer.value) return
  
  try {
    const result = await customersStore.adjustCredit(
      selectedCustomer.value.id,
      creditForm.amount,
      creditForm.type,
      creditForm.notes
    )
    
    if (result.success) {
      closeCreditModal()
      await loadCustomers()
      alert('Crédito ajustado com sucesso!')
    } else {
      alert('Erro ao ajustar crédito: ' + result.error)
    }
  } catch (error) {
    console.error('Erro ao ajustar crédito:', error)
    alert('Erro ao ajustar crédito')
  }
}

// Funções auxiliares para exibição de crédito
const getCreditPercentage = (customer: Customer) => {
  if (!customer.credit_limit || customer.credit_limit <= 0) return 0
  return Math.min(100, Math.round((customer.credit_used / customer.credit_limit) * 100))
}

const getCreditStatusLabel = (customer: Customer) => {
  const percentage = getCreditPercentage(customer)
  
  if (percentage >= 100) return 'Excedido'
  if (percentage >= 90) return 'Crítico'
  if (percentage >= 70) return 'Alto'
  if (percentage >= 50) return 'Médio'
  return 'Baixo'
}

const getCreditStatusColor = (customer: Customer) => {
  const percentage = getCreditPercentage(customer)
  
  if (percentage >= 100) return 'bg-red-100 text-red-800'
  if (percentage >= 90) return 'bg-red-100 text-red-800'
  if (percentage >= 70) return 'bg-yellow-100 text-yellow-800'
  if (percentage >= 50) return 'bg-blue-100 text-blue-800'
  return 'bg-green-100 text-green-800'
}

const getCreditProgressColor = (customer: Customer) => {
  const percentage = getCreditPercentage(customer)
  
  if (percentage >= 100) return 'bg-red-500'
  if (percentage >= 90) return 'bg-red-400'
  if (percentage >= 70) return 'bg-yellow-400'
  if (percentage >= 50) return 'bg-blue-400'
  return 'bg-green-400'
}

// Funções auxiliares para histórico
const formatTime = (dateString: string) => {
  return new Date(dateString).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}

const getPaymentMethodText = (method: string) => {
  const methods: Record<string, string> = {
    'money': 'Dinheiro',
    'card': 'Cartão',
    'pix': 'PIX',
    'credit': 'A Prazo (Crédito)',
    'multiple': 'Múltiplas Formas',
    'dinheiro': 'Dinheiro',
    'cartao_debito': 'Cartão de Débito',
    'cartao_credito': 'Cartão de Crédito',
    'transferencia': 'Transferência',
    'aprazo': 'A Prazo'
  }
  return methods[method] || method
}

const getPaymentMethodBadgeClass = (method: string) => {
  const badgeClasses: Record<string, string> = {
    'money': 'bg-green-100 text-green-800',
    'dinheiro': 'bg-green-100 text-green-800',
    'card': 'bg-blue-100 text-blue-800',
    'cartao_debito': 'bg-blue-100 text-blue-800',
    'cartao_credito': 'bg-blue-100 text-blue-800',
    'pix': 'bg-purple-100 text-purple-800',
    'credit': 'bg-orange-100 text-orange-800',
    'aprazo': 'bg-orange-100 text-orange-800',
    'transferencia': 'bg-indigo-100 text-indigo-800',
    'multiple': 'bg-gray-100 text-gray-800'
  }
  return badgeClasses[method] || 'bg-gray-100 text-gray-800'
}

// Funções específicas para OS
const getServiceOrderStatusText = (serviceOrder: any) => {
  const statusMap: Record<string, string> = {
    'aberta': 'Aberta',
    'em_andamento': 'Em Andamento',
    'aguardando_aprovacao': 'Aguardando Aprovação',
    'concluida': 'Concluída',
    'cancelada': 'Cancelada'
  }
  return statusMap[serviceOrder.status] || serviceOrder.status
}

const getServiceOrderStatusClass = (serviceOrder: any) => {
  const statusClasses: Record<string, string> = {
    'aberta': 'bg-blue-100 text-blue-800',
    'em_andamento': 'bg-yellow-100 text-yellow-800',
    'aguardando_aprovacao': 'bg-purple-100 text-purple-800',
    'concluida': 'bg-green-100 text-green-800',
    'cancelada': 'bg-red-100 text-red-800'
  }
  return statusClasses[serviceOrder.status] || 'bg-gray-100 text-gray-800'
}

const getServiceOrderPaymentStatusText = (serviceOrder: any) => {
  if (!serviceOrder.payment_method) {
    return 'Não Informado'
  }
  
  // Se tem pagamentos registrados
  if (serviceOrder.total_paid !== undefined && serviceOrder.remaining_amount !== undefined) {
    if (serviceOrder.remaining_amount <= 0) {
      return 'Pago'
    } else if (serviceOrder.total_paid > 0) {
      return 'Parcial'
    } else {
      return 'Pendente'
    }
  }
  
  // Se é a prazo, considera pendente
  if (serviceOrder.payment_method === 'aprazo') {
    return 'A Prazo'
  }
  
  // Para outras formas, considera pago
  return 'Pago'
}

const getServiceOrderPaymentStatusClass = (serviceOrder: any) => {
  const status = getServiceOrderPaymentStatusText(serviceOrder)
  
  const statusClasses: Record<string, string> = {
    'Pago': 'bg-green-100 text-green-800',
    'Parcial': 'bg-yellow-100 text-yellow-800',
    'Pendente': 'bg-red-100 text-red-800',
    'A Prazo': 'bg-blue-100 text-blue-800',
    'Não Informado': 'bg-gray-100 text-gray-800'
  }
  
  return statusClasses[status] || 'bg-gray-100 text-gray-800'
}

const getReceivableStatusText = (receivable: any) => {
  if (receivable.status === 'paid') return 'Pago'
  if (receivable.status === 'partial') return 'Parcial'
  if (receivable.status === 'overdue') return 'Vencido'
  return 'Pendente'
}

const getReceivableStatusClass = (receivable: any) => {
  if (receivable.status === 'paid') return 'bg-green-100 text-green-800'
  if (receivable.status === 'partial') return 'bg-blue-100 text-blue-800'
  if (receivable.status === 'overdue') return 'bg-red-100 text-red-800'
  return 'bg-orange-100 text-orange-800'
}

const getOrderStatusText = (order: any) => {
  // Se o pagamento foi a prazo (crédito), sempre mostrar como "A Prazo"
  if (order.payment_method === 'credit') {
    return 'A Prazo'
  }
  
  // Para outros métodos de pagamento, usar o status do pedido
  if (order.status === 'completed') return 'Finalizado'
  if (order.status === 'pending') return 'Pendente'
  if (order.status === 'cancelled') return 'Cancelado'
  
  return 'Pendente'
}

const getOrderStatusClass = (order: any) => {
  // Se o pagamento foi a prazo (crédito), usar cor azul
  if (order.payment_method === 'credit') {
    return 'bg-blue-100 text-blue-800'
  }
  
  // Para outros métodos de pagamento, usar cores baseadas no status
  if (order.status === 'completed') return 'bg-green-100 text-green-800'
  if (order.status === 'pending') return 'bg-orange-100 text-orange-800'
  if (order.status === 'cancelled') return 'bg-red-100 text-red-800'
  
  return 'bg-orange-100 text-orange-800'
}

onMounted(() => {
  loadCustomers()
})
</script>
