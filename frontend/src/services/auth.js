import { setAuthToken } from './api'

const TOKEN_KEY = 'access_token'
const USER_KEY = 'user_data'

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

export function saveUserData(userData) {
  if (!userData) {
    return
  }

  localStorage.setItem(USER_KEY, JSON.stringify(userData))
}

export function getStoredUserData() {
  const data = localStorage.getItem(USER_KEY)
  try {
    return data ? JSON.parse(data) : null
  } catch {
    return null
  }
}

export function getUserRole() {
  const userData = getStoredUserData()
  return userData?.role || 'user'
}

export function clearToken() {
  localStorage.removeItem(TOKEN_KEY)
  localStorage.removeItem(USER_KEY)
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