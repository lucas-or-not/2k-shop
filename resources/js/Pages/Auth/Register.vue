<template>
  <div class="min-h-screen flex items-center justify-center bg-white">
    <div class="w-full max-w-md bg-white rounded-xl border border-gray-200 shadow p-8">
      <div class="text-center mb-6">
        <h3 class="text-2xl font-bold mb-1" style="color: #3975f6">Create Account</h3>
        <p class="text-gray-500">Join us to start shopping</p>
      </div>
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
          <input id="name" v-model="name" required placeholder="Enter your full name"
            class="mt-1 block w-full rounded border border-gray-300 bg-white px-3 py-2 focus:ring-2 focus:ring-purple-400 focus:border-purple-400 outline-none transition" />
        </div>
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input id="email" v-model="email" type="email" required placeholder="Enter your email"
            class="mt-1 block w-full rounded border border-gray-300 bg-white px-3 py-2 focus:ring-2 focus:ring-purple-400 focus:border-purple-400 outline-none transition" />
        </div>
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input id="password" v-model="password" type="password" required placeholder="Create a password"
            class="mt-1 block w-full rounded border border-gray-300 bg-white px-3 py-2 focus:ring-2 focus:ring-purple-400 focus:border-purple-400 outline-none transition" />
        </div>
        <div>
          <label for="passwordConfirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
          <input id="passwordConfirmation" v-model="passwordConfirmation" type="password" required placeholder="Confirm your password"
            class="mt-1 block w-full rounded border border-gray-300 bg-white px-3 py-2 focus:ring-2 focus:ring-purple-400 focus:border-purple-400 outline-none transition" />
        </div>
        <button type="submit" :disabled="isLoading || password !== passwordConfirmation"
          class="w-full py-2 px-4 font-semibold rounded mt-2 text-white transition disabled:opacity-50 bg-[#3975f6] hover:bg-blue-700">
          {{ isLoading ? 'Creating account...' : 'Create Account' }}
        </button>
      </form>
      <div class="text-center mt-4">
        <small class="text-gray-500">
          Already have an account?
            <Link href="/" class="hover:underline font-medium" style="color: #3975f6">Sign in</Link>
        </small>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import useAuth from '@/stores/auth'
import { Link } from "@inertiajs/vue3";

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const { register, isLoading } = useAuth()

const handleSubmit = async () => {
  if (password.value !== passwordConfirmation.value) return
  await register(name.value, email.value, password.value)
}
</script>
