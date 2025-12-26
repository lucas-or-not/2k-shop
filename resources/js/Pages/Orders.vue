<template>
  <div class="min-h-screen bg-gray-50">
    <TopNav :user="user" @logout="logout" />
    <div class="max-w-7xl mx-auto px-4 py-8">
      <h1 class="text-3xl font-bold mb-2">My Orders</h1>
      <p class="text-gray-500 mb-8">View all your past orders</p>
      
      <div v-if="loading" class="text-center py-12">
        <p class="text-gray-500 text-lg">Loading orders...</p>
      </div>

      <div v-else-if="orders.length === 0" class="text-center py-12">
        <p class="text-gray-500 text-lg">You haven't placed any orders yet</p>
        <button
          @click="goToProducts"
          class="mt-4 px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition"
        >
          Start Shopping
        </button>
      </div>

      <div v-else class="space-y-6">
        <div v-for="order in orders" :key="order.id" class="bg-white rounded-xl shadow p-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 pb-4 border-b">
            <div>
              <h3 class="text-lg font-bold">Order #{{ order.id }}</h3>
              <p class="text-sm text-gray-500">
                Placed on {{ formatDate(order.created_at) }}
              </p>
            </div>
            <div class="mt-2 sm:mt-0 text-right">
              <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold"
                    :class="getStatusClass(order.status)">
                {{ order.status }}
              </span>
              <p class="text-xl font-bold text-blue-600 mt-2">
                ${{ formatPrice(order.total_amount) }}
              </p>
            </div>
          </div>

          <div class="space-y-3">
            <h4 class="font-semibold text-gray-700">Order Items:</h4>
            <div v-for="item in order.order_items" :key="item.id" 
                 class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div class="flex-1">
                <p class="font-medium">{{ item.product?.name || 'Product' }}</p>
                <p class="text-sm text-gray-500">
                  Quantity: {{ item.quantity }} × ${{ formatPrice(item.price_at_purchase) }}
                </p>
              </div>
              <div class="text-right">
                <p class="font-semibold">
                  ${{ formatPrice((item.quantity || 0) * (item.price_at_purchase || 0)) }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="lastPage > 1" class="flex justify-center items-center gap-4 mt-8">
          <button
            @click="fetchOrders(currentPage - 1)"
            :disabled="currentPage <= 1"
            class="px-4 py-2 border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
          >
            Previous
          </button>
          <span class="text-gray-600">
            Page {{ currentPage }} of {{ lastPage }}
          </span>
          <button
            @click="fetchOrders(currentPage + 1)"
            :disabled="currentPage >= lastPage"
            class="px-4 py-2 border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import axios from 'axios'
import TopNav from '@/components/TopNav.vue'
import { useToast } from '@/composables/useToast'

const { error } = useToast()
const user = usePage().props.user
const orders = ref([])
const loading = ref(true)
const currentPage = ref(1)
const lastPage = ref(1)

const fetchOrders = async (page = 1) => {
  loading.value = true
  try {
    const res = await axios.get('/api/v1/orders', { params: { page } })
    orders.value = res.data.data || []
    currentPage.value = res.data.pagination?.current_page || 1
    lastPage.value = res.data.pagination?.last_page || 1
  } catch (err) {
    error(err.response?.data?.message || 'Failed to fetch orders')
    orders.value = []
  } finally {
    loading.value = false
  }
}

const formatPrice = (price) => {
  if (price == null || price === '') return '0.00'
  const numPrice = typeof price === 'string' ? parseFloat(price) : price
  return isNaN(numPrice) ? '0.00' : numPrice.toFixed(2)
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getStatusClass = (status) => {
  const statusLower = status?.toLowerCase()
  if (statusLower === 'completed') {
    return 'bg-green-100 text-green-800'
  } else if (statusLower === 'pending') {
    return 'bg-yellow-100 text-yellow-800'
  } else if (statusLower === 'cancelled') {
    return 'bg-red-100 text-red-800'
  }
  return 'bg-gray-100 text-gray-800'
}

const goToProducts = () => {
  router.visit('/products')
}

const logout = () => {
  axios.post('/api/v1/auth/logout').then(() => {
    router.visit('/')
  })
}

onMounted(() => {
  fetchOrders()
})
</script>

