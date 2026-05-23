/**
 * Category Service
 * Menangani semua API calls terkait kategori produk
 */

import api from '@/api/axios.js'

export const categoryService = {
  /**
   * Fetch semua kategori aktif dari server
   * Dengan error handling dan retry logic
   * @returns {Promise<Array>} List kategori
   */
  async getCategories() {
    try {
      const response = await api.get('/categories')
      return response.data?.data || response.data || []
    } catch (error) {
      console.error('Get categories error:', error)
      throw error
    }
  },

  /**
   * Fetch kategori dengan pagination
   * @param {number} page - Halaman ke berapa (default 1)
   * @param {number} limit - Jumlah item per halaman (default 10)
   * @returns {Promise<Object>} { data: [], pagination: {...} }
   */
  async getCategoriesPaginated(page = 1, limit = 10) {
    try {
      const response = await api.get('/categories', {
        params: { page, limit }
      })
      return response.data
    } catch (error) {
      console.error('Get categories paginated error:', error)
      throw error
    }
  },

  /**
   * Fetch kategori berdasarkan slug
   * @param {string} slug - URL-friendly kategori identifier
   * @returns {Promise<Object>} Category detail dengan metadata
   */
  async getCategoryBySlug(slug) {
    try {
      const response = await api.get(`/categories/${slug}`)
      return response.data?.data || response.data
    } catch (error) {
      console.error(`Get category by slug (${slug}) error:`, error)
      throw error
    }
  },

  /**
   * Get kategori dengan cache (jika ada)
   * @param {number} cacheTime - Cache time dalam milliseconds (default 5 menit)
   * @returns {Promise<Array>} List kategori
   */
  async getCategoriesCached(cacheTime = 5 * 60 * 1000) {
    const cacheKey = 'categories_cache'
    const cached = localStorage.getItem(cacheKey)
    
    if (cached) {
      try {
        const { data, timestamp } = JSON.parse(cached)
        if (Date.now() - timestamp < cacheTime) {
          return data
        }
      } catch (e) {
        console.warn('Cache parse error:', e)
      }
    }

    // Fetch fresh data
    const data = await this.getCategories()
    
    // Save to cache
    localStorage.setItem(cacheKey, JSON.stringify({
      data,
      timestamp: Date.now()
    }))

    return data
  }
}

export default categoryService
