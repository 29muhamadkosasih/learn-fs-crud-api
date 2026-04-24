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
