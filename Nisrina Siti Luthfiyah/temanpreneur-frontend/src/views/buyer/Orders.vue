<template>
  <div class="orders-container">
    <div class="orders-back">
      <button type="button" class="orders-back__btn" @click="router.back()">
        <i class="bi bi-arrow-left"></i>
        Kembali
      </button>
    </div>
    <div class="orders-header">
      <h2>Pesanan Saya</h2>
      <p class="orders-subtitle">{{ orders.length }} pesanan ditemukan</p>
    </div>

    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Memuat pesanan Anda...</p>
    </div>

    <div v-else>
      <div v-if="!orders.length" class="empty-state">
        <p class="empty-icon"></p>
        <p>Tidak ada pesanan.</p>
        <router-link to="/katalog" class="btn btn-outline">Mulai Belanja</router-link>
      </div>

      <div v-for="order in orders" :key="order.id" class="order-card">
        <div class="order-card__header">
          <div class="order-card__info">
            <h5 class="order-card__title">
              {{ order.items?.[0]?.product?.name || ('Pesanan #' + order.id) }}
            </h5>
            <p class="order-card__shop">
              Toko: <strong>{{ order.items?.[0]?.product?.business?.name || '-' }}</strong>
            </p>
          </div>
          <div class="order-card__status">
            <span class="status-badge" :class="['status-badge', statusBadge(order.status)]">
              {{ translateStatus(order.status) }}
            </span>
          </div>
        </div>

        <div class="order-card__body">
          <div class="order-card__image">
            <img
              :src="getImage(order)"
              class="order-image"
              :alt="order.items?.[0]?.product?.name"
            />
          </div>

          <div class="order-card__details">
            <div class="detail-row">
              <span class="detail-label">Total:</span>
              <span class="detail-value">
                Rp {{ Number(order.total_amount || order.total_price || 0).toLocaleString('id-ID') }}
              </span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Tanggal Pesanan:</span>
              <span class="detail-value">{{ formatDate(order.created_at) }}</span>
            </div>

            <!-- Quick Tracking Preview -->
            <div v-if="order.trackings && order.trackings.length > 0" class="tracking-preview">
              <p class="tracking-preview__title"> Riwayat Pesanan</p>
              <div class="tracking-steps">
                <div v-for="(track, idx) in getLastTrackings(order.trackings, 3)" :key="idx" class="tracking-step">
                  <span class="tracking-dot"></span>
                  <span class="tracking-text">{{ track.status }} · {{ formatDateShort(track.created_at) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="order-card__footer">
          <router-link
            :to="`/buyer/orders/${order.id}`"
            class="btn btn-secondary"
          >
            Detail
          </router-link>
          <router-link
            :to="`/buyer/orders/${order.id}/tracking`"
            class="btn btn-primary"
          >
             Lihat Tracking
          </router-link>
          <router-link
            v-if="order.status === 'selesai' && !hasReview(order.id)"
            :to="`/buyer/review?order=${order.id}`"
            class="btn btn-success"
          >
            ⭐ Tulis Ulasan
          </router-link>
          <span v-else-if="order.status === 'selesai' && hasReview(order.id)" class="btn-reviewed">
             Sudah Diulaskan
          </span>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { normalizeImageUrl } from '@/utils/image'
import { useOrderStore } from '@/stores/orders'

export default {
  name: 'BuyerOrders',

  setup() {
    const router = useRouter()
    const orderStore = useOrderStore()
    const reviews = ref([])

    const loading = computed(() => orderStore.loading)
    const orders = computed(() => orderStore.history || [])

    const fetchOrders = async () => {
      await orderStore.fetchOrders()
    }

    const fetchReviews = async () => {
      try {
        const response = await fetch('/api/reviews/my')
        if (response.ok) {
          const data = await response.json()
          reviews.value = data.data || []
        }
      } catch (err) {
        console.error('Error fetching reviews:', err)
      }
    }

    const statusBadge = (status) => {
      const map = {
        pending: 'status-badge--pending',
        diproses: 'status-badge--diproses',
        dikemas: 'status-badge--dikemas',
        diantarkan: 'status-badge--diantarkan',
        selesai: 'status-badge--selesai',
        dibatalkan: 'status-badge--dibatalkan',
      }
      return map[status] || ''
    }

    const translateStatus = (status) => {
      const map = {
        pending: '⏳ Menunggu',
        diproses: '⏳ Diproses',
        dikemas: '📦 Dikemas',
        diantarkan: '🚚 Dikirim',
        selesai: '✅ Selesai',
        dibatalkan: '❌ Dibatalkan',
      }
      return map[status] || status || '-'
    }

    const getImage = (order) => normalizeImageUrl(order.items?.[0]?.product?.image, '/placeholder-product.png')

    const formatDate = (dateString) => {
      if (!dateString) return '-'
      const date = new Date(dateString)
      return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      })
    }

    const formatDateShort = (dateString) => {
      if (!dateString) return '-'
      const date = new Date(dateString)
      return date.toLocaleDateString('id-ID', {
        month: 'short',
        day: 'numeric',
      })
    }

    const getLastTrackings = (trackings, limit = 3) => {
      if (!trackings || !Array.isArray(trackings)) return []
      return trackings.slice().reverse().slice(0, limit)
    }

    const hasReview = (orderId) => {
      return reviews.value.some(r => r.order_id === orderId)
    }

    onMounted(() => {
      fetchOrders()
      fetchReviews()
    })

    return {
      router,
      orders,
      loading,
      statusBadge,
      translateStatus,
      getImage,
      formatDate,
      formatDateShort,
      getLastTrackings,
      hasReview,
    }
  }
}
</script>

<style scoped>
.orders-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 24px 16px;
  background: #f4f5f7;
  min-height: 100vh;
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.orders-back {
  margin-bottom: 16px;
}

.orders-back__btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
  background: #fff;
  color: #374151;
  font-size: 0.875rem;
  font-weight: 700;
  cursor: pointer;
  font-family: inherit;
  transition: border-color 0.2s, color 0.2s;
}

.orders-back__btn:hover {
  border-color: #6366f1;
  color: #4f46e5;
}

.orders-header {
  margin-bottom: 32px;
}

.orders-header h2 {
  font-size: 2rem;
  font-weight: 900;
  color: #111827;
  margin-bottom: 4px;
}

.orders-subtitle {
  font-size: 0.9rem;
  color: #9ca3af;
  margin: 0;
}

/* Loading State */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  gap: 16px;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e5e7eb;
  border-top: 4px solid #6366f1;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading-state p {
  color: #9ca3af;
  font-size: 0.95rem;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 24px;
  background: white;
  border-radius: 16px;
  border: 1px solid #e5e7eb;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 12px;
}

.empty-state p {
  font-size: 1rem;
  color: #6b7280;
  margin-bottom: 20px;
}

.btn {
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 600;
  text-decoration: none;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.btn-outline {
  background: transparent;
  color: #6366f1;
  border: 2px solid #6366f1;
}

.btn-outline:hover {
  background: #6366f1;
  color: white;
}

.btn-primary {
  background: #6366f1;
  color: white;
}

.btn-primary:hover {
  background: #4f46e5;
}

.btn-secondary {
  background: #e5e7eb;
  color: #111827;
}

.btn-secondary:hover {
  background: #d1d5db;
}

.btn-success {
  background: #10b981;
  color: white;
}

.btn-success:hover {
  background: #059669;
}

/* Order Card */
.order-card {
  background: white;
  border-radius: 16px;
  border: 1px solid #e5e7eb;
  margin-bottom: 16px;
  overflow: hidden;
  transition: box-shadow 0.2s, transform 0.2s;
}

.order-card:hover {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.order-card__header {
  padding: 16px 20px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
}

.order-card__info {
  flex: 1;
}

.order-card__title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 4px;
}

.order-card__shop {
  font-size: 0.85rem;
  color: #6b7280;
  margin: 0;
}

.order-card__shop strong {
  color: #6366f1;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 700;
  white-space: nowrap;
}

.status-badge--diproses {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
}

.status-badge--dikemas {
  background: linear-gradient(135deg, #3b82f6, #1e40af);
  color: white;
}

.status-badge--diantarkan {
  background: linear-gradient(135deg, #8b5cf6, #6d28d9);
  color: white;
}

.status-badge--selesai {
  background: linear-gradient(135deg, #10b981, #047857);
  color: white;
}

.status-badge--pending {
  background: #f3f4f6;
  color: #4b5563;
}

.status-badge--dibatalkan {
  background: linear-gradient(135deg, #ef4444, #b91c1c);
  color: white;
}

/* Order Card Body */
.order-card__body {
  padding: 16px 20px;
  display: flex;
  gap: 20px;
}

.order-card__image {
  flex-shrink: 0;
}

.order-image {
  width: 120px;
  height: 120px;
  object-fit: cover;
  border-radius: 12px;
  background: #f3f4f6;
}

.order-card__details {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
  font-size: 0.9rem;
}

.detail-label {
  color: #6b7280;
}

.detail-value {
  font-weight: 600;
  color: #111827;
}

/* Tracking Preview */
.tracking-preview {
  margin-top: 12px;
  padding: 12px;
  background: #f9fafb;
  border-radius: 8px;
  border-left: 3px solid #6366f1;
}

.tracking-preview__title {
  font-size: 0.85rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 8px;
  margin-top: 0;
}

.tracking-steps {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.tracking-step {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.8rem;
  color: #6b7280;
}

.tracking-dot {
  display: inline-block;
  width: 6px;
  height: 6px;
  background: #6366f1;
  border-radius: 50%;
  flex-shrink: 0;
}

.tracking-text {
  line-height: 1.4;
}

/* Order Card Footer */
.order-card__footer {
  padding: 16px 20px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.order-card__footer .btn {
  flex: 1;
  min-width: 120px;
  justify-content: center;
}

.btn-reviewed {
  padding: 10px 16px;
  background: #e8f4f8;
  color: #0c766e;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  flex: 1;
  min-width: 120px;
  text-align: center;
}

/* Responsive */
@media (max-width: 768px) {
  .order-card__body {
    flex-direction: column;
  }

  .order-card__image {
    align-self: flex-start;
  }

  .order-card__footer {
    flex-direction: column;
  }

  .order-card__footer .btn,
  .btn-reviewed {
    min-width: 100%;
  }

  .orders-header h2 {
    font-size: 1.5rem;
  }
}
</style>