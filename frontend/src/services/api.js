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

    const shouldShowGlobalError = !status || status >= 500

    // Show global error for server errors or when no status
    if (shouldShowGlobalError) {
      showToast({
        variant: 'danger',
        message: getErrorMessage(error),
      })
      return Promise.reject(error)
    }

    // If unauthorized / forbidden / not found, clear auth and redirect to login
    if (status === 401 || status === 403 || status === 404) {
      try {
        localStorage.removeItem('access_token')
        localStorage.removeItem('user_data')
      } catch (e) {
        // ignore
      }

      delete api.defaults.headers.common.Authorization

      showToast({
        variant: 'danger',
        message: getErrorMessage(error),
      })

      // Force navigation to login page
      try {
        window.location.href = '/login'
      } catch (e) {
        // fallback: do nothing
      }

      return Promise.reject(error)
    }

    // Other client errors: show message and reject
    showToast({
      variant: 'danger',
      message: getErrorMessage(error),
    })

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