import { reactive } from 'vue'

const DEFAULT_DURATION = 4000

export const toastState = reactive({
  message: '',
  variant: 'success',
  duration: DEFAULT_DURATION,
})

export function showToast(options = {}) {
  const {
    message = '',
    variant = 'success',
    duration = DEFAULT_DURATION,
  } = options

  if (!message) {
    return
  }

  toastState.message = message
  toastState.variant = variant
  toastState.duration = duration
}

export function hideToast() {
  toastState.message = ''
}
