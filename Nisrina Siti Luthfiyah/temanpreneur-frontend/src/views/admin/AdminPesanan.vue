<template>
  <div class="ap">
    <div class="ap__head">
      <div>
        <h1 class="ap__title">Riwayat <span>Pesanan</span></h1>
        <p class="ap__sub">Kelola semua pesanan yang masuk di marketplace</p>
      </div>
    </div>

    <!-- Filter Tabs -->
    <div class="mk__tabs-row">
      <button v-for="t in statusTabs" :key="t.id" class="mk__tab" :class="{'mk__tab--active':activeStatus===t.id}" @click="activeStatus=t.id;page=1">
        <span class="mk__tab-icon">{{ t.icon }}</span>
        {{ t.label }}
        <span class="mk__tab-count">{{ t.count }}</span>
      </button>
    </div>

    <div class="ap__card">
      <div class="ap__toolbar">
        <div class="ap__search">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          <input v-model="searchQuery" placeholder="Cari order, pembeli, penjual..."/>
        </div>
        <button class="ap__btn ap__btn--ghost" @click="fetchOrders" style="margin-left:8px;">Segarkan</button>
      </div>

      <div v-if="loading" style="padding:20px;text-align:center">
        <p>Memuat pesanan...</p>
      </div>

      <div v-else-if="filteredOrders.length === 0" class="empty-state">
        <span></span>
        <p>Tidak ada pesanan ditemukan</p>
      </div>

      <div v-else>
        <!-- Orders Table -->
        <div class="orders-table">
          <div class="orders-header">
            <div class="col-id">ID Pesanan</div>
            <div class="col-buyer">Pembeli</div>
            <div class="col-items">Produk</div>
            <div class="col-total">Total</div>
            <div class="col-status">Status</div>
            <div class="col-date">Tanggal</div>
            <div class="col-action">Aksi</div>
          </div>

          <div v-for="order in paginatedOrders" :key="order.id" class="order-row" :class="`order-status-${order.status}`">
            <div class="col-id"><strong>#{{ String(order.id).padStart(6, '0') }}</strong></div>
            <div class="col-buyer">
              <div class="buyer-name">{{ order.buyer?.name || 'Unknown' }}</div>
              <div class="buyer-email">{{ order.buyer?.email }}</div>
            </div>
            <div class="col-items">{{ order.items?.length || 0 }} produk</div>
            <div class="col-total">Rp {{ (order.total_amount || 0).toLocaleString('id-ID') }}</div>
            <div class="col-status">
              <span class="status-badge" :class="`status-${order.status}`">{{ statusLabel[order.status] }}</span>
            </div>
            <div class="col-date">{{ new Date(order.created_at).toLocaleDateString('id-ID') }}</div>
            <div class="col-action">
              <button class="order-btn" @click="viewOrder(order)" title="Lihat Detail">️</button>
              <button class="order-btn" @click="updateStatus(order)" title="Update Status" v-if="order.status !== 'selesai'"></button>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div class="ap__pagination" v-if="totalPages > 1">
          <span class="ap__pagination-info">{{ (page-1)*perPage+1 }}–{{ Math.min(page*perPage, filteredOrders.length) }} dari {{ filteredOrders.length }}</span>
          <div class="ap__pagination-btns">
            <button class="ap__page-btn" :disabled="page===1" @click="page--">‹</button>
            <button v-for="pp in totalPages" :key="pp" class="ap__page-btn" :class="{'ap__page-btn--active':page===pp}" @click="page=pp">{{ pp }}</button>
            <button class="ap__page-btn" :disabled="page===totalPages" @click="page++">›</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <teleport to="body">
      <div class="modal-bg" v-if="detail.open" @click.self="detail.open=false">
        <div class="modal-box order-detail-modal">
          <button class="mk__modal-close" @click="detail.open=false" aria-label="Tutup">×</button>
          <h2>Detail Pesanan #{{ String(detail.order?.id).padStart(6, '0') }}</h2>
          
          <div v-if="detail.order" class="detail-content">
            <div class="detail-section">
              <h3> Informasi Pembeli</h3>
              <p><strong>Nama:</strong> {{ detail.order.buyer?.name }}</p>
              <p><strong>Email:</strong> {{ detail.order.buyer?.email }}</p>
              <p><strong>Telepon:</strong> {{ detail.order.buyer?.phone }}</p>
            </div>

            <div class="detail-section">
              <h3> Pengiriman</h3>
              <p><strong>Nama Penerima:</strong> {{ detail.order.shipping_name }}</p>
              <p><strong>Ruangan:</strong> {{ detail.order.shipping_address }}</p>
              <p><strong>Telepon:</strong> {{ detail.order.shipping_phone }}</p>
            </div>

            <div class="detail-section">
              <h3> Item Pesanan</h3>
              <div v-for="item in detail.order.items" :key="item.id" class="order-item">
                <div>
                  <strong>{{ item.product?.name }}</strong>
                  <div class="item-meta">{{ item.quantity }}x @ Rp {{ (item.price || 0).toLocaleString('id-ID') }}</div>
                </div>
                <div class="item-subtotal">Rp {{ (item.subtotal || 0).toLocaleString('id-ID') }}</div>
              </div>
            </div>

            <div class="detail-section">
              <h3> Ringkasan Pembayaran</h3>
              <div class="payment-summary">
                <div class="summary-row">
                  <span>Subtotal:</span>
                  <span>Rp {{ (detail.order.total_price || 0).toLocaleString('id-ID') }}</span>
                </div>
                <div class="summary-row">
                  <span>Ongkos Kirim:</span>
                  <span>Rp {{ (detail.order.shipping_cost || 0).toLocaleString('id-ID') }}</span>
                </div>
                <div class="summary-row total">
                  <span>Total:</span>
                  <span>Rp {{ (detail.order.total_amount || 0).toLocaleString('id-ID') }}</span>
                </div>
              </div>
              <p><strong>Metode Pembayaran:</strong> {{ detail.order.payment_method }}</p>
              <p><strong>Status:</strong> <span class="status-badge" :class="`status-${detail.order.status}`">{{ statusLabel[detail.order.status] }}</span></p>
            </div>

            <div class="detail-section">
              <h3> Riwayat Status</h3>
              <div v-if="detail.order.tracking" class="tracking-history">
                <div v-for="track in detail.order.tracking" :key="track.id" class="tracking-item">
                  <div class="track-status">{{ statusLabel[track.status] }}</div>
                  <div class="track-date">{{ new Date(track.created_at).toLocaleString('id-ID') }}</div>
                </div>
              </div>
            </div>
          </div>

          <button class="ap__btn ap__btn--ghost" @click="detail.open=false" style="width:100%;margin-top:16px;">Tutup</button>
        </div>
      </div>
    </teleport>

    <!-- Update Status Modal -->
    <teleport to="body">
      <div class="modal-bg" v-if="updateModal.open" @click.self="updateModal.open=false">
        <div class="modal-box" style="max-width:400px">
          <button class="mk__modal-close" @click="updateModal.open=false" aria-label="Tutup">×</button>
          <h2>Update Status Pesanan</h2>
          
          <div v-if="updateModal.order" class="update-content">
            <p style="color:#6b7280;margin-bottom:16px">Pesanan: <strong>#{{ String(updateModal.order.id).padStart(6, '0') }}</strong></p>
            <p style="margin-bottom:16px">Status saat ini: <span class="status-badge" :class="`status-${updateModal.order.status}`">{{ statusLabel[updateModal.order.status] }}</span></p>
            
            <div v-if="nextStatuses.length > 0">
              <p style="margin-bottom:12px">Pilih status berikutnya:</p>
              <div style="display:flex;flex-direction:column;gap:8px">
                <button v-for="status in nextStatuses" :key="status" class="status-option" @click="submitStatusUpdate(status)">
                  {{ statusLabel[status] }}
                </button>
              </div>
            </div>
            <div v-else style="color:#d97706;padding:12px;background:#fef3c7;border-radius:8px">
              Pesanan sudah mencapai status akhir
            </div>
          </div>

          <button class="ap__btn ap__btn--ghost" @click="updateModal.open=false" style="width:100%;margin-top:16px;">Batal</button>
        </div>
      </div>
    </teleport>

    <div class="ap__toast" :class="{'ap__toast--show':toast.show}">{{ toast.msg }}</div>
  </div>
</template>

<script>
import { ref, computed, onMounted, reactive } from 'vue'
import api from '@/api/axios'

export default {
  name: 'AdminPesanan',
  setup() {
    const loading = ref(true)
    const searchQuery = ref('')
    const activeStatus = ref('all')
    const page = ref(1)
    const perPage = 10
    const orders = ref([])
    const detail = reactive({ open: false, order: null })
    const updateModal = reactive({ open: false, order: null })
    const toast = reactive({ show: false, msg: '' })

    const statusLabel = {
      diproses: 'Diproses',
      dikemas: 'Dikemas',
      diantarkan: 'Diantarkan',
      selesai: 'Selesai'
    }

    const statusTabs = computed(() => [
      { id: 'all', icon: '', label: 'Semua', count: orders.value.length },
      { id: 'diproses', icon: '⏳', label: 'Diproses', count: orders.value.filter(o => o.status === 'diproses').length },
      { id: 'dikemas', icon: '', label: 'Dikemas', count: orders.value.filter(o => o.status === 'dikemas').length },
      { id: 'diantarkan', icon: '', label: 'Diantarkan', count: orders.value.filter(o => o.status === 'diantarkan').length },
      { id: 'selesai', icon: '', label: 'Selesai', count: orders.value.filter(o => o.status === 'selesai').length },
    ])

    const filteredOrders = computed(() => {
      let result = [...orders.value]
      
      if (activeStatus.value !== 'all') {
        result = result.filter(o => o.status === activeStatus.value)
      }
      
      if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase()
        result = result.filter(o => 
          String(o.id).includes(q) ||
          o.buyer?.name?.toLowerCase().includes(q) ||
          o.buyer?.email?.toLowerCase().includes(q)
        )
      }
      
      return result
    })

    const totalPages = computed(() => Math.max(1, Math.ceil(filteredOrders.value.length / perPage)))
    const paginatedOrders = computed(() => filteredOrders.value.slice((page.value - 1) * perPage, page.value * perPage))

    const nextStatuses = computed(() => {
      const order = updateModal.order
      if (!order) return []
      
      const transitions = {
        diproses: ['dikemas'],
        dikemas: ['diantarkan'],
        diantarkan: ['selesai'],
        selesai: []
      }
      
      return transitions[order.status] || []
    })

    const showToast = (msg) => {
      toast.msg = msg
      toast.show = true
      setTimeout(() => { toast.show = false }, 3000)
    }

    const fetchOrders = async () => {
      loading.value = true
      try {
        const response = await api.get('/orders')
        const data = response.data.data || []
        orders.value = data
      } catch (error) {
        console.error('Failed to load admin orders:', error)
        orders.value = []
        showToast('Gagal memuat pesanan')
      } finally {
        loading.value = false
      }
    }

    const viewOrder = (order) => {
      detail.order = order
      detail.open = true
    }

    const updateStatus = (order) => {
      updateModal.order = order
      updateModal.open = true
    }

    const submitStatusUpdate = async (newStatus) => {
      try {
        await api.patch(`/orders/${updateModal.order.id}/status`, { status: newStatus })
        updateModal.order.status = newStatus
        const idx = orders.value.findIndex(o => o.id === updateModal.order.id)
        if (idx !== -1) {
          orders.value[idx].status = newStatus
        }
        updateModal.open = false
        showToast(` Status berhasil diperbarui ke ${statusLabel[newStatus]}`)
      } catch (error) {
        console.error('Failed to update status:', error)
        showToast('Gagal update status')
      }
    }

    onMounted(async () => {
      await fetchOrders()
    })

    return {
      loading, searchQuery, activeStatus, page, perPage, orders,
      detail, updateModal, toast, statusLabel, statusTabs,
      filteredOrders, totalPages, paginatedOrders, nextStatuses,
      fetchOrders, viewOrder, updateStatus, submitStatusUpdate
    }
  }
}
</script>

<style scoped>
.ap { padding: 20px; }
.ap__head { margin-bottom: 24px; }
.ap__title { font-size: 1.8rem; font-weight: 900; margin: 0 0 8px; font-family: 'Fraunces', serif; }
.ap__title span { color: #e53e3e; }
.ap__sub { color: #6b7280; margin: 0; font-size: 0.9rem; }
.ap__card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }

.mk__tabs-row { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
.mk__tab { display: flex; align-items: center; gap: 7px; padding: 9px 18px; border: 1.5px solid #e5e7eb; border-radius: 10px; background: #fff; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.83rem; font-weight: 600; color: #6b7280; cursor: pointer; transition: all 0.2s; }
.mk__tab:hover { border-color: #fca5a5; color: #e53e3e; background: #fff5f5; }
.mk__tab--active { border-color: #e53e3e; color: #e53e3e; background: #fff5f5; }
.mk__tab-count { padding: 2px 8px; border-radius: 100px; font-size: 0.68rem; background: #e5e7eb; color: #6b7280; }

.ap__toolbar { display: flex; gap: 12px; margin-bottom: 16px; align-items: center; }
.ap__search { flex: 1; display: flex; align-items: center; gap: 10px; border: 1px solid #e5e7eb; padding: 10px 14px; border-radius: 10px; background: #f9fafb; }
.ap__search input { flex: 1; border: none; background: transparent; outline: none; font-size: 0.9rem; }

.orders-table { display: flex; flex-direction: column; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
.orders-header { display: grid; grid-template-columns: 1fr 1.5fr 1fr 1fr 1fr 1fr 80px; gap: 12px; padding: 12px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; font-weight: 600; font-size: 0.85rem; color: #374151; }
.order-row { display: grid; grid-template-columns: 1fr 1.5fr 1fr 1fr 1fr 1fr 80px; gap: 12px; padding: 14px 12px; border-bottom: 1px solid #e5e7eb; align-items: center; transition: background 0.2s; }
.order-row:hover { background: #f9fafb; }

.col-id { font-weight: 600; color: #e53e3e; }
.col-buyer { min-width: 0; }
.buyer-name { font-size: 0.9rem; font-weight: 600; color: #111827; overflow: hidden; text-overflow: ellipsis; }
.buyer-email { font-size: 0.75rem; color: #9ca3af; }
.col-items { font-size: 0.9rem; }
.col-total { font-weight: 600; color: #111827; }
.col-status { }
.col-date { font-size: 0.9rem; color: #6b7280; }
.col-action { display: flex; gap: 4px; }

.status-badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; }
.status-diproses { background: #fef3c7; color: #92400e; }
.status-dikemas { background: #dbeafe; color: #1e40af; }
.status-diantarkan { background: #e0e7ff; color: #3730a3; }
.status-selesai { background: #dcfce7; color: #166534; }

.order-btn { width: 28px; height: 28px; border: none; border-radius: 6px; background: #f3f4f6; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; transition: all 0.2s; }
.order-btn:hover { background: #e5e7eb; transform: scale(1.1); }

.empty-state { text-align: center; padding: 40px 20px; }
.empty-state span { font-size: 3rem; display: block; margin-bottom: 12px; }
.empty-state p { color: #6b7280; margin: 0; }

.ap__pagination { display: flex; justify-content: space-between; align-items: center; margin-top: 16px; }
.ap__pagination-info { font-size: 0.9rem; color: #6b7280; }
.ap__pagination-btns { display: flex; gap: 6px; }
.ap__page-btn { padding: 6px 10px; border: 1px solid #e5e7eb; border-radius: 6px; background: #fff; cursor: pointer; font-size: 0.85rem; transition: all 0.2s; }
.ap__page-btn:hover { border-color: #e53e3e; color: #e53e3e; }
.ap__page-btn--active { background: #e53e3e; color: #fff; border-color: #e53e3e; }
.ap__page-btn:disabled { cursor: not-allowed; opacity: 0.5; }

.modal-bg { position: fixed; inset: 0; background: rgba(0,0,0,0.45); backdrop-filter: blur(4px); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 24px; }
.modal-box { background: #fff; border-radius: 16px; padding: 24px; width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,0.2); animation: slideIn 0.22s ease; position: relative; overflow-y: auto; max-height: 90vh; }
@keyframes slideIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }

.mk__modal-close { position: absolute; top: 16px; right: 16px; width: 28px; height: 28px; border: none; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 0.9rem; }

.order-detail-modal { max-width: 600px; }
.order-detail-modal h2 { font-family: 'Fraunces', serif; font-size: 1.3rem; margin-bottom: 20px; color: #111827; }

.detail-content { display: flex; flex-direction: column; gap: 20px; }
.detail-section h3 { font-size: 0.95rem; font-weight: 700; color: #111827; margin: 0 0 12px; }
.detail-section p { margin: 6px 0; font-size: 0.9rem; color: #374151; }

.order-item { display: flex; justify-content: space-between; align-items: flex-start; padding: 10px; background: #f9fafb; border-radius: 6px; margin-bottom: 8px; }
.item-meta { font-size: 0.85rem; color: #6b7280; margin-top: 4px; }
.item-subtotal { font-weight: 600; color: #e53e3e; }

.payment-summary { background: #f9fafb; padding: 12px; border-radius: 6px; margin-bottom: 12px; }
.summary-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 0.9rem; color: #374151; border-bottom: 1px solid #e5e7eb; }
.summary-row.total { border: none; font-weight: 700; color: #111827; font-size: 1rem; padding-top: 12px; }

.tracking-history { display: flex; flex-direction: column; gap: 12px; }
.tracking-item { display: flex; justify-content: space-between; padding: 10px; background: #f9fafb; border-radius: 6px; }
.track-status { font-weight: 600; color: #111827; }
.track-date { font-size: 0.85rem; color: #6b7280; }

.update-content { display: flex; flex-direction: column; gap: 16px; }
.status-option { padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; cursor: pointer; font-weight: 600; color: #374151; transition: all 0.2s; }
.status-option:hover { border-color: #22c55e; background: #f0fdf4; color: #16a34a; }

.ap__btn { padding: 10px 16px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.ap__btn--ghost { background: #f3f4f6; color: #374151; }
.ap__btn--ghost:hover { background: #e5e7eb; }

.ap__toast { position: fixed; bottom: 28px; right: 28px; z-index: 2000; padding: 13px 22px; border-radius: 12px; font-size: 0.875rem; font-weight: 600; box-shadow: 0 8px 28px rgba(0,0,0,0.15); transform: translateY(20px); opacity: 0; transition: all 0.3s; pointer-events: none; background: #111827; color: #fff; }
.ap__toast--show { transform: translateY(0); opacity: 1; }
</style>
