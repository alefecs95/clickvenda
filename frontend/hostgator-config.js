/**
 * Configurações específicas para Hostgator
 * Este arquivo contém configurações otimizadas para o frontend na Hostgator
 */

// Configurações da API para produção na Hostgator
export const HOSTGATOR_CONFIG = {
  // URL base da API
  API_BASE_URL: 'https://alefecarvalhosilva1758230435000.0771034.meusitehostgator.com.br/api',
  
  // Configurações de timeout
  API_TIMEOUT: 15000, // 15 segundos para servidores compartilhados
  
  // Configurações de retry
  MAX_RETRIES: 3,
  RETRY_DELAY: 1000, // 1 segundo
  
  // Configurações de cache
  CACHE_ENABLED: true,
  CACHE_DURATION: 300000, // 5 minutos
  
  // Configurações de debug
  DEBUG: false,
  
  // Configurações de CORS
  CORS_CREDENTIALS: true,
  
  // Headers padrão
  DEFAULT_HEADERS: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
}

// Configurações de build para Hostgator
export const BUILD_CONFIG = {
  // Configurações de otimização
  MINIFY: true,
  SOURCEMAP: false,
  
  // Configurações de chunk
  CHUNK_SIZE_WARNING_LIMIT: 1000,
  
  // Configurações de assets
  ASSETS_DIR: 'assets',
  
  // Configurações de base
  BASE_PATH: './',
  
  // Configurações de cache
  CACHE_BUSTING: true
}

// Configurações de ambiente
export const ENV_CONFIG = {
  PRODUCTION: {
    API_URL: HOSTGATOR_CONFIG.API_BASE_URL,
    APP_NAME: 'ClickVenda',
    DEBUG: false,
    LOG_LEVEL: 'error'
  },
  
  DEVELOPMENT: {
    API_URL: 'http://localhost:8000/api',
    APP_NAME: 'ClickVenda Dev',
    DEBUG: true,
    LOG_LEVEL: 'debug'
  }
}

// Função para obter configuração baseada no ambiente
export function getConfig() {
  const isProduction = import.meta.env.PROD
  
  if (isProduction) {
    return {
      ...HOSTGATOR_CONFIG,
      ...ENV_CONFIG.PRODUCTION
    }
  }
  
  return {
    ...HOSTGATOR_CONFIG,
    ...ENV_CONFIG.DEVELOPMENT
  }
}

// Configurações de monitoramento
export const MONITORING_CONFIG = {
  // Configurações de performance
  PERFORMANCE_MONITORING: true,
  
  // Configurações de erro
  ERROR_REPORTING: true,
  
  // Configurações de analytics
  ANALYTICS_ENABLED: false, // Desabilitado por padrão
  
  // Configurações de logs
  LOG_TO_CONSOLE: false, // Desabilitado em produção
  LOG_TO_SERVER: true
}

export default {
  HOSTGATOR_CONFIG,
  BUILD_CONFIG,
  ENV_CONFIG,
  MONITORING_CONFIG,
  getConfig
}
