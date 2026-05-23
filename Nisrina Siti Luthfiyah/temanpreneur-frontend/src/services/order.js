/**
 * Order Service
 * Menangani semua API calls terkait pesanan (checkout dan riwayat)
 */

import api from '@/api/axios.js'

export const orderService = {
  /**
   * Place Order (Checkout)
   * PENTING: Ini menyimpan pesanan ke database secara permanen
   * dan akan muncul di halaman "Riwayat Pesanan"
   * 
   * @param {Object} checkoutData - Data checkout
   *   {
   *     cart_items: [{ product_id, quantity, price, subtotal }],
   *     shipping_address: string,
   *     shipping_phone: string,
   *     shipping_name: string,
   *     payment_method: string (default: 'cod')
   *   }
   * @returns {Promise<Object>} Created order data
   */
  async placeOrder(checkoutData) {
    try {
      // Validasi data checkout
      if (!checkoutData.cart_items || checkoutData.cart_items.length === 0) {
        throw new Error('Keranjang belanja kosong')
      }

      if (!checkoutData.shipping_address || !checkoutData.shipping_phone) {
        throw new Error('Alamat pengiriman tidak lengkap')
      }

      const response = await api.post('/orders', checkoutData)
      
      return {
        success: true,
        order: response.data?.data || response.data,
        message: 'Pesanan berhasil dibuat'
      }
    } catch (error) {
      console.error('Place order error:', error)
      
      // Return error dengan pesan yang user-friendly
      const errorMessage = error.response?.data?.message || 
                          error.message || 
                          'Gagal membuat pesanan. Silakan coba lagi.'
      
      return {
        success: false,
        message: errorMessage,
        error: error
      }
    }
  },

  /**
   * Fetch semua order user yang logged in
   * Digunakan untuk menampilkan "Riwayat Pesanan"
   * 
   * @param {Object} params - Query parameters
   *   { page: 1, limit: 10, status: 'diproses' }
   * @returns {Promise<Object>} { data: [...], pagination: {...} }
   */
  async getOrders(params = {}) {
    try {
      const response = await api.get('/orders', { params })
      return response.data
    } catch (error) {
      console.error('Get orders error:', error)
      throw error
    }
  },

  /**
   * Fetch order detail berdasarkan ID
   * @param {number} orderId - Order ID
   * @returns {Promise<Object>} Order detail dengan items
   */
  async getOrderDetail(orderId) {
    try {
      const response = await api.get(`/orders/${orderId}`)
      return response.data?.data || response.data
    } catch (error) {
      console.error(`Get order detail (${orderId}) error:`, error)
      throw error
    }
  },

  /**
   * Get tracking history untuk pesanan
   * @param {number} orderId - Order ID
   * @returns {Promise<Array>} List tracking events
   */
  async getOrderTracking(orderId) {
    try {
      const response = await api.get(`/orders/${orderId}/tracking`)
      return response.data?.data || response.data || []
    } catch (error) {
      console.error(`Get order tracking (${orderId}) error:`, error)
      throw error
    }
  },

  /**
   * Update status pesanan (Admin/Seller only)
   * @param {number} orderId - Order ID
   * @param {Object} data - { status: 'diproses' | 'dikemas' | 'diantarkan' | 'selesai' }
   * @returns {Promise<Object>} Updated order
   */
  async updateOrderStatus(orderId, data) {
    try {
      const response = await api.put(`/orders/${orderId}/status`, data)
      return response.data
    } catch (error) {
      console.error(`Update order status (${orderId}) error:`, error)
      throw error
    }
  },

  /**
   * Cancel pesanan
   * Hanya bisa di-cancel jika status masih 'diproses'
   * @param {number} orderId - Order ID
   * @param {Object} data - { reason: string }
   * @returns {Promise<Object>} Response
   */
  async cancelOrder(orderId, data = {}) {
    try {
      const response = await api.patch(`/orders/${orderId}/status`, {
        status: 'dibatalkan',
        ...data,
      })
      return response.data
    } catch (error) {
      console.error(`Cancel order (${orderId}) error:`, error)
      throw error
    }
  },

  /**
   * Get order statistics (Dashboard Seller)
   * @returns {Promise<Object>} Stats { total, pending, completed, revenue }
   */
  async getOrderStats() {
    try {
      const response = await api.get('/orders/stats')
      return response.data?.data || response.data
    } catch (error) {
      console.error('Get order stats error:', error)
      throw error
    }
  },

  /**
   * Export order ke CSV (Admin/Seller)
   * @param {Object} params - Filter params
   * @returns {Promise<Blob>} CSV file
   */
  async exportOrders(params = {}) {
    try {
      const response = await api.get('/orders/export', {
        params,
        responseType: 'blob'
      })
      return response.data
    } catch (error) {
      console.error('Export orders error:', error)
      throw error
    }
  }
}

export default orderService
