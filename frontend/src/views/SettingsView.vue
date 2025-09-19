<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-white shadow-sm rounded-lg p-6">
        <h1 class="text-2xl font-bold text-gray-900 flex items-center">
          <svg class="w-6 h-6 mr-3 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
          Configurações da Loja
        </h1>
        <p class="text-gray-600 mt-1">Gerencie as configurações e informações da sua loja</p>
      </div>

      <!-- Configurações da Loja -->
      <div class="bg-white shadow-sm rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
          <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
          </svg>
          Informações da Loja
        </h2>

        <form @submit.prevent="saveAllSettings">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Informações Básicas -->
            <div class="space-y-4">
              <h3 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">Dados Básicos</h3>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nome da Loja</label>
                <input
                  v-model="storeSettings.name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">CNPJ</label>
                <input
                  v-model="storeSettings.cnpj"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
                <input
                  v-model="storeSettings.phone"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input
                  v-model="storeSettings.email"
                  type="email"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                />
              </div>
            </div>

            <!-- Endereço -->
            <div class="space-y-4">
              <h3 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">Endereço</h3>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Endereço</label>
                <input
                  v-model="storeSettings.address"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Cidade</label>
                  <input
                    v-model="storeSettings.city"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                  <input
                    v-model="storeSettings.state"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                  />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">CEP</label>
                <input
                  v-model="storeSettings.zip_code"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                />
              </div>
            </div>
          </div>
        </form>
      </div>

      <!-- Configurações de Vendas -->
      <div class="bg-white shadow-sm rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
          <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
          </svg>
          Configurações de Vendas
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Configurações de Crédito -->
          <div class="space-y-4">
            <h3 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">Vendas a Prazo</h3>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Prazo Padrão (dias)</label>
              <input
                v-model="salesSettings.default_payment_term"
                type="number"
                min="1"
                max="365"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
              />
              <p class="text-xs text-gray-500 mt-1">Prazo padrão para vencimento de vendas a prazo</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Limite de Crédito Padrão (R$)</label>
              <input
                v-model="salesSettings.default_credit_limit"
                type="number"
                step="0.01"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
              />
              <p class="text-xs text-gray-500 mt-1">Limite de crédito aplicado automaticamente a novos clientes</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Percentual de Alerta (%)</label>
              <input
                v-model="salesSettings.credit_alert_percentage"
                type="number"
                min="50"
                max="100"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
              />
              <p class="text-xs text-gray-500 mt-1">Percentual de uso do crédito para exibir alertas (recomendado: 80%)</p>
            </div>

            <div class="flex items-center">
              <input
                v-model="salesSettings.auto_send_reminders"
                type="checkbox"
                class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
              />
              <label class="ml-2 text-sm text-gray-700">Enviar lembretes automáticos por WhatsApp</label>
            </div>
          </div>

          <!-- Configurações de Impressão -->
          <div class="space-y-4">
            <h3 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">Impressão e Comunicação</h3>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Mensagem do Rodapé</label>
              <textarea
                v-model="storeSettings.receipt_footer"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                placeholder="Obrigado pela preferência! Volte sempre!"
              ></textarea>
              <p class="text-xs text-gray-500 mt-1">Mensagem exibida no rodapé dos comprovantes</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">WhatsApp da Loja</label>
              <input
                v-model="storeSettings.whatsapp"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                placeholder="(11) 99999-9999"
              />
              <p class="text-xs text-gray-500 mt-1">Número usado para comunicação com clientes</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Modelo de Impressão</label>
              <select
                v-model="storeSettings.print_template"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
              >
                <option value="80mm">80mm (Impressora Térmica Padrão)</option>
                <option value="58mm">58mm (Impressora Térmica Pequena)</option>
                <option value="a4">A4 (Impressora Laser/Jato de Tinta)</option>
              </select>
              <p class="text-xs text-gray-500 mt-1">Modelo de comprovante baseado no tipo de impressora</p>
            </div>

            <div class="flex items-center">
              <input
                v-model="systemSettings.auto_print_receipts"
                type="checkbox"
                class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
              />
              <label class="ml-2 text-sm text-gray-700">Imprimir comprovantes automaticamente</label>
            </div>
          </div>
        </div>

        <!-- Botões de Ação -->
        <div class="mt-8 flex justify-end space-x-3">
          <button
            type="button"
            @click="resetSettings"
            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-3 rounded-lg font-medium"
          >
            Restaurar Padrões
          </button>
          <button
            type="submit"
            @click="saveAllSettings"
            :disabled="saving"
            class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50"
          >
            <svg v-if="saving" class="animate-spin h-4 w-4 inline mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ saving ? 'Salvando...' : 'Salvar Configurações' }}
          </button>
        </div>
      </div>

      <!-- Configurações de WhatsApp -->
      <div class="bg-white shadow-sm rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
          <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
          </svg>
          Configurações do WhatsApp
        </h2>

        <div class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Mensagem Personalizada para Finalização de Vendas</label>
            <textarea
              v-model="storeSettings.whatsapp_message_template"
              rows="8"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
              placeholder="🛒 *CLICKVENDA - Comprovante de Venda*

📋 *Pedido:* #{PEDIDO_ID}
📅 *Data:* {DATA}
🕒 *Hora:* {HORA}

👤 *Cliente:* {CLIENTE_NOME}

📦 *Itens:*
{ITENS_LISTA}

💰 *Total: R$ {TOTAL}*

💳 *Pagamento:*
{FORMAS_PAGAMENTO}

✅ *Status:* {STATUS}

Obrigado pela preferência! 🙏

💡 Dica: Pressione Enter para quebrar linhas e use *texto* para negrito"
            ></textarea>
            <div class="mt-2 text-xs text-gray-500">
              <p class="font-medium mb-2">Variáveis disponíveis:</p>
              <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                <div><code class="bg-gray-100 px-1 rounded">{PEDIDO_ID}</code> - Número do pedido</div>
                <div><code class="bg-gray-100 px-1 rounded">{DATA}</code> - Data da venda</div>
                <div><code class="bg-gray-100 px-1 rounded">{HORA}</code> - Hora da venda</div>
                <div><code class="bg-gray-100 px-1 rounded">{CLIENTE_NOME}</code> - Nome do cliente</div>
                <div><code class="bg-gray-100 px-1 rounded">{ITENS_LISTA}</code> - Lista de itens</div>
                <div><code class="bg-gray-100 px-1 rounded">{TOTAL}</code> - Valor total</div>
                <div><code class="bg-gray-100 px-1 rounded">{FORMAS_PAGAMENTO}</code> - Formas de pagamento</div>
                <div><code class="bg-gray-100 px-1 rounded">{STATUS}</code> - Status da venda</div>
              </div>
              
              <div class="bg-blue-50 border border-blue-200 rounded p-2">
                <p class="font-medium text-blue-900 mb-1">💡 Dicas de formatação:</p>
                <div class="text-blue-800 space-y-1">
                  <div><strong>Quebra de linha:</strong> Pressione <kbd class="bg-blue-200 px-1 rounded text-xs">Enter</kbd> para criar uma nova linha</div>
                  <div><strong>Texto em negrito:</strong> Use <code class="bg-blue-200 px-1 rounded text-xs">*texto*</code> para <strong>negrito</strong></div>
                  <div><strong>Texto em itálico:</strong> Use <code class="bg-blue-200 px-1 rounded text-xs">_texto_</code> para <em>itálico</em></div>
                  <div><strong>Texto riscado:</strong> Use <code class="bg-blue-200 px-1 rounded text-xs">~texto~</code> para <del>riscado</del></div>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <h4 class="text-sm font-medium text-green-900 mb-2">Preview da Mensagem</h4>
            <div class="bg-white rounded border border-green-200 p-3 text-sm font-mono whitespace-pre-line">{{ whatsappPreview }}</div>
          </div>
        </div>
      </div>

      <!-- Configurações de Sistema -->
      <div class="bg-white shadow-sm rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
          <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
          </svg>
          Configurações do Sistema
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-4">
            <h3 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">Segurança</h3>
            
            <div class="flex items-center">
              <input
                v-model="systemSettings.require_customer_for_credit"
                type="checkbox"
                class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
              />
              <label class="ml-2 text-sm text-gray-700">Exigir cliente identificado para vendas a prazo</label>
            </div>

            <div class="flex items-center">
              <input
                v-model="systemSettings.block_negative_stock"
                type="checkbox"
                class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
              />
              <label class="ml-2 text-sm text-gray-700">Bloquear vendas com estoque negativo</label>
            </div>

            <div class="flex items-center">
              <input
                v-model="systemSettings.auto_backup"
                type="checkbox"
                class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
              />
              <label class="ml-2 text-sm text-gray-700">Backup automático diário</label>
            </div>
          </div>

          <div class="space-y-4">
            <h3 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">Interface</h3>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Tema da Interface</label>
              <select
                v-model="systemSettings.theme"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
              >
                <option value="light">Claro</option>
                <option value="dark">Escuro</option>
                <option value="auto">Automático</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Idioma</label>
              <select
                v-model="systemSettings.language"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
              >
                <option value="pt-BR">Português (Brasil)</option>
                <option value="en-US">English (US)</option>
                <option value="es-ES">Español</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Itens por Página</label>
              <select
                v-model="systemSettings.items_per_page"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
              >
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Preview das Configurações -->
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-4">Preview das Configurações</h3>
        
        <div class="bg-white rounded-lg p-4 border border-gray-200">
          <div class="text-center mb-4">
            <h4 class="text-lg font-bold">{{ storeSettings.name || 'Nome da Loja' }}</h4>
            <p class="text-sm text-gray-600">{{ storeSettings.cnpj || 'CNPJ não informado' }}</p>
          </div>
          
          <div class="text-sm space-y-1">
            <p><strong>Endereço:</strong> {{ storeSettings.address || 'Não informado' }}</p>
            <p><strong>Cidade:</strong> {{ storeSettings.city || 'Não informado' }}, {{ storeSettings.state || 'UF' }}</p>
            <p><strong>CEP:</strong> {{ storeSettings.zip_code || 'Não informado' }}</p>
            <p><strong>Telefone:</strong> {{ storeSettings.phone || 'Não informado' }}</p>
            <p><strong>Email:</strong> {{ storeSettings.email || 'Não informado' }}</p>
          </div>
          
          <div class="mt-4 pt-4 border-t border-gray-200 text-center">
            <p class="text-xs text-gray-500">{{ storeSettings.receipt_footer || 'Obrigado pela preferência!' }}</p>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
          <div>
            <strong>Prazo padrão para vendas a prazo:</strong> {{ salesSettings.default_payment_term }} dias
          </div>
          <div>
            <strong>Limite de crédito padrão:</strong> R$ {{ formatPrice(salesSettings.default_credit_limit) }}
          </div>
        </div>
      </div>

      <!-- Preview dos Modelos de Impressão -->
      <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Preview dos Modelos de Impressão</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Modelo 80mm -->
          <div class="bg-white rounded-lg border border-gray-200 p-4" :class="{ 'ring-2 ring-primary-500': storeSettings.print_template === '80mm' }">
            <h4 class="font-semibold text-gray-900 mb-2 text-center">80mm (Térmica Padrão)</h4>
            <div class="text-xs font-mono bg-gray-100 p-2 rounded text-center" style="width: 80mm; max-width: 100%; margin: 0 auto;">
              <div class="font-bold text-center">{{ storeSettings.name || 'LOJA' }}</div>
              <div class="text-center">{{ storeSettings.cnpj || 'CNPJ' }}</div>
              <div class="border-t my-1"></div>
              <div>Pedido: #12345</div>
              <div>Data: 15/12/2024</div>
              <div>Cliente: João Silva</div>
              <div class="border-t my-1"></div>
              <div>Produto A x1 R$ 10,00</div>
              <div>Produto B x2 R$ 20,00</div>
              <div class="border-t my-1"></div>
              <div class="font-bold">Total: R$ 30,00</div>
              <div class="text-center mt-2">{{ storeSettings.receipt_footer || 'Obrigado!' }}</div>
            </div>
          </div>

          <!-- Modelo 58mm -->
          <div class="bg-white rounded-lg border border-gray-200 p-4" :class="{ 'ring-2 ring-primary-500': storeSettings.print_template === '58mm' }">
            <h4 class="font-semibold text-gray-900 mb-2 text-center">58mm (Térmica Pequena)</h4>
            <div class="text-xs font-mono bg-gray-100 p-2 rounded text-center" style="width: 58mm; max-width: 100%; margin: 0 auto;">
              <div class="font-bold text-center text-xs">{{ storeSettings.name || 'LOJA' }}</div>
              <div class="text-center text-xs">{{ storeSettings.cnpj || 'CNPJ' }}</div>
              <div class="border-t my-1"></div>
              <div class="text-xs">#12345 - 15/12/24</div>
              <div class="text-xs">João Silva</div>
              <div class="border-t my-1"></div>
              <div class="text-xs">Produto A x1 R$10</div>
              <div class="text-xs">Produto B x2 R$20</div>
              <div class="border-t my-1"></div>
              <div class="font-bold text-xs">Total: R$30</div>
              <div class="text-center mt-1 text-xs">{{ storeSettings.receipt_footer || 'Obrigado!' }}</div>
            </div>
          </div>

          <!-- Modelo A4 -->
          <div class="bg-white rounded-lg border border-gray-200 p-4" :class="{ 'ring-2 ring-primary-500': storeSettings.print_template === 'a4' }">
            <h4 class="font-semibold text-gray-900 mb-2 text-center">A4 (Laser/Jato de Tinta)</h4>
            <div class="text-sm font-mono bg-gray-100 p-4 rounded" style="max-width: 210mm;">
              <div class="text-center border-b pb-2 mb-4">
                <div class="text-xl font-bold">{{ storeSettings.name || 'NOME DA LOJA' }}</div>
                <div class="text-sm">{{ storeSettings.cnpj || 'CNPJ não informado' }}</div>
                <div class="text-sm">{{ storeSettings.address || 'Endereço não informado' }}</div>
                <div class="text-sm">{{ storeSettings.city || 'Cidade' }}, {{ storeSettings.state || 'UF' }}</div>
              </div>
              
              <div class="mb-4">
                <div class="flex justify-between">
                  <span>Pedido: #12345</span>
                  <span>Data: 15/12/2024 14:30</span>
                </div>
                <div>Cliente: João Silva</div>
              </div>
              
              <div class="border-t pt-2 mb-4">
                <div class="flex justify-between font-semibold">
                  <span>Item</span>
                  <span>Qtd</span>
                  <span>Valor</span>
                  <span>Total</span>
                </div>
                <div class="flex justify-between">
                  <span>Produto A</span>
                  <span>1</span>
                  <span>R$ 10,00</span>
                  <span>R$ 10,00</span>
                </div>
                <div class="flex justify-between">
                  <span>Produto B</span>
                  <span>2</span>
                  <span>R$ 10,00</span>
                  <span>R$ 20,00</span>
                </div>
              </div>
              
              <div class="border-t pt-2 text-center">
                <div class="text-lg font-bold">Total: R$ 30,00</div>
                <div class="text-sm mt-2">{{ storeSettings.receipt_footer || 'Obrigado pela preferência!' }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useSettingsStore } from '@/stores/settings'
import AppLayout from '@/components/AppLayout.vue'

const settingsStore = useSettingsStore()
const saving = ref(false)

// Usar as configurações do store
const storeSettings = settingsStore.storeSettings
const salesSettings = settingsStore.salesSettings
const systemSettings = settingsStore.systemSettings

const formatPrice = (price: any) => {
  const numPrice = parseFloat(price) || 0
  return numPrice.toFixed(2).replace('.', ',')
}

// Preview da mensagem do WhatsApp
const whatsappPreview = computed(() => {
  const template = storeSettings.whatsapp_message_template || ''
  
  return template
    .replace('{PEDIDO_ID}', '#12345')
    .replace('{DATA}', '15/12/2024')
    .replace('{HORA}', '14:30')
    .replace('{CLIENTE_NOME}', 'João Silva')
    .replace('{ITENS_LISTA}', '• Produto A\n  1 x R$ 10,00 = R$ 10,00\n• Produto B\n  2 x R$ 10,00 = R$ 20,00')
    .replace('{TOTAL}', '30,00')
    .replace('{FORMAS_PAGAMENTO}', '• Dinheiro: R$ 30,00')
    .replace('{STATUS}', 'Finalizada')
})

const saveAllSettings = async () => {
  saving.value = true
  try {
    await settingsStore.saveAllSettings()
    alert('Todas as configurações foram salvas com sucesso!')
  } catch (error) {
    console.error('Erro ao salvar configurações:', error)
    alert('Erro ao salvar configurações. Tente novamente.')
  } finally {
    saving.value = false
  }
}

const resetSettings = () => {
  if (confirm('Tem certeza que deseja restaurar as configurações padrão? Esta ação não pode ser desfeita.')) {
    // Resetar para valores padrão
    Object.assign(storeSettings, {
      name: 'ClickVenda',
      cnpj: '',
      phone: '',
      email: '',
      address: '',
      city: '',
      state: '',
      zip_code: '',
      receipt_footer: 'Obrigado pela preferência! Volte sempre!',
      whatsapp: '',
      print_template: '80mm',
      whatsapp_message_template: `🛒 *CLICKVENDA - Comprovante de Venda*

📋 *Pedido:* #{PEDIDO_ID}
📅 *Data:* {DATA}
🕒 *Hora:* {HORA}

👤 *Cliente:* {CLIENTE_NOME}

📦 *Itens:*
{ITENS_LISTA}

💰 *Total: R$ {TOTAL}*

💳 *Pagamento:*
{FORMAS_PAGAMENTO}

✅ *Status:* {STATUS}

Obrigado pela preferência! 🙏`
    })
    
    Object.assign(salesSettings, {
      default_payment_term: 30,
      default_credit_limit: 500.00,
      credit_alert_percentage: 80,
      auto_send_reminders: false
    })
    
    Object.assign(systemSettings, {
      require_customer_for_credit: true,
      block_negative_stock: true,
      auto_backup: true,
      theme: 'light',
      language: 'pt-BR',
      items_per_page: 25,
      auto_print_receipts: false
    })
    
    alert('Configurações restauradas para os valores padrão!')
  }
}

onMounted(async () => {
  console.log('SettingsView montado, carregando configurações...')
  console.log('Token atual:', localStorage.getItem('token'))
  
  try {
    await settingsStore.loadSettings()
    console.log('Configurações carregadas com sucesso')
  } catch (error) {
    console.error('Erro ao carregar configurações:', error)
  }
})
</script>
