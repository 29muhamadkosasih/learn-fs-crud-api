import api from '../../../services/api'

export async function getBooks(page = 1, perPage = 10) {
  const response = await api.get('/books', { params: { page, per_page: perPage } })
  return response.data
}

export async function getBookById(id) {
  const response = await api.get(`/books/${id}`)
  return response.data
}

export async function createBook(payload) {
  const response = await api.post('/books', payload, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })
  return response.data
}

export async function updateBook(id, payload) {
  payload.append('_method', 'PUT')
  const response = await api.post(`/books/${id}`, payload, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })
  return response.data
}

export async function deleteBook(id) {
  const response = await api.delete(`/books/${id}`)
  return response.data
}
