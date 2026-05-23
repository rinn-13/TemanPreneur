import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api/axios'
import orderService from '@/services/order'

export const useOrderStore = defineStore('orders', () => {

  // ===== STATE =====
  const history = ref([]) //  jangan load localStorage dulu
  const currentOrder = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const successMessage = ref(null)

  // ===== COMPUTED =====
  const orderCount = computed(() => history.value.length)

  const totalSpent = computed(() =>
    history.value.reduce((sum, o) => sum + (o.total_amount || 0), 0)
  )

  const getOrdersByStatus = (status) =>
    history.value.filter(o => o.status === status)

  // ===== ACTION =====

  async function fetchOrders() {
    loading.value = true
    error.value = null

    try {
      const res = await api.get('/orders')

      // Handle different response formats
      let ordersData = []

      if (res.data?.success && res.data?.data) {
        // Backend response format: { success: true, data: [...], meta: {...} }
        ordersData = Array.isArray(res.data.data) ? res.data.data : []
      } else if (res.data?.data && Array.isArray(res.data.data)) {
        // Alternative format
        ordersData = res.data.data
      } else if (Array.isArray(res.data)) {
        // Direct array
        ordersData = res.data
      } else {
        console.warn('Unexpected orders response format:', res.data)
        ordersData = []
      }

      console.log('Fetched orders:', ordersData.length, 'orders')
      history.value = ordersData

      return ordersData

    } catch (err) {
      console.error('fetchOrders error:', err)
      error.value = err.message || 'Gagal load orders'
      history.value = []
      return []
    } finally {
      loading.value = false
    }
  }

  function addOrder(order) {
    if (!order?.id) return

    history.value = [
      order,
      ...history.value.filter(o => o.id !== order.id)
    ]
  }

  async function fetchOrderDetail(id) {
    loading.value = true
    try {
      const res = await orderService.getOrderDetail(id)
      currentOrder.value = res
      return res
    } finally {
      loading.value = false
    }
  }

  function clearOrders() {
    history.value = []
    currentOrder.value = null
  }

  return {
    history,
    currentOrder,
    loading,
    error,
    successMessage,

    orderCount,
    totalSpent,
    getOrdersByStatus,

    fetchOrders,
    fetchOrderDetail,
    addOrder,
    clearOrders,
  }
})