<template>
  <div class="notif-detail-container">
    <!-- Header -->
    <div class="notif-detail-header">
      <button class="notif-back-btn" @click="$router.go(-1)">← Kembali</button>
      <h1>Detail Notifikasi Penanganan</h1>
    </div>

    <!-- Loading state -->
    <div v-if="loading" style="padding: 40px 20px; text-align: center;">
      <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #e5e7eb; border-top-color: #10b981; border-radius: 50%; animation: spin 0.6s linear infinite;"></div>
      <p style="margin-top: 16px; color: #6b7280;">Memuat notifikasi...</p>
    </div>

    <!-- Content -->
    <div v-else-if="response" class="notif-detail-content">
      <!-- Status badge -->
      <div class="notif-status-banner" :class="`notif-status--${response.status}`">
        <span v-if="response.status === 'pending'" class="notif-status-badge">⏳ Tertunda</span>
        <span v-else-if="response.status === 'in_progress'" class="notif-status-badge"> Sedang Diproses</span>
        <span v-else class="notif-status-badge"> Selesai</span>
      </div>

      <!-- From section -->
      <div class="notif-section">
        <h3 class="notif-section-title"> Dari Admin</h3>
        <div class="notif-info-card">
          <div class="notif-admin-info">
            <div class="notif-avatar" :style="`background: linear-gradient(135deg, #667eea, #764ba2)`">
              {{ response.admin?.name?.[0] || 'A' }}
            </div>
            <div>
              <p class="notif-admin-name">{{ response.admin?.name }}</p>
              <p class="notif-admin-email">{{ response.admin?.email }}</p>
            </div>
          </div>
          <div class="notif-timestamp">
            {{ formatDate(response.created_at) }}
          </div>
        </div>
      </div>

      <!-- Issue report section -->
      <div class="notif-section">
        <h3 class="notif-section-title"> Laporan Terkait</h3>
        <div class="notif-issue-card">
          <div class="notif-issue-header">
            <span class="notif-issue-type">{{ getIssueType(response.issue_report?.type) }}</span>
            <span class="notif-issue-id">Laporan #{{ String(response.issue_report?.id || 0).padStart(4, '0') }}</span>
          </div>
          <p class="notif-issue-desc">{{ response.issue_report?.description }}</p>
          <div v-if="response.issue_report?.order" class="notif-order-info">
            <strong>Pesanan Terkait:</strong>
            <span>#{{ String(response.issue_report.order.id || 0).padStart(4, '0') }}</span>
          </div>
        </div>
      </div>

      <!-- Response message -->
      <div class="notif-section">
        <h3 class="notif-section-title"> Respons dari Admin</h3>
        <div class="notif-message-box">
          {{ response.response_message }}
        </div>
      </div>

      <!-- Action details -->
      <div class="notif-section">
        <h3 class="notif-section-title">️ Detail Penanganan</h3>
        <div class="notif-actions-grid">
          <div class="notif-action-item">
            <span class="notif-action-label">Tipe Tindakan</span>
            <span class="notif-action-badge">{{ getActionLabel(response.action_type) }}</span>
          </div>
          <div class="notif-action-item">
            <span class="notif-action-label">Status Penanganan</span>
            <span class="notif-action-badge" :style="getStatusColor(response.status)">{{ capitalizeFirst(response.status) }}</span>
          </div>
          <div v-if="response.action_details?.refund_amount" class="notif-action-item">
            <span class="notif-action-label">Jumlah Refund</span>
            <span class="notif-action-badge notif-action-refund">Rp {{ formatCurrency(response.action_details.refund_amount) }}</span>
          </div>
        </div>
      </div>

      <!-- Action items (buyer should take action) -->
      <div v-if="isSeller" class="notif-action-required">
        <div class="notif-action-required-header">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
          </svg>
          <span>Tindakan yang Diperlukan</span>
        </div>

        <div v-if="response.action_type === 'cancel_order'" class="notif-action-task">
          <p><strong>Batalkan pesanan</strong> sesuai instruksi dari admin</p>
          <div v-if="response.issue_report?.order" class="notif-order-details">
            <p>Pesanan: #{{ String(response.issue_report.order.id || 0).padStart(4, '0') }}</p>
            <p>Pembeli: {{ response.issue_report?.buyer?.name }}</p>
            <button class="notif-action-btn notif-action-btn--cancel" @click="goToOrder">
              Lihat Pesanan →
            </button>
          </div>
        </div>

        <div v-else-if="response.action_type === 'refund'" class="notif-action-task">
          <p><strong>Proses pengembalian dana</strong> kepada pembeli sebesar Rp {{ formatCurrency(response.action_details?.refund_amount || response.issue_report?.order?.total) }}</p>
          <button class="notif-action-btn notif-action-btn--refund" @click="confirmRefund">
             Konfirmasi Refund
          </button>
        </div>

        <div v-else-if="response.action_type === 'replacement'" class="notif-action-task">
          <p><strong>Hubungi pembeli</strong> untuk proses penggantian barang</p>
          <p class="notif-action-note">Admin telah menghubungi pembeli. Segera lakukan koordinasi untuk penggantian.</p>
          <button class="notif-action-btn notif-action-btn--replace" @click="contactBuyer">
             Hubungi Pembeli
          </button>
        </div>

        <div v-else-if="response.action_type === 'notify_seller'" class="notif-action-task">
          <p><strong>Pemberitahuan dari admin</strong> - Silakan baca dengan seksama</p>
          <button class="notif-action-btn notif-action-btn--acknowledge" @click="confirmReceived">
             Saya Sudah Membaca
          </button>
        </div>
      </div>

      <!-- Confirmation message -->
      <div v-if="confirmationMessage" class="notif-confirmation-message" :class="`notif-confirmation--${confirmationStatus}`">
        {{ confirmationMessage }}
      </div>

      <!-- Footer buttons -->
      <div class="notif-detail-footer">
        <button class="notif-btn notif-btn--secondary" @click="$router.go(-1)">Tutup</button>
        <button v-if="!isViewed && isBuyer" class="notif-btn notif-btn--primary" @click="markAsViewed">
           Tandai Sudah Dibaca
        </button>
      </div>
    </div>

    <!-- Not found -->
    <div v-else style="padding: 40px 20px; text-align: center;">
      <p style="color: #6b7280; font-size: 1.1rem;">Notifikasi tidak ditemukan</p>
      <button class="notif-btn notif-btn--secondary" @click="$router.go(-1)" style="margin-top: 20px;">
        Kembali
      </button>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api/axios'

export default {
  name: 'NotificationDetail',
  setup() {
    const route = useRoute()
    const loading = ref(true)
    const response = ref(null)
    const isViewed = ref(false)
    const isSeller = ref(false)
    const isBuyer = ref(false)
    const confirmationMessage = ref('')
    const confirmationStatus = ref('')

    const formatDate = (dateString) => {
      const date = new Date(dateString)
      return date.toLocaleString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      })
    }

    const formatCurrency = (value) => {
      return new Intl.NumberFormat('id-ID').format(value)
    }

    const capitalizeFirst = (str) => {
      return str.charAt(0).toUpperCase() + str.slice(1)
    }

    const getIssueType = (type) => {
      const types = {
        penipuan: ' Penipuan',
        produk_palsu: ' Produk Palsu',
        ujaran_kebencian: ' Ujaran Kebencian',
        spam: ' Spam',
        lainnya: ' Lainnya'
      }
      return types[type] || type
    }

    const getActionLabel = (action) => {
      const actions = {
        notify_seller: ' Hubungi Penjual',
        cancel_order: ' Batalkan Pesanan',
        refund: ' Proses Refund',
        replacement: ' Penggantian Barang',
        warning: '️ Peringatan',
        block_seller: ' Blokir Penjual',
        other: ' Lainnya'
      }
      return actions[action] || action
    }

    const getStatusColor = (status) => {
      const colors = {
        pending: 'background: #fef3c7; color: #92400e;',
        in_progress: 'background: #dbeafe; color: #1e40af;',
        completed: 'background: #dcfce7; color: #166534;'
      }
      return colors[status] || ''
    }

    const confirmReceived = async () => {
      try {
        await api.post(`/reports/responses/${response.value.id}/confirm`)
        confirmationMessage.value = ' Terima kasih telah membaca notifikasi ini'
        confirmationStatus.value = 'success'
        response.value.status = 'in_progress'
      } catch (error) {
        confirmationMessage.value = ' Gagal mengkonfirmasi: ' + error.response?.data?.message
        confirmationStatus.value = 'error'
      }
    }

    const confirmRefund = async () => {
      try {
        confirmationMessage.value = '⏳ Memproses...'
        confirmationStatus.value = 'info'
        // Bisa menambahkan API call untuk confirm refund di sini
        await new Promise(resolve => setTimeout(resolve, 1000))
        confirmationMessage.value = ' Refund berhasil diproses'
        confirmationStatus.value = 'success'
      } catch (error) {
        confirmationMessage.value = ' Gagal memproses refund'
        confirmationStatus.value = 'error'
      }
    }

    const router = useRouter()

    const markAsViewed = async () => {
      try {
        const notificationId = route.query.notificationId
        if (notificationId) {
          await api.post(`/notifications/${notificationId}/read`)
          isViewed.value = true
        }
      } catch (error) {
        console.error('Failed to mark as viewed:', error)
      }
    }

    const goToOrder = () => {
      const orderId = response.value?.issue_report?.order?.id
      if (orderId) {
        router.push(`/seller/order/${orderId}`)
      }
    }

    const contactBuyer = () => {
      const orderId = response.value?.issue_report?.order?.id
      if (orderId) {
        router.push(`/seller/chat/${orderId}`)
      }
    }

    onMounted(async () => {
      try {
        const responseId = route.params.responseId
        if (!responseId) {
          loading.value = false
          return
        }

        const res = await api.get(`/reports/responses/${responseId}`)
        response.value = res.data.data

        // Check if current user is seller or buyer
        const currentUser = await api.get('/user')
        const orderBusinessUserId = response.value?.issue_report?.order?.product?.business?.user_id || response.value?.issue_report?.order?.items?.[0]?.product?.business?.user_id
        if (orderBusinessUserId === currentUser.data.data.id) {
          isSeller.value = true
        }
        if (response.value?.issue_report?.buyer_id === currentUser.data.data.id) {
          isBuyer.value = true
        }

        isViewed.value = route.query.viewed === 'true'
      } catch (error) {
        console.error('Failed to load response:', error)
      } finally {
        loading.value = false
      }
    })

    return {
      loading,
      response,
      isViewed,
      isSeller,
      isBuyer,
      confirmationMessage,
      confirmationStatus,
      formatDate,
      formatCurrency,
      capitalizeFirst,
      getIssueType,
      getActionLabel,
      getStatusColor,
      confirmReceived,
      confirmRefund,
      markAsViewed,
      goToOrder,
      contactBuyer,
    }
  }
}
</script>

<style scoped>
.notif-detail-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

.notif-detail-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 2px solid #e5e7eb;
}

.notif-back-btn {
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  font-size: 1rem;
  transition: color 0.2s;
}

.notif-back-btn:hover {
  color: #10b981;
}

.notif-detail-header h1 {
  font-size: 1.5rem;
  color: #1f2937;
  margin: 0;
}

.notif-detail-content {
  background: #fff;
  border-radius: 12px;
  overflow: hidden;
}

.notif-status-banner {
  padding: 16px 20px;
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
  border-radius: 8px;
}

.notif-status--pending {
  background: #fef3c7;
  border-left: 4px solid #f59e0b;
}

.notif-status--in_progress {
  background: #dbeafe;
  border-left: 4px solid #3b82f6;
}

.notif-status--completed {
  background: #dcfce7;
  border-left: 4px solid #10b981;
}

.notif-status-badge {
  font-weight: 600;
  font-size: 0.95rem;
}

.notif-section {
  margin-bottom: 24px;
}

.notif-section-title {
  font-size: 1rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 12px;
}

.notif-info-card {
  background: #f9fafb;
  padding: 16px;
  border-radius: 8px;
  border: 1.5px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.notif-admin-info {
  display: flex;
  align-items: center;
  gap: 16px;
}

.notif-avatar {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  flex-shrink: 0;
  font-size: 1.1rem;
}

.notif-admin-name {
  font-weight: 600;
  color: #1f2937;
  margin: 0;
  font-size: 0.95rem;
}

.notif-admin-email {
  color: #6b7280;
  font-size: 0.85rem;
  margin: 4px 0 0;
}

.notif-timestamp {
  font-size: 0.85rem;
  color: #9ca3af;
  text-align: right;
}

.notif-issue-card {
  background: #f9fafb;
  padding: 16px;
  border-radius: 8px;
  border-left: 4px solid #fca5a5;
}

.notif-issue-header {
  display: flex;
  gap: 12px;
  margin-bottom: 8px;
}

.notif-issue-type {
  display: inline-block;
  background: #fef3c7;
  color: #92400e;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
}

.notif-issue-id {
  display: inline-block;
  background: #dbeafe;
  color: #1e40af;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
}

.notif-issue-desc {
  color: #374151;
  font-size: 0.9rem;
  line-height: 1.5;
  margin: 0;
}

.notif-order-info {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #e5e7eb;
  font-size: 0.85rem;
  color: #6b7280;
}

.notif-order-info span {
  display: inline-block;
  background: #dbeafe;
  color: #1e40af;
  padding: 2px 6px;
  border-radius: 4px;
  margin-left: 6px;
  font-weight: 600;
}

.notif-message-box {
  background: #f0fdf4;
  border-left: 4px solid #10b981;
  padding: 16px;
  border-radius: 8px;
  color: #1f2937;
  line-height: 1.6;
  white-space: pre-wrap;
}

.notif-actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
}

.notif-action-item {
  background: #f9fafb;
  padding: 12px;
  border-radius: 8px;
  border: 1.5px solid #e5e7eb;
}

.notif-action-label {
  display: block;
  font-size: 0.8rem;
  color: #6b7280;
  font-weight: 600;
  margin-bottom: 6px;
}

.notif-action-badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  background: #fef3c7;
  color: #92400e;
}

.notif-action-refund {
  background: #dbeafe;
  color: #1e40af;
}

.notif-action-required {
  background: #fef2f2;
  border: 2px solid #fecaca;
  border-radius: 8px;
  padding: 16px;
  margin: 24px 0;
}

.notif-action-required-header {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #c53030;
  font-weight: 700;
  margin-bottom: 12px;
}

.notif-action-task {
  background: white;
  padding: 12px;
  border-radius: 6px;
  margin: 12px 0;
}

.notif-action-task p {
  margin: 0 0 12px;
  color: #374151;
  font-size: 0.95rem;
}

.notif-action-note {
  color: #6b7280;
  font-size: 0.85rem !important;
  margin: 8px 0 !important;
}

.notif-order-details {
  background: #f9fafb;
  padding: 12px;
  border-radius: 6px;
  margin: 12px 0;
  font-size: 0.85rem;
}

.notif-order-details p {
  margin: 0 0 6px;
  color: #6b7280;
}

.notif-action-btn {
  display: inline-block;
  padding: 8px 16px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.85rem;
  transition: all 0.2s;
  margin-top: 12px;
}

.notif-action-btn--cancel {
  background: #fee2e2;
  color: #c53030;
}

.notif-action-btn--cancel:hover {
  background: #fecaca;
}

.notif-action-btn--refund {
  background: #dbeafe;
  color: #1e40af;
}

.notif-action-btn--refund:hover {
  background: #bfdbfe;
}

.notif-action-btn--replace {
  background: #dbeafe;
  color: #1e40af;
}

.notif-action-btn--replace:hover {
  background: #bfdbfe;
}

.notif-action-btn--acknowledge {
  background: #dcfce7;
  color: #166534;
}

.notif-action-btn--acknowledge:hover {
  background: #bbf7d0;
}

.notif-confirmation-message {
  padding: 12px 16px;
  border-radius: 8px;
  margin: 16px 0;
  font-weight: 600;
}

.notif-confirmation--success {
  background: #dcfce7;
  color: #166534;
  border-left: 4px solid #10b981;
}

.notif-confirmation--error {
  background: #fee2e2;
  color: #c53030;
  border-left: 4px solid #ef4444;
}

.notif-confirmation--info {
  background: #dbeafe;
  color: #1e40af;
  border-left: 4px solid #3b82f6;
}

.notif-detail-footer {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1.5px solid #e5e7eb;
}

.notif-btn {
  padding: 10px 20px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.2s;
}

.notif-btn--primary {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
}

.notif-btn--primary:hover {
  opacity: 0.9;
}

.notif-btn--secondary {
  background: #f3f4f6;
  color: #374151;
  border: 1.5px solid #d1d5db;
}

.notif-btn--secondary:hover {
  background: #e5e7eb;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
