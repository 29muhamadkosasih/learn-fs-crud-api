<script setup>
import { ref, watch, computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'info', // 'success', 'danger', 'warning', 'info'
  },
  message: {
    type: String,
    required: true,
  },
  duration: {
    type: Number,
    default: 5000, // ms
  },
})

const emit = defineEmits(['close'])

const isVisible = ref(false)
let timerId = null

const toastClass = computed(() => ({
  'toast-success': props.variant === 'success',
  'toast-danger': props.variant === 'danger',
  'toast-warning': props.variant === 'warning',
  'toast-info': props.variant === 'info',
}))

watch(
  () => props.message,
  (newMessage) => {
    if (timerId) {
      window.clearTimeout(timerId)
      timerId = null
    }

    if (newMessage) {
      isVisible.value = true
      if (props.duration > 0) {
        timerId = window.setTimeout(() => {
          isVisible.value = false
          emit('close')
        }, props.duration)
      }
      return
    }

    isVisible.value = false
  }
)

function close() {
  if (timerId) {
    window.clearTimeout(timerId)
    timerId = null
  }

  isVisible.value = false
  emit('close')
}

function getIconClass(variant) {
  const iconMap = {
    success: 'fas fa-check-circle',
    danger: 'fas fa-exclamation-circle',
    warning: 'fas fa-exclamation-triangle',
    info: 'fas fa-info-circle',
  }
  return iconMap[variant] || iconMap.info
}
</script>

<template>
  <transition name="toast">
    <div v-if="isVisible" class="toast-container" :class="toastClass" role="alert" aria-live="assertive">
      <div class="toast-content">
        <i class="toast-icon" :class="getIconClass(variant)"></i>
        <span class="toast-message">{{ message }}</span>
      </div>
      <button type="button" class="toast-close" aria-label="Close" @click="close">
        <i class="fas fa-times"></i>
      </button>
    </div>
  </transition>
</template>

<style scoped>
.toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  min-width: 300px;
  padding: 1rem;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  z-index: 9999;
  animation: slideIn 0.3s ease-out;
}

.toast-success {
  background: #d4edda;
  border: 1px solid #c3e6cb;
  color: #155724;
}

.toast-danger {
  background: #f8d7da;
  border: 1px solid #f5c6cb;
  color: #721c24;
}

.toast-warning {
  background: #fff3cd;
  border: 1px solid #ffeeba;
  color: #856404;
}

.toast-info {
  background: #d1ecf1;
  border: 1px solid #bee5eb;
  color: #0c5460;
}

.toast-content {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex: 1;
}

.toast-icon {
  font-size: 1.25rem;
  flex-shrink: 0;
}

.toast-message {
  font-size: 0.95rem;
  font-weight: 500;
}

.toast-close {
  background: none;
  border: none;
  color: inherit;
  cursor: pointer;
  opacity: 0.7;
  transition: opacity 0.2s;
  padding: 0;
  line-height: 1;
}

.toast-close:hover {
  opacity: 1;
}

@keyframes slideIn {
  from {
    transform: translateX(400px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
  transform: translateX(400px);
  opacity: 0;
}
</style>
