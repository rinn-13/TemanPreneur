<template>
  <div class="ap">
    <div class="ap__head">
      <div>
        <h1 class="ap__title">Approval <span>Premium</span></h1>
        <p class="ap__sub">Review dan setujui permintaan upgrade premium dari pelaku usaha.</p>
      </div>
      <button class="ap__btn ap__btn--ghost" type="button" @click="fetchCandidates">Muat Ulang</button>
    </div>

    <div class="ap__card">
      <div v-if="loading" class="ap__empty">Memuat kandidat...</div>
      <div v-else-if="!candidates.length" class="ap__empty">Tidak ada kandidat upgrade premium saat ini.</div>

      <div v-else class="mk__premium-grid">
        <article v-for="item in candidates" :key="item.id" class="mk__premium-card">
          <div class="mk__premium-card__head">
            <div class="mk__premium-avatar">
              <img :src="item.logoUrl" :alt="item.name" @error="onAvatarError($event)" />
            </div>
            <div>
              <h3 class="mk__premium-name">{{ item.name }}</h3>
              <p class="mk__premium-meta">{{ item.owner?.name || item.user_name || 'Pemilik tidak tersedia' }}</p>
              <p class="mk__premium-meta mk__premium-meta--muted">{{ item.owner?.email || item.user_email || 'Email tidak tersedia' }}</p>
            </div>
          </div>
          <div class="mk__premium-card__body">
            <div class="mk__premium-row"><span>Status</span><strong>{{ item.is_premium ? 'Premium' : 'Reguler' }}</strong></div>
            <div class="mk__premium-row"><span>Kategori</span><strong>{{ item.category_name || item.category || '—' }}</strong></div>
          </div>
          <button
            type="button"
            class="ap__btn ap__btn--primary mk__premium-approve"
            :disabled="item.processing"
            @click="approvePremium(item)"
          >
            {{ item.processing ? 'Menyetujui...' : 'Setujui Premium' }}
          </button>
        </article>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '@/api/axios'
import { resolveBusinessLogo, onImageError } from '@/utils/image'

const candidates = ref([])
const loading = ref(false)

const onAvatarError = (e) => onImageError(e, '/avatars/default-seller.svg')

const fetchCandidates = async () => {
  loading.value = true
  try {
    const response = await api.get('/admin/businesses?status=approved&limit=100')
    const list = response.data?.data || response.data || []
    candidates.value = (Array.isArray(list) ? list : [])
      .filter((item) => !item.is_premium)
      .map((item) => ({
        ...item,
        logoUrl: resolveBusinessLogo(item.logo, false),
        processing: false,
      }))
  } catch (error) {
    console.error('Failed load premium candidates', error)
    candidates.value = []
  } finally {
    loading.value = false
  }
}

const approvePremium = async (item) => {
  item.processing = true
  try {
    await api.post(`/admin/businesses/${item.id}/upgrade`)
    candidates.value = candidates.value.filter((c) => c.id !== item.id)
  } catch (error) {
    console.error('Failed approve premium', error)
  } finally {
    item.processing = false
  }
}

fetchCandidates()
</script>

<style scoped>
.mk__premium-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 16px;
  padding: 4px 0;
}
.mk__premium-card {
  border: 1.5px solid #e5e7eb;
  border-radius: 16px;
  padding: 18px;
  background: #fff;
  display: flex;
  flex-direction: column;
  gap: 14px;
  transition: box-shadow .2s ease, border-color .2s ease;
}
.mk__premium-card:hover {
  border-color: #fca5a5;
  box-shadow: 0 8px 24px rgba(229, 62, 62, 0.08);
}
.mk__premium-card__head {
  display: flex;
  align-items: center;
  gap: 12px;
}
.mk__premium-avatar {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  overflow: hidden;
  flex-shrink: 0;
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
}
.mk__premium-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.mk__premium-name {
  font-size: 1rem;
  font-weight: 800;
  color: #111827;
  margin: 0 0 4px;
}
.mk__premium-meta {
  font-size: .78rem;
  color: #374151;
  margin: 0;
}
.mk__premium-meta--muted { color: #9ca3af; }
.mk__premium-card__body { display: grid; gap: 8px; }
.mk__premium-row {
  display: flex;
  justify-content: space-between;
  font-size: .82rem;
  color: #6b7280;
}
.mk__premium-row strong { color: #111827; }
.mk__premium-approve { width: 100%; margin-top: auto; }
.ap__empty { padding: 32px; text-align: center; color: #9ca3af; }
</style>
