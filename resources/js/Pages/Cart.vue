<template>
  <div class="min-h-screen bg-gray-50">
    <TopNav :user="user" @logout="logout" />
    <div class="max-w-7xl mx-auto px-4 py-8">
      <h1 class="text-3xl font-bold mb-2">My Cart</h1>
      <p class="text-gray-500 mb-8">You have {{ cart.length }} item{{ cart.length === 1 ? '' : 's' }} in your cart</p>
      
      <div v-if="cart.length === 0" class="text-center py-12">
        <p class="text-gray-500 text-lg">Your cart is empty</p>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
          <div v-for="item in cart" :key="item.id" class="bg-white rounded-xl shadow p-6 flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
              <div class="font-bold text-lg mb-1">{{ item.product?.name || 'Loading...' }}</div>
              <div class="text-gray-500 text-sm mb-2">{{ item.product?.description }}</div>
              <div class="text-blue-600 font-bold text-xl mb-2">${{ formatPrice(item.product?.price) }}</div>
              <div v-if="item.product && item.product.stock_quantity === 0" class="text-red-600 text-sm font-semibold mb-2">
                Out of Stock
              </div>
              <div v-else-if="item.product && item.product.stock_quantity !== null && item.product.stock_quantity < item.quantity" class="text-orange-600 text-sm font-semibold mb-2">
                Only {{ item.product.stock_quantity }} available
              </div>
            </div>
            <div class="flex flex-col items-end gap-2">
              <div class="flex items-center gap-3">
                <button
                  @click="updateQuantity(item.product_id || item.product?.id, item.quantity - 1)"
                  :disabled="item.quantity <= 1 || isPending(item.product_id || item.product?.id)"
                  class="w-8 h-8 rounded border border-gray-300 flex items-center justify-center hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  -
                </button>
                <span class="text-lg font-semibold w-8 text-center">
                  {{ item.quantity }}
                  <span v-if="isPending(item.product_id || item.product?.id)" class="text-xs text-blue-500">...</span>
                </span>
                <button
                  @click="updateQuantity(item.product_id || item.product?.id, item.quantity + 1)"
                  :disabled="!item.product || item.product.stock_quantity == null || item.product.stock_quantity === 0 || item.product.stock_quantity <= item.quantity || isPending(item.product_id || item.product?.id)"
                  class="w-8 h-8 rounded border border-gray-300 flex items-center justify-center hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                  :title="item.product ? `Max: ${item.product.stock_quantity}` : 'Product data not loaded'"
                >
                  +
                </button>
              </div>
              <div class="text-lg font-bold text-blue-600">
                ${{ formatPrice((item.product?.price || 0) * item.quantity) }}
              </div>
              <button
                @click="removeFromCart(item.product_id || item.product?.id)"
                class="text-red-600 hover:text-red-800 text-sm font-medium"
              >
                Remove
              </button>
            </div>
          </div>
        </div>

        <div class="lg:col-span-1">
          <div class="bg-white rounded-xl shadow p-6 sticky top-4">
            <h2 class="text-xl font-bold mb-4">Order Summary</h2>
            <div class="space-y-2 mb-4">
              <div class="flex justify-between text-gray-600">
                <span>Subtotal</span>
                <span>${{ formatPrice(subtotal) }}</span>
              </div>
              <div class="border-t pt-2 mt-2">
                <div class="flex justify-between text-lg font-bold">
                  <span>Total</span>
                  <span class="text-blue-600">${{ formatPrice(subtotal) }}</span>
                </div>
              </div>
            </div>
            <button
              @click="createOrder"
              :disabled="isCreatingOrder || cart.length === 0"
              class="w-full py-3 bg-blue-600 text-white rounded font-semibold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
            >
              {{ isCreatingOrder ? 'Processing...' : 'Checkout' }}
            </button>
            <button
              @click="clearCart"
              class="w-full py-2 mt-2 border border-gray-300 text-gray-700 rounded font-semibold hover:bg-gray-50 transition"
            >
              Clear Cart
            </button>
          </div>
        </div>
      </div>

      <div v-if="cart.length > 0" class="flex justify-center mt-8 gap-2">
        <button
          @click="goToPage(currentPage - 1)"
          :disabled="currentPage === 1"
          class="px-3 py-1 rounded border bg-white text-gray-700 disabled:opacity-50"
        >Prev</button>
        <span class="px-3 py-1">{{ currentPage }} / {{ lastPage }}</span>
        <button
          @click="goToPage(currentPage + 1)"
          :disabled="currentPage === lastPage"
          class="px-3 py-1 rounded border bg-white text-gray-700 disabled:opacity-50"
        >Next</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { usePage, router } from '@inertiajs/vue3'
import TopNav from '@/components/TopNav.vue'
import { useToast } from '@/composables/useToast'
import { debounce } from '@/composables/useDebounce'

const { success, error } = useToast()

const user = usePage().props.user
const cart = ref([])
const currentPage = ref(1)
const lastPage = ref(1)
const isCreatingOrder = ref(false)

// Track server state for each item (for error recovery)
const serverState = ref(new Map())

// Track pending requests per product ID with AbortController
const pendingRequests = ref(new Map())

// Track which product IDs have pending requests (for template reactivity)
// Using an object instead of Set for better Vue reactivity
const pendingProductIds = ref({})

// Helper function to check if a product has pending updates
const isPending = (productId) => {
  return !!pendingProductIds.value[productId]
}

const formatPrice = (price) => {
  if (price == null || price === '') return '0.00'
  const numPrice = typeof price === 'string' ? parseFloat(price) : price
  return isNaN(numPrice) ? '0.00' : numPrice.toFixed(2)
}

const subtotal = computed(() => {
  return cart.value.reduce((sum, item) => {
    const price = typeof item.product?.price === 'string' 
      ? parseFloat(item.product.price) 
      : (item.product?.price || 0)
    return sum + price * (item.quantity || 0)
  }, 0)
})

const fetchCart = async (page = 1) => {
  try {
    const res = await axios.get('/api/v1/cart', { params: { page } })
    cart.value = res.data.data || []
    currentPage.value = res.data.pagination?.current_page || 1
    lastPage.value = res.data.pagination?.last_page || 1
    
    // Update server state for all items
    cart.value.forEach(item => {
      const productId = item.product_id || item.product?.id
      if (productId) {
        serverState.value.set(productId, {
          quantity: item.quantity,
          product: item.product
        })
      }
    })
  } catch (err) {
    error(err.response?.data?.message || 'Failed to fetch cart')
    cart.value = []
  }
}

onMounted(() => {
  fetchCart()
})

// Cleanup: cancel all pending requests on unmount
onUnmounted(() => {
  pendingRequests.value.forEach(controller => {
    controller.abort()
  })
  pendingRequests.value.clear()
  pendingProductIds.value = {}
})

// Optimistic UI update - update immediately
const updateQuantityOptimistic = (productId, newQuantity) => {
  if (!productId) return
  
  // Find the item in cart
  const item = cart.value.find(item => 
    (item.product_id || item.product?.id) === productId
  )
  
  if (!item) return
  
  // Store server state if not already stored
  if (!serverState.value.has(productId)) {
    serverState.value.set(productId, {
      quantity: item.quantity,
      product: item.product
    })
  }
  
  // Update UI immediately (optimistic update)
  item.quantity = newQuantity
}

// Actual API call (debounced)
const updateQuantityAPI = async (productId, newQuantity) => {
  if (!productId) {
    error('Product ID is missing')
    return
  }
  
  // Cancel previous pending request for this product
  if (pendingRequests.value.has(productId)) {
    pendingRequests.value.get(productId).abort()
    pendingRequests.value.delete(productId)
    delete pendingProductIds.value[productId]
  }
  
  // Create new AbortController for this request
  const abortController = new AbortController()
  pendingRequests.value.set(productId, abortController)
  pendingProductIds.value[productId] = true
  
  try {
    await axios.put('/api/v1/cart', {
      product_id: productId,
      quantity: newQuantity
    }, {
      signal: abortController.signal
    })
    
    // Update server state on success
    const item = cart.value.find(item => 
      (item.product_id || item.product?.id) === productId
    )
    if (item) {
      serverState.value.set(productId, {
        quantity: newQuantity,
        product: item.product
      })
    }
    
    // Refresh cart to get updated data with fresh product information
    await fetchCart(currentPage.value)
    // Dispatch event to update cart count in TopNav
    window.dispatchEvent(new CustomEvent('cart-updated'))
  } catch (err) {
    // Ignore abort errors
    if (err.name === 'AbortError' || err.code === 'ERR_CANCELED') {
      return
    }
    
    // Revert to server state on error
    const serverItem = serverState.value.get(productId)
    if (serverItem) {
      const item = cart.value.find(item => 
        (item.product_id || item.product?.id) === productId
      )
      if (item) {
        item.quantity = serverItem.quantity
      }
    }
    
    const errorMessage = err.response?.data?.message || err.response?.data?.error || 'Failed to update quantity'
    error(errorMessage)
    // Refresh cart on error to sync state
    await fetchCart(currentPage.value)
  } finally {
    // Clean up
    pendingRequests.value.delete(productId)
    delete pendingProductIds.value[productId]
  }
}

// Debounced API call (500ms delay)
const debouncedUpdateQuantityAPI = debounce(updateQuantityAPI, 500)

// Main update function - optimistic UI with debounced request
const updateQuantity = (productId, newQuantity) => {
  if (!productId) {
    error('Product ID is missing')
    return
  }
  
  if (newQuantity < 1) {
    // If quantity would be 0, remove the item instead
    removeFromCart(productId)
    return
  }
  
  // Find the item to check stock
  const item = cart.value.find(item => 
    (item.product_id || item.product?.id) === productId
  )
  
  if (!item) return
  
  // Check stock limit
  if (item.product && item.product.stock_quantity !== null && newQuantity > item.product.stock_quantity) {
    error(`Only ${item.product.stock_quantity} available in stock`)
    return
  }
  
  // Update UI immediately
  updateQuantityOptimistic(productId, newQuantity)
  
  // Send debounced request
  debouncedUpdateQuantityAPI(productId, newQuantity)
}

const removeFromCart = async (productId) => {
  try {
    await axios.delete('/api/v1/cart', { data: { product_id: productId } })
    cart.value = cart.value.filter(item => (item.product_id || item.product?.id) !== productId)
    success('Item removed from cart')
    // Dispatch event to update cart count in TopNav
    window.dispatchEvent(new CustomEvent('cart-updated'))
  } catch (err) {
    error(err.response?.data?.message || 'Failed to remove item')
  }
}

const clearCart = async () => {
  if (!confirm('Are you sure you want to clear your cart?')) return
  
  try {
    await axios.delete('/api/v1/cart/clear')
    cart.value = []
    success('Cart cleared successfully')
    // Dispatch event to update cart count in TopNav
    window.dispatchEvent(new CustomEvent('cart-updated'))
  } catch (err) {
    error(err.response?.data?.message || 'Failed to clear cart')
  }
}

const createOrder = async () => {
  if (cart.value.length === 0) return
  
  isCreatingOrder.value = true
  try {
    const response = await axios.post('/api/v1/orders')
    cart.value = []
    // Dispatch event to update cart count in TopNav
    window.dispatchEvent(new CustomEvent('cart-updated'))
    // Redirect to order success page with order ID
    router.visit(`/orders/success?order_id=${response.data.data.id}`)
  } catch (err) {
    error(err.response?.data?.message || 'Failed to create order')
    isCreatingOrder.value = false
  }
}

const logout = () => {
  axios.post('/api/v1/auth/logout').then(() => {
    router.visit('/')
  })
}

const goToPage = (page) => {
  if (page >= 1 && page <= lastPage.value) {
    fetchCart(page)
  }
}
</script>
