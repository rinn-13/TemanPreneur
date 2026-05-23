import axios from 'axios'
import { API_BASE_URL } from '@/config'

const apiBaseUrl = API_BASE_URL.replace(/\/+$/, '')
const api = axios.create({
  baseURL: `${apiBaseUrl}/api`
})

// interceptor token
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers = config.headers || {}
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

export default api