<template>
  <div class="min-h-screen bg-gray-50">
    <TopNav :user="user" @logout="logout" />
    <div class="max-w-7xl mx-auto px-4 py-8">
      <h1 class="text-3xl font-bold mb-2">All Products</h1>
      <p class="text-gray-500 mb-8">Discover amazing products and add them to your cart</p>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <div v-for="product in products" :key="product.id" class="bg-white rounded-xl shadow p-6 flex flex-col h-full">
          <div class="font-bold text-lg mb-1">{{ product.name }}</div>
          <div class="text-gray-500 text-sm mb-4 flex-grow overflow-hidden">{{ truncateDescription(product.description) }}</div>
          
          <!-- Bottom section: Price, Stock, Button -->
          <div class="mt-auto">
            <div class="text-blue-600 font-bold text-xl mb-2">${{ product.price }}</div>
            
            <!-- Stock Status -->
            <div v-if="product.stock_quantity === 0" class="mb-3">
              <span class="inline-block px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded">
                Out of Stock
              </span>
            </div>
            <div v-else-if="product.stock_quantity <= 10" class="mb-3">
              <span class="inline-block px-2 py-1 bg-orange-100 text-orange-800 text-xs font-semibold rounded">
                Only {{ product.stock_quantity }} left
              </span>
            </div>
            <div v-else class="mb-3">
              <span class="inline-block px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded">
                In Stock ({{ product.stock_quantity }})
              </span>
            </div>

            <button
              @click="toggleCart(product)"
              :disabled="product.stock_quantity === 0"
              :class="[
                product.is_in_cart 
                  ? 'bg-blue-600 text-white' 
                  : 'border border-gray-300 hover:bg-gray-50',
                product.stock_quantity === 0 && 'opacity-50 cursor-not-allowed'
              ]"
              class="w-full py-2 rounded font-semibold flex items-center justify-center gap-2 transition"
            >
              <span>{{ product.is_in_cart ? '✓' : '🛒' }}</span>
              {{ product.is_in_cart ? 'In Cart' : 'Add to Cart' }}
            </button>
          </div>
        </div>
      </div>
      <div class="flex justify-center mt-8 gap-2">
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
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { usePage, router } from '@inertiajs/vue3'
import TopNav from '@/components/TopNav.vue'
import { useToast } from '@/composables/useToast'

const { success, error } = useToast()

const products = ref([])
const loading = ref(true)
const user = usePage().props.user

const currentPage = ref(1)
const lastPage = ref(1)

const truncateDescription = (description) => {
  if (!description) return ''
  if (description.length <= 160) return description
  return description.substring(0, 160) + '...'
}

const fetchProducts = async (page = 1) => {
  loading.value = true
  const response = await axios.get('/api/v1/products', { params: { page } })

  products.value = response.data.data
  currentPage.value = response.data?.meta?.current_page
  lastPage.value = response.data?.meta?.last_page
  loading.value = false
}

onMounted(() => {
  fetchProducts()
})

const toggleCart = async (product) => {
  if (product.stock_quantity === 0) {
    error('Product is out of stock')
    return
  }
  
  try {
    if (product.is_in_cart) {
      await axios.delete('/api/v1/cart', { data: { product_id: product.id } })
      product.is_in_cart = false
      success('Removed from cart')
    } else {
      await axios.post('/api/v1/cart', { product_id: product.id, quantity: 1 })
      product.is_in_cart = true
      success('Added to cart')
    }
    // Dispatch event to update cart count in TopNav
    window.dispatchEvent(new CustomEvent('cart-updated'))
  } catch (err) {
    error(err.response?.data?.message || 'Failed to update cart')
  }
}

const goToPage = (page) => {
  if (page >= 1 && page <= lastPage.value) {
    fetchProducts(page)
  }
}

const logout = () => {
  axios.post('/api/v1/auth/logout').then(() => {
    router.visit('/')
  })
}
</script>

<style>
.material-icons {
  font-family: 'Material Icons';
  font-style: normal;
  font-weight: normal;
  font-size: 20px;
  line-height: 1;
  letter-spacing: normal;
  text-transform: none;
  display: inline-block;
  white-space: nowrap;
  direction: ltr;
  -webkit-font-feature-settings: 'liga';
  -webkit-font-smoothing: antialiased;
}
</style>
