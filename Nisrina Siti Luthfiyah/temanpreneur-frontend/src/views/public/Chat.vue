<template>
  <div class="chat-page">
    <!-- Header dengan gradient merah yang bold -->
    <header class="chat-header">
      <div class="header-content">
        <h1 class="header-title">Hubungi Seller</h1>
        <p class="header-subtitle">Terhubung langsung dengan penjual favorit Anda via WhatsApp</p>
      </div>
      <div class="header-decoration"></div>
    </header>

    <!-- Search & Filter Section -->
    <div class="search-container">
      <div class="search-wrapper">
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"></circle>
          <path d="m21 21-4.35-4.35"></path>
        </svg>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari nama seller..."
          class="search-input"
        />
      </div>
    </div>

    <!-- Chat List dengan animasi -->
    <div class="chat-list">
      <transition-group name="chat-list" tag="div" class="sellers-container">
        <div
          v-for="seller in filteredSellers"
          :key="seller.id"
          class="seller-card"
          @click="openWhatsApp(seller)"
          @mouseenter="hoveredSeller = seller.id"
          @mouseleave="hoveredSeller = null"
        >
          <!-- Avatar dengan badge status online -->
          <div class="avatar-wrapper">
            <img :src="seller.avatar" :alt="seller.name" class="avatar" />
            <span :class="['status-badge', seller.isOnline ? 'online' : 'offline']"></span>
          </div>

          <!-- Seller Info -->
          <div class="seller-info">
            <div class="seller-header">
              <h3 class="seller-name">{{ seller.name }}</h3>
            </div>
            <p class="seller-category">{{ seller.category }}</p>
            <div class="seller-meta">
            </div>
          </div>

          <!-- Call to Action Button -->
          <div class="action-button">
            <svg viewBox="0 0 24 24" fill="currentColor">
              <path d="M16.6915026,12.4744748 C16.6915026,13.4899474 16.0151852,14.1667506 15.1272231,14.1667506 C14.5051757,14.1667506 14.0237899,13.8424653 13.5584461,13.4744748 L13.5584461,20.0151288 C13.5584461,20.5748663 13.147788,21.0151288 12.5,21.0151288 C11.8522262,21.0151288 11.4411765,20.5748663 11.4411765,20.0151288 L11.4411765,13.4744748 C10.9758327,13.8424653 10.4941644,14.1667506 9.8727799,14.1667506 C8.98484049,14.1667506 8.30851702,13.4899474 8.30851702,12.4744748 C8.30851702,11.4590023 8.98484049,10.7820991 9.8727799,10.7820991 C10.4941644,10.7820991 10.9758327,11.1063844 11.4411765,11.4743748 L11.4411765,4.93371826 C11.4411765,4.37398095 11.8522262,3.93371826 12.5,3.93371826 C13.147788,3.93371826 13.5584461,4.37398095 13.5584461,4.93371826 L13.5584461,11.4743748 C14.0237899,11.1063844 14.5051757,10.7820991 15.1272231,10.7820991 C16.0151852,10.7820991 16.6915026,11.4590023 16.6915026,12.4744748 Z"/>
            </svg>
            WhatsApp
          </div>

          <!-- Hover effect overlay -->
          <div class="card-overlay"></div>
        </div>
      </transition-group>

      <!-- Empty state -->
      <div v-if="filteredSellers.length === 0" class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <circle cx="12" cy="12" r="10"></circle>
          <path d="M12 16v-4M12 8h.01"></path>
        </svg>
        <p>Tidak ada seller yang ditemukan</p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import businessService from '@/services/business.js'
import { resolveBusinessLogo } from '@/utils/image'

export default {
  name: 'ChatPage',
  setup() {
    const searchQuery = ref('')
    const hoveredSeller = ref(null)
    const sellers = ref([])
    const loading = ref(false)

    const filteredSellers = computed(() => {
      if (!searchQuery.value.trim()) {
        return sellers.value
      }
      const query = searchQuery.value.toLowerCase()
      return sellers.value.filter(seller =>
        seller.name.toLowerCase().includes(query) ||
        seller.category?.toLowerCase().includes(query) ||
        seller.phone?.includes(query)
      )
    })

    const loadSellers = async () => {
      loading.value = true
      try {
        const response = await businessService.getAllBusinesses({ per_page: 100 })
        const allSellers = response.data || []
        
        // Filter: hanya seller yang sudah verified, approved/active, dan memiliki nomor WA
        sellers.value = allSellers
          .map(seller => ({
            ...seller,
            phone: seller.phone?.trim() || seller.owner?.phone?.trim() || ''
          }))
          .filter(seller => 
            seller.is_verified && 
            ['approved', 'active'].includes(seller.status) &&
            seller.phone
          )
          .map(seller => ({
            id: seller.id,
            name: seller.name,
            category: seller.category || 'Umum',
            avatar: resolveBusinessLogo(seller.logo, seller.is_premium),
            rating: seller.rating || 4.5,
            reviews: seller.reviews_count || 0,
            isOnline: true,
            responseTime: 'Respons cepat',
            phone: seller.phone
          }))
      } catch (error) {
        console.error('Failed to load sellers:', error)
        sellers.value = []
      } finally {
        loading.value = false
      }
    }

    const openWhatsApp = (seller) => {
      if (!seller.phone) return
      
      const phoneNumber = seller.phone.replace(/\D/g, '')
      const message = `Halo ${seller.name}, saya ingin menghubungi Anda mengenai produk di temanpreneur.`
      const encodedMessage = encodeURIComponent(message)
      
      const whatsappUrl = `https://wa.me/${phoneNumber}?text=${encodedMessage}`
      window.open(whatsappUrl, '_blank')
    }

    onMounted(loadSellers)

    return {
      searchQuery,
      hoveredSeller,
      sellers,
      loading,
      filteredSellers,
      openWhatsApp,
    }
  }
}
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.chat-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* ===== HEADER ===== */
.chat-header {
  position: relative;
  background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
  padding: 60px 20px 40px;
  color: white;
  overflow: hidden;
}

.chat-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: 
    radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 80% -20%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
  pointer-events: none;
}

.header-content {
  position: relative;
  z-index: 2;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.header-title {
  font-size: clamp(28px, 5vw, 42px);
  font-weight: 700;
  margin: 0 0 12px 0;
  letter-spacing: -0.5px;
}

.header-subtitle {
  font-size: 16px;
  margin: 0;
  opacity: 0.95;
  font-weight: 400;
  line-height: 1.5;
}

.header-decoration {
  position: absolute;
  bottom: -50px;
  right: 0;
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
  border-radius: 50%;
  pointer-events: none;
}

/* ===== SEARCH ===== */
.search-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 20px;
}

.search-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 18px;
  width: 20px;
  height: 20px;
  color: #9ca3af;
  pointer-events: none;
  z-index: 1;
}

.search-input {
  width: 100%;
  padding: 16px 18px 16px 48px;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  font-size: 16px;
  font-family: inherit;
  transition: all 0.3s ease;
  background: white;
}

.search-input:focus {
  outline: none;
  border-color: #dc2626;
  box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1);
}

.search-input::placeholder {
  color: #d1d5db;
}

/* ===== SELLERS LIST ===== */
.chat-list {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px 60px;
}

.sellers-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 24px;
  animation: fadeIn 0.6s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* ===== SELLER CARD ===== */
.seller-card {
  position: relative;
  background: white;
  border-radius: 16px;
  padding: 24px;
  cursor: pointer;
  border: 2px solid #f3f4f6;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
  gap: 16px;
  overflow: hidden;
}

.seller-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(220, 38, 38, 0.05) 0%, transparent 100%);
  opacity: 0;
  transition: opacity 0.3s ease;
  pointer-events: none;
}

.seller-card:hover {
  border-color: #dc2626;
  box-shadow: 0 20px 40px rgba(220, 38, 38, 0.15);
  transform: translateY(-8px);
}

.seller-card:hover::before {
  opacity: 1;
}

.card-overlay {
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s ease;
  pointer-events: none;
}

.seller-card:hover .card-overlay {
  left: 100%;
}

/* Avatar Section */
.avatar-wrapper {
  position: relative;
  width: fit-content;
}

.avatar {
  width: 80px;
  height: 80px;
  border-radius: 12px;
  object-fit: cover;
  border: 3px solid #f3f4f6;
  transition: transform 0.3s ease;
}

.seller-card:hover .avatar {
  transform: scale(1.05);
  border-color: #dc2626;
}

.status-badge {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 3px solid white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.status-badge.online {
  background: #10b981;
  animation: pulse 2s infinite;
}

.status-badge.offline {
  background: #d1d5db;
}

@keyframes pulse {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
  }
  50% {
    box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
  }
}

/* Seller Info */
.seller-info {
  flex: 1;
}

.seller-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 8px;
}

.seller-name {
  font-size: 18px;
  font-weight: 700;
  margin: 0;
  color: #1f2937;
  line-height: 1.3;
}

.response-time {
  font-size: 11px;
  background: #fef3c7;
  color: #92400e;
  padding: 4px 10px;
  border-radius: 20px;
  font-weight: 600;
  white-space: nowrap;
  flex-shrink: 0;
}

.seller-category {
  font-size: 14px;
  color: #6b7280;
  margin: 0 0 12px 0;
}

.seller-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #6b7280;
}

.rating {
  color: #d97706;
  font-weight: 600;
}

.reviews {
  color: #9ca3af;
}

/* Action Button */
.action-button {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 14px 20px;
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 700;
  font-size: 15px;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  font-family: inherit;
}

.action-button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.1);
  transition: left 0.3s ease;
}

.action-button:hover {
  background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
  transform: scale(1.05);
}

.action-button:hover::before {
  left: 100%;
}

.action-button svg {
  width: 18px;
  height: 18px;
}

/* Empty State */
.empty-state {
  grid-column: 1 / -1;
  text-align: center;
  padding: 80px 20px;
  color: #9ca3af;
}

.empty-state svg {
  width: 80px;
  height: 80px;
  margin: 0 auto 20px;
  opacity: 0.5;
}

.empty-state p {
  font-size: 18px;
  margin: 0;
}

/* Transition Animations */
.chat-list-enter-active,
.chat-list-leave-active {
  transition: all 0.3s ease;
}

.chat-list-enter-from,
.chat-list-leave-to {
  opacity: 0;
  transform: scale(0.9) translateY(20px);
}

.chat-list-move {
  transition: transform 0.3s ease;
}

/* Responsive */
@media (max-width: 768px) {
  .sellers-container {
    grid-template-columns: 1fr;
    gap: 16px;
  }

  .chat-header {
    padding: 40px 20px 30px;
  }

  .header-title {
    font-size: 28px;
  }

  .header-subtitle {
    font-size: 14px;
  }

  .search-container {
    padding: 30px 20px;
  }

  .seller-card {
    padding: 20px;
  }

  .header-decoration {
    width: 200px;
    height: 200px;
    bottom: -30px;
  }
}

@media (max-width: 480px) {
  .chat-header {
    padding: 30px 16px 20px;
  }

  .header-title {
    font-size: 24px;
  }

  .header-subtitle {
    font-size: 13px;
  }

  .search-input {
    padding: 14px 16px 14px 40px;
    font-size: 14px;
  }

  .seller-card {
    gap: 12px;
    padding: 16px;
  }

  .action-button {
    padding: 12px 16px;
    font-size: 14px;
  }

  .seller-name {
    font-size: 16px;
  }
}
</style>