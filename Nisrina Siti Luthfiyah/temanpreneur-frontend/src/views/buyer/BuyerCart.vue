<template>
  <div class="buyer-page">
    <div class="buyer-back">
      <button @click="$router.back()" class="back-btn">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
          <path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Kembali
      </button>
    </div>

    <div class="buyer-body">

      <!-- ── HEADER ── -->
      <div class="cart-page-head">
        <div>
          <h1 class="cart-page-title">Keranjang Saya</h1>
          <p class="cart-page-sub" v-if="!loading && items.length">
            {{ items.length }} produk tersimpan
          </p>
        </div>
        <router-link to="/katalog" class="cart-browse-btn">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
            <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
            <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          Tambah Produk
        </router-link>
      </div>

      <!-- ── LOADING ── -->
      <div v-if="loading" class="cart-loading-state">
        <div v-for="n in 3" :key="n" class="cart-skel"></div>
      </div>

      <!-- ── EMPTY ── -->
      <div v-else-if="!items.length" class="cart-empty">
        <div class="cart-empty__icon">
          <svg width="44" height="44" viewBox="0 0 24 24" fill="none">
            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            <line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="1.5"/>
            <path d="M16 10a4 4 0 01-8 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
        </div>
        <h3>Keranjang Anda kosong</h3>
        <p>Temukan produk menarik dari teman sekolahmu!</p>
        <router-link class="cart-cta" to="/katalog">Telusuri Produk</router-link>
      </div>

      <!-- ── MAIN LAYOUT ── -->
      <div v-else class="cart-layout">

        <!-- ══ KIRI: daftar produk ══ -->
        <div class="cart-items-col">

          <!-- Pilih Semua bar -->
          <div class="cart-selectall-bar">
            <label class="cart-checkbox-wrap" title="Pilih semua produk">
              <input
                type="checkbox"
                class="cart-cb"
                :checked="isAllSelected"
                :indeterminate.prop="isSomeSelected && !isAllSelected"
                @change="toggleSelectAll"
              />
              <span class="cart-cb-box" :class="{ 'cart-cb-box--half': isSomeSelected && !isAllSelected }">
                <svg v-if="isAllSelected" width="10" height="10" viewBox="0 0 12 12" fill="none">
                  <polyline points="2,6 5,9 10,3" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <svg v-else-if="isSomeSelected" width="10" height="10" viewBox="0 0 12 12" fill="none">
                  <line x1="2" y1="6" x2="10" y2="6" stroke="white" stroke-width="2" stroke-linecap="round"/>
                </svg>
              </span>
              <span class="cart-selectall-label">
                Pilih Semua
                <span class="cart-selectall-count" v-if="selectedIds.size">({{ selectedIds.size }} dipilih)</span>
              </span>
            </label>

            <!-- Hapus yang dipilih -->
            <button
              v-if="selectedIds.size"
              class="cart-delete-selected"
              @click="deleteSelected"
            >
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                <polyline points="3,6 5,6 21,6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" stroke="currentColor" stroke-width="2"/>
              </svg>
              Hapus ({{ selectedIds.size }})
            </button>
          </div>

          <!-- Group per toko -->
          <div v-for="group in groupedBySeller" :key="group.sellerId" class="cart-group">

            <!-- Header toko -->
            <div class="cart-group-head">
              <!-- Checkbox pilih semua item toko ini -->
              <label class="cart-checkbox-wrap">
                <input
                  type="checkbox"
                  class="cart-cb"
                  :checked="isGroupAllSelected(group)"
                  :indeterminate.prop="isGroupSomeSelected(group) && !isGroupAllSelected(group)"
                  @change="toggleGroupSelect(group)"
                />
                <span class="cart-cb-box" :class="{ 'cart-cb-box--half': isGroupSomeSelected(group) && !isGroupAllSelected(group) }">
                  <svg v-if="isGroupAllSelected(group)" width="10" height="10" viewBox="0 0 12 12" fill="none">
                    <polyline points="2,6 5,9 10,3" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <svg v-else-if="isGroupSomeSelected(group)" width="10" height="10" viewBox="0 0 12 12" fill="none">
                    <line x1="2" y1="6" x2="10" y2="6" stroke="white" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                </span>
              </label>

              <!-- Avatar + nama toko -->
              <div class="cart-group-avatar" :style="`background:${group.sellerColor}`">
                <img v-if="group.sellerLogo" :src="group.sellerLogo" alt="Logo toko" class="cart-group-logo" @error="onImageError($event)" />
                <span v-else>{{ group.sellerName.charAt(0).toUpperCase() }}</span>
              </div>
              <span class="cart-group-name">{{ group.sellerName }}</span>
              <span class="cart-group-count">{{ group.items.length }} produk</span>

              <!-- Checkout toko ini (semua produk toko, tanpa perlu centang) -->
              <button
                class="cart-group-co-btn"
                @click="checkoutSeller(group)"
                :disabled="sellerCheckoutLoading === group.sellerId"
                title="Checkout semua produk dari toko ini"
              >
                <svg v-if="sellerCheckoutLoading !== group.sellerId" width="12" height="12" viewBox="0 0 24 24" fill="none">
                  <path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <svg v-else width="12" height="12" viewBox="0 0 24 24" fill="none" class="spin">
                  <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-dasharray="28 28"/>
                </svg>
                Checkout Toko Ini
              </button>
            </div>

            <!-- Item rows -->
            <div class="cart-items-card">
              <div
                v-for="item in group.items"
                :key="item.id"
                class="cart-item-row"
                :class="{ 'cart-item-row--selected': selectedIds.has(item.id) }"
              >
                <!-- Checkbox item -->
                <label class="cart-checkbox-wrap cart-checkbox-wrap--item">
                  <input
                    type="checkbox"
                    class="cart-cb"
                    :checked="selectedIds.has(item.id)"
                    @change="toggleItem(item.id)"
                  />
                  <span class="cart-cb-box">
                    <svg v-if="selectedIds.has(item.id)" width="10" height="10" viewBox="0 0 12 12" fill="none">
                      <polyline points="2,6 5,9 10,3" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </span>
                </label>

                <!-- Gambar -->
                <div class="cart-prod-img" :style="`background:${item._imgBg || '#1a1a2e'}`">
                  <img :src="item.productImage" alt="Foto produk" class="cart-prod-img-img" @error="onImageError($event)" />
                </div>

                <!-- Info -->
                <div class="cart-prod-info">
                  <p class="cart-prod-name">{{ item.product.name }}</p>
                  <p class="cart-prod-desc">{{ item.product.description || 'Produk TemanPreneur' }}</p>
                  <span class="cart-prod-stock" v-if="item.product.stock <= 5">
                    Sisa {{ item.product.stock }} stok
                  </span>
                </div>

                <!-- Harga satuan -->
                <div class="cart-prod-price">
                  <span class="cart-price-unit">Rp {{ Number(item.product.price).toLocaleString('id-ID') }}</span>
                  <span class="cart-price-label">per item</span>
                </div>

                <!-- Qty control -->
                <div class="cart-qty">
                  <button class="cart-qty__btn" @click="decrementQty(item)" :disabled="item.quantity <= 1">−</button>
                  <input
                    type="number"
                    min="1"
                    :max="item.product.stock"
                    v-model.number="item.quantity"
                    @change="updateQty(item)"
                    class="cart-qty__input"
                  />
                  <button class="cart-qty__btn" @click="incrementQty(item)" :disabled="item.quantity >= item.product.stock">+</button>
                </div>

                <!-- Subtotal -->
                <p class="cart-subtotal">
                  Rp {{ (item.product.price * item.quantity).toLocaleString('id-ID') }}
                </p>

                <!-- Hapus -->
                <button class="cart-remove-btn" @click="removeItem(item)" title="Hapus dari keranjang">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                    <polyline points="3,6 5,6 21,6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" stroke="currentColor" stroke-width="2"/>
                    <path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- ══ KANAN: ringkasan & checkout ══ -->
        <div class="cart-summary-col">
          <div class="cart-summary-card">
            <h3 class="cart-summary-title">Ringkasan Pesanan</h3>

            <!-- Info seleksi -->
            <div class="cart-summary-selection" v-if="selectedIds.size">
              <div class="css-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none">
                  <path d="M9 11l3 3L22 4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
                {{ selectedIds.size }} produk dipilih
              </div>
            </div>
            <div class="cart-summary-empty-sel" v-else>
              <p>Belum ada produk dipilih</p>
              <span>Centang produk yang ingin di-checkout</span>
            </div>

            <!-- Breakdown item terpilih -->
            <div class="cart-sel-list" v-if="selectedItems.length">
              <div
                v-for="item in selectedItems"
                :key="item.id"
                class="cart-sel-row"
              >
                <div class="csr-left">
                  <div class="csr-dot" :style="`background:${getGroupColor(item)}`"></div>
                  <span class="csr-name">{{ item.product.name }}</span>
                  <span class="csr-qty">×{{ item.quantity }}</span>
                </div>
                <span class="csr-price">Rp {{ (item.product.price * item.quantity).toLocaleString('id-ID') }}</span>
              </div>
            </div>

            <div class="cart-sum-divider" v-if="selectedItems.length"></div>

            <!-- Subtotal -->
            <div class="cart-sum-row" v-if="selectedItems.length">
              <span>Subtotal</span>
              <span>Rp {{ selectedTotal.toLocaleString('id-ID') }}</span>
            </div>
            <div class="cart-sum-row cart-sum-row--ongkir">
              <span>Ongkos kirim</span>
              <span class="cart-ongkir-badge">Dihitung saat checkout</span>
            </div>

            <div class="cart-sum-divider"></div>

            <!-- Total -->
            <div class="cart-sum-total">
              <span>Total</span>
              <strong>Rp {{ selectedTotal.toLocaleString('id-ID') }}</strong>
            </div>

            <!-- CTA Checkout yang dipilih -->
            <button
              class="checkout-btn checkout-btn--selected"
              @click="checkoutSelected"
              :disabled="checkoutLoading || !selectedIds.size"
            >
              <template v-if="!checkoutLoading">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                  <path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Checkout ({{ selectedIds.size || 0 }} produk)
              </template>
              <template v-else>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="spin">
                  <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-dasharray="28 28"/>
                </svg>
                Memproses...
              </template>
            </button>

            <!-- Checkout semua -->
            <button
              class="checkout-btn checkout-btn--all"
              @click="checkoutAll"
              :disabled="checkoutLoading || !items.length"
            >
              Checkout Semua ({{ items.length }} produk)
            </button>

            <!-- Aman -->
            <div class="cart-secure">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2"/>
              </svg>
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
import { useRouter } from 'vue-router'
import api from '@/api/axios'
import { resolveProductImage, resolveBusinessLogo, onImageError } from '@/utils/image'
import { useCartStore } from '@/stores/cart'

const SELLER_COLORS = [
  'linear-gradient(135deg,#f43f5e,#e11d48)',
  'linear-gradient(135deg,#6366f1,#4f46e5)',
  'linear-gradient(135deg,#10b981,#059669)',
  'linear-gradient(135deg,#f59e0b,#d97706)',
  'linear-gradient(135deg,#0ea5e9,#0284c7)',
  'linear-gradient(135deg,#ec4899,#db2777)',
]

const IMG_BG = [
  'linear-gradient(135deg,#1a1a2e,#16213e)',
  'linear-gradient(135deg,#0f3460,#533483)',
  'linear-gradient(135deg,#1a0a00,#3d1a00)',
  'linear-gradient(135deg,#0a0a1a,#1a1a3d)',
  'linear-gradient(135deg,#001a0a,#003320)',
  'linear-gradient(135deg,#1a001a,#330033)',
]

const EMOJIS = ['🛍️','👗','🍫','💻','💍','🎨','🌶️','📦','👜','📱']

export default {
  name: 'BuyerCart',
  setup() {
    const cartStore = useCartStore()
    const router = useRouter()
    const items = ref([])
    const loading = ref(true)
    const checkoutLoading = ref(false)
    const sellerCheckoutLoading = ref(null)

    // ── Seleksi ────────────────────────────────────────────
    const selectedIds = ref(new Set())

    const toggleItem = (id) => {
      const s = new Set(selectedIds.value)
      s.has(id) ? s.delete(id) : s.add(id)
      selectedIds.value = s
    }

    const toggleSelectAll = () => {
      if (isAllSelected.value) {
        selectedIds.value = new Set()
      } else {
        selectedIds.value = new Set(items.value.map(i => i.id))
      }
    }

    const toggleGroupSelect = (group) => {
      const s = new Set(selectedIds.value)
      if (isGroupAllSelected(group)) {
        group.items.forEach(i => s.delete(i.id))
      } else {
        group.items.forEach(i => s.add(i.id))
      }
      selectedIds.value = s
    }

    const isAllSelected = computed(() => items.value.length > 0 && items.value.every(i => selectedIds.value.has(i.id)))
    const isSomeSelected = computed(() => items.value.some(i => selectedIds.value.has(i.id)))
    const isGroupAllSelected = (group) => group.items.length > 0 && group.items.every(i => selectedIds.value.has(i.id))
    const isGroupSomeSelected = (group) => group.items.some(i => selectedIds.value.has(i.id))

    const selectedItems = computed(() => items.value.filter(i => selectedIds.value.has(i.id)))
    const selectedTotal = computed(() => selectedItems.value.reduce((s, i) => s + (i.product?.price || 0) * i.quantity, 0))

    const getGroupColor = (item) => {
      const g = groupedBySeller.value.find(g => g.items.some(x => x.id === item.id))
      return g ? g.sellerColor : '#9ca3af'
    }

    // ── UPDATE STORE (jumlah item di navbar) ──
    const updateCartStoreCount = () => {
      cartStore.setTotalItems(items.value.length)
    }

    // ── FETCH KERANJANG ──
    const fetchCart = async () => {
      loading.value = true
      try {
        const r = await api.get('/cart')
        const raw = Array.isArray(r.data?.data)
          ? r.data.data
          : Array.isArray(r.data)
          ? r.data
          : Array.isArray(r.data?.data?.data)
          ? r.data.data.data
          : []
        items.value = raw.map((item, idx) => {
          const product = item.product
            ? {
                ...item.product,
                seller: item.product.seller || (item.product.business && item.product.business.user
                  ? { id: item.product.business.user.id, name: item.product.business.user.name, business_name: item.product.business.name }
                  : null),
              }
            : item.product

          return {
            ...item,
            product,
            productImage: resolveProductImage(product),
            _imgBg: IMG_BG[idx % IMG_BG.length],
            _emoji: EMOJIS[idx % EMOJIS.length],
          }
        })
        updateCartStoreCount()
        cartStore.fetchTotalItems()
      } catch (e) {
        items.value = []
        updateCartStoreCount()
      } finally {
        loading.value = false
      }
    }

    // ── UPDATE QTY ──
    const updateQty = async (item) => {
      if (item.quantity < 1) item.quantity = 1
      if (item.quantity > item.product.stock) item.quantity = item.product.stock
      try {
        await api.put(`/cart/${item.id}`, { quantity: item.quantity })
      } catch (e) {
        alert(e.response?.data?.message || 'Gagal update jumlah')
      }
      await fetchCart()
    }

    const incrementQty = (item) => {
      if (item.quantity < item.product.stock) {
        item.quantity += 1
        updateQty(item)
      }
    }

    const decrementQty = (item) => {
      if (item.quantity > 1) {
        item.quantity -= 1
        updateQty(item)
      }
    }

    // ── HAPUS SATU ITEM ──
    const removeItem = async (item) => {
      if (!confirm('Hapus produk ini dari keranjang?')) return
      try {
        await api.delete(`/cart/${item.id}`)
        selectedIds.value = new Set([...selectedIds.value].filter(id => id !== item.id))
        await fetchCart()
      } catch (e) {
        alert(e.response?.data?.message || 'Gagal hapus item')
      }
    }

    // ── HAPUS YANG DIPILIH ──
    const deleteSelected = async () => {
      if (!selectedIds.value.size) return
      if (!confirm(`Hapus ${selectedIds.value.size} produk dari keranjang?`)) return
      try {
        await Promise.all([...selectedIds.value].map(id => api.delete(`/cart/${id}`)))
        selectedIds.value = new Set()
        await fetchCart()
      } catch (e) {
        alert('Gagal menghapus beberapa produk')
      }
    }

    // ── FUNGSI INTI CHECKOUT (menerima array item ids) ──
    const performCheckout = async (itemIds, isSellerCheckout = false, sellerId = null) => {
      if (!itemIds.length) {
        alert('Tidak ada produk yang dipilih untuk checkout.')
        return false
      }

      checkoutLoading.value = true
      if (isSellerCheckout) {
        sellerCheckoutLoading.value = sellerId
      }

      try {
        await router.push({
          name: 'buyer.checkout',
          query: { item_ids: itemIds.join(',') },
        })
        return true
      } catch (error) {
        alert('Gagal membuka halaman checkout')
        return false
      } finally {
        checkoutLoading.value = false
        sellerCheckoutLoading.value = null
      }
    }

    // ── CHECKOUT SEMUA (semua item di keranjang) ──
    const checkoutAll = async () => {
      if (!items.value.length) {
        alert('Keranjang kosong')
        return
      }
      performCheckout(items.value.map(i => i.id), false)
    }

    // ── CHECKOUT PRODUK YANG DIPILIH (dari checkbox) ──
    const checkoutSelected = async () => {
      if (!selectedItems.value.length) {
        alert('Pilih produk yang ingin di-checkout terlebih dahulu.')
        return
      }
      performCheckout(selectedItems.value.map(i => i.id), false)
    }

    // ── CHECKOUT PER TOKO (semua produk dari toko tertentu) ──
    const checkoutSeller = async (group) => {
      if (!group.items.length) return
      performCheckout(group.items.map(i => i.id), true, group.sellerId)
    }

    // ── GROUP PER SELLER ──
    const groupedBySeller = computed(() => {
      const map = {}
      items.value.forEach(item => {
        const sid = item.product?.business?.id
          || item.product?.business_id
          || item.product?.seller?.business_id
          || item.product?.seller?.id
          || item.product?.seller_id
          || 'unknown'
        const sname = item.product?.business?.name
          || item.product?.seller?.business_name
          || item.product?.seller?.name
          || item.product?.seller_name
          || item.product?.toko
          || 'Toko'
        if (!map[sid]) {
          const colorIdx = Object.keys(map).length % SELLER_COLORS.length
          map[sid] = { sellerId: sid, sellerName: sname, sellerColor: SELLER_COLORS[colorIdx], items: [] }
        }
        map[sid].items.push(item)
      })
      return Object.values(map).map(g => ({
        ...g,
        subtotal: g.items.reduce((s, i) => s + (i.product?.price || 0) * i.quantity, 0),
        sellerLogo: resolveBusinessLogo(
          g.items[0]?.product?.business?.logo ||
          g.items[0]?.product?.business?.logo_url ||
          g.items[0]?.product?.seller?.logo ||
          g.items[0]?.product?.seller?.avatar ||
          g.items[0]?.product?.seller?.photo ||
          null
        ),
      }))
    })

    onMounted(fetchCart)

    return {
      items, loading, checkoutLoading, sellerCheckoutLoading,
      selectedIds, selectedItems, selectedTotal,
      groupedBySeller,
      toggleItem, toggleSelectAll, toggleGroupSelect,
      isAllSelected, isSomeSelected, isGroupAllSelected, isGroupSomeSelected,
      getGroupColor,
      updateQty, incrementQty, decrementQty,
      removeItem, deleteSelected,
      checkoutAll,
      checkoutSelected,
      checkoutSeller,  // <-- fungsi baru untuk checkout per toko
    }
  },
}
</script>

<style scoped>
/* ─── (style persis sama seperti yang Anda miliki, tidak diubah) ─── */
.buyer-page { min-height:100vh; background:#f4f5f7; font-family:'Plus Jakarta Sans',sans-serif; }
.buyer-back { max-width:1100px; margin:0 auto; padding:20px 28px 0; }
.back-btn { display:flex; align-items:center; gap:7px; background:none; border:none; font-family:'Plus Jakarta Sans',sans-serif; font-size:.95rem; font-weight:700; color:#111827; cursor:pointer; text-decoration:underline; text-underline-offset:3px; }
.back-btn:hover { color:#e53e3e; }
.buyer-body { max-width:1100px; margin:0 auto; padding:24px 28px 72px; }
.cart-page-head { display:flex; align-items:flex-end; justify-content:space-between; gap:14px; margin-bottom:22px; flex-wrap:wrap; }
.cart-page-title { font-family:'Fraunces',serif; font-size:clamp(1.5rem,3vw,2rem); font-weight:900; color:#111827; margin:0 0 4px; letter-spacing:-.02em; }
.cart-page-sub { font-size:.83rem; color:#9ca3af; margin:0; }
.cart-browse-btn { display:inline-flex; align-items:center; gap:6px; padding:9px 18px; background:#fff; border:1.5px solid #e5e7eb; border-radius:9px; font-family:'Plus Jakarta Sans',sans-serif; font-size:.83rem; font-weight:700; color:#374151; text-decoration:none; transition:all .18s; }
.cart-browse-btn:hover { border-color:#e53e3e; color:#e53e3e; }
.cart-loading-state { display:flex; flex-direction:column; gap:12px; }
.cart-skel { height:88px; border-radius:14px; background:linear-gradient(90deg,#e5e7eb 25%,#d1d5db 50%,#e5e7eb 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
@keyframes shimmer { 0%{background-position:200% 0}100%{background-position:-200% 0} }
.cart-empty { text-align:center; padding:72px 24px; background:#fff; border-radius:20px; border:1.5px solid #e5e7eb; }
.cart-empty__icon { width:80px; height:80px; margin:0 auto 16px; background:#f3f4f6; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#9ca3af; }
.cart-empty h3 { font-family:'Fraunces',serif; font-size:1.3rem; font-weight:900; color:#111827; margin:0 0 6px; }
.cart-empty p { font-size:.875rem; color:#9ca3af; margin:0 0 20px; }
.cart-cta { display:inline-flex; align-items:center; gap:6px; padding:10px 24px; background:linear-gradient(135deg,#f56565,#c53030); border-radius:10px; color:#fff; font-weight:700; font-size:.875rem; text-decoration:none; transition:all .18s; }
.cart-cta:hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(229,62,62,.35); }
.cart-layout { display:grid; grid-template-columns:1fr 300px; gap:20px; align-items:start; }
.cart-selectall-bar { display:flex; align-items:center; gap:12px; background:#fff; border-radius:12px; border:1.5px solid #e5e7eb; padding:12px 18px; margin-bottom:12px; }
.cart-selectall-label { font-size:.85rem; font-weight:700; color:#111827; }
.cart-selectall-count { font-size:.78rem; font-weight:600; color:#e53e3e; margin-left:5px; }
.cart-delete-selected { margin-left:auto; display:flex; align-items:center; gap:5px; padding:6px 13px; background:#fff5f5; border:1.5px solid #fecaca; border-radius:8px; font-family:'Plus Jakarta Sans',sans-serif; font-size:.78rem; font-weight:700; color:#c53030; cursor:pointer; transition:all .18s; }
.cart-delete-selected:hover { background:#e53e3e; color:#fff; border-color:#e53e3e; }
.cart-checkbox-wrap { display:flex; align-items:center; gap:9px; cursor:pointer; flex-shrink:0; }
.cart-checkbox-wrap input[type="checkbox"] { position:absolute; opacity:0; width:0; height:0; }
.cart-cb-box { width:18px; height:18px; border-radius:5px; border:2px solid #d1d5db; background:#fff; display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:all .18s; }
.cart-checkbox-wrap input:checked ~ .cart-cb-box { background:#e53e3e; border-color:#e53e3e; }
.cart-cb-box--half { background:#e53e3e; border-color:#e53e3e; }
.cart-checkbox-wrap:hover .cart-cb-box { border-color:#e53e3e; }
.cart-group { margin-bottom:14px; }
.cart-group-head { display:flex; align-items:center; gap:10px; background:#fff; border:1.5px solid #e5e7eb; border-radius:12px 12px 0 0; border-bottom:none; padding:12px 16px; }
.cart-group-avatar { width:32px; height:32px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; color:#fff; font-size:.7rem; font-weight:900; overflow:hidden; }
.cart-group-logo { width:100%; height:100%; object-fit:cover; display:block; }
.cart-group-name { font-size:.875rem; font-weight:800; color:#111827; flex:1; }
.cart-group-count { font-size:.72rem; color:#9ca3af; white-space:nowrap; }
.cart-group-co-btn { display:flex; align-items:center; gap:5px; padding:6px 14px; border-radius:8px; border:none; background:linear-gradient(135deg,#f56565,#c53030); color:#fff; font-family:'Plus Jakarta Sans',sans-serif; font-size:.75rem; font-weight:700; cursor:pointer; transition:all .18s; white-space:nowrap; box-shadow:0 2px 8px rgba(229,62,62,.25); }
.cart-group-co-btn:hover:not(:disabled) { transform:translateY(-1px); box-shadow:0 4px 12px rgba(229,62,62,.35); }
.cart-group-co-btn:disabled { opacity:.4; cursor:not-allowed; }
.cart-items-card { background:#fff; border:1.5px solid #e5e7eb; border-top:none; border-radius:0 0 12px 12px; overflow:hidden; }
.cart-item-row { display:grid; grid-template-columns:22px 52px 1fr auto auto auto 32px; gap:12px; padding:14px 16px; align-items:center; border-top:1px solid #f3f4f6; transition:background .15s; }
.cart-item-row--selected { background:#fffbfb; }
.cart-item-row:hover { background:#fafafa; }
.cart-prod-img { width:52px; height:52px; border-radius:10px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:1.4rem; overflow:hidden; }
.cart-prod-img-img { width:100%; height:100%; object-fit:cover; display:block; }
.cart-prod-info { min-width:0; }
.cart-prod-name { font-size:.875rem; font-weight:800; color:#111827; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; margin:0 0 3px; }
.cart-prod-desc { font-size:.72rem; color:#9ca3af; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; margin:0; }
.cart-prod-stock { display:inline-block; margin-top:4px; font-size:.65rem; font-weight:700; color:#d97706; background:#fffbeb; border:1px solid #fde68a; border-radius:4px; padding:1px 6px; }
.cart-prod-price { text-align:center; min-width:70px; }
.cart-price-unit { display:block; font-size:.82rem; font-weight:700; color:#374151; white-space:nowrap; }
.cart-price-label { font-size:.65rem; color:#9ca3af; }
.cart-qty { display:flex; align-items:center; border:1.5px solid #e5e7eb; border-radius:8px; overflow:hidden; }
.cart-qty__btn { width:28px; height:28px; border:none; background:#f9fafb; font-size:.95rem; font-weight:700; color:#374151; cursor:pointer; transition:all .15s; flex-shrink:0; }
.cart-qty__btn:hover:not(:disabled) { background:#f3f4f6; color:#e53e3e; }
.cart-qty__btn:disabled { opacity:.35; cursor:not-allowed; }
.cart-qty__input { width:36px; height:28px; border:none; border-left:1px solid #e5e7eb; border-right:1px solid #e5e7eb; text-align:center; font-family:'Plus Jakarta Sans',sans-serif; font-size:.82rem; font-weight:700; color:#111827; outline:none; background:#fff; }
.cart-qty__input::-webkit-inner-spin-button, .cart-qty__input::-webkit-outer-spin-button { -webkit-appearance:none; }
.cart-subtotal { font-size:.875rem; font-weight:800; color:#e53e3e; white-space:nowrap; text-align:right; margin:0; min-width:80px; }
.cart-remove-btn { width:30px; height:30px; border:1.5px solid #fecaca; border-radius:7px; background:#fff5f5; color:#c53030; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:all .18s; }
.cart-remove-btn:hover { background:#e53e3e; color:#fff; border-color:#e53e3e; }
.cart-summary-card { background:#d0d5dd; border-radius:18px; border:1px solid #9ca3af; padding:20px 20px 18px; position:sticky; top:84px; }
.cart-summary-title { font-family:'Fraunces',serif; font-size:1rem; font-weight:900; color:#111827; margin:0 0 14px; }
.cart-summary-selection { margin-bottom:12px; }
.css-badge { display:inline-flex; align-items:center; gap:5px; padding:4px 11px; background:#dcfce7; border:1px solid #bbf7d0; border-radius:100px; font-size:.75rem; font-weight:700; color:#15803d; }
.cart-summary-empty-sel { padding:12px; background:rgba(0,0,0,.04); border-radius:10px; text-align:center; margin-bottom:12px; }
.cart-summary-empty-sel p { font-size:.82rem; font-weight:700; color:#6b7280; margin:0 0 2px; }
.cart-summary-empty-sel span { font-size:.72rem; color:#9ca3af; }
.cart-sel-list { display:flex; flex-direction:column; gap:6px; margin-bottom:12px; max-height:160px; overflow-y:auto; }
.cart-sel-list::-webkit-scrollbar { width:3px; }
.cart-sel-list::-webkit-scrollbar-thumb { background:rgba(0,0,0,.1); border-radius:2px; }
.cart-sel-row { display:flex; align-items:center; justify-content:space-between; gap:6px; }
.csr-left { display:flex; align-items:center; gap:6px; flex:1; min-width:0; }
.csr-dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
.csr-name { font-size:.75rem; color:#374151; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; flex:1; }
.csr-qty { font-size:.72rem; color:#9ca3af; flex-shrink:0; }
.csr-price { font-size:.75rem; font-weight:700; color:#374151; white-space:nowrap; flex-shrink:0; }
.cart-sum-divider { height:1px; background:rgba(0,0,0,.1); margin:12px 0; }
.cart-sum-row { display:flex; justify-content:space-between; align-items:center; font-size:.8rem; color:#6b7280; margin-bottom:6px; }
.cart-sum-row--ongkir { margin-bottom:0; }
.cart-ongkir-badge { font-size:.65rem; font-weight:700; color:#6366f1; background:rgba(99,102,241,.1); padding:2px 7px; border-radius:4px; }
.cart-sum-total { display:flex; justify-content:space-between; align-items:baseline; margin-bottom:14px; }
.cart-sum-total span { font-size:.875rem; font-weight:700; color:#374151; }
.cart-sum-total strong { font-family:'Fraunces',serif; font-size:1.4rem; font-weight:900; color:#e53e3e; }
.checkout-btn { display:flex; align-items:center; justify-content:center; gap:8px; width:100%; padding:12px; border:none; border-radius:11px; font-family:'Plus Jakarta Sans',sans-serif; font-size:.875rem; font-weight:800; cursor:pointer; transition:all .2s; margin-bottom:8px; }
.checkout-btn--selected { background:linear-gradient(135deg,#f56565,#c53030); color:#fff; box-shadow:0 3px 12px rgba(229,62,62,.3); }
.checkout-btn--selected:hover:not(:disabled) { transform:translateY(-1px); box-shadow:0 5px 18px rgba(229,62,62,.4); }
.checkout-btn--selected:disabled { opacity:.45; cursor:not-allowed; transform:none; box-shadow:none; }
.checkout-btn--all { background:rgba(0,0,0,.06); color:#6b7280; font-size:.78rem; font-weight:700; padding:9px; margin-bottom:12px; }
.checkout-btn--all:hover:not(:disabled) { background:rgba(0,0,0,.1); color:#374151; }
.checkout-btn--all:disabled { opacity:.4; cursor:not-allowed; }
.cart-secure { display:flex; align-items:center; justify-content:center; gap:5px; font-size:.7rem; color:#6b7280; }
.spin { animation:spin .8s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }
@media (max-width:900px) { .cart-layout { grid-template-columns:1fr; } .cart-summary-card { position:static; } }
@media (max-width:640px) { .buyer-body { padding:16px 14px 48px; } .cart-item-row { grid-template-columns:22px 44px 1fr; grid-template-rows:auto auto; row-gap:10px; } .cart-prod-price { display:none; } .cart-qty { grid-column:2; } .cart-subtotal { grid-column:3; text-align:right; } .cart-remove-btn { grid-column:3; grid-row:2; justify-self:end; } }
</style>