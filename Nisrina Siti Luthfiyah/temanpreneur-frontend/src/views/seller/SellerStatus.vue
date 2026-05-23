<template>
  <div class="seller-status-page">
    <div class="container">
      <h1>Status Pengajuan Toko</h1>

      <div v-if="loading" class="loading">
        <p>Memuat status...</p>
      </div>

      <div v-else-if="business" class="status-card">
        <div class="status-header">
          <h2>{{ business.name }}</h2>
          <span :class="`status-badge status-${business.status}`">
            {{ statusLabel }}
          </span>
        </div>

        <div class="status-content">
          <p v-if="business.status === 'pending'">
            Pengajuan toko Anda sedang dalam proses verifikasi oleh admin.
            Biasanya memakan waktu 1-2 hari kerja.
          </p>
          <p v-else-if="business.status === 'approved'">
            Toko Anda telah disetujui! Anda dapat mulai menjual produk.
          </p>
          <p v-else-if="business.status === 'rejected'">
            Pengajuan toko Anda ditolak.
            <br>Alasan: {{ business.rejection_reason }}
          </p>

          <div v-if="business.status === 'approved'" class="actions">
            <router-link to="/seller" class="btn btn-primary">
              Kelola Toko
            </router-link>
          </div>
        </div>
      </div>

      <div v-else class="no-business">
        <p>Anda belum mengajukan toko.</p>
        <router-link to="/seller/apply" class="btn btn-primary">
          Ajukan Toko
        </router-link>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import api from '@/api/axios'

export default {
  name: 'SellerStatus',

  setup() {
    const business = ref(null)
    const loading = ref(true)

    const statusLabel = computed(() => {
      const labels = {
        pending: 'Menunggu Verifikasi',
        approved: 'Disetujui',
        rejected: 'Ditolak'
      }
      return labels[business.value?.status] || 'Unknown'
    })

    const fetchBusiness = async () => {
      try {
        const res = await api.get('/businesses')
        const raw = res.data?.data
        business.value = Array.isArray(raw) ? raw[0] ?? null : raw ?? null
      } catch (err) {
        console.error('Error fetching business:', err)
      } finally {
        loading.value = false
      }
    }

    onMounted(fetchBusiness)

    return {
      business,
      loading,
      statusLabel
    }
  }
}
</script>

<style scoped>
.seller-status-page {
  padding: 2rem 0;
}

.container {
  max-width: 600px;
  margin: 0 auto;
  padding: 0 1rem;
}

.status-card {
  background: white;
  border-radius: 8px;
  padding: 2rem;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.status-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.status-badge {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.875rem;
}

.status-pending { background: #fef3c7; color: #d97706; }
.status-approved { background: #d1fae5; color: #065f46; }
.status-rejected { background: #fee2e2; color: #dc2626; }

.actions {
  margin-top: 2rem;
}

.btn {
  display: inline-block;
  padding: 0.75rem 1.5rem;
  background: #3b82f6;
  color: white;
  text-decoration: none;
  border-radius: 6px;
  font-weight: 600;
}

.btn-primary:hover {
  background: #2563eb;
}
</style>