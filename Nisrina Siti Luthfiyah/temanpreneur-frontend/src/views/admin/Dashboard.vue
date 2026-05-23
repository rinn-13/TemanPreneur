<template>
  <div class="ap">
    <!-- Heading -->
    <div class="ap__head">
      <div>
        <h1 class="ap__title">Dashboard <span>Admin</span></h1>
        <p class="ap__sub">Selamat datang kembali! Berikut ringkasan platform hari ini.</p>
      </div>
      <div class="admin-greeting__date">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
          <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2" />
          <line x1="16" y1="2" x2="16" y2="6" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
          <line x1="8" y1="2" x2="8" y2="6" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
          <line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="2" />
        </svg>
        {{ todayDate }}
      </div>
    </div>

    <!-- Stat cards -->
    <div class="stats-grid" v-if="loadingStats">
      <div v-for="n in 4" :key="n" class="stat-skeleton"></div>
    </div>

    <div class="stats-grid" v-else>
      <div class="stat-card stat-card--users">
        <div class="stat-card__icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
            <path
              d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"
              stroke="currentColor"
              stroke-width="2"
            />
            <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2" />
            <path
              d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"
              stroke="currentColor"
              stroke-width="2"
            />
          </svg>
        </div>
        <div class="stat-card__body">
          <p class="stat-card__label">Total Pengguna</p>
          <p class="stat-card__value">
            {{ stats.totalUsers?.toLocaleString('id-ID') || '0' }}
          </p>
        </div>
        <div class="stat-card__trend stat-card__trend--up">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none">
            <polyline
              points="23,6 13.5,15.5 8.5,10.5 1,18"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
            />
          </svg>
        </div>
      </div>

      <div class="stat-card stat-card--business">
        <div class="stat-card__icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
            <path
              d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"
              stroke="currentColor"
              stroke-width="2"
            />
            <polyline
              points="9,22 9,12 15,12 15,22"
              stroke="currentColor"
              stroke-width="2"
            />
          </svg>
        </div>
        <div class="stat-card__body">
          <p class="stat-card__label">Usaha Terverifikasi</p>
          <p class="stat-card__value">
            {{ stats.verifiedBusinesses?.toLocaleString('id-ID') || '0' }}
          </p>
        </div>
        <div class="stat-card__trend stat-card__trend--up">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none">
            <polyline
              points="23,6 13.5,15.5 8.5,10.5 1,18"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
            />
          </svg>
        </div>
      </div>

      <div class="stat-card stat-card--orders">
        <div class="stat-card__icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
            <path
              d="M16 4h2a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2"
              stroke="currentColor"
              stroke-width="2"
            />
            <rect
              x="8"
              y="2"
              width="8"
              height="4"
              rx="1"
              stroke="currentColor"
              stroke-width="2"
            />
          </svg>
        </div>
        <div class="stat-card__body">
          <p class="stat-card__label">Total Pesanan</p>
          <p class="stat-card__value">
            {{ stats.totalOrders?.toLocaleString('id-ID') || '0' }}
          </p>
        </div>
        <div class="stat-card__trend stat-card__trend--up">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none">
            <polyline
              points="23,6 13.5,15.5 8.5,10.5 1,18"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
            />
          </svg>
        </div>
      </div>

      <div class="stat-card stat-card--revenue">
        <div class="stat-card__icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
            <line
              x1="12"
              y1="1"
              x2="12"
              y2="23"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
            />
            <path
              d="M17 5H9.5a3.5 3.5 0 100 7h5a3.5 3.5 0 110 7H6"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
            />
          </svg>
        </div>
        <div class="stat-card__body">
          <p class="stat-card__label">Total Omzet</p>
          <p class="stat-card__value stat-card__value--small">
            {{ formatRupiah(stats.totalRevenue) }}
          </p>
        </div>
        <div class="stat-card__trend stat-card__trend--up">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none">
            <polyline
              points="23,6 13.5,15.5 8.5,10.5 1,18"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
            />
          </svg>
        </div>
      </div>
    </div>

    <!-- Produk per toko (transparansi admin) -->
    <div class="admin-store-products">
      <h3 class="admin-store-products-title">Produk per Toko</h3>
      <div class="admin-store-products-grid">
        <div v-for="store in productsByStore" :key="store.store" class="admin-store-card">
          <h4>{{ store.store }}</h4>
          <p>Produk: {{ store.count }}</p>
          <p>Potensi omset: {{ formatRupiah(store.total) }}</p>
          <router-link :to="`/admin/konten?store=${encodeURIComponent(store.store)}`" class="admin-store-cta">Lihat detail→</router-link>
        </div>
      </div>
    </div>

    <!-- Panels bawah -->
    <div class="admin-panels">
      <!-- Pengajuan usaha terbaru -->
      <div class="admin-panel admin-panel--main">
        <div class="admin-panel__header">
          <div>
            <h3 class="admin-panel__title">Pengajuan Usaha Terbaru</h3>
            <p class="admin-panel__sub">Menunggu verifikasi dari admin</p>
          </div>
          <router-link
            to="/admin/verifikasi"
            class="admin-panel__link"
          >
            Lihat semua →
          </router-link>
        </div>

        <div v-if="loadingApplications" class="admin-panel__loading">
          <div
            v-for="n in 4"
            :key="n"
            class="admin-panel__row-skeleton"
          ></div>
        </div>

        <div v-else-if="!recentApplications.length" class="admin-panel__empty">
          <span></span>
          <p>Tidak ada pengajuan baru</p>
        </div>

        <div v-else class="admin-panel__table">
          <div class="admin-table-head">
            <span>Nama Usaha</span>
            <span>Pengaju</span>
            <span>Kategori</span>
            <span>Tanggal</span>
            <span>Aksi</span>
          </div>
          <div
            v-for="app in recentApplications"
            :key="app.id"
            class="admin-table-row"
          >
            <div class="admin-table-row__biz">
              <div class="admin-table-row__biz-icon">
                {{ getCatIcon(app.category) }}
              </div>
              <span>{{ app.name }}</span>
            </div>
            <div class="admin-table-row__user">
              <div
                class="admin-table-row__avatar"
                :style="`background:${getColor(app.user_id)}`"
              >
              {{ app?.user_name?.charAt(0) || '?' }}
              </div>
              <span>{{ app.user_name }}</span>
            </div>
            <span class="admin-table-row__kat">
              {{ getCatLabel(app.category) }}
            </span>
            <span class="admin-table-row__date">
              {{ formatDate(app.created_at) }}
            </span>
            <div class="admin-table-row__actions">
              <button
                class="admin-tbl-btn admin-tbl-btn--approve"
                @click="quickApprove(app)"
                type="button"
              >
                Setujui
              </button>
              <button
                class="admin-tbl-btn admin-tbl-btn--reject"
                @click="quickReject(app)"
                type="button"
              >
                Tolak
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Panel kanan: pending & akses cepat -->
      <div class="admin-panel-side">
        <div class="admin-panel admin-panel--side">
          <div class="admin-panel__header">
            <h3 class="admin-panel__title">Perlu Tindakan</h3>
          </div>
          <div class="admin-pending-list">
            <router-link
              to="/admin/verifikasi"
              class="admin-pending-item"
            >
              <div class="admin-pending-item__dot admin-pending-item__dot--yellow"></div>
              <div class="admin-pending-item__info">
                <p>Pengajuan Usaha</p>
                <span>Menunggu verifikasi</span>
              </div>
              <strong class="admin-pending-item__count">
                {{ stats.pendingApplications ?? 3 }}
              </strong>
            </router-link>
            <router-link
              to="/admin/laporan"
              class="admin-pending-item"
            >
              <div class="admin-pending-item__dot admin-pending-item__dot--red"></div>
              <div class="admin-pending-item__info">
                <p>Laporan Masuk</p>
                <span>Belum diproses</span>
              </div>
              <strong class="admin-pending-item__count">
                {{ stats.pendingReports ?? 2 }}
              </strong>
            </router-link>
            <router-link
              to="/admin/pengguna"
              class="admin-pending-item"
            >
              <div class="admin-pending-item__dot admin-pending-item__dot--blue"></div>
              <div class="admin-pending-item__info">
                <p>Akun Baru</p>
                <span>Terdaftar hari ini</span>
              </div>
              <strong class="admin-pending-item__count">
                {{ stats.newUsersToday ??7 }}
              </strong>
            </router-link>
          </div>
        </div>

        <div class="admin-panel admin-panel--side">
          <div class="admin-panel__header">
            <h3 class="admin-panel__title">Akses Cepat</h3>
          </div>
          <div class="admin-quicklinks">
            <router-link
              to="/admin/verifikasi"
              class="admin-quicklink"
            >
              <span class="admin-quicklink__icon"></span>
              <span>Verifikasi Usaha</span>
            </router-link>
            <router-link
              to="/admin/pengguna"
              class="admin-quicklink"
            >
              <span class="admin-quicklink__icon"></span>
              <span>Manajemen Pengguna</span>
            </router-link>
            <router-link
              to="/admin/laporan"
              class="admin-quicklink"
            >
              <span class="admin-quicklink__icon">️</span>
              <span>Laporan Masuk</span>
            </router-link>
            <router-link
              to="/admin/performa"
              class="admin-quicklink"
            >
              <span class="admin-quicklink__icon"></span>
              <span>Laporan Performa</span>
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import api from '@/api/axios'

export default {
  name: 'AdminDashboard',
  setup() {
    const loadingStats = ref(true)
    const loadingApplications = ref(true)
    const stats = ref({})
    let refreshInterval = null
    const recentApplications = ref([])

    const kategoriMap = {
      fashion: '',
      kuliner: '',
      kerajinan: '',
      digital: '',
      aksesoris: '',
      lainnya: '',
    }
    const kategoriLabel = {
      fashion: 'Fashion',
      kuliner: 'Kuliner',
      kerajinan: 'Kerajinan',
      digital: 'Digital',
      aksesoris: 'Aksesoris',
      lainnya: 'Lainnya',
    }
    const colors = [
      'linear-gradient(135deg,#f43f5e,#e11d48)',
      'linear-gradient(135deg,#6366f1,#4f46e5)',
      'linear-gradient(135deg,#10b981,#059669)',
      'linear-gradient(135deg,#f59e0b,#d97706)',
      'linear-gradient(135deg,#ec4899,#db2777)',
      'linear-gradient(135deg,#0ea5e9,#0284c7)',
    ]

    const getCatIcon = (cat) => kategoriMap[cat] || ''
    const getCatLabel = (cat) => kategoriLabel[cat] || 'Lainnya'
    const getColor = (id) => colors[id % colors.length]

    const formatRupiah = (n) =>
      n
        ? new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
          }).format(n)
        : 'Rp 0'

    const formatDate = (iso) =>
      new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
      }).format(new Date(iso))

    const todayDate = new Intl.DateTimeFormat('id-ID', {
      weekday: 'long',
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    }).format(new Date())

    const computeProductsByStore = (products) => {
      const map = {}
      products.forEach((p) => {
        const store = p.toko || p.seller || 'Unknown'
        if (!map[store]) map[store] = { store, total: 0, count: 0 }
        map[store].total += p.harga || 0
        map[store].count += 1
      })
      return Object.values(map)
    }

    const productsByStore = ref([])

    const fetchStats = async () => {
      try {
        const { data } = await api.get('/admin/stats')
        stats.value = data || {}
      } catch (error) {
        console.error('Failed to fetch admin stats:', error)
        stats.value = {}
      } finally {
        loadingStats.value = false
      }
    }

    const fetchApplications = async () => {
      try {
        const { data } = await api.get('/businesses?status=pending&limit=5')
        recentApplications.value = data.data || data || []
      } catch (error) {
        console.error('Failed to fetch pending businesses:', error)
        recentApplications.value = []
      } finally {
        loadingApplications.value = false
      }
    }

    const fetchStoreCounts = async () => {
      try {
        const { data } = await api.get('/businesses?limit=50')
        const businesses = data.data || data || []
        productsByStore.value = businesses.map((business) => ({
          store: business.name || 'Unknown',
          count: business.products?.length || 0,
          total: business.products?.reduce((sum, item) => sum + (item.price || 0), 0) || 0,
        }))
      } catch (error) {
        console.error('Failed to load store counts:', error)
        productsByStore.value = []
      }
    }

    const quickApprove = async (app) => {
      try {
        await api.post(`/admin/businesses/${app.id}/approve`)
        recentApplications.value = recentApplications.value.filter(
          (a) => a.id !== app.id,
        )
        if (stats.value.pendingApplications) stats.value.pendingApplications--
        if (stats.value.verifiedBusinesses !== undefined)
          stats.value.verifiedBusinesses++
      } catch {
        // ignore
      }
    }

    const quickReject = async (app) => {
      try {
        await api.post(`/admin/businesses/${app.id}/reject`)
        recentApplications.value = recentApplications.value.filter(
          (a) => a.id !== app.id,
        )
        if (stats.value.pendingApplications) stats.value.pendingApplications--
      } catch {
        // ignore
      }
    }

    onMounted(() => {
      fetchStats()
      fetchApplications()
      fetchStoreCounts()
      // Real-time polling setiap 5 detik untuk stats
      refreshInterval = setInterval(() => {
        fetchStats()
      }, 5000)
    })

    onUnmounted(() => {
      if (refreshInterval) {
        clearInterval(refreshInterval)
      }
    })

    return {
      loadingStats,
      loadingApplications,
      stats,
      recentApplications,
      productsByStore,
      getCatIcon,
      getCatLabel,
      getColor,
      formatRupiah,
      formatDate,
      todayDate,
      quickApprove,
      quickReject,
    }
  },
}
</script>

<style scoped>
.admin-store-products { margin-top: 22px; }
.admin-store-products-title { font-size: 1rem; font-weight: 700; color: #111827; margin-bottom: 10px; }
.admin-store-products-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:10px; }
.admin-store-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:12px; }
.admin-store-card h4 { font-size:.95rem; margin-bottom:6px; }
.admin-store-card p { font-size:.79rem; color:#4b5563; margin-bottom:3px; }
.admin-store-cta { font-size:.78rem; color:#6366f1; font-weight:700; text-decoration:none; }
.admin-store-cta:hover { text-decoration:underline; }
</style>

<style scoped>
.admin-greeting__date {
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 7px 14px;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 100px;
  font-size: 0.78rem;
  font-weight: 600;
  color: #6b7280;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}

.stat-skeleton {
  height: 110px;
  border-radius: 16px;
  background: linear-gradient(90deg, #e5e7eb 25%, #d1d5db 50%, #e5e7eb 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

.stat-card {
  background: #fff;
  border-radius: 16px;
  padding: 18px 20px;
  border: 1.5px solid #f3f4f6;
  display: flex;
  flex-direction: column;
  gap: 4px;
  transition: transform 0.2s, box-shadow 0.2s;
  cursor: default;
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  border-radius: 16px 16px 0 0;
}

.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

.stat-card--users::before {
  background: linear-gradient(90deg, #6366f1, #8b5cf6);
}

.stat-card--business::before {
  background: linear-gradient(90deg, #e53e3e, #f56565);
}

.stat-card--orders::before {
  background: linear-gradient(90deg, #f59e0b, #fbbf24);
}

.stat-card--revenue::before {
  background: linear-gradient(90deg, #10b981, #34d399);
}

.stat-card__icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-card--users .stat-card__icon {
  background: rgba(99, 102, 241, 0.1);
  color: #6366f1;
}

.stat-card--business .stat-card__icon {
  background: rgba(229, 62, 62, 0.1);
  color: #e53e3e;
}

.stat-card--orders .stat-card__icon {
  background: rgba(245, 158, 11, 0.1);
  color: #d97706;
}

.stat-card--revenue .stat-card__icon {
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
}

.stat-card__label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #9ca3af;
}

.stat-card__value {
  font-size: 1.7rem;
  font-weight: 900;
  color: #111827;
  line-height: 1.1;
  font-family: 'Fraunces', serif;
}

.stat-card__value--small {
  font-size: 1.15rem;
}

.stat-card__trend {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.7rem;
  font-weight: 600;
  margin-top: 4px;
  color: #10b981;
}

.admin-panels {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 20px;
}

.admin-panel {
  background: #fff;
  border-radius: 16px;
  border: 1.5px solid #f3f4f6;
  overflow: hidden;
}

.admin-panel__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  padding: 18px 22px 0;
  margin-bottom: 16px;
}

.admin-panel__title {
  font-size: 0.95rem;
  font-weight: 800;
  color: #111827;
}

.admin-panel__sub {
  font-size: 0.75rem;
  color: #9ca3af;
  margin-top: 2px;
}

.admin-panel__link {
  font-size: 0.78rem;
  font-weight: 700;
  color: #e53e3e;
  text-decoration: none;
  white-space: nowrap;
  align-self: center;
}

.admin-panel__link:hover {
  text-decoration: underline;
}

.admin-panel__loading {
  padding: 0 22px 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.admin-panel__row-skeleton {
  height: 48px;
  border-radius: 10px;
  background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
}

.admin-panel__empty {
  padding: 40px 22px;
  text-align: center;
  color: #9ca3af;
  font-size: 0.875rem;
}

.admin-panel__empty span {
  font-size: 2.5rem;
  display: block;
  margin-bottom: 8px;
}

.admin-panel__table {
  padding: 0 22px 20px;
}

.admin-table-head {
  display: grid;
  grid-template-columns: 2fr 1.5fr 1fr 1fr 80px;
  gap: 12px;
  padding: 8px 12px;
  border-radius: 8px;
  background: #f9fafb;
  font-size: 0.7rem;
  font-weight: 700;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 6px;
}

.admin-table-row {
  display: grid;
  grid-template-columns: 2fr 1.5fr 1fr 1fr 80px;
  gap: 12px;
  padding: 10px 12px;
  align-items: center;
  border-radius: 10px;
  transition: background 0.15s;
}

.admin-table-row:hover {
  background: #f9fafb;
}

.admin-table-row__biz {
  display: flex;
  align-items: center;
  gap: 9px;
  min-width: 0;
}

.admin-table-row__biz-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: #f3f4f6;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  flex-shrink: 0;
}

.admin-table-row__biz span {
  font-size: 0.85rem;
  font-weight: 600;
  color: #111827;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.admin-table-row__user {
  display: flex;
  align-items: center;
  gap: 7px;
}

.admin-table-row__avatar {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 0.65rem;
  font-weight: 800;
}

.admin-table-row__user span {
  font-size: 0.82rem;
  color: #374151;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.admin-table-row__kat {
  font-size: 0.72rem;
  font-weight: 700;
  color: #e53e3e;
  background: #fff5f5;
  padding: 3px 8px;
  border-radius: 4px;
  white-space: nowrap;
}

.admin-table-row__date {
  font-size: 0.75rem;
  color: #9ca3af;
}

.admin-table-row__actions {
  display: flex;
  gap: 5px;
}

.admin-tbl-btn {
  width: 30px;
  height: 30px;
  border: none;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.admin-tbl-btn--approve {
  background: #dcfce7;
  color: #15803d;
}

.admin-tbl-btn--approve:hover {
  background: #16a34a;
  color: #fff;
}

.admin-tbl-btn--reject {
  background: #fff5f5;
  color: #c53030;
}

.admin-tbl-btn--reject:hover {
  background: #e53e3e;
  color: #fff;
}

.admin-panel-side {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.admin-panel--side {
  padding-bottom: 16px;
}

.admin-pending-list {
  padding: 0 16px 0;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.admin-pending-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 10px;
  text-decoration: none;
  transition: background 0.15s;
}

.admin-pending-item:hover {
  background: #f9fafb;
}

.admin-pending-item__dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}

.admin-pending-item__dot--yellow {
  background: #f59e0b;
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
}

.admin-pending-item__dot--red {
  background: #e53e3e;
  box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.2);
}

.admin-pending-item__dot--blue {
  background: #0ea5e9;
  box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.2);
}

.admin-pending-item__info {
  flex: 1;
  min-width: 0;
}

.admin-pending-item__info p {
  font-size: 0.83rem;
  font-weight: 700;
  color: #111827;
}

.admin-pending-item__info span {
  font-size: 0.72rem;
  color: #9ca3af;
}

.admin-pending-item__count {
  font-size: 0.9rem;
  font-weight: 900;
  color: #e53e3e;
  background: #fff5f5;
  width: 28px;
  height: 28px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.admin-quicklinks {
  padding: 0 16px 0;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.admin-quicklink {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  padding: 12px 8px;
  background: #f9fafb;
  border-radius: 10px;
  border: 1px solid #f3f4f6;
  text-decoration: none;
  font-size: 0.72rem;
  font-weight: 600;
  color: #6b7280;
  text-align: center;
  transition: all 0.2s;
}

.admin-quicklink:hover {
  border-color: #fca5a5;
  background: #fff5f5;
  color: #c53030;
  transform: translateY(-2px);
}

.admin-quicklink__icon {
  display: flex;
  color: #9ca3af;
}

.admin-quicklink:hover .admin-quicklink__icon {
  color: #e53e3e;
}

@media (max-width: 1100px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .admin-panels {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }

  .admin-table-head,
  .admin-table-row {
    grid-template-columns: 1fr 1fr 60px;
  }

  .admin-table-row > :nth-child(3),
  .admin-table-row > :nth-child(4),
  .admin-table-head > :nth-child(3),
  .admin-table-head > :nth-child(4) {
    display: none;
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr 1fr;
    gap: 10px;
  }

  .stat-card {
    padding: 14px 14px;
  }

  .stat-card__value {
    font-size: 1.4rem;
  }
}
</style>