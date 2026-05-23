import api from '@/api/axios.js'

// Auth API Service - with try/catch + fallback
export const authService = {
  async login(email, password) {
    try {
      const response = await api.post('/login', { email, password })
      const { user, token } = response.data
      
      // Save to localStorage
      localStorage.setItem('token', token)
      localStorage.setItem('user', JSON.stringify(user))
      
      return { success: true, user, token }
    } catch (error) {
      console.error('Login error:', error)
      return { success: false, message: error.response?.data?.message || 'Login gagal' }
    }
  },

  async register(data) {
    try {
      const response = await api.post('/register', data)
      const { user, token } = response.data
      
      localStorage.setItem('token', token)
      localStorage.setItem('user', JSON.stringify(user))
      
      return { success: true, user, token }
    } catch (error) {
      return { success: false, message: error.response?.data?.message || 'Registrasi gagal' }
    }
  },

  async logout() {
    try {
      await api.post('/logout')
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    }
  },

  async getUser() {
    try {
      const response = await api.get('/user')
      const user = response.data.user || response.data
      localStorage.setItem('user', JSON.stringify(user))
      return { success: true, user }
    } catch (error) {
      console.error('Get user error:', error)
      localStorage.removeItem('user')
      return { success: false, user: null }
    }
  },

  isAuthenticated() {
    const token = localStorage.getItem('token')
    const user = localStorage.getItem('user')
    return !!(token && user)
  }
}

export default authService

