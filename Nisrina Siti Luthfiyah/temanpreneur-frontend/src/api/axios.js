import axios from 'axios'
import { API_BASE_URL } from '@/config'

const apiBaseUrl = API_BASE_URL.replace(/\/+$/, '')

const api = axios.create({
  baseURL: `${apiBaseUrl}/api`,
  headers: {
    'Accept': 'application/json',
  },
  timeout: 10000,
})

// ===== REQUEST INTERCEPTOR =====
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    const activeRole = localStorage.getItem('activeRole')
    console.log('AXIOS: token=', token, 'activeRole=', activeRole)
    if (token) {
      config.headers = config.headers || {}
      config.headers['Authorization'] = `Bearer ${token}`
    }
    if (activeRole) {
      config.headers = config.headers || {}
      config.headers['X-Active-Role'] = activeRole
    }
    return config
  },
  (error) => Promise.reject(error)
)

// ===== RESPONSE INTERCEPTOR =====
api.interceptors.response.use(
  (response) => response,
  (error) => {

    console.log('API ERROR:', {
      url: error.config?.url,
      status: error.response?.status,
      data: error.response?.data
    })

    // Handle connection errors
    if (!error.response) {
      if (error.code === 'ERR_NETWORK' || error.message === 'Network Error') {
        const connectionError = new Error('Server Backend Belum Dijalankan')
        connectionError.isConnectionError = true
        connectionError.originalError = error
        connectionError.status = 0
        return Promise.reject(connectionError)
      }
    }

    // Handle authentication errors (401)
    if (error.response?.status === 401) {
      const message = error.response?.data?.message || 'Session Anda telah berakhir'
      const authError = new Error(message)
      authError.status = 401
      authError.isAuthError = true
      
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      
      if (window.location.pathname !== '/login') {
        window.location.href = '/login?expired=true'
      }
      
      return Promise.reject(authError)
    }

    // Handle forbidden errors (403)
    if (error.response?.status === 403) {
      const message = error.response?.data?.message || 'Anda tidak memiliki akses ke resource ini'
      const forbiddenError = new Error(message)
      forbiddenError.status = 403
      forbiddenError.isForbiddenError = true
      return Promise.reject(forbiddenError)
    }

    // Handle validation errors (422)
    if (error.response?.status === 422) {
      const message = error.response?.data?.message || 'Validasi gagal'
      const validationError = new Error(message)
      validationError.status = 422
      validationError.response = error.response
      validationError.errors = error.response?.data?.errors || null
      return Promise.reject(validationError)
    }

    // Handle not found errors (404)
    if (error.response?.status === 404) {
      const message =
        error.response?.data?.message ||
        'Endpoint tidak ditemukan (cek route backend)'

      const notFoundError = new Error(message)
      notFoundError.status = 404
      notFoundError.isNotFoundError = true
      notFoundError.response = error.response

      return Promise.reject(notFoundError)
    }

    // Handle not found errors (404)
    if (error.response?.status === 404) {
      const notFoundError = new Error(error.response?.data?.message || 'Resource tidak ditemukan')
      notFoundError.status = 404
      notFoundError.isNotFoundError = true
      return Promise.reject(notFoundError)
    }

    // Handle server errors (500)
    if (error.response?.status === 500) {
      const serverError = new Error(error.response?.data?.message || 'Terjadi kesalahan pada server')
      serverError.status = 500
      serverError.isServerError = true
      return Promise.reject(serverError)
    }

    // Handle other errors
    const genericError = new Error(
      error.response?.data?.message || 
      error.message || 
      'Terjadi kesalahan saat memproses permintaan'
    )
    genericError.status = error.response?.status || 0
    genericError.response = error.response
    
    return Promise.reject(genericError)
  }
)

export default api

