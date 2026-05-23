<template>
  <div class="home">

    <!-- ══════════════════════════════
         ANIMATED ABSTRACT BACKGROUND
    ══════════════════════════════ -->
    <div class="bg-canvas" aria-hidden="true">
      <canvas ref="bgCanvas" class="bg-canvas__el"></canvas>
    </div>

    <!-- ══════════════════════════════
         USER IDENTITY BAR (hanya jika login)
    ══════════════════════════════ -->

    <!-- ══════════════════════════════
         HERO
    ══════════════════════════════ -->
    <section class="hero reveal" data-reveal>
      <div class="hero__container">
        <div class="hero__content">
          <h1 class="hero__title">
            <span class="hero__title-dark">WUJUDKAN JIWA</span>
            <span class="hero__title-red">BISNISMU</span>
            <span class="hero__title-dark">SEJAK DI BANGKU</span>
            <span class="hero__title-red">SEKOLAH</span>
          </h1>
          <p class="hero__desc">
            Platform internal sekolah untuk jual beli produk kreatif karya siswa.
            Bangun tokomu, kelola pesanan, dan jadilah entrepreneur muda sukses!
          </p>
          <div class="hero__actions">
            <router-link to="/katalog" class="hero__btn hero__btn--primary">Jelajahi</router-link>
            <router-link to="/register" class="hero__btn hero__btn--ghost">Buka Toko</router-link>
          </div>
        </div>
        <div class="hero__visual">
          <div class="hero__promo-card">
            <div class="hero__promo-slides">
              <div
                v-for="(slide, idx) in promoSlides"
                :key="slide.id || idx"
                class="hero__promo-slide"
                :class="{ 'hero__promo-slide--active': currentSlide === idx }"
              >
                <img
                  :src="slide.image"
                  :alt="`Banner ${idx + 1}`"
                  class="hero__promo-img"
                  loading="eager"
                  @error="onPromoImageError($event, idx)"
                />
              </div>
            </div>
            <div class="hero__dots" v-if="promoSlides && promoSlides.length">
              <button
                v-for="(_, idx) in promoSlides"
                :key="idx"
                class="hero__dot"
                :class="{ 'hero__dot--active': currentSlide === idx }"
                @click="currentSlide = idx"
              ></button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ══════════════════════════════
         KATEGORI
    ══════════════════════════════ -->
    <section class="section reveal" data-reveal>
      <div class="section__container">
        <div class="section__title-row">
          <h2 class="section__title">Kategori</h2>
        </div>
        <div class="kategori__row">
          <button
            v-for="kat in kategori"
            :key="kat.id ?? kat.slug ?? 'semua'"
            class="kategori__item"
            :class="{ 'kategori__item--active': activeFilter === kat.id }"
            @click="setFilter(kat.id)"
          >
            <div class="kategori__circle">
              <span class="kategori__icon">{{ kat.icon }}</span>
            </div>
            <span class="kategori__label">{{ kat.nama }}</span>
          </button>
        </div>
      </div>
    </section>

    <!-- ══════════════════════════════
         PRODUK UNGGULAN
    ══════════════════════════════ -->
    <section class="section section--gray reveal" data-reveal>
      <div class="section__container">
        <div class="section__title-row">
          <h2 class="section__title">Produk <span>Unggulan</span></h2>
          <router-link to="/katalog" class="section__link">Lihat semua &rarr;</router-link>
        </div>
        <div v-if="loadingProduk" class="produk__grid">
          <div v-for="n in 6" :key="n" class="produk__skeleton"></div>
        </div>
        <div v-else-if="errorProduk" class="produk__error"><p>{{ errorProduk }}</p></div>
        <div v-else-if="produkTampil.length" class="produk__grid">
          <div v-for="p in produkTampil" :key="p.id" class="produk__card" @click="goToProduk(p.id)">
            <div class="produk__img-area">
              <img
                :src="p.gambar"
                :alt="p.nama"
                class="produk__img-photo"
                loading="lazy"
                decoding="async"
                @error="onProductImageError($event, p)"
              />
              <span class="produk__badge" v-if="p.terjual > 80">Terlaris</span>
              <button class="produk__wish" @click.stop="toggleWishlist(p.id)" aria-label="Tambah ke wishlist">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                  <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"
                    :stroke="wishlist.includes(p.id) ? '#E53E3E' : 'currentColor'"
                    :fill="wishlist.includes(p.id) ? '#E53E3E' : 'none'"
                    stroke-width="2"/>
                </svg>
              </button>
            </div>
            <div class="produk__info">
              <h3 class="produk__nama">{{ p.nama }}</h3>
              <div class="produk__seller">
                <div class="produk__seller-avatar">
                  <img
                    :src="p.businessLogo || '/avatars/default-seller.svg'"
                    :alt="p.toko"
                    loading="lazy"
                    @error="$onImageError($event, '/avatars/default-seller.svg')"
                  />
                </div>
                <p class="produk__toko">{{ p.toko }}</p>
                <span v-if="p.isPremium" class="produk__premium-badge">&#9733;</span>
              </div>
            </div>
            <div class="produk__bottom">
              <span class="produk__harga">{{ formatRupiah(p.harga) }}</span>
              <span class="produk__rating">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="#F59E0B">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                {{ p.rating }}
              </span>
            </div>
          </div>
        </div>
        <div v-else class="produk__empty">
          <p>Belum ada produk di kategori ini.</p>
        </div>
      </div>
    </section>

    <!-- ══════════════════════════════
         PRODUK TERLAKU — REDESIGNED
    ══════════════════════════════ -->
    <section class="section reveal" data-reveal>
      <div class="section__container">
        <div class="section__title-row">
          <div>
            <p class="section__sublabel">Minggu ini</p>
            <h2 class="section__title">Produk <span>Terlaku</span></h2>
          </div>
          <router-link to="/katalog?sort=terlaris" class="section__link">Lihat semua &rarr;</router-link>
        </div>

        <div v-if="loadingTerlaku" class="terlaku-new__grid">
          <div v-for="n in 3" :key="n" class="terlaku-new__skeleton"></div>
        </div>
        <div v-else-if="errorTerlaku" class="produk__error"><p>{{ errorTerlaku }}</p></div>
        <div v-else class="terlaku-new__grid">
          <div
            v-for="(item, idx) in produkTerlaku.slice(0, 3)"
            :key="item.id"
            class="terlaku-new__card"
            :class="`terlaku-new__card--${idx + 1}`"
            :style="`--card-delay: ${idx * 0.1}s`"
            @click="goToProduk(item.id)"
          >
            <!-- Header berwarna -->
            <div class="terlaku-new__header" :class="{ 'terlaku-new__header--photo': item.gambar }">
              <img
                v-if="item.gambar"
                :src="item.gambar"
                :alt="item.nama"
                class="terlaku-new__header-img"
                loading="lazy"
                @error="onProductImageError($event, item)"
              />
              <div v-else class="terlaku-new__header-bg" :style="item.gradien"></div>
              <span class="terlaku-new__rank-pill">#{{ idx + 1 }} terlaris</span>
              <div v-if="!item.gambar" class="terlaku-new__header-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.9)" stroke-width="1.5">
                  <path v-if="idx===0" d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/>
                  <path v-else-if="idx===1" d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                  <path v-else d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
              </div>
            </div>

            <!-- Body -->
            <div class="terlaku-new__body">
              <span class="terlaku-new__kat">{{ item.kategori }}</span>
              <h4 class="terlaku-new__nama">{{ item.nama }}</h4>
              <div class="terlaku-new__toko">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                <div class="terlaku-new__toko-meta">
                  <span class="terlaku-new__toko-name">{{ item.toko || 'Toko' }}</span>
                  <small class="terlaku-new__toko-sub">
                    {{ item.wirausaha?.nama ? `Usaha: ${item.wirausaha.nama}` : item.wirausaha?.kelas || 'Siswa' }}
                  </small>
                </div>
              </div>
            </div>

            <!-- Progress bar -->
            <div class="terlaku-new__bar-wrap">
              <div class="terlaku-new__bar-label">
                <span>penjualan</span>
                <span>{{ item.terjual || 0 }} terjual</span>
              </div>
              <div class="terlaku-new__bar">
                <div
                  class="terlaku-new__fill"
                  :style="`width: ${Math.min(((item.terjual||0) / (produkTerlaku?.[0]?.terjual||1)) * 100, 100)}%`"
                ></div>
              </div>
            </div>

            <!-- Footer -->
            <div class="terlaku-new__foot">
              <span class="terlaku-new__harga">{{ formatRupiah(item.harga) }}</span>
              <span class="terlaku-new__rating">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="#d97706">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                {{ item.rating || 0 }}
              </span>
            </div>

            <!-- Wirausaha inline -->
            <div class="terlaku-new__seller" @click.stop>
              <div class="terlaku-new__seller-avatar">
                <img
                  :src="item.businessLogo || '/avatars/default-seller.svg'"
                  :alt="item.toko"
                  loading="lazy"
                  @error="$onImageError($event, '/avatars/default-seller.svg')"
                />
              </div>
              <div class="terlaku-new__seller-info">
                <p class="terlaku-new__seller-nama">{{ item.toko || item.wirausaha?.nama || 'Usaha' }}</p>
                <p class="terlaku-new__seller-kelas">
                  {{ item.wirausaha?.nama ? `Usaha: ${item.wirausaha.nama}` : item.wirausaha?.kelas || 'Siswa' }}
                </p>
              </div>
              <router-link
                v-if="item.businessId || item.wirausaha?.businessId || item.wirausaha?.id"
                :to="{ name: 'seller.public', params: { id: item.businessId || item.wirausaha?.businessId || item.wirausaha?.id } }"
                class="terlaku-new__seller-btn"
              >
                Kunjungi &rarr;
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ══════════════════════════════
         SPOTLIGHT SELLER — REDESIGNED
    ══════════════════════════════ -->
    <section class="section section--gray reveal" data-reveal>
      <div class="section__container">
        <div class="section__title-row">
          <div>
            <p class="section__sublabel">Bulan ini</p>
            <h2 class="section__title">Entrepreneur <span>Terbaik</span></h2>
          </div>
        </div>

        <div class="seller-new__grid">
          <div
            v-for="(s, idx) in topSeller.slice(0, 3)"
            :key="s.businessId"
            class="seller-new__card"
            :class="[`seller-new__card--pos${idx + 1}`, { 'seller-new__card--gold': idx === 0 }]"
            :style="`--s-delay: ${idx * 0.12}s`"
          >
            <!-- Accent bar top -->
            <div class="seller-new__accent" :class="`seller-new__accent--${idx + 1}`"></div>

            <!-- Rank number watermark -->
            <div class="seller-new__watermark">{{ idx + 1 }}</div>

            <!-- Avatar + ring -->
            <div class="seller-new__avatar-wrap">
              <div class="seller-new__ring" :class="idx === 0 ? 'seller-new__ring--active' : ''"></div>
              <img
                :src="s.logoUrl"
                :alt="s.nama"
                class="seller-new__avatar seller-new__avatar--img"
                loading="lazy"
                @error="$onImageError($event, '/avatars/default-seller.svg')"
              />
              <div class="seller-new__medal" :class="`seller-new__medal--${idx + 1}`">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/>
                </svg>
              </div>
            </div>

            <!-- Identity -->
            <div class="seller-new__identity">
              <h3 class="seller-new__nama">{{ s.nama }}</h3>
              <p class="seller-new__kelas">{{ s.kelas }}</p>
            </div>

            <!-- Tagline -->
            <p class="seller-new__tagline">"{{ s.tagline }}"</p>

            <!-- Stats row -->
            <div class="seller-new__stats">
              <div class="seller-new__stat">
                <strong>{{ s.totalTerjual }}</strong>
                <span>terjual</span>
              </div>
              <div class="seller-new__stat-divider"></div>
              <div class="seller-new__stat">
                <strong>{{ s.rating }}&#9733;</strong>
                <span>rating</span>
              </div>
            </div>

            <!-- Progress bar -->
            <div class="seller-new__prog-wrap">
              <div class="seller-new__prog-label">
                <span>skor penjualan</span>
                <span>{{ idx === 0 ? '100' : idx === 1 ? '72' : '52' }}%</span>
              </div>
              <div class="seller-new__prog">
                <div
                  class="seller-new__prog-fill"
                  :class="`seller-new__prog-fill--${idx + 1}`"
                  :style="`--target-w: ${idx === 0 ? 100 : idx === 1 ? 72 : 52}%`"
                ></div>
              </div>
            </div>

            <!-- Tags -->
            <div class="seller-new__tags">
              <span v-for="t in s.kategoriJualan" :key="t" class="seller-new__tag">{{ t }}</span>
            </div>

            <!-- CTA -->
            <router-link
              v-if="s.businessId"
              :to="{ name: 'seller.public', params: { id: s.businessId } }"
              class="seller-new__btn"
            >
              Lihat Toko &rarr;
            </router-link>
            <button v-else class="seller-new__btn seller-new__btn--disabled" disabled>
              Toko Tidak Tersedia
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- ══════════════════════════════
         CTA
    ══════════════════════════════ -->
    <section class="cta reveal" data-reveal>
      <div class="cta__inner">
        <h2 class="cta__title">Siap Jadi Entrepreneur Muda?</h2>
        <p class="cta__desc">Daftarkan tokomu dan mulai jual produk kreatifmu ke seluruh teman sekolah!</p>
        <router-link to="/register" class="cta__btn">Mulai Buka Toko &mdash; Gratis!</router-link>
      </div>
    </section>

  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/api/axios'
import { productService } from '@/services/product.js'
import { categoryService } from '@/services/category.js'
import { useAuthStore } from '@/stores/auth'
import {
  normalizeImageUrl,
  onImageError,
  PLACEHOLDER_IMAGE,
  resolveProductImage,
  resolveBusinessLogo,
  pickProductImageSource,
} from '@/utils/image'

export default {
  name: 'HomeView',

  setup() {
    const router = useRouter()
    const route = useRoute()
    const authStore = useAuthStore()
    const bgCanvas = ref(null)

    const loadingProducts = ref(true)
    const loadingBestSelling = ref(true)
    const products = ref([])
    const bestSellingProducts = ref([])
    const wishlist = ref([])
    const categories = ref([])
    const selectedCategory = ref(route.query.kategori || route.query.category_id || null)
    const errorProducts = ref('')
    const errorBestSelling = ref('')
    const currentSlide = ref(0)

    const isLoggedIn = computed(() => authStore.isLoggedIn)
    const user = computed(() => authStore.user)
    const displayRole = computed(() => authStore.role || user.value?.role)
    const businessStatus = computed(() => authStore.businessStatus)

    const categoryIcons = {
      fashion: '👗',
      kuliner: '🍽️',
      kerajinan: '🧵',
      digital: '💻',
      aksesoris: '⌚',
      olahraga: '🏀',
      kecantikan: '💄',
      elektronik: '🔌',
      makanan: '🍪',
      minuman: '🥤',
      lainnya: '📦',
    }

    const kategori = categories
    const activeFilter = selectedCategory
    const produkTampil = computed(() => products.value)
    const loadingProduk = loadingProducts
    const loadingTerlaku = loadingBestSelling
    const errorProduk = errorProducts
    const errorTerlaku = errorBestSelling
    const produkTerlaku = bestSellingProducts

    const roleLabel = computed(() => ({
      admin: 'Administrator', seller: 'Penjual',
      seller_premium: 'Penjual Premium', buyer: 'Pembeli',
    }[displayRole.value] || 'Pengguna'))

    const avatarColor = computed(() => ({
      admin: 'linear-gradient(135deg,#6366f1,#4f46e5)',
      seller: 'linear-gradient(135deg,#f56565,#c53030)',
      seller_premium: 'linear-gradient(135deg,#f59e0b,#d97706)',
      buyer: 'linear-gradient(135deg,#10b981,#059669)',
    }[displayRole.value] || 'linear-gradient(135deg,#9ca3af,#6b7280)'))

    const promoSlidePaths = [
      '1slider (1).png', '1slider (2).png', '1slider (3).png',
    ]
    const promoSlides = ref(
      promoSlidePaths.map((path, i) => ({
        id: `slide-${i}`,
        image: normalizeImageUrl(path),
      }))
    )

    const onPromoImageError = (event, idx) => {
      onImageError(event, PLACEHOLDER_IMAGE)
      if (promoSlides.value[idx]) {
        promoSlides.value[idx].image = PLACEHOLDER_IMAGE
      }
    }

    const onProductImageError = (event, product) => {
      onImageError(event, PLACEHOLDER_IMAGE)
      if (product) product.gambar = PLACEHOLDER_IMAGE
    }

    const normalizeProduct = (p) => {
      const rawImages = Array.isArray(p.images) ? p.images.filter(Boolean) : []
      const mainImage = pickProductImageSource(p) || p.image || p.gambar || rawImages[0] || null
      const categoryLabel = (p.kategori || p.category?.name || p.category?.slug || p.category || '').toString()

      return {
        ...p,
        kategori: categoryLabel || 'lainnya',
        toko: p.toko || p.business?.name || p.seller?.business_name || p.seller?.name || p.store || p.shop || 'Usaha',
        businessLogo: resolveBusinessLogo(
          p.business?.logo || p.seller?.business_logo || p.logo,
          !!(p.business?.is_premium || p.seller?.is_premium)
        ),
        gradien: p.gradient || p.gradien || (p.business?.theme_color ? `linear-gradient(135deg, ${p.business.theme_color}, #00000020)` : 'linear-gradient(135deg,#d1d5db,#9ca3af)'),
        emoji: p.emoji || '',
        harga: p.harga || p.price || 0,
        price: p.harga || p.price || 0,
        rating: typeof p.rating === 'number' ? p.rating : (p.reviews_avg_rating ?? p.rating ?? 0),
        terjual: p.terjual || p.total_sold || p.totalSold || 0,
        nama: p.nama || p.name || 'Produk',
        gambar: resolveProductImage(mainImage),
        businessId: p.business?.id || p.seller?.business_id || p.business_id || null,
        ownerName: p.business?.user?.name || p.seller?.name || '',
        gallery: (p.gallery || rawImages || (p.image ? [p.image] : [])).map((u) => normalizeImageUrl(u)).filter(Boolean),
        isPremium: !!(p.business?.is_premium || p.seller?.is_premium),
        tokoColor: p.business?.theme_color || p.tokoColor || '#e2e8f0',
      }
    }

    const loadCategories = async () => {
      try {
        const result = await categoryService.getCategories()
        const data = result.data || result
        if (Array.isArray(data) && data.length) {
          categories.value = [
            { id: null, icon: '◆', nama: 'Semua' },
            ...data.map((cat) => ({
              id: cat.id,
              slug: cat.slug,
              icon: categoryIcons[cat.slug] || '▫',
              nama: cat.name,
            })),
          ]
        }
      } catch (err) {
        console.error('Failed to load categories for home:', err)
        categories.value = [{ id: null, icon: '◆', nama: 'Semua' }]
      }
    }

    const fetchProducts = async (categoryId = null) => {
      loadingProducts.value = true
      errorProducts.value = ''
      try {
        const params = { sort: 'rating_desc', page: 1 }
        const cat = categories.value.find((c) => c.id === categoryId || c.slug === categoryId)
        if (categoryId != null && categoryId !== '') {
          if (cat?.id && !Number.isNaN(Number(cat.id))) {
            params.category_id = cat.id
          } else if (!Number.isNaN(Number(categoryId))) {
            params.category_id = categoryId
          } else {
            params.category = categoryId
          }
        }
        const result = await productService.getProducts(params)
        const apiData = Array.isArray(result.data) ? result.data : []
        products.value = apiData
          .map(normalizeProduct)
          .sort((a, b) => (b.rating || 0) - (a.rating || 0) || (b.terjual || 0) - (a.terjual || 0))
          .slice(0, 6)
        if (!products.value.length) errorProducts.value = 'Tidak ada produk yang sesuai kategori.'
      } catch (err) {
        errorProducts.value = 'Gagal memuat produk. Periksa koneksi atau server.'
        products.value = []
      } finally {
        loadingProducts.value = false
      }
    }

    const fetchBestSelling = async () => {
      loadingBestSelling.value = true
      errorBestSelling.value = ''
      try {
        const result = await productService.getProducts({ sort: 'terlaris', page: 1 })
        const apiData = Array.isArray(result.data) ? result.data : []
        bestSellingProducts.value = apiData
          .map(normalizeProduct)
          .sort((a, b) => (b.terjual || 0) - (a.terjual || 0))
          .slice(0, 5)
        if (!bestSellingProducts.value.length) errorBestSelling.value = 'Tidak ada produk terlaris dari server.'
      } catch (err) {
        errorBestSelling.value = 'Gagal memuat produk terlaris dari server.'
        bestSellingProducts.value = []
      } finally {
        loadingBestSelling.value = false
      }
    }

    const topSellerList = ref([])
    const fetchTopSellers = async () => {
      try {
        const result = await api.get('/reviews/top-sellers')
        const apiData = Array.isArray(result.data?.data) ? result.data.data : []
        topSellerList.value = apiData.map((seller) => ({
          id: seller.id,
          businessId: seller.id,
          nama: seller.name,
          kelas: seller.user_class || 'Siswa',
          tagline: seller.description
            ? String(seller.description).substring(0, 80)
            : 'Penjual terbaik dari toko lokal.',
          warna: seller.theme_color || '#10b981',
          logoUrl: resolveBusinessLogo(seller.logo, seller.is_premium),
          totalTerjual: seller.total_sales || 0,
          rating: seller.rating || 0,
          ratingCount: seller.rating_count || 0,
          kategoriJualan: (seller.category_labels && seller.category_labels.length
            ? seller.category_labels
            : seller.is_premium
              ? ['Premium']
              : ['UMKM']),
          isPremium: seller.is_premium,
        }))
      } catch (err) {
        console.error('Failed to fetch top sellers:', err)
      }
    }

    watch(selectedCategory, (categoryId) => { fetchProducts(categoryId) }, { immediate: true })
    watch(
      () => route.query.kategori || route.query.category_id,
      (kategori) => {
        if (!kategori) {
          selectedCategory.value = null
          return
        }
        const q = kategori.toString().toLowerCase()
        const match = categories.value.find(
          (c) => String(c.id) === q || (c.slug && c.slug.toLowerCase() === q)
        )
        selectedCategory.value = match ? match.id : kategori
      },
      { immediate: true }
    )

    const topSeller = computed(() => {
      if (topSellerList.value && topSellerList.value.length > 0) return topSellerList.value.slice(0, 3)
      const seen = new Set()
      const rows = []
      for (const product of bestSellingProducts.value) {
        const bid = product.businessId || product.business?.id
        if (!bid || seen.has(bid)) continue
        seen.add(bid)
        rows.push({
          id: product.id, businessId: bid,
          nama: product.toko || product.business?.name || 'Usaha',
          kelas: product.business?.user?.class || product.ownerName || 'Siswa',
          tagline: product.description ? String(product.description).substring(0, 60) : 'Produk terlaris dari toko lokal.',
          warna: product.tokoColor || '#10b981',
          totalTerjual: product.terjual || 0,
          rating: product.rating || 0,
          kategoriJualan: [(product.kategori || 'lainnya').toString().replace(/^(.)/, (m) => m.toUpperCase())],
        })
        if (rows.length >= 3) break
      }
      return rows
    })

    const setFilter = (id) => {
      selectedCategory.value = id
      const query = {}
      if (id) query.kategori = id
      router.replace({ name: 'home', query })
    }

    const toggleWishlist = async (id) => {
      const idx = wishlist.value.indexOf(id)
      if (idx === -1) {
        try { await api.post('/wishlist', { product_id: id }) } catch {}
        wishlist.value.push(id)
      } else {
        try { await api.delete(`/wishlist/${id}`) } catch {}
        wishlist.value.splice(idx, 1)
      }
    }

    const goToProduk = (id) => router.push({ name: 'product-detail', params: { id } })

    const formatRupiah = (n) =>
      new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n)

    /* ── ABSTRACT CANVAS BACKGROUND ── */
    let animFrame = null
    const initCanvas = () => {
      const canvas = bgCanvas.value
      if (!canvas) return
      const ctx = canvas.getContext('2d')
      let W = window.innerWidth
      let H = document.documentElement.scrollHeight
      canvas.width = W
      canvas.height = H

      const resize = () => {
        W = window.innerWidth
        H = document.documentElement.scrollHeight
        canvas.width = W
        canvas.height = H
      }
      window.addEventListener('resize', resize)

      const INTENSITY = 1.55
      const BLOB_COUNT = 7
      const blobs = Array.from({ length: BLOB_COUNT }, (_, i) => ({
        x: Math.random() * W,
        y: Math.random() * H,
        r: (200 + Math.random() * 240) * INTENSITY,
        vx: (Math.random() - 0.5) * 0.42 * INTENSITY,
        vy: (Math.random() - 0.5) * 0.42 * INTENSITY,
        hue: i % 2 === 0 ? 0 : 10,
        alpha: (0.055 + Math.random() * 0.06) * INTENSITY,
      }))

      const LINE_COUNT = 28
      const lines = Array.from({ length: LINE_COUNT }, () => ({
        x1: Math.random() * W,
        y1: Math.random() * H,
        x2: Math.random() * W,
        y2: Math.random() * H,
        vx1: (Math.random() - 0.5) * 0.28,
        vy1: (Math.random() - 0.5) * 0.28,
        vx2: (Math.random() - 0.5) * 0.28,
        vy2: (Math.random() - 0.5) * 0.28,
        alpha: (0.06 + Math.random() * 0.08) * INTENSITY,
      }))

      const DOT_COUNT = 50
      const dots = Array.from({ length: DOT_COUNT }, () => ({
        x: Math.random() * W,
        y: Math.random() * H,
        r: (2 + Math.random() * 3) * INTENSITY,
        vx: (Math.random() - 0.5) * 0.22,
        vy: (Math.random() - 0.5) * 0.22,
        alpha: (0.08 + Math.random() * 0.12) * INTENSITY,
      }))

      const draw = () => {
        ctx.clearRect(0, 0, W, H)

        blobs.forEach(b => {
          b.x += b.vx; b.y += b.vy
          if (b.x < -b.r) b.x = W + b.r
          if (b.x > W + b.r) b.x = -b.r
          if (b.y < -b.r) b.y = H + b.r
          if (b.y > H + b.r) b.y = -b.r
          const g = ctx.createRadialGradient(b.x, b.y, 0, b.x, b.y, b.r)
          g.addColorStop(0, `hsla(${b.hue},78%,48%,${b.alpha})`)
          g.addColorStop(0.5, `hsla(${b.hue},72%,42%,${b.alpha * 0.45})`)
          g.addColorStop(1, `hsla(${b.hue},72%,42%,0)`)
          ctx.beginPath()
          ctx.arc(b.x, b.y, b.r, 0, Math.PI * 2)
          ctx.fillStyle = g
          ctx.fill()
        })

        lines.forEach(l => {
          l.x1 += l.vx1; l.y1 += l.vy1
          l.x2 += l.vx2; l.y2 += l.vy2
          ;[['x1','vx1'],['y1','vy1'],['x2','vx2'],['y2','vy2']].forEach(([pos, vel]) => {
            if (l[pos] < 0 || l[pos] > (pos.includes('x') ? W : H)) l[vel] *= -1
          })
          ctx.beginPath()
          ctx.moveTo(l.x1, l.y1)
          ctx.lineTo(l.x2, l.y2)
          ctx.strokeStyle = `rgba(197,48,48,${l.alpha})`
          ctx.lineWidth = 1.2
          ctx.stroke()
        })

        dots.forEach(d => {
          d.x += d.vx; d.y += d.vy
          if (d.x < 0) d.x = W; if (d.x > W) d.x = 0
          if (d.y < 0) d.y = H; if (d.y > H) d.y = 0
          ctx.beginPath()
          ctx.arc(d.x, d.y, d.r, 0, Math.PI * 2)
          ctx.fillStyle = `rgba(197,48,48,${d.alpha})`
          ctx.fill()
        })

        animFrame = requestAnimationFrame(draw)
      }
      draw()

      return () => window.removeEventListener('resize', resize)
    }

    let revealObserver = null
    const observeReveal = () => {
      const items = document.querySelectorAll('[data-reveal]')
      if (!items.length) return
      revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return
          entry.target.classList.add('is-visible')
          observer.unobserve(entry.target)
        })
      }, { threshold: 0.12 })
      items.forEach((item) => revealObserver.observe(item))
    }

    let slideTimer = null
    const startSlide = () => {
      if (slideTimer) clearInterval(slideTimer)
      slideTimer = setInterval(() => {
        if (!promoSlides.value.length) return
        currentSlide.value = (currentSlide.value + 1) % promoSlides.value.length
      }, 3500)
    }

    onMounted(async () => {
      await Promise.all([loadCategories(), fetchBestSelling(), fetchTopSellers()])
      startSlide()
      observeReveal()
      initCanvas()
    })

    onUnmounted(() => {
      clearInterval(slideTimer)
      if (animFrame) cancelAnimationFrame(animFrame)
      if (revealObserver) revealObserver.disconnect()
    })

    return {
      bgCanvas,
      loadingProducts, loadingBestSelling, errorProducts, errorBestSelling,
      loadingProduk, loadingTerlaku, errorProduk, errorTerlaku,
      isLoggedIn, user, roleLabel, avatarColor,
      products, produkTampil, bestSellingProducts, produkTerlaku,
      categories, kategori, selectedCategory, activeFilter,
      topSeller, wishlist, promoSlides, currentSlide, onPromoImageError, onProductImageError,
      toggleWishlist, goToProduk, formatRupiah, setFilter,
    }
  },
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:wght@700;900&display=swap');

/* ── CANVAS BACKGROUND ── */
.bg-canvas {
  position: fixed;
  inset: 0;
  pointer-events: none;
  z-index: 0;
  overflow: hidden;
}
.bg-canvas__el {
  width: 100%;
  height: 100%;
  display: block;
  opacity: 1;
}

.home {
  padding-top: 8px;
  position: relative;
  z-index: 1;
}

.home > *:not(.bg-canvas) {
  position: relative;
  z-index: 1;
}

/* ── REVEAL ── */
.reveal {
  opacity: 0;
  transform: translateY(24px);
  transition: opacity 0.7s ease, transform 0.7s ease;
}
.reveal.is-visible {
  opacity: 1;
  transform: translateY(0);
}

/* ── IDENTITY BAR ── */
.identity-bar {
  background: linear-gradient(135deg, #1a1a1a 0%, #2d1b1b 100%);
  border-bottom: 2px solid #e53e3e;
  padding: 10px 0;
}
.identity-bar__inner {
  max-width: 1280px; margin: 0 auto; padding: 0 24px;
  display: flex; align-items: center; justify-content: space-between; gap: 16px;
}
.identity-bar__left { display: flex; align-items: center; gap: 12px; }
.identity-bar__avatar {
  width: 44px; height: 44px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 1.1rem; font-weight: 800;
  border: 2px solid rgba(255,255,255,.2);
  box-shadow: 0 3px 12px rgba(0,0,0,.3); flex-shrink: 0;
}
.identity-bar__greeting { font-size: .9rem; color: rgba(255,255,255,.9); font-family: 'Plus Jakarta Sans', sans-serif; margin-bottom: 2px; }
.identity-bar__greeting strong { color: #fff; }
.identity-bar__role { display: flex; align-items: center; gap: 8px; font-size: .78rem; color: rgba(255,255,255,.5); }
.identity-bar__role-badge { padding: 2px 10px; border-radius: 100px; font-size: .72rem; font-weight: 700; }
.identity-bar__role-badge--admin          { background: rgba(99,102,241,.25);  color: #a5b4fc; border: 1px solid rgba(99,102,241,.4); }
.identity-bar__role-badge--seller         { background: rgba(229,62,62,.25);   color: #fca5a5; border: 1px solid rgba(229,62,62,.4); }
.identity-bar__role-badge--seller_premium { background: rgba(245,158,11,.25);  color: #fcd34d; border: 1px solid rgba(245,158,11,.4); }
.identity-bar__role-badge--buyer          { background: rgba(16,185,129,.25);  color: #6ee7b7; border: 1px solid rgba(16,185,129,.4); }
.identity-bar__school { color: rgba(255,255,255,.4); }
.identity-bar__actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.identity-bar__btn {
  padding: 7px 16px; border-radius: 8px;
  font-family: 'Plus Jakarta Sans', sans-serif; font-size: .8rem; font-weight: 700;
  text-decoration: none; transition: all .2s; white-space: nowrap; border: none; cursor: pointer;
}
.identity-bar__btn--outline { background: transparent; color: rgba(255,255,255,.8); border: 1.5px solid rgba(255,255,255,.2); }
.identity-bar__btn--outline:hover { border-color: #fca5a5; color: #fca5a5; }
.identity-bar__btn--primary { background: #e53e3e; color: #fff; box-shadow: 0 2px 8px rgba(229,62,62,.4); }
.identity-bar__btn--primary:hover { background: #c53030; transform: translateY(-1px); }

/* ── HERO ── */
.hero {
  background: linear-gradient(180deg, rgba(255,255,255,0.88) 0%, rgba(243,244,246,0.85) 100%);
  padding: 28px 0 48px;
  position: relative; overflow: hidden;
}
.hero__container {
  max-width: 1280px; margin: 0 auto; padding: 0 24px;
  display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: center;
}
.hero__content { max-width: 560px; }
.hero__title {
  font-family: 'Fraunces', serif;
  font-size: clamp(2rem, 4vw, 3.4rem);
  font-weight: 900; line-height: 1.05;
  color: #111827; margin-bottom: 22px; letter-spacing: -0.04em;
}
.hero__title-dark {
  color: #111827;
}
.hero__title-red {
  display: block;
  color: #dc2626;
}
.hero__desc { font-size: 1rem; color: #4b5563; line-height: 1.85; max-width: 515px; margin-bottom: 32px; }
.hero__actions { display: flex; flex-wrap: wrap; gap: 14px; }
.hero__btn {
  padding: 14px 28px; border-radius: 18px;
  font-family: 'Plus Jakarta Sans',sans-serif; font-size: .95rem; font-weight: 700;
  text-decoration: none; transition: transform .3s ease, box-shadow .3s ease;
  border: none; cursor: pointer;
}
.hero__btn--primary { background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; box-shadow: 0 14px 35px rgba(220,38,38,.18); }
.hero__btn--primary:hover { transform: translateY(-2px); box-shadow: 0 18px 40px rgba(220,38,38,.26); }
.hero__btn--ghost { background: rgba(255,255,255,.94); color: #374151; border: 1.5px solid #e5e7eb; }
.hero__btn--ghost:hover { border-color: #fca5a5; color: #c2410c; transform: translateY(-2px); }
.hero__visual { position: relative; display: flex; justify-content: center; }
.hero__promo-card {
  width: 100%; border-radius: 28px; overflow: hidden;
  box-shadow: 0 24px 60px rgba(15,23,42,.12);
  position: relative; background: rgba(255,255,255,.62);
}
.hero__promo-slides { position: relative; min-height: 320px; }
.hero__promo-slide {
  position: absolute; inset: 0;
  opacity: 0;
  transition: opacity .85s ease-in-out;
  pointer-events: none;
}
.hero__promo-slide--active { opacity: 1; pointer-events: auto; }
.hero__promo-img {
  width: 100%;
  height: 100%;
  min-height: 320px;
  object-fit: cover;
  object-position: center;
  display: block;
}
.hero__dots { position: absolute; bottom: 16px; left: 50%; transform: translateX(-50%); display: flex; gap: 8px; }
.hero__dot { width: 10px; height: 10px; border-radius: 50%; background: rgba(255,255,255,.45); border: none; cursor: pointer; transition: all .25s ease; }
.hero__dot--active { background: #fff; width: 24px; border-radius: 999px; }

/* ── SECTION COMMON ── */
.section { padding: 56px 0; }
.section--gray { background: rgba(243,244,246,0.82); }
.section__container { max-width: 1280px; margin: 0 auto; padding: 0 24px; }
.section__title-row { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 28px; }
.section__sublabel { font-size: 11px; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: #9ca3af; margin-bottom: 4px; }
.section__title { font-family: 'Fraunces', serif; font-size: clamp(1.4rem,2.5vw,1.9rem); font-weight: 800; color: #111827; }
.section__title span { color: #e53e3e; }
.section__link { font-size: .85rem; font-weight: 700; color: #e53e3e; text-decoration: none; }
.section__link:hover { text-decoration: underline; }

/* ── KATEGORI ── */
.kategori__row {
  display: flex; gap: 0; justify-content: space-between;
  border-bottom: 1.5px solid #e5e7eb; padding-bottom: 20px;
}
.kategori__item { display: flex; flex-direction: column; align-items: center; gap: 8px; background: none; border: none; cursor: pointer; padding: 0 12px; transition: transform .2s; }
.kategori__item:hover { transform: translateY(-3px); }
.kategori__circle { width: 64px; height: 64px; border-radius: 50%; background: #d1d5db; display: flex; align-items: center; justify-content: center; transition: background .2s; font-size: 1.6rem; }
.kategori__item:hover .kategori__circle { background: #fecaca; }
.kategori__item--active .kategori__circle { background: #e53e3e; }
.kategori__label { font-size: .75rem; font-weight: 600; color: #6b7280; font-family: 'Plus Jakarta Sans', sans-serif; }
.kategori__item--active .kategori__label { color: #c53030; }

/* ── PRODUK UNGGULAN (unchanged layout) ── */
.produk__grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; }
.produk__skeleton {
  height: 340px; border-radius: 20px;
  background: linear-gradient(90deg,#e5e7eb 25%,#d1d5db 50%,#e5e7eb 75%);
  background-size: 200% 100%; animation: shimmer 1.4s infinite;
}
@keyframes shimmer { 0%{ background-position:200% 0; } 100%{ background-position:-200% 0; } }
.produk__card {
  background: #e5e7eb; border-radius: 20px; overflow: hidden;
  cursor: pointer; transition: transform .2s, box-shadow .2s;
  display: flex; flex-direction: column; border: 1.5px solid #d1d5db;
  min-height: 460px;
}
.produk__card:hover { transform: translateY(-4px); box-shadow: 0 12px 36px rgba(0,0,0,.12); }
.produk__img-area { position: relative; width: 100%; aspect-ratio: 1 / 1; background: #f3f4f6; overflow: hidden; }
.produk__img-photo {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  display: block;
}
.produk__img-rect { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-family: 'Fraunces', serif; font-size: 2.5rem; font-weight: 900; color: rgba(255,255,255,.7); }
.produk__seller-avatar {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
  background: #f3f4f6;
}
.produk__seller-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.produk__badge { position: absolute; top: 10px; left: 10px; background: #e53e3e; color: #fff; font-size: .65rem; font-weight: 700; padding: 3px 9px; border-radius: 100px; }
.produk__wish { position: absolute; top: 10px; right: 10px; width: 30px; height: 30px; background: rgba(255,255,255,.85); border: none; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #9ca3af; transition: color .2s, transform .2s; }
.produk__wish:hover { color: #e53e3e; transform: scale(1.1); }
.produk__info { padding: 14px 16px 8px; flex: 1; display: flex; flex-direction: column; justify-content: space-between; }
.produk__nama { font-size: .9rem; font-weight: 700; color: #111827; margin-bottom: 4px; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.produk__seller { display: flex; align-items: center; gap: 6px; }
.produk__seller-avatar { width: 20px; height: 20px; border-radius: 50%; flex-shrink: 0; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #e5e7eb; }
.produk__seller-avatar img { width: 100%; height: 100%; object-fit: cover; }
.produk__toko { font-size: .75rem; color: #6b7280; font-weight: 500; }
.produk__premium-badge { font-size: 0.6rem; margin-left: 2px; }
.produk__bottom { margin: 0 16px 16px; padding: 8px 12px; background: #c9cdd4; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; }
.produk__harga { font-size: .9rem; font-weight: 800; color: #c53030; }
.produk__rating { display: flex; align-items: center; gap: 3px; font-size: .75rem; font-weight: 700; color: #374151; }
.produk__empty { text-align: center; padding: 48px; color: #9ca3af; }
.produk__error { text-align: center; padding: 32px; color: #e53e3e; }

/* ════════════════════════════════════
   PRODUK TERLAKU — REDESIGNED
════════════════════════════════════ */
.terlaku-new__grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 16px;
}
.terlaku-new__skeleton {
  height: 340px; border-radius: 20px;
  background: linear-gradient(90deg,#e5e7eb 25%,#d1d5db 50%,#e5e7eb 75%);
  background-size: 200% 100%; animation: shimmer 1.4s infinite;
}

.terlaku-new__card {
  background: #fff;
  border-radius: 20px;
  border: 1.5px solid #e5e7eb;
  overflow: hidden;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
  animation: cardRise .55s ease both;
  animation-delay: var(--card-delay, 0s);
}
@keyframes cardRise {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}
.terlaku-new__card:hover {
  transform: translateY(-5px);
  box-shadow: 0 16px 40px rgba(197,48,48,.13);
  border-color: rgba(197,48,48,.3);
}
.terlaku-new__card--1 { border-top: 3px solid #c53030; }
.terlaku-new__card--2 { border-top: 3px solid #6b7280; }
.terlaku-new__card--3 { border-top: 3px solid #b45309; }

/* Header colored block */
.terlaku-new__header--photo { background: #f3f4f6; }
.terlaku-new__header-img {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
}
.terlaku-new__seller-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}
.terlaku-new__header {
  height: 96px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}
.terlaku-new__header-bg {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255,255,255,.08) 0%, transparent 60%);
  pointer-events: none;
}
.terlaku-new__rank-pill {
  position: absolute;
  z-index: 2;
  top: 10px; left: 10px;
  font-size: 10px; font-weight: 700;
  background: rgba(0,0,0,.22);
  color: #fff;
  padding: 3px 10px;
  border-radius: 999px;
  letter-spacing: .04em;
  text-transform: uppercase;
}
.terlaku-new__header-icon {
  width: 52px; height: 52px;
  border-radius: 14px;
  background: rgba(255,255,255,.15);
  border: 1px solid rgba(255,255,255,.25);
  display: flex; align-items: center; justify-content: center;
}

/* Body */
.terlaku-new__body { padding: 14px 16px 8px; }
.terlaku-new__kat {
  font-size: 10px; font-weight: 700;
  letter-spacing: .08em; text-transform: uppercase;
  color: #c53030; margin-bottom: 5px; display: block;
}
.terlaku-new__nama {
  font-size: 14px; font-weight: 700;
  color: #111827; line-height: 1.3; margin-bottom: 6px;
}
.terlaku-new__toko {
  display: flex; align-items: center; gap: 10px;
  font-size: 11px; color: #6b7280;
}
.terlaku-new__toko svg { flex-shrink: 0; }
.terlaku-new__toko-meta {
  display: flex;
  flex-direction: column;
  gap: 2px;
  line-height: 1.2;
}
.terlaku-new__toko-name { font-size: 12px; color: #374151; font-weight: 600; }
.terlaku-new__toko-sub { font-size: 10px; color: #9ca3af; }

/* Progress bar */
.terlaku-new__bar-wrap { padding: 10px 16px 0; }
.terlaku-new__bar-label {
  display: flex; justify-content: space-between;
  font-size: 10px; color: #9ca3af; margin-bottom: 5px;
}
.terlaku-new__bar {
  height: 4px;
  background: #f3f4f6;
  border-radius: 999px;
  overflow: hidden;
}
.terlaku-new__fill {
  height: 100%;
  background: linear-gradient(90deg, #f87171, #c53030);
  border-radius: 999px;
  transition: width 1.2s cubic-bezier(.4,0,.2,1);
}

/* Footer */
.terlaku-new__foot {
  display: flex; align-items: center; justify-content: space-between;
  padding: 10px 16px;
  margin-top: 2px;
}
.terlaku-new__harga { font-size: 14px; font-weight: 800; color: #c53030; }
.terlaku-new__rating {
  display: flex; align-items: center; gap: 4px;
  font-size: 12px; font-weight: 600; color: #374151;
}

/* Seller strip */
.terlaku-new__seller {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 16px 14px;
  border-top: 1px solid #f3f4f6;
  margin-top: auto;
}
.terlaku-new__seller-avatar {
  width: 30px; height: 30px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: .8rem; font-weight: 700;
  flex-shrink: 0;
}
.terlaku-new__seller-info { flex: 1; min-width: 0; }
.terlaku-new__seller-nama { font-size: 12px; font-weight: 600; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.terlaku-new__seller-kelas { font-size: 10px; color: #9ca3af; }
.terlaku-new__seller-btn {
  font-size: 11px; font-weight: 700; color: #c53030;
  text-decoration: none;
  padding: 5px 12px;
  border: 1.5px solid rgba(197,48,48,.35);
  border-radius: 999px;
  white-space: nowrap;
  transition: background .2s, color .2s;
  flex-shrink: 0;
}
.terlaku-new__seller-btn:hover { background: #c53030; color: #fff; }

/* ════════════════════════════════════
   TOP SELLER — REDESIGNED
════════════════════════════════════ */
.seller-new__grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
  align-items: end;
}

.seller-new__card {
  background: #fff;
  border-radius: 20px;
  border: 1.5px solid #e5e7eb;
  overflow: hidden;
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 0 0 18px;
  cursor: default;
  transition: transform .25s ease, box-shadow .25s ease;
  animation: cardRise .55s ease both;
  animation-delay: var(--s-delay, 0s);
}
.seller-new__card:hover {
  transform: translateY(-5px);
  box-shadow: 0 16px 40px rgba(197,48,48,.12);
}
.seller-new__card--gold {
  border-color: rgba(197,48,48,.3);
}

/* Watermark number */
.seller-new__watermark {
  position: absolute;
  top: -10px; right: 14px;
  font-family: 'Fraunces', serif;
  font-size: 5rem; font-weight: 900;
  color: rgba(197,48,48,.05);
  line-height: 1;
  pointer-events: none;
  user-select: none;
}

/* Accent top bar */
.seller-new__accent {
  width: 100%; height: 5px; flex-shrink: 0;
}
.seller-new__accent--1 { background: #c53030; }
.seller-new__accent--2 { background: #6b7280; }
.seller-new__accent--3 { background: #b45309; }

/* Avatar */
.seller-new__avatar-wrap {
  position: relative;
  margin: 20px 0 12px;
  width: 68px; height: 68px;
  display: flex; align-items: center; justify-content: center;
}
.seller-new__avatar--img {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  object-fit: cover;
  position: relative;
  z-index: 1;
}
.seller-new__avatar {
  width: 60px; height: 60px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 1.4rem; font-weight: 700;
  position: relative; z-index: 1;
}
.seller-new__ring {
  position: absolute; inset: -5px; border-radius: 50%;
  border: 1.5px solid rgba(197,48,48,.2);
}
.seller-new__ring--active {
  animation: ringPulse 2.2s ease-in-out infinite;
}
@keyframes ringPulse {
  0%,100% { transform: scale(1); opacity: .65; }
  50%      { transform: scale(1.14); opacity: .1; }
}
.seller-new__medal {
  position: absolute; bottom: -3px; right: -3px;
  width: 22px; height: 22px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  background: #fff;
  border: 1.5px solid #e5e7eb;
  z-index: 2;
}
.seller-new__medal--1 { border-color: #c53030; color: #c53030; }
.seller-new__medal--2 { border-color: #6b7280; color: #6b7280; }
.seller-new__medal--3 { border-color: #b45309; color: #b45309; }

/* Identity */
.seller-new__identity { padding: 0 16px; }
.seller-new__nama { font-size: 15px; font-weight: 700; color: #111827; margin-bottom: 2px; }
.seller-new__kelas { font-size: 11px; color: #9ca3af; margin-bottom: 10px; }

/* Tagline */
.seller-new__tagline {
  font-size: 12px; color: #6b7280;
  font-style: italic; line-height: 1.6;
  padding: 0 18px; margin-bottom: 14px;
}

/* Stats */
.seller-new__stats {
  display: flex; align-items: center;
  width: calc(100% - 32px);
  background: #f9fafb;
  border-radius: 12px;
  margin-bottom: 14px;
  overflow: hidden;
}
.seller-new__stat {
  flex: 1; padding: 10px 0;
}
.seller-new__stat strong { display: block; font-size: 15px; font-weight: 800; color: #111827; }
.seller-new__stat span { font-size: 10px; color: #9ca3af; text-transform: uppercase; letter-spacing: .05em; }
.seller-new__stat-divider { width: 1px; height: 28px; background: #e5e7eb; flex-shrink: 0; }

/* Progress */
.seller-new__prog-wrap { width: calc(100% - 32px); margin-bottom: 14px; }
.seller-new__prog-label {
  display: flex; justify-content: space-between;
  font-size: 10px; color: #9ca3af; margin-bottom: 5px;
}
.seller-new__prog { height: 4px; background: #f3f4f6; border-radius: 999px; overflow: hidden; }
.seller-new__prog-fill {
  height: 100%;
  background: linear-gradient(90deg, #f87171, #c53030);
  border-radius: 999px;
  width: 0;
  animation: fillBar 1.3s cubic-bezier(.4,0,.2,1) both;
  animation-delay: calc(var(--s-delay, 0s) + .4s);
}
.seller-new__prog-fill--1 { animation-name: fill100; }
.seller-new__prog-fill--2 { animation-name: fill72; }
.seller-new__prog-fill--3 { animation-name: fill52; }
@keyframes fill100 { from{width:0} to{width:100%} }
@keyframes fill72  { from{width:0} to{width:72%} }
@keyframes fill52  { from{width:0} to{width:52%} }

/* Tags */
.seller-new__tags { display: flex; flex-wrap: wrap; gap: 5px; justify-content: center; margin-bottom: 14px; padding: 0 16px; }
.seller-new__tag {
  font-size: 10px; font-weight: 600;
  padding: 3px 9px;
  background: #fff5f5; color: #c53030;
  border-radius: 999px; border: 1px solid #fecaca;
}

/* CTA button */
.seller-new__btn {
  display: inline-block;
  padding: 8px 22px;
  background: #c53030; color: #fff;
  border-radius: 999px;
  font-size: 12px; font-weight: 700;
  text-decoration: none;
  border: none; cursor: pointer;
  transition: background .2s, transform .2s;
}
.seller-new__btn:hover { background: #9b1c1c; transform: translateY(-1px); }
.seller-new__btn--disabled { background: #e5e7eb; color: #9ca3af; cursor: not-allowed; }
.seller-new__btn--disabled:hover { background: #e5e7eb; transform: none; }

/* ── CTA ── */
.cta { background: linear-gradient(135deg,#c53030,#e53e3e,#f56565); padding: 64px 24px; text-align: center; position: relative; overflow: hidden; }
.cta__inner { max-width: 600px; margin: 0 auto; position: relative; }
.cta__title { font-family: 'Fraunces',serif; font-size: clamp(1.7rem,4vw,2.6rem); font-weight: 900; color: #fff; margin-bottom: 14px; line-height: 1.2; }
.cta__desc { font-size: .95rem; color: rgba(255,255,255,.85); line-height: 1.75; margin-bottom: 28px; }
.cta__btn { display: inline-block; padding: 13px 30px; background: #fff; color: #c53030; border-radius: 10px; font-family: 'Plus Jakarta Sans',sans-serif; font-size: .9rem; font-weight: 800; text-decoration: none; transition: transform .2s, box-shadow .2s; box-shadow: 0 4px 18px rgba(0,0,0,.2); }
.cta__btn:hover { transform: translateY(-3px); box-shadow: 0 8px 28px rgba(0,0,0,.3); }

/* ── RESPONSIVE ── */
@media (max-width:1100px) {
  .terlaku-new__grid { grid-template-columns: repeat(3, 1fr); }
  .seller-new__grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width:900px) {
  .produk__grid { grid-template-columns: repeat(2,1fr); }
  .hero__container { grid-template-columns: 1fr; }
  .hero__visual { order: -1; }
  .terlaku-new__grid { grid-template-columns: 1fr; gap: 12px; }
  .seller-new__grid { grid-template-columns: 1fr; gap: 12px; }
}
@media (max-width:640px) {
  .identity-bar__actions { display: none; }
  .kategori__row { gap: 4px; flex-wrap: wrap; justify-content: center; }
  .kategori__circle { width: 52px; height: 52px; font-size: 1.3rem; }
  .produk__grid { grid-template-columns: repeat(2,1fr); gap: 12px; }
  .terlaku-new__grid { grid-template-columns: 1fr; }
  .seller-new__grid { grid-template-columns: 1fr; }
}
</style>