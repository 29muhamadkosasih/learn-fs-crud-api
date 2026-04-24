import api from '../../../services/api'

export async function getProducts(page = 1) {
  const response = await api.get('/products', { params: { page } })
  return response.data
}

export async function getProductById(id) {
  const response = await api.get(`/products/${id}`)
  return response.data
}

export async function createProduct(payload) {
  const response = await api.post('/products', payload)
  return response.data
}

export async function updateProduct(id, payload) {
  const response = await api.put(`/products/${id}`, payload)
  return response.data
}

export async function deleteProduct(id) {
  const response = await api.delete(`/products/${id}`)
  return response.data
}
