<template>
  <div class="min-h-screen flex items-center justify-center bg-white">
    <div class="w-full max-w-md bg-white rounded-xl border border-gray-200 shadow p-8">
      <div class="text-center mb-6">
        <h3 class="text-2xl font-bold mb-1" style="color: #3975f6">Welcome Back</h3>
        <p class="text-gray-500">Sign in to your account to access your cart</p>
      </div>
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input id="email" v-model="email" type="email" required placeholder="Enter your email"
            class="mt-1 block w-full rounded border border-gray-300 bg-white px-3 py-2 focus:ring-2 focus:ring-purple-400 focus:border-purple-400 outline-none transition" />
        </div>
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input id="password" v-model="password" type="password" required placeholder="Enter your password"
            class="mt-1 block w-full rounded border border-gray-300 bg-white px-3 py-2 focus:ring-2 focus:ring-purple-400 focus:border-purple-400 outline-none transition" />
        </div>
        <button :disabled="isLoading" type="submit"
          class="w-full py-2 px-4 font-semibold rounded mt-2 text-white transition disabled:opacity-50 bg-[#3975f6] hover:bg-blue-700">
          {{ isLoading ? 'Signing in...' : 'Sign In' }}
        </button>
      </form>
      <div class="text-center mt-4">
        <small class="text-gray-500">
          Don't have an account?
          <Link href="/register" class="hover:underline font-medium" style="color: #3975f6">Sign up</Link>
        </small>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import useAuth from '@/stores/auth'
import { Link } from '@inertiajs/vue3';


const email = ref('')
const password = ref('')
const { login, isLoading } = useAuth()

const handleSubmit = async () => {
  await login(email.value, password.value)
}
</script>
