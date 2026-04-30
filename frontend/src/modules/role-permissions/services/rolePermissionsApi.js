import api from '../../../services/api'

export async function getRolePermissions() {
  const response = await api.get('/role-permissions')
  return response.data
}

export async function createRolePermission(payload) {
  const response = await api.post('/role-permissions', payload)
  return response.data
}

export async function updateRolePermission(id, payload) {
  const response = await api.put(`/role-permissions/${id}`, payload)
  return response.data
}

export async function deleteRolePermission(id) {
  const response = await api.delete(`/role-permissions/${id}`)
  return response.data
}