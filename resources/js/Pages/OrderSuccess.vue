<template>
  <div class="min-h-screen bg-gray-50">
    <TopNav :user="user" @logout="logout" />
    <div class="max-w-2xl mx-auto px-4 py-12">
      <div class="bg-white rounded-xl shadow-lg p-8 text-center">
        <div class="mb-6">
          <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-4xl text-green-600">✓</span>
          </div>
          <h1 class="text-3xl font-bold text-gray-900 mb-2">Order Placed Successfully!</h1>
          <p class="text-gray-600">Thank you for your purchase. Your order has been confirmed.</p>
        </div>

        <div v-if="orderId" class="mb-6 p-4 bg-gray-50 rounded-lg">
          <p class="text-sm text-gray-600 mb-1">Order ID</p>
          <p class="text-lg font-semibold text-gray-900">#{{ orderId }}</p>
        </div>

        <div class="space-y-4">
          <p class="text-gray-600">
            You will receive an email confirmation shortly with your order details.
          </p>
          
          <div class="flex gap-4 justify-center mt-8">
            <button
              @click="goToProducts"
              class="px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition"
            >
              Continue Shopping
            </button>
            <button
              @click="goToOrders"
              class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition"
            >
              View Orders
            </button>
          </div>
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

const user = usePage().props.user
const orderId = ref(null)

onMounted(() => {
  const urlParams = new URLSearchParams(window.location.search)
  orderId.value = urlParams.get('order_id')
})

const goToProducts = () => {
  router.visit('/products')
}

const goToOrders = () => {
  router.visit('/orders')
}

const logout = () => {
  axios.post('/api/v1/auth/logout').then(() => {
    router.visit('/')
  })
}
</script>

