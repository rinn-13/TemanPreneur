import api from '@/api/axios.js'

// Product API Service
export const productService = {
  async getProducts({ category, category_id, sort, min_price, max_price, search, page = 1, per_page } = {}) {
    try {
      const params = new URLSearchParams({ page: page.toString() });
      if (category_id) {
        params.append('category_id', category_id);
      } else if (category) {
        params.append('category', category);
      }
      if (sort) params.append('sort', sort);
      if (min_price !== undefined && min_price !== null && min_price !== '') {
        params.append('min_price', min_price);
      }
      if (max_price !== undefined && max_price !== null && max_price !== '') {
        params.append('max_price', max_price);
      }
      if (search && String(search).trim()) {
        params.append('search', String(search).trim());
      }
      if (per_page) {
        params.append('per_page', per_page);
      }
      
      const response = await api.get(`/products?${params}`);
      return {
        success: true,
        data: response.data.data || response.data,
        pagination: response.data.pagination,
        meta: response.data.meta,
      };
    } catch (error) {
      console.error('Get products error:', error);
      return { success: false, data: [] };
    }
  },

  async getProduct(id) {
    try {
      const response = await api.get(`/products/${id}`)
      return { success: true, data: response.data.data || response.data }
    } catch (error) {
      console.error('Get product error:', error)
      return { success: false, data: null }
    }
  },

  async createProduct(data) {
    try {
      const response = await api.post('/products', data, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      return { success: true, data: response.data }
    } catch (error) {
      return { success: false, message: error.response?.data?.message || 'Gagal membuat produk' }
    }
  },

  async getSellerProducts() {
    try {
      const response = await api.get('/seller/products')
      return { success: true, data: response.data.data || response.data }
    } catch (error) {
      console.error('Get seller products error:', error)
      return { success: false, data: [] }
    }
  },

  async getProductsByBusiness(businessId, { search, sort, page = 1, per_page } = {}) {
    try {
      if (!businessId || String(businessId).trim() === '') {
        console.error('Invalid businessId:', businessId)
        return { success: false, data: [] }
      }

      const target = String(businessId).trim()
      const endpoint = isNaN(Number(target)) ? target : parseInt(target)
      const params = new URLSearchParams()
      params.append('page', page.toString())
      if (search) params.append('search', search)
      if (sort) params.append('sort', sort)
      if (per_page) params.append('per_page', per_page)

      const response = await api.get(`/businesses/${endpoint}/products?${params}`)
      return { success: true, data: response.data.data || response.data }
    } catch (error) {
      console.error('Get products by business error:', error)
      return { success: false, data: [] }
    }
  },

  async updateProduct(id, data) {
    try {
      const response = await api.put(`/products/${id}`, data, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      return { success: true, data: response.data }
    } catch (error) {
      return { success: false, message: error.response?.data?.message || 'Gagal update produk' }
    }
  },

  async deleteProduct(id) {
    try {
      const response = await api.delete(`/products/${id}`)
      return { success: true, data: response.data }
    } catch (error) {
      return { success: false, message: error.response?.data?.message || 'Gagal hapus produk' }
    }
  }
}

export default productService

