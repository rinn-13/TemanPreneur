import api from '@/api/axios'

export const sellerDashboardService = {
  /**
   * Get seller dashboard with all stats
   */
  async getDashboard() {
    try {
      const response = await api.get('/seller/dashboard')
      return {
        success: true,
        data: response.data.data,
      }
    } catch (error) {
      console.error('Dashboard fetch error:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to fetch dashboard',
      }
    }
  },

  /**
   * Get detailed analytics for seller
   */
  async getAnalytics(period = '30days', startDate = null, endDate = null) {
    try {
      const params = { period }
      if (startDate) params.start_date = startDate
      if (endDate) params.end_date = endDate

      const response = await api.get('/seller/analytics', { params })
      return {
        success: true,
        data: response.data.data,
      }
    } catch (error) {
      console.error('Analytics fetch error:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to fetch analytics',
      }
    }
  },

  /**
   * Get revenue tracking data
   */
  async getRevenue() {
    try {
      const response = await api.get('/seller/revenue')
      return {
        success: true,
        data: response.data.data,
      }
    } catch (error) {
      console.error('Revenue fetch error:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to fetch revenue data',
      }
    }
  },

  /**
   * Get seller orders with filtering
   */
  async getOrders(status = null, page = 1, perPage = 15) {
    try {
      const params = { page, per_page: perPage }
      if (status) params.status = status

      const response = await api.get('/seller/orders', { params })
      return {
        success: true,
        data: response.data.data,
        pagination: response.data.pagination,
      }
    } catch (error) {
      console.error('Orders fetch error:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to fetch orders',
      }
    }
  },
}

export default sellerDashboardService
