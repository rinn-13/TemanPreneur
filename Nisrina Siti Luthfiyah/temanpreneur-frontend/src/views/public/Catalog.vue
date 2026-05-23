<template>
  <div class="katalog-page">
    <AbstractBackground :intensity="1.1" />

    <!-- ── PAGE HEADER ── -->
    <PageHeader title="Katalog Produk" subtitle="Temukan barang unik buatan teman sekolahmu">
      <template #actions>
        <router-link to="/ajukan-usaha" class="kat-btn kat-btn--outline">Buka Toko Anda</router-link>
        <div class="kat-search-bar">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          <input v-model="searchQ" type="text" placeholder="Cari produk..."/>
        </div>
      </template>
    </PageHeader>

    <!-- ── FILTER BAR ── -->
    <div class="kat-filterbar">
      <div class="kat-filterbar__inner">
        <!-- Kategori pills -->
        <div class="kat-pills">
          <button
            v-for="k in kategoriList"
            :key="k.id"
            class="kat-pill"
            :class="{ 'kat-pill--active': (k.id === 'semua' && activeKat === '') || activeKat === k.id }"
            @click="selectKategori(k.id)"
          >
            {{ k.icon }} {{ k.nama }}
          </button>
        </div>
        <!-- Sort -->
        <div class="kat-sort">
          <select v-model="sortBy" class="kat-select">
            <option value="terlaris">Terlaris</option>
            <option value="terbaru">Terbaru</option>
            <option value="termurah">Termurah</option>
            <option value="termahal">Termahal</option>
            <option value="rating">Rating</option>
          </select>
          <span class="kat-total">{{ totalProductCount }} produk</span>
        </div>
      </div>
    </div>

    <!-- ── PRODUK GRID ── -->
    <div class="kat-body">

      <!-- Loading skeleton -->
      <div class="kat-grid" v-if="loading">
        <div v-for="n in 9" :key="n" class="kat-skeleton"></div>
      </div>

      <!-- Empty -->
      <div class="kat-empty" v-else-if="!paginated.length">
        <span></span>
        <p>Tidak ada produk ditemukan</p>
        <button class="kat-btn kat-btn--outline" @click="resetFilter">Reset Filter</button>
      </div>

      <!-- Grid -->
      <div class="kat-grid" v-else>
        <div
          v-for="p in paginated"
          :key="p.id"
          class="prod-card tp-card-equal"
          @click="$router.push({ name: 'product-detail', params: { id: p.id } })"
        >
          <!-- Gambar area -->
          <div class="prod-card__img tp-card-equal__media">
            <img
              v-if="p.imageUrl"
              :src="p.imageUrl"
              :alt="p.nama"
              class="tp-img-cover"
              loading="lazy"
              @error="$onImageError($event)"
            />
            <div
              v-else
              class="prod-card__img-inner"
              :style="`background:${p.gradient}`"
            >
              <span class="prod-card__emoji">{{ p.emoji || p.nama?.charAt(0) || 'P' }}</span>
            </div>
            <!-- Badge -->
            <span class="prod-badge prod-badge--hot" v-if="p.terjual > 100">Terlaris</span>
            <span class="prod-badge prod-badge--new" v-else-if="p.isNew">Baru</span>
            <!-- Wishlist -->
            <button
              class="prod-wish"
              :class="{ 'prod-wish--on': wishlist.has(p.id) }"
              @click.stop="toggleWish(p.id)"
              title="Simpan"
            >
              <svg width="14" height="14" viewBox="0 0 24 24">
                <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"
                  :stroke="wishlist.has(p.id) ? '#e53e3e' : '#9ca3af'"
                  :fill="wishlist.has(p.id) ? '#e53e3e' : 'none'"
                  stroke-width="2"/>
              </svg>
            </button>
          </div>

          <!-- Info -->
          <div class="prod-card__info tp-card-equal__body">
            <!-- Rating -->
            <div class="prod-rating">
              <span class="prod-rating__val">{{ p.rating }} / 5.0</span>
            </div>
            <p class="prod-name">{{ p.nama }}</p>
           <p class="prod-price">
  Rp {{ p?.harga?.toLocaleString('id-ID') ?? '0' }},00
</p>
            <div class="prod-toko">
              <div class="prod-toko__avatar" :style="p.businessLogo ? `background-image:url(${p.businessLogo});background-size:cover;background-position:center` : `background:${p.tokoColor}`">
                <img v-if="p.businessLogo" :src="p.businessLogo" :alt="p.toko" />
                <span v-else>{{ p.toko?.charAt(0) || 'T' }}</span>
              </div>
              <span>{{ p.toko }}</span>
              <span v-if="p.isPremium" class="prod-toko__badge">⭐</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="kat-pagination" v-if="totalPages > 1">
        <button class="kpg" :disabled="page===1" @click="page--;scrollTop()">← Prev</button>
        <button
          v-for="pp in totalPages"
          :key="pp"
          class="kpg"
          :class="{'kpg--active': page===pp}"
          @click="page=pp;scrollTop()"
        >{{ pp }}</button>
        <button class="kpg" :disabled="page===totalPages" @click="page++;scrollTop()">Next →</button>
      </div>

    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api/axios'
import { productService } from '@/services/product.js'
import { categoryService } from '@/services/category.js'
import { normalizeImageUrl, resolveProductImage, resolveBusinessLogo } from '@/utils/image'
import AbstractBackground from '@/components/AbstractBackground.vue'

export default {
  name: 'KatalogPage',
  components: { AbstractBackground },
  setup() {
    const route  = useRoute()
    const router = useRouter()
    const loading = ref(false)
    const products  = ref([])
    const searchQ   = ref('')
    const activeKat = ref('')
    const sortBy    = ref('terlaris')
    const page      = ref(1)
    const perPage   = 9
    const minPrice  = ref(0)
    const maxPrice  = ref(1000000)
    const wishlist  = ref(new Set())

    const kategoriList = ref([
      {id:'semua', nama:'Semua'},
      {id:'fashion', nama:'Fashion'},
      {id:'kuliner', nama:'Kuliner'},
      {id:'kerajinan',nama:'Kerajinan'},
      {id:'digital', nama:'Digital'},
      {id:'aksesoris',nama:'Aksesoris'},
      {id:'lainnya', nama:'Lainnya',},
    ])

    const normalizeProduct = (product) => ({
      ...product,
      id: product.id,
      nama: product.nama || product.name || 'Produk',
      harga: product.harga || product.price || 0,
      stok: product.stok || product.stock || 0,
      imageUrl: resolveProductImage(product.imageUrl || product.image || product.images?.[0]),
      toko: product.business?.name || product.seller?.business_name || product.seller?.name || product.toko || 'Toko',
      tokoColor: product.tokoColor || '#d1d5db',
      businessLogo: resolveBusinessLogo(product.business?.logo || product.seller?.business_logo, product.business?.is_premium || product.seller?.is_premium),
      kategori: (
        product.category_slug ||
        product.kategori ||
        product.category?.slug ||
        product.category?.name ||
        product.category ||
        'lainnya'
      ).toString().toLowerCase(),
      terjual: product.terjual || product.total_sold || 0,
      rating: typeof product.rating === 'number' ? product.rating : product.reviews_avg_rating || 0,
      gradient: product.gradient || product.gradien || 'linear-gradient(135deg,#d1d5dd,#b0b8c4)',
      emoji: product.emoji || '',
      ownerName: product.business?.user?.name || product.seller?.name || '',
      business: product.business || null,
      seller: product.seller || null,
      isPremium: product.business?.is_premium || product.seller?.is_premium || false
    })

    const paginationMeta = ref({ total: 0, last_page: 1 })

    // Daftar produk dari API (filter utama di server — sama dengan home/katalog)
    const filtered = computed(() => {
      return products.value.filter((product) => {
        const matchesCategory = !activeKat.value || activeKat.value === 'semua'
          ? true
          : (product.kategori || product.category || '').toString().toLowerCase() === activeKat.value
        const matchesSearch = !searchQ.value.trim()
          ? true
          : [product.nama, product.toko, product.description, product.excerpt]
              .filter(Boolean)
              .some((field) => field.toString().toLowerCase().includes(searchQ.value.trim().toLowerCase()))
        const matchesMinPrice = product.harga >= minPrice.value
        const matchesMaxPrice = product.harga <= maxPrice.value
        return matchesCategory && matchesSearch && matchesMinPrice && matchesMaxPrice
      })
    })

    const totalPages = computed(() => {
      const t = paginationMeta.value.total || filtered.value.length
      return Math.max(1, Math.ceil(t / perPage))
    })

    const paginated = computed(() => {
      if (paginationMeta.value.total) {
        return filtered.value.slice(0, perPage)
      }
      return filtered.value.slice((page.value - 1) * perPage, page.value * perPage)
    })

    const totalProductCount = computed(() => paginationMeta.value.total || filtered.value.length)

    const toggleWish = async (id) => {
      const w = new Set(wishlist.value)
      if (!w.has(id)) {
        try {
          await api.post('/wishlist', { product_id: id })
          w.add(id)
        } catch (error) {
          console.error('Wishlist add error:', error)
        }
      } else {
        try {
          await api.delete(`/wishlist/${id}`)
          w.delete(id)
        } catch (error) {
          console.error('Wishlist remove error:', error)
        }
      }
      wishlist.value = w
    }

    const loadWishlist = async () => {
      try {
        const r = await api.get('/wishlist')
        const data = r.data?.data || r.data || []
        wishlist.value = new Set(
          data.map((item) => item.product_id || item.product?.id).filter(Boolean)
        )
      } catch (error) {
        console.error('Failed to load wishlist:', error)
        wishlist.value = new Set()
      }
    }

    const updateQuery = () => {
      const query = {}
      if (activeKat.value && activeKat.value !== 'semua') query.kategori = activeKat.value
      if (searchQ.value.trim()) query.q = searchQ.value.trim()
      router.replace({ name: 'catalog', query })
    }

    const selectKategori = (kategoriId) => {
      const normalized = kategoriId === 'semua' ? '' : kategoriId
      activeKat.value = activeKat.value === normalized ? '' : normalized
      page.value = 1
      updateQuery()
    }

    const resetFilter = () => {
      activeKat.value = ''
      searchQ.value = ''
      sortBy.value = 'terlaris'
      minPrice.value = 0
      maxPrice.value = 1000000
      page.value = 1
      router.replace({ name: 'catalog', query: {} })
    }

    const scrollTop = () => window.scrollTo({ top:0, behavior:'smooth' })

    watch(() => route.query, q => {
      const incomingKat = q.kategori ? q.kategori.toString().toLowerCase() : ''
      const incomingQ  = q.q ? q.q.toString() : ''
      const list = Array.isArray(kategoriList.value) ? kategoriList.value : []
      activeKat.value = list.some((k) => k.id === incomingKat) ? incomingKat : ''
      searchQ.value = incomingQ
    }, { immediate: true })

    watch([activeKat, searchQ], () => {
      page.value = 1
      updateQuery()
    })

    watch(sortBy, () => { page.value = 1 })

    const loadCategories = async () => {
      try {
        const categories = await categoryService.getCategories()
        if (Array.isArray(categories) && categories.length) {
          kategoriList.value = [
            {id:'semua', icon:'️', nama:'Semua'},
            ...categories.map((cat) => ({
              id: cat.slug,
              icon: categoryIcons[cat.slug] || '',
              nama: cat.name,
            })),
          ]
        }
      } catch (error) {
        console.error('Failed to load category list:', error)
      }
    }

    let searchDebounce = null
    const fetchProducts = async () => {
      loading.value = true
      try {
        const result = await productService.getProducts({
          category: activeKat.value === '' || activeKat.value === 'semua' ? null : activeKat.value,
          sort: sortBy.value,
          min_price: minPrice.value,
          max_price: maxPrice.value,
          search: searchQ.value.trim() || undefined,
          page: page.value,
          per_page: perPage,
        })
        const list = Array.isArray(result.data) ? result.data : []
        let normalizedProducts = list.map(normalizeProduct)
        
        // Randomize products on refresh (when not searching or filtering)
        if (!searchQ.value.trim() && activeKat.value === '' && sortBy.value === 'terlaris') {
          // Fisher-Yates shuffle algorithm
          for (let i = normalizedProducts.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [normalizedProducts[i], normalizedProducts[j]] = [normalizedProducts[j], normalizedProducts[i]];
          }
        }
        
        products.value = normalizedProducts
        const pag = result.pagination || result.meta || {}
        paginationMeta.value = {
          total: pag.total ?? list.length,
          last_page: pag.last_page ?? 1,
        }
      } catch (error) {
        console.error('Fetch products failed:', error)
        products.value = []
      } finally {
        loading.value = false
      }
    }

    watch([activeKat, sortBy, minPrice, maxPrice, page], fetchProducts)

    watch(searchQ, () => {
      page.value = 1
      if (searchDebounce) clearTimeout(searchDebounce)
      searchDebounce = setTimeout(fetchProducts, 350)
    })

    onMounted(async () => {
      await Promise.all([fetchProducts(), loadWishlist(), loadCategories()])
    })

    return {
      loading,
      filtered,
      paginated,
      totalPages,
      totalProductCount,
      page,
      searchQ,
      activeKat,
      sortBy,
      minPrice,
      maxPrice,
      wishlist,
      kategoriList,
      toggleWish,
      resetFilter,
      scrollTop,
      selectKategori,
    }
  }
}
</script>

<style scoped>
/* ─── Base ─────────────────────────────────── */
.katalog-page {
  min-height: 100vh;
  background: #ffffff;
  font-family: 'Plus Jakarta Sans', sans-serif;
  padding-top: 24px;
}

/* ─── Header ────────────────────────────────── */
/* ─── Page Header Actions ────────────────────── */
.kat-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 9px 18px;
  border-radius: 8px;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: .83rem;
  font-weight: 700;
  cursor: pointer;
  text-decoration: none;
  white-space: nowrap;
  transition: all .18s;
  border: none;
}
.kat-btn--outline {
  background: #fff;
  color: #374151;
  border: 1.5px solid #d1d5db;
}
.kat-btn--outline:hover { border-color: #e53e3e; color: #e53e3e; }
.kat-search-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  height: 38px;
  padding: 0 13px;
  background: #f9fafb;
  border: 1.5px solid #e5e7eb;
  border-radius: 8px;
  color: #9ca3af;
  min-width: 200px;
  transition: border-color .18s;
}
.kat-search-bar:focus-within { border-color: #e53e3e; background: #fff; }
.kat-search-bar input {
  flex: 1;
  border: none;
  outline: none;
  background: transparent;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: .83rem;
  color: #111827;
}
.kat-search-bar input::placeholder { color: #9ca3af; }

/* ─── Filter bar ────────────────────────────── */
.kat-filterbar {
  background: #fff;
  border-bottom: 1.5px solid #f3f4f6;
  position: sticky;
  top: var(--navbar-h, 68px);
  z-index: 10;
}
.kat-filterbar__inner {
  max-width: var(--tp-container-max, 1280px);
  margin: 0 auto;
  padding: 0 var(--tp-container-pad, 28px);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}
.kat-pills {
  display: flex;
  gap: 0;
  overflow-x: auto;
  scrollbar-width: none;
  -webkit-overflow-scrolling: touch;
  flex: 1;
}
.kat-pills::-webkit-scrollbar { display: none; }
.kat-pill {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 14px 16px;
  border: none;
  background: transparent;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: .82rem;
  font-weight: 500;
  color: #6b7280;
  cursor: pointer;
  white-space: nowrap;
  border-bottom: 2px solid transparent;
  transition: all .15s;
}
.kat-pill:hover { color: #111827; background: #f9fafb; }
.kat-pill--active { color: #e53e3e; border-bottom-color: #e53e3e; font-weight: 700; }
.kat-sort {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-shrink: 0;
}
.kat-select {
  height: 34px;
  padding: 0 10px;
  border: 1.5px solid #e5e7eb;
  border-radius: 7px;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: .8rem;
  font-weight: 600;
  color: #374151;
  background: #fff;
  outline: none;
  cursor: pointer;
}
.kat-total {
  font-size: .75rem;
  font-weight: 600;
  color: #9ca3af;
  white-space: nowrap;
}

/* ─── Body & Grid ───────────────────────────── */
.kat-body {
  max-width: var(--tp-container-max, 1280px);
  margin: 0 auto;
  padding: 28px var(--tp-container-pad, 28px) 64px;
}
.kat-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: var(--tp-grid-gap, 20px);
  align-items: stretch;
}
.kat-skeleton {
  height: 290px;
  border-radius: 12px;
  background: linear-gradient(90deg, #f3f4f6 25%, #e9eaec 50%, #f3f4f6 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
}
@keyframes shimmer {
  0%   { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* ─── Product card ──────────────────────────── */
.prod-card {
  background: #fff;
  border-radius: var(--tp-radius-lg, 16px);
  border: 1px solid #e5e7eb;
  overflow: hidden;
  cursor: pointer;
  transition: box-shadow .2s, transform .2s;
  box-shadow: var(--tp-shadow, 0 4px 16px rgba(0,0,0,.06));
  display: flex;
  flex-direction: column;
  height: 100%;
}
.prod-card:hover {
  box-shadow: 0 6px 24px rgba(0,0,0,.09);
  transform: translateY(-3px);
}

/* image block */
.prod-card__img {
  position: relative;
  width: 100%;
  aspect-ratio: 1 / 1;
  background: #f3f4f6;
  overflow: hidden;
}
.prod-card__img-inner {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}
.prod-card__emoji {
  font-size: 3.5rem;
  user-select: none;
  filter: drop-shadow(0 2px 8px rgba(0,0,0,.3));
}
/* badges */
.prod-badge {
  position: absolute;
  top: 10px;
  left: 10px;
  padding: 3px 9px;
  border-radius: 100px;
  font-size: .62rem;
  font-weight: 700;
  letter-spacing: .02em;
}
.prod-badge--hot { background: #e53e3e; color: #fff; }
.prod-badge--new { background: #059669; color: #fff; }

/* wishlist btn */
.prod-wish {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  border: none;
  background: rgba(255,255,255,.9);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all .18s;
  backdrop-filter: blur(4px);
}
.prod-wish:hover { background: #fff; transform: scale(1.1); }
.prod-wish--on { background: #fff5f5; }

/* info block */
.prod-card__info {
  padding: 12px 14px 14px;
  border-top: 1px solid #f3f4f6;
  flex: 1;
  display: flex;
  flex-direction: column;
}
.prod-rating {
  display: flex;
  align-items: center;
  gap: 4px;
  margin-bottom: 4px;
}
.prod-star {
  color: #f59e0b;
  font-size: .9rem;
}
.prod-rating__val {
  font-size: .72rem;
  font-weight: 700;
  color: #374151;
}
.prod-name {
  font-size: .875rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 5px;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.prod-price {
  font-size: .875rem;
  font-weight: 800;
  color: #e53e3e;
  margin-bottom: 8px;
}
.prod-toko {
  display: flex;
  align-items: center;
  gap: 6px;
}
.prod-toko__avatar {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: .55rem;
  font-weight: 800;
  overflow: hidden;
}
.prod-toko__avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.prod-toko span {
  font-size: .72rem;
  color: #9ca3af;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.prod-toko__badge {
  font-size: 0.6rem;
  margin-left: 2px;
}

/* ─── Pagination ────────────────────────────── */
.kat-pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  margin-top: 36px;
  flex-wrap: wrap;
}
.kpg {
  padding: 8px 16px;
  border: 1.5px solid #e5e7eb;
  border-radius: 8px;
  background: #fff;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: .82rem;
  font-weight: 600;
  color: #374151;
  cursor: pointer;
  transition: all .18s;
}
.kpg:hover:not(:disabled) { border-color: #e53e3e; color: #e53e3e; }
.kpg--active { background: #e53e3e; border-color: #e53e3e; color: #fff; }
.kpg:disabled { opacity: .35; cursor: not-allowed; }

/* ─── Empty ─────────────────────────────────── */
.kat-empty {
  text-align: center;
  padding: 72px 24px;
}
.kat-empty span { font-size: 3rem; display: block; margin-bottom: 12px; }
.kat-empty p { font-size: .9rem; color: #9ca3af; margin-bottom: 18px; }

/* ─── Responsive ────────────────────────────── */
@media (max-width: 900px) {
  .kat-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {
  .kat-body { padding: 18px 14px 48px; }
  .kat-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
  .kat-filterbar__inner { padding: 0 14px; }
}
@media (max-width: 380px) {
  .kat-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
}
.kat-price-filter {
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-width: 200px;
}
.price-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #374151;
  text-align: center;
}
.price-slider {
  height: 4px;
  border-radius: 2px;
  background: #e5e7eb;
  outline: none;
  -webkit-appearance: none;
  appearance: none;
}
.price-slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #e53e3e;
  cursor: pointer;
}
.price-slider::-moz-range-thumb {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #e53e3e;
  cursor: pointer;
  border: none;
}
</style>