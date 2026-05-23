<template>
  <div class="buyer-page">
    <div class="buyer-back">
      <button @click="$router.back()" class="back-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Kembali
      </button>
    </div>

    <div class="buyer-body">
      <div class="fav-header">
        <div>
          <h1 class="fav-title">Produk Favorit</h1>
          <p class="fav-sub">{{ items.length }} produk tersimpan</p>
        </div>
        <router-link to="/katalog" class="fav-btn">+ Jelajahi Produk</router-link>
      </div>

      <!-- Loading -->
      <div class="fav-grid" v-if="loading">
        <div v-for="n in 6" :key="n" class="fav-skeleton"></div>
      </div>

      <!-- Empty -->
      <div class="fav-empty" v-else-if="!items.length">
        <span></span>
        <p>Belum ada produk favorit</p>
        <router-link to="/katalog" class="fav-btn">Jelajahi Katalog</router-link>
      </div>

      <!-- Grid -->
      <div class="fav-grid" v-else>
        <div v-for="p in items" :key="p.id" class="fav-card" @click="$router.push({ name: 'product-detail', params: { id: p.id } })">

          <!-- Avatar seller menonjol di atas kartu -->
          <div class="fav-card__seller-avatar" :style="`background:${p.sellerColor}`">
            {{ p.sellerName?.[0] }}
          </div>

          <!-- Gambar produk -->
          <div class="fav-card__img" :style="`background:${p.imgBg}`">
            <span class="fav-card__emoji">{{ p.emoji }}</span>
            <!-- Rating chip -->
            <div class="fav-card__rating" v-if="p.rating">
              <span class="fav-star"></span>
              <span>{{ p.rating }} / 5.0</span>
            </div>
          </div>

          <!-- Info -->
          <div class="fav-card__body">
            <h3 class="fav-card__name">{{ p.name }}</h3>
            <p class="fav-card__price">Rp. {{ p.harga.toLocaleString('id-ID') }},00</p>

            <!-- Tombol beli -->
            <button v-if="isBuyer()" class="fav-card__buy" @click.stop="addToCart(p)">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" stroke="currentColor" stroke-width="2"/><line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="2"/><path d="M16 10a4 4 0 01-8 0" stroke="currentColor" stroke-width="2"/></svg>
            </button>
          </div>

          <!-- Footer toko -->
          <div class="fav-card__footer">
            <div class="fav-card__toko-avatar" :style="`background:${p.sellerColor}`">{{ p.sellerName[0] }}</div>
            <span class="fav-card__toko-name">{{ p.sellerName }}</span>
          </div>

          <!-- Hapus favorit -->
          <button class="fav-card__remove" @click.stop="removeItem(p.id)" title="Hapus dari favorit">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z" fill="#e53e3e" stroke="#e53e3e" stroke-width="2"/></svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import api from '@/api/axios'
import { hasRole } from '@/utils/roles'
import { normalizeImageUrl } from '@/utils/image'

export default {
  name: 'BuyerFavorit',
  setup() {
    const loading = ref(true)
    const items   = ref([])
    const localUser = ref(null)
    try { localUser.value = JSON.parse(localStorage.getItem('user') || 'null') } catch (e) { localUser.value = null }
    const isBuyer = () => hasRole(localUser.value, 'buyer')

    const removeItem = async (productId) => {
      try {
        await api.delete(`/wishlist/${productId}`)
        alert('Produk dihapus dari favorit')
      } catch (error) {
        console.error('Failed to remove item:', error)
        alert('Gagal menghapus dari favorit')
      }
      items.value = items.value.filter(i => i.id !== productId)
    }

    const addToCart  = async (p) => {
      try {
        await api.post('/cart', { product_id: p.id, quantity: 1 })
        alert(`${p.name} ditambahkan ke keranjang!`)
      } catch (error) {
        console.error('Add to cart error:', error)
        alert('Gagal menambahkan ke keranjang')
      }
    }

    onMounted(async () => {
      try {
        const r = await api.get('/wishlist')
        const apiData = r.data?.data || r.data || []
        items.value = apiData.map((item) => {
          const product = item.product || {}
          const imageUrl = normalizeImageUrl(product.image || item.product_image || null, null)
          return {
            id: item.product_id || item.id,
            name: product.name || item.product_name || 'Produk',
            harga: product.price || item.product_price || 0,
            image: imageUrl,
            imgBg: imageUrl ? `url(${imageUrl})` : 'linear-gradient(135deg,#f3f4f6,#e5e7eb)',
            sellerName: product.business?.name || product.seller?.name || product.business?.business_name || 'Toko',
            sellerColor: product.business?.color || '#10b981',
            emoji: product.emoji || '',
            addedAt: item.added_at || item.created_at,
          }
        })
      } catch (error) {
        console.error('Failed to load wishlist:', error)
        items.value = []
      } finally {
        loading.value = false
      }
    })

    return { loading, items, removeItem, addToCart, isBuyer }
  }
}
</script>

<style scoped>
.buyer-page { min-height:100vh; background:#f4f5f7; font-family:'Plus Jakarta Sans',sans-serif; }
.buyer-back { max-width:1100px; margin:0 auto; padding:20px 28px 0; }
.back-btn { display:flex; align-items:center; gap:7px; background:none; border:none; font-family:'Plus Jakarta Sans',sans-serif; font-size:.95rem; font-weight:700; color:#111827; cursor:pointer; text-decoration:underline; text-underline-offset:3px; }
.back-btn:hover { color:#e53e3e; }
.buyer-body { max-width:1100px; margin:0 auto; padding:24px 28px 64px; }

/* Header */
.fav-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; flex-wrap:wrap; gap:12px; }
.fav-title { font-family:'Fraunces',serif; font-size:1.7rem; font-weight:900; color:#111827; }
.fav-sub { font-size:.85rem; color:#9ca3af; margin-top:3px; }
.fav-btn { display:inline-flex; align-items:center; gap:6px; padding:9px 18px; background:#fff; border:1.5px solid #e5e7eb; border-radius:9px; font-family:'Plus Jakarta Sans',sans-serif; font-size:.83rem; font-weight:700; color:#374151; cursor:pointer; text-decoration:none; transition:all .18s; }
.fav-btn:hover { border-color:#e53e3e; color:#e53e3e; }

/* Grid */
.fav-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:24px; }
.fav-skeleton { height:320px; border-radius:16px; background:linear-gradient(90deg,#e5e7eb 25%,#d1d5db 50%,#e5e7eb 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
@keyframes shimmer { 0%{background-position:200% 0}100%{background-position:-200% 0} }

/* Card */
.fav-card {
  background:#e5e7eb;
  border-radius:16px;
  border:1px solid #d1d5db;
  overflow:visible;
  cursor:pointer;
  position:relative;
  transition:box-shadow .2s, transform .2s;
  margin-top:28px; /* space for floating avatar */
}
.fav-card:hover { box-shadow:0 8px 28px rgba(0,0,0,.12); transform:translateY(-3px); }

/* Avatar seller menonjol */
.fav-card__seller-avatar {
  position:absolute;
  top:-24px;
  left:14px;
  width:52px; height:52px; border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  color:#fff; font-size:1.1rem; font-weight:900;
  box-shadow:0 3px 12px rgba(0,0,0,.2);
  border:3px solid #f4f5f7;
  z-index:2;
}

/* Gambar */
.fav-card__img {
  width:100%;
  aspect-ratio:4/3;
  border-radius:14px 14px 0 0;
  display:flex; align-items:center; justify-content:center;
  position:relative;
  overflow:hidden;
}
.fav-card__emoji { font-size:3.5rem; filter:drop-shadow(0 2px 8px rgba(0,0,0,.4)); }
.fav-card__rating {
  position:absolute; bottom:8px; right:10px;
  display:flex; align-items:center; gap:3px;
  background:rgba(255,255,255,.9); border-radius:100px;
  padding:3px 8px; font-size:.72rem; font-weight:700; color:#374151;
}
.fav-star { color:#f59e0b; font-size:.85rem; }

/* Body */
.fav-card__body { padding:10px 14px 4px; display:flex; align-items:flex-start; justify-content:space-between; gap:8px; }
.fav-card__name { font-size:.88rem; font-weight:900; color:#111827; line-height:1.3; flex:1; }
.fav-card__price { font-size:.82rem; font-weight:800; color:#b45309; margin-top:4px; }
.fav-card__buy {
  width:34px; height:34px; border-radius:9px; border:1.5px solid #e5e7eb;
  background:#fff; display:flex; align-items:center; justify-content:center;
  cursor:pointer; flex-shrink:0; color:#374151; transition:all .18s;
}
.fav-card__buy:hover { border-color:#e53e3e; color:#e53e3e; }

/* Footer */
.fav-card__footer {
  display:flex; align-items:center; gap:8px;
  padding:8px 14px 14px;
  border-top:1px solid rgba(0,0,0,.06);
  margin-top:4px;
}
.fav-card__toko-avatar {
  width:26px; height:26px; border-radius:50%; flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
  color:#fff; font-size:.6rem; font-weight:800;
}
.fav-card__toko-name { font-size:.75rem; color:#6b7280; font-weight:500; }

/* Remove btn */
.fav-card__remove {
  position:absolute; top:10px; right:10px;
  width:28px; height:28px; border-radius:50%; border:none;
  background:rgba(255,255,255,.9); display:flex; align-items:center; justify-content:center;
  cursor:pointer; z-index:2; transition:all .18s; backdrop-filter:blur(4px);
}
.fav-card__remove:hover { background:#fff; transform:scale(1.1); }

/* Empty */
.fav-empty { text-align:center; padding:72px 24px; }
.fav-empty span { font-size:3.5rem; display:block; margin-bottom:12px; }
.fav-empty p { font-size:.9rem; color:#9ca3af; margin-bottom:18px; }

@media (max-width:900px) { .fav-grid { grid-template-columns:repeat(2,1fr); } }
@media (max-width:560px) { .fav-grid { grid-template-columns:1fr 1fr; gap:16px; } .buyer-body { padding:16px 14px 48px; } }
</style>