import { reactive } from 'vue'

let nextId = 1

export const alertState = reactive({
  items: [],
})

export function pushAlert(message, type = 'success', duration = 3000) {
  if (!message) {
    return
  }

  const id = nextId++
  alertState.items.push({ id, message, type })

  if (duration > 0) {
    setTimeout(() => removeAlert(id), duration)
  }
}

export function removeAlert(id) {
  const index = alertState.items.findIndex((item) => item.id === id)
  if (index !== -1) {
    alertState.items.splice(index, 1)
  }
}

export function alertSuccess(message) {
  pushAlert(message, 'success', 2500)
}

export function alertError(message) {
  pushAlert(message, 'danger', 4500)
}
