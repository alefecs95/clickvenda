<template>
  <AppLayout>
    <!-- Header da Página -->
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Produtos</h1>
        <p class="text-gray-600">Gerencie seu catálogo de produtos</p>
      </div>
      
      <div class="flex space-x-3">
        <button
          @click="showXmlImport = !showXmlImport"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
          </svg>
          <span>Compra e Importar XML</span>
        </button>
        
        <button
          @click="createProduct"
          class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors flex items-center space-x-2"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          <span>Novo Produto</span>
        </button>
      </div>
    </div>

    <!-- Seção de Importação XML -->
    <div v-if="showXmlImport" class="bg-white shadow-sm rounded-lg p-6 mb-8">
      <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
        </svg>
        Compra e Importação de Produtos XML
      </h2>

      <div class="space-y-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-blue-800">Como funciona a importação</h3>
              <div class="mt-2 text-sm text-blue-700">
                <ul class="list-disc list-inside space-y-1">
                  <li><strong>Produtos novos:</strong> Serão criados automaticamente no sistema</li>
                  <li><strong>Produtos existentes:</strong> Terão o estoque atualizado (identificados por SKU ou código de barras)</li>
                  <li><strong>Campos suportados:</strong> nome, descrição, preço de venda, preço de compra, margem de lucro, SKU, código de barras, quantidade</li>
                  <li><strong>Código único:</strong> Cada produto é identificado pelo SKU ou código de barras que não muda entre notas</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Arquivo XML da Nota Fiscal</label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
              <div class="space-y-1 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                  <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="flex text-sm text-gray-600">
                  <label for="xml-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                    <span>Selecione um arquivo XML</span>
                    <input 
                      id="xml-upload" 
                      ref="xmlFileInput"
                      type="file" 
                      accept=".xml"
                      @change="handleFileSelect"
                      class="sr-only"
                    />
                  </label>
                  <p class="pl-1">ou arraste e solte aqui</p>
                </div>
                <p class="text-xs text-gray-500">XML até 10MB</p>
              </div>
            </div>
          </div>

          <div v-if="selectedFile" class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <div class="ml-3">
                  <p class="text-sm font-medium text-gray-900">{{ selectedFile.name }}</p>
                  <p class="text-sm text-gray-500">{{ formatFileSize(selectedFile.size) }}</p>
                </div>
              </div>
              <button
                @click="removeFile"
                type="button"
                class="text-gray-400 hover:text-gray-600"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
          </div>

          <div class="flex space-x-4">
            <button
              @click="validateXml"
              :disabled="!selectedFile || validating"
              type="button"
              class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="validating" class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              {{ validating ? 'Validando...' : 'Validar XML' }}
            </button>

            <button
              @click="importXml"
              :disabled="!selectedFile || !validationResult?.valid || importing"
              type="button"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="importing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
              </svg>
              {{ importing ? 'Importando...' : 'Importar Produtos' }}
            </button>
          </div>

          <!-- Opções de Margem de Lucro -->
          <div v-if="validationResult?.valid" class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-sm font-medium text-blue-800 mb-3">Configurações de Margem de Lucro</h3>
            
            <div class="space-y-4">
              <!-- Opção: Seguir Margem Padrão -->
              <div class="flex items-start">
                <div class="flex items-center h-5">
                  <input
                    id="use-default-margin"
                    v-model="useDefaultMargin"
                    type="checkbox"
                    class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 rounded"
                  />
                </div>
                <div class="ml-3 text-sm">
                  <label for="use-default-margin" class="font-medium text-gray-700">
                    Seguir Margem Padrão ({{ settingsStore.salesSettings.default_profit_margin }}%)
                  </label>
                  <p class="text-gray-500">
                    Aplicar automaticamente a margem de lucro padrão configurada no sistema para todos os produtos.
                  </p>
                </div>
              </div>

              <!-- Opções Manuais (quando margem padrão não está marcada) -->
              <div v-if="!useDefaultMargin" class="border-t border-blue-200 pt-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Margem de Lucro Personalizada (%)
                    </label>
                    <input
                      v-model.number="customMargin"
                      type="number"
                      step="0.01"
                      min="0"
                      max="1000"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                      placeholder="Ex: 30"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Fator de Multiplicação
                    </label>
                    <input
                      v-model.number="priceFactor"
                      type="number"
                      step="0.01"
                      min="1"
                      max="10"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                      placeholder="Ex: 1.3"
                    />
                  </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                  Você pode definir uma margem personalizada ou um fator de multiplicação para calcular os preços de venda.
                </p>
              </div>
            </div>
          </div>

          <!-- Preview dos produtos a serem importados -->
          <div v-if="validationResult?.valid && validationResult?.products" class="mt-6">
            <h3 class="text-md font-medium text-gray-900 mb-4">Preview dos Produtos ({{ validationResult.products.length }} encontrados)</h3>
            <div class="bg-gray-50 rounded-lg p-4 max-h-96 overflow-y-auto">
              <div class="space-y-3">
                <div 
                  v-for="(product, index) in validationResult.products" 
                  :key="index"
                  class="bg-white rounded-lg p-3 border border-gray-200"
                >
                  <div class="flex justify-between items-start">
                    <div class="flex-1">
                      <div class="flex items-center space-x-2 mb-2">
                        <h4 class="font-medium text-gray-900">{{ product.name }}</h4>
                        <span 
                          :class="{
                            'bg-green-100 text-green-800': product.action === 'update',
                            'bg-blue-100 text-blue-800': product.action === 'create'
                          }"
                          class="inline-flex px-2 py-1 text-xs font-medium rounded-full"
                        >
                          {{ product.action === 'create' ? 'Novo' : 'Atualizar' }}
                        </span>
                      </div>
                      <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm text-gray-600">
                        <div><strong>SKU:</strong> {{ product.sku || '-' }}</div>
                        <div><strong>Código:</strong> {{ product.barcode || '-' }}</div>
                        <div><strong>Preço:</strong> R$ {{ product.price?.toFixed(2) || '0,00' }}</div>
                        <div><strong>Qtd:</strong> {{ product.quantity || 0 }}</div>
                      </div>
                      <div v-if="product.description" class="text-sm text-gray-500 mt-1">
                        {{ product.description }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Resultado da validação -->
          <div v-if="validationResult" class="mt-4">
            <div v-if="validationResult.valid" class="bg-green-50 border border-green-200 rounded-lg p-4">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-green-800">XML válido!</h3>
                  <div class="mt-2 text-sm text-green-700">
                    <p>{{ validationResult.products_count }} produtos encontrados no arquivo.</p>
                    <p v-if="validationResult.new_products">{{ validationResult.new_products }} produtos novos serão criados.</p>
                    <p v-if="validationResult.existing_products">{{ validationResult.existing_products }} produtos existentes terão estoque atualizado.</p>
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="bg-red-50 border border-red-200 rounded-lg p-4">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-red-800">Erro na validação</h3>
                  <div class="mt-2 text-sm text-red-700">
                    <p>{{ validationResult.message }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Resultado da importação -->
          <div v-if="importResult" class="mt-4">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-blue-800">Importação concluída!</h3>
                  <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                      <li>{{ importResult.created }} produtos criados</li>
                      <li>{{ importResult.updated }} produtos com estoque atualizado</li>
                      <li v-if="importResult.errors > 0">{{ importResult.errors }} produtos com erro</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Search and Filters -->
    <div class="mb-6">
      <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Buscar produtos..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            @input="handleSearch"
          />
        </div>
        <div class="flex gap-2">
          <select
            v-model="statusFilter"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          >
            <option value="">Todos os status</option>
            <option value="active">Ativos</option>
            <option value="inactive">Inativos</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="productsStore.error" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
      <p class="text-red-800">{{ productsStore.error }}</p>
    </div>

    <!-- Products Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="product in filteredProducts"
        :key="product.id"
        class="bg-white rounded-lg shadow hover:shadow-md transition-shadow"
      >
        <div class="p-6">
          <div class="flex justify-between items-start mb-4">
            <div class="flex-1">
              <div class="flex justify-end space-x-2 mb-2">
                <button
                  @click="manageStock(product)"
                  class="text-gray-400 hover:text-blue-600"
                  title="Gerenciar Estoque"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                  </svg>
                </button>
                <button
                  @click="editProduct(product)"
                  class="text-gray-400 hover:text-gray-600"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                </button>
                <button
                  @click="deleteProduct(product.id)"
                  class="text-gray-400 hover:text-red-600"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ product.name }}</h3>
              <p class="text-sm text-gray-600 mb-3">{{ product.description || 'Sem descrição' }}</p>
              
              <div class="space-y-2">
                <div class="flex justify-between">
                  <span class="text-sm text-gray-500">Preço:</span>
                  <span class="text-sm font-medium text-gray-900">{{ formatPrice(product.price) }}</span>
                </div>
                
                <!-- Mostrar preço total com vassilhame se aplicável -->
                <div v-if="product.returnable && product.returnable_price" class="flex justify-between">
                  <span class="text-sm text-gray-500">+ Vassilhame:</span>
                  <span class="text-sm font-medium text-orange-600">
                    {{ formatPrice(product.returnable_price) }} x{{ product.returnable_quantity }}
                  </span>
                </div>
                
                <div class="flex justify-between">
                  <span class="text-sm text-gray-500">Estoque:</span>
                  <div class="text-right">
                    <span 
                      :class="{
                        'text-green-600': (product.actual_stock || product.stock_quantity) > (product.minimum_stock || 0) && (product.is_available_for_sale !== false),
                        'text-yellow-600': (product.actual_stock || product.stock_quantity) > 0 && (product.actual_stock || product.stock_quantity) <= (product.minimum_stock || 0),
                        'text-red-600': (product.actual_stock || product.stock_quantity) === 0 || (product.is_available_for_sale === false)
                      }"
                      class="text-sm font-medium block"
                    >
                      {{ product.actual_stock || product.stock_quantity }} unidades
                    </span>
                    <span v-if="product.minimum_stock && product.minimum_stock > 0" class="text-xs text-gray-500 block">
                      Mín: {{ product.minimum_stock }}
                    </span>
                    <span v-if="!product.manages_stock && product.parent_product" class="text-xs text-gray-400 block">
                      ({{ product.stock_multiplier }}x de {{ product.parent_product.name }})
                    </span>
                    <span v-if="product.is_available_for_sale === false" class="text-xs text-red-500 block font-medium">
                      Indisponível
                    </span>
                  </div>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-500">SKU:</span>
                  <span class="text-sm text-gray-900">{{ product.sku || '-' }}</span>
                </div>
              </div>
              
              <div class="mt-4 flex flex-wrap gap-2">
                <span
                  :class="{
                    'bg-green-100 text-green-800': product.active,
                    'bg-red-100 text-red-800': !product.active
                  }"
                  class="inline-flex px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ product.active ? 'Ativo' : 'Inativo' }}
                </span>
                
                <span
                  :class="{
                    'bg-blue-100 text-blue-800': product.available,
                    'bg-gray-100 text-gray-800': !product.available
                  }"
                  class="inline-flex px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ product.available ? 'Disponível' : 'Indisponível' }}
                </span>
                
                <span
                  v-if="product.returnable"
                  class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-800"
                >
                  🍺 Retornável
                </span>
                
                <span
                  v-if="product.manages_stock"
                  class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800"
                >
                  📦 Pai
                </span>
                
                <span
                  v-if="!product.manages_stock && product.parent_product_id"
                  class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800"
                >
                  📎 Filho ({{ product.stock_multiplier }}x)
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!loading && filteredProducts.length === 0" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum produto encontrado</h3>
      <p class="mt-1 text-sm text-gray-500">Comece criando um novo produto.</p>
    </div>

    <!-- Create/Edit Product Modal -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-10 mx-auto p-6 border max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            {{ showEditModal ? 'Editar Produto' : 'Novo Produto' }}
          </h3>
          <form @submit.prevent="handleSubmit" @keydown="handleKeyDown">
            <div class="grid grid-cols-2 gap-6">
              <!-- Coluna Esquerda -->
              <div class="space-y-4">
                <!-- Código de Barras - PRIMEIRO CAMPO -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">Código de Barras</label>
                  <input
                    ref="barcodeInput"
                    v-model="form.barcode"
                    type="text"
                    @keydown.enter.prevent="focusNextField('name')"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                    placeholder="Digite ou escaneie o código de barras"
                  />
                </div>
                
                <!-- Nome -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">Nome *</label>
                  <input
                    ref="nameInput"
                    v-model="form.name"
                    type="text"
                    required
                    @keydown.enter.prevent="focusNextField('price')"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                  />
                </div>
                
                <!-- Preço -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">Preço de Venda *</label>
                  <input
                    ref="priceInput"
                    v-model="form.price"
                    type="number"
                    step="0.01"
                    min="0"
                    required
                    @input="onPriceChange"
                    @keydown.enter.prevent="focusNextField('purchase_price')"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                  />
                </div>
                
                <!-- Valor de Compra -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">Valor de Compra</label>
                  <input
                    ref="purchasePriceInput"
                    v-model="form.purchase_price"
                    type="number"
                    step="0.01"
                    min="0"
                    @input="calculateProfitMargin"
                    @keydown.enter.prevent="focusNextField('profit_margin')"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                  />
                </div>
                
                <!-- Configuração de Margem de Lucro -->
                <div class="space-y-3">
                  <label class="block text-sm font-medium text-gray-700">Margem de Lucro</label>
                  
                  <!-- Checkbox para seguir margem padrão -->
                  <div class="flex items-center space-x-3">
                    <input
                      v-model="form.use_default_margin"
                      type="checkbox"
                      id="use_default_margin"
                      @change="onDefaultMarginToggle"
                      class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                    />
                    <label for="use_default_margin" class="text-sm text-gray-700">
                      Seguir Margem Padrão ({{ settingsStore.salesSettings.default_profit_margin }}%)
                    </label>
                  </div>
                  
                  <!-- Campo de margem personalizada (visível apenas quando não usar padrão) -->
                  <div v-if="!form.use_default_margin">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Margem de Lucro Personalizada (%)</label>
                    <input
                      ref="profitMarginInput"
                      v-model="form.profit_margin"
                      type="number"
                      step="0.01"
                      min="0"
                      @input="onMarginChange"
                      @keydown.enter.prevent="focusNextField('sku')"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                      placeholder="Digite a margem personalizada"
                    />
                  </div>
                  
                  <!-- Exibição da margem atual (quando usando padrão) -->
                  <div v-else class="text-sm text-gray-600">
                    Margem aplicada: {{ settingsStore.salesSettings.default_profit_margin }}%
                  </div>
                </div>
                
                <!-- SKU -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">SKU</label>
                  <input
                    ref="skuInput"
                    v-model="form.sku"
                    type="text"
                    @keydown.enter.prevent="focusNextField('manages_stock')"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                  />
                </div>
                
                <!-- Este produto gerencia o próprio estoque -->
                <div class="flex items-center space-x-3">
                  <input
                    ref="managesStockInput"
                    v-model="form.manages_stock"
                    type="checkbox"
                    id="manages_stock"
                    @keydown.enter.prevent="toggleManagesStock"
                    class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                  />
                  <label for="manages_stock" class="text-sm font-medium text-gray-700">
                    Este produto gerencia o próprio estoque
                  </label>
                </div>
                
                <!-- Campos condicionais baseados no gerenciamento de estoque -->
                <div v-if="form.manages_stock" class="space-y-4">
                  <!-- Quantidade em Estoque -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Quantidade em Estoque *</label>
                    <input
                      ref="stockQuantityInput"
                      v-model="form.stock_quantity"
                      type="number"
                      min="0"
                      required
                      @keydown.enter.prevent="focusNextField('minimum_stock')"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                    />
                  </div>
                  
                  <!-- Estoque Mínimo -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Estoque Mínimo</label>
                    <input
                      ref="minimumStockInput"
                      v-model="form.minimum_stock"
                      type="number"
                      min="0"
                      placeholder="0"
                      @keydown.enter.prevent="focusNextField('returnable')"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                    />
                    <p class="mt-1 text-sm text-gray-500">
                      Quando o estoque ficar abaixo deste valor, o produto será marcado como indisponível
                    </p>
                  </div>
                </div>
                
                <!-- Campos para produto filho (que compartilha estoque) -->
                <div v-else class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Produto Pai *</label>
                    <select
                      ref="parentProductInput"
                      v-model="form.parent_product_id"
                      required
                      @keydown.enter.prevent="focusNextField('stock_multiplier')"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                    >
                      <option value="">Selecione o produto pai</option>
                      <option
                        v-for="parent in parentProducts"
                        :key="parent.id"
                        :value="parent.id"
                      >
                        {{ parent.name }} (Estoque: {{ parent.stock_quantity }})
                      </option>
                    </select>
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Quantidade necessária para formar o pacote *</label>
                    <input
                      ref="stockMultiplierInput"
                      v-model="form.stock_multiplier"
                      type="number"
                      min="1"
                      required
                      @keydown.enter.prevent="focusNextField('returnable')"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                      placeholder="Ex: 24 (1 caixa = 24 unidades)"
                    />
                  </div>
                  
                  <!-- Mostrar quantidade calculada no estoque -->
                  <div v-if="form.parent_product_id && form.stock_multiplier" class="p-3 bg-blue-50 rounded-md">
                    <p class="text-sm text-blue-800">
                      <strong>Quantidade disponível deste produto:</strong> 
                      {{ Math.floor((selectedParentProduct?.stock_quantity || 0) / (form.stock_multiplier || 1)) }} unidades
                    </p>
                    <p class="text-xs text-blue-600 mt-1">
                      Baseado no estoque do produto pai ({{ selectedParentProduct?.stock_quantity || 0 }}) ÷ {{ form.stock_multiplier }}
                    </p>
                  </div>
                  
                  <div class="text-xs text-gray-500">
                    <p><strong>Exemplo:</strong> Caixa com 24 cervejas</p>
                    <p>• Produto Pai: Cerveja Skol (unidade)</p>
                    <p>• Quantidade necessária: 24 (1 caixa = 24 unidades)</p>
                    <p>• Ao vender 1 caixa, desconta 24 unidades do estoque</p>
                  </div>
                </div>
              </div>
              
              <!-- Coluna Direita -->
              <div class="space-y-4">
                <!-- Descrição -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">Descrição</label>
                  <textarea
                    ref="descriptionInput"
                    v-model="form.description"
                    rows="3"
                    @keydown.enter.prevent="focusNextField('available')"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                  ></textarea>
                </div>
                
                <!-- Campo Disponível -->
                <div class="flex items-center space-x-3">
                  <input
                    ref="availableInput"
                    v-model="form.available"
                    type="checkbox"
                    id="available"
                    @keydown.enter.prevent="toggleAvailable"
                    class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                  />
                  <label for="available" class="text-sm font-medium text-gray-700">
                    Produto disponível para venda
                  </label>
                </div>
                
                <!-- Se necessário vasilhame -->
                <div class="space-y-3">
                  <div class="flex items-center space-x-3">
                    <input
                      ref="returnableInput"
                      v-model="form.returnable"
                      type="checkbox"
                      id="returnable"
                      @keydown.enter.prevent="toggleReturnable"
                      class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                    />
                    <label for="returnable" class="text-sm font-medium text-gray-700">
                      Se necessário vasilhame
                    </label>
                  </div>
                  
                  <!-- Campos de vassilhame (visíveis apenas se retornável estiver marcado) -->
                  <div v-if="form.returnable" class="ml-7 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                      <div>
                        <label class="block text-sm font-medium text-gray-700">Preço do Vasilhame *</label>
                        <input
                          ref="returnablePriceInput"
                          v-model="form.returnable_price"
                          type="number"
                          step="0.01"
                          min="0"
                          required
                          @keydown.enter.prevent="focusNextField('returnable_quantity')"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                          placeholder="0,00"
                        />
                      </div>
                      
                      <div>
                        <label class="block text-sm font-medium text-gray-700">Qtd. Vasilhames</label>
                        <input
                          ref="returnableQuantityInput"
                          v-model="form.returnable_quantity"
                          type="number"
                          min="1"
                          @keydown.enter.prevent="focusNextField('vasilhame_model_id')"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                        />
                      </div>
                    </div>

                    <!-- Seleção de Modelo de Vasilhame -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Modelo de Vasilhame *</label>
                      <select
                        ref="vasilhameModelInput"
                        v-model="form.vasilhame_model_id"
                        required
                        @keydown.enter.prevent="submitForm"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                      >
                        <option value="">Selecione um modelo</option>
                        <option v-for="modelo in vasilhamesStore.modelos" :key="modelo.id" :value="modelo.id">
                          {{ modelo.name }} — {{ modelo.returnable_quantity }} un. ({{ new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(modelo.returnable_price) }})
                        </option>
                      </select>
                      <p class="text-xs text-gray-500 mt-1">Defina qual modelo de vasilhame este produto consome/devolve.</p>
                    </div>
                    
                    <div class="text-xs text-gray-500">
                      <p><strong>Exemplo:</strong> Cerveja Skol - 1 vasilhame por unidade</p>
                      <p><strong>Exemplo:</strong> Caixa Skol (24 unidades) - 24 vasilhames por caixa</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="flex gap-3 mt-6">
              <button
                type="submit"
                :disabled="submitting"
                class="flex-1 bg-primary-600 text-white py-2 px-4 rounded-md hover:bg-primary-700 disabled:opacity-50"
              >
                {{ submitting ? 'Salvando...' : 'Salvar' }}
              </button>
              <button
                type="button"
                @click="closeModal"
                class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400"
              >
                Cancelar
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal de Gerenciamento de Estoque -->
    <div v-if="showStockModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Gerenciar Estoque - {{ selectedProduct?.name }}
          </h3>
          
          <!-- Informações Atuais -->
          <div v-if="selectedProduct" class="mb-6 p-4 bg-gray-50 rounded-lg">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Situação Atual</h4>
            <div class="space-y-1 text-sm">
              <div class="flex justify-between">
                <span>Estoque Atual:</span>
                <span class="font-medium">{{ selectedProduct.actual_stock || selectedProduct.stock_quantity }} unidades</span>
              </div>
              <div v-if="selectedProduct.minimum_stock" class="flex justify-between">
                <span>Estoque Mínimo:</span>
                <span class="font-medium">{{ selectedProduct.minimum_stock }} unidades</span>
              </div>
              <div v-if="!selectedProduct.manages_stock && selectedProduct.parent_product" class="text-xs text-gray-500">
                Produto compartilha estoque com: {{ selectedProduct.parent_product.name }}
              </div>
            </div>
          </div>

          <!-- Abas -->
          <div class="border-b border-gray-200 mb-4">
            <nav class="-mb-px flex space-x-8">
              <button
                @click="stockTab = 'adjust'"
                :class="{
                  'border-primary-500 text-primary-600': stockTab === 'adjust',
                  'border-transparent text-gray-500 hover:text-gray-700': stockTab !== 'adjust'
                }"
                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
              >
                Ajustar Estoque
              </button>
              <button
                @click="stockTab = 'movement'"
                :class="{
                  'border-primary-500 text-primary-600': stockTab === 'movement',
                  'border-transparent text-gray-500 hover:text-gray-700': stockTab !== 'movement'
                }"
                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
              >
                Entrada/Saída
              </button>
            </nav>
          </div>

          <!-- Aba Ajustar Estoque -->
          <div v-if="stockTab === 'adjust'">
            <form @submit.prevent="updateStock">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Nova Quantidade</label>
                  <input 
                    v-model="stockForm.new_quantity"
                    type="number" 
                    min="0"
                    required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Digite a nova quantidade"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Motivo do Ajuste</label>
                  <select 
                    v-model="stockForm.reason"
                    required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="">Selecione o motivo</option>
                    <option value="inventory">Inventário</option>
                    <option value="correction">Correção</option>
                    <option value="damage">Produto danificado</option>
                    <option value="loss">Perda</option>
                    <option value="other">Outro</option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                  <textarea 
                    v-model="stockForm.notes"
                    rows="3"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Observações sobre o ajuste (opcional)"
                  ></textarea>
                </div>
              </div>
              
              <div class="flex justify-end space-x-3 mt-6">
                <button
                  type="button"
                  @click="closeStockModal"
                  class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-400"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  :disabled="loading"
                  class="px-4 py-2 bg-primary-600 text-white rounded-md text-sm font-medium hover:bg-primary-700 disabled:opacity-50"
                >
                  {{ loading ? 'Salvando...' : 'Atualizar Estoque' }}
                </button>
              </div>
            </form>
          </div>

          <!-- Aba Entrada/Saída -->
          <div v-if="stockTab === 'movement'">
            <form @submit.prevent="addStockMovement">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Movimento</label>
                  <select 
                    v-model="stockForm.movement_type"
                    required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="">Selecione o tipo</option>
                    <option value="in">Entrada (+)</option>
                    <option value="out">Saída (-)</option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade</label>
                  <input 
                    v-model="stockForm.quantity"
                    type="number" 
                    min="1"
                    required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Digite a quantidade"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Motivo</label>
                  <select 
                    v-model="stockForm.reason"
                    required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                  >
                    <option value="">Selecione o motivo</option>
                    <template v-if="stockForm.movement_type === 'in'">
                      <option value="purchase">Compra</option>
                      <option value="return">Devolução</option>
                      <option value="production">Produção</option>
                      <option value="adjustment">Ajuste</option>
                    </template>
                    <template v-if="stockForm.movement_type === 'out'">
                      <option value="sale">Venda</option>
                      <option value="damage">Produto danificado</option>
                      <option value="loss">Perda</option>
                      <option value="transfer">Transferência</option>
                      <option value="adjustment">Ajuste</option>
                    </template>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                  <textarea 
                    v-model="stockForm.notes"
                    rows="3"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Observações sobre o movimento (opcional)"
                  ></textarea>
                </div>
                
                <!-- Preview do resultado -->
                <div v-if="stockForm.quantity && selectedProduct" class="p-3 bg-blue-50 rounded-md">
                  <p class="text-sm text-blue-800">
                    <strong>Resultado:</strong> 
                    {{ (selectedProduct.actual_stock || selectedProduct.stock_quantity) }}
                    {{ stockForm.movement_type === 'in' ? '+' : '-' }}
                    {{ stockForm.quantity }}
                    = 
                    {{ calculateNewStock() }} unidades
                  </p>
                </div>
              </div>
              
              <div class="flex justify-end space-x-3 mt-6">
                <button
                  type="button"
                  @click="closeStockModal"
                  class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-400"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  :disabled="loading"
                  class="px-4 py-2 bg-primary-600 text-white rounded-md text-sm font-medium hover:bg-primary-700 disabled:opacity-50"
                >
                  {{ loading ? 'Salvando...' : 'Registrar Movimento' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useProductsStore } from '@/stores/products'
import { useVasilhamesStore } from '@/stores/vasilhames'
import { useSettingsStore } from '@/stores/settings'
import AppLayout from '@/components/AppLayout.vue'
import api from '@/services/api'

const router = useRouter()
const productsStore = useProductsStore()
const vasilhamesStore = useVasilhamesStore()
const settingsStore = useSettingsStore()

const loading = ref(false)
const searchQuery = ref('')
const statusFilter = ref('')
const showCreateModal = ref(false)
const showEditModal = ref(false)
const editingProduct = ref<any>(null)
const submitting = ref(false)

// XML Import variables
const showXmlImport = ref(false)
const selectedFile = ref<File | null>(null)
const validating = ref(false)
const importing = ref(false)
const validationResult = ref<any>(null)
const importResult = ref<any>(null)
const xmlFileInput = ref<HTMLInputElement>()

// Margin configuration variables
const useDefaultMargin = ref(true)
const customMargin = ref<number | string>('')
const priceFactor = ref<number | string>('')

// Modal de Estoque
const showStockModal = ref(false)
const selectedProduct = ref<any>(null)
const stockTab = ref('adjust')
const stockForm = ref({
  new_quantity: '',
  quantity: '',
  movement_type: '',
  reason: '',
  notes: ''
})

const form = ref({
  name: '',
  description: '',
  price: '',
  purchase_price: '',
  profit_margin: '',
  use_default_margin: true,
  stock_quantity: '',
  minimum_stock: '',
  sku: '',
  barcode: '',
  available: true,
  returnable: false,
  returnable_price: '',
  returnable_quantity: 1,
  vasilhame_model_id: null as number | null,
  parent_product_id: null as number | null,
  stock_multiplier: 1,
  manages_stock: true
})

const parentProducts = ref<any[]>([])

// Refs para os campos do formulário
const barcodeInput = ref<HTMLInputElement>()
const nameInput = ref<HTMLInputElement>()
const priceInput = ref<HTMLInputElement>()
const skuInput = ref<HTMLInputElement>()
const managesStockInput = ref<HTMLInputElement>()
const stockQuantityInput = ref<HTMLInputElement>()
const minimumStockInput = ref<HTMLInputElement>()
const parentProductInput = ref<HTMLSelectElement>()
const stockMultiplierInput = ref<HTMLInputElement>()
const descriptionInput = ref<HTMLTextAreaElement>()
const availableInput = ref<HTMLInputElement>()
const returnableInput = ref<HTMLInputElement>()
const returnablePriceInput = ref<HTMLInputElement>()
const returnableQuantityInput = ref<HTMLInputElement>()
const vasilhameModelInput = ref<HTMLSelectElement>()

// Computed para produto pai selecionado
const selectedParentProduct = computed(() => {
  if (!form.value.parent_product_id) return null
  return parentProducts.value.find(p => p.id === form.value.parent_product_id)
})

// Função para focar no próximo campo
const focusNextField = (fieldName: string) => {
  const fieldMap: Record<string, any> = {
    name: nameInput.value,
    price: priceInput.value,
    sku: skuInput.value,
    manages_stock: managesStockInput.value,
    stock_quantity: stockQuantityInput.value,
    minimum_stock: minimumStockInput.value,
    parent_product_id: parentProductInput.value,
    stock_multiplier: stockMultiplierInput.value,
    description: descriptionInput.value,
    available: availableInput.value,
    returnable: returnableInput.value,
    returnable_price: returnablePriceInput.value,
    returnable_quantity: returnableQuantityInput.value,
    vasilhame_model_id: vasilhameModelInput.value
  }
  
  const nextField = fieldMap[fieldName]
  if (nextField) {
    nextField.focus()
  }
}

// Função para alternar checkbox com Enter
const toggleManagesStock = () => {
  form.value.manages_stock = !form.value.manages_stock
  // Focar no próximo campo baseado no estado
  if (form.value.manages_stock) {
    focusNextField('stock_quantity')
  } else {
    focusNextField('parent_product_id')
  }
}

const toggleAvailable = () => {
  form.value.available = !form.value.available
  focusNextField('returnable')
}

const toggleReturnable = () => {
  form.value.returnable = !form.value.returnable
  if (form.value.returnable) {
    focusNextField('returnable_price')
  }
}

// Funções de cálculo para preço e margem
const calculateProfitMargin = () => {
  const purchasePrice = parseFloat(form.value.purchase_price)
  const salePrice = parseFloat(form.value.price)
  
  if (purchasePrice > 0 && salePrice > 0) {
    const margin = ((salePrice - purchasePrice) / purchasePrice) * 100
    form.value.profit_margin = margin.toFixed(2)
    // Desmarcar margem padrão quando calculada manualmente
    form.value.use_default_margin = false
  }
}

const calculateSalePrice = () => {
  const purchasePrice = parseFloat(form.value.purchase_price)
  const margin = parseFloat(form.value.profit_margin)
  
  if (purchasePrice > 0 && margin > 0) {
    const salePrice = purchasePrice * (1 + margin / 100)
    form.value.price = salePrice.toFixed(2)
  }
}

// Função para lidar com mudança na margem padrão
const onDefaultMarginToggle = () => {
  if (form.value.use_default_margin) {
    // Aplicar margem padrão
    const defaultMargin = parseFloat(settingsStore.salesSettings.default_profit_margin)
    form.value.profit_margin = defaultMargin.toString()
    
    // Recalcular preço se há valor de compra
    const purchasePrice = parseFloat(form.value.purchase_price)
    if (purchasePrice > 0) {
      const salePrice = purchasePrice * (1 + defaultMargin / 100)
      form.value.price = salePrice.toFixed(2)
    }
  }
}

// Função para lidar com mudança manual na margem
const onMarginChange = () => {
  // Desmarcar margem padrão quando alterada manualmente
  form.value.use_default_margin = false
  calculateSalePrice()
}

// Função para lidar com mudança no preço de venda
const onPriceChange = () => {
  // Desmarcar margem padrão quando preço alterado manualmente
  form.value.use_default_margin = false
  calculateProfitMargin()
}

// Função para submeter o formulário
const submitForm = () => {
  handleSubmit()
}

// Função para lidar com teclas globais do formulário
const handleKeyDown = (event: KeyboardEvent) => {
  // Permitir navegação normal dentro de textareas
  if (event.target instanceof HTMLTextAreaElement) {
    return
  }
  
  // ESC para fechar modal
  if (event.key === 'Escape') {
    closeModal()
  }
}

const filteredProducts = computed(() => {
  let products = productsStore.products

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    products = products.filter(product => 
      product.name.toLowerCase().includes(query) ||
      product.sku?.toLowerCase().includes(query) ||
      product.barcode?.toLowerCase().includes(query)
    )
  }

  if (statusFilter.value === 'active') {
    products = products.filter(product => product.active)
  } else if (statusFilter.value === 'inactive') {
    products = products.filter(product => !product.active)
  }

  return products
})

const handleSearch = () => {
  // A busca é reativa através do computed filteredProducts
}

const editProduct = (product: any) => {
  editingProduct.value = product
  
  // Verificar se o produto está usando margem padrão
  const defaultMargin = parseFloat(settingsStore.salesSettings.default_profit_margin)
  const productMargin = product.profit_margin ? parseFloat(product.profit_margin) : 0
  const isUsingDefaultMargin = Math.abs(productMargin - defaultMargin) < 0.01
  
  form.value = {
    name: product.name,
    description: product.description || '',
    price: product.price.toString(),
    purchase_price: product.purchase_price ? product.purchase_price.toString() : '',
    profit_margin: product.profit_margin ? product.profit_margin.toString() : '',
    use_default_margin: isUsingDefaultMargin,
    stock_quantity: product.stock_quantity.toString(),
    minimum_stock: product.minimum_stock ? product.minimum_stock.toString() : '',
    sku: product.sku || '',
    barcode: product.barcode || '',
    available: product.available,
    returnable: product.returnable,
    returnable_price: product.returnable_price ? product.returnable_price.toString() : '',
    returnable_quantity: product.returnable_quantity || 1,
    vasilhame_model_id: product.vasilhame_model_id || null,
    parent_product_id: product.parent_product_id,
    stock_multiplier: product.stock_multiplier || 1,
    manages_stock: product.manages_stock
  }
  showEditModal.value = true
}

const deleteProduct = async (id: number) => {
  if (confirm('Tem certeza que deseja excluir este produto?')) {
    await productsStore.deleteProduct(id)
  }
}

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(price)
}

const handleSubmit = async () => {
  submitting.value = true
  
  try {
    const productData: any = {
      name: form.value.name,
      description: form.value.description || null,
      price: parseFloat(form.value.price),
      purchase_price: form.value.purchase_price ? parseFloat(form.value.purchase_price) : null,
      profit_margin: form.value.profit_margin ? parseFloat(form.value.profit_margin) : null,
      stock_quantity: parseInt(form.value.stock_quantity),
      minimum_stock: form.value.minimum_stock ? parseInt(form.value.minimum_stock) : 0,
      sku: form.value.sku || null,
      barcode: form.value.barcode || null,
      available: form.value.available,
      returnable: form.value.returnable,
      returnable_price: form.value.returnable && form.value.returnable_price ? parseFloat(form.value.returnable_price) : null,
      returnable_quantity: form.value.returnable ? parseInt(form.value.returnable_quantity as any) || 1 : null,
      vasilhame_model_id: form.value.returnable ? form.value.vasilhame_model_id : null,
      parent_product_id: form.value.parent_product_id || null,
      stock_multiplier: parseInt(form.value.stock_multiplier as any) || 1,
      manages_stock: form.value.manages_stock
    }
    if (showEditModal.value && editingProduct.value) {
      await productsStore.updateProduct(editingProduct.value.id, {
        ...productData,
        returnable_quantity: productData.returnable_quantity ?? undefined
      })
    } else {
      await productsStore.createProduct({
        ...productData,
        returnable_quantity: productData.returnable_quantity ?? undefined
      })
    }

    closeModal()
  } catch (error) {
    console.error('Erro ao salvar produto:', error)
  } finally {
    submitting.value = false
  }
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  editingProduct.value = null
  form.value = {
    name: '',
    description: '',
    price: '',
    purchase_price: '',
    profit_margin: '',
    use_default_margin: true,
    stock_quantity: '',
    sku: '',
    barcode: '',
    available: true,
    returnable: false,
    returnable_price: '',
    returnable_quantity: 1,
    vasilhame_model_id: null,
    parent_product_id: null,
    stock_multiplier: 1,
    manages_stock: true,
    minimum_stock: ''
  }
}

const createProduct = () => {
  showCreateModal.value = true
}

// Métodos do Modal de Estoque
const manageStock = (product: any) => {
  selectedProduct.value = product
  stockTab.value = 'adjust'
  stockForm.value = {
    new_quantity: product.actual_stock || product.stock_quantity || '',
    quantity: '',
    movement_type: '',
    reason: '',
    notes: ''
  }
  showStockModal.value = true
}

const closeStockModal = () => {
  showStockModal.value = false
  selectedProduct.value = null
  stockForm.value = {
    new_quantity: '',
    quantity: '',
    movement_type: '',
    reason: '',
    notes: ''
  }
}

const calculateNewStock = () => {
  if (!selectedProduct.value || !stockForm.value.quantity) return 0
  
  const currentStock = selectedProduct.value.actual_stock || selectedProduct.value.stock_quantity || 0
  const quantity = parseInt(stockForm.value.quantity)
  
  if (stockForm.value.movement_type === 'in') {
    return currentStock + quantity
  } else {
    return Math.max(0, currentStock - quantity)
  }
}

const updateStock = async () => {
  if (!selectedProduct.value) return
  
  loading.value = true
  try {
    const result = await productsStore.updateProductStock(selectedProduct.value.id, {
      new_quantity: parseInt(stockForm.value.new_quantity),
      reason: stockForm.value.reason,
      notes: stockForm.value.notes
    })
    
    if (result.success) {
      await productsStore.fetchProducts()
      closeStockModal()
    }
  } catch (error) {
    console.error('Erro ao atualizar estoque:', error)
  } finally {
    loading.value = false
  }
}

const addStockMovement = async () => {
  if (!selectedProduct.value) return
  
  loading.value = true
  try {
    const result = await productsStore.addStockMovement(selectedProduct.value.id, {
      type: stockForm.value.movement_type,
      quantity: parseInt(stockForm.value.quantity),
      reason: stockForm.value.reason,
      notes: stockForm.value.notes
    })
    
    if (result.success) {
      await productsStore.fetchProducts()
      closeStockModal()
    }
  } catch (error) {
    console.error('Erro ao registrar movimento de estoque:', error)
  } finally {
    loading.value = false
  }
}

// Watcher para focar no código de barras quando modal abrir
watch(showCreateModal, (newValue) => {
  if (newValue) {
    nextTick(() => {
      barcodeInput.value?.focus()
    })
  }
})

// Watcher para focar no código de barras quando modal de edição abrir
watch(showEditModal, (newValue) => {
  if (newValue) {
    nextTick(() => {
      barcodeInput.value?.focus()
    })
  }
})

const loadParentProducts = async () => {
  const result = await productsStore.fetchParentProducts()
  if (result.success) {
    parentProducts.value = result.products
  }
}

// XML Import Functions
const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    selectedFile.value = target.files[0]
    validationResult.value = null
    importResult.value = null
  }
}

const removeFile = () => {
  selectedFile.value = null
  validationResult.value = null
  importResult.value = null
  if (xmlFileInput.value) {
    xmlFileInput.value.value = ''
  }
}

const formatFileSize = (bytes: number) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const validateXml = async () => {
  if (!selectedFile.value) return
  
  validating.value = true
  validationResult.value = null
  
  try {
    const formData = new FormData()
    formData.append('xml_file', selectedFile.value)
    
    const response = await api.post('/products/xml/validate', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    
    validationResult.value = response.data
    
    if (!response.data.valid) {
      console.error('Erro na validação:', response.data.message)
    }
  } catch (error) {
    console.error('Erro ao validar XML:', error)
    validationResult.value = {
      valid: false,
      message: 'Erro ao processar o arquivo XML. Verifique se o arquivo está correto.'
    }
  } finally {
    validating.value = false
  }
}

const importXml = async () => {
  if (!selectedFile.value || !validationResult.value?.valid) return
  
  importing.value = true
  importResult.value = null
  
  try {
    const formData = new FormData()
    formData.append('xml_file', selectedFile.value)
    
    // Adicionar configurações de margem
    if (useDefaultMargin.value) {
      formData.append('use_default_margin', 'true')
      formData.append('default_margin', settingsStore.salesSettings.default_profit_margin.toString())
    } else {
      formData.append('use_default_margin', 'false')
      if (customMargin.value) {
        formData.append('custom_margin', customMargin.value.toString())
      }
      if (priceFactor.value) {
        formData.append('price_factor', priceFactor.value.toString())
      }
    }
    
    const response = await api.post('/products/xml/import', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    
    importResult.value = response.data
    
    if (response.data.success) {
      // Recarregar lista de produtos
      await productsStore.fetchProducts()
      
      // Limpar formulário
      selectedFile.value = null
      validationResult.value = null
      if (xmlFileInput.value) {
        xmlFileInput.value.value = ''
      }
    }
  } catch (error) {
    console.error('Erro ao importar XML:', error)
    importResult.value = {
      success: false,
      message: 'Erro ao importar produtos. Tente novamente.'
    }
  } finally {
    importing.value = false
  }
}

onMounted(async () => {
  loading.value = true
  await productsStore.fetchProducts()
  await loadParentProducts()
  // carregar modelos de vasilhame para o select
  try { await vasilhamesStore.carregarModelos() } catch {}
  // carregar configurações
  await settingsStore.loadSettings()
  loading.value = false
})
</script>
