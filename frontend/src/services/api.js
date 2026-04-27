import axios from 'axios'
import { showToast } from './toast'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  headers: {
    Accept: 'application/json',
  },
})

function getErrorMessage(error) {
  if (typeof error?.response?.data?.message === 'string') {
    return error.response.data.message
  }

  if (error?.response?.data?.errors && typeof error.response.data.errors === 'object') {
    return Object.values(error.response.data.errors).flat().join(', ')
  }

  if (error?.message) {
    return error.message
  }

  return 'Terjadi kesalahan pada request API.'
}

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error?.response?.status
    const shouldShowGlobalError = !status || status >= 500 || status === 401 || status === 403

    if (shouldShowGlobalError) {
      showToast({
        variant: 'danger',
        message: getErrorMessage(error),
      })
    }

    return Promise.reject(error)
  },
)

export function setAuthToken(token) {
  if (token) {
    api.defaults.headers.common.Authorization = `Bearer ${token}`
    return
  }

  delete api.defaults.headers.common.Authorization
}

export default api