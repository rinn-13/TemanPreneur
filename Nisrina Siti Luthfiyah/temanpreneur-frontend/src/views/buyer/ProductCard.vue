<!-- src/views/buyer/ProductCard.vue -->
<!-- 
  Contoh komponen yang menampilkan produk dengan fitur Favorit
  Menggunakan Axios untuk fetchData dan addToFavorite
-->

<template>
  <div class="product-card">
    <!-- Product Image -->
    <div class="product-image">
      <img :src="normalizeImageUrl(product.image)" :alt="product.name" class="img-fluid">
      
      <!-- Favorite Button -->
      <button 
        class="favorite-btn"
        :class="{ 'is-favorited': isFavoritedNow }"
        @click="toggleFavoriteHandler"
        :disabled="favoriteLoading"
        :title="isFavoritedNow ? 'Hapus dari Favorit' : 'Tambah ke Favorit'"
      >
        <i :class="isFavoritedNow ? 'fa fa-heart' : 'fa fa-heart-o'"></i>
        <span v-if="favoriteLoading" class="spinner"></span>
      </button>
    </div>

    <!-- Product Info -->
    <div class="product-info">
      <h4 class="product-name">{{ product.name }}</h4>
      
      <p class="product-seller">
        <i class="fa fa-store"></i>
        {{ product.business?.name || 'Penjual Tidak Diketahui' }}
      </p>

      <!-- Rating -->
      <div v-if="product.reviews_avg_rating" class="product-rating">
        <div class="stars">
          <i v-for="i in 5" :key="i" 
             class="fa fa-star"
             :class="i <= Math.round(product.reviews_avg_rating) ? 'filled' : 'empty'">
          </i>
        </div>
        <span class="rating-value">{{ product.reviews_avg_rating.toFixed(1) }}</span>
      </div>

      <!-- Price -->
      <div class="product-price">
        <span class="price">Rp {{ formatPrice(product.price) }}</span>
        <span v-if="product.stock < 5" class="stock-warning">
          Stok Terbatas: {{ product.stock }}
        </span>
      </div>

      <!-- Action Buttons -->
      <div class="product-actions">
        <router-link :to="`/products/${product.id}`" class="btn btn-view">
          Lihat Detail
        </router-link>
        <button 
          class="btn btn-cart"
          @click="addToCart"
          :disabled="product.stock === 0"
        >
          <i class="fa fa-shopping-cart"></i>
          Keranjang
        </button>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="favoriteError" class="error-message">
      {{ favoriteError }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useFavoriteStore } from '@/stores/favorite'
import { hasRole } from '@/utils/roles'
import { normalizeImageUrl } from '@/utils/image'

const props = defineProps({
  product: Object
})

const emit = defineEmits(['add-to-cart'])

const favoriteStore = useFavoriteStore()
const favoriteLoading = ref(false)
const favoriteError = ref(null)

const localUser = JSON.parse(localStorage.getItem('user') || 'null')

const isBuyer = computed(() => {
  return hasRole(localUser, 'buyer')
})

const isFavoritedNow = computed(() => {
  return favoriteStore.isFavorite(props.product.id)
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const toggleFavoriteHandler = async () => {
  favoriteLoading.value = true
  favoriteError.value = null

  try {
    await favoriteStore.toggle(props.product.id)
  } catch (error) {
    favoriteError.value =
      error.message === 'Server Backend Belum Dijalankan'
        ? 'Server belum berjalan'
        : error.message || 'Gagal'
  } finally {
    favoriteLoading.value = false
  }
}

const addToCart = () => {
  emit('add-to-cart', props.product)
}

onMounted(() => {
  if (favoriteStore.favorites.length === 0) {
    favoriteStore.fetchFavorites()
  }
})
</script>

<style scoped>
.product-card {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.product-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.product-image {
  position: relative;
  overflow: hidden;
  background: #f5f5f5;
  aspect-ratio: 1;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.favorite-btn {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: white;
  border: 1px solid #ddd;
  cursor: pointer;
  font-size: 18px;
  color: #999;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.favorite-btn:hover {
  border-color: #ff4757;
  color: #ff4757;
  background: #fff5f7;
}

.favorite-btn.is-favorited {
  background: #ff4757;
  color: white;
  border-color: #ff4757;
}

.favorite-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.favorite-btn .spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid #ff4757;
  border-radius: 50%;
  border-top-color: transparent;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.product-info {
  padding: 12px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.product-name {
  margin: 0 0 8px 0;
  font-size: 14px;
  font-weight: 600;
  color: #212529;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.product-seller {
  margin: 0 0 8px 0;
  font-size: 12px;
  color: #999;
}

.product-rating {
  display: flex;
  align-items: center;
  gap: 4px;
  margin-bottom: 8px;
}

.stars {
  font-size: 12px;
}

.stars .fa {
  color: #ffc107;
}

.stars .fa.empty {
  color: #ddd;
}

.rating-value {
  font-size: 12px;
  color: #999;
}

.product-price {
  margin-bottom: 8px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.price {
  font-size: 16px;
  font-weight: 700;
  color: #ff4757;
}

.stock-warning {
  font-size: 11px;
  color: #ff6b6b;
  background: #ffe0e0;
  padding: 2px 6px;
  border-radius: 3px;
}

.product-actions {
  display: flex;
  gap: 8px;
  margin-top: auto;
}

.btn {
  flex: 1;
  padding: 8px 12px;
  border: none;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  text-decoration: none;
  text-align: center;
}

.btn-view {
  background: #f8f9fa;
  color: #212529;
  border: 1px solid #ddd;
}

.btn-view:hover {
  background: #e9ecef;
}

.btn-cart {
  background: #007bff;
  color: white;
}

.btn-cart:hover:not(:disabled) {
  background: #0056b3;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.error-message {
  padding: 8px 12px;
  background: #ffe0e0;
  color: #721c24;
  font-size: 12px;
  border-radius: 4px;
  margin-top: 8px;
}
</style>
