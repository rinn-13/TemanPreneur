<template>
  <div class="buyer-page">

    <!-- Back -->
    <div class="buyer-back">
      <button @click="$router.back()" class="back-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Kembali
      </button>
    </div>

    <div class="buyer-body">

      <!-- ── BANNER PROFIL ── -->
      <div class="buyer-banner">
        <div class="buyer-banner__avatar-wrap">
          <div class="buyer-banner__avatar" :style="userPhoto ? '' : `background:${avatarGrad}`">
            <img
              v-if="userPhoto"
              :src="userPhoto"
              alt="Foto Profil"
              class="buyer-banner__avatar-img"
              @error="onImageError($event, '/avatars/default-buyer.svg')"
            />
            <span v-else>{{ userInitial }}</span>
          </div>
        </div>
        <div class="buyer-banner__info">
          <h2 class="buyer-banner__name">{{ user.name || 'Nama Pembeli' }}</h2>
          <p class="buyer-banner__email">{{ user.email || 'email@sekolah.id' }}</p>
          <span class="buyer-banner__badge"> Pembeli</span>
        </div>
        <div class="buyer-banner__stats">
          <div class="bbs__item">
            <strong>{{ stats.totalOrders || 0 }}</strong>
            <span>Pesanan</span>
          </div>
          <div class="bbs__sep"></div>
          <div class="bbs__item">
            <strong>{{ stats.wishlistCount || 0 }}</strong>
            <span>Favorit</span>
          </div>
          <div class="bbs__sep"></div>
          <div class="bbs__item">
            <strong>{{ stats.reviewCount || 0 }}</strong>
            <span>Ulasan</span>
          </div>
        </div>
      </div>

      <!-- ── MENU DASHBOARD PER BUYER (terhubung) ── -->
      <div class="buyer-menu">
        <router-link
          v-for="m in menuItems"
          :key="m.path"
          :to="m.path"
          class="bmenu-item"
        >
          <div class="bmenu-item__circle" :style="`background:${m.color}`">
            <span class="bmenu-item__icon" v-html="m.icon"></span>
          </div>
          <span class="bmenu-item__label">{{ m.label }}</span>
        </router-link>
      </div>

      <!-- ── PANEL BAWAH ── -->
      <div class="buyer-panels">

        <!-- Panel pesanan terbaru -->
        <div class="buyer-panel">
          <div class="buyer-panel__head">
            <h3>Pesanan Terbaru</h3>
            <router-link to="/buyer/orders" class="buyer-panel__link">Lihat semua →</router-link>
          </div>
          <div v-if="loadingOrders" class="buyer-panel__loading">
            <div v-for="n in 3" :key="n" class="bskeleton"></div>
          </div>
          <div class="buyer-panel__empty" v-else-if="!recentOrders.length">
            <span></span><p>Belum ada pesanan</p>
          </div>
          <div v-else class="buyer-orders-list">
            <div v-for="o in recentOrders" :key="o.id" class="border-order">
              <div class="border-order__img" :style="`background:${o.gradient||'#e5e7eb'}`">
                <span>{{ o.emoji || '' }}</span>
              </div>
              <div class="border-order__info">
                <p class="border-order__name">{{ o.product_name || 'Produk' }}</p>
                <p class="border-order__price">Rp {{ (o.total_price||0).toLocaleString('id-ID') }}</p>
              </div>
              <span class="border-order__status" :class="`bos--${o.status}`">{{ statusLabel(o.status) }}</span>
            </div>
          </div>
        </div>

        <!-- Panel aktivitas -->
        <div class="buyer-panel">
          <div class="buyer-panel__head">
            <h3>Aktivitas Terakhir</h3>
          </div>
          <div class="buyer-activity-list">
            <div v-for="a in activities" :key="a.id" class="bact-item">
              <div class="bact-item__dot" :style="`background:${a.color}`"></div>
              <div class="bact-item__body">
                <p>{{ a.text }}</p>
                <span>{{ a.time }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Panel laporan pengaduan -->
        <div class="buyer-panel" v-if="stats.openReports > 0">
          <div class="buyer-panel__head">
            <h3>Laporan Pengaduan</h3>
            <router-link to="/buyer/laporan" class="buyer-panel__link">Lihat semua →</router-link>
          </div>
          <div v-if="loadingReports" class="buyer-panel__loading">
            <div v-for="n in 2" :key="n" class="bskeleton"></div>
          </div>
          <div class="buyer-panel__empty" v-else-if="!openReports.length">
            <span></span><p>Tidak ada laporan terbuka</p>
          </div>
          <div v-else class="buyer-reports-list">
            <div v-for="r in openReports" :key="r.id" class="border-report" @click="$router.push('/buyer/laporan')" style="cursor:pointer;">
              <div class="border-report__badge" :style="`background:${reportStatusColor(r.status)}`">
                {{ reportStatusLabel(r.status) }}
              </div>
              <div class="border-report__info">
                <p class="border-report__title">{{ r.subject || 'Laporan Masalah' }}</p>
                <p class="border-report__desc">{{ r.description?.substring(0, 60) }}...</p>
              </div>
              <span class="border-report__id">#{{ String(r.id).padStart(4, '0') }}</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import api from '@/api/axios'
import { normalizeImageUrl, onImageError } from '@/utils/image'

export default {
  name: 'BuyerDashboard',
  setup() {
    const user = ref(JSON.parse(localStorage.getItem('user') || '{}'))
    const stats = ref({ totalOrders:0, wishlistCount:0, reviewCount:0, openReports:0 })
    const recentOrders = ref([])
    const openReports = ref([])
    const loadingOrders = ref(true)
    const loadingReports = ref(true)

    const avatarGrad = computed(() => 'linear-gradient(135deg,#10b981,#059669)')
    const userInitial = computed(() => (user.value.name || 'P').charAt(0).toUpperCase())
    const userPhoto = computed(() => normalizeImageUrl(
      user.value.photo ||
      user.value.avatar ||
      user.value.photo_url ||
      user.value.profile_photo ||
      user.value.profile_photo_path ||
      user.value.profile_picture ||
      user.value.profileImage ||
      user.value.image ||
      null,
      null
    ))

    const statusLabel = s => ({ pending:'Menunggu', diproses:'Diproses', dikemas:'Dikemas', diantarkan:'Dikirim', selesai:'Selesai', dibatalkan:'Dibatalkan' })[s] || s

    const menuItems = [
      { label:'Pesanan',   path:'/buyer/orders',   color:'linear-gradient(135deg,#6366f1,#4f46e5)', icon:'<svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M16 4h2a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2" stroke="white" stroke-width="2"/><rect x="8" y="2" width="8" height="4" rx="1" stroke="white" stroke-width="2"/></svg>' },
      { label:'Favorit',   path:'/buyer/favorit',  color:'linear-gradient(135deg,#e53e3e,#c53030)', icon:'<svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z" stroke="white" stroke-width="2" fill="rgba(255,255,255,.25)"/></svg>' },
      { label:'Ulasan',    path:'/buyer/ulasan',   color:'linear-gradient(135deg,#f59e0b,#d97706)', icon:'<svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke="white" stroke-width="2" fill="rgba(255,255,255,.25)"/></svg>' },
      { label:'Profil',    path:'/buyer/profil',   color:'linear-gradient(135deg,#0ea5e9,#0284c7)', icon:'<svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke="white" stroke-width="2"/><circle cx="12" cy="7" r="4" stroke="white" stroke-width="2"/></svg>' },
      { label:'Laporan',   path:'/buyer/laporan',  color:'linear-gradient(135deg,#ec4899,#db2777)', icon:'<svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="white" stroke-width="2"/><line x1="12" y1="9" x2="12" y2="13" stroke="white" stroke-width="2" stroke-linecap="round"/><line x1="12" y1="17" x2="12.01" y2="17" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>' },
    ]

    const fetchOrders = async () => {
      try {
        const r = await api.get('/orders')
        
        // Handle different response formats
        let ordersData = []
        
        if (r.data?.success && r.data?.data) {
          // Backend response format: { success: true, data: [...], meta: {...} }
          ordersData = Array.isArray(r.data.data) ? r.data.data : []
        } else if (r.data?.data && Array.isArray(r.data.data)) {
          // Alternative format
          ordersData = r.data.data
        } else if (Array.isArray(r.data)) {
          // Direct array
          ordersData = r.data
        } else {
          console.warn('Unexpected orders response format in dashboard:', r.data)
          ordersData = []
        }

        console.log('Dashboard fetched orders:', ordersData.length, 'orders')
        recentOrders.value = ordersData.slice(0, 3)
        stats.value.totalOrders = ordersData.length
      } catch (error) {
        console.error('Failed to fetch orders in dashboard:', error)
        recentOrders.value = []
        stats.value.totalOrders = 0
      }
    }

    const fetchWishlistCount = async () => {
      try {
        const r = await api.get('/wishlist')
        const list = r.data.data || r.data || []
        stats.value.wishlistCount = r.data.count ?? list.length
      } catch (error) {
        console.error('Failed to fetch wishlist count:', error)
        stats.value.wishlistCount = 0
      }
    }

    const fetchReviewCount = async () => {
      try {
        const r = await api.get('/reviews/my')
        const list = r.data.data || r.data || []
        stats.value.reviewCount = list.length
      } catch (error) {
        console.error('Failed to fetch review count:', error)
        stats.value.reviewCount = 0
      }
    }

    const fetchReports = async () => {
      try {
        loadingReports.value = true
        const r = await api.get('/reports')
        const reports = r.data.data || r.data || []
        // Filter only open/in_progress reports
        openReports.value = reports.filter(rep => rep.status === 'open' || rep.status === 'in_progress').slice(0, 3)
        stats.value.openReports = reports.filter(rep => rep.status === 'open' || rep.status === 'in_progress').length
      } catch (error) {
        console.error('Failed to fetch reports:', error)
        openReports.value = []
        stats.value.openReports = 0
      } finally {
        loadingReports.value = false
      }
    }

    const reportStatusLabel = s => ({ open:'Menunggu', in_progress:'Sedang Diproses', closed:'Selesai' })[s] || s
    const reportStatusColor = s => ({ open:'#fecaca', in_progress:'#fef3c7', closed:'#dcfce7' })[s] || '#e5e7eb'

    const activities = computed(() => {
      return recentOrders.value.map((order, idx) => ({
        id: order.id,
        text: `Pesanan ${order.product?.name || order.product_name || 'produk'} ${statusLabel(order.status).toLowerCase()}`,
        time: order.updated_at ? new Intl.DateTimeFormat('id-ID',{day:'numeric',month:'short'}).format(new Date(order.updated_at)) : 'Baru saja',
        color: ['#6366f1','#f59e0b','#10b981','#e53e3e'][idx % 4],
      }))
    })

    onMounted(async () => {
      await Promise.all([fetchOrders(), fetchWishlistCount(), fetchReviewCount(), fetchReports()])
      loadingOrders.value = false
    })

    return { user, stats, recentOrders, openReports, loadingOrders, loadingReports, avatarGrad, userInitial, userPhoto, menuItems, activities, statusLabel, reportStatusLabel, reportStatusColor, onImageError }
  }
}
</script>

<style scoped>
.buyer-page { min-height:100vh; background:#f4f5f7; font-family:'Plus Jakarta Sans',sans-serif; }

/* Back */
.buyer-back { max-width:1100px; margin:0 auto; padding:20px 28px 0; }
.back-btn { display:flex; align-items:center; gap:7px; background:none; border:none; font-family:'Plus Jakarta Sans',sans-serif; font-size:.95rem; font-weight:700; color:#111827; cursor:pointer; text-decoration:underline; text-underline-offset:3px; }
.back-btn:hover { color:#e53e3e; }

.buyer-body { max-width:1100px; margin:0 auto; padding:24px 28px 64px; display:flex; flex-direction:column; gap:28px; }

/* Banner */
.buyer-banner {
  background:linear-gradient(135deg,#d0d5dd,#b0b8c4);
  border-radius:20px;
  border:1px solid #9ca3af;
  padding:28px 32px;
  display:flex;
  align-items:center;
  gap:24px;
  flex-wrap:wrap;
}
.buyer-banner__avatar-wrap { flex-shrink:0; }
.buyer-banner__avatar {
  width:120px; height:120px; border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  font-size:3rem; font-weight:900; color:#fff;
  box-shadow:0 4px 20px rgba(0,0,0,.18);
  border: 4px solid rgba(255,255,255,.9);
  background: #fff;
  flex-shrink:0;
  overflow:hidden;
}
.buyer-banner__avatar-img {
  width:100%;
  height:100%;
  object-fit:cover;
  display:block;
}
.buyer-banner__info { flex:1; }
.buyer-banner__name { font-family:'Fraunces',serif; font-size:1.5rem; font-weight:900; color:#111827; margin-bottom:4px; }
.buyer-banner__email { font-size:.85rem; color:#6b7280; margin-bottom:8px; }
.buyer-banner__badge { display:inline-flex; align-items:center; gap:5px; padding:4px 12px; background:rgba(16,185,129,.15); border:1px solid rgba(16,185,129,.3); border-radius:100px; font-size:.75rem; font-weight:700; color:#059669; }
.buyer-banner__stats { display:flex; align-items:center; gap:0; background:rgba(255,255,255,.4); border:1px solid rgba(255,255,255,.6); border-radius:14px; overflow:hidden; flex-shrink:0; }
.bbs__item { padding:16px 24px; text-align:center; }
.bbs__item strong { display:block; font-family:'Fraunces',serif; font-size:1.4rem; font-weight:900; color:#111827; }
.bbs__item span { font-size:.7rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.06em; }
.bbs__sep { width:1px; height:40px; background:rgba(0,0,0,.1); }

/* Menu bulat */
.buyer-menu {
  display:flex;
  justify-content:center;
  gap:48px;
  padding:12px 0;
  flex-wrap:wrap;
}
.bmenu-item { display:flex; flex-direction:column; align-items:center; gap:10px; text-decoration:none; cursor:pointer; }
.bmenu-item__circle {
  width:90px; height:90px; border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  box-shadow:0 4px 16px rgba(0,0,0,.15);
  transition:transform .2s, box-shadow .2s;
}
.bmenu-item:hover .bmenu-item__circle { transform:translateY(-4px) scale(1.05); box-shadow:0 8px 24px rgba(0,0,0,.22); }
.bmenu-item__icon { display:flex; }
.bmenu-item__label { font-size:.8rem; font-weight:700; color:#374151; text-align:center; }

/* Panels */
.buyer-panels { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
.buyer-panel { background:#d0d5dd; border-radius:20px; border:1px solid #9ca3af; padding:0; overflow:hidden; }
.buyer-panel__head { display:flex; align-items:center; justify-content:space-between; padding:18px 22px 14px; }
.buyer-panel__head h3 { font-size:.95rem; font-weight:800; color:#111827; }
.buyer-panel__link { font-size:.78rem; font-weight:600; color:#6366f1; text-decoration:none; }
.buyer-panel__link:hover { text-decoration:underline; }
.buyer-panel__loading { padding:0 22px 16px; display:flex; flex-direction:column; gap:8px; }
.bskeleton { height:52px; border-radius:10px; background:linear-gradient(90deg,rgba(255,255,255,.3) 25%,rgba(255,255,255,.5) 50%,rgba(255,255,255,.3) 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
.buyer-panel__empty { text-align:center; padding:36px 24px; color:#6b7280; }
.buyer-panel__empty span { font-size:2rem; display:block; margin-bottom:8px; }

/* Orders list */
.buyer-orders-list { padding:0 22px 18px; display:flex; flex-direction:column; gap:8px; }
.border-order { display:flex; align-items:center; gap:10px; background:rgba(255,255,255,.5); border-radius:12px; padding:10px 13px; }
.border-order__img { width:40px; height:40px; border-radius:9px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:1.2rem; }
.border-order__info { flex:1; min-width:0; }
.border-order__name { font-size:.84rem; font-weight:700; color:#111827; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.border-order__price { font-size:.76rem; color:#6b7280; }
.border-order__status { padding:3px 9px; border-radius:100px; font-size:.65rem; font-weight:700; white-space:nowrap; }
.bos--selesai    { background:#dcfce7; color:#15803d; }
.bos--diantarkan { background:#dbeafe; color:#1d4ed8; }
.bos--diproses   { background:#fef3c7; color:#d97706; }
.bos--dikemas    { background:#e0e7ff; color:#4338ca; }
.bos--pending    { background:#f3f4f6; color:#6b7280; }
.bos--dibatalkan { background:#fff5f5; color:#c53030; }

/* Activity */
.buyer-activity-list { padding:0 22px 18px; display:flex; flex-direction:column; gap:10px; }
.bact-item { display:flex; align-items:flex-start; gap:10px; }
.bact-item__dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; margin-top:5px; }
.bact-item__body p { font-size:.83rem; color:#111827; font-weight:500; line-height:1.4; }
.bact-item__body span { font-size:.7rem; color:#9ca3af; }

/* Reports list */
.buyer-reports-list { padding:0 22px 18px; display:flex; flex-direction:column; gap:8px; }
.border-report { display:flex; align-items:center; gap:10px; background:rgba(255,255,255,.5); border-radius:12px; padding:10px 13px; transition:background .2s; }
.border-report:hover { background:rgba(255,255,255,.7); }
.border-report__badge { padding:3px 9px; border-radius:100px; font-size:.65rem; font-weight:700; white-space:nowrap; flex-shrink:0; }
.border-report__info { flex:1; min-width:0; }
.border-report__title { font-size:.84rem; font-weight:700; color:#111827; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.border-report__desc { font-size:.76rem; color:#6b7280; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.border-report__id { font-size:.7rem; color:#9ca3af; flex-shrink:0; }

@media (max-width:768px) {
  .buyer-panels { grid-template-columns:1fr; }
  .buyer-menu { gap:24px; }
  .bmenu-item__circle { width:72px; height:72px; }
  .buyer-banner { flex-direction:column; text-align:center; }
  .buyer-banner__stats { width:100%; justify-content:center; }
}
</style>