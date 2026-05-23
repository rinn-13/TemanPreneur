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
      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="loader"></div>
        <p>Memuat detail pesanan...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-state">
        <p>️ {{ error }}</p>
        <button @click="$router.back()" class="btn-back">Kembali</button>
      </div>

      <!-- Main Content -->
      <div v-else>
        <!-- Order Status Bar -->
        <div class="order-status-bar" :class="`status-${order.status}`">
          <div class="status-icon">
            <svg v-if="order.status === 'selesai'" width="24" height="24" viewBox="0 0 24 24" fill="none">
              <polyline points="20,6 9,17 4,12" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="none">
            </svg>
          </div>
          <div>
          </div>
        </div>

        <!-- Main Content Grid -->
        <div class="order-layout">
          <!-- Left: Invoice & Items -->
          <div class="order-main">
            <Invoice 
              :order="order"
              :buyer="buyer"
              :items="items"
              :trackings="trackings"
            />
          </div>

          <!-- Right: Actions & Timeline -->
          <div class="order-sidebar">
            <!-- Quick Actions -->
            <div class="action-card">
              <h3>Aksi Pesanan</h3>
              <div class="actions-list">
                <button 
                  v-if="order.status === 'diantarkan'" 
                  class="action-btn primary"
                  @click="confirmReceived"
                  :disabled="actionLoading"
                >
                  <svg v-if="!actionLoading" width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <polyline points="20,6 9,17 4,12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                  </svg>
                  <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" class="spin">
                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-dasharray="28 28"/>
                  </svg>
                  {{ actionLoading ? 'Memproses...' : 'Konfirmasi Diterima' }}
                </button>

                <router-link 
                  :to="`/buyer/review?order=${order.id}`"
                  class="action-btn outline"
                >
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15H9v-3L18.5 2.5z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                  </svg>
                  Tulis Ulasan
                </router-link>

                <button 
                  v-if="canCancel" 
                  class="action-btn danger"
                  @click="showCancelModal = true"
                  :disabled="actionLoading"
                >
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                  Batalkan
                </button>

                <button class="action-btn secondary" @click="downloadPdf" :disabled="pdfLoading">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                  </svg>
                  {{ pdfLoading ? 'Mengunduh...' : 'Download PDF' }}
                </button>
              </div>
            </div>

            <!-- Order Summary -->
            <div class="summary-card">
              <h3>Ringkasan Pesanan</h3>
              <div class="summary-item">
                <span>Subtotal</span>
                <strong>Rp {{ subtotal.toLocaleString('id-ID') }}</strong>
              </div>
              <div class="summary-item">
                <span>Ongkir</span>
                <strong>Rp {{ shipping_cost.toLocaleString('id-ID') }}</strong>
              </div>
              <div class="summary-divider"></div>
              <div class="summary-item total">
                <span>Total</span>
                <strong>Rp {{ total_amount.toLocaleString('id-ID') }}</strong>
              </div>
            </div>

            <!-- Payment Info -->
            <div class="payment-card">
              <h3>Informasi Pembayaran</h3>
              <div class="payment-detail">
                <label>Metode Pembayaran</label>
                <value>{{ translatePaymentMethod(order.payment_method) }}</value>
              </div>
            </div>

            <!-- Timeline (Mobile Friendly) -->
            <div class="timeline-card" v-if="trackings.length">
              <h3>Riwayat Status</h3>
              <div class="timeline-list">
                <div v-for="(tracking, idx) in trackings" :key="tracking.id" class="timeline-item">
                  <div class="timeline-dot" :style="{ '--idx': idx }"></div>
                  <div class="timeline-text">
                    <p>{{ translateStatus(tracking.status) }}</p>
                    <small>{{ formatDate(tracking.created_at) }}</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="order-mobile-actions" v-if="order && !loading">
      <button v-if="order.status === 'diantarkan'" class="action-btn primary" @click="confirmReceived" :disabled="actionLoading">
        Konfirmasi Diterima
      </button>
      <button v-if="canCancel" class="action-btn danger" @click="showCancelModal = true" :disabled="actionLoading">
        Batalkan Pesanan
      </button>
    </div>

    <CancelOrderModal
      :open="showCancelModal"
      :order-id="order?.id"
      :loading="actionLoading"
      @close="showCancelModal = false"
      @confirm="handleCancelConfirm"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/api/axios'
import { useToast } from '@/composables/useToast'
import Invoice from '@/components/Invoice.vue'
import CancelOrderModal from '@/components/CancelOrderModal.vue'

const route = useRoute()

const order = ref(null)
const buyer = ref(null)
const items = ref([])
const trackings = ref([])
const loading = ref(true)
const actionLoading = ref(false)
const pdfLoading = ref(false)
const showCancelModal = ref(false)
const error = ref(null)
const { success, error: showError } = useToast()

const CANCELLABLE = ['pending', 'diproses', 'dikemas']
const canCancel = computed(() => order.value && CANCELLABLE.includes(order.value.status))

// Computed properties
const subtotal = computed(() => {
  return items.value.reduce((sum, item) => sum + Number(item.subtotal), 0)
})

const shipping_cost = computed(() => Number(order.value?.shipping_cost) || 0)

const total_amount = computed(() => Number(order.value?.total_amount) || subtotal.value + shipping_cost.value)

// Methods
const formatDate = (date) => {
  if (!date) return '-'
  const d = new Date(date)
  return d.toLocaleDateString('id-ID', {
    weekday: 'short',
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const translateStatus = (status) => {
  const statusMap = {
    'diproses': 'Diproses',
    'dikemas': 'Dikemas',
    'diantarkan': 'Diantarkan',
    'selesai': 'Selesai',
    'dibatalkan': 'Dibatalkan',
    'pending': 'Pending',
  }
  return statusMap[status] || status
}

const translatePaymentMethod = (method) => {
  const methodMap = {
    'transfer': 'Transfer Bank',
    'ewallet': 'E-Wallet',
    'cod': 'Bayar di Tempat (COD)',
  }
  return methodMap[method] || method
}

const confirmReceived = async () => {
  if (!confirm('Konfirmasi bahwa Anda telah menerima paket ini?')) return

  actionLoading.value = true
  try {
    await api.post(`/orders/${order.value.id}/complete`)
    success('Terima kasih! Pesanan Anda ditandai sebagai selesai.')
    await fetchOrderDetail()
  } catch (err) {
    showError(
      err.response?.data?.message ||
      err.response?.data?.error ||
      'Gagal mengkonfirmasi penerimaan'
    )
  } finally {
    actionLoading.value = false
  }
}

const handleCancelConfirm = async (reason) => {
  actionLoading.value = true
  try {
    await api.post(`/orders/${order.value.id}/cancel`, { reason })
    showCancelModal.value = false
    success(`Pesanan berhasil dibatalkan. Alasan: ${reason}`)
    await fetchOrderDetail()
  } catch (err) {
    showError(err.response?.data?.message || 'Gagal membatalkan pesanan')
  } finally {
    actionLoading.value = false
  }
}

const downloadPdf = async () => {
  pdfLoading.value = true
  try {
    const res = await api.get(`/orders/${order.value.id}/invoice/pdf`, { responseType: 'blob' })
    const url = window.URL.createObjectURL(new Blob([res.data], { type: 'application/pdf' }))
    const a = document.createElement('a')
    a.href = url
    a.download = `struk-${order.value.id}.pdf`
    a.click()
    window.URL.revokeObjectURL(url)
  } catch (err) {
    showError('Gagal mengunduh PDF struk')
  } finally {
    pdfLoading.value = false
  }
}

const printInvoice = () => {
  window.print()
}

const fetchOrderDetail = async () => {
  loading.value = true
  error.value = null
  
  try {
    const orderId = route.params.id
    const response = await api.get(`/orders/${orderId}`)

    const data = response.data?.data || response.data
    order.value = data
    buyer.value = data?.buyer || {}
    items.value = data?.items || []
    trackings.value = data?.trackings || data?.tracking || []
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Gagal memuat detail pesanan'
    console.error('Error fetching order:', err)
  } finally {
    loading.value = false
  }
}

onMounted(fetchOrderDetail)
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

.loading-state,
.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  text-align: center;
  background: white;
  border-radius: 14px;
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

.error-state p {
  font-size: 1rem;
  color: #dc2626;
  margin: 0 0 16px;
}

.btn-back {
  padding: 10px 24px;
  background: #e53e3e;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.18s;
}

.btn-back:hover {
  background: #c53030;
}

.order-status-bar {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px 24px;
  margin-bottom: 24px;
  border-radius: 14px;
  color: white;
}

.status-diproses {
  background: linear-gradient(135deg, #f59e0b, #d97706);
}

.status-dikemas {
  background: linear-gradient(135deg, #3b82f6, #1e40af);
}

.status-diantarkan {
  background: linear-gradient(135deg, #8b5cf6, #6d28d9);
}

.status-selesai {
  background: linear-gradient(135deg, #10b981, #047857);
}

.status-dibatalkan {
  background: linear-gradient(135deg, #ef4444, #b91c1c);
}

.status-icon {
  flex-shrink: 0;
}

.status-title {
  font-family: 'Fraunces', serif;
  font-size: 1.3rem;
  font-weight: 900;
  margin: 0;
}

.status-subtitle {
  margin: 0;
  opacity: 0.9;
  font-size: 0.85rem;
}

.order-layout {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 24px;
  align-items: start;
}

.order-sidebar {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.action-card,
.summary-card,
.payment-card,
.timeline-card {
  background: white;
  border-radius: 14px;
  border: 1.5px solid #e5e7eb;
  padding: 20px;
}

.action-card h3,
.summary-card h3,
.payment-card h3,
.timeline-card h3 {
  font-size: 0.95rem;
  font-weight: 700;
  color: #111827;
  margin: 0 0 16px;
  padding-bottom: 12px;
  border-bottom: 2px solid #e5e7eb;
}

.actions-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.action-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 14px;
  border: 1.5px solid #e5e7eb;
  border-radius: 8px;
  background: white;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.85rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.18s;
  text-decoration: none;
  color: #374151;
}

.action-btn:hover:not(:disabled) {
  border-color: #d1d5db;
  background: #f9fafb;
}

.action-btn.primary {
  background: #e53e3e;
  border-color: #e53e3e;
  color: white;
}

.action-btn.primary:hover:not(:disabled) {
  background: #c53030;
  border-color: #c53030;
}

.action-btn.danger {
  background: #fecaca;
  border-color: #fca5a5;
  color: #991b1b;
}

.action-btn.danger:hover:not(:disabled) {
  background: #fbbf24;
}

.action-btn.secondary {
  background: #f3f4f6;
  border-color: #e5e7eb;
  color: #374151;
}

.action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.action-btn svg.spin {
  animation: spin 0.8s linear infinite;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  font-size: 0.9rem;
  color: #374151;
}

.summary-item.total {
  font-weight: 700;
  font-size: 1rem;
  color: #111827;
  padding-top: 8px;
  border-top: 1px solid #e5e7eb;
}

.summary-item.total strong {
  color: #e53e3e;
  font-size: 1.1rem;
}

.summary-divider {
  border-top: 1px solid #e5e7eb;
  margin: 12px 0;
}

.payment-detail {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.payment-detail label {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #9ca3af;
}

.payment-detail value {
  font-size: 0.9rem;
  color: #374151;
  font-weight: 600;
}

.timeline-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.timeline-item {
  display: flex;
  gap: 12px;
  align-items: flex-start;
}

.timeline-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #e5e7eb;
  margin-top: 5px;
  flex-shrink: 0;
}

.timeline-item:nth-child(1) .timeline-dot {
  background: #e53e3e;
}

.timeline-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.timeline-text p {
  font-size: 0.9rem;
  font-weight: 600;
  color: #111827;
  margin: 0;
}

.timeline-text small {
  font-size: 0.75rem;
  color: #9ca3af;
}

/* Responsive */
.order-mobile-actions {
  display: none;
  flex-direction: column;
  gap: 10px;
  margin-top: 20px;
  padding: 0 4px;
}

@media (max-width: 768px) {
  .order-layout {
    grid-template-columns: 1fr;
  }

  .order-sidebar {
    display: none;
  }

  .order-mobile-actions {
    display: flex;
  }

  .order-mobile-actions .action-btn {
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    font-weight: 700;
    border: none;
    cursor: pointer;
  }

  .order-mobile-actions .action-btn.primary {
    background: #e53e3e;
    color: #fff;
  }

  .order-mobile-actions .action-btn.danger {
    background: #fee2e2;
    color: #991b1b;
    border: 1.5px solid #fca5a5;
  }

  .buyer-body {
    padding: 16px 16px 48px;
  }

  .back-btn {
    font-size: 0.85rem;
  }

  .order-status-bar {
    padding: 16px;
    margin-bottom: 16px;
  }

  .status-title {
    font-size: 1rem;
  }
}

/* Print Styles */
@media print {
  .buyer-back,
  .order-status-bar,
  .order-sidebar {
    display: none;
  }

  .order-layout {
    grid-template-columns: 1fr;
  }
}
</style>