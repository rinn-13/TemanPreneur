<template>
  <div class="ap">
    <div class="ap__head">
      <div>
        <h1 class="ap__title">Moderasi <span>Konten</span></h1>
        <p class="ap__sub">Tinjau dan kelola produk serta blog yang dikirimkan oleh seller</p>
      </div>
      <!-- Mode switcher di header -->
      <div class="mk__mode-switcher">
        <button class="mk__mode-btn" :class="{ 'mk__mode-btn--active': mode === 'products' }" @click="mode = 'products'">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z" stroke="currentColor" stroke-width="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" stroke="currentColor" stroke-width="2"/></svg>
          Produk
        </button>
        <button class="mk__mode-btn" :class="{ 'mk__mode-btn--active': mode === 'blogs' }" @click="mode = 'blogs'">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="2"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          Blog
          <span class="mk__mode-count" v-if="blogs.length">{{ blogs.length }}</span>
        </button>
      </div>
    </div>

    <!-- ── PRODUCTS MODE ── -->
    <template v-if="mode === 'products'">
      <!-- Status Tabs -->
      <div class="mk__tabs-row">
        <button v-for="t in tabs" :key="t.id" class="mk__tab" :class="{ 'mk__tab--active': activeTab === t.id }" @click="activeTab = t.id; page = 1">
          <span class="mk__tab-icon">{{ t.icon }}</span>
          {{ t.label }}
          <span class="mk__tab-count" :class="`mk__tab-count--${t.id}`">{{ t.count }}</span>
        </button>
      </div>

      <div class="ap__card">
        <div class="ap__toolbar">
          <div class="ap__search">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            <input v-model="q" placeholder="Cari nama produk atau toko..."/>
          </div>
          <select v-model="filterKat" class="ap__select">
            <option value="">Semua Kategori</option>
            <option value="fashion">Fashion</option>
            <option value="kuliner">Kuliner</option>
            <option value="kerajinan">Kerajinan</option>
            <option value="digital">Digital</option>
            <option value="aksesoris">Aksesoris</option>
            <option value="lainnya">Lainnya</option>
          </select>
          <select v-model="filterSort" class="ap__select">
            <option value="newest">Terbaru</option>
            <option value="oldest">Terlama</option>
          </select>
          <select v-model="filterBusiness" class="ap__select">
            <option value="">Semua Toko</option>
            <option v-for="store in storesGrouped" :key="store.store" :value="store.products[0]?.business?.id">{{ store.store }}</option>
          </select>
          <button class="ap__btn ap__btn--ghost" @click="fetchProducts">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><polyline points="23 4 23 10 17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><polyline points="1 20 1 14 7 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            Segarkan
          </button>
        </div>

        <!-- Skeleton -->
        <div style="padding:0 22px 20px;display:flex;flex-direction:column;gap:8px;" v-if="loading">
          <div v-for="n in 5" :key="n" class="skeleton" style="height:72px;"></div>
        </div>

        <template v-else>
          <div class="ap__th mk__grid">
            <span>Produk</span>
            <span>Toko / Seller</span>
            <span>Tanggal</span>
            <span>Status</span>
            <span>Aksi</span>
          </div>

          <div class="ap__tr mk__grid" v-for="p in paginated" :key="p.id">
            <!-- Produk -->
            <div class="mk__product">
              <div class="mk__img" :style="`background:${color(p.business?.id || p.id)}`">
                <img v-if="p.image || p.business?.logo" :src="normalizeImageUrl(p.image || p.business?.logo, null)" :alt="p.name" style="width:100%;height:100%;object-fit:cover;border-radius:8px;"/>
                <span v-else>{{ p.name?.[0] || 'P' }}</span>
              </div>
              <div>
                <p class="mk__prod-name">{{ p.name }}</p>
                <p class="mk__prod-sub">{{ p.total_sold || 0 }} terjual · {{ p.reviews_avg_rating ? p.reviews_avg_rating.toFixed(1) : '0.0' }} </p>
              </div>
            </div>
            <!-- Toko -->
            <div class="mk__seller">
              <div class="mk__s-avatar" :style="`background:${color(p.business?.id || p.id)}`">
                <img v-if="p.business?.logo" :src="normalizeImageUrl(p.business.logo, null)" :alt="p.business.name" style="width:100%;height:100%;object-fit:cover;border-radius:50%;"/>
                <span v-else>{{ p.business?.name?.[0] || 'S' }}</span>
              </div>
              <span>{{ p.business?.name || 'Belum ditentukan' }}</span>
            </div>
            <!-- Tanggal -->
            <span class="mk__kat">{{ p.created_at ? new Date(p.created_at).toLocaleDateString('id-ID') : '-' }}</span>
            <!-- Status -->
            <span class="ap__badge" :class="statusBadge(p.status)">{{ statusLabel[p.status] || 'Aktif' }}</span>
            <!-- Aksi -->
            <div class="mk__actions">
              <button class="mk__btn mk__btn--del" @click="remove(p)" title="Hapus Produk">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M19 6l-1 14H6L5 6M10 11v6M14 11v6M9 6V4h6v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Hapus
              </button>
            </div>
          </div>

          <div class="empty-state" v-if="!filtered.length">
            <span>∅</span>
            <p>Tidak ada produk ditemukan</p>
          </div>
        </template>

        <div class="ap__pagination" v-if="mode === 'products' && totalPages > 1">
          <span class="ap__pagination-info">{{ (page - 1) * perPage + 1 }}–{{ Math.min(page * perPage, filtered.length) }} dari {{ filtered.length }}</span>
          <div class="ap__pagination-btns">
            <button class="ap__page-btn" :disabled="page === 1" @click="page--">‹</button>
            <button v-for="pp in totalPages" :key="pp" class="ap__page-btn" :class="{ 'ap__page-btn--active': page === pp }" @click="page = pp">{{ pp }}</button>
            <button class="ap__page-btn" :disabled="page === totalPages" @click="page++">›</button>
          </div>
        </div>
      </div>
    </template>

    <!-- ── BLOGS MODE ── -->
    <template v-if="mode === 'blogs'">
      <div class="ap__card">
        <div class="ap__toolbar" style="padding: 16px 22px 0;">
          <div class="ap__search">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            <input v-model="q" placeholder="Cari judul artikel atau penulis..."/>
          </div>
          <button class="ap__btn ap__btn--ghost" @click="fetchBlogs">Segarkan</button>
        </div>
        <div class="ap__card-header">
          <div>
            <h3 class="ap__card-title"> Moderasi Blog</h3>
            <p style="font-size:.8rem;color:#9ca3af;margin-top:2px;">Tinjau artikel yang dikirimkan seller. Anda dapat melihat atau menghapus.</p>
          </div>
          <span class="ap__badge ap__badge--blue">{{ filteredBlogs.length }} artikel</span>
        </div>

        <div v-if="!filteredBlogs.length" class="empty-state" style="margin:0 0 8px">
          <span></span>
          <p>{{ q.trim() ? 'Tidak ada artikel ditemukan' : 'Tidak ada blog untuk dimoderasi' }}</p>
        </div>

        <div v-else class="mk__blog-list">
          <div v-for="b in filteredBlogs" :key="b.id" class="mk__blog-card">
            <div class="mk__blog-meta">
              <span class="ap__badge ap__badge--blue">Blog</span>
              <span class="mk__kat">{{ b.created_at ? new Date(b.created_at).toLocaleDateString('id-ID') : '-' }}</span>
            </div>
            <h4 class="mk__blog-title">{{ b.title || b.judul || 'Tanpa judul' }}</h4>
            <p class="mk__blog-author">oleh <strong>{{ b.user_name || b.author_name || b.user?.name || 'Unknown' }}</strong></p>
            <p class="mk__blog-excerpt">{{ (b.excerpt || b.content || '').slice(0, 200) }}{{ (b.excerpt || b.content || '').length > 200 ? '…' : '' }}</p>
            <div class="mk__blog-actions">
              <button class="ap__btn ap__btn--ghost" style="padding:8px 16px;font-size:.8rem;" @click="viewBlog(b)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><polyline points="15 3 21 3 21 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="10" y1="14" x2="21" y2="3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Lihat
              </button>
              <button class="mk__btn mk__btn--del" style="padding:8px 16px;font-size:.8rem;width:auto;height:auto;" @click="removeBlog(b)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M19 6l-1 14H6L5 6M10 11v6M14 11v6M9 6V4h6v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Hapus
              </button>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Detail modal -->
    <teleport to="body">
      <div class="modal-bg" v-if="detail.open" @click.self="detail.open = false">
        <div class="modal-box mk__detail-modal">
          <button class="mk__modal-close" @click="detail.open = false" aria-label="Tutup">×</button>
          <div class="mk__detail-img" :style="`background:${detail.p?.gradient}`">
            <span></span>
          </div>
          <h3 class="mk__detail-name">{{ detail.p?.name }}</h3>
          <p class="mk__detail-price">Rp {{ detail.p?.price?.toLocaleString('id-ID') }}</p>
          <div class="mk__detail-meta">
            <span class="ap__badge" :class="statusBadge(detail.p?.status)">{{ statusLabel[detail.p?.status] }}</span>
          </div>
          <p class="mk__detail-desc">{{ detail.p?.description }}</p>
          <div class="mk__detail-stats">
            <div><strong>{{ detail.p?.terjual }}</strong><span>Terjual</span></div>
            <div><strong>{{ detail.p?.rating }} </strong><span>Rating</span></div>
            <div><strong>{{ detail.p?.seller_name }}</strong><span>Seller</span></div>
          </div>
          <div class="mk__detail-btns">
            <button class="ap__btn ap__btn--ghost" @click="detail.open = false">Tutup</button>
            <button class="ap__btn ap__btn--primary" @click="remove(detail.p); detail.open = false">Hapus Produk</button>
          </div>
        </div>
      </div>
    </teleport>

    <div class="ap__toast" :class="{ 'ap__toast--show': toast.show }">{{ toast.msg }}</div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted, reactive, watch } from 'vue'
import api from '@/api/axios'
import { normalizeImageUrl } from '@/utils/image'

export default {
  name: 'AdminKonten',
  setup() {
    const loading = ref(true)
    const q = ref('')
    const filterKat = ref('')
    const filterBusiness = ref('')
    const filterSort = ref('newest')
    const activeTab = ref('all')
    const mode = ref('products')
    const page = ref(1)
    const perPage = 10
    const products = ref([])
    const blogs = ref([])
    const detail = reactive({ open: false, p: null })
    const toast = reactive({ show: false, msg: '' })

    let pollInterval = null

    const statusLabel = { active: 'Aktif', blocked: 'Diblokir', removed: 'Dihapus' }
    const statusBadge = s => ({ active: 'ap__badge--green', blocked: 'ap__badge--yellow', removed: 'ap__badge--red' })[s] || 'ap__badge--gray'
    const colors_ = ['linear-gradient(135deg,#f43f5e,#e11d48)', 'linear-gradient(135deg,#6366f1,#4f46e5)', 'linear-gradient(135deg,#10b981,#059669)', 'linear-gradient(135deg,#f59e0b,#d97706)', 'linear-gradient(135deg,#0ea5e9,#0284c7)']
    const color = id => colors_[id % colors_.length]

    const uniqueProducts = computed(() => [...new Map(products.value.map(p => [p.id, p])).values()])

    const tabs = computed(() => [
      { id: 'all', icon: '', label: 'Semua', count: uniqueProducts.value.filter(p => p.status !== 'removed').length },
      { id: 'active', icon: '', label: 'Aktif', count: uniqueProducts.value.filter(p => p.status === 'active').length },
      { id: 'removed', icon: '️', label: 'Dihapus', count: uniqueProducts.value.filter(p => p.status === 'removed').length },
    ])

    const storesGrouped = computed(() => {
      const map = {}
      uniqueProducts.value.forEach(p => {
        const store = p.business?.name || 'Unknown'
        if (!map[store]) map[store] = { store, products: [] }
        map[store].products.push(p)
      })
      return Object.values(map)
    })

    const productCategorySlug = (p) => {
      if (typeof p.category === 'string') return p.category
      return p.category?.slug || p.business?.category || ''
    }

    const filtered = computed(() => {
      let l = [...uniqueProducts.value]
      if (activeTab.value === 'all') l = l.filter(p => p.status !== 'removed')
      else l = l.filter(p => p.status === activeTab.value)
      if (filterKat.value) {
        l = l.filter(p => productCategorySlug(p) === filterKat.value)
      }
      if (filterBusiness.value) {
        l = l.filter(p => String(p.business?.id) === String(filterBusiness.value))
      }
      if (q.value.trim()) {
        const s = q.value.toLowerCase()
        l = l.filter(p =>
          (p.name || '').toLowerCase().includes(s) ||
          (p.business?.name || '').toLowerCase().includes(s)
        )
      }
      if (filterSort.value === 'newest') l.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
      else if (filterSort.value === 'oldest') l.sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
      return l
    })
    const totalPages = computed(() => Math.max(1, Math.ceil(filtered.value.length / perPage)))
    const paginated = computed(() => filtered.value.slice((page.value - 1) * perPage, page.value * perPage))

    const filteredBlogs = computed(() => {
      let list = [...blogs.value]
      if (q.value.trim()) {
        const s = q.value.toLowerCase()
        list = list.filter((b) => {
          const title = (b.title || b.judul || '').toLowerCase()
          const author = (b.user_name || b.author_name || b.user?.name || '').toLowerCase()
          const excerpt = (b.excerpt || b.content || '').toLowerCase()
          return title.includes(s) || author.includes(s) || excerpt.includes(s)
        })
      }
      return list
    })

    const showToast = msg => { toast.msg = msg; toast.show = true; setTimeout(() => toast.show = false, 3000) }

    const remove = async p => {
      if (!confirm(`Hapus produk "${p.name}"?`)) return
      try {
        await api.delete(`/admin/products/${p.id}`)
        products.value = products.value.filter(x => x.id !== p.id)
        showToast(`️ "${p.name}" dihapus`)
      } catch (e) {
        console.error(e); showToast('Gagal menghapus produk')
      }
    }

    const buildProductParams = () => {
      const params = { per_page: 200, sort: filterSort.value }
      if (activeTab.value === 'all') params.status = 'all'
      else params.status = activeTab.value
      if (filterKat.value) params.category = filterKat.value
      if (filterBusiness.value) params.business_id = filterBusiness.value
      if (q.value.trim()) params.search = q.value.trim()
      return params
    }

    const fetchProducts = async () => {
      loading.value = true
      try {
        const r = await api.get('/admin/products', { params: buildProductParams() })
        let payload = r.data?.data ?? r.data ?? []
        if (!Array.isArray(payload) && payload?.data) payload = payload.data
        if (!Array.isArray(payload)) payload = []
        products.value = payload.map(product => ({
          ...product,
          status: product.status || 'active',
          reviews_avg_rating: product.reviews_avg_rating || product.rating || 0,
          total_sold: product.total_sold || product.terjual || 0
        }))
      } catch (e) {
        console.error(e); products.value = []
      } finally { loading.value = false }
    }

    let searchDebounce = null
    watch([activeTab, filterKat, filterBusiness, filterSort], () => {
      page.value = 1
      fetchProducts()
    })
    watch(q, () => {
      page.value = 1
      clearTimeout(searchDebounce)
      searchDebounce = setTimeout(() => fetchProducts(), 350)
    })

    const fetchBlogs = async () => {
      try {
        const r = await api.get('/admin/blogs')
        let payload = r.data?.data ?? r.data ?? []
        if (!Array.isArray(payload)) {
          const r2 = await api.get('/blogs')
          payload = r2.data?.data ?? r2.data ?? []
        }
        blogs.value = Array.isArray(payload) ? payload : []
      } catch (e) {
        console.error(e); blogs.value = []
      }
    }

    const viewBlog = b => {
      const url = b.url || (b.slug ? `/blog/${b.slug}` : `/blogs/${b.id}`)
      window.open(url, '_blank')
    }

    const removeBlog = async b => {
      if (!confirm(`Hapus artikel "${b.title || b.judul || 'tanpa judul'}"?`)) return
      try {
        await api.delete(`/admin/blogs/${b.id}`)
        blogs.value = blogs.value.filter(x => x.id !== b.id)
        showToast('️ Blog dihapus')
      } catch (e) {
        console.error(e); showToast('Gagal menghapus blog')
      }
    }

    onMounted(async () => {
      await Promise.all([fetchProducts(), fetchBlogs()])
      pollInterval = setInterval(() => { fetchProducts(); fetchBlogs() }, 30000)
    })
    onUnmounted(() => clearInterval(pollInterval))

    return {
      loading, q, filterKat, filterBusiness, filterSort, activeTab, mode,
      page, perPage, products, filtered, paginated, totalPages, detail, toast,
      tabs, statusLabel, statusBadge, color, remove, blogs, filteredBlogs,
      storesGrouped, viewBlog, removeBlog, fetchProducts, fetchBlogs,
      normalizeImageUrl,
    }
  }
}
</script>

<style scoped>
/* ── Mode switcher (in header) ──────────────────────────────────── */
.mk__mode-switcher {
  display: flex;
  background: #f3f4f6;
  border-radius: 12px;
  padding: 4px;
  gap: 2px;
}
.mk__mode-btn {
  display: flex; align-items: center; gap: 7px;
  padding: 9px 18px; border: none; border-radius: 9px;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: .83rem; font-weight: 600; color: #6b7280;
  background: transparent; cursor: pointer; transition: all .2s;
}
.mk__mode-btn--active {
  background: #fff; color: #111827;
  box-shadow: 0 2px 8px rgba(0,0,0,.08);
}
.mk__mode-count {
  background: #e53e3e; color: #fff;
  font-size: .62rem; font-weight: 800;
  padding: 2px 6px; border-radius: 999px;
}

/* ── Status tabs ─────────────────────────────────────────────────── */
.mk__tabs-row { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
.mk__tab { display: flex; align-items: center; gap: 7px; padding: 9px 18px; border: 1.5px solid #e5e7eb; border-radius: 10px; background: #fff; font-family: 'Plus Jakarta Sans', sans-serif; font-size: .83rem; font-weight: 600; color: #6b7280; cursor: pointer; transition: all .2s; }
.mk__tab:hover { border-color: #fca5a5; color: #e53e3e; background: #fff5f5; }
.mk__tab--active { border-color: #e53e3e; color: #e53e3e; background: #fff5f5; }
.mk__tab-count { padding: 2px 8px; border-radius: 100px; font-size: .68rem; background: #e5e7eb; color: #6b7280; }
.mk__tab--active .mk__tab-count { background: #fecaca; color: #c53030; }

/* ── Product table ───────────────────────────────────────────────── */
.mk__grid { grid-template-columns: 2.5fr 1.5fr 1fr 1fr 100px; gap: 12px; }
.mk__product { display: flex; align-items: center; gap: 10px; min-width: 0; }
.mk__img { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
.mk__prod-name { font-size: .84rem; font-weight: 700; color: #111827; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.mk__prod-sub { font-size: .7rem; color: #9ca3af; }
.mk__seller { display: flex; align-items: center; gap: 7px; }
.mk__s-avatar { width: 26px; height: 26px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: #fff; font-size: .65rem; font-weight: 800; }
.mk__seller span { font-size: .8rem; color: #374151; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.mk__kat { font-size: .75rem; color: #6b7280; }
.mk__actions { display: flex; gap: 4px; }
.mk__btn { display: inline-flex; align-items: center; gap: 5px; padding: 0; width: 30px; height: 30px; border: none; border-radius: 8px; cursor: pointer; font-size: .75rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 600; transition: all .2s; justify-content: center; }
.mk__btn--del { background: #fff5f5; color: #e53e3e; }
.mk__btn--del:hover { background: #e53e3e; color: #fff; }

/* ── Blog list ───────────────────────────────────────────────────── */
.mk__blog-list { display: flex; flex-direction: column; gap: 0; }
.mk__blog-card { padding: 20px 28px; border-bottom: 1px solid #f3f4f6; transition: background .15s; }
.mk__blog-card:hover { background: #fafafa; }
.mk__blog-card:last-child { border-bottom: none; }
.mk__blog-meta { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
.mk__blog-title { font-size: .95rem; font-weight: 800; color: #111827; margin: 0 0 4px; }
.mk__blog-author { font-size: .78rem; color: #9ca3af; margin: 0 0 8px; }
.mk__blog-excerpt { font-size: .84rem; color: #6b7280; line-height: 1.6; margin: 0 0 14px; }
.mk__blog-actions { display: flex; gap: 8px; }

/* ── Detail modal ────────────────────────────────────────────────── */
.mk__detail-modal { max-width: 440px; text-align: center; }
.mk__modal-close { position: absolute; top: 16px; right: 16px; width: 30px; height: 30px; border: none; background: #f3f4f6; border-radius: 8px; cursor: pointer; font-size: .85rem; color: #6b7280; }
.mk__detail-img { width: 100%; height: 120px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 3.5rem; margin-bottom: 16px; background: #f3f4f6; }
.mk__detail-name { font-family: 'Fraunces', serif; font-size: 1.3rem; font-weight: 900; color: #111827; margin-bottom: 6px; }
.mk__detail-price { font-size: 1rem; font-weight: 800; color: #e53e3e; margin-bottom: 12px; }
.mk__detail-meta { display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 12px; }
.mk__detail-desc { font-size: .84rem; color: #6b7280; line-height: 1.7; margin-bottom: 16px; }
.mk__detail-stats { display: flex; justify-content: center; gap: 28px; padding: 14px; background: #f9fafb; border-radius: 12px; margin-bottom: 20px; }
.mk__detail-stats strong { display: block; font-size: 1rem; font-weight: 800; color: #111827; text-align: center; }
.mk__detail-stats span { font-size: .7rem; color: #9ca3af; }
.mk__detail-btns { display: flex; gap: 8px; justify-content: center; }
</style>