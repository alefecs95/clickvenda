import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../services/api'

interface ClienteVasilhames {
  id: number
  nome: string
  documento: string
  totalVasilhames: number
  ultimaMovimentacao: string | null
  status: string
  debitosPorModelo?: Array<{
    modelo_id: number
    modelo_nome: string
    modelo_preco: number
    quantidade_devida: number
  }>
}

interface VasilhameModel {
  id: number
  name: string
  description: string | null
  returnable_price: number
  returnable_quantity: number
  stock_quantity: number
  minimum_stock: number
  available: number
  created_at: string
  updated_at: string
}

export interface PendenciaVasilhame {
  id: number
  cliente_id: number
  vasilhame_model_id: number
  venda_id: number | null
  quantidade_pendente: number
  quantidade_necessaria: number
  quantidade_entregue: number
  observacoes: string | null
  resolvida: boolean
  data_resolucao: string | null
  created_at: string
  updated_at: string
  cliente?: {
    id: number
    name: string
    cpf_cnpj: string
  }
  vasilhameModel?: VasilhameModel
  venda?: any
}

interface PendenciaPorModelo {
  modelo: VasilhameModel
  quantidade_total: number
  pendencias: PendenciaVasilhame[]
}

export const useVasilhamesStore = defineStore('vasilhames', () => {
  // Estado
  const clientes = ref<ClienteVasilhames[]>([])
  const carregando = ref(false)
  const erro = ref<string | null>(null)
  const produtosRetornaveis = ref<any[]>([])
  const modelos = ref<VasilhameModel[]>([])
  const carregandoModelos = ref(false)
  const pendencias = ref<PendenciaVasilhame[]>([])
  const pendenciasPorCliente = ref<Record<number, PendenciaPorModelo[]>>({})
  const carregandoPendencias = ref(false)

  // Métodos
  const carregarClientes = async (filtro = 'todos', busca = '') => {
    carregando.value = true
    erro.value = null
    
    try {
      const params = new URLSearchParams()
      if (filtro !== 'todos') params.append('filtro', filtro)
      if (busca) params.append('busca', busca)
      
      const response = await api.get(`/vasilhames?${params}`)
      clientes.value = response.data.clientes
      
      // Debug: Log dos dados recebidos
      console.log('Dados recebidos do backend:', response.data)
      console.log('Clientes carregados:', clientes.value)
      console.log('Primeiro cliente debitosPorModelo:', clientes.value[0]?.debitosPorModelo)
    } catch (err: any) {
      erro.value = 'Erro ao carregar dados dos vasilhames'
      console.error('Erro ao carregar vasilhames:', err)
      throw err
    } finally {
      carregando.value = false
    }
  }

  const carregarProdutosRetornaveis = async () => {
    try {
      const response = await api.get('/vasilhames/produtos')
      // Backend retorna um array simples; porém tornamos robusto caso venha embrulhado
      const data = response.data
      produtosRetornaveis.value = Array.isArray(data)
        ? data
        : (data?.produtos ?? data?.products ?? [])
    } catch (err: any) {
      console.error('Erro ao carregar produtos retornáveis:', err)
      throw err
    }
  }

  // Métodos para gerenciar modelos de vasilhame
  const carregarModelos = async () => {
    carregandoModelos.value = true
    try {
      const response = await api.get('/vasilhames/modelos')
      // Verificar diferentes estruturas de resposta possíveis
      modelos.value = response.data.modelos || response.data || []
      console.log('Modelos carregados:', modelos.value)
    } catch (err: any) {
      console.error('Erro ao carregar modelos:', err)
      throw err
    } finally {
      carregandoModelos.value = false
    }
  }

  const criarModelo = async (dados: { name: string; description?: string; returnable_price: number; returnable_quantity: number; stock_quantity: number; minimum_stock?: number; available?: boolean }) => {
    try {
      const response = await api.post('/vasilhames/modelos', dados)
      await carregarModelos()
      return response.data.modelo
    } catch (err: any) {
      console.error('Erro ao criar modelo:', err)
      throw err
    }
  }

  const atualizarModelo = async (id: number, dados: { name?: string; description?: string; returnable_price?: number; returnable_quantity?: number; stock_quantity?: number; minimum_stock?: number; available?: boolean }) => {
    try {
      const response = await api.put(`/vasilhames/modelos/${id}`, dados)
      await carregarModelos()
      return response.data.modelo
    } catch (err: any) {
      console.error('Erro ao atualizar modelo:', err)
      throw err
    }
  }

  const excluirModelo = async (id: number) => {
    try {
      await api.delete(`/vasilhames/modelos/${id}`)
      await carregarModelos()
    } catch (err: any) {
      console.error('Erro ao excluir modelo:', err)
      throw err
    }
  }

  const registrarDevolucao = async (clienteId: number, vasilhameModelId: number, quantidade: number, observacoes?: string) => {
    carregando.value = true
    erro.value = null
    
    try {
      const response = await api.post('/vasilhames/devolucao', {
        cliente_id: clienteId,
        vasilhame_model_id: vasilhameModelId,
        quantidade,
        observacoes
      })
      
      // Recarregar dados atualizados
      await carregarClientes()
      return response.data.movimentacao
    } catch (err: any) {
      erro.value = 'Erro ao registrar devolução'
      console.error('Erro ao registrar devolução:', err)
      throw err
    } finally {
      carregando.value = false
    }
  }

  const registrarSaida = async (clienteId: number, produtoId: number, quantidade: number, observacoes?: string, vasilhameModelId?: number, pedidoId?: number) => {
    carregando.value = true
    erro.value = null
    
    try {
      const response = await api.post('/vasilhames/saida', {
        cliente_id: clienteId,
        produto_id: produtoId,
        vasilhame_model_id: vasilhameModelId,
        pedido_id: pedidoId,
        quantidade,
        observacoes
      })
      
      // Recarregar dados atualizados
      await carregarClientes()
      return response.data
    } catch (err: any) {
      erro.value = 'Erro ao registrar saída'
      console.error('Erro ao registrar saída:', err)
      throw err
    } finally {
      carregando.value = false
    }
  }

  const obterHistoricoCliente = async (clienteId: number) => {
    carregando.value = true
    erro.value = null
    
    try {
      const response = await api.get(`/vasilhames/historico-cliente/${clienteId}`)
      return response.data.historico
    } catch (err: any) {
      erro.value = 'Erro ao carregar histórico'
      console.error('Erro ao carregar histórico:', err)
      throw err
    } finally {
      carregando.value = false
    }
  }

  const obterRelatorio = async () => {
    carregando.value = true
    erro.value = null
    
    try {
      const response = await api.get('/vasilhames/relatorio')
      return response.data
    } catch (err: any) {
      erro.value = 'Erro ao gerar relatório'
      console.error('Erro ao gerar relatório:', err)
      throw err
    } finally {
      carregando.value = false
    }
  }

  // Métodos para gerenciar pendências
  const carregarPendencias = async (filtros = {}) => {
    carregandoPendencias.value = true
    erro.value = null
    
    try {
      const params = new URLSearchParams()
      
      // Adicionar filtros à query string
      Object.entries(filtros).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          params.append(key, String(value))
        }
      })
      
      const response = await api.get(`/vasilhames/pendencias?${params}`)
      pendencias.value = response.data.data || []
      
      return pendencias.value
    } catch (err: any) {
      erro.value = 'Erro ao carregar pendências de vasilhames'
      console.error('Erro ao carregar pendências:', err)
      throw err
    } finally {
      carregandoPendencias.value = false
    }
  }
  
  const carregarPendenciasPorCliente = async (clienteId: number) => {
    carregandoPendencias.value = true
    erro.value = null
    
    try {
      const response = await api.get(`/vasilhames/pendencias/cliente/${clienteId}`)
      
      // Armazenar no cache por cliente
      pendenciasPorCliente.value[clienteId] = response.data.pendencias_por_modelo || []
      
      // Return both the pendencias and the client information
      return {
        pendencias: pendenciasPorCliente.value[clienteId],
        cliente: response.data.cliente
      }
    } catch (err: any) {
      erro.value = 'Erro ao carregar pendências do cliente'
      console.error('Erro ao carregar pendências do cliente:', err)
      throw err
    } finally {
      carregandoPendencias.value = false
    }
  }
  
  const resolverPendencia = async (pendenciaId: number, quantidade: number, observacoes?: string) => {
    carregando.value = true
    erro.value = null
    
    try {
      const response = await api.post(`/vasilhames/pendencias/${pendenciaId}/resolver`, {
        quantidade,
        observacoes
      })
      
      // Atualizar pendências após resolução
      await carregarPendencias()
      
      // Limpar cache de pendências por cliente
      pendenciasPorCliente.value = {}
      
      return response.data.pendencia
    } catch (err: any) {
      erro.value = 'Erro ao resolver pendência'
      console.error('Erro ao resolver pendência:', err)
      throw err
    } finally {
      carregando.value = false
    }
  }

  // Limpar estado
  const limparEstado = () => {
    clientes.value = []
    carregando.value = false
    erro.value = null
    pendencias.value = []
    pendenciasPorCliente.value = {}
  }

  return {
    // Estado
    clientes,
    carregando,
    erro,
    modelos,
    carregandoModelos,
    pendencias,
    pendenciasPorCliente,
    carregandoPendencias,
    
    // Métodos
    carregarClientes,
    carregarProdutosRetornaveis,
    carregarModelos,
    criarModelo,
    atualizarModelo,
    excluirModelo,
    registrarDevolucao,
    registrarSaida,
    obterHistoricoCliente,
    obterRelatorio,
    carregarPendencias,
    carregarPendenciasPorCliente,
    resolverPendencia,
    limparEstado,
    // Expor produtos retornáveis
    produtosRetornaveis
  }
})