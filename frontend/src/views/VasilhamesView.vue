<template>
  <AppLayout>
    <!-- Header da Página -->
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Gestão de Vasilhames</h1>
        <p class="text-gray-600">Gerencie os vasilhames em circulação e clientes com débitos</p>
      </div>
      <div class="flex items-center gap-2">
        <button
          @click="abrirModalTipo"
          class="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 transition-colors flex items-center space-x-2"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          <span>Cadastrar Tipo</span>
        </button>
      </div>
    </div>

    <!-- Aba de Navegação -->
    <div class="bg-white rounded-lg shadow mb-6">
      <nav class="flex flex-wrap border-b border-gray-200 px-4 sm:px-6">
        <button
          @click="abaAtiva = 'clientes'"
          :class="[
            'py-3 px-2 sm:px-4 border-b-2 font-medium text-sm whitespace-nowrap',
            abaAtiva === 'clientes'
              ? 'border-primary-500 text-primary-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Clientes com Débito
        </button>
        <button
          @click="abaAtiva = 'modelos'"
          :class="[
            'py-3 px-2 sm:px-4 border-b-2 font-medium text-sm whitespace-nowrap',
            abaAtiva === 'modelos'
              ? 'border-primary-500 text-primary-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Modelos Cadastrados
        </button>
      </nav>
    </div>

    <!-- Estatísticas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
      <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-6 w-6 sm:h-8 sm:w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
          </div>
          <div class="ml-3 sm:ml-4">
            <p class="text-xs sm:text-sm font-medium text-gray-500">Total de Clientes</p>
            <p class="text-lg sm:text-2xl font-semibold text-gray-900">{{ estatisticas.totalClientes }}</p>
          </div>
        </div>
      </div>
      

      
      <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-6 w-6 sm:h-8 sm:w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
            </svg>
          </div>
          <div class="ml-3 sm:ml-4">
            <p class="text-xs sm:text-sm font-medium text-gray-500">Vasilhames Circulando</p>
            <p class="text-lg sm:text-2xl font-semibold text-gray-900">{{ estatisticas.totalVasilhames }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-6 w-6 sm:h-8 sm:w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
          </div>
          <div class="ml-3 sm:ml-4">
            <p class="text-xs sm:text-sm font-medium text-gray-500">Clientes com Débito</p>
            <p class="text-lg sm:text-2xl font-semibold text-gray-900">{{ estatisticas.clientesComDebito }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtros e Busca -->
    <div v-if="abaAtiva === 'clientes'" class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6">
      <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">
        <select 
          v-model="filtroSelecionado" 
          class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 touch-manipulation"
        >
          <option value="todos">Todos os Clientes</option>
          <option value="comDebito">Com Débito</option>
          <option value="semDebito">Sem Débito</option>
        </select>
        
        <div class="relative flex-1">
          <input 
            v-model="termoBusca" 
            type="text" 
            placeholder="Buscar cliente..."
            class="w-full border border-gray-300 rounded-md pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 touch-manipulation"
          />
          <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
        </div>
        
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
          <button 
            @click="buscarClientesVasilhames" 
            class="bg-primary-600 text-white px-4 py-3 rounded-md hover:bg-primary-700 active:bg-primary-800 transition-colors flex items-center justify-center space-x-2 text-sm font-medium touch-manipulation min-h-[44px]"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            <span>Buscar</span>
          </button>
          
          <button 
            class="bg-green-600 text-white px-4 py-3 rounded-md hover:bg-green-700 active:bg-green-800 transition-colors flex items-center justify-center space-x-2 text-sm font-medium touch-manipulation min-h-[44px]"
            @click="abrirModal('saida', { id: 0, nome: 'Novo Cliente', documento: '', totalVasilhames: 0, valorTotal: 0 })"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            <span>Nova Saída</span>
          </button>
        </div>
      </div>
    </div>
      
    

    <!-- Conteúdo da Aba -->
   
      <!-- Tabela de Clientes -->
        <div v-if="vasilhamesStore.carregando" class="text-center py-12">
          <svg class="animate-spin h-8 w-8 text-primary-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <p class="mt-2 text-gray-600">Carregando dados...</p>
        </div>
        
        <div v-else-if="vasilhamesStore.erro" class="bg-red-50 border border-red-200 rounded-md p-4 m-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-red-800">{{ vasilhamesStore.erro }}</p>
            </div>
            <div class="ml-auto pl-3">
              <button @click="buscarClientesVasilhames" class="text-red-400 hover:text-red-600">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
          <button 
            @click="buscarClientesVasilhames" 
            class="mt-3 bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors text-sm"
          >
            Tentar Novamente
          </button>
        </div>
        
        <div v-else>
           <!-- Layout Mobile Simplificado -->
          <div class="block lg:hidden space-y-2 p-3">
            <div v-for="cliente in clientesFiltrados.filter(c => c.totalVasilhames > 0)" :key="cliente.id" 
                 class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
              
              <!-- Layout Vertical: Nome em cima, botões embaixo -->
              <div class="p-3">
                <!-- Nome do Cliente -->
                <div class="mb-3">
                  <div class="text-sm font-semibold text-gray-900">{{ cliente.nome }}</div>
                  <div class="text-xs text-gray-500">{{ cliente.documento }}</div>
                  <div class="text-xs mt-1">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                      Em Débito - {{ cliente.totalVasilhames }} vasilhames
                    </span>
                  </div>
                </div>
                
                <!-- Botões de Ação -->
                <div class="flex gap-2 justify-between">
                  <!-- Devolver -->
                  <button 
                    @click="abrirModal('devolucao', cliente)" 
                    class="flex-1 px-3 py-2 bg-blue-600 text-white rounded text-xs font-medium hover:bg-blue-700 transition-colors"
                  >
                    DEVOLVER
                  </button>
                  
                  <!-- Histórico -->
                  <button 
                    @click="verHistorico(cliente)" 
                    class="flex-1 px-3 py-2 bg-gray-600 text-white rounded text-xs font-medium hover:bg-gray-700 transition-colors"
                  >
                    HISTÓRICO
                  </button>
                  
                  <!-- Nova Saída -->
                  <button 
                    @click="abrirModal('saida', cliente)" 
                    class="flex-1 px-3 py-2 bg-green-600 text-white rounded text-xs font-medium hover:bg-green-700 transition-colors"
                  >
                    NOVA SAÍDA
                  </button>
                </div>
              </div>
            </div>
            
            <!-- Mensagem quando não há clientes com débitos -->
            <div v-if="clientesFiltrados.filter(c => c.totalVasilhames > 0).length === 0" class="text-center py-8">
              <div class="text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum cliente com débitos</h3>
                <p class="mt-1 text-sm text-gray-500">Todos os clientes estão em dia com os vasilhames.</p>
              </div>
            </div>
          </div>
          <!-- Layout Desktop - Tabela -->
          <div class="hidden lg:block overflow-x-auto -mx-4 sm:mx-0">
            <div class="inline-block min-w-full align-middle">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <!-- Coluna Nome -->
                    <th class="px-2 sm:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 z-10 min-w-[140px]">
                      Nome
                    </th>
                    <!-- Colunas dinâmicas para cada modelo -->
                    <th 
                      v-for="modelo in vasilhamesStore.modelos" 
                      :key="modelo.id"
                      class="px-2 sm:px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[80px] sm:min-w-[100px]"
                      :title="`${modelo.name} - R$ ${modelo.returnable_price}`"
                    >
                      <div class="flex flex-col items-center">
                        <span class="truncate max-w-[60px] sm:max-w-[80px]">{{ modelo.name }}</span>
                        <span class="text-xs text-gray-400 font-normal">R$ {{ modelo.returnable_price }}</span>
                      </div>
                    </th>
                    <!-- Coluna Devolução -->
                    <th class="px-2 sm:px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[80px]">
                      Devolução
                    </th>
                    <!-- Coluna Movimentações -->
                    <th class="px-2 sm:px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[120px]">
                      Movimentações
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="cliente in clientesFiltrados.filter(c => c.totalVasilhames > 0)" :key="cliente.id" class="hover:bg-gray-50">
                    <!-- Coluna Nome -->
                    <td class="px-2 sm:px-4 py-3 sm:py-4 whitespace-nowrap sticky left-0 bg-white z-10 border-r border-gray-200">
                    <div class="flex flex-col">
                      <div class="text-sm font-medium text-gray-900">{{ cliente.nome }}</div>
                      <div class="text-xs text-gray-500">{{ cliente.documento }}</div>
                      <div class="text-xs mt-1">
                        <span :class="['inline-flex px-2 py-1 text-xs font-semibold rounded-full', cliente.totalVasilhames > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800']">
                          {{ cliente.status || (cliente.totalVasilhames > 0 ? 'Em Débito' : 'Quitado') }}
                        </span>
                      </div>
                    </div>
                  </td>
                  <!-- Colunas dinâmicas para cada modelo -->
                  <td 
                    v-for="modelo in vasilhamesStore.modelos" 
                    :key="modelo.id"
                    class="px-2 sm:px-3 py-3 sm:py-4 text-center whitespace-nowrap"
                  >
                    <div class="flex flex-col items-center">
                      <span 
                        :class="[
                          'text-sm font-bold',
                          getQuantidadeModelo(cliente, modelo.id) > 0 ? 'text-red-600' : 'text-gray-400'
                        ]"
                      >
                        {{ getQuantidadeModelo(cliente, modelo.id) }}
                      </span>
                      <span v-if="getQuantidadeModelo(cliente, modelo.id) > 0" class="text-xs text-gray-500">
                        R$ {{ ((getQuantidadeModelo(cliente, modelo.id) || 0) * (modelo.returnable_price || 0)).toFixed(2) }}
                      </span>
                    </div>
                  </td>
                  <!-- Coluna Devolução -->
                  <td class="px-2 sm:px-4 py-3 sm:py-4 text-center whitespace-nowrap">
                    <button 
                      @click="abrirModal('devolucao', cliente)" 
                      :disabled="cliente.totalVasilhames === 0"
                      :class="[
                        'px-2 sm:px-3 py-1 rounded-md text-xs sm:text-sm font-medium transition-colors',
                        cliente.totalVasilhames === 0 
                          ? 'bg-gray-200 text-gray-400 cursor-not-allowed' 
                          : 'bg-blue-600 text-white hover:bg-blue-700'
                      ]"
                    >
                      Devolver
                    </button>
                  </td>
                  <!-- Coluna Movimentações -->
                  <td class="px-2 sm:px-4 py-3 sm:py-4 text-center whitespace-nowrap">
                    <div class="flex flex-col items-center space-y-1">
                      <button 
                        @click="verHistorico(cliente)" 
                        class="px-2 sm:px-3 py-1 bg-gray-600 text-white rounded-md text-xs sm:text-sm hover:bg-gray-700 font-medium transition-colors w-full"
                      >
                        Histórico
                      </button>
                      <button 
                        @click="abrirModal('saida', cliente)" 
                        class="px-2 sm:px-3 py-1 bg-green-600 text-white rounded-md text-xs sm:text-sm hover:bg-green-700 font-medium transition-colors w-full"
                      >
                        Nova Saída
                      </button>
                      <div v-if="cliente.ultimaMovimentacao" class="text-xs text-gray-500">
                        {{ formatarData(cliente.ultimaMovimentacao) }}
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

         
          
          <div v-if="clientesFiltrados.filter(c => c.totalVasilhames > 0).length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="mt-2 text-gray-600">Nenhum cliente com débitos encontrado</p>
            <p class="text-sm text-gray-500">Todos os clientes estão em dia com os vasilhames.</p>
          </div>
        </div>
      </div>
    

    <!-- Seção de Pendências -->
    <div v-if="abaAtiva === 'pendencias'" class="bg-white rounded-lg shadow overflow-hidden">
      <div class="p-4 sm:p-6 border-b border-gray-200">
        <div class="flex flex-col space-y-4 sm:flex-row sm:justify-between sm:items-center sm:space-y-0">
          <h2 class="text-lg font-medium text-gray-900">Pendências de Vasilhames</h2>
          <div class="flex flex-col space-y-2 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">
            <select 
              v-model="filtroPendencias.resolvida" 
              class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
              @change="carregarPendencias"
            >
              <option :value="false">Pendentes</option>
              <option :value="true">Resolvidas</option>
              <option :value="null">Todas</option>
            </select>
            
            <button
              @click="carregarPendencias"
              class="bg-primary-600 text-white px-3 sm:px-4 py-2 rounded-md hover:bg-primary-700 transition-colors flex items-center justify-center space-x-2 text-sm"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
              <span>Atualizar</span>
            </button>
          </div>
        </div>
      </div>
      
      <!-- Lista de Pendências -->
      <div v-if="vasilhamesStore.carregandoPendencias" class="text-center py-12">
        <svg class="animate-spin h-8 w-8 text-primary-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="mt-2 text-gray-600">Carregando pendências...</p>
      </div>
      
      <div v-else-if="clienteSelecionadoPendencias" class="p-4 sm:p-6">
        <!-- Detalhes de pendências por cliente -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-2 sm:space-y-0">
          <h3 class="text-lg font-medium text-gray-900">
            Pendências de {{ clientePendencias?.name || 'Cliente' }}
          </h3>
          <button
            @click="limparPendencias"
            class="self-end sm:self-auto text-gray-600 hover:text-gray-900 p-2 -m-2"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <div v-if="pendenciasDoCliente.length === 0" class="text-center py-8">
          <p class="text-gray-600">Nenhuma pendência encontrada para este cliente.</p>
        </div>
        
        <div v-else class="space-y-4 sm:space-y-6">
          <div v-for="grupo in pendenciasDoCliente" :key="grupo.modelo.id" class="border border-gray-200 rounded-lg p-3 sm:p-4">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-3 sm:mb-4 space-y-2 sm:space-y-0">
              <h4 class="font-medium text-gray-900 text-sm sm:text-base">{{ grupo.modelo.name }}</h4>
              <div class="text-xs sm:text-sm bg-red-100 text-red-800 px-2 py-1 rounded self-start sm:self-auto">
                {{ grupo.quantidade_total }} unidades pendentes
              </div>
            </div>
            
            <div class="space-y-3 sm:space-y-4">
              <div v-for="pendencia in grupo.pendencias" :key="pendencia.id" class="bg-gray-50 p-3 rounded">
                <div class="flex flex-col space-y-3 sm:space-y-0 sm:flex-row sm:justify-between sm:items-start">
                  <div class="flex-1 space-y-1">
                    <div class="grid grid-cols-2 gap-2 text-xs sm:text-sm">
                      <div>
                        <span class="font-medium text-gray-700">Venda:</span> 
                        <div class="text-gray-900">#{{ pendencia.venda_id || 'N/A' }}</div>
                      </div>
                      <div>
                        <span class="font-medium text-gray-700">Necessários:</span> 
                        <div class="text-gray-900">{{ pendencia.quantidade_necessaria }}</div>
                      </div>
                      <div>
                        <span class="font-medium text-gray-700">Entregues:</span> 
                        <div class="text-gray-900">{{ pendencia.quantidade_entregue }}</div>
                      </div>
                      <div>
                        <span class="font-medium text-gray-700">Pendentes:</span> 
                        <div class="text-red-600 font-medium">{{ pendencia.quantidade_pendente }}</div>
                      </div>
                    </div>
                    <p v-if="pendencia.observacoes" class="text-xs text-gray-500 mt-2 p-2 bg-gray-100 rounded">
                      {{ pendencia.observacoes }}
                    </p>
                  </div>
                  
                  <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-2 sm:space-y-0 sm:space-x-2 sm:ml-4">
                    <div class="flex items-center space-x-2">
                      <label class="text-xs text-gray-600 whitespace-nowrap">Devolver:</label>
                      <input
                        type="number"
                        min="1"
                        :max="pendencia.quantidade_pendente"
                        v-model.number="(pendencia as any).quantidadeDevolver"
                        class="w-16 border border-gray-300 rounded px-2 py-1 text-sm"
                        :placeholder="pendencia.quantidade_pendente.toString()"
                      />
                    </div>
                    <button
                      @click="resolverPendencia(pendencia.id, pendencia.quantidade_pendente)"
                      class="bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700 text-sm font-medium transition-colors"
                    >
                      Devolver
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div v-else>
        <!-- Versão Mobile: Cards -->
        <div class="block sm:hidden">
          <div v-if="vasilhamesStore.pendencias.length === 0" class="p-4 text-center text-gray-500">
            Nenhuma pendência encontrada
          </div>
          <div v-else class="space-y-3 p-4">
            <div v-for="pendencia in vasilhamesStore.pendencias" :key="pendencia.id" class="bg-gray-50 rounded-lg p-4 border border-gray-200">
              <div class="flex justify-between items-start mb-3">
                <div class="flex-1">
                  <h4 class="text-sm font-medium text-gray-900">
                    {{ (pendencia as any).cliente?.name || `Cliente #${pendencia.cliente_id}` }}
                  </h4>
                  <p class="text-xs text-gray-500 mt-1">
                    {{ pendencia.vasilhameModel?.name || `Modelo #${pendencia.vasilhame_model_id}` }}
                  </p>
                </div>
                <span
                  :class="[
                    'px-2 py-1 text-xs font-medium rounded-full',
                    pendencia.resolvida
                      ? 'bg-green-100 text-green-800'
                      : 'bg-yellow-100 text-yellow-800'
                  ]"
                >
                  {{ pendencia.resolvida ? 'Resolvida' : 'Pendente' }}
                </span>
              </div>
              
              <div class="grid grid-cols-2 gap-3 text-xs">
                <div>
                  <span class="text-gray-500">Quantidade:</span>
                  <div class="font-medium">{{ pendencia.quantidade_pendente }} / {{ pendencia.quantidade_necessaria }}</div>
                  <div class="text-gray-400">{{ pendencia.quantidade_entregue }} entregues</div>
                </div>
                <div>
                  <span class="text-gray-500">Venda:</span>
                  <div class="font-medium">#{{ pendencia.venda_id || 'N/A' }}</div>
                </div>
              </div>
              
              <div v-if="!pendencia.resolvida" class="mt-3 pt-3 border-t border-gray-200">
                <button
                  @click="carregarPendenciasCliente(pendencia.cliente_id)"
                  class="w-full bg-primary-600 text-white px-3 py-2 rounded-md text-sm hover:bg-primary-700 transition-colors"
                >
                  Ver Detalhes
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Versão Desktop: Tabela -->
        <div class="hidden sm:block overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modelo</th>
                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantidade</th>
                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venda</th>
                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="vasilhamesStore.pendencias.length === 0">
                <td colspan="6" class="px-4 sm:px-6 py-4 text-center text-gray-500">
                  Nenhuma pendência encontrada
                </td>
              </tr>
              <tr v-for="pendencia in vasilhamesStore.pendencias" :key="pendencia.id" class="hover:bg-gray-50">
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">
                    {{ (pendencia as any).cliente?.name || `Cliente #${pendencia.cliente_id}` }}
                  </div>
                </td>
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    {{ pendencia.vasilhameModel?.name || `Modelo #${pendencia.vasilhame_model_id}` }}
                  </div>
                </td>
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    {{ pendencia.quantidade_pendente }} / {{ pendencia.quantidade_necessaria }}
                  </div>
                  <div class="text-xs text-gray-500">
                    {{ pendencia.quantidade_entregue }} entregues
                  </div>
                </td>
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    #{{ pendencia.venda_id || 'N/A' }}
                  </div>
                </td>
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'px-2 py-1 text-xs font-medium rounded-full',
                      pendencia.resolvida
                        ? 'bg-green-100 text-green-800'
                        : 'bg-yellow-100 text-yellow-800'
                    ]"
                  >
                    {{ pendencia.resolvida ? 'Resolvida' : 'Pendente' }}
                  </span>
                </td>
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button
                    v-if="!pendencia.resolvida"
                    @click="carregarPendenciasCliente(pendencia.cliente_id)"
                    class="text-primary-600 hover:text-primary-900 mr-3"
                  >
                    Detalhes
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
    <!-- Seção de Modelos Cadastrados -->
    <div v-else-if="abaAtiva === 'modelos'" class="bg-white rounded-lg shadow overflow-hidden">
      <div class="p-6 border-b border-gray-200 flex justify-between items-center">
        <div>
          <h2 class="text-lg font-medium text-gray-900">Modelos de Vasilhames Cadastrados</h2>
          <p class="text-sm text-gray-600 mt-1">Gerencie os modelos de vasilhames disponíveis no sistema</p>
        </div>
        <button 
          @click="abrirModalTipo" 
          class="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 transition-colors"
        >
          Cadastrar Novo Modelo
        </button>
      </div>

      <div v-if="modelosCarregando" class="text-center py-12">
        <svg class="animate-spin h-8 w-8 text-primary-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="mt-2 text-gray-600">Carregando modelos...</p>
      </div>

      <div v-else-if="modelosErro" class="bg-red-50 border border-red-200 rounded-md p-4 m-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-red-800">{{ modelosErro }}</p>
          </div>
        </div>
        <button 
          @click="carregarModelos" 
          class="mt-3 bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors text-sm"
        >
          Tentar Novamente
        </button>
      </div>

      <div v-else>
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preço</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estoque</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="modelo in modelos" :key="modelo.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ modelo.name }}</div>
                <div v-if="modelo.description" class="text-sm text-gray-500">{{ modelo.description }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatarMoeda(modelo.returnable_price || modelo.price) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="['text-sm font-medium', modelo.stock_quantity > 0 ? 'text-green-600' : 'text-red-600']">
                  {{ modelo.stock_quantity }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="['inline-flex px-2 py-1 text-xs font-semibold rounded-full', modelo.available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800']">
                  {{ modelo.available ? 'Ativo' : 'Inativo' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button 
                    @click="editarModelo(modelo)" 
                    class="px-3 py-1 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700"
                  >
                    Editar
                  </button>
                  <button 
                    v-if="modelo.available"
                    @click="confirmarAcao('deactivate', modelo)" 
                    class="px-3 py-1 bg-yellow-600 text-white rounded-md text-sm hover:bg-yellow-700"
                  >
                    Desativar
                  </button>
                  <button 
                    v-else
                    @click="confirmarAcao('activate', modelo)" 
                    class="px-3 py-1 bg-green-600 text-white rounded-md text-sm hover:bg-green-700"
                  >
                    Ativar
                  </button>
                  <button 
                    @click="confirmarAcao('delete', modelo)" 
                    class="px-3 py-1 bg-red-600 text-white rounded-md text-sm hover:bg-red-700"
                  >
                    Excluir
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        
        <div v-if="!modelos || modelos.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <p class="mt-2 text-gray-600">Nenhum modelo de vasilhame cadastrado</p>
          <button 
            @click="abrirModalTipo" 
            class="mt-4 bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 transition-colors"
          >
            Cadastrar Primeiro Modelo
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de Movimentação -->
    <div v-if="modalAberto" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
          <h3 class="text-lg font-medium text-gray-900">
            {{ modalTipo === 'devolucao' ? 'Registrar Devolução' : 'Registrar Nova Saída' }}
          </h3>
          <button @click="fecharModal" class="text-gray-400 hover:text-gray-600">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <div class="mt-4">
          <!-- Seleção de Cliente para Nova Saída -->
          <div v-if="modalTipo === 'saida' && (!clienteSelecionado || clienteSelecionado.id === 0)" class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Selecionar Cliente</label>
            <div class="relative">
              <input 
                v-model="pesquisaCliente"
                @focus="mostrarDropdownClientes = true"
                @blur="esconderDropdownClientes"
                type="text" 
                placeholder="Digite o nome do cliente para pesquisar..."
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
              <div 
                 v-if="mostrarDropdownClientes && clientesFiltradosModal.length > 0" 
                 class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto"
               >
                 <div 
                   v-for="cliente in clientesFiltradosModal" 
                   :key="cliente.id"
                   @click="selecionarCliente(cliente)"
                   class="px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0"
                 >
                   <div class="font-medium text-gray-900">{{ cliente.nome }}</div>
                   <div class="text-sm text-gray-500">{{ cliente.documento }}</div>
                 </div>
               </div>
               <div 
                 v-if="mostrarDropdownClientes && clientesFiltradosModal.length === 0 && pesquisaCliente.length > 0" 
                 class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg"
               >
                 <div class="px-3 py-2 text-gray-500 text-center">
                   Nenhum cliente encontrado
                 </div>
               </div>
            </div>
          </div>
          
          <div v-if="clienteSelecionado && clienteSelecionado.id > 0" class="bg-gray-50 p-4 rounded-md mb-4">
            <h4 class="text-sm font-medium text-gray-900">{{ clienteSelecionado.nome }}</h4>
            <p class="text-sm text-gray-500">Documento: {{ clienteSelecionado.documento }}</p>
            <p v-if="modalTipo === 'devolucao'" class="text-sm text-red-600 font-medium mt-2">
              Débito atual: {{ clienteSelecionado.totalVasilhames }} vasilhames
            </p>
            <p v-if="modalTipo === 'saida'" class="text-sm text-blue-600 font-medium mt-2">
              Cliente selecionado para receber débito de vasilhame
            </p>
          </div>
          
          <div v-if="modalTipo === 'devolucao'" class="space-y-4">
            <!-- Seleção do Modelo -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Modelo a Devolver</label>
              <select 
                v-model="modeloSelecionadoDevolucao" 
                @change="atualizarQuantidadeDevida"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
              >
                <option value="">Selecione um modelo</option>
                <option 
                  v-for="debito in clienteSelecionado?.debitosPorModelo || []" 
                  :key="debito.modelo_id" 
                  :value="debito"
                >
                  {{ debito.modelo_nome }} ({{ debito.quantidade_devida }} unidades)
                </option>
              </select>
              <p v-if="!clienteSelecionado?.debitosPorModelo || clienteSelecionado.debitosPorModelo.length === 0" class="text-sm text-gray-500 mt-1">
                Este cliente não possui débitos de vasilhames.
              </p>
            </div>
            
            <!-- Informações do Modelo Selecionado -->
            <div v-if="modeloSelecionadoDevolucao" class="bg-blue-50 p-3 rounded-md">
              <div class="text-sm">
                <p class="font-medium text-blue-900">{{ modeloSelecionadoDevolucao.modelo_nome }}</p>
                <p class="text-blue-700">Quantidade devida: {{ modeloSelecionadoDevolucao.quantidade_devida }}</p>
                <p class="text-blue-700">Valor unitário: {{ formatarMoeda(modeloSelecionadoDevolucao.modelo_preco) }}</p>
              </div>
            </div>
            
            <!-- Quantidade a Devolver -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Quantidade a Devolver</label>
              <input 
                v-model.number="quantidadeDevolver" 
                type="number" 
                :min="1" 
                :max="modeloSelecionadoDevolucao ? modeloSelecionadoDevolucao.quantidade_devida : 1"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                :disabled="!modeloSelecionadoDevolucao"
              />
              <p v-if="modeloSelecionadoDevolucao" class="text-xs text-gray-500 mt-1">
                Máximo: {{ modeloSelecionadoDevolucao.quantidade_devida }}
              </p>
            </div>
            
            <!-- Observações -->
            <div>
              <label class="block text-sm font-medium text-gray-700">Observações</label>
              <textarea 
                v-model="observacoes" 
                placeholder="Observações sobre a devolução..."
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 h-20"
              ></textarea>
            </div>
          </div>
          
          <div v-else-if="modalTipo === 'saida'" class="space-y-4">
            
            <!-- Seleção do Produto -->
            <div>
              <label class="block text-sm font-medium text-gray-700">Produto</label>
              <select 
                v-model="produtoSelecionado" 
                @change="atualizarValorUnitario" 
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
              >
                <option 
                  v-for="produto in produtosRetornaveis" 
                  :key="produto.id" 
                  :value="produto"
                >
                  {{ produto.name }} - {{ formatarMoeda(produto.returnable_price ?? produto.price) }}
                </option>
              </select>
            </div>
            
            <!-- Quantidade -->
            <div>
              <label class="block text-sm font-medium text-gray-700">Quantidade</label>
              <input 
                v-model.number="quantidade" 
                type="number" 
                :min="1" 
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
            </div>
            
            <!-- Observações -->
            <div>
              <label class="block text-sm font-medium text-gray-700">Observações</label>
              <textarea 
                v-model="observacoes" 
                placeholder="Observações sobre a saída..."
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 h-20"
              ></textarea>
            </div>
          </div>
        </div>
        
        <div class="flex justify-end space-x-3 pt-4 border-t mt-4">
          <button @click="fecharModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
            Cancelar
          </button>
          <button 
            @click="processarMovimentacao" 
            class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="vasilhamesStore.carregando || quantidade <= 0 || (modalTipo === 'saida' && !produtoSelecionado) || (modalTipo === 'saida' && (!clienteSelecionado || clienteSelecionado.id === 0))"
          >
            {{ vasilhamesStore.carregando ? 'Processando...' : 'Confirmar' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de Cadastro de Tipo de Vasilhame -->
    <div v-if="tipoModalAberto" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle w-full max-w-lg">
          <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">Cadastrar Tipo de Vasilhame</h3>

                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Nome do Tipo *</label>
                    <input v-model="tipoForm.nome" type="text" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Ex: Água, Gás, Cerveja, Refrigerante" />
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Removido o campo Preço do Item -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Preço do Vasilhame (Depósito) *</label>
                      <input v-model="tipoForm.precoVasilhame" type="number" min="0" step="0.01" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="0,00" />
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Quantidade Disponível *</label>
                      <input v-model="tipoForm.quantidade" type="number" min="0" step="1" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="0" />
                    </div>
                    <div class="flex items-center mt-6">
                      <input v-model="tipoForm.disponivel" id="tipo-disponivel" type="checkbox" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" />
                      <label for="tipo-disponivel" class="ml-2 block text-sm text-gray-700">Disponível para uso</label>
                    </div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700">Estoque Mínimo (opcional)</label>
                    <input v-model="tipoForm.estoqueMinimo" type="number" min="0" step="1" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="0" />
                  </div>

                  <p v-if="tipoErro" class="text-sm text-red-600">{{ tipoErro }}</p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse">
            <button
              @click="salvarTipo"
              :disabled="tipoSalvando || !podeSalvarTipo"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ tipoSalvando ? 'Salvando...' : 'Salvar' }}
            </button>
            <button
              @click="fecharModalTipo"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 sm:mt-0 sm:w-auto sm:text-sm"
            >
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Edição de Modelo -->
    <div v-if="modalEdicaoAberto" class="modal-edicao">
      <div class="modal-edicao-content">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Editar Modelo</h3>
          
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Nome</label>
              <input 
                v-model="modeloEditando.name" 
                type="text" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
              >
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Preço</label>
              <input 
                v-model="modeloEditando.returnable_price" 
                type="number" 
                step="0.01"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
              >
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Estoque</label>
              <input 
                v-model="modeloEditando.stock_quantity" 
                type="number" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
              >
            </div>
            
            <div>
              <label class="flex items-center">
                <input 
                  v-model="modeloEditando.available" 
                  type="checkbox" 
                  class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
                <span class="ml-2 text-sm text-gray-700">Ativo</span>
              </label>
            </div>
          </div>

          <div class="flex justify-end space-x-3 mt-6">
            <button 
              @click="modalEdicaoAberto = false" 
              class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
            >
              Cancelar
            </button>
            <button 
              @click="salvarModeloEditado" 
              class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700"
            >
              Salvar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmação -->
    <div v-if="modalConfirmacaoAberto" class="modal-confirmacao">
      <div class="modal-confirmacao-content">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            {{ acaoConfirmacao === 'delete' ? 'Confirmar Exclusão' : acaoConfirmacao === 'deactivate' ? 'Confirmar Desativação' : 'Confirmar Ativação' }}
          </h3>
          
          <p class="text-sm text-gray-600 mb-4">
            {{ acaoConfirmacao === 'delete' 
              ? `Tem certeza que deseja excluir o modelo "${modeloSelecionado?.name}"? Esta ação não pode ser desfeita.` 
              : acaoConfirmacao === 'deactivate' 
              ? `Tem certeza que deseja desativar o modelo "${modeloSelecionado?.name}"?`
              : `Tem certeza que deseja ativar o modelo "${modeloSelecionado?.name}"?`
            }}
          </p>

          <div class="flex justify-end space-x-3">
            <button 
              @click="modalConfirmacaoAberto = false" 
              class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
            >
              Cancelar
            </button>
            <button 
              @click="executarAcaoConfirmada" 
              :class="['px-4 py-2 text-white rounded-md', 
                     acaoConfirmacao === 'delete' ? 'bg-red-600 hover:bg-red-700' : 
                     acaoConfirmacao === 'deactivate' ? 'bg-yellow-600 hover:bg-yellow-700' : 
                     'bg-green-600 hover:bg-green-700']"
            >
              {{ acaoConfirmacao === 'delete' ? 'Excluir' : acaoConfirmacao === 'deactivate' ? 'Desativar' : 'Ativar' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Histórico -->
    <div v-if="modalHistoricoAberto" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="fecharModalHistorico">
      <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white" @click.stop>
        <div class="flex justify-between items-center pb-3 border-b">
          <h3 class="text-lg font-medium text-gray-900">
            Histórico de Movimentações - {{ clienteHistorico?.nome }}
          </h3>
          <button @click="fecharModalHistorico" class="text-gray-400 hover:text-gray-600">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <div class="mt-4">
          <!-- Informações do Cliente -->
          <div v-if="clienteHistorico" class="bg-gray-50 p-4 rounded-md mb-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <h4 class="text-sm font-medium text-gray-900">{{ clienteHistorico.nome }}</h4>
                <p class="text-sm text-gray-500">Documento: {{ clienteHistorico.documento }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-700">
                  <span class="font-medium">Débito Total:</span> 
                  {{ clienteHistorico.totalVasilhames }} vasilhames
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-700">
                  <span class="font-medium">Valor do Débito:</span> 
                  R$ {{ clienteHistorico.valorDebito?.toFixed(2) || '0,00' }}
                </p>
              </div>
            </div>
          </div>

          <!-- Loading -->
          <div v-if="carregandoHistorico" class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
            <p class="mt-2 text-gray-600">Carregando histórico...</p>
          </div>

          <!-- Histórico de Movimentações -->
          <div v-else-if="historicoMovimentacoes.length > 0" class="space-y-4 max-h-96 overflow-y-auto">
            <div 
              v-for="movimentacao in historicoMovimentacoes" 
              :key="movimentacao.id"
              class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50"
            >
              <div class="flex justify-between items-start">
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-2">
                    <span 
                      :class="[
                        'px-2 py-1 text-xs font-medium rounded-full',
                        movimentacao.tipo === 'saida' 
                          ? 'bg-red-100 text-red-800' 
                          : 'bg-green-100 text-green-800'
                      ]"
                    >
                      {{ movimentacao.tipo === 'saida' ? 'Saída' : 'Devolução' }}
                    </span>
                    <span class="text-sm text-gray-500">
                      {{ new Date(movimentacao.data_movimentacao).toLocaleDateString('pt-BR') }}
                    </span>
                  </div>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <p class="text-sm">
                        <span class="font-medium">Produto:</span> 
                        {{ movimentacao.produto?.name || 'N/A' }}
                      </p>
                      <p class="text-sm">
                        <span class="font-medium">Quantidade:</span> 
                        {{ movimentacao.quantidade }}
                      </p>
                    </div>
                    <div>
                      <p v-if="movimentacao.usuario" class="text-sm">
                        <span class="font-medium">Usuário:</span> 
                        {{ movimentacao.usuario.name }}
                      </p>
                      <p v-if="movimentacao.pedido_id" class="text-sm">
                        <span class="font-medium">Pedido:</span> 
                        <a 
                          @click.prevent="navegarParaVenda(movimentacao.pedido_id)"
                          href="#"
                          class="text-blue-600 hover:text-blue-800 underline font-medium cursor-pointer ml-1"
                        >
                          #{{ movimentacao.pedido_id }}
                        </a>
                      </p>
                    </div>
                  </div>
                  
                  <p v-if="movimentacao.observacoes" class="text-sm text-gray-600 mt-2">
                    <span class="font-medium">Observações:</span> 
                    {{ movimentacao.observacoes }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Sem movimentações -->
          <div v-else class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="mt-2 text-gray-600">Nenhuma movimentação encontrada</p>
          </div>
        </div>

        <div class="flex justify-end mt-6">
          <button 
            @click="fecharModalHistorico" 
            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
          >
            Fechar
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useVasilhamesStore } from '../stores/vasilhames'
import { useProductsStore } from '@/stores/products'
import { useCustomersStore } from '@/stores/customers'
import AppLayout from '@/components/AppLayout.vue'
import api from '@/services/api'

const router = useRouter()
const vasilhamesStore = useVasilhamesStore()
const productsStore = useProductsStore()
const customersStore = useCustomersStore()

// Estados
const abaAtiva = ref('clientes')
const filtroSelecionado = ref('todos')
const termoBusca = ref('')
const modalAberto = ref(false)
const modalTipo = ref<'devolucao' | 'saida'>('devolucao')
const clienteSelecionado = ref<any>(null)
const quantidade = ref(1)
const valorUnitario = ref(0)
const produtoSelecionado = ref<any>(null)
const observacoes = ref('')
const produtosRetornaveis = ref<any[]>([])

// Estados para pesquisa de cliente no modal
const pesquisaCliente = ref('')
const mostrarDropdownClientes = ref(false)

// Estados para devolução por modelo
const modeloSelecionadoDevolucao = ref<any>(null)
const quantidadeDevolver = ref(1)

// Estado para histórico
const modalHistoricoAberto = ref(false)
const clienteHistorico = ref<any>(null)
const historicoMovimentacoes = ref<any[]>([])
const carregandoHistorico = ref(false)

// Estado para pendências
const filtroPendencias = ref({
  resolvida: false,
  cliente_id: null as number | null,
  modelo_id: null as number | null
})
const clienteSelecionadoPendencias = ref<number | null>(null)
const clientePendencias = ref<any>(null)
const pendenciasDoCliente = computed(() => {
  if (!clienteSelecionadoPendencias.value) return []
  return vasilhamesStore.pendenciasPorCliente[clienteSelecionadoPendencias.value] || []
})

watch(abaAtiva, (newAba) => {
  if (newAba === 'modelos') {
    carregarModelos()
  } else if (newAba === 'pendencias') {
    carregarPendencias()
  }
})

// Métodos para pendências
const carregarPendencias = async () => {
  try {
    await vasilhamesStore.carregarPendencias(filtroPendencias.value)
  } catch (error) {
    console.error('Erro ao carregar pendências:', error)
  }
}

const carregarPendenciasCliente = async (clienteId: number) => {
  try {
    clienteSelecionadoPendencias.value = clienteId
    const response = await vasilhamesStore.carregarPendenciasPorCliente(clienteId)
    // Store the client information from the response
    clientePendencias.value = response?.cliente || null
  } catch (error) {
    console.error('Erro ao carregar pendências do cliente:', error)
  }
}

const resolverPendencia = async (pendenciaId: number, quantidadeDevolvida: number, observacao?: string) => {
  try {
    await vasilhamesStore.resolverPendencia(pendenciaId, quantidadeDevolvida, observacao)
    
    // Recarregar pendências do cliente se estiver visualizando
    if (clienteSelecionadoPendencias.value) {
      await carregarPendenciasCliente(clienteSelecionadoPendencias.value)
    }
    
    // Recarregar lista geral de pendências
    await carregarPendencias()
    
    // Recarregar lista de clientes para atualizar débitos
    await buscarClientesVasilhames()
  } catch (error) {
    console.error('Erro ao resolver pendência:', error)
  }
}

// Function to clear pendências data when closing
const limparPendencias = () => {
  clienteSelecionadoPendencias.value = null
  clientePendencias.value = null
}

// Estado do modal de tipos
const tipoModalAberto = ref(false)
const tipoSalvando = ref(false)
const tipoErro = ref<string | null>(null)
const tipoForm = ref({
  nome: '',
  precoVasilhame: '',
  quantidade: '',
  disponivel: true,
  estoqueMinimo: ''
})

// Estado para modelos
const modelosCarregando = ref(false)
const modelosErro = ref<string | null>(null)
const modelos = ref<any[]>([])
const modeloEditando = ref<any>(null)
const modalEdicaoAberto = ref(false)
const modalConfirmacaoAberto = ref(false)
const acaoConfirmacao = ref<'delete' | 'deactivate' | 'activate'>('delete')
const modeloSelecionado = ref<any>(null)

const podeSalvarTipo = computed(() => {
  const nomeOk = tipoForm.value.nome.trim().length > 0
  const precoVasilhameOk = parseFloat(tipoForm.value.precoVasilhame) >= 0
  const qntOk = Number.isInteger(Number(tipoForm.value.quantidade)) && parseInt(tipoForm.value.quantidade) >= 0
  return nomeOk && precoVasilhameOk && qntOk
})

// Computed
const clientesFiltrados = computed(() => {
  let filtrados = vasilhamesStore.clientes
  
  // Aplicar filtro
  if (filtroSelecionado.value === 'semDebito') {
    // Para o filtro "sem débito", mostrar todos os clientes sem débito
    filtrados = filtrados.filter(cliente => cliente.totalVasilhames === 0)
  } else if (filtroSelecionado.value === 'comDebito') {
    // Para o filtro "com débito", mostrar apenas clientes com débito
    filtrados = filtrados.filter(cliente => cliente.totalVasilhames > 0)
  }
  // Para "todos" ou padrão, mostrar todos os clientes
  
  // Aplicar busca
  if (termoBusca.value) {
    const busca = termoBusca.value.toLowerCase()
    filtrados = filtrados.filter(cliente => 
      cliente.nome.toLowerCase().includes(busca) ||
      cliente.documento.toLowerCase().includes(busca)
    )
  }
  
  return filtrados
})

// Computed para filtrar clientes no modal de pesquisa
const clientesFiltradosModal = computed(() => {
  if (!pesquisaCliente.value) {
    return vasilhamesStore.clientes.slice(0, 10) // Mostrar apenas os primeiros 10 quando não há pesquisa
  }
  
  const busca = pesquisaCliente.value.toLowerCase()
  return vasilhamesStore.clientes.filter(cliente => 
    cliente.nome.toLowerCase().includes(busca) ||
    cliente.documento.toLowerCase().includes(busca)
  ).slice(0, 20) // Limitar a 20 resultados para performance
})

const estatisticas = computed(() => {
  const totalClientes = vasilhamesStore.clientes.length
  const clientesComDebito = vasilhamesStore.clientes.filter(c => c.totalVasilhames > 0).length
  const totalVasilhames = vasilhamesStore.clientes.reduce((sum, c) => sum + c.totalVasilhames, 0)
  
  return {
    totalClientes,
    clientesComDebito,
    totalVasilhames,
    clientesSemDebito: totalClientes - clientesComDebito
  }
})

// Métodos
const selecionarCliente = (cliente: any) => {
  clienteSelecionado.value = cliente
  pesquisaCliente.value = cliente.nome
  mostrarDropdownClientes.value = false
}

const esconderDropdownClientes = () => {
  setTimeout(() => {
    mostrarDropdownClientes.value = false
  }, 200)
}

const abrirModal = async (tipo: 'devolucao' | 'saida', cliente: any) => {
  modalTipo.value = tipo
  clienteSelecionado.value = cliente
  quantidade.value = tipo === 'devolucao' ? Math.min(cliente.totalVasilhames, 1) : 1
  observacoes.value = ''
  
  // Resetar campos de pesquisa para Nova Saída
  if (tipo === 'saida') {
    pesquisaCliente.value = ''
    mostrarDropdownClientes.value = false
    clienteSelecionado.value = { id: 0, nome: 'Novo Cliente', documento: '', totalVasilhames: 0, valorTotal: 0 }
  }
  
  // Resetar campos específicos de devolução por modelo
  if (tipo === 'devolucao') {
    modeloSelecionadoDevolucao.value = null
    quantidadeDevolver.value = 1
  }
  
  // Carregar modelos de vasilhames se for saída
  if (tipo === 'saida') {
    try {
      await vasilhamesStore.carregarModelos()
      produtosRetornaveis.value = vasilhamesStore.modelos
      if (produtosRetornaveis.value.length > 0) {
        produtoSelecionado.value = produtosRetornaveis.value[0]
        valorUnitario.value = produtoSelecionado.value.returnable_price || 0
      }
    } catch (error) {
      console.error('Erro ao carregar modelos:', error)
    }
  }
  
  modalAberto.value = true
}

const fecharModal = () => {
  modalAberto.value = false
  clienteSelecionado.value = null
  produtoSelecionado.value = null
  produtosRetornaveis.value = []
  
  // Limpar campos de pesquisa
  pesquisaCliente.value = ''
  mostrarDropdownClientes.value = false
  
  // Limpar dados de devolução por modelo
  modeloSelecionadoDevolucao.value = null
  quantidadeDevolver.value = 1
}

const processarMovimentacao = async () => {
  // Usar sempre o cliente selecionado (que já vem pré-selecionado)
  const cliente = clienteSelecionado.value
  
  if (!cliente) {
    alert('Selecione um cliente')
    return
  }
  
  try {
    if (modalTipo.value === 'devolucao') {
      // Validar se modelo foi selecionado
      if (!modeloSelecionadoDevolucao.value) {
        alert('Selecione um modelo para devolver')
        return
      }
      
      if (!quantidadeDevolver.value || quantidadeDevolver.value <= 0) {
        alert('Informe uma quantidade válida para devolver')
        return
      }
      
      if (quantidadeDevolver.value > modeloSelecionadoDevolucao.value.quantidade_devida) {
        alert('Quantidade a devolver não pode ser maior que a quantidade devida')
        return
      }
      
      // Registrar devolução diretamente com o modelo de vasilhame
      await vasilhamesStore.registrarDevolucao(
        cliente.id,
        modeloSelecionadoDevolucao.value.modelo_id, // Usar o ID do modelo de vasilhame
        quantidadeDevolver.value,
        observacoes.value
      )
    } else {
      if (!produtoSelecionado.value) {
        throw new Error('Selecione um produto')
      }
      await vasilhamesStore.registrarSaida(
        cliente.id,
        produtoSelecionado.value.id,
        quantidade.value,
        observacoes.value
      )
    }
    
    fecharModal()
    await buscarClientesVasilhames() // Recarregar lista de clientes
  } catch (error) {
    console.error('Erro ao processar movimentação:', error)
  }
}

// Método para atualizar quantidade devida quando modelo é selecionado
const atualizarQuantidadeDevida = () => {
  if (modeloSelecionadoDevolucao.value) {
    quantidadeDevolver.value = 1
  }
}

const abrirModalTipo = () => {
  tipoErro.value = null
  tipoForm.value = {
    nome: '',
    precoVasilhame: '',
    quantidade: '',
    disponivel: true,
    estoqueMinimo: ''
  }
  tipoModalAberto.value = true
}

const fecharModalTipo = () => {
  tipoModalAberto.value = false
}

const salvarTipo = async () => {
  try {
    tipoSalvando.value = true
    tipoErro.value = null

    // Validar campos obrigatórios
    if (!tipoForm.value.nome?.trim()) {
      throw new Error('Nome do tipo é obrigatório')
    }
    if (!tipoForm.value.precoVasilhame || parseFloat(tipoForm.value.precoVasilhame) <= 0) {
      throw new Error('Preço do vasilhame deve ser maior que zero')
    }
    if (!tipoForm.value.quantidade || parseInt(tipoForm.value.quantidade) <= 0) {
      throw new Error('Quantidade deve ser maior que zero')
    }

    // Criar payload para o novo modelo de vasilhame
    const payload = {
      name: tipoForm.value.nome.trim(),
      description: '',
      returnable_price: parseFloat(tipoForm.value.precoVasilhame) || 0,
      returnable_quantity: parseInt(tipoForm.value.quantidade) || 0,
      stock_quantity: parseInt(tipoForm.value.quantidade) || 0,
      minimum_stock: tipoForm.value.estoqueMinimo ? parseInt(tipoForm.value.estoqueMinimo) : 0,
      available: !!tipoForm.value.disponivel
    }

    // Usar o novo store de vasilhames para criar o modelo
    const modelo = await vasilhamesStore.criarModelo(payload)

    // Atualizar lista de modelos
    await carregarModelos()

    // Selecionar automaticamente o novo item criado e ajustar o valor unitário somente se o modal de saída estiver aberto
    if (modalAberto.value && modalTipo.value === 'saida') {
      const novo = modelos.value.find((p: any) => p.id === modelo.id)
      if (novo) {
        produtoSelecionado.value = novo
        valorUnitario.value = novo.returnable_price || 0
      }
    }

    tipoModalAberto.value = false
  } catch (e: any) {
    tipoErro.value = e?.message || 'Erro ao salvar tipo'
    console.error('Erro ao salvar tipo:', e)
  } finally {
    tipoSalvando.value = false
  }
}

const verDetalhes = (cliente: any) => {
  // Navegar para detalhes do cliente
  console.log('Ver detalhes do cliente:', cliente)
}

const buscarClientesVasilhames = async () => {
  try {
    await vasilhamesStore.carregarClientes(filtroSelecionado.value, termoBusca.value)
  } catch (error) {
    console.error('Erro ao buscar clientes:', error)
  }
}

// Métodos para modelos de vasilhames
const carregarModelos = async () => {
  try {
    modelosCarregando.value = true
    modelosErro.value = null
    // Usar o novo store de vasilhames para carregar modelos
    await vasilhamesStore.carregarModelos()
    modelos.value = vasilhamesStore.modelos
  } catch (error: any) {
    modelosErro.value = 'Erro ao carregar modelos: ' + (error?.message || '')
    console.error('Erro ao carregar modelos:', error)
  } finally {
    modelosCarregando.value = false
  }
}

const editarModelo = (modelo: any) => {
  modeloEditando.value = { ...modelo }
  modalEdicaoAberto.value = true
}

const confirmarAcao = (acao: 'delete' | 'deactivate' | 'activate', modelo: any) => {
  modeloSelecionado.value = modelo
  acaoConfirmacao.value = acao
  modalConfirmacaoAberto.value = true
}

const executarAcaoConfirmada = async () => {
  try {
    switch (acaoConfirmacao.value) {
      case 'delete':
        await vasilhamesStore.excluirModelo(modeloSelecionado.value.id)
        break
      case 'deactivate':
        await vasilhamesStore.atualizarModelo(modeloSelecionado.value.id, { 
          ...modeloSelecionado.value, 
          available: false 
        })
        break
      case 'activate':
        await vasilhamesStore.atualizarModelo(modeloSelecionado.value.id, { 
          ...modeloSelecionado.value, 
          available: true 
        })
        break
    }
    await carregarModelos()
    modalConfirmacaoAberto.value = false
  } catch (error) {
    console.error('Erro ao executar ação:', error)
  }
}

const salvarModeloEditado = async () => {
  try {
    await vasilhamesStore.atualizarModelo(modeloEditando.value.id, modeloEditando.value)
    await carregarModelos()
    modalEdicaoAberto.value = false
  } catch (error) {
    console.error('Erro ao salvar modelo:', error)
  }
}

const atualizarValorUnitario = () => {
  if (produtoSelecionado.value) {
    valorUnitario.value = produtoSelecionado.value.returnable_price || 0
  }
}

const formatarMoeda = (valor: number) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(valor)
}

const formatarData = (data: string | null) => {
  if (!data) return 'Nunca'
  return new Date(data).toLocaleDateString('pt-BR')
}

// Método para obter quantidade de um modelo específico para um cliente
const getQuantidadeModelo = (cliente: any, modeloId: number) => {
  if (!cliente.debitosPorModelo || !Array.isArray(cliente.debitosPorModelo)) {
    return 0
  }
  
  const debito = cliente.debitosPorModelo.find((d: any) => d.modelo_id === modeloId)
  return debito ? debito.quantidade_devida : 0
}

// Método para ver histórico de movimentações
const verHistorico = async (cliente: any) => {
  clienteHistorico.value = cliente
  modalHistoricoAberto.value = true
  carregandoHistorico.value = true
  
  try {
    const response = await api.get(`/vasilhames/cliente/${cliente.id}/historico`)
    
    if (response.status === 200) {
      historicoMovimentacoes.value = response.data.historico || []
    } else {
      console.error('Erro ao carregar histórico:', response.data.error)
      historicoMovimentacoes.value = []
    }
  } catch (error) {
    console.error('Erro ao buscar histórico:', error)
    historicoMovimentacoes.value = []
  } finally {
    carregandoHistorico.value = false
  }
}

const fecharModalHistorico = () => {
  modalHistoricoAberto.value = false
  clienteHistorico.value = null
  historicoMovimentacoes.value = []
}

// Função para navegar para a visualização da venda
const navegarParaVenda = (pedidoId: number) => {
  console.log('navegarParaVenda chamada com pedidoId:', pedidoId)
  try {
    // Fechar o modal primeiro
    fecharModalHistorico()
    // Aguardar um pouco antes de navegar
    setTimeout(() => {
      router.push(`/orders/${pedidoId}`)
      console.log('Navegação executada com sucesso')
    }, 100)
  } catch (error) {
    console.error('Erro na navegação:', error)
  }
}

// Lifecycle
onMounted(async () => {
  await buscarClientesVasilhames()
  await carregarModelos()
})
</script>

<style scoped>
.vasilhames-container {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.page-header h1 {
  color: #2c3e50;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 10px;
}

.stats-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  display: flex;
  align-items: center;
  gap: 15px;
}

.stat-icon {
  width: 50px;
  height: 50px;
  background: #3498db;
  border-radius: 50%;
}

  /* Modal de Edição de Modelo */
  .modal-edicao {
    position: fixed;
    inset: 0;
    background-color: rgba(107, 114, 128, 0.5);
    overflow-y: auto;
    z-index: 50;
  }

  .modal-edicao-content {
    position: relative;
    top: 5rem;
    margin: 0 auto;
    padding: 1.25rem;
    border: 1px solid #e5e7eb;
    width: 24rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    border-radius: 0.375rem;
    background-color: white;
  }

  /* Modal de Confirmação */
  .modal-confirmacao {
    position: fixed;
    inset: 0;
    background-color: rgba(107, 114, 128, 0.5);
    overflow-y: auto;
    z-index: 50;
  }

  .modal-confirmacao-content {
    position: relative;
    top: 5rem;
    margin: 0 auto;
    padding: 1.25rem;
    border: 1px solid #e5e7eb;
    width: 24rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    border-radius: 0.375rem;
    background-color: white;
  }
  /* Modal de Confirmação */
  .modal-confirmacao {
    position: fixed;
    inset: 0;
    background-color: rgba(107, 114, 128, 0.5);
    overflow-y: auto;
    z-index: 50;
  }

  .modal-confirmacao-content {
    position: relative;
    top: 5rem;
    margin: 0 auto;
    padding: 1.25rem;
    border: 1px solid #e5e7eb;
    width: 24rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    border-radius: 0.375rem;
    background-color: white;
  }
</style>