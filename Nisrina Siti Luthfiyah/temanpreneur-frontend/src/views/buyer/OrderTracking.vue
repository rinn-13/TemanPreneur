<template>
  <div class="tracking-page">
    <div class="tracking-header">
      <button @click="$router.back()" class="back-btn">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
          <path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Kembali
      </button>
    </div>

    <div class="tracking-body">
      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="loader"></div>
        <p>Memuat tracking pesanan...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-state">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
          <path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <p>{{ error }}</p>
        <button @click="fetchTracking" class="btn-retry">Coba Lagi</button>
      </div>

      <!-- Main Content -->
      <div v-else class="tracking-content">
        <!-- Order Header Card -->
        <div class="header-card">
          <div class="header-info">
            <h1>Pesanan <span class="order-id">#{{ order?.id }}</span></h1>
            <p class="order-date">{{ formatDate(order?.created_at) }}</p>
          </div>
          <div class="current-status" :class="`status-${order?.status}`">
            <div class="status-badge">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                <path v-if="order?.status === 'selesai'" d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              <span>{{ translateStatus(order?.status) }}</span>
            </div>
          </div>
        </div>

        <!-- Main Timeline -->
        <div class="timeline-container">
          <div class="timeline-track">
            <!-- Progress Bar -->
            <div class="progress-bar">
              <div class="progress-fill" :style="{ width: progressPercentage + '%' }"></div>
            </div>

            <!-- Timeline Items -->
            <div class="timeline-steps">
              <div 
                v-for="(step, idx) in steps" 
                :key="idx"
                class="timeline-step"
                :class="{
                  'active': isStepActive(step),
                  'completed': isStepCompleted(step),
                  'pending': isStepPending(step)
                }"
              >
                <div class="step-dot">
                  <svg v-if="isStepCompleted(step)" width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <polyline points="20 6 9 17 4 12" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <div v-else class="dot-indicator"></div>
                </div>

                <div class="step-content">
                  <h3>{{ step.label }}</h3>
                  <p v-if="getStepTimestamp(step)" class="timestamp">
                    {{ formatDateTime(getStepTimestamp(step)) }}
                  </p>
                  <p v-else class="pending-text">Menunggu...</p>

                  <!-- Updated By Info -->
                  <div v-if="getStepTracking(step)" class="tracking-meta">
                    <span class="meta-badge">Diupdate oleh: {{ getStepTracking(step).updater?.name || 'Sistem' }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Detailed Tracking History -->
        <div class="history-card" v-if="trackings?.length">
          <h2>Riwayat Lengkap</h2>
          <div class="history-list">
            <div v-for="tracking in trackingsSorted" :key="tracking.id" class="history-item">
              <div class="history-left">
                <div class="history-dot"></div>
              </div>
              <div class="history-content">
                <div class="history-header">
                  <h4>{{ translateStatus(tracking.status) }}</h4>
                  <span class="history-time">{{ formatDateTime(tracking.created_at) }}</span>
                </div>
                <p class="history-by">Diupdate oleh: <strong>{{ tracking.updater?.name || 'Sistem' }}</strong></p>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="summary-card">
          <h2>Ringkasan Pesanan</h2>
          <div class="order-items">
            <div v-for="item in order?.items" :key="item.id" class="order-item">
              <div class="item-info">
                <h4>{{ item.product?.name }}</h4>
                <p class="item-qty">Qty: {{ item.quantity }}</p>
              </div>
              <div class="item-price">
                Rp {{ Number(item.subtotal).toLocaleString('id-ID') }}
              </div>
            </div>
          </div>

          <div class="summary-divider"></div>

          <div class="summary-row">
            <span>Subtotal</span>
            <strong>Rp {{ subtotal.toLocaleString('id-ID') }}</strong>
          </div>
          <div class="summary-row">
            <span>Ongkir</span>
            <strong>Rp {{ order?.shipping_cost?.toLocaleString('id-ID') || 0 }}</strong>
          </div>

          <div class="summary-total">
            <span>Total</span>
            <strong>Rp {{ Number(order?.total_amount).toLocaleString('id-ID') }}</strong>
          </div>
        </div>

        <!-- Shipping Info -->
        <div class="shipping-card">
          <h2>Informasi Pengiriman</h2>
          <div class="shipping-detail">
            <div class="detail-row">
              <label>Penerima</label>
              <value>{{ order?.shipping_name }}</value>
            </div>
            <div class="detail-row">
              <label>Telepon</label>
              <value>{{ order?.shipping_phone }}</value>
            </div>
            <div class="detail-row full">
              <label>Ruangan</label>
              <value>{{ order?.shipping_address }}</value>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div v-if="order?.status !== 'selesai' && order?.status !== 'dibatalkan'" class="action-buttons">
          <button 
            v-if="order?.status === 'diantarkan'" 
            class="btn btn-primary"
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

          <router-link :to="`/buyer/laporan?order=${order?.id}`" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
              <path d="M12 16v-4M12 8h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            Laporkan Masalah
          </router-link>
        </div>

        <div v-if="order?.status === 'selesai'" class="completion-message">
          <div class="checkmark-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
              <polyline points="20 6 9 17 4 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <h3>Pesanan Selesai!</h3>
          <p>Terima kasih telah berbelanja. Bagikan pengalaman Anda dengan ulasan.</p>
          <router-link :to="`/buyer/review?order=${order?.id}`" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15H9v-3L18.5 2.5z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            Tulis Ulasan
          </router-link>
        </div>

        <!-- Auto Refresh Info -->
        <div class="refresh-info">
          <p>ℹ️ Halaman ini diperbarui setiap 10 detik secara otomatis.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/api/axios'

const route = useRoute()

const order = ref(null)
const trackings = ref([])
const loading = ref(true)
const actionLoading = ref(false)
const error = ref(null)
let refreshInterval = null

// Status steps for timeline
const steps = [
  { status: 'diproses', label: 'Diproses' },
  { status: 'dikemas', label: 'Dikemas' },
  { status: 'diantarkan', label: 'Diantarkan' },
  { status: 'selesai', label: 'Selesai' }
]

// Computed
const trackingsSorted = computed(() => {
  return [...(trackings.value || [])].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
})

const subtotal = computed(() => {
  return (order.value?.items || []).reduce((sum, item) => sum + Number(item.subtotal), 0)
})

const progressPercentage = computed(() => {
  const currentIdx = steps.findIndex(s => s.status === order.value?.status)
  if (currentIdx === -1) return 0
  return ((currentIdx + 1) / steps.length) * 100
})

// Methods
const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    weekday: 'short',
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

const formatDateTime = (date) => {
  if (!date) return '-'
  const d = new Date(date)
  return d.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const translateStatus = (status) => {
  const map = {
    'diproses': 'Diproses',
    'dikemas': 'Dikemas',
    'diantarkan': 'Diantarkan',
    'selesai': 'Selesai',
    'dibatalkan': 'Dibatalkan',
  }
  return map[status] || status
}

const isStepCompleted = (step) => {
  const currentIdx = steps.findIndex(s => s.status === order.value?.status)
  const stepIdx = steps.findIndex(s => s.status === step.status)
  return stepIdx < currentIdx
}

const isStepActive = (step) => {
  return step.status === order.value?.status
}

const isStepPending = (step) => {
  const currentIdx = steps.findIndex(s => s.status === order.value?.status)
  const stepIdx = steps.findIndex(s => s.status === step.status)
  return stepIdx > currentIdx
}

const getStepTracking = (step) => {
  return trackings.value?.find(t => t.status === step.status)
}

const getStepTimestamp = (step) => {
  const tracking = getStepTracking(step)
  return tracking?.created_at
}

const confirmReceived = async () => {
  if (!confirm('Konfirmasi bahwa Anda telah menerima paket ini?')) return

  actionLoading.value = true
  try {
    const orderId = route.params.id

    await api.post(`/orders/${orderId}/complete`)

    //  redirect ke halaman ulasan
    router.push(`/buyer/review?order=${orderId}`)

  } catch (err) {
    alert(
      err.response?.data?.message ||
      err.response?.data?.error ||
      'Gagal mengkonfirmasi penerimaan'
    )
  } finally {
    actionLoading.value = false
  }
}

const fetchTracking = async () => {
  try {
    const orderId = route.params.id
    const response = await api.get(`/orders/${orderId}`)
    
    order.value = response.data?.data
    trackings.value = response.data?.data?.trackings || []
    error.value = null
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal memuat tracking pesanan'
    console.error('Error fetching tracking:', err)
  }
}

const loadTracking = async () => {
  loading.value = true
  await fetchTracking()
  loading.value = false
}

const startAutoRefresh = () => {
  refreshInterval = setInterval(fetchTracking, 10000) // Refresh setiap 10 detik
}

onMounted(() => {
  loadTracking()
  startAutoRefresh()
})

onUnmounted(() => {
  if (refreshInterval) clearInterval(refreshInterval)
})
</script>

<style scoped>
.tracking-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f4f5f7 0%, #f8f9fa 100%);
  font-family: 'Plus Jakarta Sans', sans-serif;
}

.tracking-header {
  background: white;
  padding: 20px 28px;
  border-bottom: 1.5px solid #e5e7eb;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
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
  transition: color 0.18s;
}

.back-btn:hover {
  color: #e53e3e;
}

.tracking-body {
  max-width: 900px;
  margin: 0 auto;
  padding: 28px 20px;
}

.loading-state,
.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  background: white;
  border-radius: 16px;
  text-align: center;
}

.loader {
  width: 48px;
  height: 48px;
  border: 3px solid #e5e7eb;
  border-top-color: #e53e3e;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-state svg {
  color: #dc2626;
  margin-bottom: 16px;
}

.error-state p {
  font-size: 1rem;
  color: #dc2626;
  margin: 0 0 16px;
}

.btn-retry {
  padding: 10px 20px;
  background: #e53e3e;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.18s;
}

.btn-retry:hover {
  background: #c53030;
}

.tracking-content {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.header-card {
  background: white;
  border-radius: 16px;
  padding: 28px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.header-info h1 {
  font-size: 1.8rem;
  font-weight: 900;
  margin: 0 0 8px;
  color: #111827;
}

.order-id {
  color: #6b7280;
  font-weight: 700;
}

.order-date {
  font-size: 0.9rem;
  color: #6b7280;
  margin: 0;
}

.current-status {
  flex-shrink: 0;
}

.status-badge {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.95rem;
  color: white;
}

.status-diproses .status-badge { background: linear-gradient(135deg, #f59e0b, #d97706); }
.status-dikemas .status-badge { background: linear-gradient(135deg, #3b82f6, #1e40af); }
.status-diantarkan .status-badge { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
.status-selesai .status-badge { background: linear-gradient(135deg, #10b981, #047857); }
.status-dibatalkan .status-badge { background: linear-gradient(135deg, #ef4444, #b91c1c); }

.timeline-container {
  background: white;
  border-radius: 16px;
  padding: 32px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.timeline-track {
  position: relative;
}

.progress-bar {
  position: relative;
  height: 3px;
  background: #e5e7eb;
  border-radius: 999px;
  margin-bottom: 40px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #10b981, #3b82f6);
  transition: width 0.6s ease;
  border-radius: 999px;
}

.timeline-steps {
  display: flex;
  justify-content: space-between;
  position: relative;
}

.timeline-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  flex: 1;
  position: relative;
}

.timeline-step:not(:last-child)::after {
  content: '';
  position: absolute;
  top: 20px;
  left: 50%;
  width: 50%;
  height: 2px;
  background: #e5e7eb;
}

.timeline-step.completed:not(:last-child)::after {
  background: linear-gradient(90deg, #10b981, #3b82f6);
}

.step-dot {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: white;
  border: 2px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  z-index: 1;
  transition: all 0.18s;
}

.timeline-step.completed .step-dot {
  background: linear-gradient(135deg, #10b981, #047857);
  border-color: #047857;
  color: white;
}

.timeline-step.active .step-dot {
  background: linear-gradient(135deg, #3b82f6, #1e40af);
  border-color: #1e40af;
  color: white;
  box-shadow: 0 0 0 8px rgba(59, 130, 246, 0.1);
}

.timeline-step.pending .step-dot {
  opacity: 0.5;
}

.dot-indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: currentColor;
}

.step-content {
  text-align: center;
  flex: 1;
  min-width: 0;
}

.step-content h3 {
  font-size: 0.9rem;
  font-weight: 700;
  margin: 0;
  color: #111827;
}

.timestamp {
  font-size: 0.8rem;
  color: #10b981;
  margin: 4px 0 0;
  font-weight: 600;
}

.pending-text {
  font-size: 0.8rem;
  color: #9ca3af;
  margin: 4px 0 0;
  font-style: italic;
}

.tracking-meta {
  margin-top: 8px;
}

.meta-badge {
  display: inline-block;
  font-size: 0.7rem;
  background: #f0f9ff;
  color: #0369a1;
  padding: 4px 8px;
  border-radius: 4px;
  font-weight: 600;
}

.history-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.history-card h2 {
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0 0 20px;
  color: #111827;
}

.history-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.history-item {
  display: flex;
  gap: 16px;
  padding: 16px;
  background: #f9fafb;
  border-radius: 10px;
  transition: background 0.18s;
}

.history-item:hover {
  background: #f3f4f6;
}

.history-left {
  flex-shrink: 0;
}

.history-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #3b82f6;
  margin-top: 4px;
}

.history-content {
  flex: 1;
  min-width: 0;
}

.history-header {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  gap: 12px;
  margin-bottom: 6px;
}

.history-header h4 {
  font-size: 0.9rem;
  font-weight: 700;
  margin: 0;
  color: #111827;
}

.history-time {
  font-size: 0.8rem;
  color: #6b7280;
  flex-shrink: 0;
}

.history-by {
  font-size: 0.85rem;
  color: #6b7280;
  margin: 0;
}

.history-by strong {
  color: #374151;
}

.summary-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.summary-card h2 {
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0 0 20px;
  color: #111827;
}

.order-items {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 20px;
}

.order-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  padding: 12px;
  background: #f9fafb;
  border-radius: 8px;
}

.item-info h4 {
  font-size: 0.9rem;
  font-weight: 700;
  margin: 0;
  color: #111827;
}

.item-qty {
  font-size: 0.8rem;
  color: #6b7280;
  margin: 4px 0 0;
}

.item-price {
  font-weight: 700;
  color: #e53e3e;
  font-size: 0.95rem;
  white-space: nowrap;
}

.summary-divider {
  border-top: 1.5px solid #e5e7eb;
  margin: 16px 0;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 10px 0;
  font-size: 0.9rem;
  color: #374151;
}

.summary-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  font-weight: 700;
  font-size: 1.1rem;
  color: #111827;
  border-top: 1px solid #e5e7eb;
  margin-top: 12px;
}

.summary-total strong {
  color: #e53e3e;
  font-size: 1.2rem;
}

.shipping-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.shipping-card h2 {
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0 0 20px;
  color: #111827;
}

.shipping-detail {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.detail-row {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.detail-row.full {
  grid-column: 1 / -1;
}

.detail-row label {
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #9ca3af;
}

.detail-row value {
  font-size: 0.95rem;
  color: #374151;
  line-height: 1.5;
}

.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 24px;
  border: none;
  border-radius: 10px;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.18s;
}

.btn-primary {
  background: linear-gradient(135deg, #e53e3e, #c53030);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(229, 62, 62, 0.3);
}

.btn-secondary {
  background: white;
  color: #374151;
  border: 1.5px solid #e5e7eb;
}

.btn-secondary:hover {
  background: #f9fafb;
  border-color: #d1d5db;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn svg.spin {
  animation: spin 0.8s linear infinite;
}

.completion-message {
  background: white;
  border-radius: 16px;
  padding: 40px 24px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.checkmark-icon {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: linear-gradient(135deg, #10b981, #047857);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
}

.completion-message h3 {
  font-size: 1.3rem;
  font-weight: 700;
  margin: 0 0 8px;
  color: #111827;
}

.completion-message p {
  font-size: 0.95rem;
  color: #6b7280;
  margin: 0 0 20px;
}

.refresh-info {
  text-align: center;
  padding: 16px;
  background: #f0f9ff;
  border: 1px solid #bfdbfe;
  border-radius: 10px;
  font-size: 0.85rem;
  color: #0369a1;
}

.refresh-info p {
  margin: 0;
}

/* Responsive */
@media (max-width: 768px) {
  .tracking-header {
    padding: 16px;
  }

  .tracking-body {
    padding: 16px 12px;
  }

  .header-card {
    flex-direction: column;
    padding: 20px;
  }

  .timeline-container {
    padding: 20px;
  }

  .timeline-steps {
    flex-direction: column;
    gap: 32px;
  }

  .timeline-step:not(:last-child)::after {
    display: none;
  }

  .step-content h3 {
    font-size: 0.85rem;
  }

  .history-item {
    padding: 12px;
  }

  .history-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .action-buttons {
    flex-direction: column;
  }

  .btn {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .header-info h1 {
    font-size: 1.4rem;
  }

  .timeline-container {
    padding: 16px;
  }

  .step-dot {
    width: 36px;
    height: 36px;
  }

  .step-content h3 {
    font-size: 0.8rem;
  }

  .summary-card,
  .shipping-card,
  .history-card {
    padding: 16px;
  }
}
</style>
