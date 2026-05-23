<template>
  <div class="invoice-container">

    <!-- HEADER -->
    <div class="invoice-header">
      <div class="invoice-title">

        <div class="invoice-brand">
          <div class="invoice-brand-logo">
            <img
              :src="brandLogoUrl"
              alt="TemanPreneur Logo"
            >
          </div>

          <div class="invoice-brand-info">
            <h1>STRUK PESANAN</h1>
            <p>TemanPreneur Marketplace</p>
          </div>
        </div>

        <div class="invoice-meta-heading">
          <span class="invoice-id">
            {{ order?.order_number || order?.number || `#${order?.id || '-'}` }}
          </span>

          <span class="invoice-date">
            {{ formatDate(order?.created_at) }}
          </span>
        </div>

      </div>
    </div>

    <!-- CONTENT -->
    <div class="invoice-content" ref="printContent">

      <!-- META -->
      <div class="invoice-meta">

        <div class="meta-item">
          <label>Nomor Invoice</label>
          <value>
            {{ order?.order_number || order?.number || `#${order?.id || '-'}` }}
          </value>
        </div>

        <div class="meta-item">
          <label>Tanggal</label>
          <value>{{ formatDate(order?.created_at) }}</value>
        </div>

        <div class="meta-item">
          <label>Status</label>

          <value :class="`status-${order?.status}`">
            {{ translateStatus(order?.status) }}
          </value>
        </div>

        <div class="meta-item">
          <label>Pembayaran</label>
          <value>{{ translatePaymentMethod(order?.payment_method) }}</value>
        </div>

      </div>

      <!-- BUYER -->
      <section class="invoice-section">
        <h3>Informasi Pembeli</h3>

        <div class="info-grid">

          <div class="info-item">
            <label>Nama</label>
            <value>{{ buyer?.name || '-' }}</value>
          </div>

          <div class="info-item">
            <label>Email</label>
            <value>{{ buyer?.email || '-' }}</value>
          </div>

          <div class="info-item">
            <label>Telepon</label>
            <value>{{ buyer?.phone || '-' }}</value>
          </div>

        </div>
      </section>

      <!-- SHIPPING -->
      <section class="invoice-section">
        <h3>Ruangan Pengiriman</h3>

        <div class="shipping-info">

          <div class="info-item">
            <label>Penerima</label>
            <value>{{ order?.shipping_name || '-' }}</value>
          </div>

          <div class="info-item">
            <label>Telepon</label>
            <value>{{ order?.shipping_phone || '-' }}</value>
          </div>

          <div class="info-item full">
            <label>Ruangan</label>
            <value>{{ order?.shipping_address || '-' }}</value>
          </div>

          <div class="info-item full" v-if="order?.buyer_notes">
            <label>Catatan</label>
            <value>{{ order.buyer_notes }}</value>
          </div>

        </div>
      </section>

      <!-- ITEMS -->
      <section class="invoice-section">
        <h3>Detail Pesanan</h3>

        <div
          v-for="(shopGroup, shopId) in groupItemsByShop"
          :key="shopId"
          class="shop-section"
        >

          <div class="shop-header">
            <h4>{{ shopGroup.shopName }}</h4>

            <span
              class="shop-contact"
              v-if="shopGroup.shopPhone"
            >
              {{ shopGroup.shopPhone }}
            </span>
          </div>

          <table class="invoice-table">

            <thead>
              <tr>
                <th>Produk</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Subtotal</th>
              </tr>
            </thead>

            <tbody>

              <tr
                v-for="item in shopGroup.items"
                :key="item.id"
              >

                <td>
                  <strong>
                    {{ item?.product_name || '-' }}
                  </strong>
                </td>

                <td class="text-center">
                  {{ item?.quantity || 0 }}
                </td>

                <td class="text-right">
                  Rp {{ Number(item?.price || 0).toLocaleString('id-ID') }}
                </td>

                <td class="text-right">
                  Rp {{
                    Number(
                      (item?.price || 0) * (item?.quantity || 0)
                    ).toLocaleString('id-ID')
                  }}
                </td>

              </tr>

            </tbody>

            <tfoot>
              <tr class="shop-subtotal">
                <td colspan="3" class="text-right">
                  Subtotal Toko
                </td>

                <td class="text-right">
                  <strong>
                    Rp {{ Number(shopGroup.subtotal).toLocaleString('id-ID') }}
                  </strong>
                </td>
              </tr>
            </tfoot>

          </table>
        </div>
      </section>

      <!-- SUMMARY -->
      <section class="invoice-section invoice-summary">

        <div class="summary-row">
          <span>Jumlah Item</span>
          <span>{{ itemCount }}</span>
        </div>

        <div class="summary-row">
          <span>Subtotal</span>
          <span>Rp {{ subtotalAmount.toLocaleString('id-ID') }}</span>
        </div>

        <div class="summary-row">
          <span>Ongkir</span>
          <span>Rp {{ shippingCost.toLocaleString('id-ID') }}</span>
        </div>

        <div class="summary-divider"></div>

        <div class="summary-row summary-total">
          <span>Total</span>

          <strong>
            Rp {{ totalAmount.toLocaleString('id-ID') }}
          </strong>
        </div>

      </section>

      <!-- TIMELINE -->
      <section class="invoice-section" v-if="trackings?.length">

        <h3>Riwayat Pesanan</h3>

        <div class="timeline">

          <div
            v-for="t in trackings"
            :key="t.id"
            class="timeline-item"
          >

            <div class="timeline-marker"></div>

            <div class="timeline-content">
              <p class="timeline-status">
                {{ translateStatus(t?.status) }}
              </p>

              <span class="timeline-date">
                {{ formatDate(t?.created_at) }}
              </span>
            </div>

          </div>

        </div>
      </section>

      <!-- FOOTER -->
      <div class="invoice-footer">

        <p>
          Terima kasih telah berbelanja di
          <strong>TemanPreneur</strong>
        </p>

        <p class="footer-note">
          Simpan struk ini sebagai bukti transaksi
        </p>

      </div>

      <!-- ACTIONS -->
      <div class="invoice-actions">

        <button
          class="action-btn"
          @click="downloadPdf"
          :disabled="pdfLoading"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
            <path
              d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"
              stroke="currentColor"
              stroke-width="1.5"
              stroke-linecap="round"
            />
          </svg>

          {{ pdfLoading ? 'Mengunduh...' : 'Download PDF' }}
        </button>

        <router-link
          :to="`/buyer/orders/${order?.id}/tracking`"
          class="action-btn view-order"
        >

          <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
            <path
              d="M12 5v14M5 12h14"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
            />
          </svg>

          Lihat Pesanan
        </router-link>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '@/api/axios'
import { normalizeImageUrl } from '@/utils/image'
import { useRoute } from 'vue-router'

const route = useRoute()
const brandLogoUrl = computed(() => normalizeImageUrl('/storage/logo1.png'))

const order = ref(null)
const buyer = ref(null)
const items = ref([])
const trackings = ref([])
const loading = ref(false)
const isPrinting = ref(false)
const pdfLoading = ref(false)

const itemCount = computed(() => {
  return items.value.reduce((count, item) => count + (Number(item.quantity) || 0), 0)
})

const subtotalAmount = computed(() => {
  if (order.value?.subtotal !== undefined && order.value?.subtotal !== null) {
    return Number(order.value.subtotal) || 0
  }
  return items.value.reduce((sum, item) => sum + (Number(item.price) || 0) * (Number(item.quantity) || 0), 0)
})

const shippingCost = computed(() => Number(order.value?.shipping_cost) || 0)
const totalAmount = computed(() => {
  if (order.value?.total_amount !== undefined && order.value?.total_amount !== null) {
    return Number(order.value.total_amount) || 0
  }
  return subtotalAmount.value + shippingCost.value
})

/* =========================
   GROUP ITEMS BY SHOP
========================= */
const groupItemsByShop = computed(() => {
  const grouped = {}

  items.value.forEach(item => {
    const businessId = item.business_id || 'unknown'
    const shopName = item.business_name || item.shop_name || 'Toko Tidak Diketahui'
    const shopPhone = item.business_phone || item.shop_phone || ''

    if (!grouped[businessId]) {
      grouped[businessId] = {
        shopId: businessId,
        shopName,
        shopPhone,
        items: [],
        subtotal: 0,
      }
    }

    const itemTotal = (item.price || 0) * (item.quantity || 0)
    grouped[businessId].items.push(item)
    grouped[businessId].subtotal += itemTotal
  })

  return grouped
})

/* =========================
   FORMAT DATE
========================= */
const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

/* =========================
   STATUS
========================= */
const translateStatus = (status) => {
  const map = {
    pending: 'Menunggu Pembayaran',
    diproses: 'Diproses',
    dikemas: 'Dikemas / Belum Dikirim',
    diantarkan: 'Dikirim',
    selesai: 'Selesai',
    dibatalkan: 'Dibatalkan',
  }
  return map[status] || status || '-'
}

/* =========================
   PAYMENT
========================= */
const translatePaymentMethod = (method) => {
  const map = {
    cod: 'Cash on Delivery',
    transfer: 'Transfer Bank',
    ewallet: 'E-Wallet',
    qris: 'QRIS',
  }
  return map[method] || method || '-'
}

/* =========================
   FETCH
========================= */
const fetchInvoice = async () => {
  loading.value = true

  try {
    const res = await api.get(`/orders/${route.params.id}/invoice`)
    const data = res.data?.data

    if (!data) return

    order.value = {
      id: data.order_id,
      order_number: data.order_number,
      number: data.order_number,
      created_at: data.created_at,
      status: data.status,
      payment_method: data.payment_method,
      subtotal: data.subtotal,
      shipping_cost: data.shipping_cost,
      total_amount: data.total_amount,
      shipping_address: data.shipping?.address,
      shipping_phone: data.shipping?.phone,
      shipping_name: data.shipping?.name,
      buyer_notes: data.shipping?.notes || '',
    }

    buyer.value = data?.buyer || {}

    items.value = (data?.items || []).map(i => ({
      id: i.id,
      product_name: i.product_name,
      quantity: i.quantity,
      price: i.price,
      business_id: i.product?.business?.id || i.business_id,
      business_name: i.product?.business?.name || i.business_name || 'Toko',
      business_phone: i.product?.business?.phone || i.business_phone || '',
      shop_name: i.business_name || i.product?.business?.name,
      shop_phone: i.business_phone || i.product?.business?.phone,
    }))

    trackings.value = data?.trackings || []

  } catch (err) {
    console.error('Invoice error:', err)
  } finally {
    loading.value = false
  }
}

onMounted(fetchInvoice)

/* =========================
   PRINT
========================= */
const downloadPdf = async () => {
  const orderId = route.params.id || order.value?.id
  if (!orderId) return
  pdfLoading.value = true
  try {
    const res = await api.get(`/orders/${orderId}/invoice/pdf`, { responseType: 'blob' })
    const url = window.URL.createObjectURL(new Blob([res.data], { type: 'application/pdf' }))
    const a = document.createElement('a')
    a.href = url
    a.download = `struk-${orderId}.pdf`
    a.click()
    window.URL.revokeObjectURL(url)
  } catch {
    alert('Gagal mengunduh PDF struk')
  } finally {
    pdfLoading.value = false
  }
}

const printInvoice = () => {
  window.print()
}
</script>

<style scoped>
.invoice-container {
  background: #ffffff;
  border-radius: 20px;
  border: 1px solid #e5e7eb;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}

.invoice-header {
  padding: 28px 32px;
  border-bottom: 1px solid #e5e7eb;
  background: #ffffff;
}

.invoice-title {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
}

.invoice-brand {
  display: flex;
  align-items: center;
  gap: 16px;
}

.invoice-brand-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.invoice-brand-info p {
  margin: 0;
  color: #6b7280;
  font-size: 0.9rem;
}

.invoice-brand-logo {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  background: #fff;
  border: 1.5px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  flex-shrink: 0;
}

.invoice-brand-logo img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  padding: 6px;
}

.invoice-title h1 {
  font-size: 1.4rem;
  font-weight: 900;
  margin: 0;
  color: #111827;
}

.invoice-meta-heading {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
}

.invoice-id {
  font-size: 0.9rem;
  font-weight: 700;
  color: #9ca3af;
}

.invoice-date {
  color: #6b7280;
  font-size: 0.85rem;
}

.invoice-content {
  padding: 32px;
}

.invoice-meta {
  display: grid;
  grid-template-columns: repeat(4,1fr);
  gap: 20px;
  margin-bottom: 32px;
  padding: 20px 24px;
  background: #f9fafb;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
}

.meta-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.meta-item label {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #9ca3af;
}

.meta-item value {
  font-size: 0.95rem;
  font-weight: 700;
  color: #111827;
}

.invoice-section {
  margin-bottom: 32px;
}

.invoice-section h3 {
  font-size: 1rem;
  font-weight: 800;
  color: #111827;
  margin: 0 0 16px;
  padding-bottom: 10px;
  border-bottom: 2px solid #e53e3e;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit,minmax(200px,1fr));
  gap: 16px;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.info-item.full {
  grid-column: 1 / -1;
}

.info-item label {
  font-size: 0.8rem;
  font-weight: 700;
  color: #9ca3af;
  text-transform: uppercase;
}

.info-item value {
  font-size: 0.9rem;
  color: #374151;
}

.shipping-info {
  display: flex;
  flex-direction: column;
  gap: 14px;
  padding: 18px;
  background: #f9fafb;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}

.shop-section {
  margin-bottom: 24px;
  padding: 20px;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  background: #fafafa;
}

.shop-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.shop-header h4 {
  margin: 0;
  font-size: 1rem;
  font-weight: 800;
}

.shop-contact {
  font-size: 0.85rem;
  color: #6b7280;
}

.invoice-table {
  width: 100%;
  border-collapse: collapse;
}

.invoice-table thead {
  background: #f3f4f6;
}

.invoice-table th,
.invoice-table td {
  padding: 14px 12px;
  font-size: 0.9rem;
  border-bottom: 1px solid #f0f0f0;
  vertical-align: middle;
}

.invoice-table th {
  font-size: 0.75rem;
  text-transform: uppercase;
  color: #6b7280;
}

.invoice-table tbody tr:hover {
  background: #fafafa;
}

.text-center {
  text-align: center;
}

.text-right {
  text-align: right;
}

.shop-subtotal {
  background: #f0fdf4;
}

.invoice-summary {
  padding: 28px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 10px 0;
  font-size: 0.95rem;
}

.summary-divider {
  border-top: 1px solid #e5e7eb;
  margin: 12px 0;
}

.summary-total {
  font-weight: 800;
  font-size: 1.15rem;
}

.summary-total strong {
  color: #e53e3e;
  font-size: 1.5rem;
}

.timeline {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.timeline-item {
  display: flex;
  gap: 12px;
}

.timeline-marker {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #e53e3e;
  margin-top: 4px;
}

.timeline-status {
  margin: 0;
  font-weight: 700;
}

.timeline-date {
  font-size: 0.8rem;
  color: #9ca3af;
}

.invoice-footer {
  margin-top: 40px;
  padding: 24px;
  text-align: center;
  border-top: 1px solid #e5e7eb;
}

.footer-note {
  font-size: 0.8rem;
  color: #9ca3af;
}

.invoice-actions {
  display: grid;
  grid-template-columns: repeat(3,1fr);
  gap: 12px;
  margin-top: 24px;
}

.action-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 16px;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
  background: #ffffff;
  font-size: 0.9rem;
  font-weight: 700;
  color: #374151;
  cursor: pointer;
  text-decoration: none;
  transition: all .2s;
}

.action-btn:hover {
  transform: translateY(-2px);
  border-color: #d1d5db;
  background: #f9fafb;
}

.view-order {
  background: linear-gradient(135deg,#e53e3e,#c53030);
  color: white;
  border: none;
}

.view-order:hover {
  opacity: .92;
}

.status-diproses {
  color: #f59e0b;
}

.status-dikemas {
  color: #3b82f6;
}

.status-diantarkan {
  color: #8b5cf6;
}

.status-selesai {
  color: #10b981;
}

.status-dibatalkan {
  color: #ef4444;
}

@media (max-width: 768px) {

  .invoice-title {
    flex-direction: column;
    align-items: flex-start;
  }

  .invoice-meta {
    grid-template-columns: repeat(2,1fr);
  }

  .invoice-content {
    padding: 20px;
  }

  .invoice-actions {
    grid-template-columns: 1fr;
  }

  .invoice-table {
    display: block;
    overflow-x: auto;
  }

  .invoice-meta-heading {
    align-items: flex-start;
  }
}

@media print {

  .invoice-actions {
    display: none !important;
  }

  .invoice-container {
    border: none;
    box-shadow: none;
  }

  .invoice-content {
    padding: 0;
  }

  .invoice-header {
    padding: 0 0 20px;
  }
}
</style>
