import { reactive, toRefs } from 'vue'
import { router } from '@inertiajs/vue3'

import axios from 'axios'

const state = reactive({
  isLoading: false,
  user: null,
  error: null,
})

async function login(email, password) {
  state.isLoading = true
  state.error = null
  try {
    const { data } = await axios.post('/api/v1/auth/login', { email, password })
    state.user = data.user
      router.visit('/products')
  } catch (err) {
    state.error = err.response?.data?.message || 'Login failed'
  } finally {
    state.isLoading = false
  }
}

async function register(name, email, password) {
  state.isLoading = true
  state.error = null
  try {
    const { data } = await axios.post('/api/v1/auth/register', { name, email, password, password_confirmation: password })
    state.user = data.user
      router.visit('/products')
  } catch (err) {
    state.error = err.response?.data?.message || 'Registration failed'
  } finally {
    state.isLoading = false
  }
}

async function logout() {
  await axios.post('/api/v1/auth/logout')
  state.user = null
    router.visit('/')
}

export default function useAuth() {
  return {
    ...toRefs(state),
    login,
    register,
    logout,
  }
}
