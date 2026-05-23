import { defineStore } from 'pinia'
import api from '@/api/axios'

function getStoredUser() {
  try {
    const raw = localStorage.getItem('user')
    return raw ? normalizeUserData(JSON.parse(raw)) : null
  } catch {
    return null
  }
}

function normalizeUserData(user) {
  if (!user || typeof user !== 'object') return user

  const photo =
    user.photo ||
    user.avatar ||
    user.photo_url ||
    user.profile_picture ||
    user.profile_photo ||
    user.profile_photo_path ||
    user.image ||
    user.profileImage ||
    user.avatar_url ||
    ''

  return { ...user, photo }
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: getStoredUser(),
    token: localStorage.getItem('token') || null,
    activeRole: localStorage.getItem('activeRole') || null,
  }),

  getters: {
    isLoggedIn: (state) => !!state.token,

    role: (state) => state.activeRole || state.user?.role || null,

    roles: (state) =>
      state.user?.roles || (state.user?.role ? [state.user.role] : []),

    //  FIXED
    isBuyer: (state) => {
      const roles = state.user?.roles || []
      const active = state.activeRole

      if (active) return active === 'buyer'
      return roles.includes('buyer')
    },

    //  FIXED (INI YANG PALING PENTING)
    isSeller: (state) => {
      const roles = state.user?.roles || []
      const active = state.activeRole

      if (active) {
        return active === 'seller' || active === 'seller_premium'
      }

      return roles.includes('seller') || roles.includes('seller_premium')
    },

    isAdmin: (state) => state.user?.role === 'admin',

    business: (state) => state.user?.business || null,

    businessStatus: (state) => state.user?.business?.status || null,

    hasMultipleRoles: (state) => {
      const roles =
        state.user?.roles || (state.user?.role ? [state.user.role] : [])

      const relevant = roles.filter((x) =>
        ['buyer', 'seller', 'seller_premium'].includes(x)
      )

      return relevant.length > 1
    },
  },

  actions: {
    async login(credentials) {
      try {
        const response = await api.post('/login', credentials)

        this.token = response.data.token
        this.user = normalizeUserData(response.data.user || response.data)

        localStorage.setItem('token', this.token)
        localStorage.setItem('user', JSON.stringify(this.user))

        //  HANDLE ACTIVE ROLE
        if (this.user?.roles?.length > 1) {
          this.activeRole = null
          localStorage.removeItem('activeRole')
        } else {
          this.activeRole = this.user?.role || null
          localStorage.setItem('activeRole', this.activeRole)
        }

        api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`

        return { success: true }
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Login gagal',
          code: error.response?.data?.code,
        }
      }
    },

    async setActiveRole(role) {
      this.activeRole = role
      localStorage.setItem('activeRole', role)

      // Clear user-specific stores when switching roles
      try {
        const { useOrderStore } = await import('./orders.js')
        const orderStore = useOrderStore()
        orderStore.clearOrders()

        const { useCartStore } = await import('./cart.js')
        const cartStore = useCartStore()
        cartStore.clearCart?.()
      } catch (err) {
        console.error('Error clearing stores on role switch:', err)
      }
    },

    async register(userData) {
      try {
        const response = await api.post('/register', userData)

        this.token = response.data.token
        this.user = normalizeUserData(response.data.user || response.data)

        localStorage.setItem('token', this.token)
        localStorage.setItem('user', JSON.stringify(this.user))

        if (this.user?.roles?.length > 1) {
          this.activeRole = localStorage.getItem('activeRole')
        } else {
          this.activeRole = this.user?.role || null
          localStorage.setItem('activeRole', this.activeRole)
        }

        api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`

        return { success: true }
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Registrasi gagal',
        }
      }
    },

    async logout() {
      try {
        await api.post('/logout')
      } catch (error) {
        console.error('Logout error', error)
      } finally {
        // Clear all user-specific stores
        const { useOrderStore } = await import('./orders.js')
        const orderStore = useOrderStore()
        orderStore.clearOrders()

        const { useCartStore } = await import('./cart.js')
        const cartStore = useCartStore()
        cartStore.clearCart?.()

        const { useFavoriteStore } = await import('./favorite.js')
        const favoriteStore = useFavoriteStore()
        favoriteStore.clearFavorites?.()

        this.token = null
        this.user = null
        this.activeRole = null

        localStorage.removeItem('token')
        localStorage.removeItem('user')
        localStorage.removeItem('activeRole')

        delete api.defaults.headers.common['Authorization']
      }
    },

    async fetchUser() {
      if (!this.token) return

      try {
        const response = await api.get('/user')
        this.user = normalizeUserData(response.data.user || response.data)
        localStorage.setItem('user', JSON.stringify(this.user))
      } catch (error) {
        this.logout()
      }
    },
  },
})