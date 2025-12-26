<template>
  <nav class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between">
    <div class="flex items-center gap-8">
      <span class="text-2xl font-bold text-blue-700">2kShop</span>
      <div class="flex gap-6">
        <Link href="/products" class="flex items-center gap-1 text-gray-700 hover:text-blue-700 font-medium">
          <span class="material-icons">shopping_cart</span> Products
        </Link>
        <Link href="/cart" class="flex items-center gap-1 text-gray-700 hover:text-blue-700 font-medium relative">
          <span class="material-icons">shopping_cart</span> Cart
          <span v-if="cartCount > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
            {{ cartCount > 99 ? '99+' : cartCount }}
          </span>
        </Link>
      </div>
    </div>
    <div class="flex items-center gap-4">
      <span class="text-gray-700 font-medium">{{ user?.name || 'User' }}</span>
      <button @click="$emit('logout')" class="text-gray-500 hover:text-red-600" title="Logout">
        <span class="material-icons">logout</span>
      </button>
    </div>
  </nav>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { defineProps, defineEmits } from 'vue'
import { Link } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
  user: Object
})
defineEmits(['logout'])

const cartCount = ref(0)
let refreshInterval = null

const fetchCartCount = async () => {
  if (!props.user) return
  
  try {
    const res = await axios.get('/api/v1/cart/count')
    cartCount.value = res.data.data?.count || 0
  } catch (err) {
    cartCount.value = 0
  }
}

// Listen for cart updates from other components
const handleCartUpdate = () => {
  fetchCartCount()
}

onMounted(() => {
  fetchCartCount()
  // Refresh cart count every 5 seconds to keep it in sync
  refreshInterval = setInterval(fetchCartCount, 5000)
  // Listen for custom events when cart is updated
  window.addEventListener('cart-updated', handleCartUpdate)
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
  window.removeEventListener('cart-updated', handleCartUpdate)
})
</script>

<style scoped>
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
