import api from '@/api/axios.js'

// Business API Service
export const businessService = {
  async getBusinesses({ status, limit, id } = {}) {
    try {
      if (id) return await this.getBusiness(id)
      
      const params = new URLSearchParams()
      if (status) params.append('status', status)
      if (limit) params.append('limit', limit)
      
      const response = await api.get(`/businesses?${params}`)
      return { success: true, data: response.data.data || response.data }
    } catch (error) {
      console.error('Get businesses error:', error)
      return { success: false, data: [] }
    }
  },

  async getBusiness(id) {
    try {
      if (!id || String(id).trim() === '') {
        console.error('Invalid business id:', id)
        return { success: false, data: null }
      }

      const target = String(id).trim()
      const endpoint = isNaN(Number(target)) ? target : parseInt(target)
      const response = await api.get(`/businesses/${endpoint}`)
      return { success: true, data: response.data.data || response.data }
    } catch (error) {
      console.error('Get business error:', error)
      return { success: false, data: null }
    }
  },

  async getDashboard() {
    try {
      const response = await api.get('/businesses/dashboard')
      return { success: true, data: response.data }
    } catch (error) {
      console.error('Get dashboard error:', error)
      return { success: false, data: null }
    }
  },

  async getProducts() {
    try {
      const response = await api.get('/businesses/products')
      return { success: true, data: response.data.data || response.data }
    } catch (error) {
      console.error('Get business products error:', error)
      return { success: false, data: [] }
    }
  },

  async getSettings() {
    try {
      const response = await api.get('/businesses/me')
      return { success: true, data: response.data.data || response.data }
    } catch (error) {
      if (error.response?.status === 404) {
        try {
          const fallback = await api.get('/business/pengaturan')
          return { success: true, data: fallback.data.data || fallback.data }
        } catch (fallbackError) {
          console.error('Fallback business settings error:', fallbackError)
        }
      }
      console.error('Get business settings error:', error)
      return { success: false, data: null, message: error.response?.data?.message || 'Gagal memuat data toko' }
    }
  },

  async createBusiness(data) {
    try {
      const response = await api.post('/businesses', data)
      return {
        success: true,
        data: response.data?.data || response.data,
      }
    } catch (error) {
      return { success: false, message: error.response?.data?.message || 'Gagal membuat usaha' }
    }
  },

  async getMyBusiness() {
    try {
      const response = await api.get('/businesses')
      const raw = response.data?.data
      // Seller: backend returns a single business object in `data`. Admin: array of businesses.
      const business = Array.isArray(raw) ? raw[0] ?? null : raw ?? null
      console.debug('[business] getMyBusiness', { raw, business })
      return { success: true, data: business }
    } catch (error) {
      console.error('getMyBusiness error', error.response?.status, error.response?.data)
      return this.getSettings()
    }
  },

  async getSellerProfile() {
    try {
      const response = await api.get('/seller/profile')
      const data = response.data?.data || {}
      return {
        success: true,
        data: data.business || null,
        profile: data.profile || null,
        stats: data.stats || null,
      }
    } catch (error) {
      console.error('Get seller profile error:', error)
      return { success: false, data: null, profile: null, stats: null, message: error.response?.data?.message || 'Gagal memuat profil seller' }
    }
  },

  async updateBusiness(businessId, data) {
    const id = parseInt(businessId, 10)
    if (!id || Number.isNaN(id)) {
      return { success: false, message: 'ID toko tidak valid' }
    }
    const formData = new FormData();
    Object.keys(data).forEach(key => {
      if (data[key] !== null && data[key] !== undefined) {
        formData.append(key, data[key]);
      }
    });

    try {
      const response = await api.put(`/businesses/${id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      return {
        success: true,
        data: response.data?.data || response.data,
      };
    } catch (error) {
      return { success: false, message: error.response?.data?.message || 'Gagal update toko' };
    }
  },

  async getAllBusinesses(params = {}) {
    try {
      const response = await api.get('/businesses/all', { params })
      return {
        success: true,
        data: response.data?.data || response.data || []
      }
    } catch (error) {
      console.error('Get all businesses error:', error)
      return { success: false, data: [] }
    }
  },

  /**
   * Admin: Block business
   */
  async blockBusiness(businessId, reason = '') {
    try {
      const response = await api.post(`/admin/businesses/${businessId}/block`, { reason });
      return { success: true, data: response.data };
    } catch (error) {
      console.error('Block error:', error);
      return { success: false, data: null };
    }
  },

  /**
   * Admin: Unblock business
   */
  async unblockBusiness(businessId) {
    try {
      const response = await api.post(`/admin/businesses/${businessId}/unblock`);
      return { success: true, data: response.data };
    } catch (error) {
      console.error('Unblock error:', error);
      return { success: false, data: null };
    }
  }
}

export default businessService

