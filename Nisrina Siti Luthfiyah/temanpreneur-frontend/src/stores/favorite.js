/**
 * Favorites Store (Pinia)
 * Mengelola state favorit produk dengan persistence ke database
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import favoriteService from '@/services/favorite'

export const useFavoriteStore = defineStore('favorite', () => {
  // ===== STATE =====
  const favorites = ref([]) // Array of product IDs atau full product objects
  const favoritedProductIds = ref(new Set()) // Set untuk quick lookup
  const loading = ref(false)
  const error = ref(null)

  // ===== COMPUTED =====
  const favoriteCount = computed(() => favoritedProductIds.value.size)
  
  const isFavorite = (productId) => {
    return favoritedProductIds.value.has(productId)
  }

  // ===== ACTIONS =====
  
  /**
   * Fetch semua favorit dari server
   * Called saat app initialization atau user login
   */
  async function fetchFavorites() {
    loading.value = true
    error.value = null
    
    try {
      const data = await favoriteService.getFavorites()
      favorites.value = data
      
      // Build Set untuk quick lookup
      favoritedProductIds.value.clear()
      data.forEach(item => {
        const productId = item.product_id || item.id
        favoritedProductIds.value.add(productId)
      })
    } catch (err) {
      console.error('Fetch favorites error:', err)
      error.value = err.message || 'Gagal memuat favorit'
    } finally {
      loading.value = false
    }
  }

  /**
   * Add produk ke favorit
   * Menyimpan ke database via API
   */
  async function addFavorite(productId) {
    if (favoritedProductIds.value.has(productId)) {
      return { success: true, message: 'Sudah dalam favorit' }
    }

    loading.value = true
    error.value = null
    
    try {
      const result = await favoriteService.addToFavorite(productId)
      
      // Add ke state jika belum ada
      if (!favoritedProductIds.value.has(productId)) {
        favoritedProductIds.value.add(productId)
        favorites.value.push({ product_id: productId })
      }
      
      return {
        success: true,
        message: result.message || 'Ditambahkan ke favorit'
      }
    } catch (err) {
      console.error('Add favorite error:', err)
      error.value = err.message
      
      return {
        success: false,
        message: err.message || 'Gagal menambahkan favorit'
      }
    } finally {
      loading.value = false
    }
  }

  /**
   * Remove produk dari favorit
   */
  async function removeFavorite(productId) {
    if (!favoritedProductIds.value.has(productId)) {
      return { success: true, message: 'Tidak dalam favorit' }
    }

    loading.value = true
    error.value = null
    
    try {
      const result = await favoriteService.removeFromFavorite(productId)
      
      // Remove dari state
      favoritedProductIds.value.delete(productId)
      favorites.value = favorites.value.filter(
        item => (item.product_id || item.id) !== productId
      )
      
      return {
        success: true,
        message: result.message || 'Dihapus dari favorit'
      }
    } catch (err) {
      console.error('Remove favorite error:', err)
      error.value = err.message
      
      return {
        success: false,
        message: err.message || 'Gagal menghapus favorit'
      }
    } finally {
      loading.value = false
    }
  }

  /**
   * Toggle favorite status
   * Helper untuk UI yang memudahkan add/remove
   */
  async function toggleFavorite(productId) {
    const isFav = favoritedProductIds.value.has(productId)
    
    if (isFav) {
      return await removeFavorite(productId)
    } else {
      return await addFavorite(productId)
    }
  }

  /**
   * Clear all favorites (pada logout)
   */
  function clearFavorites() {
    favorites.value = []
    favoritedProductIds.value.clear()
    error.value = null
  }

  return {
    // State
    favorites,
    favoritedProductIds,
    loading,
    error,
    
    // Computed
    favoriteCount,
    isFavorite,
    
    // Actions
    fetchFavorites,
    addFavorite,
    removeFavorite,
    toggleFavorite,
    clearFavorites
  }
})
