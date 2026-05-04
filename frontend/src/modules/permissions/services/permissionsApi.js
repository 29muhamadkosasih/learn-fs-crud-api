import axios from 'axios'

const API_URL = '/api/permissions'

const api = axios.create({
    baseURL: API_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
})

// Add token to every request
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('access_token')
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
})

export async function getPermissions() {
    try {
        const response = await api.get('')
        return response.data
    } catch (error) {
        throw error.response?.data || error
    }
}

export async function createPermission(data) {
    try {
        const response = await api.post('', data)
        return response.data
    } catch (error) {
        throw error.response?.data || error
    }
}

export async function updatePermission(id, data) {
    try {
        const response = await api.put(`/${id}`, data)
        return response.data
    } catch (error) {
        throw error.response?.data || error
    }
}

export async function deletePermission(id) {
    try {
        const response = await api.delete(`/${id}`)
        return response.data
    } catch (error) {
        throw error.response?.data || error
    }
}
