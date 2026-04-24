<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'success',
  },
  title: {
    type: String,
    default: '',
  },
  message: {
    type: String,
    default: '',
  },
  items: {
    type: Array,
    default: () => [],
  },
  dismissible: {
    type: Boolean,
    default: true,
  },
  timeout: {
    type: Number,
    default: 5000,
  },
})

const emit = defineEmits(['close'])

const timerId = ref(null)

const wrapperClass = computed(() => 'toast show')

const wrapperStyle = computed(() => ({
  top: '1rem',
  right: '1rem',
  zIndex: 1080,
  width: 'min(420px, calc(100vw - 2rem))',
}))

const iconClass = computed(() => {
  if (props.variant === 'danger') return 'bg-danger'
  if (props.variant === 'secondary') return 'bg-secondary'
  if (props.variant === 'warning') return 'bg-warning'
  if (props.variant === 'info') return 'bg-info'
  return 'bg-success'
})

const defaultTitle = computed(() => {
  if (props.title) return props.title
  if (props.variant === 'danger') return 'Terjadi kesalahan'
  if (props.variant === 'secondary') return 'Info'
  return 'Berhasil'
})

const hasAutoClose = computed(() => props.dismissible && props.timeout > 0)

const progressStyle = computed(() => ({
  animationDuration: `${props.timeout}ms`,
}))

function closeBanner() {
  emit('close')
}

onMounted(() => {
  if (!props.dismissible || props.timeout <= 0) {
    return
  }

  timerId.value = window.setTimeout(() => {
    closeBanner()
  }, props.timeout)
})

onBeforeUnmount(() => {
  if (timerId.value) {
    window.clearTimeout(timerId.value)
    timerId.value = null
  }
})
</script>

<template>
  <div class="page-toast position-fixed" :class="wrapperClass" :style="wrapperStyle" role="alert">
    <div class="toast-card bg-white rounded-lg shadow border-left" :class="`border-left-${variant}`">
      <div class="d-flex align-items-start p-3">
        <div class="mr-3">
          <div class="icon-circle" :class="iconClass">
            <i class="fas" :class="variant === 'danger' ? 'fa-exclamation-triangle' : 'fa-check'"></i>
          </div>
        </div>

        <div class="flex-fill pr-2">
          <div class="font-weight-bold text-gray-900 mb-1">{{ defaultTitle }}</div>
          <div v-if="message">{{ message }}</div>
          <ul v-if="items.length" class="mb-0 pl-3">
            <li v-for="(item, index) in items" :key="index">{{ item }}</li>
          </ul>

          <div v-if="hasAutoClose" class="d-flex align-items-center text-muted small mt-2">
          </div>
        </div>

        <button v-if="dismissible" type="button" class="close ml-3" aria-label="Close" @click="closeBanner">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div v-if="hasAutoClose" class="toast-progress">
        <div class="toast-progress-bar" :class="`bg-${variant}`" :style="progressStyle"></div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.toast-progress {
  height: 3px;
  background: rgba(0, 0, 0, 0.06);
  overflow: hidden;
  border-bottom-left-radius: 0.5rem;
  border-bottom-right-radius: 0.5rem;
}

.toast-progress-bar {
  width: 100%;
  height: 100%;
  transform-origin: left center;
  animation-name: toast-shrink;
  animation-timing-function: linear;
  animation-fill-mode: forwards;
}

@keyframes toast-shrink {
  from {
    transform: scaleX(1);
  }
  to {
    transform: scaleX(0);
  }
}
</style>
