import api from '../../../services/api'

export async function getCourses(page = 1) {
  const response = await api.get('/courses', { params: { page } })
  return response.data
}

export async function getCourseById(id) {
  const response = await api.get(`/courses/${id}`)
  return response.data
}

export async function createCourse(payload) {
  const response = await api.post('/courses', payload)
  return response.data
}

export async function updateCourse(id, payload) {
  const response = await api.put(`/courses/${id}`, payload)
  return response.data
}

export async function deleteCourse(id) {
  const response = await api.delete(`/courses/${id}`)
  return response.data
}
