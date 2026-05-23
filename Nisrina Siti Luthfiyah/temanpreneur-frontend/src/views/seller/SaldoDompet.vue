<template>
  <section class="sd">
    <header class="sd__head">
      <h1 class="sd__title">Saldo Dompet</h1>
      <p class="sd__sub">Saldo dan riwayat transaksi seller langsung terhubung dengan data dompet.</p>
    </header>

    <div class="sd__grid">
      <div class="sd__card sd__balance">
        <p class="sd__label">Saldo Tersedia</p>
        <p class="sd__value">Rp {{ balance.toLocaleString('id-ID') }}</p>
        <div class="sd__info-row">
          <span>Total Masuk: Rp {{ totalEarned.toLocaleString('id-ID') }}</span>
          <span>Total Penarikan: Rp {{ totalWithdrawn.toLocaleString('id-ID') }}</span>
        </div>
        <div class="sd__actions">
          <button class="sd__btn sd__btn--primary">Tarik Dana</button>
          <button class="sd__btn" @click="fetchWallet">Segarkan</button>
        </div>
      </div>

      <div class="sd__card">
        <p class="sd__label">Transaksi Terakhir</p>
        <div v-if="loading" class="sd__loading">Memuat riwayat...</div>
        <div v-else-if="error" class="sd__error">{{ error }}</div>
        <div v-else-if="transactions.length" class="sd__tx" v-for="t in transactions" :key="t.id">
          <div>
            <div class="sd__tx-title">{{ t.description || t.type || 'Transaksi' }}</div>
            <div class="sd__tx-meta">{{ formatDate(t.created_at) }}</div>
          </div>
          <div class="sd__tx-amt" :class="t.amount > 0 ? 'sd__tx-amt--plus' : 'sd__tx-amt--minus'">
            {{ t.amount > 0 ? '+' : '' }}Rp {{ Math.abs(t.amount).toLocaleString('id-ID') }}
          </div>
        </div>
        <div v-else class="sd__empty">Belum ada transaksi dompet.</div>
      </div>
    </div>
  </section>
</template>

<script>
import { ref, onMounted } from 'vue'
import api from '@/api/axios'

export default {
  name: 'SellerSaldoDompet',
  setup() {
    const balance = ref(0)
    const totalEarned = ref(0)
    const totalWithdrawn = ref(0)
    const transactions = ref([])
    const loading = ref(true)
    const error = ref('')

    const fetchWallet = async () => {
      loading.value = true
      error.value = ''
      try {
        const response = await api.get('/seller/wallet')
        const data = response.data.data || {}
        balance.value = data.balance ?? 0
        totalEarned.value = data.total_earned ?? 0
        totalWithdrawn.value = data.total_withdrawn ?? 0
        transactions.value = Array.isArray(data.transactions) ? data.transactions : []
      } catch (err) {
        console.error('Fetch wallet failed:', err)
        error.value = err.response?.data?.message || 'Gagal memuat data dompet'
      } finally {
        loading.value = false
      }
    }

    const formatDate = (value) => {
      if (!value) return '-'
      return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric'
      }).format(new Date(value))
    }

    onMounted(fetchWallet)

    return {
      balance,
      totalEarned,
      totalWithdrawn,
      transactions,
      loading,
      error,
      fetchWallet,
      formatDate,
    }
  }
}
</script>

<style scoped>
.sd{ padding: 24px 24px 56px; }
.sd__head{ margin-bottom: 18px; }
.sd__title{ font-size: 1.5rem; font-weight: 900; color: #111827; }
.sd__sub{ color:#6b7280; margin-top: 4px; font-size: .9rem; }
.sd__grid{ display:grid; grid-template-columns: 1.1fr .9fr; gap: 12px; }
.sd__card{ background:#fff; border:1px solid #e5e7eb; border-radius: 14px; padding: 14px; }
.sd__label{ color:#9ca3af; font-weight: 800; font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; }
.sd__value{ margin-top: 6px; font-size: 1.6rem; font-weight: 900; color:#111827; }
.sd__actions{ margin-top: 12px; display:flex; gap: 10px; flex-wrap:wrap; }
.sd__btn{ height: 40px; padding: 0 14px; border-radius: 10px; border: 1px solid #e5e7eb; background:#fff; font-weight: 800; cursor:pointer; }
.sd__btn--primary{ border:none; background:#111827; color:#fff; }
.sd__btn:hover{ opacity: .92; }
.sd__tx{ display:flex; justify-content:space-between; align-items:center; padding: 10px 0; border-top: 1px solid #f3f4f6; }
.sd__tx:first-of-type{ border-top:none; padding-top: 12px; }
.sd__tx-title{ font-weight: 900; color:#111827; }
.sd__tx-meta{ color:#9ca3af; font-size: .78rem; margin-top: 2px; }
.sd__tx-amt{ font-weight: 900; }
.sd__tx-amt--plus{ color:#15803d; }
.sd__tx-amt--minus{ color:#b91c1c; }
@media (max-width: 900px){ .sd__grid{ grid-template-columns: 1fr; } }
</style>

