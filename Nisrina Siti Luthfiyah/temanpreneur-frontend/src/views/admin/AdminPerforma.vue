<template>
  <div class="ap">
    <div class="ap__head">
      <div>
        <h1 class="ap__title">Laporan <span>Performa</span></h1>
        <p class="ap__sub">Analitik dan statistik platform secara menyeluruh</p>
      </div>
      <div class="pf__head-right">
        <select v-model="period" class="ap__select">
          <option value="7">7 Hari Terakhir</option>
          <option value="30">30 Hari Terakhir</option>
          <option value="90">90 Hari Terakhir</option>
          <option value="month">Bulan ini</option>
          <option value="year">Tahun ini</option>
        </select>
        <div class="ap__export-buttons">
          <button class="ap__btn ap__btn--outline" @click="generateReport('pdf')" :disabled="generating">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2"/><polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2"/></svg>
            Generate PDF
          </button>
          <button class="ap__btn ap__btn--outline" @click="generateReport('excel')" :disabled="generating">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2"/><polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2"/><path d="M16 13H8" stroke="currentColor" stroke-width="2"/><path d="M16 17H8" stroke="currentColor" stroke-width="2"/><polyline points="10,9 9,9 8,9" stroke="currentColor" stroke-width="2"/></svg>
            Generate Excel
          </button>
          <button class="ap__btn ap__btn--outline" @click="generateReport('word')" :disabled="generating">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2"/><polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2"/><path d="M16 13H8" stroke="currentColor" stroke-width="2"/><path d="M16 17H8" stroke="currentColor" stroke-width="2"/><path d="M10 9h4" stroke="currentColor" stroke-width="2"/></svg>
            Generate Word
          </button>
        </div>
      </div>
    </div>

    <!-- KPI Cards: selalu 4 kolom -->
    <div class="pf__kpi-grid">
      <div v-for="kpi in kpis" :key="kpi.label" class="pf__kpi" :style="`--accent:${kpi.color}`">
        <div class="pf__kpi-glow"></div>
        <div class="pf__kpi-icon"><i :class="kpi.iconClass"></i></div>
        <div class="pf__kpi-body">
          <p class="pf__kpi-label">{{ kpi.label }}</p>
          <p class="pf__kpi-val">{{ kpi.val }}</p>
          <div class="pf__kpi-trend" :class="kpi.up ? 'pf__trend--up' : 'pf__trend--down'">
            {{ kpi.up ? '↑' : '↓' }} {{ kpi.change }} vs periode lalu
          </div>
        </div>
        <!-- Mini sparkline decoration -->
        <svg class="pf__kpi-spark" viewBox="0 0 60 30" preserveAspectRatio="none">
          <polyline :points="kpi.spark" fill="none" :stroke="kpi.color" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" opacity="0.5"/>
        </svg>
      </div>
    </div>

    <div class="pf__panels">
      <!-- AREA/LINE CHART: Omzet Harian -->
      <div class="ap__card pf__chart-card">
        <div class="ap__card-header">
          <div>
            <h3 class="ap__card-title">Omzet Harian</h3>
            <p class="pf__chart-sub">Tren pendapatan 7 hari terakhir</p>
          </div>
          <div class="pf__chart-badge">
            <span class="pf__chart-total">Total: Rp {{ totalOmzet.toLocaleString('id-ID') }}</span>
          </div>
        </div>

        <div class="pf__area-container" v-if="chartData.length">
          <svg class="pf__area-svg" :viewBox="`0 0 ${svgW} ${svgH}`" preserveAspectRatio="none">
            <defs>
              <linearGradient id="omzetGrad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#e53e3e" stop-opacity="0.28"/>
                <stop offset="75%" stop-color="#e53e3e" stop-opacity="0.04"/>
                <stop offset="100%" stop-color="#e53e3e" stop-opacity="0"/>
              </linearGradient>
              <filter id="glow">
                <feGaussianBlur stdDeviation="2.5" result="coloredBlur"/>
                <feMerge><feMergeNode in="coloredBlur"/><feMergeNode in="SourceGraphic"/></feMerge>
              </filter>
            </defs>
            <!-- Grid lines -->
            <line v-for="n in 4" :key="n"
              :x1="padX" :y1="padY + ((n-1) / 3) * (svgH - padY - padB)"
              :x2="svgW - padX" :y2="padY + ((n-1) / 3) * (svgH - padY - padB)"
              stroke="#f3f4f6" stroke-width="1" stroke-dasharray="4,4"/>
            <!-- Area fill -->
            <path :d="areaPath" fill="url(#omzetGrad)"/>
            <!-- Line with glow -->
            <path :d="linePath" fill="none" stroke="#e53e3e" stroke-width="2.5"
              stroke-linecap="round" stroke-linejoin="round" filter="url(#glow)"/>
            <!-- Dots -->
            <g v-for="(pt, i) in linePoints" :key="i">
              <circle :cx="pt.x" :cy="pt.y" r="5" fill="#fff" stroke="#e53e3e" stroke-width="2.5"/>
              <!-- Value label -->
              <text :x="pt.x" :y="pt.y - 10" text-anchor="middle"
                font-size="9" font-weight="700" fill="#c53030">
                {{ chartData[i].val > 0 ? shortNum(chartData[i].val) : '' }}
              </text>
            </g>
          </svg>

          <!-- X-axis labels -->
          <div class="pf__area-labels">
            <span v-for="d in chartData" :key="d.label" class="pf__area-lbl">{{ d.label }}</span>
          </div>
        </div>
        <div class="pf__chart-empty" v-else>Belum ada data omzet.</div>
      </div>

      <!-- Top sellers -->
      <div class="ap__card pf__side-card">
        <div class="ap__card-header">
          <h3 class="ap__card-title"> Top Seller</h3>
        </div>
        <div class="pf__top-list">
          <div v-for="(s, i) in topSellers" :key="s.id" class="pf__top-item">
            <div class="pf__rank" :class="`pf__rank--${i+1}`">{{ i+1 }}</div>
            <div class="pf__top-avatar" :style="s.avatar ? '' : `background:${color(s.id)}`">
              <img v-if="s.avatar" :src="s.avatar" alt="Seller photo" @error="onImageError($event, '/avatars/default-seller.svg')" />
              <span v-else>{{ s.name?.[0] }}</span>
            </div>
            <div class="pf__top-info">
              <p>{{ s.name }}</p>
              <span>{{ s.products }} produk · {{ s.orders }} pesanan</span>
            </div>
            <p class="pf__top-rev">Rp {{ s.revenue.toLocaleString('id-ID') }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="pf__panels">
      <!-- DONUT CHART: Penjualan per Kategori -->
      <div class="ap__card pf__cat-card">
        <div class="ap__card-header">
          <h3 class="ap__card-title"> Penjualan per Kategori</h3>
        </div>

        <div class="pf__cat-inner" v-if="categories.length">
          <!-- Donut SVG -->
          <div class="pf__donut-area">
            <svg class="pf__donut-svg" viewBox="0 0 180 180">
              <defs>
                <filter id="dshadow" x="-20%" y="-20%" width="140%" height="140%">
                  <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="rgba(0,0,0,0.12)"/>
                </filter>
              </defs>
              <!-- Background ring -->
              <circle cx="90" cy="90" r="68" fill="none" stroke="#f3f4f6" stroke-width="22"/>
              <!-- Segments -->
              <circle
                v-for="(seg, i) in donutSegs"
                :key="i"
                cx="90" cy="90" r="68"
                fill="none"
                :stroke="seg.color"
                stroke-width="22"
                :stroke-dasharray="`${seg.dash} ${seg.gap}`"
                :stroke-dashoffset="seg.offset"
                stroke-linecap="butt"
                filter="url(#dshadow)"
                style="transition: stroke-dasharray .8s ease"
              />
              <!-- Center text -->
              <text x="90" y="85" text-anchor="middle" font-size="11" font-weight="600" fill="#9ca3af">Total</text>
              <text x="90" y="103" text-anchor="middle" font-size="14" font-weight="900" fill="#111827">
                {{ categories.length }} Kat.
              </text>
            </svg>
          </div>

          <!-- Legend + bars -->
          <div class="pf__cat-legend">
            <div v-for="(c, i) in categories" :key="c.id" class="pf__cat-item">
              <span class="pf__cat-swatch" :style="`background:${c.color}`"></span>
              <span class="pf__cat-icon"><i :class="c.icon"></i></span>
              <div class="pf__cat-body">
                <div class="pf__cat-row">
                  <span class="pf__cat-name">{{ c.nama }}</span>
                  <span class="pf__cat-pct">{{ c.pct }}%</span>
                </div>
                <div class="pf__cat-bar-bg">
                  <div class="pf__cat-bar-fill" :style="`width:${c.pct}%;background:${c.color}`"></div>
                </div>
              </div>
              <span class="pf__cat-val">Rp {{ Number(c.rawVal ?? c.val ?? 0).toLocaleString('id-ID') }}</span>
            </div>
          </div>
        </div>
        <div class="pf__chart-empty" v-else>Belum ada data kategori.</div>
      </div>

      <!-- Produk Terpopuler -->
      <div class="ap__card pf__side-card">
        <div class="ap__card-header">
          <h3 class="ap__card-title"> Produk Terpopuler</h3>
          <router-link to="/admin/konten" class="ap__btn ap__btn--ghost" style="padding:5px 12px;font-size:.75rem;">Lihat semua</router-link>
        </div>
        <div class="pf__orders-list">
          <div v-for="(product, index) in popularProducts" :key="product.id" class="pf__order-item">
            <div class="pf__product-thumb">
              <img :src="product.image" alt="Product photo" @error="onImageError($event, '/placeholder-product.png')" />
            </div>
            <!-- Radial rank badge -->
            <div class="pf__radial-rank" :style="`--rcolor:${rankColor(index)}`">
              <svg viewBox="0 0 36 36" class="pf__radial-svg">
                <circle cx="18" cy="18" r="15" fill="none" stroke="#f3f4f6" stroke-width="3"/>
                <circle cx="18" cy="18" r="15" fill="none" :stroke="rankColor(index)" stroke-width="3"
                  :stroke-dasharray="`${(5 - index) / 5 * 94.2} 94.2`"
                  stroke-dashoffset="23.5" stroke-linecap="round"/>
                <text x="18" y="22" text-anchor="middle" font-size="10" font-weight="900" :fill="rankColor(index)">{{ index + 1 }}</text>
              </svg>
            </div>
            <div class="pf__order-info">
              <p>{{ product.name || product.nama }}</p>
              <span>{{ product.business?.name || 'Unknown' }} · {{ product.total_sold || 0 }} terjual</span>
            </div>
            <div class="pf__order-right">
              <p class="pf__order-price">⭐ {{ (product.reviews_avg_rating || 0).toFixed(1) }}</p>
              <span class="ap__badge ap__badge--green">#{{ index + 1 }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch, onMounted } from 'vue'
import api from '@/api/axios'
import { EXCLUDED_CATEGORY_SLUGS, resolveProductImage, resolveAvatar, onImageError } from '@/utils/image'

export default {
  name: 'AdminPerforma',
  setup() {
    const period = ref('30')
    const generating = ref(false)

    const performanceSummary = ref({ total_orders: 0, total_revenue: 0, total_products: 0, total_users: 0, orders_per_day: [] })
    const orders = ref([])
    const products = ref([])
    const users = ref([])
    const kpis = ref([])
    const chartData = ref([])
    const topSellers = ref([])
    const categories = ref([])
    const recentOrders = ref([])
    const popularProducts = ref([])

    const getOrderItems = (order) => Array.isArray(order.items) ? order.items : []
    const getPrimaryProduct = (order) => getOrderItems(order)[0]?.product || order.product || null
    const getPrimaryBusiness = (order) => getPrimaryProduct(order)?.business || null
    const getOrderRevenue = (order) => order.total_amount || order.subtotal || 0

    const buildKpis = (totalRevenue, totalOrders, totalUsersCount, totalProductsCount) => [
      { label: 'Total Omzet',      val: 'Rp ' + totalRevenue.toLocaleString('id-ID'), change: '12%', up: true,  color: '#10b981', iconClass: 'bi bi-currency-dollar', spark: sparks[0] },
      { label: 'Total Pesanan',    val: totalOrders.toLocaleString('id-ID'),          change: '8%',  up: true,  color: '#6366f1', iconClass: 'bi bi-bag-check', spark: sparks[1] },
      { label: 'Pengguna Aktif',   val: totalUsersCount.toLocaleString('id-ID'),       change: '15%', up: true,  color: '#f59e0b', iconClass: 'bi bi-people', spark: sparks[2] },
      { label: 'Produk Terdaftar', val: totalProductsCount.toLocaleString('id-ID'),    change: '5%',  up: true,  color: '#e53e3e', iconClass: 'bi bi-box-seam', spark: sparks[3] },
    ]

    const resolveSellerPhoto = (seller) => {
      if (!seller || typeof seller !== 'object') return null
      const source = seller.logo || seller.photo || seller.avatar || seller.photo_url || seller.avatar_url || seller.image || seller.profile_picture || seller.profile_photo || seller.profile_photo_path
      return resolveAvatar({ ...seller, avatar: source, photo: source }, 'seller')
    }

    const resolveProductPhoto = (product) => resolveProductImage(product, '/placeholder-product.png')

    const computeTopSellers = (ordersList) => {
      const sellerStats = {}
      ordersList.forEach(order => {
        getOrderItems(order).forEach(item => {
          const business = item.product?.business || order.business || getPrimaryBusiness(order)
          const sellerId = business?.id || order.business_id || 'unknown'
          const sellerName = business?.name || (order.business?.name ?? 'Unknown')
          const avatarSource = business?.logo || business?.photo || business?.avatar || business?.photo_url || business?.avatar_url || business?.image
          const amount = item.subtotal || (item.price || 0) * (item.quantity || 1)
          if (!sellerStats[sellerId]) {
            sellerStats[sellerId] = { id: sellerId, name: sellerName, revenue: 0, orders: 0, products: 0, avatarSource }
          }
          sellerStats[sellerId].revenue += amount
          sellerStats[sellerId].orders += 1
          if (!sellerStats[sellerId].avatarSource && avatarSource) {
            sellerStats[sellerId].avatarSource = avatarSource
          }
        })
      })
      products.value.forEach(product => {
        const sid = product.business?.id
        if (sellerStats[sid]) sellerStats[sid].products += 1
      })
      return Object.values(sellerStats)
        .map((seller) => ({ ...seller, avatar: resolveSellerPhoto({ ...seller, logo: seller.avatarSource }) }))
        .sort((a, b) => b.revenue - a.revenue)
        .slice(0, 5)
    }

    const computeCategories = (ordersList) => {
      const categoryStats = {}
      ordersList.forEach(order => {
        getOrderItems(order).forEach(item => {
          const cat = item.product?.category
          const categoryName = cat?.name || item.product?.category || 'Tanpa Kategori'
          const categorySlug = cat?.slug || (cat?.name ? String(cat.name).toLowerCase().replace(/\s+/g, '-') : 'tanpa-kategori')
          const amount = item.subtotal || (item.price || 0) * (item.quantity || 1)
          const key = cat?.id != null ? `id:${cat.id}` : `slug:${categorySlug}`
          if (!categoryStats[key]) {
            categoryStats[key] = { id: categorySlug, nama: categoryName, rawVal: 0, val: 0, color: getCategoryColor(categorySlug), icon: getCategoryIcon(categorySlug) }
          }
          categoryStats[key].rawVal += amount
          categoryStats[key].val = categoryStats[key].rawVal
        })
      })
      const totalCatRev = Object.values(categoryStats).reduce((sum, c) => sum + Number(c.rawVal || c.val || 0), 0)
      return Object.values(categoryStats)
        .filter((c) => {
          const slug = (c.id || '').toString().toLowerCase()
          return slug && !EXCLUDED_CATEGORY_SLUGS.has(slug)
        })
        .map(c => ({ ...c, pct: totalCatRev ? Math.round((Number(c.rawVal || c.val) / totalCatRev) * 100) : 0 }))
        .sort((a, b) => b.val - a.val)
    }

    const computePopularProducts = (ordersList) => {
      const productStats = {}
      ordersList.forEach(order => {
        getOrderItems(order).forEach(item => {
          const pid = item.product?.id || item.product_id || order.product_id
          if (!pid) return
          const product = item.product || products.value.find(p => p.id === pid)
          if (!product) return
          const sold = item.quantity || 1
          const revenue = item.subtotal || (item.price || 0) * sold
          if (!productStats[pid]) {
            productStats[pid] = { ...product, total_sold: 0, total_revenue: 0, image: resolveProductPhoto(product) }
          }
          productStats[pid].total_sold += sold
          productStats[pid].total_revenue += revenue
        })
      })
      return Object.values(productStats).sort((a, b) => b.total_sold - a.total_sold).slice(0, 5)
    }

    const colors_ = [
      'linear-gradient(135deg,#f43f5e,#e11d48)',
      'linear-gradient(135deg,#6366f1,#4f46e5)',
      'linear-gradient(135deg,#10b981,#059669)',
      'linear-gradient(135deg,#f59e0b,#d97706)',
      'linear-gradient(135deg,#0ea5e9,#0284c7)'
    ]
    const color = id => colors_[id % colors_.length]

    const rankColors = ['#e53e3e', '#6366f1', '#10b981', '#f59e0b', '#0ea5e9']
    const rankColor = i => rankColors[i % rankColors.length]

    const shortNum = n =>
      n >= 1_000_000 ? `${(n / 1_000_000).toFixed(1)}jt` :
      n >= 1_000 ? `${(n / 1_000).toFixed(0)}rb` : n

    const getPeriodDateRange = () => {
      const now = new Date()
      const end = new Date(now)
      const start = new Date(now)
      const p = period.value
      if (p === '7') start.setDate(now.getDate() - 6)
      else if (p === '30') start.setDate(now.getDate() - 29)
      else if (p === '90') start.setDate(now.getDate() - 89)
      else if (p === 'month') { start.setDate(1); start.setMonth(now.getMonth()); start.setFullYear(now.getFullYear()) }
      else if (p === 'year') { start.setMonth(0, 1); start.setFullYear(now.getFullYear()) }
      else start.setDate(now.getDate() - 29)
      return {
        start_date: start.toISOString().split('T')[0],
        end_date: end.toISOString().split('T')[0],
      }
    }

    const getCategoryIcon = cat => ({
      fashion: 'bi bi-bag-heart', kuliner: 'bi bi-cup-hot', kerajinan: 'bi bi-palette',
      digital: 'bi bi-laptop', aksesoris: 'bi bi-watch', lainnya: 'bi bi-grid',
      uncategorized: 'bi bi-tag', 'tanpa-kategori': 'bi bi-tag',
    }[cat] || 'bi bi-grid')
    const getCategoryColor = cat => ({ fashion:'#f43f5e', kuliner:'#10b981', kerajinan:'#f59e0b', digital:'#6366f1', aksesoris:'#ec4899', lainnya:'#6b7280' }[cat] || '#6b7280')

    // Fake sparkline points per KPI (decorative)
    const sparks = [
      '0,25 10,20 20,22 30,15 40,18 50,8 60,12',
      '0,20 10,18 20,22 30,14 40,16 50,10 60,8',
      '0,28 10,22 20,25 30,18 40,20 50,14 60,10',
      '0,22 10,19 20,24 30,17 40,15 50,12 60,9',
    ]

    // ── Area chart geometry ────────────────────────────────────────────
    const svgW = 600
    const svgH = 200
    const padX = 28
    const padY = 24
    const padB = 16

    const maxVal = computed(() =>
      chartData.value.length ? Math.max(...chartData.value.map(d => d.val), 1) : 1
    )

    const linePoints = computed(() => {
      const items = chartData.value
      if (!items.length) return []
      return items.map((d, i) => ({
        x: padX + (i / Math.max(items.length - 1, 1)) * (svgW - padX * 2),
        y: padY + (1 - d.val / maxVal.value) * (svgH - padY - padB),
      }))
    })

    const linePath = computed(() => {
      const pts = linePoints.value
      if (!pts.length) return ''
      // Smooth cubic bezier
      return pts.reduce((acc, pt, i) => {
        if (i === 0) return `M${pt.x},${pt.y}`
        const prev = pts[i - 1]
        const cpX = (prev.x + pt.x) / 2
        return `${acc} C${cpX},${prev.y} ${cpX},${pt.y} ${pt.x},${pt.y}`
      }, '')
    })

    const areaPath = computed(() => {
      const pts = linePoints.value
      if (!pts.length) return ''
      const bottom = svgH - padB
      const line = pts.reduce((acc, pt, i) => {
        if (i === 0) return `M${pt.x},${pt.y}`
        const prev = pts[i - 1]
        const cpX = (prev.x + pt.x) / 2
        return `${acc} C${cpX},${prev.y} ${cpX},${pt.y} ${pt.x},${pt.y}`
      }, '')
      const last = pts[pts.length - 1]
      const first = pts[0]
      return `${line} L${last.x},${bottom} L${first.x},${bottom} Z`
    })

    const totalOmzet = computed(() => chartData.value.reduce((a, d) => a + (d.val || 0), 0))

    const barHeight = v => Math.max(5, Math.round((v / maxVal.value) * 100))

    // ── Donut chart ────────────────────────────────────────────────────
    const circumference = 2 * Math.PI * 68

    const donutSegs = computed(() => {
      const items = categories.value
      if (!items.length) return []
      const total = items.reduce((s, i) => s + Number(i.rawVal ?? i.val ?? 0), 0) || 1
      let offset = circumference * 0.25
      return items.map(item => {
        const val = Number(item.rawVal ?? item.val ?? 0)
        const dash = (val / total) * circumference - 2
        const gap = circumference - dash
        const seg = { dash, gap, offset: -offset, color: item.color }
        offset += dash + 2
        return seg
      })
    })

    // ── Fetch ──────────────────────────────────────────────────────────
    const fetchStats = async () => {
      try {
        const ordersRes = await api.get('/orders')
        orders.value = ordersRes.data.data || []

        const productsRes = await api.get('/admin/products')
        products.value = productsRes.data.data || []

        const usersRes = await api.get('/admin/users')
        users.value = usersRes.data.data || []

        const totalRevenue = orders.value.reduce((sum, o) => sum + getOrderRevenue(o), 0)

        // chart data dari orders terbaru jika belum ada performa filter
        const dailyRevenue = {}
        orders.value.forEach(order => {
          const date = new Date(order.created_at).toISOString().split('T')[0]
          dailyRevenue[date] = (dailyRevenue[date] || 0) + getOrderRevenue(order)
        })
        chartData.value = Object.entries(dailyRevenue)
          .sort(([a], [b]) => a.localeCompare(b))
          .slice(-7)
          .map(([date, revenue]) => ({
            label: new Date(date).toLocaleDateString('id-ID', { month: 'short', day: 'numeric' }),
            val: revenue
          }))

        kpis.value = buildKpis(totalRevenue, orders.value.length, users.value.length, products.value.length)
        topSellers.value = computeTopSellers(orders.value)
        categories.value = computeCategories(orders.value)
        popularProducts.value = computePopularProducts(orders.value)
      } catch (e) {
        console.error('Fetch stats error:', e)
        chartData.value = []; kpis.value = []; topSellers.value = []; categories.value = []; popularProducts.value = []
      }
    }

    const fetchPerformance = async () => {
      try {
        const range = getPeriodDateRange()
        const response = await api.get('/admin/performance', { params: range })
        const data = response.data || {}
        performanceSummary.value = {
          total_orders: data.total_orders || 0,
          total_revenue: data.total_revenue || 0,
          total_products: data.total_products || 0,
          total_users: data.total_users || 0,
          orders_per_day: data.orders_per_day || [],
          revenue_by_day: data.revenue_by_day || [],
          sales_by_category: data.sales_by_category || [],
        }

        const revDay = performanceSummary.value.revenue_by_day || []
        if (revDay.length) {
          chartData.value = revDay.map(item => ({
            label: new Date(item.date).toLocaleDateString('id-ID', { month: 'short', day: 'numeric' }),
            val: Number(item.revenue ?? 0),
          }))
        }

        kpis.value = buildKpis(
          performanceSummary.value.total_revenue,
          performanceSummary.value.total_orders,
          users.value.length || performanceSummary.value.total_users,
          products.value.length || performanceSummary.value.total_products
        )

        const sc = (performanceSummary.value.sales_by_category || []).filter((row) => {
          const slug = (row.slug || row.category || '').toString().toLowerCase()
          return slug && !EXCLUDED_CATEGORY_SLUGS.has(slug)
        })
        if (sc.length) {
          const totalRev = sc.reduce((s, row) => s + Number(row.revenue || 0), 0)
          categories.value = sc.map((row) => {
            const slug = row.slug || row.category || 'uncategorized'
            const rev = Number(row.revenue || 0)
            return {
              id: slug,
              nama: row.name,
              rawVal: rev,
              val: rev,
              pct: totalRev ? Math.round((rev / totalRev) * 100) : 0,
              color: getCategoryColor(slug),
              icon: getCategoryIcon(slug),
            }
          })
        }
      } catch (e) {
        console.error('Fetch performance error:', e)
      }
    }

    // ── Report ─────────────────────────────────────────────────────────
    const generateReport = async (format) => {
      if (generating.value) return
      if (format === 'word') { alert('Export Word belum tersedia. Silakan gunakan PDF atau Excel.'); return }
      generating.value = true
      try {
        const now = new Date()
        const startDate = new Date(now)
        startDate.setDate(now.getDate() - (parseInt(period.value, 10) - 1))
        const params = new URLSearchParams({ type: format, start_date: startDate.toISOString().split('T')[0], end_date: now.toISOString().split('T')[0] })
        const response = await api.get(`/admin/performance/export?${params}`, { responseType: 'blob' })
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', `performance-report-${period.value}days.${format === 'excel' ? 'xlsx' : 'pdf'}`)
        document.body.appendChild(link); link.click(); link.remove()
        window.URL.revokeObjectURL(url)
        alert(`Laporan berhasil di-generate: ${format.toUpperCase()}`)
      } catch (e) {
        console.error(e); alert('Gagal generate laporan. Silakan coba lagi.')
      } finally { generating.value = false }
    }

    onMounted(() => {
      fetchStats()
      fetchPerformance()
    })
    watch(period, () => { fetchPerformance() })


    return {
      period, generating, kpis, chartData, totalOmzet, barHeight,
      topSellers, categories, recentOrders, popularProducts,
      color, rankColor, shortNum, getCategoryIcon, generateReport,
      resolveProductPhoto, resolveSellerPhoto, onImageError,
      svgW, svgH, padX, padY, padB, linePoints, linePath, areaPath,
      donutSegs,
    }
  }
}
</script>

<style scoped>
/* ── KPI grid: ALWAYS 4 columns ─────────────────────────────────── */
.pf__kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
  margin-bottom: 20px;
}
.pf__kpi {
  background: #fff;
  border-radius: 16px;
  padding: 18px 16px 14px;
  border: 1.5px solid #f3f4f6;
  display: flex;
  flex-direction: column;
  gap: 4px;
  position: relative;
  overflow: hidden;
  transition: transform .2s, box-shadow .2s;
}
.pf__kpi::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  background: var(--accent);
}
.pf__kpi:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.09); }
.pf__kpi-glow {
  position: absolute;
  top: -30px; right: -30px;
  width: 80px; height: 80px;
  border-radius: 50%;
  background: var(--accent);
  opacity: 0.07;
  pointer-events: none;
}
.pf__kpi-spark {
  position: absolute;
  bottom: 8px; right: 8px;
  width: 60px; height: 30px;
  opacity: 0.6;
}
.pf__kpi-icon { font-size: 1.4rem; margin-bottom: 4px; }
.pf__kpi-label { font-size: .72rem; font-weight: 600; color: #9ca3af; margin-bottom: 2px; }
.pf__kpi-val { font-size: 1.35rem; font-weight: 900; color: #111827; line-height: 1.1; }
.pf__kpi-trend { font-size: .7rem; font-weight: 600; margin-top: 5px; }
.pf__trend--up { color: #10b981; }
.pf__trend--down { color: #e53e3e; }

/* ── Layout ─────────────────────────────────────────────────────── */
.pf__head-right { display: flex; gap: 10px; align-items: center; }
.ap__export-buttons { display: flex; gap: 8px; flex-wrap: wrap; }
.pf__panels { display: grid; grid-template-columns: 1fr 320px; gap: 16px; margin-bottom: 16px; }
.pf__chart-card, .pf__cat-card { flex: 1; }
.pf__side-card { min-width: 0; }

/* ── Area chart ─────────────────────────────────────────────────── */
.pf__area-container { padding: 0 4px 0; }
.pf__area-svg { width: 100%; height: 200px; display: block; overflow: visible; }
.pf__area-labels {
  display: flex;
  justify-content: space-between;
  padding: 6px 28px 0;
}
.pf__area-lbl { font-size: .72rem; font-weight: 600; color: #9ca3af; text-align: center; flex: 1; }
.pf__chart-sub { font-size: .75rem; color: #9ca3af; margin-top: 2px; }
.pf__chart-badge { display: flex; align-items: center; }
.pf__chart-total { font-size: .82rem; font-weight: 700; color: #6b7280; background: #f9fafb; padding: 4px 10px; border-radius: 999px; border: 1px solid #e5e7eb; }

/* ── Donut + category ───────────────────────────────────────────── */
.pf__cat-inner { display: flex; gap: 20px; align-items: flex-start; padding: 0 8px 8px; }
.pf__donut-area { flex-shrink: 0; }
.pf__donut-svg { width: 150px; height: 150px; }
.pf__cat-legend { flex: 1; display: flex; flex-direction: column; gap: 12px; padding-top: 4px; }
.pf__cat-item { display: flex; align-items: center; gap: 8px; }
.pf__cat-swatch { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
.pf__cat-icon { font-size: 1.1rem; flex-shrink: 0; }
.pf__cat-body { flex: 1; }
.pf__cat-row { display: flex; justify-content: space-between; margin-bottom: 4px; }
.pf__cat-name { font-size: .83rem; font-weight: 600; color: #374151; }
.pf__cat-pct  { font-size: .78rem; font-weight: 800; color: #111827; }
.pf__cat-bar-bg { height: 5px; background: #f3f4f6; border-radius: 100px; overflow: hidden; }
.pf__cat-bar-fill { height: 100%; border-radius: 100px; transition: width .8s ease; }
.pf__cat-val { font-size: .72rem; color: #9ca3af; white-space: nowrap; min-width: 58px; text-align: right; }

/* ── Top sellers ────────────────────────────────────────────────── */
.pf__top-list { padding: 0 16px 16px; display: flex; flex-direction: column; gap: 8px; }
.pf__top-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; transition: background .15s; }
.pf__top-item:hover { background: #f9fafb; }
.pf__rank { width: 26px; height: 26px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .78rem; font-weight: 900; flex-shrink: 0; }
.pf__rank--1 { background: linear-gradient(135deg,#fbbf24,#d97706); color: #fff; }
.pf__rank--2 { background: linear-gradient(135deg,#9ca3af,#6b7280); color: #fff; }
.pf__rank--3 { background: linear-gradient(135deg,#d97706,#92400e); color: #fff; }
.pf__rank--4,.pf__rank--5 { background: #f3f4f6; color: #6b7280; }
.pf__top-avatar { width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: #fff; font-size: .8rem; font-weight: 800; overflow: hidden; background: #e2e8f0; }
.pf__top-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pf__top-info { flex: 1; min-width: 0; }
.pf__top-info p { font-size: .84rem; font-weight: 700; color: #111827; }
.pf__top-info span { font-size: .7rem; color: #9ca3af; }
.pf__top-rev { font-size: .78rem; font-weight: 800; color: #10b981; white-space: nowrap; }

/* ── Popular products (radial rank) ─────────────────────────────── */
.pf__orders-list { padding: 0 16px 16px; display: flex; flex-direction: column; gap: 8px; }
.pf__order-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; transition: background .15s; }
.pf__order-item:hover { background: #f9fafb; }
.pf__product-thumb { width: 54px; height: 54px; flex-shrink: 0; border-radius: 12px; overflow: hidden; background: #f3f4f6; display: flex; align-items: center; justify-content: center; }
.pf__product-thumb img { width: 100%; height: 100%; object-fit: cover; }
.pf__radial-rank { width: 36px; height: 36px; flex-shrink: 0; }
.pf__radial-svg { width: 100%; height: 100%; transform: rotate(-90deg); }
.pf__radial-svg text { transform: rotate(90deg) translateX(0); transform-box: fill-box; transform-origin: center; }
.pf__order-info { flex: 1; min-width: 0; }
.pf__order-info p { font-size: .83rem; font-weight: 700; color: #111827; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.pf__order-info span { font-size: .7rem; color: #9ca3af; }
.pf__order-right { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; }
.pf__order-price { font-size: .8rem; font-weight: 800; color: #111827; white-space: nowrap; }

/* ── Empty state ────────────────────────────────────────────────── */
.pf__chart-empty { padding: 32px; text-align: center; color: #9ca3af; font-size: .9rem; background: #f9fafb; border-radius: 12px; border: 1px dashed #e5e7eb; margin: 8px; }

/* ── Responsive ─────────────────────────────────────────────────── */
@media (max-width: 1200px) {
  .pf__kpi-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 1100px) {
  .pf__panels { grid-template-columns: 1fr; }
  .pf__cat-inner { flex-direction: column; align-items: center; }
}
@media (max-width: 640px) {
  .pf__kpi-grid { grid-template-columns: 1fr 1fr; }
}
</style>