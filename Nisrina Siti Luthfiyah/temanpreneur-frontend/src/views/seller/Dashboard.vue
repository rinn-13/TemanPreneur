<template>
  <div class="ap sdash">
    <PremiumBackground
      v-if="stats.isPremium"
      variant="overlay"
      primary="#f59e0b"
      secondary="#e53e3e"
      accent="#7c3aed"
    />
    <header class="ap__head sdash__head">
      <div>
        <h1 class="ap__title">
          Dashboard
          <span v-if="stats.isPremium" class="sdash__badge">Premium</span>
        </h1>
        <p class="ap__sub">
          {{ stats.isPremium ? 'Selamat datang di dashboard seller premium Anda.' : 'Pantau performa toko Anda di satu tempat.' }}
        </p>
      </div>
    </header>

    <div class="sdash-body">
      <section class="sdash-main">
        <div class="sdash-panel">
          <div class="sdash-panel__header">
            <h2>Ringkasan Bisnis</h2>
            <p>Statistik utama untuk performa toko Anda dalam sekejap.</p>
          </div>

          <div v-if="loading" class="pf__kpi-grid">
            <div v-for="n in 3" :key="n" class="skeleton" style="height:120px;border-radius:18px;"></div>
          </div>

          <div v-else class="pf__kpi-grid">
            <div class="pf__kpi" style="--accent:#e53e3e">
              <p class="pf__kpi-label">Total Produk</p>
              <p class="pf__kpi-val">{{ stats.totalProducts || 0 }}</p>
              <span class="sdash__hint">Produk aktif di toko</span>
            </div>
            <div class="pf__kpi" style="--accent:#6366f1">
              <p class="pf__kpi-label">Pesanan Masuk</p>
              <p class="pf__kpi-val">{{ stats.pendingOrders || 0 }}</p>
              <span class="sdash__hint">Menunggu diproses</span>
            </div>
            <div class="pf__kpi" style="--accent:#10b981">
              <p class="pf__kpi-label">Omzet Bulan Ini</p>
              <p class="pf__kpi-val">{{ formatRupiah(stats.monthlyRevenue) }}</p>
              <span class="sdash__hint">{{ stats.completedOrders || 0 }} pesanan selesai</span>
            </div>
          </div>
        </div>
      </section>

      <aside class="sdash-side">
        <div v-if="!loading && stats.businessName" class="ap__card sdash__info">
          <div class="sdash__info-title">Informasi Toko</div>
          <div class="sdash__info-row"><span>Nama Toko</span><strong>{{ stats.businessName }}</strong></div>
          <div class="sdash__info-row"><span>Status</span><span class="ap__badge" :class="statusBadgeClass">{{ statusLabel }}</span></div>
          <div v-if="stats.isPremium" class="sdash__info-row"><span>Tipe Akun</span><span class="ap__badge ap__badge--gold">Premium</span></div>
        </div>

        <div v-if="error" class="ap__card sdash__error">{{ error }}</div>

        <div class="ap__card sdash__actions">
          <div class="sdash__actions-header">
            <h3>Aksi Cepat</h3>
            <p>Langsung akses fungsi penting seller.</p>
          </div>
          <div class="sdash__quick">
            <router-link to="/seller/produk" class="ap__btn ap__btn--primary">Kelola Produk</router-link>
            <router-link to="/seller/pesanan" class="ap__btn ap__btn--ghost">Pesanan</router-link>
            <router-link to="/seller/analitik" class="ap__btn ap__btn--ghost">Analitik</router-link>
            <router-link to="/seller/saldo" class="ap__btn ap__btn--ghost">Saldo</router-link>
          </div>
        </div>
      </aside>
    </div>
  </div>
</template>

<script>

import { ref, computed, onMounted, onUnmounted } from 'vue'
import api from '@/api/axios'
import PremiumBackground from '@/components/PremiumBackground.vue'

export default {
  components: { PremiumBackground },
  setup() {
    const stats = ref({
      totalProducts: 0,
      pendingOrders: 0,
      completedOrders: 0,
      monthlyRevenue: 0,
      totalRevenue: 0,
      businessName: '',
      businessStatus: 'pending',
      isPremium: false,
    })
    const loading = ref(true)
    const error = ref('')
    let refreshInterval = null

    const formatRupiah = (value) => {
      if (!value) return 'Rp 0'
      return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value)
    }

    const statusLabel = computed(() => {
      const s = stats.value.businessStatus
      if (s === 'approved') return 'Disetujui'
      if (s === 'pending') return 'Menunggu'
      if (s === 'rejected') return 'Ditolak'
      return s
    })

    const statusBadgeClass = computed(() => {
      const s = stats.value.businessStatus
      if (s === 'approved') return 'ap__badge--green'
      if (s === 'pending') return 'ap__badge--yellow'
      return 'ap__badge--red'
    })

    const fetchStats = async () => {
      error.value = ''
      try {
        const response = await api.get('/seller/stats')
        const data = response?.data || {}
        stats.value = {
          totalProducts: Number.isInteger(data?.totalProducts) ? data.totalProducts : 0,
          pendingOrders: Number.isInteger(data?.pendingOrders) ? data.pendingOrders : 0,
          completedOrders: Number.isInteger(data?.completedOrders) ? data.completedOrders : 0,
          monthlyRevenue: Number.isFinite(data?.monthlyRevenue) ? data.monthlyRevenue : 0,
          totalRevenue: Number.isFinite(data?.totalRevenue) ? data.totalRevenue : 0,
          businessName: typeof data?.businessName === 'string' ? data.businessName : 'Usaha Saya',
          businessStatus: data?.businessStatus ? String(data.businessStatus) : 'pending',
          isPremium: Boolean(data?.isPremium),
        }
      } catch (err) {
        error.value = err.response?.data?.message || 'Gagal memuat statistik.'
        stats.value = { totalProducts: 0, pendingOrders: 0, completedOrders: 0, monthlyRevenue: 0, totalRevenue: 0, businessName: '', businessStatus: 'pending', isPremium: false }
      } finally {
        loading.value = false
      }
    }

    onMounted(() => { fetchStats(); refreshInterval = setInterval(fetchStats, 10000) })
    onUnmounted(() => { if (refreshInterval) clearInterval(refreshInterval) })

    return { stats, loading, error, formatRupiah, statusLabel, statusBadgeClass }
  },
}

</script>


<style scoped>
.sdash { position: relative; overflow: hidden; }
.sdash__head { position: relative; z-index: 1; padding-bottom: 4px; border-bottom: 1px solid rgba(15, 23, 42, 0.08); margin-bottom: 24px; }
.sdash__badge { display: inline-block; margin-left: 8px; padding: 4px 10px; border-radius: 999px; font-size: .7rem; font-weight: 800; background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; vertical-align: middle; }
.dash__subtitle { margin-top: 6px; }
.sdash__hint { font-size: .72rem; color: #6b7280; margin-top: 6px; display: block; }
.sdash-body { display: flex; gap: 24px; align-items: flex-start; flex-wrap: wrap; }
.sdash-main { flex: 1 1 640px; min-width: 280px; }
.sdash-side { flex: 0 0 320px; display: grid; gap: 20px; }
.sdash-panel { background: #fff; border-radius: 24px; border: 1px solid #e5e7eb; padding: 24px; box-shadow: 0 16px 40px rgba(15, 23, 42, 0.05); }
.sdash-panel__header { margin-bottom: 20px; }
.sdash-panel__header h2 { margin: 0 0 8px; font-size: 1.1rem; color: #111827; }
.sdash-panel__header p { margin: 0; color: #6b7280; font-size: .94rem; }
.sdash__info { display: grid; gap: 14px; padding: 22px; border-radius: 20px; background: #fff; border: 1px solid #e5e7eb; box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06); }
.sdash__info-title { font-weight: 800; color: #111827; font-size: .98rem; }
.sdash__info-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; font-size: .94rem; color: #334155; }
.sdash__error { color: #b91c1c; background: #fef2f2; border-color: #fecaca; padding: 16px; border-radius: 18px; }
.sdash__actions { padding: 20px; border-radius: 20px; background: #fff; border: 1px solid #e5e7eb; box-shadow: 0 16px 36px rgba(15, 23, 42, 0.05); }
.sdash__actions-header h3 { margin: 0 0 6px; font-size: 1rem; color: #111827; }
.sdash__actions-header p { margin: 0; color: #6b7280; font-size: .88rem; }
.sdash__quick { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-top: 18px; }
.sdash__quick .ap__btn { width: 100%; min-width: auto; }
.ap__badge--gold { background: rgba(245, 158, 11, 0.15); color: #b45309; }

/* KPI grid & cards — copy of admin style to ensure consistency */
.pf__kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}
.pf__kpi {
  background: #fff;
  border-radius: 16px;
  padding: 18px 16px 14px;
  border: 1.5px solid #f3f4f6;
  display: flex;
  flex-direction: column;
  gap: 6px;
  position: relative;
  overflow: hidden;
  transition: transform .18s, box-shadow .18s;
}
.pf__kpi::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  background: var(--accent, #e53e3e);
}
.pf__kpi:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.09); }
.pf__kpi-glow {
  position: absolute;
  top: -30px; right: -30px;
  width: 80px; height: 80px;
  border-radius: 50%;
  background: var(--accent, #e53e3e);
  opacity: 0.07;
  pointer-events: none;
}
.pf__kpi-icon { font-size: 1.4rem; margin-bottom: 4px; }
.pf__kpi-label { font-size: .72rem; font-weight: 600; color: #9ca3af; margin-bottom: 2px; }
.pf__kpi-val { font-size: 1.35rem; font-weight: 900; color: #111827; line-height: 1.1; }
.pf__kpi-trend { font-size: .7rem; font-weight: 600; margin-top: 5px; }
.pf__trend--up { color: #10b981; }
.pf__trend--down { color: #e53e3e; }

/* Responsive tweaks */
@media (max-width: 1100px) {
  .pf__kpi-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
  .pf__kpi-grid { grid-template-columns: 1fr; }
}

</style>

