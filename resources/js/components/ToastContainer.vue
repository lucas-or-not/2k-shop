<template>
  <div class="fixed top-4 right-4 z-50 space-y-2">
    <TransitionGroup name="toast" tag="div">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="getToastClasses(toast.type)"
        class="min-w-[300px] max-w-md px-4 py-3 rounded-lg shadow-lg flex items-center justify-between gap-4"
      >
        <div class="flex items-center gap-3 flex-1">
          <span class="text-xl">{{ getIcon(toast.type) }}</span>
          <p class="text-sm font-medium">{{ toast.message }}</p>
        </div>
        <button
          @click="removeToast(toast.id)"
          class="text-gray-400 hover:text-gray-600 transition"
        >
          <span class="material-icons text-sm">close</span>
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { useToast } from '@/composables/useToast'

const { toasts, removeToast } = useToast()

const getToastClasses = (type) => {
  const classes = {
    success: 'bg-green-50 border border-green-200 text-green-800',
    error: 'bg-red-50 border border-red-200 text-red-800',
    warning: 'bg-orange-50 border border-orange-200 text-orange-800',
    info: 'bg-blue-50 border border-blue-200 text-blue-800',
  }
  return classes[type] || classes.info
}

const getIcon = (type) => {
  const icons = {
    success: '✓',
    error: '✕',
    warning: '⚠',
    info: 'ℹ',
  }
  return icons[type] || icons.info
}
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
</style>

