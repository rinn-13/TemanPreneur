<template>
  <div class="blog-page">
    <AbstractBackground :intensity="1.1" />

    <!-- ── PAGE HEADER ── -->
    <PageHeader title="Inspirasi & Ilmu Wirausaha" subtitle="Bagikan pengalamanmu dan belajar dari mereka yang sudah sukses membangun bisnis di sekolah">
      <template #actions>
        <div class="blog-search-bar">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          <input v-model="searchQ" type="text" placeholder="Cari artikel..."/>
          <button v-if="searchQ" @click="searchQ=''" class="blog-search__clear" type="button">Bersihkan</button>
        </div>
        <router-link to="/blog/tulis" class="blog-btn blog-btn--primary">Tulis Artikel</router-link>
      </template>
    </PageHeader>

    <!-- ── BODY ── -->
    <div class="blog-body">

      <!-- Loading skeleton -->
      <div class="blog-grid" v-if="loading">
        <div v-for="n in 6" :key="n" class="blog-skeleton"></div>
      </div>

      <!-- Empty -->
      <div class="blog-empty" v-else-if="!paginated.length">
        <span>∅</span>
        <p>Tidak ada artikel yang cocok</p>
        <button class="blog-btn blog-btn--outline" @click="resetFilter">Reset Filter</button>
      </div>

      <!-- Grid artikel -->
      <div class="blog-grid" v-else>
        <div
          v-for="art in paginated"
          :key="art.id"
          class="art-card tp-card-equal"
          @click="$router.push({ name: 'blog.detail', params: { slug: art.slug } })"
        >
         <div class="art-card__img tp-card-equal__media">
            <img
              v-if="art.image"
              :src="getImageUrl(art.image)"
              class="art-cover tp-img-cover"
              :alt="art.title"
              loading="lazy"
              @error="$onImageError($event)"
            />
            <div v-else class="art-card__img-fallback">📝</div>
            <!-- Category tag di atas gambar -->

          </div>

          <!-- Body teks -->
          <div class="art-card__body tp-card-equal__body">
            <!-- Meta: author + tanggal -->
            <div class="art-meta">
              <div class="art-author">
                <div class="art-author__avatar" :style="`background:${art.authorColor}`">{{ art.author?.[0] }}</div>
                <span>{{ art.author }}</span>
              </div>
              <span class="art-date">{{ art.date }}</span>
            </div>

            <h3 class="art-title">{{ art.title }}</h3>
            <p class="art-excerpt">{{ art.excerpt }}</p>

            <button class="art-read-more" @click.stop="$router.push({ name: 'blog.detail', params: { slug: art.slug } })">
              Baca Selengkapnya
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="blog-pagination" v-if="totalPages > 1">
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
import { categoryService } from '@/services/category.js'
import { normalizeImageUrl } from '@/utils/image'
import AbstractBackground from '@/components/AbstractBackground.vue'

export default {
  components: { AbstractBackground },
  name: 'BlogPage',
  setup() {
    const route    = useRoute()
    const router   = useRouter()
    const loading  = ref(true)
    const articles = ref([])
    const searchQ  = ref('')
    const activeCat = ref('')
    const page      = ref(1)
    const perPage   = 6
    const categories = ref([
      { id: 'semua', icon: '', nama: 'Semua' }
    ])

    const categoryIcons = {
      fashion: '',
      kuliner: '',
      kerajinan: '',
      digital: '',
      aksesoris: '',
      lainnya: '',
    }

    const catLabel = (slug) => {
      if (!slug) return 'Semua'
      const category = categories.value.find((c) => c.id === slug)
      return category?.nama || slug
    }

    const filtered = computed(() => {
      let l = [...articles.value]
      if (activeCat.value && activeCat.value !== 'semua') {
        l = l.filter(a => (a.category || '').toString().toLowerCase() === activeCat.value)
      }
      if (searchQ.value.trim()) {
        const s = searchQ.value.toLowerCase()
        l = l.filter(a => a.title.toLowerCase().includes(s) || a.excerpt.toLowerCase().includes(s) || a.author.toLowerCase().includes(s))
      }
      return l
    })

    const totalPages = computed(() => Math.max(1, Math.ceil(filtered.value.length / perPage)))
    const paginated  = computed(() => filtered.value.slice((page.value-1)*perPage, page.value*perPage))

    const updateBlogQuery = () => {
      const query = {}
      if (activeCat.value && activeCat.value !== 'semua') query.cat = activeCat.value
      if (searchQ.value.trim()) query.q = searchQ.value.trim()
      router.replace({ name: 'blog', query })
    }

    const setCategory = (catId) => {
      activeCat.value = activeCat.value === catId ? '' : catId
      page.value = 1
      updateBlogQuery()
    }

    const resetFilter = () => { activeCat.value=''; searchQ.value=''; page.value=1; updateBlogQuery() }
    const scrollTop   = () => window.scrollTo({ top:0, behavior:'smooth' })

    watch(() => route.query, q => {
      activeCat.value = q.cat ? q.cat : ''
      searchQ.value = q.q ? q.q : ''
    }, { immediate: true })

    watch([activeCat, searchQ], () => { page.value = 1; updateBlogQuery() })

    const loadCategories = async () => {
      const result = await categoryService.getCategories()
      if (result.success) {
        categories.value = [
          { id: 'semua', icon: '', nama: 'Semua' },
          ...result.data.map((cat) => ({
            id: cat.slug,
            icon: categoryIcons[cat.slug] || '',
            nama: cat.name,
          })),
        ]
      }
    }

    const fetchBlogs = async () => {
      loading.value = true
      const { blogService } = await import('@/services/blog.js')
      const result = await blogService.getBlogs({ 
        category: activeCat.value === 'semua' ? null : activeCat.value,
        search: searchQ.value.trim() || undefined,
        page: page.value,
      })
      articles.value = result.data || []
      loading.value = false
    }

    watch([activeCat, searchQ, page], fetchBlogs)
    onMounted(async () => {
      await loadCategories()
      await fetchBlogs()
    })
    const getImageUrl = (path) => normalizeImageUrl(path, '/placeholder.png')

    return { loading, filtered, paginated, totalPages, page, searchQ, activeCat, categories, catLabel, setCategory, resetFilter, scrollTop, getImageUrl }
  }
}
</script>

<style scoped>
/* ─── Base ─────────────────────────────────── */
.blog-page {
  min-height: 100vh;
  background: #ffffff;
  font-family: 'Plus Jakarta Sans', sans-serif;
  padding-top: 24px;
}

/* ─── Header ────────────────────────────────── */
/* ─── Page Header Actions ────────────────────── */
.blog-search-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  height: 38px;
  padding: 0 12px;
  background: #f9fafb;
  border: 1.5px solid #e5e7eb;
  border-radius: 8px;
  color: #9ca3af;
  min-width: 200px;
  transition: border-color .18s;
}
.blog-search-bar:focus-within { border-color: #e53e3e; background: #fff; }
.blog-search-bar input {
  flex: 1;
  border: none;
  outline: none;
  background: transparent;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: .83rem;
  color: #111827;
}
.blog-search__clear {
  background: none;
  border: none;
  cursor: pointer;
  color: #9ca3af;
  font-size: .75rem;
  line-height: 1;
}
.blog-btn {
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
.blog-btn--primary {
  background: #e53e3e;
  color: #fff;
  box-shadow: 0 2px 10px rgba(229,62,62,.28);
}
.blog-btn--primary:hover { background: #c53030; transform: translateY(-1px); }
.blog-btn--outline {
  background: #fff;
  color: #374151;
  border: 1.5px solid #d1d5db;
}
.blog-btn--outline:hover { border-color: #e53e3e; color: #e53e3e; }

/* ─── Tabs ──────────────────────────────────── */
.blog-tabs {
  background: #fff;
  border-bottom: 1.5px solid #f3f4f6;
  position: sticky;
  top: 64px;
  z-index: 10;
}
.blog-tabs__inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 28px;
  display: flex;
  gap: 0;
  overflow-x: auto;
  scrollbar-width: none;
  -webkit-overflow-scrolling: touch;
}
.blog-tabs__inner::-webkit-scrollbar { display: none; }
.blog-tab {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 14px 18px;
  border: none;
  background: transparent;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: .82rem;
  font-weight: 500;
  color: #6b7280;
  cursor: pointer;
  white-space: nowrap;
  border-bottom: 2.5px solid transparent;
  transition: all .15s;
}
.blog-tab:hover { color: #111827; }
.blog-tab--active {
  color: #e53e3e;
  border-bottom-color: #e53e3e;
  font-weight: 700;
}

/* ─── Body ──────────────────────────────────── */
.blog-body {
  max-width: var(--tp-container-max, 1280px);
  margin: 0 auto;
  padding: 32px var(--tp-container-pad, 28px) 72px;
}

/* ─── Grid ──────────────────────────────────── */
.blog-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: var(--tp-grid-gap, 20px);
  align-items: stretch;
  margin-bottom: 36px;
}
.blog-skeleton {
  height: 320px;
  border-radius: 12px;
  background: linear-gradient(90deg, #f3f4f6 25%, #e9eaec 50%, #f3f4f6 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
}
@keyframes shimmer {
  0%   { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* ─── Article card ──────────────────────────── */
.art-card {
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
.art-card__img-fallback {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
}
.art-card:hover {
  box-shadow: 0 6px 24px rgba(0,0,0,.09);
  transform: translateY(-3px);
}

/* image */
.art-card__img {
  width: 100%;
  aspect-ratio: 16 / 10;
  min-height: 160px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
  background: #f3f4f6;
}
.art-card__emoji {
  font-size: 3.5rem;
  user-select: none;
  filter: drop-shadow(0 2px 8px rgba(0,0,0,.4));
}
.art-cat-tag {
  position: absolute;
  top: 10px;
  right: 10px;
  padding: 3px 9px;
  background: rgba(255,255,255,.15);
  border: 1px solid rgba(255,255,255,.25);
  border-radius: 100px;
  font-size: .62rem;
  font-weight: 700;
  color: rgba(255,255,255,.9);
  backdrop-filter: blur(4px);
  letter-spacing: .03em;
}

/* body text */
.art-card__body {
  padding: 14px 16px 16px;
  flex: 1;
  display: flex;
  flex-direction: column;
}
.art-read-more { margin-top: auto; }

.art-cover {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.art-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 9px;
}
.art-author {
  display: flex;
  align-items: center;
  gap: 6px;
}
.art-author__avatar {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: .55rem;
  font-weight: 800;
}
.art-author span {
  font-size: .75rem;
  font-weight: 600;
  color: #374151;
}
.art-date {
  font-size: .72rem;
  color: #9ca3af;
}
.art-title {
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: .92rem;
  font-weight: 800;
  color: #111827;
  line-height: 1.4;
  margin-bottom: 6px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.art-excerpt {
  font-size: .78rem;
  color: #6b7280;
  line-height: 1.65;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  margin-bottom: 12px;
}
.art-read-more {
  background: none;
  border: none;
  color: #e53e3e;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: .78rem;
  font-weight: 700;
  cursor: pointer;
  padding: 0;
  transition: color .15s;
}
.art-read-more:hover { color: #c53030; text-decoration: underline; }

/* ─── Pagination ────────────────────────────── */
.blog-pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
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
.blog-empty {
  text-align: center;
  padding: 72px 24px;
}
.blog-empty span { font-size: 3rem; display: block; margin-bottom: 12px; }
.blog-empty p { font-size: .9rem; color: #9ca3af; margin-bottom: 18px; }

/* ─── Responsive ────────────────────────────── */
@media (max-width: 900px) {
  .blog-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {

  .blog-body { padding: 20px 14px 56px; }
  .blog-grid { grid-template-columns: 1fr; gap: 14px; }
  .blog-tabs__inner { padding: 0 14px; }
}
</style>