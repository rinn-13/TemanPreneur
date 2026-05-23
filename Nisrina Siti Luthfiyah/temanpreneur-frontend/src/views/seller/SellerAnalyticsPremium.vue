<template>
  <section class="analytics-premium">
    <header class="analytics__head">
      <h1 class="analytics__title">Advanced Analytics</h1>
      <p class="analytics__sub">Dapatkan insights mendalam tentang performa bisnis Anda dengan analytics premium.</p>
    </header>

    <div class="analytics__filters">
      <div class="filter-group">
        <label>Periode:</label>
        <select v-model="selectedPeriod" class="select-input">
          <option value="7">7 Hari Terakhir</option>
          <option value="30">30 Hari Terakhir</option>
          <option value="90">90 Hari Terakhir</option>
          <option value="365">1 Tahun Terakhir</option>
        </select>
      </div>
    </div>

    <div v-if="loading" class="analytics__loading">
      <div class="skeleton" style="height: 100px;"></div>
      <div class="skeleton" style="height: 300px;"></div>
    </div>

    <div v-else class="analytics__content">
      <!-- KPI Cards -->
      <div class="kpi-grid">
        <div class="kpi-card">
          <p class="kpi-label">️ Total Pengunjung</p>
          <p class="kpi-value">{{ analytics.totalVisitors?.toLocaleString('id-ID') || 0 }}</p>
          <p class="kpi-trend">
            <span :class="analytics.visitorsTrend >= 0 ? 'trend-up' : 'trend-down'">
              {{ analytics.visitorsTrend >= 0 ? '↑' : '↓' }} {{ Math.abs(analytics.visitorsTrend) }}%
            </span>
            dibanding periode sebelumnya
          </p>
        </div>

        <div class="kpi-card">
          <p class="kpi-label"> Total Transaksi</p>
          <p class="kpi-value">{{ analytics.totalOrders || 0 }}</p>
          <p class="kpi-trend">
            <span :class="analytics.ordersTrend >= 0 ? 'trend-up' : 'trend-down'">
              {{ analytics.ordersTrend >= 0 ? '↑' : '↓' }} {{ Math.abs(analytics.ordersTrend) }}%
            </span>
          </p>
        </div>

        <div class="kpi-card">
          <p class="kpi-label"> Total Revenue</p>
          <p class="kpi-value">{{ formatRupiah(analytics.totalRevenue) }}</p>
          <p class="kpi-trend">
            <span :class="analytics.revenueTrend >= 0 ? 'trend-up' : 'trend-down'">
              {{ analytics.revenueTrend >= 0 ? '↑' : '↓' }} {{ Math.abs(analytics.revenueTrend) }}%
            </span>
          </p>
        </div>

        <div class="kpi-card">
          <p class="kpi-label"> Conversion Rate</p>
          <p class="kpi-value">{{ analytics.conversionRate || 0 }}%</p>
          <p class="kpi-trend">
            <span :class="analytics.conversionTrend >= 0 ? 'trend-up' : 'trend-down'">
              {{ analytics.conversionTrend >= 0 ? '↑' : '↓' }} {{ Math.abs(analytics.conversionTrend) }}%
            </span>
          </p>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="analytics__charts">
        <div class="chart-card">
          <h2> Tren Pengunjung & Penjualan</h2>
          <div class="chart-placeholder">
            <p>Grafik interaktif akan ditampilkan di sini</p>
            <small>Performa daily visitor dan order trend</small>
          </div>
        </div>

        <div class="chart-card">
          <h2> Traffic Source</h2>
          <div class="traffic-sources">
            <div class="traffic-item" v-for="source in analytics.trafficSources" :key="source.name">
              <span class="traffic-icon">{{ source.icon }}</span>
              <div class="traffic-info">
                <p class="traffic-name">{{ source.name }}</p>
                <p class="traffic-percentage">{{ source.percentage }}%</p>
              </div>
              <div class="traffic-bar">
                <div class="traffic-fill" :style="{ width: source.percentage + '%' }"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Product Performance -->
      <div class="analytics__products">
        <h2> Produk Terbaik</h2>
        <table class="analytics__table">
          <thead>
            <tr>
              <th>Produk</th>
              <th>Kunjungan</th>
              <th>Penjualan</th>
              <th>Revenue</th>
              <th>Rating</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="product in analytics.topProducts" :key="product.id">
              <td class="product-name">{{ product.name }}</td>
              <td>{{ product.views?.toLocaleString('id-ID') || 0 }}</td>
              <td>{{ product.sold || 0 }}</td>
              <td>{{ formatRupiah(product.revenue) }}</td>
              <td>
                <span class="rating">⭐ {{ product.rating || 0 }}/5</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Customer Insights -->
      <div class="analytics__insights">
        <h2> Customer Insights</h2>
        <div class="insights-grid">
          <div class="insight-card">
            <p class="insight-label">New Customers</p>
            <p class="insight-value">{{ analytics.newCustomers || 0 }}</p>
          </div>
          <div class="insight-card">
            <p class="insight-label">Returning Customers</p>
            <p class="insight-value">{{ analytics.returningCustomers || 0 }}</p>
          </div>
          <div class="insight-card">
            <p class="insight-label">Avg Order Value</p>
            <p class="insight-value">{{ formatRupiah(analytics.avgOrderValue) }}</p>
          </div>
          <div class="insight-card">
            <p class="insight-label">Customer Lifetime Value</p>
            <p class="insight-value">{{ formatRupiah(analytics.customerLifetimeValue) }}</p>
          </div>
        </div>
      </div>

      <!-- Review & Ratings -->
      <div class="analytics__reviews">
        <h2>⭐ Review & Rating</h2>
        <div class="review-stats">
          <div class="review-card">
            <p class="review-label">Rating Rata-rata</p>
            <p class="review-value">{{ analytics.avgRating || 0 }}/5</p>
          </div>
          <div class="review-card">
            <p class="review-label">Total Review</p>
            <p class="review-value">{{ analytics.totalReviews || 0 }}</p>
          </div>
          <div class="review-card">
            <p class="review-label">5⭐ Percentage</p>
            <p class="review-value">{{ analytics.fiveStarPercentage || 0 }}%</p>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="analytics__actions">
        <button class="btn btn-primary"> Export Report</button>
        <button class="btn btn-secondary"> Share Analytics</button>
        <button class="btn btn-secondary"> Get Recommendations</button>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api/axios'

const loading = ref(true)
const selectedPeriod = ref('30')
const analytics = ref({
  totalVisitors: 0,
  visitorsTrend: 0,
  totalOrders: 0,
  ordersTrend: 0,
  totalRevenue: 0,
  revenueTrend: 0,
  conversionRate: 0,
  conversionTrend: 0,
  trafficSources: [
    { name: 'Direct', icon: '', percentage: 35 },
    { name: 'Search', icon: '', percentage: 30 },
    { name: 'Social Media', icon: '', percentage: 20 },
    { name: 'Referral', icon: '', percentage: 15 },
  ],
  topProducts: [],
  newCustomers: 0,
  returningCustomers: 0,
  avgOrderValue: 0,
  customerLifetimeValue: 0,
  avgRating: 0,
  totalReviews: 0,
  fiveStarPercentage: 0,
})

const formatRupiah = (value) => {
  if (!value) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(value)
}

const fetchAnalytics = async () => {
  loading.value = true
  try {
    const response = await api.get(`/seller/analytics-premium?period=${selectedPeriod.value}`)
    const data = response?.data || {}

    analytics.value = {
      totalVisitors: data.totalVisitors || 0,
      visitorsTrend: data.visitorsTrend || 0,
      totalOrders: data.totalOrders || 0,
      ordersTrend: data.ordersTrend || 0,
      totalRevenue: data.totalRevenue || 0,
      revenueTrend: data.revenueTrend || 0,
      conversionRate: data.conversionRate || 0,
      conversionTrend: data.conversionTrend || 0,
      trafficSources: data.trafficSources || analytics.value.trafficSources,
      topProducts: data.topProducts || [],
      newCustomers: data.newCustomers || 0,
      returningCustomers: data.returningCustomers || 0,
      avgOrderValue: data.avgOrderValue || 0,
      customerLifetimeValue: data.customerLifetimeValue || 0,
      avgRating: data.avgRating || 0,
      totalReviews: data.totalReviews || 0,
      fiveStarPercentage: data.fiveStarPercentage || 0,
    }
  } catch (error) {
    console.error('Failed to fetch analytics:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchAnalytics()
})
</script>

<style scoped>
.analytics-premium {
  padding: 24px;
}

.analytics__head {
  margin-bottom: 24px;
}

.analytics__title {
  font-size: 1.875rem;
  font-weight: 900;
  color: #111827;
  margin: 0 0 8px;
}

.analytics__sub {
  color: #6b7280;
  margin: 0;
  font-size: 0.95rem;
}

.analytics__filters {
  display: flex;
  gap: 16px;
  margin-bottom: 24px;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 8px;
}

.filter-group label {
  font-weight: 700;
  color: #374151;
}

.select-input {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  background: #fff;
  cursor: pointer;
}

.analytics__loading {
  display: grid;
  gap: 16px;
}

.analytics__content {
  display: grid;
  gap: 24px;
}

.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
}

.kpi-card {
  background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
  border: 1px solid #d1d5db;
  border-radius: 12px;
  padding: 20px;
  transition: all 0.3s ease;
}

.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.kpi-label {
  font-size: 0.85rem;
  font-weight: 700;
  color: #6b7280;
  text-transform: uppercase;
  margin-bottom: 8px;
}

.kpi-value {
  font-size: 2rem;
  font-weight: 900;
  color: #111827;
  margin-bottom: 8px;
}

.kpi-trend {
  color: #6b7280;
  font-size: 0.9rem;
  margin: 0;
}

.trend-up {
  color: #22c55e;
  font-weight: 700;
}

.trend-down {
  color: #ef4444;
  font-weight: 700;
}

.analytics__charts {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 16px;
}

.chart-card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
}

.chart-card h2 {
  margin-top: 0;
  font-size: 1.1rem;
}

.chart-placeholder {
  background: #f9fafb;
  border-radius: 8px;
  padding: 40px;
  text-align: center;
  color: #9ca3af;
}

.traffic-sources {
  display: grid;
  gap: 12px;
}

.traffic-item {
  display: grid;
  grid-template-columns: 40px 1fr 100px;
  align-items: center;
  gap: 12px;
}

.traffic-icon {
  font-size: 1.5rem;
}

.traffic-name {
  margin: 0;
  font-weight: 700;
  color: #374151;
}

.traffic-percentage {
  margin: 4px 0 0;
  font-size: 0.85rem;
  color: #6b7280;
}

.traffic-bar {
  background: #e5e7eb;
  border-radius: 4px;
  height: 8px;
  overflow: hidden;
}

.traffic-fill {
  background: linear-gradient(90deg, #2563eb, #0ea5e9);
  height: 100%;
}

.analytics__products {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
}

.analytics__products h2 {
  margin-top: 0;
}

.analytics__table {
  width: 100%;
  border-collapse: collapse;
}

.analytics__table th,
.analytics__table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
}

.analytics__table th {
  background: #f9fafb;
  font-weight: 700;
  color: #374151;
}

.product-name {
  font-weight: 700;
}

.rating {
  background: #fef3c7;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.9rem;
  font-weight: 700;
}

.analytics__insights {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
}

.analytics__insights h2 {
  margin-top: 0;
}

.insights-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 16px;
}

.insight-card {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 14px;
  text-align: center;
}

.insight-label {
  font-size: 0.8rem;
  font-weight: 700;
  color: #6b7280;
  text-transform: uppercase;
  margin-bottom: 8px;
}

.insight-value {
  font-size: 1.5rem;
  font-weight: 900;
  color: #111827;
  margin: 0;
}

.analytics__reviews {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
}

.analytics__reviews h2 {
  margin-top: 0;
}

.review-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 16px;
}

.review-card {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 14px;
  text-align: center;
}

.review-label {
  font-size: 0.8rem;
  font-weight: 700;
  color: #6b7280;
  text-transform: uppercase;
  margin-bottom: 8px;
}

.review-value {
  font-size: 1.5rem;
  font-weight: 900;
  color: #f59e0b;
  margin: 0;
}

.analytics__actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  flex-wrap: wrap;
}

.btn {
  padding: 10px 16px;
  border: 1px solid #e5e7eb;
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
  border: none;
}

.btn-primary:hover {
  background: #1d4ed8;
}

.btn-secondary:hover {
  background: #f3f4f6;
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

@media (max-width: 900px) {
  .analytics__charts {
    grid-template-columns: 1fr;
  }

  .traffic-item {
    grid-template-columns: 40px 1fr;
  }

  .traffic-bar {
    grid-column: span 2;
  }
}
</style>
