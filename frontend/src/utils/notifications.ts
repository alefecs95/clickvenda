export interface NotificationOptions {
  type?: 'success' | 'error' | 'warning' | 'info'
  duration?: number
  position?: 'top-right' | 'top-left' | 'bottom-right' | 'bottom-left'
}

export const showNotification = (message: string, options: NotificationOptions = {}) => {
  const {
    type = 'info',
    duration = 3000,
    position = 'top-right'
  } = options

  // Cores baseadas no tipo
  const typeClasses = {
    success: 'bg-green-500',
    error: 'bg-red-500',
    warning: 'bg-orange-500',
    info: 'bg-blue-500'
  }

  // Ícones baseados no tipo
  const typeIcons = {
    success: `
      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
      </svg>
    `,
    error: `
      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    `,
    warning: `
      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
      </svg>
    `,
    info: `
      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
    `
  }

  // Posicionamento
  const positionClasses = {
    'top-right': 'top-4 right-4',
    'top-left': 'top-4 left-4',
    'bottom-right': 'bottom-4 right-4',
    'bottom-left': 'bottom-4 left-4'
  }

  // Criar elemento de notificação
  const notification = document.createElement('div')
  notification.className = `fixed ${positionClasses[position]} ${typeClasses[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center max-w-md transition-all duration-300 transform translate-x-full opacity-0`
  
  notification.innerHTML = `
    ${typeIcons[type]}
    <span class="flex-1">${message}</span>
    <button class="ml-3 text-white hover:text-gray-200 focus:outline-none" onclick="this.parentElement.remove()">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>
  `

  // Adicionar ao DOM
  document.body.appendChild(notification)

  // Animar entrada
  setTimeout(() => {
    notification.classList.remove('translate-x-full', 'opacity-0')
    notification.classList.add('translate-x-0', 'opacity-100')
  }, 10)

  // Remover automaticamente após o tempo especificado
  if (duration > 0) {
    setTimeout(() => {
      notification.classList.add('translate-x-full', 'opacity-0')
      setTimeout(() => {
        if (notification.parentElement) {
          notification.remove()
        }
      }, 300)
    }, duration)
  }

  return notification
}

// Funções de conveniência
export const showSuccess = (message: string, duration?: number) => 
  showNotification(message, { type: 'success', duration })

export const showError = (message: string, duration?: number) => 
  showNotification(message, { type: 'error', duration })

export const showWarning = (message: string, duration?: number) => 
  showNotification(message, { type: 'warning', duration })

export const showInfo = (message: string, duration?: number) => 
  showNotification(message, { type: 'info', duration })