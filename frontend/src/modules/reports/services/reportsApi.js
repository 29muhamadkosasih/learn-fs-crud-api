import api from '../../../services/api'

export async function getReports() {
  const response = await api.get('/reports')
  return response.data
}