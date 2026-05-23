```html
<template>
  <div class="buyer-page">
    <div class="buyer-back">
      <button @click="$router.back()" class="back-btn">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
          <path
            d="M19 12H5M12 5l-7 7 7 7"
            stroke="currentColor"
            stroke-width="2.5"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
        Kembali
      </button>
    </div>

    <div class="buyer-body">
      <h1 class="checkout-title">Checkout</h1>

      <!-- Main Layout -->
      <div
        class="checkout-layout"
        :class="{ 'is-loading': loading }"
      >
        <!-- LEFT -->
        <div class="checkout-main">
          <!-- Produk -->
          <div class="checkout-section">
            <h2 class="section-title">Produk Dipesan</h2>

            <div
              v-for="group in groupedItems"
              :key="group.storeId"
              class="store-group"
            >
              <div class="store-header">
                <span class="store-name">
                  {{ group.storeName }}
                </span>
              </div>

              <div class="items-list">
                <div
                  v-for="item in group.items"
                  :key="item.id"
                  class="item-row"
                >
                  <div
                    v-if="item.product?.image"
                    class="item-image item-image--photo"
                  >
                    <img
                      :src="normalizeImageUrl(item.product.image)"
                      alt=""
                    />
                  </div>

                  <div
                    v-else
                    class="item-image"
                    :style="`background:${item._imgBg}`"
                  >
                    {{ item._emoji }}
                  </div>

                  <div class="item-details">
                    <h4>{{ item.product.name }}</h4>

                    <p class="item-desc">
                      {{
                        item.product.description ||
                        'Produk TemanPreneur'
                      }}
                    </p>
                  </div>

                  <div class="item-qty-price">
                    <span class="qty">
                      {{ item.quantity }}x
                    </span>

                    <span class="price">
                      Rp
                      {{
                        Number(item.product.price)
                          .toLocaleString('id-ID')
                      }}
                    </span>
                  </div>

                  <div class="item-subtotal">
                    Rp
                    {{
                      (
                        item.product.price *
                        item.quantity
                      ).toLocaleString('id-ID')
                    }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Informasi Pengiriman -->
          <div class="checkout-section">
            <h2 class="section-title">
              Informasi Pengiriman
            </h2>

            <div class="form-group">
              <label>Nama Penerima</label>

              <input
                v-model="form.values.shipping_name"
                @blur="form.handleBlur('shipping_name')"
                type="text"
                placeholder="Nama lengkap penerima paket"
                class="form-input"
                :class="{
                  'is-error':
                    form.errors.shipping_name
                }"
              />

              <span
                v-if="form.errors.shipping_name"
                class="form-error"
              >
                {{ form.errors.shipping_name }}
              </span>
            </div>

            <div class="form-group">
              <label>Nomor Telepon</label>

              <input
                v-model="form.values.shipping_phone"
                @blur="form.handleBlur('shipping_phone')"
                type="tel"
                placeholder="Contoh: 08123456789"
                class="form-input"
                :class="{
                  'is-error':
                    form.errors.shipping_phone
                }"
              />

              <span
                v-if="form.errors.shipping_phone"
                class="form-error"
              >
                {{ form.errors.shipping_phone }}
              </span>
            </div>

            <div class="form-group">
              <label>Ruangan</label>

              <textarea
                v-model="form.values.shipping_address"
                @blur="form.handleBlur('shipping_address')"
                placeholder="Jl. Contoh No.123"
                class="form-textarea"
                :class="{
                  'is-error':
                    form.errors.shipping_address
                }"
                rows="3"
              ></textarea>

              <span
                v-if="form.errors.shipping_address"
                class="form-error"
              >
                {{ form.errors.shipping_address }}
              </span>
            </div>

            <div class="form-group">
              <label>
                Catatan untuk penjual
              </label>

              <textarea
                v-model="form.values.buyer_notes"
                rows="2"
                class="form-textarea"
                placeholder="Opsional"
              ></textarea>
            </div>
          </div>

          <!-- Pembayaran -->
          <div class="checkout-section">
            <h2 class="section-title">
              Metode Pembayaran
            </h2>

            <div class="payment-options">
              <label class="payment-option">
                <input
                  type="radio"
                  v-model="form.values.payment_method"
                  value="transfer"
                />

                <span class="payment-label">
                  <strong>
                    Transfer Bank
                  </strong>

                  <span class="payment-desc">
                    Transfer langsung ke rekening
                  </span>
                </span>
              </label>

              <label class="payment-option">
                <input
                  type="radio"
                  v-model="form.values.payment_method"
                  value="ewallet"
                />

                <span class="payment-label">
                  <strong>E-Wallet</strong>

                  <span class="payment-desc">
                    OVO, DANA, GoPay
                  </span>
                </span>
              </label>

              <label class="payment-option">
                <input
                  type="radio"
                  v-model="form.values.payment_method"
                  value="cod"
                />

                <span class="payment-label">
                  <strong>COD</strong>

                  <span class="payment-desc">
                    Bayar saat barang sampai
                  </span>
                </span>
              </label>
            </div>
          </div>
        </div>

        <!-- RIGHT -->
        <div class="checkout-summary">
          <div class="summary-card">
            <h3>Ringkasan Pesanan</h3>

            <div class="summary-section">
              <div
                v-for="group in groupedItems"
                :key="group.storeId"
                class="summary-store"
              >
                <p class="summary-store-name">
                  {{ group.storeName }}
                </p>

                <div
                  v-for="item in group.items"
                  :key="item.id"
                  class="summary-item"
                >
                  <span>
                    {{ item.product.name }}
                    ×{{ item.quantity }}
                  </span>

                  <span>
                    Rp
                    {{
                      (
                        item.product.price *
                        item.quantity
                      ).toLocaleString('id-ID')
                    }}
                  </span>
                </div>
              </div>
            </div>

            <div class="summary-divider"></div>

            <div class="summary-row">
              <span>Subtotal</span>

              <strong>
                Rp
                {{
                  subtotal.toLocaleString('id-ID')
                }}
              </strong>
            </div>

            <div class="summary-total">
              <span>Total Pembayaran</span>

              <strong class="total-price">
                Rp
                {{
                  orderGrandTotal.toLocaleString(
                    'id-ID'
                  )
                }}
              </strong>
            </div>

            <button
              type="button"
              class="checkout-btn"
              @click.stop.prevent="submitCheckout"
              :disabled="isProcessing"
            >
              <template v-if="!isProcessing">
                Lanjut ke Pembayaran
              </template>

              <template v-else>
                Memproses...
              </template>
            </button>

            <p v-if="submissionError" class="submit-error">{{ submissionError }}</p>

            <div class="security-badge">
              Transaksi aman & terpercaya
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/api/axios'
import { useOrderStore } from '@/stores/orders'
import { useFormValidation } from '@/composables/useFormValidation'
import { useToast } from '@/composables/useToast'
import { normalizeImageUrl } from '@/utils/image'

const IMG_BG = [
  'linear-gradient(135deg,#1a1a2e,#16213e)',
  'linear-gradient(135deg,#0f3460,#533483)',
  'linear-gradient(135deg,#1a0a00,#3d1a00)',
  'linear-gradient(135deg,#0a0a1a,#1a1a3d)',
  'linear-gradient(135deg,#001a0a,#003320)',
  'linear-gradient(135deg,#1a001a,#330033)',
]

const EMOJIS = ['️','','','','','','️','','','']

export default {
  name: 'BuyerCheckout',
  setup() {
    const router = useRouter()
    const route = useRoute()
    const orderStore = useOrderStore()
    const { success, error: showError } = useToast()
    
    const form = useFormValidation({
      payment_method: 'transfer',
      shipping_address: '',
      shipping_phone: '',
      shipping_name: '',
      buyer_notes: '',
    })
    
    const items = ref([])
    const loading = ref(true)
    const isProcessing = ref(false)
    const submissionError = ref('')
    const user = ref(null)

    // Group items by store
    const groupedItems = computed(() => {
      const map = {}
      items.value.forEach((item, idx) => {
        const storeId = item.product?.business_id || item.product?.business?.id || 'unknown'
        const storeName = item.product?.business?.name || 'Toko'
        
        if (!map[storeId]) {
          map[storeId] = {
            storeId,
            storeName,
            items: [],
          }
        }
        
        // Add visual properties
        if (!item._imgBg) {
          item._imgBg = IMG_BG[idx % IMG_BG.length]
          item._emoji = EMOJIS[idx % EMOJIS.length]
        }
        
        map[storeId].items.push(item)
      })
      return Object.values(map)
    })

    const subtotal = computed(() => {
      return items.value.reduce((sum, item) => {
        return sum + (item.product?.price || 0) * item.quantity
      }, 0)
    })

    const orderGrandTotal = computed(() => subtotal.value)

    const validateForm = () => {
      const rules = {
        shipping_name: { required: true, minLength: 2, minLengthMessage: 'Nama penerima wajib diisi' },
        shipping_phone: { required: true, minLength: 8, minLengthMessage: 'Nomor telepon wajib diisi (minimal 8 digit)' },
        shipping_address: { required: true, minLength: 3, minLengthMessage: 'Ruangan harus minimal 3 huruf' },
        payment_method: { required: true },
      }

      return form.validateForm(rules)
    }

    const parseItemIds = () => {
      const raw = route.query.item_ids
      if (!raw) return []
      const values = Array.isArray(raw) ? raw.join(',') : raw
      return values
        .split(',')
        .map(id => Number(id.trim()))
        .filter(id => Number.isInteger(id) && id > 0)
    }

    const fetchCart = async () => {
      loading.value = true

      try {
        const response = await api.get('/cart')
        const allItems = Array.isArray(response.data?.data)
          ? response.data.data
          : Array.isArray(response.data)
          ? response.data
          : Array.isArray(response.data?.data?.data)
          ? response.data.data.data
          : []
        const selectedItemIds = parseItemIds()

        items.value = allItems
          .filter(item => !selectedItemIds.length || selectedItemIds.includes(item.id))
          .map((item, idx) => ({
            ...item,
            _imgBg: item._imgBg || IMG_BG[idx % IMG_BG.length],
            _emoji: item._emoji || EMOJIS[idx % EMOJIS.length],
          }))

        if (selectedItemIds.length && !items.value.length) {
          showError('Produk yang dipilih tidak ditemukan di keranjang')
        }

        // Get current user to prefill shipping
        const userResponse = await api.get('/user')
        const userData = userResponse.data.user || userResponse.data
        user.value = userData

        // Prefill shipping name + phone from logged-in user profile
        if (!form.values.shipping_name) {
          form.setFieldValue('shipping_name', userData.name || '')
        }
        if (!form.values.shipping_phone) {
          form.setFieldValue('shipping_phone', userData.phone || '')
        }
      } catch (error) {
        console.error('Error loading cart:', error)
        showError(error.message)
        items.value = []
      } finally {
        loading.value = false
      }
    }

    const submitCheckout = async () => {
      console.log('Checkout button clicked', {
        isProcessing: isProcessing.value,
        values: form.values,
      })
      submissionError.value = ''

      // Run validation and log detailed info for debugging
      const isValid = validateForm()
      // ensure we log raw values and errors
      const errorsVal = form.errors?.value ?? form.errors
      const valuesVal = form.values?.value ?? form.values
      const lengths = {
        shipping_name: (valuesVal.shipping_name || '')?.toString().trim().length,
        shipping_phone: (valuesVal.shipping_phone || '')?.toString().trim().length,
        shipping_address: (valuesVal.shipping_address || '')?.toString().trim().length,
      }
      console.log('Validation result:', isValid)
      console.log('Validation errors:', errorsVal)
      console.log('Field lengths:', lengths)
      console.log('Values:', valuesVal)

      if (!isValid) {
        Object.keys(form.values).forEach((field) => {
          form.setFieldTouched(field, true)
        })
        submissionError.value = 'Mohon lengkapi semua data sebelum melanjutkan.'
        showError('Mohon lengkapi semua data sebelum melanjutkan')
        return
      }

      isProcessing.value = true
      try {
        const checkoutData = {
          payment_method: form.values.payment_method || 'transfer',
          // Shipping cost removed; backend treats shipping as zero
          shipping_cost: 0,
          shipping_address: form.values.shipping_address,
          shipping_phone: form.values.shipping_phone,
          shipping_name: form.values.shipping_name,
          buyer_notes: form.values.buyer_notes || null,
          item_ids: items.value.map(i => i.id),
        }

        const response = await api.post('/orders', checkoutData)
        const payload = response.data?.data || response.data || {}
        let newOrder = payload.order
        if (!newOrder?.id && payload.order_group?.orders?.length) {
          const first = payload.order_group.orders[0]
          newOrder = first?.data ?? first
        }
        const orderId = newOrder?.id || payload.order?.id || response.data?.order_id

        if (newOrder && newOrder.id) {
          orderStore.addOrder(newOrder)
        }

        success('Checkout berhasil! Pesanan Anda sedang diproses.')

        if (orderId) {
          router.push({ name: 'buyer.order.detail', params: { id: orderId } })
        } else {
          router.push('/buyer/orders')
        }
      } catch (error) {
        const serverMessage = error.response?.data?.message || error.message
        showError(serverMessage)
      } finally {
        isProcessing.value = false
      }
    }

    onMounted(() => {
      // Ensure active role is buyer when visiting checkout so backend allows checkout
      try {
        const ar = localStorage.getItem('activeRole')
        if (ar !== 'buyer') localStorage.setItem('activeRole', 'buyer')
      } catch (e) {}
      fetchCart()
    })

    return {
      items,
      loading,
      isProcessing,
      submissionError,
      form,
      groupedItems,
      subtotal,
      validateForm,
      submitCheckout,
      normalizeImageUrl,
      orderGrandTotal,
    }
  },
}
</script>

<style scoped>
.buyer-page {
  min-height: 100vh;
  background: #f4f5f7;
  font-family: 'Plus Jakarta Sans', sans-serif;
  overflow-x: hidden;
}

.buyer-back {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px 28px 0;
}

.back-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: transparent;
  border: none;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.95rem;
  font-weight: 700;
  color: #111827;
  cursor: pointer;
  transition: all 0.2s ease;
}

.back-btn:hover {
  color: #dc2626;
  transform: translateX(-2px);
}

.buyer-body {
  max-width: 1200px;
  margin: 0 auto;
  padding: 24px 28px 72px;
  overflow: visible;
}

.checkout-title {
  font-family: 'Fraunces', serif;
  font-size: 2.1rem;
  font-weight: 900;
  color: #111827;
  margin: 0 0 30px;
}

.checkout-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 60vh;
}

.loader {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  border: 3px solid #e5e7eb;
  border-top-color: #dc2626;
  animation: spin 0.8s linear infinite;
  margin-bottom: 14px;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.checkout-layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 360px;
  gap: 24px;
  align-items: start;
  overflow: visible;
}

.checkout-main {
  min-width: 0;
}

.checkout-section {
  background: white;
  border-radius: 22px;
  padding: 24px;
  margin-bottom: 22px;
  border: 1px solid #ececec;
  box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
}

.section-title {
  font-size: 1.05rem;
  font-weight: 800;
  color: #111827;
  margin: 0 0 20px;
}

.store-group {
  margin-bottom: 22px;
}

.store-group:last-child {
  margin-bottom: 0;
}

.store-header {
  padding: 12px 15px;
  background: linear-gradient(to right, #f9fafb, #f3f4f6);
  border-radius: 14px;
  margin-bottom: 14px;
}

.store-name {
  font-size: 0.9rem;
  font-weight: 800;
  color: #374151;
}

.items-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.item-row {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px;
  background: #fafafa;
  border-radius: 18px;
  border: 1px solid #ededed;
  transition: all 0.2s ease;
}

.item-row:hover {
  border-color: #fca5a5;
  transform: translateY(-1px);
}

.item-image {
  width: 68px;
  height: 68px;
  border-radius: 16px;
  overflow: hidden;
  flex-shrink: 0;
  background: #f3f4f6;
}

.item-image--photo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.item-details {
  flex: 1;
  min-width: 0;
}

.item-details h4 {
  margin: 0 0 5px;
  font-size: 0.95rem;
  font-weight: 800;
  color: #111827;
}

.item-desc {
  margin: 0;
  font-size: 0.78rem;
  color: #9ca3af;
  line-height: 1.5;
}

.item-qty-price {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 3px;
}

.qty {
  font-size: 0.78rem;
  color: #6b7280;
  font-weight: 700;
}

.price {
  font-size: 0.85rem;
  font-weight: 700;
  color: #374151;
}

.item-subtotal {
  font-size: 0.92rem;
  font-weight: 800;
  color: #dc2626;
  white-space: nowrap;
}

.form-group {
  margin-bottom: 18px;
}

.form-group:last-child {
  margin-bottom: 0;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-size: 0.88rem;
  font-weight: 800;
  color: #111827;
}

.form-input,
.form-textarea {
  width: 100%;
  border: 1.5px solid #e5e7eb;
  border-radius: 14px;
  padding: 12px 14px;
  font-size: 0.9rem;
  font-family: inherit;
  background: white;
  transition: all 0.2s ease;
}

.form-input:focus,
.form-textarea:focus {
  outline: none;
  border-color: #ef4444;
  box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.08);
}

.form-textarea {
  resize: vertical;
}

.form-input.is-error,
.form-textarea.is-error {
  border-color: #dc2626;
  background: #fff5f5;
}

.form-error {
  display: block;
  margin-top: 6px;
  font-size: 0.78rem;
  color: #dc2626;
  font-weight: 600;
}

.payment-options {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.payment-option {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  padding: 16px;
  border-radius: 18px;
  border: 2px solid #ececec;
  cursor: pointer;
  transition: all 0.2s ease;
  background: white;
}

.payment-option:hover {
  border-color: #f87171;
  background: #fff5f5;
}

.payment-option input {
  margin-top: 4px;
  accent-color: #dc2626;
}

.payment-label {
  flex: 1;
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.payment-label svg {
  flex-shrink: 0;
  color: #6b7280;
}

.payment-label strong {
  display: block;
  font-size: 0.9rem;
  color: #111827;
  margin-bottom: 3px;
}

.payment-desc {
  font-size: 0.76rem;
  color: #9ca3af;
}

.checkout-summary {
  position: sticky;
  top: 24px;
  z-index: 5;
  overflow: visible;
}

.summary-card {
  position: relative;
  z-index: 5;
  background: white;
  border-radius: 24px;
  padding: 22px;
  border: 1px solid #ececec;
  box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
}

.summary-card h3 {
  margin: 0 0 18px;
  font-size: 1.05rem;
  font-weight: 800;
  color: #111827;
}

.summary-section {
  margin-bottom: 18px;
}

.summary-store {
  margin-bottom: 14px;
}

.summary-store:last-child {
  margin-bottom: 0;
}

.summary-store-name {
  margin: 0 0 8px;
  font-size: 0.74rem;
  font-weight: 800;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  font-size: 0.84rem;
  margin-bottom: 6px;
  color: #374151;
}

.summary-item span:last-child {
  font-weight: 700;
}

.summary-divider {
  height: 1px;
  background: #ececec;
  margin: 18px 0;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  font-size: 0.9rem;
  color: #374151;
}

.summary-row strong {
  color: #111827;
}

.summary-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 16px;
  border-top: 2px dashed #e5e7eb;
}

.summary-total span {
  font-size: 0.92rem;
  font-weight: 800;
  color: #111827;
}

.total-price {
  font-size: 1.45rem;
  font-weight: 900;
  color: #dc2626;
}

.checkout-btn {
  width: 100%;
  border: none;
  border-radius: 16px;
  margin-top: 22px;
  padding: 15px 18px;
  background: linear-gradient(135deg, #ef4444, #b91c1c);
  color: white;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.92rem;
  font-weight: 800;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  transition: all 0.22s ease;
  box-shadow: 0 14px 24px rgba(239, 68, 68, 0.24);

  position: relative;
  z-index: 999;
  pointer-events: auto;
}

.checkout-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 18px 30px rgba(239, 68, 68, 0.32);
}

.checkout-btn:active {
  transform: scale(0.98);
}

.submit-error {
  margin-top: 12px;
  color: #b91c1c;
  font-size: 0.85rem;
  font-weight: 700;
}

.checkout-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.checkout-btn.is-loading {
  opacity: 0.85;
}

.checkout-btn svg.spin {
  animation: spin 0.8s linear infinite;
}

.security-badge {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  margin-top: 14px;
  padding-top: 14px;
  border-top: 1px solid #ececec;
  font-size: 0.76rem;
  color: #9ca3af;
}

.security-badge svg {
  color: #10b981;
}

@media (max-width: 992px) {
  .checkout-layout {
    grid-template-columns: 1fr;
  }

  .checkout-summary {
    position: static;
  }
}

@media (max-width: 768px) {
  .buyer-back,
  .buyer-body {
    padding-left: 16px;
    padding-right: 16px;
  }

  .checkout-section,
  .summary-card {
    border-radius: 20px;
    padding: 18px;
  }

  .item-row {
    flex-wrap: wrap;
  }

  .item-qty-price {
    width: 100%;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }

  .item-subtotal {
    margin-left: auto;
  }

  .checkout-title {
    font-size: 1.7rem;
  }
}
</style>