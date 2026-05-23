import api from '@/api/axios.js'
import { useAuthStore } from '@/stores/auth.js'

// Team API Service
export const teamService = {
  async getTeam(businessId) {
    try {
      const response = await api.get(`/business/${businessId}/team`)
      return { success: true, data: response.data.data || response.data }
    } catch (error) {
      console.error('Get team error:', error)
      return { success: false, data: [] }
    }
  },

  async addTeamMember(businessId, data) {
    try {
      const response = await api.post(`/business/${businessId}/team`, data)
      return { success: true, data: response.data }
    } catch (error) {
      return { success: false, message: error.response?.data?.message || 'Gagal tambah anggota tim' }
    }
  },

  async updateTeamMember(businessId, memberId, data) {
    try {
      const response = await api.put(`/business/${businessId}/team/${memberId}`, data)
      return { success: true, data: response.data }
    } catch (error) {
      return { success: false, message: error.response?.data?.message || 'Gagal update anggota tim' }
    }
  },

  async removeTeamMember(businessId, memberId) {
    try {
      const response = await api.delete(`/business/${businessId}/team/${memberId}`)
      return { success: true, data: response.data }
    } catch (error) {
      return { success: false, message: error.response?.data?.message || 'Gagal hapus anggota tim' }
    }
  }
}

export default teamService

