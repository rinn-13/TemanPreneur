// resources/js/stores/cart.js
import { defineStore } from 'pinia'
import api from '@/api/axios'   // sesuaikan path alias @ ke resources/js

export const useCartStore = defineStore('cart', {
  state: () => ({
    totalItems: 0,
  }),
  actions: {
    setTotalItems(count) {
      this.totalItems = count
    },
    async fetchTotalItems() {
      try {
        const response = await api.get('/cart/count')
        this.totalItems = response.data.count
      } catch (error) {
        console.error('Gagal ambil jumlah keranjang', error)
        this.totalItems = 0
      }
    },
    incrementBy(delta) {
      this.totalItems += delta
      if (this.totalItems < 0) this.totalItems = 0
    },
  },
})