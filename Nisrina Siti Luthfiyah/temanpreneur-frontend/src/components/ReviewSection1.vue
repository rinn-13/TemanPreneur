<template>
  <div class="review-section">
    <!-- Leave Review -->
    <div v-if="canReview && !hasReviewed" class="review-form">
      <h3 class="review-title">Beri Ulasan untuk Produk Ini</h3>

      <div class="review-form-content">
        <!-- Rating -->
        <div class="form-group">
          <label class="form-label">Rating</label>
          <div class="rating-input">
            <button
              v-for="i in 5"
              :key="i"
              type="button"
              class="star"
              :class="{ active: rating >= i }"
              @click="rating = i"
            >
              <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Komentar -->
        <div class="form-group">
          <label class="form-label">Komentar (Opsional)</label>
          <textarea
            v-model="comment"
            placeholder="Bagaimana pengalaman Anda dengan produk ini?"
            class="form-textarea"
            rows="4"
          ></textarea>
        </div>

        <!-- Submit -->
        <button
          class="btn btn-primary"
          @click="submitReview"
          :disabled="isSubmitting || !rating"
        >
          <template v-if="!isSubmitting">
            Kirim Ulasan
          </template>
          <template v-else>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="spin">
              <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-dasharray="28 28"/>
            </svg>
            Mengirim...
          </template>
        </button>
      </div>
    </div>

    <!-- Reviews List -->
    <div class="reviews-list">
      <h3 class="review-title">Ulasan Pembeli ({{ reviews.length }})</h3>

      <!-- Empty -->
      <div v-if="reviews.length === 0" class="empty-state">
        <p>Belum ada ulasan untuk produk ini</p>
      </div>

      <!-- Item -->
      <div
        v-for="review in reviews"
        :key="review.id"
        class="review-item"
      >
        <div class="review-header">
          <!-- User -->
          <div class="reviewer-info">
            <div
              class="reviewer-avatar"
              :style="reviewAvatar(review) ? '' : 'background: #e2e8f0'"
            >
              <img
                v-if="reviewAvatar(review)"
                :src="reviewAvatar(review)"
                @error="onReviewAvatarError"
                alt="avatar"
                style="width:100%; height:100%; object-fit:cover; border-radius:50%;"
              />
              <span v-else>
                {{ review.user_name?.charAt(0).toUpperCase() || '?' }}
              </span>
            </div>

            <div>
              <p class="reviewer-name">
                {{ review.user_name || 'Pengguna' }}
              </p>
              <p class="review-date">
                {{ formatDate(review.created_at) }}
              </p>
            </div>
          </div>

          <!-- Rating -->
          <div class="review-rating">
            <div class="stars">
              <span
                v-for="i in 5"
                :key="i"
                class="star-item"
                :class="{ filled: i <= review.rating }"
              >
                
              </span>
            </div>
            <span class="rating-value">{{ review.rating }}/5</span>
          </div>
        </div>

        <!-- Komentar -->
        <p v-if="review.comment" class="review-comment">
          {{ review.comment }}
        </p>

        <!-- Seller Response -->
        <div v-if="review.seller_response" class="seller-response">
          <p class="response-label">Balasan Penjual:</p>
          <p class="response-text">{{ review.seller_response }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useToast } from '@/composables/useToast'
import api from '@/api/axios'
import { normalizeImageUrl, onImageError, PLACEHOLDER_IMAGE } from '@/utils/image'

const props = defineProps({
  orderId: [String, Number],
  productId: [String, Number],
  canReview: { type: Boolean, default: false },
})

const emit = defineEmits(['review-submitted'])

const { success, error: showError } = useToast()

const reviews = ref([])
const rating = ref(0)
const comment = ref('')
const isSubmitting = ref(false)
const isLoading = ref(true)

const hasReviewed = computed(() => {
  return reviews.value.some(r => r.is_by_current_user)
})

const reviewAvatar = (review) => {
  const src = review?.user_photo || review?.photo || review?.avatar || review?.user?.photo
  return normalizeImageUrl(src, null)
}

const onReviewAvatarError = (e) => onImageError(e, PLACEHOLDER_IMAGE)

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

const fetchReviews = async () => {
  isLoading.value = true
  try {
    const { data } = await api.get(`/reviews/product/${props.productId}`)
    reviews.value = data || []
  } catch (error) {
    console.error('Error fetching reviews:', error)
    reviews.value = []
  } finally {
    isLoading.value = false
  }
}

const submitReview = async () => {
  if (!rating.value) {
    showError('Mohon berikan rating')
    return
  }

  isSubmitting.value = true
  try {
    const payload = {
      order_id: props.orderId,
      rating: rating.value,
      comment: comment.value.trim() || null,
    }

    const { data } = await api.post('/reviews', payload)
    success('Ulasan Anda berhasil dikirim terima kasih!')
    
    rating.value = 0
    comment.value = ''
    
    await fetchReviews()
    emit('review-submitted', data)
  } catch (error) {
    showError(error.message)
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  if (props.productId) {
    fetchReviews()
  }
})
</script>

<style scoped>
.review-section {
  margin-top: 24px;
}

.review-form {
  background: #f9fafb;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 32px;
}

.review-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #111827;
  margin: 0 0 20px;
}

.review-form-content {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-label {
  font-weight: 600;
  color: #374151;
  font-size: 0.9rem;
}

.rating-input {
  display: flex;
  gap: 8px;
}

.star {
  background: none;
  border: none;
  cursor: pointer;
  color: #d1d5db;
  font-size: 1.5rem;
  padding: 4px;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.star:hover {
  color: #fbbf24;
  transform: scale(1.1);
}

.star.active {
  color: #fbbf24;
}

.form-textarea {
  padding: 12px;
  border: 1.5px solid #e5e7eb;
  border-radius: 8px;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.9rem;
  resize: vertical;
}

.form-textarea:focus {
  outline: none;
  border-color: #e53e3e;
}

.btn {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.2s;
}

.btn-primary {
  background: linear-gradient(135deg, #e53e3e, #c53030);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(229, 62, 62, 0.3);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.reviews-list {
  margin-top: 32px;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #9ca3af;
}

.review-item {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 16px;
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
  gap: 12px;
}

.reviewer-info {
  display: flex;
  gap: 12px;
  flex: 1;
}

.reviewer-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #e53e3e, #c53030);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  flex-shrink: 0;
}

.reviewer-name {
  font-weight: 600;
  color: #111827;
  margin: 0 0 4px;
}

.review-date {
  font-size: 0.85rem;
  color: #9ca3af;
  margin: 0;
}

.review-rating {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
}

.stars {
  display: flex;
  gap: 2px;
}

.star-item {
  color: #fbbf24;
  font-size: 1.1rem;
}

.star-item:not(.filled) {
  color: #d1d5db;
}

.rating-value {
  font-weight: 600;
  color: #374151;
  font-size: 0.85rem;
}

.review-comment {
  color: #374151;
  line-height: 1.6;
  margin: 0 0 12px;
}

.seller-response {
  background: #fef3c7;
  border-left: 4px solid #fbbf24;
  padding: 12px;
  border-radius: 6px;
  margin-top: 12px;
}

.response-label {
  font-weight: 600;
  color: #92400e;
  font-size: 0.85rem;
  margin: 0 0 4px;
}

.response-text {
  color: #78350f;
  margin: 0;
  font-size: 0.9rem;
  line-height: 1.5;
}

@media (max-width: 640px) {
  .review-header {
    flex-direction: column;
  }

  .review-rating {
    align-items: flex-start;
  }
}
</style>
