import { setAuthToken } from './api'

const TOKEN_KEY = 'access_token'

export function getStoredToken() {
  return localStorage.getItem(TOKEN_KEY) || ''
}

export function saveToken(token) {
  if (!token) {
    return
  }

  localStorage.setItem(TOKEN_KEY, token)
  setAuthToken(token)
}

export function clearToken() {
  localStorage.removeItem(TOKEN_KEY)
  setAuthToken('')
}

export function initAuth() {
  const token = getStoredToken()
  if (token) {
    setAuthToken(token)
  }
}

export function isAuthenticated() {
  return Boolean(getStoredToken())
}