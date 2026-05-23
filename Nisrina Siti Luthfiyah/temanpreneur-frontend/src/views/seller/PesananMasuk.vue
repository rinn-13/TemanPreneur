<template>
  <section class="po">
    <header class="po__head">
      <h1 class="po__title">Pesanan Masuk</h1>
      <p class="po__sub">Daftar pesanan terbaru dari pembeli.</p>
    </header>

    <!-- Loading State -->
    <div v-if="loading" class="po__card">
      <div class="po__list">
        <div v-for="n in 3" :key="n" class="skeleton" style="height: 80px; margin-bottom: 10px;"></div>
      </div>
    </div>

    <!-- Main Content -->
    <div v-else class="po__card">
      <div class="po__toolbar">
        <select v-model="filterStatus" class="po__select">
          <option value="">Semua status</option>
          <option value="diproses">Menunggu</option>
          <option value="dikemas">Dipacking</option>
          <option value="diantarkan">Dikirim</option>
          <option value="selesai">Selesai</option>
        </select>
        <input 
          v-model="searchQuery"
          class="po__search" 
          placeholder="Cari order / nama pembeli..."
        />
      </div>

      <!-- Empty State -->
      <div v-if="filteredOrders.length === 0" class="po__empty">
        <p>Belum ada pesanan masuk</p>
      </div>

      <!-- Orders List -->
      <div v-else class="po__list">
        <div 
          class="po__item" 
          v-for="order in filteredOrders" 
          :key="order.id"
          @click="selectOrder(order)"
        >
          <div class="po__left">
            <div class="po__id">#{{ order.id }}</div>
            <div class="po__meta">
              {{ order.buyer?.name || 'Pembeli' }} • {{ order.items?.length || 1 }} item
            </div>
            <div class="po__time">{{ formatDate(order.created_at) }}</div>
          </div>
          <div class="po__right">
            <span class="po__badge" :class="badgeClass(order.status)">{{ formatStatus(order.status) }}</span>
            <div class="po__price">Rp {{ formatPrice(order.total_amount) }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Order Detail Modal -->
    <div v-if="selectedOrder" class="modal" @click.self="selectedOrder = null">
      <div class="modal__content">
        <div class="modal__header">
          <h2>Detail Pesanan #{{ selectedOrder.id }}</h2>
          <button class="modal__close" @click="selectedOrder = null">×</button>
        </div>

        <div class="modal__body">
          <div class="detail__section">
            <h3>Informasi Pembeli</h3>
            <div class="detail__row">
              <span class="detail__label">Nama:</span>
              <span class="detail__value">{{ selectedOrder.buyer?.name }}</span>
            </div>
            <div class="detail__row">
              <span class="detail__label">Email:</span>
              <span class="detail__value">{{ selectedOrder.buyer?.email }}</span>
            </div>
          </div>

          <div class="detail__section">
            <h3>Produk Dipesan</h3>
            <div 
              v-for="item in selectedOrder.items" 
              :key="item.id"
              class="detail__item"
            >
              <div class="detail__item-name">{{ item.product?.name }}</div>
              <div class="detail__item-meta">
                Qty: {{ item.quantity }} × Rp {{ formatPrice(item.price) }}
              </div>
              <div class="detail__item-subtotal">
                Subtotal: Rp {{ formatPrice(item.subtotal) }}
              </div>
            </div>
          </div>

          <div class="detail__section">
            <h3>Ruangan Pengiriman</h3>
            <div class="detail__row">
              <span class="detail__label">Nama Penerima:</span>
              <span class="detail__value">{{ selectedOrder.shipping_name }}</span>
            </div>
            <div class="detail__row">
              <span class="detail__label">Ruangan:</span>
              <span class="detail__value">{{ selectedOrder.shipping_address }}</span>
            </div>
            <div class="detail__row">
              <span class="detail__label">No. HP:</span>
              <span class="detail__value">{{ selectedOrder.shipping_phone }}</span>
            </div>
          </div>

          <div class="detail__section">
            <h3>Ringkasan Pembayaran</h3>
            <div class="detail__row">
              <span class="detail__label">Subtotal:</span>
              <span class="detail__value">Rp {{ formatPrice(selectedOrder.total_amount - selectedOrder.shipping_cost) }}</span>
            </div>
            <div class="detail__row">
              <span class="detail__label">Ongkir:</span>
              <span class="detail__value">Rp {{ formatPrice(selectedOrder.shipping_cost) }}</span>
            </div>
            <div class="detail__row detail__row--total">
              <span class="detail__label">Total:</span>
              <span class="detail__value">Rp {{ formatPrice(selectedOrder.total_amount) }}</span>
            </div>
            <div class="detail__row">
              <span class="detail__label">Metode Pembayaran:</span>
              <span class="detail__value">{{ selectedOrder.payment_method || 'COD' }}</span>
            </div>
          </div>

          <div class="detail__section">
            <h3>Status Pesanan</h3>
            <div class="detail__status">
              <span class="po__badge" :class="badgeClass(selectedOrder.status)">
                {{ formatStatus(selectedOrder.status) }}
              </span>
            </div>
            <div v-if="selectedOrder.status !== 'selesai'" class="detail__actions">
              <button 
                v-if="selectedOrder.status === 'diproses'"
                @click="updateOrderStatus(selectedOrder.id, 'dikemas')"
                class="detail__btn detail__btn--primary"
              >
                Proses (Packing)
              </button>
              <button 
                v-if="selectedOrder.status === 'dikemas'"
                @click="updateOrderStatus(selectedOrder.id, 'diantarkan')"
                class="detail__btn detail__btn--primary"
              >
                Kirim
              </button>
              <div v-if="selectedOrder.status === 'diantarkan'" class="detail__note">
                Menunggu konfirmasi selesai dari pembeli sebelum pesanan dapat ditandai selesai.
              </div>
            </div>
          </div>
        </div>

        <div class="modal__footer">
          <button @click="selectedOrder = null" class="modal__btn">Tutup</button>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import api from '@/api/axios'

export default {
  name: 'SellerPesananMasuk',
  setup() {
    const orders = ref([])
    const selectedOrder = ref(null)
    const filterStatus = ref('')
    const searchQuery = ref('')
    const loading = ref(false)

    const filteredOrders = computed(() => {
      let result = orders.value
      
      if (filterStatus.value) {
        result = result.filter(o => o.status === filterStatus.value)
      }
      
      if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase()
        result = result.filter(o => 
          (o.buyer?.name || '').toLowerCase().includes(q) ||
          o.id.toString().includes(q)
        )
      }
      
      return result
    })

    const fetchOrders = async () => {
      loading.value = true
      try {
        const { data } = await api.get('/seller/orders')
        // Seller page should use seller-specific order endpoint
        orders.value = data.data || data || []
      } catch (err) {
        console.error('Fetch orders error:', err)
        alert(err.response?.data?.message || 'Gagal memuat pesanan')
      } finally {
        loading.value = false
      }
    }

    const selectOrder = (order) => {
      selectedOrder.value = { ...order }
    }

    const formatStatus = (status) => {
      const statuses = {
        'diproses': 'Menunggu Dikemas',
        'dikemas': 'Siap Dikirim',
        'diantarkan': 'Sedang Dikirim',
        'selesai': 'Selesai'
      }
      return statuses[status] || status
    }

    const badgeClass = (status) => ({
      'diproses': 'po__badge--yellow',
      'dikemas': 'po__badge--blue',
      'diantarkan': 'po__badge--orange',
      'selesai': 'po__badge--green',
    }[status] || 'po__badge--gray')

    const formatPrice = (price) => {
      if (!price) return '0'
      return parseInt(price).toLocaleString('id-ID')
    }

    const formatDate = (date) => {
      if (!date) return ''
      return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    const updateOrderStatus = async (orderId, newStatus) => {
      try {
        const { data } = await api.patch(`/orders/${orderId}/status`, { status: newStatus })
        selectedOrder.value = data.data
        const orderIndex = orders.value.findIndex(o => o.id === orderId)
        if (orderIndex !== -1) {
          orders.value[orderIndex] = data.data
        }
        alert('Status pesanan berhasil diperbarui!')
      } catch (err) {
        console.error('Update order status error:', err)
        alert(err.response?.data?.message || 'Gagal memperbarui status')
      }
    }

    onMounted(fetchOrders)

    return {
      orders,
      selectedOrder,
      filteredOrders,
      filterStatus,
      searchQuery,
      loading,
      selectOrder,
      formatStatus,
      badgeClass,
      formatPrice,
      formatDate,
      updateOrderStatus,
    }
  }
}
</script>

<style scoped>
/* Main Container */
.po { padding: 24px 24px 56px; }
.po__head { margin-bottom: 18px; }
.po__title { font-size: 1.5rem; font-weight: 900; color: #111827; }
.po__sub { color: #6b7280; margin-top: 4px; font-size: .9rem; }

/* Card & Toolbar */
.po__card { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden; }
.po__toolbar { display: flex; gap: 10px; padding: 14px; border-bottom: 1px solid #f3f4f6; flex-wrap: wrap; }
.po__select { height: 40px; border-radius: 10px; border: 1.5px solid #e5e7eb; padding: 0 10px; outline: none; background: #fff; }
.po__search { flex: 1; min-width: 220px; height: 40px; border-radius: 10px; border: 1.5px solid #e5e7eb; padding: 0 12px; outline: none; }
.po__search:focus { border-color: #fca5a5; box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.1); }

/* List & Items */
.po__list { display: flex; flex-direction: column; }
.po__empty { padding: 40px 20px; text-align: center; color: #9ca3af; }
.po__item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px;
  border-top: 1px solid #f3f4f6;
  cursor: pointer;
  transition: background 0.2s;
}
.po__item:hover { background: #f9fafb; }
.po__left { flex: 1; }
.po__id { font-weight: 900; color: #111827; }
.po__meta { color: #9ca3af; font-size: .82rem; margin-top: 2px; }
.po__time { color: #d1d5db; font-size: 0.75rem; margin-top: 4px; }
.po__right { display: flex; align-items: center; gap: 12px; }
.po__price { font-weight: 900; color: #111827; }
.po__badge {
  display: inline-flex;
  padding: 3px 10px;
  border-radius: 999px;
  font-size: .72rem;
  font-weight: 800;
}
.po__badge--green { background: #dcfce7; color: #15803d; }
.po__badge--yellow { background: #fffbeb; color: #d97706; }
.po__badge--blue { background: #eff6ff; color: #1d4ed8; }
.po__badge--orange { background: #fed7aa; color: #9a3412; }
.po__badge--gray { background: #f3f4f6; color: #6b7280; }

/* Modal */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.modal__content {
  background: #fff;
  border-radius: 12px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}
.modal__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
}
.modal__header h2 {
  margin: 0;
  font-size: 1.25rem;
  color: #111827;
}
.modal__close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #9ca3af;
}
.modal__body {
  padding: 20px;
}
.modal__footer {
  padding: 12px 20px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
.modal__btn {
  padding: 10px 16px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: #fff;
  color: #374151;
  font-weight: 700;
  cursor: pointer;
}
.modal__btn:hover { background: #f3f4f6; }

/* Detail Sections */
.detail__section {
  margin-bottom: 20px;
}
.detail__section h3 {
  margin: 0 0 12px;
  font-size: 1rem;
  font-weight: 800;
  color: #111827;
  border-bottom: 2px solid #f3f4f6;
  padding-bottom: 8px;
}
.detail__row {
  display: grid;
  grid-template-columns: 120px 1fr;
  gap: 12px;
  padding: 8px 0;
  align-items: flex-start;
}
.detail__row--total {
  border-top: 2px solid #f3f4f6;
  padding-top: 12px;
  font-weight: 700;
}
.detail__label {
  color: #6b7280;
  font-weight: 600;
  font-size: 0.9rem;
}
.detail__value {
  color: #111827;
  word-break: break-word;
}
.detail__item {
  background: #f9fafb;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 10px;
}
.detail__item-name {
  font-weight: 700;
  color: #111827;
}
.detail__item-meta {
  font-size: 0.9rem;
  color: #6b7280;
  margin-top: 4px;
}
.detail__item-subtotal {
  font-size: 0.9rem;
  font-weight: 600;
  color: #111827;
  margin-top: 4px;
}
.detail__status {
  margin: 10px 0;
}
.detail__actions {
  display: flex;
  gap: 10px;
  margin-top: 12px;
}
.detail__note {
  flex: 1;
  padding: 14px 16px;
  border-radius: 12px;
  background: #f8fafc;
  color: #475569;
  border: 1px solid #e2e8f0;
  font-weight: 600;
}
.detail__btn {
  flex: 1;
  padding: 10px 16px;
  border-radius: 8px;
  border: none;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}
.detail__btn--primary {
  background: #111827;
  color: #fff;
}
.detail__btn--primary:hover {
  opacity: 0.92;
}

.skeleton {
  background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
}
@keyframes loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

@media (max-width: 768px) {
  .modal__content { width: 95%; }
  .detail__row { grid-template-columns: 1fr; }
}
</style>

<style scoped>
.po{ padding: 24px 24px 56px; }
.po__head{ margin-bottom: 18px; }
.po__title{ font-size: 1.5rem; font-weight: 900; color: #111827; }
.po__sub{ color:#6b7280; margin-top: 4px; font-size: .9rem; }
.po__card{ background:#fff; border:1px solid #e5e7eb; border-radius: 14px; overflow:hidden; }
.po__toolbar{ display:flex; gap:10px; padding: 14px; border-bottom: 1px solid #f3f4f6; flex-wrap: wrap; }
.po__select{ height: 40px; border-radius: 10px; border: 1.5px solid #e5e7eb; padding: 0 10px; outline:none; background:#fff; }
.po__search{ flex:1; min-width: 220px; height: 40px; border-radius: 10px; border: 1.5px solid #e5e7eb; padding: 0 12px; outline:none; }
.po__search:focus{ border-color:#fca5a5; box-shadow: 0 0 0 3px rgba(229,62,62,.10); }
.po__list{ display:flex; flex-direction:column; }
.po__item{ display:flex; justify-content:space-between; align-items:center; padding: 14px; border-top: 1px solid #f3f4f6; }
.po__id{ font-weight: 900; color:#111827; }
.po__meta{ color:#9ca3af; font-size:.82rem; margin-top: 2px; }
.po__right{ display:flex; align-items:center; gap: 12px; }
.po__price{ font-weight: 900; color:#111827; }
.po__badge{ display:inline-flex; padding: 3px 10px; border-radius: 999px; font-size:.72rem; font-weight: 800; }
.po__badge--green{ background:#dcfce7; color:#15803d; }
.po__badge--yellow{ background:#fffbeb; color:#d97706; }
.po__badge--blue{ background:#eff6ff; color:#1d4ed8; }
.po__badge--gray{ background:#f3f4f6; color:#6b7280; }
</style>

