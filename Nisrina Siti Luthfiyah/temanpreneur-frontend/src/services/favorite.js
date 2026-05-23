/**
 * Favorite Service
 * Menangani semua API calls terkait favorit produk
 */

import api from '@/api/axios.js'

export const favoriteService = {
  /**
   * Fetch semua favorit user yang logged in
   * @returns {Promise<Array>} List produk favorit
   */
  async getFavorites() {
    try {
      const response = await api.get('/favorites')
      return response.data?.data || response.data || []
    } catch (error) {
      console.error('Get favorites error:', error)
      throw error
    }
  },

  /**
   * Check apakah produk sudah di-favorite
   * @param {number} productId - Product ID
   * @returns {Promise<boolean>} True jika sudah di-favorite
   */
  async isFavorite(productId) {
    try {
      const response = await api.get(`/favorites/check/${productId}`)
      return response.data?.is_favorite || false
    } catch (error) {
      console.error(`Check favorite for product ${productId} error:`, error)
      return false
    }
  },

  /**
   * Add produk ke favorit
   * PENTING: Ini menyimpan data ke database secara permanen
   * @param {number} productId - Product ID
   * @returns {Promise<Object>} Response { message, is_favorited }
   */
  async addToFavorite(productId) {
    try {
      const response = await api.post('/favorites', {
        product_id: productId
      })
      return response.data
    } catch (error) {
      console.error(`Add to favorite (product ${productId}) error:`, error)
      
      // Handle specific error cases
      if (error.response?.status === 409) {
        // Already favorited
        return {
          success: true,
          message: 'Produk sudah dalam daftar favorit',
          is_duplicate: true
        }
      }
      
      throw error
    }
  },

  /**
   * Remove produk dari favorit
   * @param {number} productId - Product ID
   * @returns {Promise<Object>} Response { message }
   */
  async removeFromFavorite(productId) {
    try {
      const response = await api.delete(`/favorites/${productId}`)
      return response.data
    } catch (error) {
      console.error(`Remove from favorite (product ${productId}) error:`, error)
      throw error
    }
  },

  /**
   * Toggle favorit status (add atau remove)
   * Helper function untuk simplify UI logic
   * @param {number} productId - Product ID
   * @param {boolean} isFavoritedNow - Current favorite status
   * @returns {Promise<Object>} Response
   */
  async toggleFavorite(productId, isFavoritedNow = false) {
    try {
      if (isFavoritedNow) {
        return await this.removeFromFavorite(productId)
      } else {
        return await this.addToFavorite(productId)
      }
    } catch (error) {
      console.error(`Toggle favorite (product ${productId}) error:`, error)
      throw error
    }
  },

  /**
   * Get count favorit dari user
   * @returns {Promise<number>} Jumlah favorit
   */
  async getFavoriteCount() {
    try {
      const response = await api.get('/favorites/count')
      return response.data?.count || 0
    } catch (error) {
      console.error('Get favorite count error:', error)
      return 0
    }
  }
}

export default favoriteService
