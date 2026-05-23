<template>
  <div class="seller-profile" :style="{ '--seller-color': sellerColor }" :class="{ 'seller-profile--premium': isPremiumSeller }">
    <PremiumBackground
      v-if="isPremiumSeller"
      variant="overlay"
      :primary="sellerColor"
      secondary="#e53e3e"
      accent="#7c3aed"
      :vivid="true"
    />
    <!-- Premium Badge moved outside header so it won't be clipped by header or container -->
    <div v-if="isPremiumSeller" class="sp-premium-badge">
      <span class="premium-star">&#9733;</span> PREMIUM SELLER
    </div>
    <div v-if="loading" class="sp-loading">
      <p>Memuat profil penjual...</p>
    </div>

    <div v-else class="sp-header sp-fade-in">

      <div class="sp-banner" :style="{ backgroundImage: sellerBanner ? `linear-gradient(180deg, rgba(0,0,0,.3), rgba(0,0,0,.45)), url(${sellerBanner})` : '' }">
        <div class="sp-avatar-large sp-avatar--photo">
          <img :src="businessLogo" :alt="sellerTitle" @error="onLogoError" />
        </div>
      </div>

      <div class="sp-info" :class="{ 'sp-info--premium': isPremiumSeller }">
        <div class="sp-info-top">
          <div class="sp-info-content">
            <h1 class="sp-name">{{ sellerTitle }}</h1>
            <p class="sp-meta">
              <span class="sp-meta-item">{{ sellerData.owner?.name || 'Penjual' }}</span>
              <span class="sp-meta-separator">·</span>
              <span v-if="sellerData.category" class="sp-meta-item">{{ sellerData.category }}</span>
            </p>
          </div>
        </div>

        <p class="sp-bio">{{ sellerDescription }}</p>

        <div class="sp-stats">
          <div class="sp-stat">
            <span class="sp-stat-number">{{ stats.products }}</span>
            <span class="sp-stat-label">Produk</span>
          </div>
          <div class="sp-stat">
            <span class="sp-stat-number">{{ stats.blogs }}</span>
            <span class="sp-stat-label">Artikel</span>
          </div>
          <div class="sp-stat">
            <span class="sp-stat-number">{{ stats.sales }}</span>
            <span class="sp-stat-label">Penjualan</span>
          </div>
        </div>

        <div class="sp-contact">
          <div class="sp-contact-item">
            <span class="contact-icon"></span>
            <span>{{ sellerData.phone || 'Nomor belum tersedia' }}</span>
          </div>
          <div class="sp-contact-item">
            <span class="contact-icon"></span>
            <span>{{ sellerData.address || 'Kelas belum tersedia' }}</span>
          </div>
        </div>

        <div v-if="isPremiumSeller" class="sp-premium-features">
          <div class="premium-feature">
            <span></span> Seller premium dengan akses fitur eksklusif
          </div>
          <div class="premium-feature">
            <span></span> Featured di halaman utama marketplace
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="sp-tabs">
      <button 
        v-for="tab in tabs" 
        :key="tab.id"
        class="sp-tab" 
        :class="{ 'sp-tab--active': activeTab === tab.id }"
        @click="activeTab = tab.id"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Products Tab -->
    <div v-if="activeTab === 'products'" class="sp-products">
      <div class="sp-products-grid">
        <article v-for="product in sellerProducts" :key="product.id" class="sp-product-card tp-card-equal">
          <div class="sp-product-img tp-card-equal__media">
            <img :src="product.imageUrl" :alt="product.name" class="tp-img-cover" loading="lazy" @error="onProductImgError($event, product)" />
          </div>
          <div class="sp-product-body tp-card-equal__body">
            <h4 class="sp-product-name">{{ product.name }}</h4>
            <p class="sp-product-price">Rp {{ product.price.toLocaleString('id-ID') }}</p>
            <router-link :to="`/product/${product.id}`" class="sp-product-link">Lihat Detail</router-link>
          </div>
        </article>
      </div>
    </div>

    <!-- Blogs Tab -->
    <div v-if="activeTab === 'blogs'" class="sp-blogs">
      <article v-for="blog in sellerBlogs" :key="blog.id" class="sp-blog-card tp-card-equal">
        <div v-if="blog.imageUrl" class="sp-blog-thumb tp-card-equal__media">
          <img :src="blog.imageUrl" :alt="blog.title" class="tp-img-cover" loading="lazy" @error="$onImageError($event)" />
        </div>
        <div class="sp-blog-body tp-card-equal__body">
          <div class="sp-blog-meta">
            <span class="sp-blog-date">{{ new Date(blog.created_at).toLocaleDateString('id-ID') }}</span>
          </div>
          <h4 class="sp-blog-title">{{ blog.title }}</h4>
          <p class="sp-blog-excerpt">{{ blog.excerpt }}</p>
          <router-link :to="`/blog/${blog.slug}`" class="sp-blog-link">Baca Lengkap →</router-link>
        </div>
      </article>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import businessService from '@/services/business.js'
import { productService } from '@/services/product.js'
import blogService from '@/services/blog.js'
import {
  normalizeImageUrl,
  onImageError,
  resolveProductImage,
  resolveBusinessLogo,
  resolveBusinessBanner,
  pickProductImageSource,
} from '@/utils/image'
import PremiumBackground from '@/components/PremiumBackground.vue'

export default {
  name: 'SellerProfile',
  components: { PremiumBackground },

  setup() {
    const route = useRoute()
    const sellerId = computed(() => route.params.id)
    const seller = ref(null)
    const sellerProducts = ref([])
    const sellerBlogs = ref([])
    const stats = ref({ products: 0, blogs: 0, sales: 0 })
    const activeTab = ref('products')
    const loading = ref(true)

    const tabs = [
      { id: 'products', label: 'Produk' },
      { id: 'blogs', label: 'Blog' }
    ]

    const sellerData = computed(() => seller.value || {})
    const isPremiumSeller = computed(() => Boolean(sellerData.value.is_premium))
    const businessLogo = computed(() =>
      resolveBusinessLogo(sellerData.value.logo, isPremiumSeller.value)
    )
    const sellerColor = computed(() => sellerData.value.theme_color || (isPremiumSeller.value ? '#f59e0b' : '#6366f1'))
    const sellerBanner = computed(() =>
      resolveBusinessBanner(sellerData.value.banner, sellerData.value.logo, isPremiumSeller.value)
    )

    const onLogoError = (e) => onImageError(e, resolveBusinessLogo(null, isPremiumSeller.value))
    const onProductImgError = (e, product) => {
      onImageError(e)
      if (product) product.imageUrl = resolveProductImage(null)
    }

    const mapProduct = (p) => ({
      ...p,
      imageUrl: resolveProductImage(pickProductImageSource(p) || p.image),
    })

    const mapBlog = (b) => ({
      ...b,
      imageUrl: normalizeImageUrl(b.image || b.thumbnail),
      excerpt: b.excerpt || (b.content || '').slice(0, 160),
    })
    const sellerTitle = computed(() => sellerData.value.name || sellerData.value.owner?.name || 'Penjual')
    const sellerDescription = computed(() => sellerData.value.description || 'Deskripsi penjual belum tersedia.')

    const loadSellerProfile = async () => {
      loading.value = true
      try {
        if (!sellerId.value) {
          console.warn(' No seller ID provided')
          seller.value = {}
          sellerProducts.value = []
          sellerBlogs.value = []
          stats.value = { products: 0, blogs: 0, sales: 0 }
          return
        }

        console.log(' Loading seller profile for business identifier:', sellerId.value)

        const businessResult = await businessService.getBusiness(sellerId.value)
        console.log(' Business Result:', businessResult)
        seller.value = businessResult.data || {}
        console.log(' Seller Data:', seller.value)

        const productResult = await productService.getProductsByBusiness(seller.value.id, {
          sort: 'terlaris',
          per_page: 8,
        })
        sellerProducts.value = (productResult.data || []).map(mapProduct)
        stats.value.products = sellerProducts.value.length
        console.log(' Products:', sellerProducts.value.length)

        const blogResult = await blogService.getBlogsByBusiness(seller.value.id)
        sellerBlogs.value = (blogResult.data || []).map(mapBlog)
        stats.value.blogs = sellerBlogs.value.length
        console.log(' Blogs:', sellerBlogs.value.length)

        stats.value.sales = sellerProducts.value.reduce((sum, p) => sum + (p.total_sold || 0), 0)
        console.log(' Stats:', stats.value)
      } catch (error) {
        console.error(' Failed to load seller profile:', error)
        seller.value = {}
        sellerProducts.value = []
        sellerBlogs.value = []
        stats.value = { products: 0, blogs: 0, sales: 0 }
      } finally {
        loading.value = false
      }
    }

    onMounted(loadSellerProfile)

    watch(
      () => sellerId.value,
      async (newId, oldId) => {
        if (newId && newId !== oldId) {
          seller.value = null
          sellerProducts.value = []
          sellerBlogs.value = []
          stats.value = { products: 0, blogs: 0, sales: 0 }
          await loadSellerProfile()
        }
      }
    )

    return {
      seller,
      sellerProducts,
      sellerBlogs,
      stats,
      activeTab,
      loading,
      tabs,
      sellerData,
      isPremiumSeller,
      businessLogo,
      sellerColor,
      sellerBanner,
      sellerTitle,
      sellerDescription,
      onLogoError,
      onProductImgError,
    }
  }
}
</script>

<style scoped>
.seller-profile {
  position: relative;
  overflow: visible;
  max-width: var(--tp-container-max, 1200px);
  margin: 0 auto;
  padding: 36px var(--tp-container-pad, 20px) 40px;
  background: transparent;
}

.seller-profile::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at top left, rgba(245, 158, 11, 0.08), transparent 28%),
              radial-gradient(circle at top right, rgba(59, 130, 246, 0.06), transparent 20%);
  pointer-events: none;
  z-index: 0;
}

.seller-profile--premium {
  background: transparent;
}

.sp-header {
  position: relative;
  background: rgba(255, 255, 255, 0.88);
  border-radius: 24px;
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
  overflow: visible;
  margin-bottom: 32px;
  border: 1px solid rgba(255, 255, 255, 0.72);
  backdrop-filter: blur(18px);
}

.sp-banner {
  height: 260px;
  background-size: cover;
  background-position: center;
  position: relative;
  display: flex;
  align-items: end;
  padding: 32px;
  border-bottom: 1px solid rgba(255,255,255,.16);
  box-shadow: inset 0 0 0 1000px rgba(0,0,0,.08);
}

.sp-avatar-large {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--seller-color), #111827);
  color: white;
  font-size: 2.5rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 12px 30px rgba(0,0,0,0.18);
  border: 4px solid rgba(255,255,255,0.22);
  overflow: hidden;
}

.sp-avatar-large img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.sp-avatar--photo {
  background: linear-gradient(135deg, rgba(255,255,255,0.08), rgba(255,255,255,0.18));
}

.sp-fade-in {
  animation: fadeInUp 0.7s ease forwards;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(22px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.sp-info {
  padding: 32px;
  background: linear-gradient(180deg, #fff 0%, #fafafa 100%);
  position: relative;
  backdrop-filter: blur(8px);
}

.sp-info::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(ellipse 60% 50% at 15% 20%, rgba(var(--seller-rgb, 245, 158, 11), 0.04) 0%, transparent 50%);
  pointer-events: none;
  border-radius: 0 0 20px 20px;
}

.sp-info > * {
  position: relative;
  z-index: 1;
}

.sp-info-top {
  display: flex;
  justify-content: space-between;
  gap: 24px;
  align-items: flex-start;
  margin-bottom: 18px;
}

.sp-logo img {
  width: 72px;
  height: 72px;
  object-fit: cover;
  border-radius: 18px;
  border: 1px solid rgba(17,24,39,.08);
  box-shadow: 0 10px 25px rgba(0,0,0,.08);
}

.sp-name {
  font-size: 2.2rem;
  font-weight: 900;
  color: #111827;
  margin-bottom: 10px;
}

.sp-bio {
  font-size: 1.1rem;
  color: #6b7280;
  line-height: 1.6;
  margin-bottom: 24px;
}

.sp-stats {
  display: flex;
  gap: 32px;
  margin-bottom: 24px;
}

.sp-stat {
  text-align: center;
}

.sp-stat-number {
  display: block;
  font-size: 2rem;
  font-weight: 900;
  color: #111827;
}

.sp-stat-label {
  font-size: 0.875rem;
  color: #6b7280;
}

.sp-contact {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
  font-size: 0.95rem;
  color: #374151;
  margin-bottom: 24px;
}

.sp-contact-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 16px;
  border-radius: 18px;
  background: #f8fafc;
  border: 1px solid #e5e7eb;
}

.sp-premium-note {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  margin-top: 18px;
  padding: 14px 18px;
  border-radius: 18px;
  background: rgba(245,158,11,.1);
  color: #92400e;
  font-weight: 700;
  border: 1px solid rgba(245,158,11,.2);
}

.sp-premium-badge {
  /* make badge fixed so it's always visible above navbar */
  position: fixed;
  top: calc(var(--navbar-h, 64px) + 10px);
  right: calc((100% - var(--tp-container-max, 1200px)) / 2 + 28px);
  background: linear-gradient(135deg, #f59e0b, #f97316);
  color: white;
  padding: 10px 18px;
  border-radius: 999px;
  font-weight: 800;
  font-size: 0.92rem;
  display: flex;
  align-items: center;
  gap: 10px;
  z-index: 1400; /* above navbar (1200) */
  box-shadow: 0 20px 46px rgba(245, 158, 11, 0.36);
  transform: translateZ(0);
  animation: premium-badge-fadein 500ms ease-out both, premium-badge-float 3.6s ease-in-out infinite;
}

.premium-star {
  font-size: 1.1rem;
}

@keyframes premium-badge-float {
  0% { transform: translateZ(0) translateY(0); box-shadow: 0 18px 40px rgba(245,158,11,0.28); }
  45% { transform: translateZ(0) translateY(-6px) rotate(-0.3deg); box-shadow: 0 28px 56px rgba(245,158,11,0.36); }
  100% { transform: translateZ(0) translateY(0); box-shadow: 0 18px 40px rgba(245,158,11,0.28); }
}

@keyframes premium-badge-fadein {
  0% { opacity: 0; transform: translateY(-6px) scale(.98); }
  100% { opacity: 1; transform: translateY(0) scale(1); }
}

@media (max-width: 900px) {
  .sp-premium-badge {
    right: 16px;
    top: calc(var(--navbar-h, 64px) + 8px);
    font-size: 0.82rem;
    padding: 8px 12px;
  }
}

.sp-header,
.sp-tabs,
.sp-products,
.sp-blogs {
  position: relative;
  z-index: 1;
}

.sp-header {
  position: relative;
}

.sp-info--premium {
  background: linear-gradient(180deg, rgba(245, 158, 11, 0.02) 0%, rgba(249, 115, 22, 0.01) 100%);
  border-bottom: 2px solid rgba(245, 158, 11, 0.08);
}

.sp-premium-features {
  display: grid;
  gap: 12px;
  margin-top: 20px;
  padding-top: 20px;
  border-top: 2px solid rgba(245, 158, 11, 0.1);
  animation: fadeInUp 0.7s ease-out 0.3s forwards;
  opacity: 0;
}

.premium-feature {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  border-radius: 14px;
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, rgba(245, 158, 11, 0.04) 100%);
  color: #92400e;
  font-weight: 700;
  font-size: 0.95rem;
  border: 1px solid rgba(245, 158, 11, 0.12);
  backdrop-filter: blur(6px);
  transition: all 0.3s ease;
}

.premium-feature:hover {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.12) 0%, rgba(245, 158, 11, 0.08) 100%);
  border-color: rgba(245, 158, 11, 0.2);
  transform: translateX(4px);
}

.sp-info-content {
  flex: 1;
}

.sp-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #6b7280;
  font-size: 0.95rem;
  margin-bottom: 8px;
}

.sp-meta-item {
  display: inline;
}

.sp-meta-separator {
  display: inline;
  color: #d1d5db;
}

.sp-tabs {
  display: flex;
  background: white;
  border-radius: 16px;
  padding: 4px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.08);
  margin-bottom: 24px;
  border: 1px solid rgba(0,0,0,0.02);
  backdrop-filter: blur(8px);
}

.sp-tab {
  flex: 1;
  padding: 12px 20px;
  border: none;
  background: none;
  border-radius: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  color: #6b7280;
}

.sp-tab:hover {
  background: rgba(0,0,0,0.02);
}

.sp-tab--active {
  background: linear-gradient(135deg, #f8fafc 0%, #f3f4f6 100%);
  color: #111827;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.sp-products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: var(--tp-grid-gap, 20px);
  align-items: stretch;
}

.sp-blogs {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: var(--tp-grid-gap, 20px);
  align-items: stretch;
}

.sp-product-card,
.sp-blog-card {
  background: white;
  border: 1.5px solid var(--tp-border, #e5e7eb);
  border-radius: var(--tp-radius-lg, 16px);
  box-shadow: var(--tp-shadow, 0 4px 16px rgba(0,0,0,.06));
  overflow: hidden;
  transition: transform .3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow .3s ease;
  backdrop-filter: blur(4px);
}

.sp-product-card:hover,
.sp-blog-card:hover {
  transform: translateY(-6px) scale(1.02);
  box-shadow: var(--tp-shadow-hover, 0 12px 32px rgba(229,62,62,.12));
}

.sp-product-img {
  aspect-ratio: 1;
  background: #f3f4f6;
  overflow: hidden;
}

.sp-product-body,
.sp-blog-body {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
}

.sp-product-name,
.sp-blog-title {
  font-size: .95rem;
  font-weight: 800;
  color: #111827;
  margin: 0;
  line-height: 1.35;
}

.sp-product-price {
  font-size: .9rem;
  font-weight: 800;
  color: #e53e3e;
  margin: 0;
}

.sp-blog-thumb {
  aspect-ratio: 16/9;
  background: #f3f4f6;
  overflow: hidden;
}

.sp-blog-excerpt {
  font-size: .84rem;
  color: #6b7280;
  line-height: 1.6;
  margin: 0;
  flex: 1;
  display: -webkit-box;
  line-clamp: 3;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

@media (max-width: 768px) {
  .sp-stats {
    gap: 16px;
  }
  
  .sp-stat-number {
    font-size: 1.5rem;
  }
}

.sp-header,
.sp-tabs,
.sp-products,
.sp-blogs {
  position: relative;
  z-index: 1;
}
</style>  


