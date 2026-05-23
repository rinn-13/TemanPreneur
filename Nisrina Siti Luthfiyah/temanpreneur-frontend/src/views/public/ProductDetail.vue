<template>
  <div class="product-detail">
    <!-- Loading Skeleton -->
    <div v-if="loading" class="skeleton-container">
      <div class="skeleton-breadcrumb"></div>
      <div class="skeleton-hero">
        <div class="skeleton-gallery"></div>
        <div class="skeleton-info">
          <div class="skeleton-title"></div>
          <div class="skeleton-price"></div>
          <div class="skeleton-sold"></div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div v-else-if="product" class="main-content">
      <!-- Breadcrumb -->
      <nav class="breadcrumb">
        <router-link to="/" class="breadcrumb-link">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Beranda
        </router-link>
        <span class="breadcrumb-sep">/</span>
        <router-link to="/katalog" class="breadcrumb-link">Katalog</router-link>
        <span class="breadcrumb-sep">/</span>
        <span class="breadcrumb-current">{{ product?.nama || 'Produk' }}</span>
      </nav>

      <div class="hero-section">
        <!-- Product Gallery -->
        <div class="gallery">
          <div class="gallery-main" @click="openPreview(currentImage)">
            <img :src="currentImage" alt="Gambar produk" class="gallery-img" />
            <div class="gallery-overlay" :style="product?.gradient || {}"></div>
          </div>
          <div class="gallery-thumbs">
            <button 
              v-for="(img, idx) in product?.gallery || []" 
              :key="idx"
              class="thumb" 
              :class="{ 'thumb-active': currentImage === img }"
              @click="currentImage = img"
              :style="{ backgroundImage: `url(${img})` }"
              type="button"
            ></button>
          </div>

          <div v-if="imagePreviewOpen" class="image-modal" @click.self="closePreview">
            <button class="modal-close" @click="closePreview">×</button>
            <img :src="previewImage" alt="Preview produk" />
          </div>
        </div>

        <!-- Product Info -->
        <div class="info">
          <h1 class="product-title">{{ product?.nama || 'Produk' }}</h1>
          
          <!-- Rating & Sold -->
          <div class="stats-row">
            <div class="rating-display">
              <span class="rating-stars">{{ product?.rating?.toFixed(1) ?? '0' }}</span>
              <span class="rating-count">({{ product?.reviewsCount || 0 }} Review)</span>
            </div>
            <div class="sold-badge">
              (Terjual {{ product?.terjual || 0 }} Pcs)
            </div>
          </div>

          <!-- Price -->
          <div class="price-container">
            <span class="price">{{ formatRupiah(product?.harga || 0) }}</span>
            <div class="stock-info">
              <span class="stock">Stok: {{ product?.stok || 0 }}</span>
              <span class="category-badge" :style="{ background: (product?.tokoColor || '#e2e8f0') + '20' }">
                {{ product?.kategori || 'category' }}
              </span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="actions">
            <div class="quantity-input">
              <button @click="quantity--" :disabled="quantity <= 1" class="qty-btn">-</button>
              <input v-model.number="quantity" type="number" min="1" :max="product?.stok || 1">
              <button @click="quantity++" :disabled="quantity >= (product?.stok || 1)" class="qty-btn">+</button>
            </div>
            <div class="action-buttons">
              <button @click="addToCart" class="btn-cart">
                <svg width="18" height="18" viewBox="0 0 24 24">
                  <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.293 2.586A2 2 0 0 0 6.414 17H18a2 2 0 0 0 1.707-2.414L19 13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Keranjang
              </button>
              <button @click="buyNow" class="btn-buy" :disabled="quantity > (product?.stok || 0)">
                Beli Sekarang
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Seller Profile -->
      <section class="seller-section">
        <div class="seller-card">
          <div class="seller-avatar" :style="product?.seller?.avatarUrl ? '' : `background: ${product?.seller?.avatarColor || '#e2e8f0'}`">
            <img
              v-if="product?.seller?.avatarUrl"
              :src="product.seller.avatarUrl"
              :alt="product?.seller?.nama || 'Avatar penjual'"
              class="seller-avatar-img"
              @error="onImageError($event, '/avatars/default-seller.svg')"
            />
            <span v-else>{{ product?.seller?.avatarLetter || '?' }}</span>
          </div>
          <div class="seller-info">
            <h3 class="seller-title-row">
              {{ product?.seller?.nama || 'Penjual' }}
              <span v-if="product?.seller?.is_premium" class="seller-premium-badge" title="Seller Premium">
                <i class="bi bi-patch-check-fill" aria-hidden="true"></i>
                Premium
              </span>
            </h3>
            <div class="seller-status">{{ product?.seller?.status || 'Seller' }}</div>
            <div class="seller-kelas">{{ product?.seller?.kelas || 'Kelas' }}</div>
          </div>
          <div class="seller-actions">
            <button class="chat-btn">
              <span>+{{ product?.unreadMessages || 2 }}</span>
              Chat
            </button>
            <router-link
              :to="{ name: 'seller.public', params: { id: product?.businessId || product?.seller?.business_id || product?.business?.id || 'toko' } }"
              class="visit-store"
            >
              Kunjungi Toko →
            </router-link>
          </div>
        </div>
        <button class="ask-seller">Tanyakan pada Penjual</button>
      </section>

      <!-- Reviews Section -->
      <section class="reviews-section">
        <div class="section-header">
          <h2>RATING & REVIEW</h2>
          <span class="rating-count">({{ product.reviewsCount || 0 }} Review)</span>
        </div>
        <div class="reviews-list">
          <div v-for="review in product?.reviews || []" :key="review.id" class="review-card">
            <div class="review-header">
              <div class="review-avatar" :style="`background: ${review?.avatarColor || '#e2e8f0'}`">
                {{ review?.reviewer?.charAt(0) || '?' }}
              </div>
              <div class="review-author">
                <div class="review-name">{{ review?.reviewer || 'Anonim' }}</div>
                <div class="review-rating">{{ review?.rating?.toFixed(1) ?? '0' }}</div>
              </div>
              <div class="review-time">{{ review?.created_at || 'Baru-baru ini' }}</div>
            </div>
            <p class="review-text">{{ review?.comment || 'Tidak ada komentar' }}</p>
          </div>
        </div>
      </section>

      <!-- Recommendations -->
      <section class="rekomendasi-section">
        <h2 class="section-title">REKOMENDASI</h2>
        <div class="rekomendasi-grid">
          <div 
            v-for="rec in recommendations" 
            :key="rec.id" 
            class="rekomendasi-card" 
            @click="$router.push({ name: 'product-detail', params: { id: rec.id } })"
          >
            <div class="rec-image" :style="{ backgroundImage: `url(${rec.image})`, backgroundColor: rec.gradient }">
              <span class="rec-emoji">{{ rec.emoji }}</span>
            </div>
            <div class="rec-info">
              <h3>{{ rec.nama }}</h3>
              <div class="rec-price">{{ formatRupiah(rec.harga) }}</div>
              <div class="rec-seller">{{ rec.toko }}</div>
            </div>
          </div>
        </div>
        <router-link to="/katalog" class="see-more">LIHAT SELENGKAPNYA →</router-link>
      </section>
    </div>

    <!-- Empty/Not Found -->
    <div v-else class="empty-state">
      <div class="empty-icon"></div>
      <h2>Produk tidak ditemukan</h2>
      <router-link to="/katalog" class="btn-back">Kembali ke Katalog</router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api/axios.js'
import { normalizeImageUrl, onImageError } from '@/utils/image'
import { useCartStore } from '@/stores/cart'
import productService from '@/services/product.js'

const route = useRoute()
const router = useRouter()
const cartStore = useCartStore()

const loading = ref(true)
const product = ref(null)
const quantity = ref(1)
const currentImage = ref('')
const imagePreviewOpen = ref(false)
const previewImage = ref('')
const recommendations = ref([])

const normalizeProduct = (product) => {
  const images = Array.isArray(product.images)
    ? product.images.filter(Boolean).map(normalizeImageUrl)
    : []

  const gallery = Array.isArray(product.gallery) && product.gallery.length
    ? product.gallery.map(normalizeImageUrl)
    : images.length
      ? images
      : product.image
        ? [normalizeImageUrl(product.image)]
        : []

  const image = normalizeImageUrl(product.image || product.gambar || images[0] || gallery[0] || null)
  const categoryLabel = product.category?.name || product.category?.slug || product.category || product.kategori || 'lainnya'
  const business = product.business || null
  const sellerInfo = product.seller || null
  const sellerName = sellerInfo?.business_name || sellerInfo?.name || business?.name || sellerInfo?.nama || 'Penjual'
  const sellerClass = sellerInfo?.kelas || sellerInfo?.user_class || business?.user?.class || 'Siswa'
  const sellerStatus = business?.status ? String(business.status).charAt(0).toUpperCase() + String(business.status).slice(1) : sellerInfo?.status || 'Seller'
  const sellerPremium = Boolean(sellerInfo?.is_premium ?? business?.is_premium)
  const sellerAvatar = normalizeImageUrl(
    sellerInfo?.photo ||
    sellerInfo?.avatar ||
    sellerInfo?.photo_url ||
    sellerInfo?.profile_photo ||
    sellerInfo?.profile_photo_path ||
    sellerInfo?.avatar_url ||
    sellerInfo?.image ||
    business?.logo ||
    business?.logo_url ||
    null,
    null
  )

  return {
    ...product,
    nama: product.nama || product.name || 'Produk',
    harga: product.harga || product.price || 0,
    stok: product.stok || product.stock || 0,
    kategori: categoryLabel,
    toko: product.toko || business?.name || sellerInfo?.business_name || sellerInfo?.name || 'Usaha',
    businessId: business?.id || product.business_id || sellerInfo?.business_id || sellerInfo?.businessId || null,
    image,
    gallery,
    images,
    rating: typeof product.rating === 'number' ? product.rating : Number(product.rating) || 0,
    terjual: product.terjual || product.total_sold || product.totalSold || 0,
    reviewsCount: typeof product.reviews_count === 'number' ? product.reviews_count : Array.isArray(product.reviews) ? product.reviews.length : 0,
    seller: {
      ...sellerInfo,
      id: sellerInfo?.id || business?.user?.id || null,
      nama: sellerName,
      name: sellerName,
      kelas: sellerClass,
      status: sellerStatus,
      business_id: business?.id || sellerInfo?.business_id || sellerInfo?.businessId || null,
      avatarUrl: sellerAvatar,
      avatarColor: sellerInfo?.avatarColor || business?.theme_color || '#e2e8f0',
      avatarLetter: sellerName?.[0]?.toUpperCase() || '?',
      is_premium: sellerPremium,
    },
    reviews: Array.isArray(product.reviews) ? product.reviews.map((review) => ({
      ...review,
      reviewer: review.reviewer || review.user_name || review.name || 'Anonim',
      rating: typeof review.rating === 'number' ? review.rating : Number(review.rating) || 0,
      comment: review.comment || review.komentar || '',
      created_at: review.created_at || review.waktu || '',
    })) : [],
  }
}

// State
const fetchProduct = async () => {
  const id = parseInt(route.params.id)
  loading.value = true

  try {
    const response = await productService.getProduct(id)
    if (!response.success) throw new Error('Gagal ambil produk')
    const payload = response.data || {}
    product.value = normalizeProduct(payload)
    currentImage.value = product.value.image || (product.value.gallery?.[0] || '')

    await fetchRecommendations()
  } catch (error) {
    console.error('Fetch product error:', error)
    product.value = null
  } finally {
    loading.value = false
  }
}

const fetchRecommendations = async () => {
  recommendations.value = []

  try {
    const categoryId = product.value?.category?.id
    const categorySlug = product.value?.category?.slug || product.value?.kategori
    const params = { per_page: 8 }

    if (categoryId) {
      params.category_id = categoryId
    } else if (categorySlug) {
      params.category = categorySlug
    } else {
      return
    }

    const response = await productService.getProducts(params)
    if (!response.success) return

    recommendations.value = (response.data || [])
      .filter((item) => item.id !== product.value.id)
      .map((item) => normalizeProduct(item))
      .slice(0, 4)
  } catch (error) {
    console.error('Fetch recommendations error:', error)
    recommendations.value = []
  }
}

const addToCart = async () => {
  if (!localStorage.getItem('token')) {
    router.push('/login?redirect=' + route.fullPath)
    return
  }

  try {
    await api.post('/cart', {
      product_id: product.value.id,
      quantity: quantity.value,
    })
    cartStore.incrementBy(quantity.value)
    alert(` ${quantity.value}x ${product.value.nama} berhasil ditambahkan ke keranjang`)
    router.push('/keranjang')
  } catch (error) {
    alert(' Gagal tambah ke keranjang: ' + (error.response?.data?.message || 'Coba lagi'))
  }
}

const buyNow = async () => {
  if (!localStorage.getItem('token')) {
    router.push('/login?redirect=' + route.fullPath)
    return
  }
  
  try {
    await api.post('/orders', {
      product_id: product.value.id,
      quantity: quantity.value
    })
    alert(' Pesanan berhasil dibuat!')
    router.push('/buyer/orders')
  } catch (error) {
    alert(' Gagal: ' + (error.response?.data?.message || 'Cek koneksi'))
  }
}

const openPreview = (imageUrl) => {
  if (!imageUrl) return
  previewImage.value = imageUrl
  imagePreviewOpen.value = true
}

const closePreview = () => {
  imagePreviewOpen.value = false
  previewImage.value = ''
}

const formatRupiah = (harga) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(harga)
}

watch(() => route.params.id, () => {
  product.value = null
  currentImage.value = ''
  recommendations.value = []
  fetchProduct()
})

onMounted(fetchProduct)
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:wght@700;900&display=swap');

/* Layout */
.product-detail {
  max-width: 1440px;
  margin: 0 auto;
  padding: 24px;
  font-family: 'Plus Jakarta Sans', sans-serif;
  background: #f8fafc;
}

.main-content {
  background: #fff;
}

/* Skeleton */
.skeleton-container {
  max-width: 1440px;
  margin: 0 auto;
  padding: 24px;
}

.skeleton-breadcrumb { height: 24px; width: 300px; background: linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite; border-radius: 6px; margin-bottom: 32px; }

.skeleton-hero { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
.skeleton-gallery { height: 500px; background: linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite; border-radius: 24px; }
.skeleton-info { display: flex; flex-direction: column; gap: 20px; }
.skeleton-title { height: 32px; width: 80%; background: linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%); animation: shimmer 1.5s infinite; border-radius: 8px; }
.skeleton-price { height: 28px; width: 200px; background: linear-gradient(90deg,#fee2e2 25%,#fecaca 50%,#fee2e2 75%); animation: shimmer 1.5s infinite; border-radius: 8px; }
.skeleton-sold { height: 20px; width: 150px; background: linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%); animation: shimmer 1.5s infinite; border-radius: 6px; }

@keyframes shimmer { 0%{ background-position:200% 0; } 100%{ background-position:-200% 0; } }

/* Breadcrumb */
.breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 32px;
  font-size: 0.875rem;
  color: #64748b;
}

.breadcrumb-link {
  color: #475569;
  text-decoration: none;
  transition: color 0.2s;
}

.breadcrumb-link:hover { color: #e53e3e; }

.breadcrumb-current { color: #111827; font-weight: 600; }

.breadcrumb-sep { color: #d1d5db; }

/* Hero Section */
.hero-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 48px;
  margin-bottom: 48px;
  padding: 32px 0;
}

/* Gallery */
.gallery { position: relative; }

.gallery-main {
  height: 500px;
  border-radius: 24px;
  overflow: hidden;
  position: relative;
  background-size: cover;
  background-position: center;
  margin-bottom: 20px;
  border: 1px solid #e2e8f0;
    cursor: zoom-in;
  }

  .gallery-main .gallery-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  .gallery-overlay {
    position: absolute;
    inset: 0;
    opacity: 0.9;
  }

  .image-modal {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 50;
    padding: 24px;
  }

  .image-modal img {
    max-width: 100%;
    max-height: 100%;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.45);
  }

  .modal-close {
    position: absolute;
    top: 24px;
    right: 24px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: none;
    background: rgba(255,255,255,0.92);
    color: #111827;
    font-size: 1.75rem;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 12px 30px rgba(0,0,0,0.2);
  filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3));
}

.gallery-thumbs {
  display: flex;
  gap: 12px;
}

.thumb {
  width: 72px;
  height: 72px;
  border-radius: 12px;
  border: 2px solid transparent;
  background-size: cover;
  background-position: center;
  cursor: pointer;
  transition: all 0.2s;
}

.thumb-active,
.thumb:hover {
  border-color: #e53e3e;
  transform: scale(1.05);
}

/* Product Info */
.info { display: flex; flex-direction: column; gap: 24px; }

.product-title {
  font-family: 'Fraunces', serif;
  font-size: clamp(2rem, 5vw, 3.5rem);
  font-weight: 900;
  line-height: 1.1;
  color: #111827;
  margin: 0;
}

.stats-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.rating-display {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 1.125rem;
  font-weight: 700;
  color: #f59e0b;
}

.rating-stars { font-size: 1.375rem; }

.rating-count { color: #64748b; font-weight: 500; font-size: 0.875rem; }

.sold-badge {
  background: #fef2f2;
  color: #dc2626;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 600;
}

.price-container {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.price {
  font-family: 'Fraunces', serif;
  font-size: clamp(2.25rem, 6vw, 3.75rem);
  font-weight: 900;
  color: #ea580c;
  line-height: 1;
}

.stock-info {
  display: flex;
  gap: 12px;
  align-items: center;
}

.stock {
  color: #64748b;
  font-size: 0.875rem;
}

.category-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  color: #ea580c;
  border: 1px solid rgba(234, 88, 12, 0.2);
}

/* Actions */
.actions { display: flex; flex-direction: column; gap: 20px; }

.quantity-input {
  display: flex;
  align-items: center;
  gap: 12px;
  max-width: 160px;
}

.qty-btn {
  width: 44px;
  height: 44px;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  background: #fff;
  font-size: 1.25rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.qty-btn:hover:not(:disabled) { border-color: #e53e3e; background: #fef2f2; }
.qty-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.quantity-input input {
  width: 60px;
  height: 44px;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  text-align: center;
  font-size: 1.125rem;
  font-weight: 700;
}

.action-buttons {
  display: flex;
  gap: 12px;
}

.btn-cart,
.btn-buy {
  flex: 1;
  padding: 14px 24px;
  border-radius: 12px;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.95rem;
  font-weight: 700;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-cart {
  background: #f8fafc;
  color: #374151;
  border: 2px solid #e2e8f0;
}

.btn-cart:hover { border-color: #e53e3e; background: #fef2f2; transform: translateY(-1px); }

.btn-buy {
  background: linear-gradient(135deg, #ea580c, #c2410c);
  color: white;
  box-shadow: 0 4px 16px rgba(234, 88, 12, 0.3);
}

.btn-buy:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(234, 88, 12, 0.4);
}

.btn-buy:disabled { opacity: 0.6; cursor: not-allowed; }

/* Seller Section */
.seller-section {
  background: #fff;
  border-radius: 24px;
  padding: 32px;
  margin: 48px 0;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 16px rgba(0,0,0,0.05);
}

.seller-card {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 20px;
  align-items: center;
  margin-bottom: 24px;
}

.seller-avatar {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: 800;
  color: white;
  overflow: hidden;
}

.seller-avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.seller-title-row {
  margin: 0 0 4px 0;
  font-size: 1.25rem;
  font-weight: 800;
  color: #111827;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
}

.seller-premium-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 0.65rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #92400e;
  background: linear-gradient(135deg, #fffbeb, #fef3c7);
  border: 1px solid #fde68a;
  padding: 3px 8px;
  border-radius: 999px;
  vertical-align: middle;
}

.seller-premium-badge i {
  font-size: 0.75rem;
  color: #d97706;
}

.seller-status {
  background: #ecfdf5;
  color: #059669;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
}

.seller-kelas {
  color: #64748b;
  font-size: 0.875rem;
  margin-top: 4px;
}

.seller-actions {
  display: flex;
  flex-direction: column;
  gap: 12px;
  min-width: 160px;
}

.chat-btn {
  background: #f0f9ff;
  color: #0369a1;
  border: 2px solid #bae6fd;
  padding: 10px 20px;
  border-radius: 12px;
  font-weight: 700;
  cursor: pointer;
  position: relative;
}

.chat-btn span {
  position: absolute;
  top: -4px;
  right: -4px;
  background: #ef4444;
  color: white;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  font-size: 0.75rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
}

.visit-store {
  background: #f8fafc;
  color: #374151;
  padding: 10px 20px;
  border-radius: 12px;
  font-weight: 700;
  text-decoration: none;
  text-align: center;
  border: 2px solid #e2e8f0;
  transition: all 0.2s;
}

.visit-store:hover { border-color: #e53e3e; color: #e53e3e; }

.ask-seller {
  width: 100%;
  background: linear-gradient(135deg, #f8fafc, #f1f5f9);
  border: 2px dashed #cbd5e1;
  color: #475569;
  padding: 16px;
  border-radius: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.ask-seller:hover { border-style: solid; background: #f1f5f9; }

/* Reviews */
.reviews-section {
  background: #fff;
  border-radius: 24px;
  padding: 40px;
  margin: 48px 0;
  border: 1px solid #e2e8f0;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 32px;
}

.section-header h2 {
  font-family: 'Fraunces', serif;
  font-size: 2rem;
  font-weight: 900;
  color: #111827;
  margin: 0;
}

.review-count {
  background: #f8fafc;
  color: #64748b;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.875rem;
}

.reviews-list {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.review-card {
  display: flex;
  gap: 20px;
  padding: 24px;
  background: #f8fafc;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
}

.review-header {
  display: flex;
  gap: 16px;
  align-items: center;
  flex: 1;
  min-width: 0;
}

.review-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.125rem;
  font-weight: 700;
  color: white;
  flex-shrink: 0;
}

.review-author {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.review-name {
  font-weight: 700;
  color: #111827;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.review-rating {
  color: #f59e0b;
  font-weight: 700;
  font-size: 0.875rem;
}

.review-time {
  color: #9ca3af;
  font-size: 0.75rem;
}

.review-text {
  color: #374151;
  line-height: 1.6;
  margin: 12px 0 0 0;
}

/* Recommendations */
.rekomendasi-section {
  background: #fff;
  border-radius: 24px;
  padding: 48px;
  margin: 64px 0;
  border: 1px solid #e2e8f0;
}

.section-title {
  font-family: 'Fraunces', serif;
  font-size: 2.5rem;
  font-weight: 900;
  color: #111827;
  text-align: center;
  margin-bottom: 40px;
}

.rekomendasi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 24px;
}

.rekomendasi-card {
  background: #fff;
  border-radius: 20px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s;
  border: 1px solid #e2e8f0;
}

.rekomendasi-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.rec-image {
  height: 200px;
  position: relative;
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
}

.rec-emoji {
  font-size: 3rem;
  filter: drop-shadow(0 4px 12px rgba(0,0,0,0.4));
}

.rec-info {
  padding: 20px;
}

.rec-info h3 {
  font-weight: 800;
  color: #111827;
  margin: 0 0 8px 0;
  font-size: 1.1rem;
}

.rec-price {
  font-weight: 800;
  color: #ea580c;
  font-size: 1.125rem;
  margin-bottom: 4px;
}

.rec-seller {
  color: #64748b;
  font-size: 0.875rem;
}

.see-more {
  display: inline-block;
  margin: 32px auto 0;
  background: transparent;
  color: #e53e3e;
  font-weight: 700;
  font-size: 1.125rem;
  padding: 14px 32px;
  border: 2px solid #fecaca;
  border-radius: 50px;
  text-decoration: none;
  transition: all 0.2s;
}

.see-more:hover {
  background: #fef2f2;
  transform: translateY(-2px);
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 96px 24px;
  color: #64748b;
}

.empty-icon { font-size: 4rem; margin-bottom: 24px; display: block; }

.empty-state h2 { font-size: 1.5rem; margin-bottom: 16px; color: #374151; }

.btn-back {
  background: #e53e3e;
  color: white;
  padding: 12px 32px;
  border-radius: 12px;
  text-decoration: none;
  font-weight: 700;
  display: inline-block;
}

/* Responsive */
@media (max-width: 1024px) {
  .hero-section { grid-template-columns: 1fr; gap: 32px; }
  .gallery-main { height: 400px; }
}

@media (max-width: 768px) {
  .product-detail { padding: 16px; }
  .hero-section { padding: 24px 0; }
  .seller-card { grid-template-columns: 60px 1fr; gap: 16px; }
  .seller-actions { flex-direction: row; justify-content: flex-end; }
  .reviews-section, .rekomendasi-section { padding: 24px; }
}

@media (max-width: 480px) {
  .stats-row { flex-direction: column; align-items: flex-start; gap: 8px; }
  .quantity-input { max-width: 140px; }
  .action-buttons { flex-direction: column; }
}
</style>

