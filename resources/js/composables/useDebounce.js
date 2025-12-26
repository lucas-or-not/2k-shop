import { ref, onUnmounted } from 'vue'

/**
 * Creates a debounced function that delays execution until after wait time
 * @param {Function} fn - Function to debounce
 * @param {number} wait - Delay in milliseconds
 * @returns {Function} Debounced function
 */
export function debounce(fn, wait = 500) {
  let timeoutId = null
  
  const debounced = function (...args) {
    // Clear previous timeout
    if (timeoutId !== null) {
      clearTimeout(timeoutId)
    }
    
    // Set new timeout
    timeoutId = setTimeout(() => {
      fn.apply(this, args)
      timeoutId = null
    }, wait)
  }
  
  // Cancel function to clear pending execution
  debounced.cancel = () => {
    if (timeoutId !== null) {
      clearTimeout(timeoutId)
      timeoutId = null
    }
  }
  
  return debounced
}

/**
 * Composable for debounced functions with automatic cleanup
 * @param {Function} fn - Function to debounce
 * @param {number} wait - Delay in milliseconds
 * @returns {Object} Object with debounced function and cancel method
 */
export function useDebounce(fn, wait = 500) {
  const debouncedFn = debounce(fn, wait)
  
  // Cleanup on unmount
  onUnmounted(() => {
    debouncedFn.cancel()
  })
  
  return {
    debounced: debouncedFn,
    cancel: () => debouncedFn.cancel()
  }
}

