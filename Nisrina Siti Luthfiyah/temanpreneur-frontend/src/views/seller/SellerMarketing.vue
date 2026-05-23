<template>
  <section class="marketing">
    <header class="marketing__head">
      <h1 class="marketing__title">Marketing Tools</h1>
      <p class="marketing__sub">Tingkatkan penjualan dengan tools marketing eksklusif untuk seller premium.</p>
    </header>

    <div v-if="loading" class="marketing__loading">
      <div class="skeleton" style="height: 120px; border-radius: 12px; margin-bottom: 16px;"></div>
      <div class="skeleton" style="height: 200px; border-radius: 12px;"></div>
    </div>

    <div v-else class="marketing__content">
      <!-- Discount Campaign -->
      <div class="marketing__card">
        <div class="marketing__card-header">
          <h2>️ Kampanye Diskon</h2>
          <span class="badge badge-premium">Premium</span>
        </div>
        <p class="marketing__card-desc">Buat kampanye diskon untuk meningkatkan penjualan dan retensi pelanggan.</p>
        <div class="marketing__card-body">
          <div class="campaign-item">
            <span>Flash Sale (Diskon hingga 70%)</span>
            <button class="btn btn-sm">Buat Kampanye</button>
          </div>
          <div class="campaign-item">
            <span>Bundle Deals (Paket hemat produk)</span>
            <button class="btn btn-sm">Buat Paket</button>
          </div>
          <div class="campaign-item">
            <span>Member Exclusive (Khusus member setia)</span>
            <button class="btn btn-sm">Konfigurasi</button>
          </div>
        </div>
      </div>

      <!-- Email Marketing -->
      <div class="marketing__card">
        <div class="marketing__card-header">
          <h2> Email Marketing</h2>
          <span class="badge badge-premium">Premium</span>
        </div>
        <p class="marketing__card-desc">Kirim email promosi ke pelanggan setia dan dapatkan repeat order.</p>
        <div class="marketing__card-body">
          <div class="email-stats">
            <div class="stat">
              <p class="stat-label">Email Terkirim</p>
              <p class="stat-value">{{ emailStats.sent || 0 }}</p>
            </div>
            <div class="stat">
              <p class="stat-label">Open Rate</p>
              <p class="stat-value">{{ emailStats.openRate || 0 }}%</p>
            </div>
            <div class="stat">
              <p class="stat-label">Click Rate</p>
              <p class="stat-value">{{ emailStats.clickRate || 0 }}%</p>
            </div>
          </div>
          <button class="btn btn-primary" style="width: 100%; margin-top: 12px;">Buat Email Campaign</button>
        </div>
      </div>

      <!-- Social Media Integration -->
      <div class="marketing__card">
        <div class="marketing__card-header">
          <h2> Social Media Integration</h2>
          <span class="badge badge-premium">Premium</span>
        </div>
        <p class="marketing__card-desc">Integrasikan toko Anda dengan media sosial untuk jangkauan lebih luas.</p>
        <div class="marketing__card-body">
          <div class="social-links">
            <div class="social-item">
              <span class="social-icon">f</span>
              <span>Facebook Shop</span>
              <button class="btn btn-sm">{{ socialConnected.facebook ? 'Kelola' : 'Hubungkan' }}</button>
            </div>
            <div class="social-item">
              <span class="social-icon"></span>
              <span>Instagram Shop</span>
              <button class="btn btn-sm">{{ socialConnected.instagram ? 'Kelola' : 'Hubungkan' }}</button>
            </div>
            <div class="social-item">
              <span class="social-icon"></span>
              <span>TikTok Shop</span>
              <button class="btn btn-sm">{{ socialConnected.tiktok ? 'Kelola' : 'Hubungkan' }}</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Referral Program -->
      <div class="marketing__card">
        <div class="marketing__card-header">
          <h2> Program Referral</h2>
          <span class="badge badge-premium">Premium</span>
        </div>
        <p class="marketing__card-desc">Ajak teman untuk menjual produk Anda dan dapatkan komisi.</p>
        <div class="marketing__card-body">
          <div class="referral-info">
            <p>Kode Referral: <strong>{{ referralCode }}</strong></p>
            <button class="btn btn-sm" @click="copyReferralCode"> Copy</button>
          </div>
          <div class="referral-stats">
            <div class="stat">
              <p class="stat-label">Referral Aktif</p>
              <p class="stat-value">{{ referralStats.active || 0 }}</p>
            </div>
            <div class="stat">
              <p class="stat-label">Komisi Earned</p>
              <p class="stat-value">{{ formatRupiah(referralStats.earned) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Analytics & Reports -->
      <div class="marketing__card">
        <div class="marketing__card-header">
          <h2> Laporan Marketing</h2>
          <span class="badge badge-premium">Premium</span>
        </div>
        <p class="marketing__card-desc">Pantau performa kampanye marketing Anda secara real-time.</p>
        <div class="marketing__card-body">
          <div class="report-actions">
            <button class="btn btn-sm"> Download Laporan</button>
            <button class="btn btn-sm"> Lihat Grafik</button>
            <button class="btn btn-sm"> Saran Optimasi</button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api/axios'

const loading = ref(true)
const emailStats = ref({ sent: 0, openRate: 0, clickRate: 0 })
const referralCode = ref('SELLER' + Math.random().toString(36).substring(7).toUpperCase())
const referralStats = ref({ active: 0, earned: 0 })
const socialConnected = ref({ facebook: false, instagram: false, tiktok: false })

const formatRupiah = (value) => {
  if (!value) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(value)
}

const copyReferralCode = () => {
  navigator.clipboard.writeText(referralCode.value)
  alert('Kode referral berhasil dicopy!')
}

const fetchMarketingData = async () => {
  try {
    const response = await api.get('/seller/marketing')
    const data = response?.data || {}
    
    if (data.emailStats) emailStats.value = data.emailStats
    if (data.referralStats) referralStats.value = data.referralStats
    if (data.socialConnected) socialConnected.value = data.socialConnected
  } catch (error) {
    console.error('Failed to fetch marketing data:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchMarketingData()
})
</script>

<style scoped>
.marketing {
  padding: 24px;
}

.marketing__head {
  margin-bottom: 24px;
}

.marketing__title {
  font-size: 1.875rem;
  font-weight: 900;
  color: #111827;
  margin: 0 0 8px;
}

.marketing__sub {
  color: #6b7280;
  margin: 0;
  font-size: 0.95rem;
}

.marketing__loading {
  display: grid;
  gap: 16px;
}

.marketing__content {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 20px;
}

.marketing__card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
  transition: all 0.3s ease;
}

.marketing__card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transform: translateY(-2px);
}

.marketing__card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.marketing__card-header h2 {
  margin: 0;
  font-size: 1.1rem;
  color: #111827;
}

.badge {
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
}

.badge-premium {
  background: #fef3c7;
  color: #92400e;
}

.marketing__card-desc {
  color: #6b7280;
  font-size: 0.9rem;
  margin-bottom: 14px;
}

.marketing__card-body {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.campaign-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px;
  background: #f9fafb;
  border-radius: 8px;
}

.email-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
  margin-bottom: 10px;
}

.stat {
  background: #f9fafb;
  padding: 10px;
  border-radius: 8px;
  text-align: center;
}

.stat-label {
  font-size: 0.75rem;
  color: #9ca3af;
  text-transform: uppercase;
  margin: 0;
}

.stat-value {
  font-size: 1.4rem;
  font-weight: 900;
  color: #111827;
  margin: 4px 0 0;
}

.social-links {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.social-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  background: #f9fafb;
  border-radius: 8px;
}

.social-icon {
  font-size: 1.2rem;
  font-weight: 900;
}

.referral-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px;
  background: #f9fafb;
  border-radius: 8px;
  margin-bottom: 10px;
}

.referral-stats {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.report-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.btn {
  padding: 8px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #fff;
  color: #111827;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.9rem;
}

.btn:hover {
  background: #f3f4f6;
}

.btn-sm {
  padding: 6px 10px;
  font-size: 0.8rem;
}

.btn-primary {
  background: #2563eb;
  color: #fff;
  border: none;
}

.btn-primary:hover {
  background: #1d4ed8;
}

.skeleton {
  background: linear-gradient(90deg, #e5e7eb 25%, #d1d5db 50%, #e5e7eb 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
</style>
