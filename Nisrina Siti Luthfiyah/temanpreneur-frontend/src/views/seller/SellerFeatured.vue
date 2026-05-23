<template>
  <section class="featured">
    <header class="featured__head">
      <h1 class="featured__title">Featured Listing</h1>
      <p class="featured__sub">Tampilkan produk Anda di halaman utama dan tingkatkan visibilitas hingga 5x lipat.</p>
    </header>

    <div v-if="loading" class="featured__loading">
      <div class="skeleton" style="height: 100px;"></div>
      <div class="skeleton" style="height: 200px;"></div>
    </div>

    <div v-else class="featured__container">
      <!-- Current Featured Product -->
      <div v-if="currentFeatured" class="featured__current">
        <div class="featured__current-header">
          <h2>Produk Featured Aktif</h2>
          <span class="badge badge-active"> Aktif</span>
        </div>
        <div class="featured__product-card">
          <img v-if="currentFeatured.image" :src="currentFeatured.image" :alt="currentFeatured.name" class="featured__product-image" />
          <div v-else class="featured__product-image-empty">Tidak ada gambar</div>
          <div class="featured__product-info">
            <h3>{{ currentFeatured.name }}</h3>
            <p class="featured__product-price">{{ formatRupiah(currentFeatured.price) }}</p>
            <p class="featured__product-meta">{{ currentFeatured.views || 0 }} kunjungan</p>
            <p class="featured__product-until">Featured hingga: {{ formatDate(currentFeatured.featured_until) }}</p>
            <button class="btn btn-danger" @click="removeFeatured(currentFeatured.id)">Hapus Featured</button>
          </div>
        </div>
      </div>

      <!-- Available Featured Slots -->
      <div class="featured__slots">
        <div class="featured__slots-header">
          <h2>Featured Slots Tersedia</h2>
          <span class="slot-badge">{{ availableSlots }} slot tersedia</span>
        </div>
        <p class="featured__slots-desc">Tingkatkan produk Anda ke featured untuk mendapatkan eksposur maksimal.</p>
        
        <div v-if="availableSlots > 0" class="featured__select-product">
          <label>Pilih Produk untuk Difeatured:</label>
          <select v-model="selectedProductId" class="select-input">
            <option value="">-- Pilih Produk --</option>
            <option v-for="product in availableProducts" :key="product.id" :value="product.id">
              {{ product.name }} - {{ formatRupiah(product.price) }}
            </option>
          </select>
          
          <label>Durasi Featured:</label>
          <div class="duration-options">
            <button
              v-for="option in durationOptions"
              :key="option.value"
              class="duration-btn"
              :class="{ active: selectedDuration === option.value }"
              @click="selectedDuration = option.value"
            >
              {{ option.label }}
              <span class="duration-price">{{ formatRupiah(option.price) }}</span>
            </button>
          </div>

          <button 
            class="btn btn-primary btn-block"
            :disabled="!selectedProductId || processing"
            @click="addFeatured"
          >
            {{ processing ? 'Memproses...' : 'Featured Sekarang' }}
          </button>
        </div>

        <div v-else class="featured__no-slots">
          <p> Semua slot featured Anda sudah terisi. Upgrade ke paket lebih tinggi untuk mendapat slot tambahan.</p>
        </div>
      </div>

      <!-- Featured History -->
      <div class="featured__history">
        <h2>Riwayat Featured</h2>
        <table class="featured__table">
          <thead>
            <tr>
              <th>Produk</th>
              <th>Durasi</th>
              <th>Tanggal Berakhir</th>
              <th>Hasil</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="history in featuredHistory" :key="history.id">
              <td>{{ history.productName }}</td>
              <td>{{ history.duration }} hari</td>
              <td>{{ formatDate(history.endDate) }}</td>
              <td>
                <span class="history-stat">{{ history.clicks }} klik</span>
                <span class="history-stat">{{ history.orders }} order</span>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="!featuredHistory.length" class="featured__empty">
          <p>Belum ada riwayat featured.</p>
        </div>
      </div>

      <!-- Pricing & Benefits -->
      <div class="featured__benefits">
        <h2>Keuntungan Featured</h2>
        <div class="benefits-grid">
          <div class="benefit-card">
            <span class="benefit-icon">️</span>
            <h3>5x Lebih Banyak Kunjungan</h3>
            <p>Produk Anda akan tampil di halaman utama marketplace</p>
          </div>
          <div class="benefit-card">
            <span class="benefit-icon"></span>
            <h3>Target Pembeli Tepat</h3>
            <p>Ditampilkan kepada pembeli yang relevan dengan kategori</p>
          </div>
          <div class="benefit-card">
            <span class="benefit-icon"></span>
            <h3>Boost Penjualan</h3>
            <p>Meningkatkan conversion rate hingga 3x lipat</p>
          </div>
          <div class="benefit-card">
            <span class="benefit-icon">⭐</span>
            <h3>Brand Awareness</h3>
            <p>Tingkatkan reputasi dan kepercayaan pembeli</p>
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
const processing = ref(false)
const currentFeatured = ref(null)
const availableProducts = ref([])
const availableSlots = ref(1)
const selectedProductId = ref('')
const selectedDuration = ref('7')
const featuredHistory = ref([])

const durationOptions = [
  { label: '7 Hari', value: '7', price: 99000 },
  { label: '30 Hari', value: '30', price: 349000 },
  { label: '90 Hari', value: '90', price: 899000 },
]

const formatRupiah = (value) => {
  if (!value) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(value)
}

const formatDate = (dateString) => {
  return new Intl.DateTimeFormat('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  }).format(new Date(dateString))
}

const fetchFeaturedData = async () => {
  try {
    const response = await api.get('/seller/featured')
    const data = response?.data || {}

    if (data.currentFeatured) currentFeatured.value = data.currentFeatured
    if (data.availableProducts) availableProducts.value = data.availableProducts
    if (data.availableSlots) availableSlots.value = data.availableSlots
    if (data.featuredHistory) featuredHistory.value = data.featuredHistory
  } catch (error) {
    console.error('Failed to fetch featured data:', error)
  } finally {
    loading.value = false
  }
}

const addFeatured = async () => {
  if (!selectedProductId.value) {
    alert('Pilih produk terlebih dahulu')
    return
  }

  processing.value = true
  try {
    await api.post('/seller/featured', {
      product_id: selectedProductId.value,
      duration: parseInt(selectedDuration.value),
    })
    alert('Produk berhasil difeatured!')
    selectedProductId.value = ''
    selectedDuration.value = '7'
    fetchFeaturedData()
  } catch (error) {
    console.error('Failed to add featured:', error)
    alert('Gagal membuat featured. Coba lagi.')
  } finally {
    processing.value = false
  }
}

const removeFeatured = async (productId) => {
  if (!confirm('Apakah Anda yakin ingin menghapus featured ini?')) return

  try {
    await api.delete(`/seller/featured/${productId}`)
    alert('Featured berhasil dihapus')
    fetchFeaturedData()
  } catch (error) {
    console.error('Failed to remove featured:', error)
    alert('Gagal menghapus featured. Coba lagi.')
  }
}

onMounted(() => {
  fetchFeaturedData()
})
</script>

<style scoped>
.featured {
  padding: 24px;
}

.featured__head {
  margin-bottom: 24px;
}

.featured__title {
  font-size: 1.875rem;
  font-weight: 900;
  color: #111827;
  margin: 0 0 8px;
}

.featured__sub {
  color: #6b7280;
  margin: 0;
  font-size: 0.95rem;
}

.featured__loading {
  display: grid;
  gap: 16px;
}

.featured__container {
  display: grid;
  gap: 24px;
}

.featured__current {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  border: 2px solid #fcd34d;
  border-radius: 12px;
  padding: 20px;
}

.featured__current-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.featured__current-header h2 {
  margin: 0;
  font-size: 1.2rem;
  color: #92400e;
}

.badge-active {
  background: #22c55e;
  color: #fff;
  padding: 6px 10px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
}

.featured__product-card {
  display: grid;
  grid-template-columns: 200px 1fr;
  gap: 20px;
  background: #fff;
  border-radius: 10px;
  overflow: hidden;
}

.featured__product-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.featured__product-image-empty {
  width: 100%;
  height: 200px;
  background: #f3f4f6;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #9ca3af;
}

.featured__product-info {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.featured__product-info h3 {
  margin: 0;
  font-size: 1.1rem;
}

.featured__product-price {
  font-size: 1.5rem;
  font-weight: 900;
  color: #059669;
  margin: 0;
}

.featured__product-meta {
  color: #6b7280;
  font-size: 0.9rem;
  margin: 0;
}

.featured__product-until {
  color: #92400e;
  font-weight: 600;
  margin: 0;
}

.featured__slots {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
}

.featured__slots-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.featured__slots-header h2 {
  margin: 0;
  font-size: 1.2rem;
}

.slot-badge {
  background: #e0f2fe;
  color: #0369a1;
  padding: 6px 10px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
}

.featured__slots-desc {
  color: #6b7280;
  margin-bottom: 16px;
}

.featured__select-product {
  display: grid;
  gap: 14px;
}

label {
  font-weight: 700;
  color: #374151;
  display: block;
}

.select-input {
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.95rem;
}

.duration-options {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
  gap: 10px;
}

.duration-btn {
  padding: 12px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  background: #fff;
  cursor: pointer;
  transition: all 0.2s;
  text-align: center;
}

.duration-btn:hover {
  border-color: #d1d5db;
  background: #f9fafb;
}

.duration-btn.active {
  background: #2563eb;
  color: #fff;
  border-color: #2563eb;
}

.duration-price {
  display: block;
  font-size: 0.8rem;
  font-weight: 700;
}

.btn-block {
  width: 100%;
}

.btn-danger {
  background: #ef4444;
  color: #fff;
  border: none;
  padding: 10px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 700;
}

.btn-danger:hover {
  background: #dc2626;
}

.featured__no-slots {
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 8px;
  padding: 16px;
  color: #7f1d1d;
}

.featured__history {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
}

.featured__history h2 {
  margin-top: 0;
}

.featured__table {
  width: 100%;
  border-collapse: collapse;
}

.featured__table th,
.featured__table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
}

.featured__table th {
  background: #f9fafb;
  font-weight: 700;
  color: #374151;
}

.history-stat {
  display: inline-block;
  margin-right: 10px;
  padding: 4px 8px;
  background: #f3f4f6;
  border-radius: 6px;
  font-size: 0.85rem;
}

.featured__empty {
  text-align: center;
  color: #6b7280;
  padding: 20px;
}

.featured__benefits {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
}

.benefits-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-top: 16px;
}

.benefit-card {
  background: #f9fafb;
  border-radius: 10px;
  padding: 16px;
  text-align: center;
}

.benefit-icon {
  font-size: 2rem;
  display: block;
  margin-bottom: 8px;
}

.benefit-card h3 {
  margin: 0 0 8px;
  font-size: 1rem;
}

.benefit-card p {
  margin: 0;
  color: #6b7280;
  font-size: 0.85rem;
}

.btn {
  padding: 10px 16px;
  border: none;
  border-radius: 8px;
  background: #fff;
  color: #111827;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary {
  background: #2563eb;
  color: #fff;
}

.btn-primary:hover:not(:disabled) {
  background: #1d4ed8;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.skeleton {
  background: linear-gradient(90deg, #e5e7eb 25%, #d1d5db 50%, #e5e7eb 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
  border-radius: 12px;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

@media (max-width: 600px) {
  .featured__product-card {
    grid-template-columns: 1fr;
  }

  .benefits-grid {
    grid-template-columns: 1fr;
  }
}
</style>
