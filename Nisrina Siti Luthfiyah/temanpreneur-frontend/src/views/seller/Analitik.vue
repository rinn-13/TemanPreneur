<template>
  <section class="sa">
    <header class="sa__head">
      <div>
        <h1 class="sa__title">Analitik <span>Toko</span></h1>
        <p class="sa__sub">Data performa toko berdasarkan penjualan dan produk nyata.</p>
        <p v-if="metaRange.start" class="sa__range">{{ metaRange.periodLabel }} · {{ metaRange.start }} — {{ metaRange.end }}</p>
      </div>
      <div class="sa__toolbar">
        <label class="sa__filter-label">
          <span>Periode</span>
          <select v-model="period" class="sa__period-select">
            <option v-for="o in periodOptions" :key="o.value" :value="o.value">{{ o.label }}</option>
          </select>
        </label>
        <div v-if="period === 'day'" class="sa__field">
          <input type="date" v-model="selectedDate" class="sa__date-input" />
        </div>
        <div v-if="period === 'month'" class="sa__field">
          <input type="month" v-model="selectedMonth" class="sa__month-input" />
        </div>
        <button type="button" class="sa__export-btn" @click="exportReportCsv" :disabled="loading">
          <i class="bi bi-download"></i>
          Export CSV
        </button>
      </div>
    </header>

    <!-- Skeleton -->
    <div v-if="loading" class="sa__kpi-grid">
      <div v-for="n in 4" :key="n" class="sa__kpi skeleton-card"></div>
    </div>

    <div v-else>
      <div v-if="error" class="sa__alert">{{ error }}</div>

      <!-- KPI Cards: 4 kolom rata -->
      <div class="sa__kpi-grid">
        <div class="sa__kpi" style="--accent:#10b981">
          <div class="sa__kpi-glow"></div>
          <svg class="sa__kpi-spark" viewBox="0 0 60 30" preserveAspectRatio="none">
            <polyline points="0,25 10,18 20,22 30,12 40,16 50,8 60,10" fill="none" stroke="#10b981" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" opacity="0.5"/>
          </svg>
          <div class="sa__kpi-icon"></div>
          <p class="sa__kpi-label">Total Penjualan</p>
          <p class="sa__kpi-val">{{ formatRupiah(stats.total_revenue) }}</p>
          <div class="sa__kpi-trend sa__trend--up">↑ data nyata</div>
        </div>

        <div class="sa__kpi" style="--accent:#6366f1">
          <div class="sa__kpi-glow"></div>
          <svg class="sa__kpi-spark" viewBox="0 0 60 30" preserveAspectRatio="none">
            <polyline points="0,20 10,18 20,24 30,14 40,18 50,10 60,8" fill="none" stroke="#6366f1" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" opacity="0.5"/>
          </svg>
          <div class="sa__kpi-icon"></div>
          <p class="sa__kpi-label">Produk Terjual</p>
          <p class="sa__kpi-val">{{ stats.total_sold }}</p>
          <div class="sa__kpi-trend sa__trend--up">↑ total unit</div>
        </div>

        <div class="sa__kpi" style="--accent:#f59e0b">
          <div class="sa__kpi-glow"></div>
          <svg class="sa__kpi-spark" viewBox="0 0 60 30" preserveAspectRatio="none">
            <polyline points="0,22 10,20 20,25 30,15 40,19 50,12 60,9" fill="none" stroke="#f59e0b" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" opacity="0.5"/>
          </svg>
          <div class="sa__kpi-icon"></div>
          <p class="sa__kpi-label">Jumlah Pesanan</p>
          <p class="sa__kpi-val">{{ stats.total_orders }}</p>
          <div class="sa__kpi-trend sa__trend--up">↑ pesanan masuk</div>
        </div>

        <div class="sa__kpi" style="--accent:#e53e3e">
          <div class="sa__kpi-glow"></div>
          <svg class="sa__kpi-spark" viewBox="0 0 60 30" preserveAspectRatio="none">
            <polyline points="0,28 10,22 20,26 30,18 40,20 50,14 60,10" fill="none" stroke="#e53e3e" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" opacity="0.5"/>
          </svg>
          <div class="sa__kpi-icon"></div>
          <p class="sa__kpi-label">Saldo Dompet</p>
          <p class="sa__kpi-val">{{ formatRupiah(stats.wallet_balance) }}</p>
          <div class="sa__kpi-trend sa__trend--up">↑ saldo aktif</div>
        </div>
      </div>

      <!-- ROW 1: Area chart + Donut -->
      <div class="sa__panels">
        <!-- Area/Line Chart -->
        <div class="sa__card sa__chart-card">
          <div class="sa__card-header">
            <div>
              <h3 class="sa__card-title">Pendapatan per Kategori</h3>
              <p class="sa__card-sub">Tren pendapatan berdasarkan kategori produk</p>
            </div>
            <span class="sa__chip">Data Sebenarnya</span>
          </div>

          <div v-if="revenueByCategory.length" class="sa__area-container">
            <svg class="sa__area-svg" :viewBox="`0 0 ${svgW} ${svgH}`" preserveAspectRatio="none">
              <defs>
                <linearGradient id="saAreaGrad" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%"   stop-color="#111827" stop-opacity="0.22"/>
                  <stop offset="70%"  stop-color="#374151" stop-opacity="0.05"/>
                  <stop offset="100%" stop-color="#374151" stop-opacity="0"/>
                </linearGradient>
                <filter id="saGlow">
                  <feGaussianBlur stdDeviation="2.5" result="coloredBlur"/>
                  <feMerge><feMergeNode in="coloredBlur"/><feMergeNode in="SourceGraphic"/></feMerge>
                </filter>
              </defs>
              <line v-for="n in 4" :key="n"
                :x1="padX" :y1="padY + ((n-1)/3)*(svgH-padY-padB)"
                :x2="svgW-padX" :y2="padY + ((n-1)/3)*(svgH-padY-padB)"
                stroke="#f3f4f6" stroke-width="1" stroke-dasharray="4,4"/>
              <path :d="areaPath" fill="url(#saAreaGrad)"/>
              <path :d="linePath" fill="none" stroke="#111827" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round" filter="url(#saGlow)"/>
              <g v-for="(pt, i) in linePoints" :key="i">
                <circle :cx="pt.x" :cy="pt.y" r="5" fill="#fff" stroke="#111827" stroke-width="2.5"/>
                <text :x="pt.x" :y="pt.y-10" text-anchor="middle" font-size="9" font-weight="700" fill="#374151">
                  {{ revenueByCategory[i]?.total > 0 ? shortNum(revenueByCategory[i].total) : '' }}
                </text>
              </g>
            </svg>
            <div class="sa__area-labels">
              <span v-for="item in revenueByCategory" :key="item.category" class="sa__area-lbl">{{ item.category }}</span>
            </div>
          </div>
          <div class="sa__chart-empty" v-else>Belum ada data penjualan tersedia.</div>
        </div>

        <!-- Donut Chart -->
        <div class="sa__card sa__donut-card">
          <div class="sa__card-header">
            <div>
              <h3 class="sa__card-title">Distribusi Kategori</h3>
              <p class="sa__card-sub">Proporsi tiap kategori</p>
            </div>
          </div>
          <div v-if="revenueByCategory.length" class="sa__donut-inner">
            <svg class="sa__donut-svg" viewBox="0 0 180 180">
              <defs>
                <filter id="saDshadow" x="-20%" y="-20%" width="140%" height="140%">
                  <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="rgba(0,0,0,0.10)"/>
                </filter>
              </defs>
              <circle cx="90" cy="90" r="68" fill="none" stroke="#f3f4f6" stroke-width="22"/>
              <circle v-for="(seg, i) in donutSegs" :key="i"
                cx="90" cy="90" r="68" fill="none"
                :stroke="seg.color" stroke-width="22"
                :stroke-dasharray="`${seg.dash} ${seg.gap}`"
                :stroke-dashoffset="seg.offset"
                stroke-linecap="butt" filter="url(#saDshadow)"/>
              <text x="90" y="85" text-anchor="middle" font-size="11" font-weight="600" fill="#9ca3af">Kategori</text>
              <text x="90" y="103" text-anchor="middle" font-size="15" font-weight="900" fill="#111827">{{ revenueByCategory.length }}</text>
            </svg>
            <div class="sa__donut-legend">
              <div v-for="(item, i) in revenueByCategory" :key="item.category" class="sa__legend-item">
                <span class="sa__legend-dot" :style="`background:${donutColors[i % donutColors.length]}`"></span>
                <span class="sa__legend-name">{{ item.category }}</span>
                <span class="sa__legend-val">{{ formatRupiah(item.total) }}</span>
              </div>
            </div>
          </div>
          <div class="sa__chart-empty" v-else>Belum ada data kategori.</div>
        </div>
      </div>

      <!-- ROW 2: Top Products horizontal bar + radial rank -->
      <div class="sa__card sa__products-card">
        <div class="sa__card-header">
          <div>
            <h3 class="sa__card-title">Performa Produk</h3>
            <p class="sa__card-sub">Produk dengan penjualan tertinggi</p>
          </div>
          <span class="sa__chip sa__chip--gold">Top Produk</span>
        </div>

        <div v-if="topProducts.length" class="sa__products-list">
          <div v-for="(product, index) in topProducts" :key="product.id" class="sa__product-row">
            <div class="sa__radial-rank">
              <svg viewBox="0 0 36 36" class="sa__radial-svg">
                <circle cx="18" cy="18" r="15" fill="none" stroke="#f3f4f6" stroke-width="3"/>
                <circle cx="18" cy="18" r="15" fill="none"
                  :stroke="rankColors[index % rankColors.length]"
                  stroke-width="3"
                  :stroke-dasharray="`${(topProducts.length - index) / topProducts.length * 94.2} 94.2`"
                  stroke-dashoffset="23.5" stroke-linecap="round"/>
                <text x="18" y="22" text-anchor="middle" font-size="10" font-weight="900"
                  :fill="rankColors[index % rankColors.length]">{{ index + 1 }}</text>
              </svg>
            </div>
            <div class="sa__product-info">
              <p class="sa__product-name">{{ product.name }}</p>
              <p class="sa__product-meta">Terjual {{ product.sold }} unit</p>
            </div>
            <div class="sa__hbar-wrap">
              <div class="sa__hbar" :style="`width:${getHBarWidth(product.revenue)};background:${rankColors[index % rankColors.length]}`"></div>
            </div>
            <span class="sa__product-rev">{{ formatRupiah(product.revenue) }}</span>
            <span class="sa__product-badge" :style="`background:${rankColors[index % rankColors.length]}18;color:${rankColors[index % rankColors.length]}`">
              Top {{ index + 1 }}
            </span>
          </div>
        </div>
        <div class="sa__chart-empty" v-else>Belum ada produk terjual untuk ditampilkan.</div>
      </div>

    </div>
  </section>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
import sellerDashboardService from '@/services/sellerDashboard'

export default {
  name: 'SellerAnalitik',
  setup() {
    const loading = ref(true)
    const error   = ref('')
    const stats   = ref({ total_revenue: 0, total_sold: 0, total_orders: 0, wallet_balance: 0 })
    const topProducts       = ref([])
    const revenueByCategory = ref([])
    const period = ref('30days')
    const periodOptions = [
      { value: '7days', label: '7 hari terakhir' },
      { value: '30days', label: '30 hari terakhir' },
      { value: 'day', label: 'Per Hari' },
      { value: 'month', label: 'Per Bulan' },
      { value: 'year', label: 'Tahun ini' },
    ]
    const selectedDate = ref(new Date().toISOString().slice(0, 10))
    const selectedMonth = ref(new Date().toISOString().slice(0, 7))
    const metaRange = ref({ start: '', end: '', periodLabel: '' })

    const rankColors  = ['#e53e3e', '#6366f1', '#10b981', '#f59e0b', '#0ea5e9']
    const donutColors = ['#111827', '#374151', '#6b7280', '#9ca3af', '#d1d5db']

    // ── Area chart ────────────────────────────────────────────────────
    const svgW = 560; const svgH = 190
    const padX = 28;  const padY = 22; const padB = 14

    const maxCatVal = computed(() =>
      revenueByCategory.value.length ? Math.max(...revenueByCategory.value.map(i => i.total), 1) : 1
    )

    const linePoints = computed(() => {
      const items = revenueByCategory.value
      if (!items.length) return []
      return items.map((d, i) => ({
        x: padX + (i / Math.max(items.length - 1, 1)) * (svgW - padX * 2),
        y: padY + (1 - d.total / maxCatVal.value) * (svgH - padY - padB),
      }))
    })

    const linePath = computed(() => {
      const pts = linePoints.value
      if (!pts.length) return ''
      return pts.reduce((acc, pt, i) => {
        if (i === 0) return `M${pt.x},${pt.y}`
        const prev = pts[i - 1]; const cpX = (prev.x + pt.x) / 2
        return `${acc} C${cpX},${prev.y} ${cpX},${pt.y} ${pt.x},${pt.y}`
      }, '')
    })

    const areaPath = computed(() => {
      const pts = linePoints.value
      if (!pts.length) return ''
      const bottom = svgH - padB
      const line = pts.reduce((acc, pt, i) => {
        if (i === 0) return `M${pt.x},${pt.y}`
        const prev = pts[i - 1]; const cpX = (prev.x + pt.x) / 2
        return `${acc} C${cpX},${prev.y} ${cpX},${pt.y} ${pt.x},${pt.y}`
      }, '')
      return `${line} L${pts[pts.length-1].x},${bottom} L${pts[0].x},${bottom} Z`
    })

    // ── Donut ─────────────────────────────────────────────────────────
    const circumference = 2 * Math.PI * 68
    const donutSegs = computed(() => {
      const items = revenueByCategory.value
      if (!items.length) return []
      const total = items.reduce((s, i) => s + i.total, 0) || 1
      let offset = circumference * 0.25
      return items.map((item, i) => {
        const dash = (item.total / total) * circumference - 2
        const gap  = circumference - dash
        const seg  = { dash, gap, offset: -offset, color: donutColors[i % donutColors.length] }
        offset += dash + 2; return seg
      })
    })

    // ── Helpers ───────────────────────────────────────────────────────
    const getHBarWidth = (revenue) => {
      const max = Math.max(...topProducts.value.map(p => p.revenue), 1)
      return `${Math.max(6, Math.round((revenue / max) * 100))}%`
    }

    const shortNum = n =>
      n >= 1_000_000 ? `${(n / 1_000_000).toFixed(1)}jt` :
      n >= 1_000 ? `${(n / 1_000).toFixed(0)}rb` : n

    const formatRupiah = (amount) =>
      new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0)

    // kept for backward compat
    const getBarHeight = (value) => {
      const max = Math.max(...revenueByCategory.value.map(i => i.total), 1)
      return `${Math.max(8, Math.round((value / max) * 100))}%`
    }

    // ── Fetch ─────────────────────────────────────────────────────────
    const fetchAnalytics = async () => {
      loading.value = true; error.value = ''
      try {
        let start = null; let end = null
        if (period.value === 'day' && selectedDate.value) {
          start = selectedDate.value; end = selectedDate.value
        } else if (period.value === 'month' && selectedMonth.value) {
          const [y, m] = selectedMonth.value.split('-')
          start = `${y}-${m}-01`
          // compute last day of month
          const lastDay = new Date(y, parseInt(m), 0).getDate()
          end = `${y}-${m}-${String(lastDay).padStart(2, '0')}`
        }

        const result = await sellerDashboardService.getAnalytics(period.value, start, end)
        if (!result.success) throw new Error(result.error || 'Gagal memuat data analitik')
        const data = result.data || {}
        stats.value = {
          total_revenue: data.stats?.total_revenue ?? 0,
          total_sold: data.stats?.total_sold ?? 0,
          total_orders: data.stats?.total_orders ?? 0,
          wallet_balance: data.stats?.wallet_balance ?? 0,
        }
        topProducts.value = data.top_products || []
        revenueByCategory.value = data.revenue_by_category || []
        const label = periodOptions.find(p => p.value === period.value)?.label || period.value
        metaRange.value = {
          start: data.start_date || start || '',
          end: data.end_date || end || '',
          periodLabel: label,
        }
      } catch (err) {
        error.value = err.message || 'Gagal memuat analitik'
      } finally { loading.value = false }
    }

    const exportReportCsv = () => {
      const esc = (v) => `"${String(v ?? '').replace(/"/g, '""')}"`
      const rows = [
        ['Laporan Analitik Toko — TemanPreneur'],
        [`Periode: ${metaRange.value.periodLabel} (${metaRange.value.start} — ${metaRange.value.end})`],
        [],
        ['Ringkasan'],
        ['Total pendapatan (Rp)', stats.value.total_revenue],
        ['Produk terjual (unit)', stats.value.total_sold],
        ['Jumlah pesanan', stats.value.total_orders],
        ['Saldo dompet (Rp)', stats.value.wallet_balance],
        [],
        ['Kategori', 'Pendapatan (Rp)'],
        ...revenueByCategory.value.map((r) => [r.category, r.total]),
        [],
        ['Produk', 'Terjual', 'Pendapatan (Rp)'],
        ...topProducts.value.map((p) => [p.name, p.sold, p.revenue]),
      ]
      const csv = rows.map((r) => (Array.isArray(r) ? r.map(esc).join(',') : r)).join('\n')
      const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' })
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      const stamp = period.value === 'day' ? (selectedDate.value || metaRange.value.start) :
                    period.value === 'month' ? (selectedMonth.value || metaRange.value.start) :
                    (metaRange.value.start || 'export')
      a.download = `analitik-toko-${period.value}-${stamp}.csv`
      a.click()
      URL.revokeObjectURL(url)
    }

    watch(period, fetchAnalytics)
    watch(selectedDate, (v) => { if (period.value === 'day') fetchAnalytics() })
    watch(selectedMonth, (v) => { if (period.value === 'month') fetchAnalytics() })

    onMounted(fetchAnalytics)

    return {
      loading, error, stats, topProducts, revenueByCategory,
      period, periodOptions, metaRange, exportReportCsv,
      fetchAnalytics, getBarHeight, formatRupiah, shortNum,
      svgW, svgH, padX, padY, padB, linePoints, linePath, areaPath,
      donutColors, donutSegs, rankColors, getHBarWidth,
    }
  }
}
</script>

<style scoped>
/* ── Page ───────────────────────────────────────────────────────── */
.sa { padding: 24px 24px 56px; }
.sa__head { margin-bottom: 20px; display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.sa__title { font-size: 1.8rem; font-weight: 900; color: #111827; letter-spacing: -0.02em; }
.sa__title span { color: #e53e3e; }
.sa__sub { color: #6b7280; margin-top: 4px; font-size: .9rem; }
.sa__range { margin: 8px 0 0; font-size: .78rem; font-weight: 600; color: #9ca3af; }

.sa__toolbar {
  display: flex;
  align-items: flex-end;
  gap: 12px;
  flex-wrap: wrap;
}
.sa__filter-label {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: .72rem;
  font-weight: 700;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: .04em;
}
.sa__period-select {
  min-width: 180px;
  height: 40px;
  padding: 0 12px;
  border-radius: 10px;
  border: 1.5px solid #e5e7eb;
  background: #fff;
  font-family: inherit;
  font-size: .88rem;
  font-weight: 600;
  color: #111827;
  cursor: pointer;
}
.sa__date-input, .sa__month-input {
  height: 40px;
  padding: 0 10px;
  border-radius: 10px;
  border: 1.5px solid #e5e7eb;
  background: #fff;
  font-size: .88rem;
}
.sa__export-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  height: 40px;
  padding: 0 16px;
  border-radius: 10px;
  border: none;
  background: #111827;
  color: #fff;
  font-family: inherit;
  font-size: .85rem;
  font-weight: 700;
  cursor: pointer;
  transition: opacity .2s;
}
.sa__export-btn:disabled { opacity: .5; cursor: not-allowed; }
.sa__export-btn:hover:not(:disabled) { opacity: .92; }

/* ── KPI Grid: always 4 columns ─────────────────────────────────── */
.sa__kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 28px;
}
.sa__kpi {
  background: #fff;
  border-radius: 16px;
  padding: 20px 18px 16px;
  border: 1.5px solid #f3f4f6;
  display: flex;
  flex-direction: column;
  gap: 6px;
  position: relative;
  overflow: hidden;
  transition: all 0.2s;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.sa__kpi::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0;
  height: 4px;
  background: var(--accent);
}
.sa__kpi:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,0.1); }
.sa__kpi-glow {
  position: absolute; top: -30px; right: -30px;
  width: 80px; height: 80px; border-radius: 50%;
  background: var(--accent); opacity: 0.07; pointer-events: none;
}
.sa__kpi-spark {
  position: absolute; bottom: 8px; right: 8px;
  width: 60px; height: 30px; opacity: 0.6;
}
.sa__kpi-icon  { font-size: 1.4rem; margin-bottom: 4px; }
.sa__kpi-label { font-size: 0.75rem; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.04em; }
.sa__kpi-val   { font-size: 1.4rem; font-weight: 900; color: #111827; line-height: 1.15; letter-spacing: -0.01em; }
.sa__kpi-trend { font-size: .7rem; font-weight: 600; margin-top: 4px; }
.sa__trend--up   { color: #10b981; }
.sa__trend--down { color: #e53e3e; }

/* ── Cards ───────────────────────────────────────────────────────── */
.sa__card { background: #fff; border: 1.5px solid #e5e7eb; border-radius: 16px; padding: 22px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.sa__card-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 16px; gap: 12px; padding-bottom: 14px; border-bottom: 2.5px solid #e53e3e; }
.sa__card-title  { font-size: 1.05rem; font-weight: 900; color: #111827; margin: 0; letter-spacing: -0.01em; }
.sa__card-sub    { font-size: 0.8rem; color: #9ca3af; margin-top: 3px; font-weight: 500; }
.sa__chip {
  font-size: 0.75rem;
  font-weight: 800;
  color: #fff;
  background: linear-gradient(135deg, #e53e3e, #c53030);
  padding: 4px 12px;
  border-radius: 8px;
  white-space: nowrap;
  flex-shrink: 0;
  letter-spacing: 0.05em;
  box-shadow: 0 2px 6px rgba(229,62,62,0.2);
}
.sa__chip--gold { color: #92400e; background: #fef3c7; border: 1px solid #fde68a; }

/* ── Panel layout ────────────────────────────────────────────────── */
.sa__panels { display: grid; grid-template-columns: 1fr 280px; gap: 16px; margin-bottom: 16px; }

/* ── Area chart ──────────────────────────────────────────────────── */
.sa__area-svg { width: 100%; height: 190px; display: block; overflow: visible; }
.sa__area-labels { display: flex; justify-content: space-between; padding: 6px 28px 0; }
.sa__area-lbl { font-size: .7rem; font-weight: 600; color: #9ca3af; text-align: center; flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* ── Donut ───────────────────────────────────────────────────────── */
.sa__donut-inner { display: flex; flex-direction: column; align-items: center; gap: 14px; }
.sa__donut-svg   { width: 150px; height: 150px; }
.sa__donut-legend { width: 100%; display: flex; flex-direction: column; gap: 8px; }
.sa__legend-item  { display: flex; align-items: center; gap: 8px; }
.sa__legend-dot   { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
.sa__legend-name  { font-size: .78rem; font-weight: 600; color: #374151; flex: 1; }
.sa__legend-val   { font-size: .72rem; font-weight: 700; color: #111827; white-space: nowrap; }

/* ── Products ────────────────────────────────────────────────────── */
.sa__products-card { margin-bottom: 0; }
.sa__products-list { display: flex; flex-direction: column; gap: 10px; }
.sa__product-row {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 14px; border: 1px solid #f3f4f6; border-radius: 12px;
  transition: background .15s, box-shadow .15s;
}
.sa__product-row:hover { background: #f9fafb; box-shadow: 0 2px 8px rgba(0,0,0,.05); }
.sa__radial-rank { width: 36px; height: 36px; flex-shrink: 0; }
.sa__radial-svg  { width: 100%; height: 100%; transform: rotate(-90deg); }
.sa__radial-svg text { transform: rotate(90deg); transform-box: fill-box; transform-origin: center; }
.sa__product-info  { flex-shrink: 0; min-width: 120px; }
.sa__product-name  { font-weight: 800; color: #111827; margin: 0 0 2px; font-size: .88rem; }
.sa__product-meta  { color: #9ca3af; font-size: .75rem; margin: 0; }
.sa__hbar-wrap { flex: 1; height: 6px; background: #f3f4f6; border-radius: 999px; overflow: hidden; }
.sa__hbar { height: 100%; border-radius: 999px; transition: width .5s ease; opacity: .85; }
.sa__product-rev { font-size: .82rem; font-weight: 800; color: #111827; white-space: nowrap; min-width: 110px; text-align: right; }
.sa__product-badge { border-radius: 999px; padding: 4px 10px; font-size: .72rem; font-weight: 700; white-space: nowrap; flex-shrink: 0; }

/* ── Misc ────────────────────────────────────────────────────────── */
.sa__alert { margin-bottom: 16px; padding: 14px 16px; border-radius: 12px; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
.sa__chart-empty { padding: 32px; text-align: center; color: #9ca3af; font-size: .9rem; background: #f9fafb; border-radius: 12px; border: 1px dashed #e5e7eb; }
.skeleton-card { min-height: 120px; border-radius: 16px; background: linear-gradient(90deg,#f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite; }
@keyframes shimmer { 0%{ background-position:200% 0; } 100%{ background-position:-200% 0; } }

/* ── Responsive ──────────────────────────────────────────────────── */
@media (max-width: 1100px) {
  .sa__panels { grid-template-columns: 1fr; }
  .sa__donut-inner { flex-direction: row; align-items: flex-start; }
  .sa__donut-svg { width: 120px; height: 120px; }
}
@media (max-width: 900px) { .sa__kpi-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px)  { .sa__kpi-grid { grid-template-columns: 1fr 1fr; } }
</style>