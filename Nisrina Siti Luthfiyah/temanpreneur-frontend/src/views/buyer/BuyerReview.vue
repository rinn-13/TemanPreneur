<template>
  <div class="buyer-page">
    <div class="buyer-back">
      <button @click="$router.back()" class="back-btn">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
          <path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Kembali
      </button>
    </div>

    <div class="buyer-body">
      <h1 class="review-title">Beri Ulasan Produk</h1>

      <!-- Loading -->
      <div v-if="loading" class="review-loading">
        <div class="loader"></div>
        <p>Memuat data pesanan...</p>
      </div>

      <!-- No orders to review -->
      <div v-else-if="!reviewableOrders.length" class="review-empty">
        <span>⭐</span>
        <h3>Tidak ada pesanan untuk diulas</h3>
        <p>Semua pesanan Anda sudah diberi ulasan atau belum selesai.</p>
        <router-link to="/buyer/orders" class="btn btn-primary">Lihat Pesanan Saya</router-link>
      </div>

      <!-- Reviewable Orders -->
      <div v-else class="review-orders">
        <div v-for="order in reviewableOrders" :key="order.id" class="review-order-card">
          <div class="order-header">
            <div class="order-info">
              <h4>Pesanan #{{ order.id }}</h4>
              <p class="order-date">{{ formatDate(order.created_at) }}</p>
              <p class="order-status">Status: {{ order.status }}</p>
            </div>
            <div class="order-total">
              <strong>Rp {{ formatPrice(order.total_amount) }}</strong>
            </div>
          </div>

          <!-- Order Items -->
          <div class="order-items">
            <div v-for="item in order.items" :key="item.id" class="order-item">
              <div class="item-image" :style="`background:${getItemBg(item)}`">
                {{ getItemEmoji(item) }}
              </div>
              <div class="item-details">
                <h5>{{ item.product.name }}</h5>
                <p class="item-desc">{{ item.product.description || 'Produk TemanPreneur' }}</p>
                <p class="item-quantity">Qty: {{ item.quantity }}</p>
              </div>
              <div class="item-review">
                <button
                  v-if="!hasReviewed(item)"
                  @click="openReviewModal(order, item)"
                  class="btn-review"
                >
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke="currentColor" stroke-width="2"/>
                  </svg>
                  Beri Ulasan
                </button>
                <div v-else class="review-done">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  Sudah Diulas
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Review Modal -->
    <teleport to="body">
      <div v-if="reviewModal.show" class="review-modal-overlay" @click.self="closeReviewModal">
        <div class="review-modal">
          <div class="modal-header">
            <h3>Beri Ulasan</h3>
            <button @click="closeReviewModal" class="modal-close">×</button>
          </div>

          <div class="modal-product">
            <div class="product-image" :style="`background:${getItemBg(reviewModal.item)}`">
              {{ getItemEmoji(reviewModal.item) }}
            </div>
            <div class="product-info">
              <h4>{{ reviewModal.item?.product?.name }}</h4>
              <p>{{ reviewModal.item?.product?.description }}</p>
            </div>
          </div>

          <form @submit.prevent="submitReview" class="review-form">
            <!-- Rating -->
            <div class="form-group">
              <label class="form-label">Rating</label>
              <div class="rating-input">
                <button
                  v-for="i in 5"
                  :key="i"
                  type="button"
                  class="star-btn"
                  :class="{ active: reviewRating >= i }"
                  @click="reviewRating = i"
                >
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                  </svg>
                </button>
              </div>
              <p class="rating-text">{{ reviewRating }}/5 bintang</p>
            </div>

            <!-- Comment -->
            <div class="form-group">
              <label class="form-label">Komentar (Opsional)</label>
              <textarea
                v-model="reviewComment"
                placeholder="Bagaimana pengalaman Anda dengan produk ini?"
                class="form-textarea"
                rows="4"
              ></textarea>
            </div>

            <!-- Submit -->
            <div class="form-actions">
              <button type="button" @click="closeReviewModal" class="btn btn-secondary">
                Batal
              </button>
              <button type="submit" :disabled="!reviewRating || submitting" class="btn btn-primary">
                <template v-if="!submitting">
                  Kirim Ulasan
                </template>
                <template v-else>
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="spin">
                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-dasharray="28 28"/>
                  </svg>
                  Mengirim...
                </template>
              </button>
            </div>
          </form>
        </div>
      </div>
    </teleport>

    <!-- Toast -->
    <div class="toast" :class="{ 'toast--show': toast.show }">
      {{ toast.message }}
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useToast } from '@/composables/useToast'
import api from '@/api/axios'

const IMG_BG = [
  'linear-gradient(135deg,#1a1a2e,#16213e)',
  'linear-gradient(135deg,#0f3460,#533483)',
  'linear-gradient(135deg,#1a0a00,#3d1a00)',
  'linear-gradient(135deg,#0a0a1a,#1a1a3d)',
  'linear-gradient(135deg,#001a0a,#003320)',
  'linear-gradient(135deg,#1a001a,#330033)',
]

const EMOJIS = ['️','','','','','','️','','','']

export default {
  name: 'BuyerReview',
  setup() {
    const { success, error: showError } = useToast()

    const loading = ref(true)
    const reviewableOrders = ref([])
    const toast = ref({ show: false, message: '' })

    const reviewModal = ref({
      show: false,
      order: null,
      item: null
    })

    const reviewRating = ref(5)
    const reviewComment = ref('')
    const submitting = ref(false)
    const route = useRoute()

    const fetchReviewableOrders = async () => {
      try {
        loading.value = true

        const [ordersResponse, reviewsResponse] = await Promise.all([
          api.get('/orders'),
          api.get('/reviews/my'),
        ])

        const orders = ordersResponse.data.data || []
        const reviewedOrderIds = (reviewsResponse.data.data || [])
          .map(r => r.order_id)
          .filter(Boolean)

        const filtered = orders.filter(order =>
          order.status === 'selesai' &&
          !reviewedOrderIds.includes(order.id)
        )

        const orderId = route.query.order

        if (orderId) {
          reviewableOrders.value = filtered.filter(o => o.id == orderId)
        } else {
          reviewableOrders.value = filtered
        }

      } catch (err) {
        console.error('Error fetching orders:', err)
        showError('Gagal memuat data pesanan')
      } finally {
        loading.value = false
      }
    }

    const hasReviewed = (item) => {
      return item.review !== null && item.review !== undefined
    }

    const openReviewModal = (order, item) => {
      reviewModal.value = {
        show: true,
        order,
        item
      }
      reviewRating.value = 5
      reviewComment.value = ''
    }

    const closeReviewModal = () => {
      reviewModal.value.show = false
      reviewModal.value.order = null
      reviewModal.value.item = null
    }

    const submitReview = async () => {
      if (!reviewRating.value) {
        showError('Silakan pilih rating')
        return
      }

      try {
        submitting.value = true

        const reviewData = {
          order_id: reviewModal.value.order.id,
          rating: reviewRating.value,
          comment: reviewComment.value.trim() || null
        }

        await api.post('/reviews', reviewData)

        success('Ulasan berhasil dikirim!')

        // Refresh data
        await fetchReviewableOrders()
        closeReviewModal()

      } catch (err) {
        console.error('Error submitting review:', err)
        showError(err.response?.data?.message || 'Gagal mengirim ulasan')
      } finally {
        submitting.value = false
      }
    }

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    }

    const formatPrice = (price) => {
      return new Intl.NumberFormat('id-ID').format(price)
    }

    const getItemBg = (item) => {
      const index = item.id % IMG_BG.length
      return IMG_BG[index]
    }

    const getItemEmoji = (item) => {
      const index = item.id % EMOJIS.length
      return EMOJIS[index]
    }

    onMounted(fetchReviewableOrders)

    return {
      loading,
      reviewableOrders,
      reviewModal,
      reviewRating,
      reviewComment,
      submitting,
      toast,
      hasReviewed,
      openReviewModal,
      closeReviewModal,
      submitReview,
      formatDate,
      formatPrice,
      getItemBg,
      getItemEmoji
    }
  }
}
</script>

<style scoped>
.buyer-page {
  min-height: 100vh;
  background: #f4f5f7;
  font-family: 'Plus Jakarta Sans', sans-serif;
}

.buyer-back {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px 28px 0;
}

.back-btn {
  display: flex;
  align-items: center;
  gap: 7px;
  background: none;
  border: none;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.95rem;
  font-weight: 700;
  color: #111827;
  cursor: pointer;
  text-decoration: underline;
  text-underline-offset: 3px;
  transition: color 0.18s;
}

.back-btn:hover {
  color: #e53e3e;
}

.buyer-body {
  max-width: 1200px;
  margin: 0 auto;
  padding: 24px 28px 72px;
}

.review-title {
  font-family: 'Fraunces', serif;
  font-size: 2rem;
  font-weight: 900;
  color: #111827;
  margin: 0 0 32px;
}

.review-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  text-align: center;
}

.loader {
  width: 40px;
  height: 40px;
  border: 3px solid #e5e7eb;
  border-top-color: #e53e3e;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.review-empty {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 14px;
  border: 1.5px solid #e5e7eb;
}

.review-empty span {
  font-size: 3rem;
  display: block;
  margin-bottom: 16px;
}

.review-empty h3 {
  color: #111827;
  margin: 0 0 8px;
}

.review-empty p {
  color: #6b7280;
  margin: 0 0 24px;
}

.review-orders {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.review-order-card {
  background: white;
  border-radius: 14px;
  padding: 24px;
  border: 1.5px solid #e5e7eb;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e5e7eb;
}

.order-info h4 {
  margin: 0 0 4px;
  color: #111827;
  font-weight: 700;
}

.order-info p {
  margin: 0;
  color: #6b7280;
  font-size: 0.9rem;
}

.order-total strong {
  color: #111827;
  font-size: 1.1rem;
}

.order-items {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.order-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: #f9fafb;
  border-radius: 8px;
}

.item-image {
  width: 50px;
  height: 50px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
}

.item-details {
  flex: 1;
}

.item-details h5 {
  margin: 0 0 4px;
  color: #111827;
  font-weight: 600;
}

.item-details p {
  margin: 0;
  color: #6b7280;
  font-size: 0.9rem;
}

.item-review {
  flex-shrink: 0;
}

.btn-review {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: #e53e3e;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-review:hover {
  background: #c53030;
}

.review-done {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #10b981;
  font-size: 0.9rem;
  font-weight: 600;
}

/* Modal */
.review-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.review-modal {
  background: white;
  border-radius: 14px;
  padding: 24px;
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.modal-header h3 {
  margin: 0;
  color: #111827;
  font-weight: 700;
}

.modal-close {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #6b7280;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-product {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: #f9fafb;
  border-radius: 8px;
  margin-bottom: 20px;
}

.product-image {
  width: 50px;
  height: 50px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
}

.product-info h4 {
  margin: 0 0 4px;
  color: #111827;
  font-weight: 600;
}

.product-info p {
  margin: 0;
  color: #6b7280;
  font-size: 0.9rem;
}

.review-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-label {
  font-weight: 600;
  color: #111827;
}

.rating-input {
  display: flex;
  gap: 4px;
}

.star-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: #d1d5db;
  transition: color 0.2s;
}

.star-btn.active {
  color: #fbbf24;
}

.rating-text {
  margin: 8px 0 0;
  color: #6b7280;
  font-size: 0.9rem;
}

.form-textarea {
  padding: 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-family: inherit;
  resize: vertical;
}

.form-textarea:focus {
  outline: none;
  border-color: #e53e3e;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary {
  background: #e53e3e;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #c53030;
}

.btn-primary:disabled {
  background: #9ca3af;
  cursor: not-allowed;
}

.btn-secondary {
  background: #f3f4f6;
  color: #374151;
}

.btn-secondary:hover {
  background: #e5e7eb;
}

.spin {
  animation: spin 1s linear infinite;
}

/* Toast */
.toast {
  position: fixed;
  bottom: 20px;
  right: 20px;
  background: #10b981;
  color: white;
  padding: 12px 20px;
  border-radius: 6px;
  font-weight: 600;
  opacity: 0;
  transform: translateY(100px);
  transition: all 0.3s;
  z-index: 1001;
}

.toast--show {
  opacity: 1;
  transform: translateY(0);
}
</style>