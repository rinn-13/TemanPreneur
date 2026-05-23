import api from '@/api/axios.js'

// Blog API Service
export const blogService = {
  async getBlogs({ category, search, page = 1 } = {}) {
    try {
      const params = new URLSearchParams({ page: page.toString() });
      if (category) params.append('category', category);
      if (search && String(search).trim()) params.append('search', String(search).trim());
      
      const response = await api.get(`/blogs?${params}`);
      return { success: true, data: response.data.data || response.data };
    } catch (error) {
      console.error('Get blogs error:', error);
      return { success: false, data: [] };
    }
  },

  async getBlog(slug) {
    try {
      const response = await api.get(`/blogs/${slug}`);
      return { success: true, data: response.data.data || response.data };
    } catch (error) {
      console.error('Get blog error:', error);
      return { success: false, data: null };
    }
  },

  // Seller methods
  async getSellerBlogs({ page = 1 } = {}) {
    try {
      const params = new URLSearchParams({ page: page.toString() });
      const response = await api.get(`/seller/blogs?${params}`);
      return { success: true, data: response.data };
    } catch (error) {
      console.error('Get seller blogs error:', error);
      return { success: false, data: { data: [], last_page: 1 } };
    }
  },

  async getBlogsByBusiness(businessId) {
    try {
      if (!businessId || String(businessId).trim() === '') {
        console.error('Invalid businessId:', businessId)
        return { success: false, data: [] }
      }

      const target = String(businessId).trim()
      const endpoint = isNaN(Number(target)) ? target : parseInt(target)
      const response = await api.get(`/businesses/${endpoint}/blogs`)
      return { success: true, data: response.data.data || response.data }
    } catch (error) {
      console.error('Get blogs by business error:', error)
      return { success: false, data: [] }
    }
  },

  async createBlog(data) {
    try {
      const response = await api.post('/seller/blogs', data, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      return { success: true, data: response.data };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || error.message || 'Gagal membuat blog',
        errors: error.response?.data?.errors || null,
      };
    }
  },

  async updateBlog(id, data) {
    try {
      const response = await api.put(`/seller/blogs/${id}`, data, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      return { success: true, data: response.data };
    } catch (error) {
      return { success: false, message: error.response?.data?.message || 'Gagal update blog' };
    }
  },

  async deleteBlog(id) {
    try {
      const response = await api.delete(`/seller/blogs/${id}`);
      return { success: true, data: response.data };
    } catch (error) {
      return { success: false, message: error.response?.data?.message || 'Gagal hapus blog' };
    }
  }
};

export default blogService;

