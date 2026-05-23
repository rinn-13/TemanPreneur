<template>
  <div class="apply-wrapper">
    <div class="apply-container">
      <!-- Header -->
      <div class="apply-header">
        <div class="apply-header__icon">
          <img :src="logoSrc" alt="TemanPreneur" class="apply-header__logo" />
        </div>
        <div class="apply-header__content">
          <h1 class="apply-title">Ajukan Toko Anda</h1>
          <p class="apply-subtitle">Daftarkan toko Anda untuk bergabung dengan TemanPreneur</p>
        </div>
      </div>

      <!-- Description -->
      <p class="apply-desc">Isi formulir di bawah untuk mendaftarkan toko Anda. Tim admin kami akan meninjau pengajuan Anda dalam waktu 1-2 hari kerja.</p>

      <!-- Error Alert -->
      <div v-if="error" class="apply-alert apply-alert--error">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10" stroke-linecap="round" stroke-linejoin="round"/>
          <line x1="12" y1="8" x2="12" y2="12" stroke-linecap="round" stroke-linejoin="round"/>
          <line x1="12" y1="16" x2="12.01" y2="16" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <div class="apply-alert__content">
          <strong>Terjadi Kesalahan</strong>
          <p>{{ error }}</p>
        </div>
        <button type="button" class="apply-alert__close" @click="error = ''">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18" stroke-linecap="round" stroke-linejoin="round"/>
            <line x1="6" y1="6" x2="18" y2="18" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </div>

      <!-- Success Alert -->
      <div v-if="success" class="apply-alert apply-alert--success">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <div class="apply-alert__content">
          <strong>Berhasil!</strong>
          <p>Toko Anda berhasil diajukan. Tunggu persetujuan dari admin kami.</p>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="apply-form">
        <!-- Nama Toko -->
        <div class="apply-field">
          <label class="apply-label">
            <span class="apply-label__icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke-linecap="round" stroke-linejoin="round"/>
                <polyline points="9 22 9 12 15 12 15 22" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
            Nama Toko
          </label>
          <input
            type="text"
            v-model="form.name"
            class="apply-input"
            required
            placeholder="Contoh: Mama Malya Fashion"
            :disabled="loading"
          >
          <small class="apply-hint">Nama toko akan ditampilkan di profil toko Anda</small>
        </div>

        <!-- Deskripsi Toko -->
        <div class="apply-field">
          <label class="apply-label">
            <span class="apply-label__icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
            Deskripsi Toko
          </label>
          <textarea
            v-model="form.description"
            class="apply-textarea"
            rows="5"
            required
            placeholder="Jelaskan apa yang akan Anda jual, keunggulan produk, target market, dll..."
            :disabled="loading"
          ></textarea>
          <small class="apply-hint">Deskripsi yang baik membantu admin mengevaluasi toko Anda</small>
        </div>

        <!-- Kelas -->
        <div class="apply-field">
          <label class="apply-label">
            <span class="apply-label__icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="10" r="3" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
            Kelas
          </label>
          <textarea
            v-model="form.address"
            class="apply-textarea"
            rows="3"
            required
            placeholder="XI IPA 1"
            :disabled="loading"
          ></textarea>
          <small class="apply-hint">Kelas lengkap untuk sekolah Anda</small>
        </div>

        <!-- Nomor WhatsApp -->
        <div class="apply-field">
          <label class="apply-label">
            <span class="apply-label__icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2m0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8"/>
              </svg>
            </span>
            Nomor WhatsApp <span class="apply-optional">(Opsional)</span>
          </label>
          <input
            type="text"
            v-model="form.phone"
            class="apply-input"
            placeholder="08xxxxxxxxxx"
            :disabled="loading"
          >
          <small class="apply-hint">Untuk kemudahan komunikasi dengan admin dan pembeli</small>
        </div>

        <!-- Action Buttons -->
        <div class="apply-actions">
          <button
            type="submit"
            class="apply-btn apply-btn--primary"
            :disabled="loading"
          >
            <svg v-if="!loading" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span v-if="!loading">Ajukan Sekarang</span>
            <span v-else>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="apply-spinner">
                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-dasharray="28 28"/>
              </svg>
              Sedang diproses...
            </span>
          </button>
          <router-link to="/" class="apply-btn apply-btn--secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="19" y1="12" x2="5" y2="12" stroke-linecap="round" stroke-linejoin="round"/>
              <polyline points="12 19 5 12 12 5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Kembali ke Beranda
          </router-link>
        </div>
      </form>

      <!-- Tips Box -->
      <div class="apply-tips">
        <div class="apply-tips__icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10" stroke-linecap="round" stroke-linejoin="round"/>
            <line x1="12" y1="8" x2="12" y2="12" stroke-linecap="round" stroke-linejoin="round"/>
            <line x1="12" y1="16" x2="12.01" y2="16" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div>
          <strong class="apply-tips__title">Tips Pengajuan:</strong>
          <ul class="apply-tips__list">
            <li>Berikan deskripsi yang jelas, detail, dan menarik</li>
            <li>Jelaskan keunggulan dan target market toko Anda</li>
            <li>Gunakan nomor WhatsApp yang aktif dan mudah dihubungi</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'
import { normalizeImageUrl } from '@/utils/image'

export default {
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()
    const form = ref({ name: '', description: '', phone: '', address: '' })
    const loading = ref(false)
    const error = ref('')
    const success = ref(false)

    onMounted(async () => {
      // Check if user already has a business
      if (!authStore.isLoggedIn) {
        router.push('/login')
        return
      }
      
      if (!['seller', 'seller_premium'].includes(authStore.role)) {
        // Jika user ini sudah punya usaha, redirect ke dashboard
        try {
          const { data } = await api.get('/user')
          if (data.business && data.business.id) {
            alert('Anda sudah memiliki usaha')
            if (data.business.status === 'approved') {
              router.push('/seller')
            } else {
              router.push('/seller/dashboard')
            }
          }
        } catch (err) {
          console.error(err)
        }
      }
    })

    const logoSrc = computed(() => normalizeImageUrl('/storage/logo1.png'))

    const submit = async () => {
      error.value = ''
      success.value = false
      loading.value = true

      try {
        const response = await api.post('/businesses', {
          name: form.value.name,
          description: form.value.description,
          phone: form.value.phone || null,
          address: form.value.address,
        })

        success.value = true
        form.value = { name: '', description: '', phone: '', address: '' }

        // Redirect ke status page untuk melihat persetujuan
        setTimeout(() => {
          router.push('/seller/status')
        }, 2000)
      } catch (err) {
        const errorMsg = err.response?.data?.message || err.message || 'Terjadi kesalahan saat mengirim data'
        error.value = errorMsg
        console.error('Submit error:', err)
      } finally {
        loading.value = false
      }
    }

    return { form, error, loading, success, submit, logoSrc }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.apply-wrapper {
  min-height: 100vh;
  background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
  padding: 40px 20px;
  font-family: 'Plus Jakarta Sans', sans-serif;
}

.apply-container {
  max-width: 620px;
  margin: 0 auto;
  background: white;
  border-radius: 20px;
  border: 1.5px solid #e5e7eb;
  box-shadow: 0 10px 30px rgba(0,0,0,0.08);
  overflow: hidden;
}

/* Header */
.apply-header {
  background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
  padding: 40px 32px;
  display: flex;
  align-items: center;
  gap: 20px;
}

.apply-header__icon {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  background: rgba(255,255,255,0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  flex-shrink: 0;
}
.apply-header__logo {
  width: 44px;
  height: 44px;
  object-fit: contain;
}

.apply-header__content {
  flex: 1;
}

.apply-title {
  font-size: 1.6rem;
  font-weight: 900;
  color: #fff;
  margin: 0 0 6px;
  letter-spacing: -0.01em;
}

.apply-subtitle {
  font-size: 0.9rem;
  color: rgba(255,255,255,0.8);
  margin: 0;
}

/* Content */
.apply-desc {
  padding: 28px 32px 0;
  font-size: 0.95rem;
  color: #6b7280;
  line-height: 1.6;
  margin: 0 0 24px;
}

/* Form */
.apply-form {
  padding: 0 32px 24px;
}

.apply-field {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 24px;
}

.apply-label {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.9rem;
  font-weight: 700;
  color: #111827;
}

.apply-label__icon {
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #e53e3e;
}

.apply-optional {
  font-weight: 500;
  color: #9ca3af;
  font-size: 0.8rem;
}

.apply-input,
.apply-textarea {
  padding: 12px 16px;
  background: #f8fafc;
  border: 1.5px solid #e5e7eb;
  border-radius: 12px;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.95rem;
  color: #111827;
  transition: all 0.2s;
  resize: vertical;
}

.apply-input::placeholder,
.apply-textarea::placeholder {
  color: #d1d5db;
}

.apply-input:focus,
.apply-textarea:focus {
  outline: none;
  border-color: #e53e3e;
  background: #fff;
  box-shadow: 0 0 0 4px rgba(229,62,62,0.1);
}

.apply-textarea {
  font-weight: 500;
  line-height: 1.5;
}

.apply-hint {
  display: block;
  font-size: 0.8rem;
  color: #9ca3af;
}

/* Alerts */
.apply-alert {
  margin-bottom: 20px;
  padding: 16px 20px;
  border-radius: 12px;
  display: flex;
  align-items: flex-start;
  gap: 14px;
  margin-left: 32px;
  margin-right: 32px;
  margin-top: 20px;
}

.apply-alert--error {
  background: #fee2e2;
  border: 1.5px solid #fecaca;
  color: #991b1b;
}

.apply-alert--error svg {
  color: #dc2626;
  flex-shrink: 0;
  margin-top: 2px;
}

.apply-alert--success {
  background: #dcfce7;
  border: 1.5px solid #bbf7d0;
  color: #166534;
}

.apply-alert--success svg {
  color: #22c55e;
  flex-shrink: 0;
}

.apply-alert__content {
  flex: 1;
}

.apply-alert__content strong {
  display: block;
  font-weight: 700;
  margin-bottom: 2px;
}

.apply-alert__content p {
  margin: 0;
  font-size: 0.9rem;
}

.apply-alert__close {
  background: none;
  border: none;
  color: inherit;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0.7;
  transition: opacity 0.2s;
  flex-shrink: 0;
}

.apply-alert__close:hover {
  opacity: 1;
}

/* Buttons */
.apply-actions {
  display: flex;
  gap: 12px;
  flex-direction: column;
}

.apply-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 14px 24px;
  border: none;
  border-radius: 12px;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.95rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.25s;
  text-decoration: none;
  white-space: nowrap;
}

.apply-btn--primary {
  background: linear-gradient(135deg, #f56565, #c53030);
  color: #fff;
  box-shadow: 0 8px 20px rgba(229,62,62,0.3);
}

.apply-btn--primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 12px 28px rgba(229,62,62,0.4);
}

.apply-btn--primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.apply-btn--secondary {
  background: #f9fafb;
  color: #374151;
  border: 1.5px solid #e5e7eb;
}

.apply-btn--secondary:hover {
  background: #f3f4f6;
  border-color: #d1d5db;
  color: #111827;
}

.apply-spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Tips */
.apply-tips {
  margin-top: 32px;
  padding: 24px 32px 32px;
  background: linear-gradient(135deg, #fef2f2 0%, #fef9f3 100%);
  border-top: 1.5px solid #fecaca;
  border-radius: 0 0 20px 20px;
  display: flex;
  align-items: flex-start;
  gap: 16px;
}

.apply-tips__icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: #fed7d7;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #c53030;
  flex-shrink: 0;
}

.apply-tips__title {
  display: block;
  font-size: 0.95rem;
  color: #991b1b;
  margin-bottom: 10px;
}

.apply-tips__list {
  margin: 0;
  padding-left: 20px;
  list-style: disc;
}

.apply-tips__list li {
  font-size: 0.9rem;
  color: #7f1d1d;
  margin-bottom: 6px;
  line-height: 1.5;
}

/* Responsive */
@media (max-width: 640px) {
  .apply-wrapper {
    padding: 20px;
  }
  
  .apply-container {
    border-radius: 16px;
  }
  
  .apply-header {
    padding: 28px 24px;
    gap: 16px;
  }
  
  .apply-header__icon {
    width: 56px;
    height: 56px;
  }
  
  .apply-title {
    font-size: 1.4rem;
  }
  
  .apply-form,
  .apply-desc,
  .apply-alert,
  .apply-tips {
    padding-left: 24px;
    padding-right: 24px;
  }
}
</style>
        const res = await api.get('/user')
        if (res.data?.business) {
          router.replace('/seller')
        }
      } catch {}
    })

    const submit = async () => {
      loading.value = true
      try {
        await api.post('/businesses', form.value)
        alert('Pengajuan berhasil dikirim. Tunggu verifikasi admin.')
        router.push('/')
      } catch (error) {
        alert(error.response?.data?.message || 'Gagal mengajukan usaha')
      } finally {
        loading.value = false
      }
    }

    return { form, loading, submit }
  }
}