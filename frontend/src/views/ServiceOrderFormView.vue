<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center">
            <button
              @click="router.back()"
              class="mr-4 p-2 text-gray-600 hover:text-gray-900"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
              </svg>
            </button>
            <h1 class="text-xl font-semibold text-gray-900">
              {{ isEdit ? 'Editar OS' : 'Nova Ordem de Serviço' }}
            </h1>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Dados Básicos -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Dados Básicos</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Cliente (Opcional - apenas para faturamento)</label>
              <select 
                v-model="form.customer_id" 
                @change="onCustomerChange"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
              >
                <option value="">Selecione um cliente (opcional)</option>
                <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                  {{ customer.name }}
                </option>
              </select>
              
              <!-- Informações de Crédito do Cliente -->
              <div v-if="selectedCustomer && form.payment_method === 'credit'" class="mt-2 p-3 bg-blue-50 rounded-lg">
                <div class="flex justify-between items-center mb-2">
                  <span class="text-sm font-medium text-gray-700">Limite de Crédito:</span>
                  <span class="text-sm font-bold text-blue-600">{{ formatCurrency(selectedCustomer.credit_limit || 0) }}</span>
                </div>
                <div class="flex justify-between items-center mb-2">
                  <span class="text-sm font-medium text-gray-700">Crédito Usado:</span>
                  <span class="text-sm font-bold text-orange-600">{{ formatCurrency(selectedCustomer.credit_used || 0) }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm font-medium text-gray-700">Crédito Disponível:</span>
                  <span class="text-sm font-bold" :class="getAvailableCreditClass(selectedCustomer)">
                    {{ formatCurrency(getAvailableCredit(selectedCustomer)) }}
                  </span>
                </div>
                <div v-if="!hasEnoughCredit" class="mt-2 p-2 bg-red-100 border border-red-300 rounded">
                  <p class="text-xs text-red-700">
                    ⚠️ Cliente não possui crédito suficiente para esta OS a prazo
                  </p>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-4">Veículo/Equipamento *</label>
              
              <!-- Grid com 2 colunas para os campos -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <!-- Coluna 1: PLACA e MARCA -->
                <div class="space-y-4">
                  <!-- Campo PLACA -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">PLACA *</label>
                    <div class="relative">
                      <input 
                        v-model="vehicleForm.plate" 
                        @input="onPlateInput"
                        @keyup.enter="searchVehicleByPlate"
                        type="text" 
                        placeholder="INSERIR A PLACA" 
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        required
                      >
                      <button
                        type="button"
                        @click="searchVehicleByPlate"
                        :disabled="!vehicleForm.plate"
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 disabled:opacity-50"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                      </button>
                    </div>
                  </div>
                  
                  <!-- Campo MARCA -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">MARCA</label>
                    <input 
                      v-model="vehicleForm.make" 
                      type="text" 
                      placeholder="MARCA" 
                      class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 text-gray-600"
                      readonly
                    >
                  </div>
                </div>
                
                <!-- Coluna 2: MODELO e COR -->
                <div class="space-y-4">
                  <!-- Campo MODELO -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">MODELO</label>
                    <input 
                      v-model="vehicleForm.model" 
                      type="text" 
                      placeholder="MODELO" 
                      class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 text-gray-600"
                      readonly
                    >
                  </div>
                  
                  <!-- Campo COR -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">COR</label>
                    <input 
                      v-model="vehicleForm.color" 
                      type="text" 
                      placeholder="COR" 
                      class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 text-gray-600"
                      readonly
                    >
                  </div>
                </div>
                
              </div>
              
              <!-- Botão para cadastrar novo veículo -->
              <div class="text-center mt-6">
                <button
                  type="button"
                  @click="openVehicleModal"
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                >
                  ➕ Cadastrar novo veículo/equipamento
                </button>
              </div>
              
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Responsável Técnico *</label>
              <select 
                v-model="form.technical_responsible_id" 
                required
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
              >
                <option value="">Selecione um técnico</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Data de Abertura *</label>
              <input 
                v-model="form.opening_date" 
                type="date" 
                required
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Previsão de Entrega</label>
              <input 
                v-model="form.expected_delivery_date" 
                :min="form.opening_date"
                type="date"
                @change="validateDeliveryDate"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
              >
              <p class="text-xs text-gray-500 mt-1">Deve ser maior ou igual à data de abertura</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pagamento *</label>
              <select 
                v-model="form.payment_method" 
                required
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
              >
                <option value="money">Dinheiro</option>
                <option value="card">Cartão</option>
                <option value="pix">PIX</option>
                <option value="credit">A Prazo (Crédito)</option>
                <option value="multiple">Múltiplas Formas</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Descrições -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Descrições</h2>
          
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Descrição do Problema *</label>
              <textarea 
                v-model="form.problem_description" 
                required
                rows="3"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                placeholder="Descreva o problema relatado pelo cliente..."
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Diagnóstico</label>
              <textarea 
                v-model="form.diagnosis" 
                rows="3"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                placeholder="Diagnóstico técnico..."
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Observações Internas</label>
              <textarea 
                v-model="form.internal_observations" 
                rows="2"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                placeholder="Observações internas..."
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Observações do Cliente</label>
              <textarea 
                v-model="form.customer_observations" 
                rows="2"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                placeholder="Observações do cliente..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Itens da OS -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-medium text-gray-900">Itens da OS</h2>
            <button
              type="button"
              @click="addItem"
              class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors"
            >
              Adicionar Item
            </button>
          </div>

          <div v-if="form.items.length === 0" class="text-center py-8 text-gray-500">
            Nenhum item adicionado
          </div>

          <div v-else class="space-y-4">
            <div 
              v-for="(item, index) in form.items" 
              :key="index"
              class="border border-gray-200 rounded-lg p-4"
            >
              <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                  <select 
                    v-model="item.item_type"
                    @change="onItemTypeChange(index)"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="product">Produto</option>
                    <option value="service">Serviço</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ item.item_type === 'product' ? 'Produto' : 'Serviço' }}
                  </label>
                  
                  <div class="relative">
                    <div class="relative">
                      <input 
                        v-model="item.searchTerm"
                        @input="onItemSearch(index)"
                        @focus="showItemDropdown(index)"
                        @blur="hideItemDropdown(index)"
                        @click="showItemDropdown(index)"
                        type="text" 
                        :placeholder="`Digite o nome do ${item.item_type === 'product' ? 'produto' : 'serviço'}...`"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-primary-500 cursor-pointer"
                      >
                      <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                      </div>
                    </div>
                    
                    <!-- Dropdown de produtos/serviços -->
                    <div 
                      v-if="item.showDropdown" 
                      class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
                      @mousedown.prevent
                    >
                      <div v-if="getFilteredItemOptions(item).length === 0" class="px-3 py-2 text-gray-500 text-center">
                        Nenhum {{ item.item_type === 'product' ? 'produto' : 'serviço' }} encontrado
                      </div>
                      
                      <div 
                        v-for="option in getFilteredItemOptions(item)" 
                        :key="option.id"
                        @click="selectItemOption(index, option)"
                        @mousedown="selectItemOption(index, option)"
                        :class="[
                          'px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors',
                          { 'text-red-500 bg-red-50': item.item_type === 'product' && option.stock <= 0 }
                        ]"
                      >
                        <div class="font-medium">{{ option.name }}</div>
                        <div class="text-sm text-gray-600">
                          {{ formatCurrency(option.price) }}
                          <span v-if="item.item_type === 'product'" class="ml-2">
                            - Estoque: {{ option.stock }} {{ option.stock <= 0 ? '(ESGOTADO)' : '' }}
                          </span>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Botão para criar novo -->
                    <button
                      v-if="item.item_type === 'service' && canCreateServices"
                      type="button"
                      @click="showServiceModal = true"
                      class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                      </svg>
                    </button>
                  </div>
                  
                  <!-- Item selecionado -->
                  <div v-if="getSelectedItemOption(item)" class="mt-2 p-2 bg-blue-50 rounded border">
                    <div class="font-medium text-blue-700">{{ getSelectedItemOption(item).name }}</div>
                    <div class="text-sm text-blue-600">
                      {{ formatCurrency(getSelectedItemOption(item).price) }}
                      <span v-if="item.item_type === 'product'" class="ml-2">
                        - Estoque: {{ getSelectedItemOption(item).stock }}
                      </span>
                    </div>
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade</label>
                  <input 
                    v-model.number="item.quantity"
                    @input="calculateItemTotal(index)"
                    type="number" 
                    min="1"
                    :data-item-index="index"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Preço Unitário</label>
                  <input 
                    v-model.number="item.unit_price"
                    @input="calculateItemTotal(index)"
                    type="number" 
                    step="0.01"
                    min="0"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Total</label>
                  <input 
                    v-model.number="item.total_price"
                    type="number" 
                    step="0.01"
                    readonly
                    class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50"
                  >
                </div>

                <div class="flex items-end">
                  <button
                    type="button"
                    @click="removeItem(index)"
                    class="w-full bg-red-600 text-white px-3 py-2 rounded-md hover:bg-red-700 transition-colors"
                  >
                    Remover
                  </button>
                </div>
              </div>

              <div class="mt-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                <input 
                  v-model="item.observations"
                  type="text"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  placeholder="Observações do item..."
                >
              </div>
            </div>
          </div>
        </div>

        <!-- Resumo Financeiro -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Resumo Financeiro</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Total Produtos</label>
              <input 
                v-model.number="form.total_products"
                type="number" 
                step="0.01"
                readonly
                class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Total Serviços</label>
              <input 
                v-model.number="form.total_services"
                type="number" 
                step="0.01"
                readonly
                class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Desconto</label>
              <input 
                v-model.number="form.discount_amount"
                @input="calculateTotals"
                type="number" 
                step="0.01"
                min="0"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Valor Final</label>
              <input 
                v-model.number="form.final_amount"
                type="number" 
                step="0.01"
                readonly
                class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 font-semibold"
              >
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-4">
          <button
            type="button"
            @click="router.back()"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 disabled:opacity-50 transition-colors"
          >
            {{ loading ? 'Salvando...' : (isEdit ? 'Atualizar' : 'Criar OS') }}
          </button>
        </div>
      </form>
    </main>

    <!-- Modal de Cadastro de Veículo -->
    <div v-if="showVehicleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-medium text-gray-900">Cadastrar Novo Veículo/Equipamento</h3>
            <button @click="closeVehicleModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <!-- Formulário de Cadastro -->
          <div>
            <form @submit.prevent="createQuickVehicle" class="space-y-4">
              
              <!-- Cliente (Opcional - apenas para faturamento) -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cliente (Opcional - apenas para faturamento)</label>
                <select
                  v-model="quickVehicleForm.customer_id"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                  <option value="">Selecione um cliente (opcional)</option>
                  <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                    {{ customer.name }}
                  </option>
                </select>
              </div>

              <!-- Informações do Cliente no Momento -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Cliente no Momento</label>
                  <input
                    v-model="quickVehicleForm.customer_name_at_time"
                    type="text"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Nome do cliente no momento"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Telefone do Cliente no Momento</label>
                  <input
                    v-model="quickVehicleForm.customer_phone_at_time"
                    type="text"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Telefone do cliente no momento"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Email do Cliente no Momento</label>
                  <input
                    v-model="quickVehicleForm.customer_email_at_time"
                    type="email"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Email do cliente no momento"
                  />
                </div>
              </div>

              <!-- Tipo -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                <select
                  v-model="quickVehicleForm.type"
                  required
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                  <option value="vehicle">Veículo</option>
                  <option value="equipment">Equipamento</option>
                </select>
              </div>

              <!-- Placa -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Placa *</label>
                <input
                  v-model="quickVehicleForm.plate"
                  @input="formatPlateInput"
                  type="text"
                  required
                  maxlength="8"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  placeholder="ABC-1234"
                />
                <p class="text-xs text-gray-500 mt-1">Formato: XXX-XXXX</p>
              </div>

              <!-- Marca e Modelo -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                  <input
                    v-model="quickVehicleForm.make"
                    type="text"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Ex: Toyota, Honda, Ford"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Modelo *</label>
                  <input
                    v-model="quickVehicleForm.model"
                    type="text"
                    required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Ex: Corolla, Civic, Focus"
                  />
                </div>
              </div>

              <!-- Ano e Cor -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Ano</label>
                  <input
                    v-model="quickVehicleForm.year"
                    type="text"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="2020"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Cor</label>
                  <input
                    v-model="quickVehicleForm.color"
                    type="text"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Ex: Branco, Preto, Prata"
                  />
                </div>
              </div>

              <!-- Número do Chassi e Motor -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Número do Chassi</label>
                  <input
                    v-model="quickVehicleForm.chassis_number"
                    type="text"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Número do chassi"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Número do Motor</label>
                  <input
                    v-model="quickVehicleForm.engine_number"
                    type="text"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Número do motor"
                  />
                </div>
              </div>

              <!-- Observações -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                <textarea
                  v-model="quickVehicleForm.notes"
                  rows="3"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  placeholder="Observações adicionais sobre o veículo/equipamento"
                ></textarea>
              </div>

              <!-- Status -->
              <div>
                <label class="flex items-center">
                  <input
                    v-model="quickVehicleForm.active"
                    type="checkbox"
                    class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                  />
                  <span class="ml-2 text-sm text-gray-700">Veículo ativo</span>
                </label>
              </div>
              
              <div class="flex justify-end space-x-2 pt-4 border-t border-gray-200">
                <button type="button" @click="closeVehicleModal" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                  Cancelar
                </button>
                <button type="submit" :disabled="vehicleLoading" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
                  {{ vehicleLoading ? 'Salvando...' : 'Salvar' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Cadastro Rápido de Serviço -->
    <div v-if="showServiceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Cadastrar Serviço</h3>
            <button @click="showServiceModal = false" class="text-gray-400 hover:text-gray-600">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <form @submit.prevent="createQuickService" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
              <input v-model="quickServiceForm.name" type="text" required class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Nome do serviço">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Preço *</label>
              <input v-model.number="quickServiceForm.price" type="number" step="0.01" min="0" required class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="0.00">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
              <input v-model="quickServiceForm.category" type="text" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Ex: Manutenção, Instalação">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
              <textarea v-model="quickServiceForm.description" rows="2" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Descrição do serviço"></textarea>
            </div>
            
            <div class="flex justify-end space-x-2">
              <button type="button" @click="showServiceModal = false" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Cancelar
              </button>
              <button type="submit" :disabled="serviceLoading" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50">
                {{ serviceLoading ? 'Salvando...' : 'Salvar' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Veículo Não Encontrado -->
  <div v-if="showVehicleNotFoundModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <div class="flex items-center justify-center mb-4">
          <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
          </div>
        </div>
        
        <div class="text-center">
          <h3 class="text-lg font-medium text-gray-900 mb-2">Veículo Não Encontrado</h3>
          <div class="mt-2 px-7 py-3">
            <p class="text-sm text-gray-500 mb-4">
              A placa <strong>{{ vehicleNotFoundPlate }}</strong> não foi encontrada na base de dados.
            </p>
            
            <div class="flex flex-col space-y-3">
              <button
                @click="retryVehicleSearch"
                class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                🔍 Digitar Placa Novamente
              </button>
              
              <button
                @click="createNewVehicleFromNotFound"
                class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
              >
                ➕ Cadastrar Novo Veículo
              </button>
              
              <button
                @click="closeVehicleNotFoundModal"
                class="w-full bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500"
              >
                Cancelar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useServiceOrdersStore } from '@/stores/serviceOrders'
import { useCustomersStore } from '@/stores/customers'
import { useVehiclesStore } from '@/stores/vehicles'
import { useServicesStore } from '@/stores/services'
import { useProductsStore } from '@/stores/products'
import { useUsersStore } from '@/stores/users'

const router = useRouter()
const route = useRoute()
const serviceOrdersStore = useServiceOrdersStore()
const customersStore = useCustomersStore()
const vehiclesStore = useVehiclesStore()
const servicesStore = useServicesStore()
const productsStore = useProductsStore()
const usersStore = useUsersStore()

const isEdit = computed(() => !!route.params.id)
const loading = ref(false)

// Modal states
const showVehicleModal = ref(false)
const showServiceModal = ref(false)
const vehicleLoading = ref(false)
const serviceLoading = ref(false)

// Modal para veículo não encontrado
const showVehicleNotFoundModal = ref(false)
const vehicleNotFoundPlate = ref('')

// Vehicle search states

// Quick forms
const quickVehicleForm = ref({
  customer_id: '', // Opcional - apenas para faturamento
  customer_name_at_time: '', // Nome do cliente no momento
  customer_phone_at_time: '', // Telefone do cliente no momento
  customer_email_at_time: '', // Email do cliente no momento
  type: 'vehicle',
  plate: '', // Obrigatório
  make: '',
  model: '', // Obrigatório
  year: '',
  color: '',
  chassis_number: '',
  engine_number: '',
  notes: '',
  active: true
})

const vehicleSearchForm = ref({
  plate: ''
})

const quickServiceForm = ref({
  name: '',
  price: 0,
  category: '',
  description: '',
  active: true
})

// Helper function to get current date in local timezone
const getCurrentDate = () => {
  const now = new Date()
  const year = now.getFullYear()
  const month = String(now.getMonth() + 1).padStart(2, '0')
  const day = String(now.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

// Form data
const form = ref({
  customer_id: '',
  vehicle_id: '',
  technical_responsible_id: '',
  opening_date: getCurrentDate(),
  expected_delivery_date: '',
  problem_description: '',
  diagnosis: '',
  internal_observations: '',
  customer_observations: '',
  payment_method: 'money',
  discount_amount: 0,
  total_products: 0,
  total_services: 0,
  total_amount: 0,
  final_amount: 0,
  items: [] as any[]
})

// Computed
const customers = computed(() => customersStore.customers)
const users = computed(() => usersStore.users)
const customerVehicles = ref([])
const selectedCustomer = ref(null)
const vehicleSearch = ref('')
const showVehicleDropdown = ref(false)
const selectedVehicle = ref(null)
const allVehicles = ref([])

// Formulário de veículo
const vehicleForm = ref({
  plate: '',
  make: '',
  model: '',
  color: ''
})
const products = computed(() => productsStore.products)
const services = computed(() => servicesStore.activeServices)

// Permission check for creating services
const canCreateServices = computed(() => {
  // Verificar se o usuário tem permissão para criar serviços
  // Por enquanto, sempre permitir - pode ser implementado com sistema de permissões
  return true
})

// Methods
const loadData = async () => {
  await Promise.all([
    customersStore.fetchCustomers(),
    usersStore.fetchUsers(),
    servicesStore.fetchActiveServices(),
    productsStore.fetchProducts(),
    loadAllVehicles()
  ])

  if (isEdit.value) {
    await loadServiceOrder()
  }
}

const loadServiceOrder = async () => {
  const result = await serviceOrdersStore.fetchServiceOrder(Number(route.params.id))
  if (result.success) {
    const serviceOrder = result.serviceOrder
    form.value = {
      customer_id: serviceOrder.customer_id,
      vehicle_id: serviceOrder.vehicle_id || '',
      technical_responsible_id: serviceOrder.technical_responsible_id,
      opening_date: serviceOrder.opening_date,
      expected_delivery_date: serviceOrder.expected_delivery_date || '',
      problem_description: serviceOrder.problem_description,
      diagnosis: serviceOrder.diagnosis || '',
      internal_observations: serviceOrder.internal_observations || '',
      customer_observations: serviceOrder.customer_observations || '',
      payment_method: serviceOrder.payment_method || 'money',
      discount_amount: serviceOrder.discount_amount || 0,
      total_products: serviceOrder.total_products || 0,
      total_services: serviceOrder.total_services || 0,
      total_amount: serviceOrder.total_amount || 0,
      final_amount: serviceOrder.final_amount || 0,
      items: serviceOrder.items || []
    }
    await loadAllVehicles()
    // Recalcular totais após carregar dados
    calculateTotals()
  }
}

const loadAllVehicles = async () => {
  console.log('=== INICIANDO LOAD ALL VEHICLES ===')
  console.log('Carregando todos os veículos...')
  console.log('Token no localStorage:', localStorage.getItem('token') ? 'Presente' : 'Ausente')
  try {
    const result = await vehiclesStore.fetchVehicles()
    console.log('Resultado da busca de veículos:', result)
    console.log('Estrutura do resultado:', JSON.stringify(result, null, 2))
    
    if (result.success) {
      // Tentar diferentes estruturas de dados
      allVehicles.value = vehiclesStore.vehicles || []
      let vehicles = []
      if (result.data && result.data.data) {
        vehicles = result.data.data
      } else if (result.data && Array.isArray(result.data)) {
        vehicles = result.data
      } else if (Array.isArray(result)) {
        vehicles = result
      }
      
      customerVehicles.value = vehicles
    } else {
      console.error('Erro ao carregar veículos:', result.error)
      customerVehicles.value = []
    }
  } catch (error) {
    console.error('Erro na requisição:', error)
    customerVehicles.value = []
  }
}

const searchVehicles = (event: Event) => {
  const target = event.target as HTMLInputElement
  let value = target.value.toUpperCase()
  
  // Remove caracteres não alfanuméricos
  value = value.replace(/[^A-Z0-9]/g, '')
  
  // Aplica formatação XXX-XXXX
  if (value.length > 3) {
    value = value.substring(0, 3) + '-' + value.substring(3, 7)
  }
  
  // Atualiza o valor no input e no v-model
  target.value = value
  plateSearch.value = value
  
  // Se o valor corresponde a uma placa, seleciona o veículo automaticamente
  if (value.length >= 3) {
    const matchingVehicle = customerVehicles.value.find(vehicle => 
      vehicle.plate && vehicle.plate.toLowerCase().includes(value.toLowerCase())
    )
    
    if (matchingVehicle) {
      form.value.vehicle_id = matchingVehicle.id
    }
  } else if (value.length === 0) {
    // Se limpar a busca, limpa a seleção
    form.value.vehicle_id = ''
  }
}

const formatPlate = (event: Event) => {
  const target = event.target as HTMLInputElement
  let value = target.value.replace(/\D/g, '') // Remove tudo que não é dígito
  
  if (value.length > 3) {
    value = value.substring(0, 3) + '-' + value.substring(3, 7)
  }
  
}


const addItem = () => {
  form.value.items.push({
    item_type: 'product',
    product_id: '',
    service_id: '',
    quantity: 1,
    unit_price: 0,
    total_price: 0,
    observations: '',
    searchTerm: '',
    showDropdown: false
  })
}

const removeItem = (index: number) => {
  form.value.items.splice(index, 1)
  calculateTotals()
}

const onItemTypeChange = (index: number) => {
  const item = form.value.items[index]
  item.product_id = ''
  item.service_id = ''
  item.unit_price = 0
  item.total_price = 0
}

// Funções para busca de veículos
const onVehicleSearch = () => {
  const searchTerm = vehicleSearch.value.toLowerCase()
  if (searchTerm.length === 0) {
    filteredVehicles.value = []
    return
  }
  
  const filtered = allVehicles.value.filter(vehicle => {
    const plate = vehicle.plate?.toLowerCase() || ''
    const model = vehicle.model?.toLowerCase() || ''
    const brand = vehicle.brand?.toLowerCase() || ''
    const identification = getVehicleIdentification(vehicle).toLowerCase()
    
    return plate.includes(searchTerm) || 
           model.includes(searchTerm) || 
           brand.includes(searchTerm) ||
           identification.includes(searchTerm)
  })
  
  filteredVehicles.value = [...filtered]
}

const selectVehicle = (vehicle) => {
  selectedVehicle.value = vehicle
  form.value.vehicle_id = vehicle.id
  vehicleSearch.value = getVehicleIdentification(vehicle)
  showVehicleDropdown.value = false
}

const hideVehicleDropdown = () => {
  setTimeout(() => {
    showVehicleDropdown.value = false
  }, 200)
}

const onVehicleChange = () => {
  if (form.value.vehicle_id === '__create_new__') {
    showVehicleModal.value = true
    form.value.vehicle_id = ''
  }
}

// Funções para busca de produtos/serviços
const onItemSearch = (index) => {
  const item = form.value.items[index]
  // Mostrar dropdown quando há texto
  if (item.searchTerm && item.searchTerm.length > 0) {
    item.showDropdown = true
  } else {
    // Mostrar dropdown mesmo sem texto para mostrar opções
    item.showDropdown = true
  }
}

const getFilteredItemOptions = (item) => {
  const searchTerm = item.searchTerm?.toLowerCase() || ''
  const options = getItemOptions(item.item_type)
  
  if (searchTerm.length === 0) {
    return options.slice(0, 15) // Mostrar mais itens quando não há busca
  }
  
  return options.filter(option => 
    option.name.toLowerCase().includes(searchTerm)
  )
}

const selectItemOption = (index, option) => {
  const item = form.value.items[index]
  item.searchTerm = option.name
  
  if (item.item_type === 'product') {
    item.product_id = option.id
    item.service_id = ''
  } else {
    item.service_id = option.id
    item.product_id = ''
  }
  
  item.unit_price = option.price
  item.total_price = item.quantity * option.price
  item.showDropdown = false
  
  calculateTotals()
  
  // Focar no próximo campo
  setTimeout(() => {
    const nextInput = document.querySelector(`input[data-item-index="${index}"]`)
    if (nextInput) {
      nextInput.focus()
    }
  }, 100)
}

const getSelectedItemOption = (item) => {
  const selectedId = item.item_type === 'product' ? item.product_id : item.service_id
  if (!selectedId) return null
  
  return getItemOptions(item.item_type).find(option => option.id == selectedId)
}

const hideItemDropdown = (index) => {
  // Usar setTimeout para permitir que o click no dropdown funcione
  setTimeout(() => {
    if (form.value.items[index]) {
      form.value.items[index].showDropdown = false
    }
  }, 200)
}

const showItemDropdown = (index) => {
  if (form.value.items[index]) {
    form.value.items[index].showDropdown = true
  }
}

const onItemChange = (index: number) => {
  const item = form.value.items[index]
  const selectedValue = item.item_type === 'product' ? item.product_id : item.service_id
  
  // Verificar se é a opção de cadastrar novo serviço
  if (selectedValue === '__create_new__') {
    showServiceModal.value = true
    // Limpar a seleção
    if (item.item_type === 'product') {
      item.product_id = ''
    } else {
      item.service_id = ''
    }
    return
  }
  
  const option = getItemOptions(item.item_type).find(opt => 
    opt.id === selectedValue
  )
  
  if (option) {
    // Verificar estoque para produtos
    if (item.item_type === 'product' && option.stock <= 0) {
      alert(`❌ Produto "${option.name}" está esgotado! Estoque disponível: ${option.stock}`)
      item.product_id = ''
      return
    }
    
    item.unit_price = option.price
    item.description = option.name
    calculateItemTotal(index)
  }
}

const getItemOptions = (type: string) => {
  return type === 'product' ? products.value : services.value
}

const calculateItemTotal = (index: number) => {
  const item = form.value.items[index]
  const quantity = item.quantity || 0
  const unitPrice = item.unit_price || 0
  item.total_price = quantity * unitPrice
  calculateTotals()
}

const validateDeliveryDate = () => {
  if (form.value.expected_delivery_date && form.value.opening_date) {
    const openingDate = new Date(form.value.opening_date)
    const expectedDate = new Date(form.value.expected_delivery_date)
    
    if (expectedDate < openingDate) {
      alert('❌ A data de previsão de entrega deve ser maior ou igual à data de abertura!')
      form.value.expected_delivery_date = form.value.opening_date
    }
  }
}

// Funções do formulário de veículo
const onPlateInput = (event) => {
  const input = event.target
  let value = input.value.replace(/[^A-Za-z0-9]/g, '').toUpperCase()
  
  // Formatar como XXX-XXXX
  if (value.length > 3) {
    value = value.substring(0, 3) + '-' + value.substring(3, 7)
  }
  
  // Atualiza o valor no formulário
  vehicleForm.value.plate = value
  
  // Limpar campos se placa mudou
  if (selectedVehicle.value && selectedVehicle.value.plate !== value) {
    vehicleForm.value.make = ''
    vehicleForm.value.model = ''
    selectedVehicle.value = null
    form.value.vehicle_id = ''
  }
}

const searchVehicleByPlate = async () => {
  if (!vehicleForm.value.plate) return
  
  try {
    // Buscar na lista local de veículos
    const foundVehicle = allVehicles.value.find(vehicle => 
      vehicle.plate?.toLowerCase() === vehicleForm.value.plate.toLowerCase()
    )
    
    if (foundVehicle) {
      // Preencher campos automaticamente
      vehicleForm.value.make = foundVehicle.make || ''
      vehicleForm.value.model = foundVehicle.model || ''
      vehicleForm.value.color = foundVehicle.color || ''
      selectedVehicle.value = foundVehicle
      form.value.vehicle_id = foundVehicle.id
      
      // Silencioso - sem alert
    } else {
      // Limpar campos se não encontrou
      vehicleForm.value.make = ''
      vehicleForm.value.model = ''
      vehicleForm.value.color = ''
      selectedVehicle.value = null
      form.value.vehicle_id = ''
      
      // Mostrar modal de veículo não encontrado
      vehicleNotFoundPlate.value = vehicleForm.value.plate
      showVehicleNotFoundModal.value = true
    }
  } catch (error) {
    console.error('Erro ao buscar veículo:', error)
    vehicleNotFoundPlate.value = vehicleForm.value.plate
    showVehicleNotFoundModal.value = true
  }
}

// Funções para o modal de veículo não encontrado
const closeVehicleNotFoundModal = () => {
  showVehicleNotFoundModal.value = false
  vehicleNotFoundPlate.value = ''
}

const retryVehicleSearch = () => {
  closeVehicleNotFoundModal()
  // Focar no campo de placa
  setTimeout(() => {
    const plateInput = document.querySelector('input[placeholder="INSERIR A PLACA"]')
    if (plateInput) {
      plateInput.focus()
      plateInput.select()
    }
  }, 100)
}

const createNewVehicleFromNotFound = () => {
  closeVehicleNotFoundModal()
  showVehicleModal.value = true
  // Preencher a placa no modal de cadastro
  quickVehicleForm.value.plate = vehicleNotFoundPlate.value
}


const onCustomerChange = () => {
  if (form.value.customer_id) {
    selectedCustomer.value = customers.value.find(c => c.id == form.value.customer_id)
  } else {
    selectedCustomer.value = null
  }
}

const getAvailableCredit = (customer) => {
  if (!customer) return 0
  return (customer.credit_limit || 0) - (customer.credit_used || 0)
}

const getAvailableCreditClass = (customer) => {
  const available = getAvailableCredit(customer)
  const total = form.value.final_amount || 0
  
  if (available >= total) {
    return 'text-green-600'
  } else if (available > 0) {
    return 'text-yellow-600'
  } else {
    return 'text-red-600'
  }
}

const hasEnoughCredit = computed(() => {
  if (!selectedCustomer.value || form.value.payment_method !== 'credit') return true
  const available = getAvailableCredit(selectedCustomer.value)
  const total = form.value.final_amount || 0
  return available >= total
})

const formatCurrency = (value) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

const calculateTotals = () => {
  const totalProducts = form.value.items
    .filter(item => item.item_type === 'product')
    .reduce((sum, item) => sum + (item.total_price || 0), 0)
  
  const totalServices = form.value.items
    .filter(item => item.item_type === 'service')
    .reduce((sum, item) => sum + (item.total_price || 0), 0)
  
  form.value.total_products = totalProducts
  form.value.total_services = totalServices
  form.value.total_amount = totalProducts + totalServices
  form.value.final_amount = Math.max(0, form.value.total_amount - (form.value.discount_amount || 0))
}

const submitForm = async () => {
  loading.value = true
  
  try {
    // Limpar dados antes de enviar - remover campos null desnecessários
    const cleanedForm = { ...form.value }
    
    // Converter customer_id vazio para null
    if (cleanedForm.customer_id === '') {
      cleanedForm.customer_id = null
    }
    
    // Limpar items - remover campos null
    cleanedForm.items = form.value.items.map(item => {
      const cleanedItem = { ...item }
      
      // Se é produto, remover service_id
      if (item.item_type === 'product') {
        delete cleanedItem.service_id
      }
      
      // Se é serviço, remover product_id
      if (item.item_type === 'service') {
        delete cleanedItem.product_id
      }
      
      return cleanedItem
    })
    
        // Validação de datas
        if (cleanedForm.expected_delivery_date && cleanedForm.opening_date) {
          const openingDate = new Date(cleanedForm.opening_date)
          const expectedDate = new Date(cleanedForm.expected_delivery_date)
          
          if (expectedDate < openingDate) {
            alert('❌ A data de previsão de entrega deve ser maior ou igual à data de abertura!')
            return
          }
        }

        // Validação de crédito para OS a prazo
        if (cleanedForm.payment_method === 'credit' && cleanedForm.customer_id && selectedCustomer.value) {
          const available = getAvailableCredit(selectedCustomer.value)
          const total = cleanedForm.final_amount
          
          if (available < total) {
            alert(`❌ Cliente não possui crédito suficiente!\n\nCrédito disponível: ${formatCurrency(available)}\nValor necessário: ${formatCurrency(total)}`)
            return
          }
        }
    
    console.log('=== ENVIANDO DADOS PARA CRIAR OS ===')
    console.log('Form data:', cleanedForm)
    console.log('Items:', cleanedForm.items)
    
    const result = isEdit.value 
      ? await serviceOrdersStore.updateServiceOrder(Number(route.params.id), cleanedForm)
      : await serviceOrdersStore.createServiceOrder(cleanedForm)
    
    console.log('Resultado da criação:', result)
    
    if (result.success) {
      router.push('/service-orders')
    } else {
      alert(result.error)
    }
  } finally {
    loading.value = false
  }
}

const getVehicleIdentification = (vehicle: any) => {
  return vehiclesStore.getShortIdentification(vehicle)
}


// Funções do modal de veículos
const openVehicleModal = () => {
  showVehicleModal.value = true
}

const closeVehicleModal = () => {
  showVehicleModal.value = false
  // Limpar formulário
  quickVehicleForm.value = {
    customer_id: '',
    customer_name_at_time: '',
    customer_phone_at_time: '',
    customer_email_at_time: '',
    type: 'vehicle',
    plate: '',
    make: '',
    model: '',
    year: '',
    color: '',
    chassis_number: '',
    engine_number: '',
    notes: '',
    active: true
  }
}




// Quick creation methods
const createQuickVehicle = async () => {
  vehicleLoading.value = true
  
  try {
    // Limpar dados vazios
    const cleanData = { ...quickVehicleForm.value }
    Object.keys(cleanData).forEach(key => {
      if (cleanData[key] === '' || cleanData[key] === null) {
        cleanData[key] = null
      }
    })
    
    const result = await vehiclesStore.createVehicle(cleanData)
    
    if (result.success) {
      // Atualizar lista de veículos do cliente
      await loadAllVehicles()
      // Preencher campos do formulário principal
      vehicleForm.value.plate = result.vehicle.plate
      vehicleForm.value.make = result.vehicle.make
      vehicleForm.value.model = result.vehicle.model
      vehicleForm.value.color = result.vehicle.color || ''
      selectedVehicle.value = result.vehicle
      form.value.vehicle_id = result.vehicle.id
      
      // Fechar modal e limpar formulário
      closeVehicleModal()
      quickVehicleForm.value = {
        customer_id: '',
        customer_name_at_time: '',
        customer_phone_at_time: '',
        customer_email_at_time: '',
        type: 'vehicle',
        plate: '',
        make: '',
        model: '',
        year: '',
        color: '',
        chassis_number: '',
        engine_number: '',
        notes: '',
        active: true
      }
      
      // Silencioso - sem alert
    } else {
      alert(result.error)
    }
  } finally {
    vehicleLoading.value = false
  }
}

const createQuickService = async () => {
  serviceLoading.value = true
  
  try {
    const result = await servicesStore.createService(quickServiceForm.value)
    
    if (result.success) {
      // Atualizar lista de serviços
      await servicesStore.fetchServices()
      
      // Encontrar o item atual que está sendo editado e selecionar o novo serviço
      const currentItemIndex = form.value.items.findIndex(item => 
        item.item_type === 'service' && !item.service_id
      )
      
      if (currentItemIndex !== -1) {
        form.value.items[currentItemIndex].service_id = result.service.id
        form.value.items[currentItemIndex].unit_price = result.service.price
        form.value.items[currentItemIndex].description = result.service.name
        calculateItemTotal(currentItemIndex)
      }
      
      // Fechar modal e limpar formulário
      showServiceModal.value = false
      quickServiceForm.value = {
        name: '',
        price: 0,
        category: '',
        description: '',
        active: true
      }
    } else {
      alert(result.error)
    }
  } finally {
    serviceLoading.value = false
  }
}

// Watchers
watch(() => form.value.customer_id, (newCustomerId) => {
  form.value.vehicle_id = ''
})

// Lifecycle
onMounted(() => {
  loadData()
})
</script>
