<template>
  <div class="container mt-4">
    <h2>Pesanan Saya</h2>
    <div v-for="order in orders" :key="order.id" class="card mb-3">
      <div class="card-body">
        <h5>{{ order.product.name }}</h5>
        <p>Status: <span class="badge" :class="statusBadge(order.status)">{{ order.status }}</span></p>
        <p>Jumlah: {{ order.quantity }}</p>
        <p>Total: Rp {{ order.total_price.toLocaleString() }}</p>
        <router-link :to="'/buyer/orders/'+order.id" class="btn btn-sm btn-info">Detail</router-link>
        <button v-if="order.status === 'diantarkan'" @click="reportIssue(order)" class="btn btn-sm btn-warning">Laporkan Masalah</button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import api from '@/api/axios'

export default {
  setup() {
    const orders = ref([])

    const fetchOrders = async () => {
      const { data } = await api.get('/orders')
      orders.value = data.data
    }

    const statusBadge = (status) => {
      const map = {
        diproses: 'bg-secondary',
        dikemas: 'bg-primary',
        diantarkan: 'bg-warning',
        selesai: 'bg-success',
      }
      return map[status] || 'bg-secondary'
    }

    const reportIssue = (order) => {
      // buka modal atau redirect ke form issue
    }

    onMounted(fetchOrders)

    return { orders, statusBadge, reportIssue }
  }
}
</script>