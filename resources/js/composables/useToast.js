import { ref } from 'vue'

const toasts = ref([])

export function useToast() {
  const showToast = (message, type = 'info') => {
    const id = Date.now()
    const toast = {
      id,
      message,
      type, // 'success', 'error', 'warning', 'info'
    }
    
    toasts.value.push(toast)
    
    // Auto remove after 5 seconds
    setTimeout(() => {
      removeToast(id)
    }, 5000)
    
    return id
  }
  
  const removeToast = (id) => {
    const index = toasts.value.findIndex(t => t.id === id)
    if (index > -1) {
      toasts.value.splice(index, 1)
    }
  }
  
  const success = (message) => showToast(message, 'success')
  const error = (message) => showToast(message, 'error')
  const warning = (message) => showToast(message, 'warning')
  const info = (message) => showToast(message, 'info')
  
  return {
    toasts,
    showToast,
    removeToast,
    success,
    error,
    warning,
    info,
  }
}

