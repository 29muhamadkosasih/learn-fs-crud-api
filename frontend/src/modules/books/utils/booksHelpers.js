export function getErrorMessage(error) {
  if (typeof error?.response?.data?.message === 'string') {
    return error.response.data.message
  }

  if (error?.response?.data && typeof error.response.data === 'object') {
    return Object.values(error.response.data).flat().join(', ')
  }

  return 'Terjadi kesalahan pada request.'
}

export function getErrorList(error) {
  const apiErrors = error?.response?.data?.errors

  if (apiErrors && typeof apiErrors === 'object') {
    return Object.values(apiErrors).flat().filter(Boolean)
  }

  if (error?.response?.data && typeof error.response.data === 'object') {
    return Object.values(error.response.data).flat().filter((item) => typeof item === 'string')
  }

  const fallback = getErrorMessage(error)
  return fallback ? [fallback] : []
}

export function mapPaginatedBooks(payload) {
  const paginator = payload?.data

  if (paginator && Array.isArray(paginator.data)) {
    return {
      items: paginator.data,
      currentPage: paginator.current_page || 1,
      lastPage: paginator.last_page || 1,
      total: paginator.total || paginator.data.length,
      message: payload?.message || '',
    }
  }

  const items = Array.isArray(payload) ? payload : []
  return {
    items,
    currentPage: 1,
    lastPage: 1,
    total: items.length,
    message: payload?.message || '',
  }
}

export function mapBookDetail(payload) {
  return payload?.data || null
}
