<template>
  <section class="st">
    <header class="st__head">
      <h1 class="st__title">Profil & Pengaturan Toko</h1>
      <p class="st__sub">Atur profil toko, kelas, dan preferensi.</p>
    </header>

    <!-- Alert -->
    <div v-if="error" class="alert alert-danger">
      {{ error }}
    </div>
    <div v-if="successMessage" class="alert alert-success">
      {{ successMessage }}
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="st__grid">
      <div class="skeleton" style="height: 300px;"></div>
      <div class="skeleton" style="height: 300px;"></div>
    </div>

    <!-- Form Grid -->
    <div v-else class="st__grid">
      <!-- Profile Card -->
      <div class="st__card">
        <h3 class="st__card-title"> Profil Toko</h3>
        <form @submit.prevent="saveProfile" class="st__form">
          <div class="st__field">
            <label>Nama Toko *</label>
            <input 
              v-model="business.name" 
              type="text"
              placeholder="Contoh: Mama Malya Snack"
              required
            />
          </div>

          <div class="st__field">
            <label>Deskripsi Toko</label>
            <textarea 
              v-model="business.description"
              rows="4"
              placeholder="Ceritakan singkat tentang usahamu..."
            ></textarea>
          </div>

          <div class="st__field">
            <label>Kategori Usaha</label>
            <select v-model="business.category">
              <option value="fashion">Fashion</option>
              <option value="kuliner">Kuliner</option>
              <option value="kerajinan">Kerajinan</option>
              <option value="digital">Digital</option>
              <option value="aksesoris">Aksesoris</option>
              <option value="lainnya">Lainnya</option>
            </select>
          </div>

          <div class="st__field">
            <label>Telepon/WhatsApp</label>
            <input 
              v-model="business.phone"
              type="tel"
              placeholder="08xxxxxxxxx"
            />
          </div>

          <div class="st__field">
            <label>Kelas</label>
            <textarea 
              v-model="business.address"
              rows="3"
              placeholder="XI IPA 1"
            ></textarea>
          </div>

          <button type="submit" class="st__btn st__btn--primary" :disabled="saving">
            {{ saving ? 'Menyimpan...' : 'Simpan Profil' }}
          </button>
        </form>
      </div>

      <!-- Media & Theme Card -->
      <div class="st__card">
        <h3 class="st__card-title"> Media & Tema</h3>
        <form @submit.prevent="saveMedia">
          <div class="st__field">
            <label>Logo Toko</label>
            <div class="st__upload">
              <div v-if="business.logo || business.logoPreview" class="st__preview">
                <img :src="business.logoPreview || logoSrc(business.logo)" :alt="business.name" @error="onImageError($event, resolveBusinessLogo(null, business.is_premium))" />
                <button type="button" class="st__preview-remove" @click="business.logo = null; business.logoPreview = null; business.logoFile = null">×</button>
              </div>
              <input 
                @change="onLogoSelected"
                type="file"
                accept="image/*"
                class="st__file"
              />
              <small>JPG/PNG, max 2MB</small>
            </div>
          </div>

          <div class="st__field">
            <label>Banner Toko</label>
            <div class="st__upload">
              <div v-if="business.banner || business.bannerPreview" class="st__preview st__preview--wide">
                <img :src="business.bannerPreview || logoSrc(business.banner)" :alt="business.name" />
                <button type="button" class="st__preview-remove" @click="business.banner = null; business.bannerPreview = null; business.bannerFile = null">×</button>
              </div>
              <input 
                @change="onBannerSelected"
                type="file"
                accept="image/*"
                class="st__file"
              />
              <small>JPG/PNG, max 2MB (rekomendasi: 1200x300px)</small>
            </div>
          </div>

          <div v-if="business.is_premium" class="st__field st__field--premium">
            <label>Warna Animasi Profil Premium</label>
            <p class="st__field-hint">Hanya memengaruhi tampilan profil toko premium Anda.</p>
            <div class="st__color-input-group">
              <input
                v-model="business.theme_color"
                type="color"
                class="st__color-input"
              />
              <input
                v-model="business.theme_color"
                type="text"
                placeholder="#f59e0b"
                class="st__color-text"
              />
            </div>
            <div class="st__premium-preview">
              <PremiumBackground
                :primary="business.theme_color || '#f59e0b'"
                :secondary="premiumSecondary"
                :accent="premiumAccent"
              />
              <span class="st__premium-preview-label">Pratinjau animasi latar</span>
            </div>
          </div>

          <button type="submit" class="st__btn st__btn--primary" :disabled="saving">
            {{ saving ? 'Menyimpan...' : 'Simpan Media & Tema' }}
          </button>
        </form>
      </div>
    </div>

    <!-- Business Status Info -->
    <div v-if="!loading && business.id" class="st__status-card">
      <h3>Status Toko</h3>
      <div class="st__status-grid">
        <div class="st__status-item">
          <span class="st__status-label">Status Verifikasi</span>
          <span 
            class="st__status-badge"
            :class="{
              'st__status-badge--approved': business.status === 'approved',
              'st__status-badge--pending': business.status === 'pending',
              'st__status-badge--rejected': business.status === 'rejected'
            }"
          >
            {{ business.status === 'approved' ? ' Terverifikasi' : 
               business.status === 'pending' ? '⏳ Menunggu' : ' Ditolak' }}
          </span>
        </div>
        <div class="st__status-item">
          <span class="st__status-label">Tipe Akun</span>
          <span class="st__status-badge" :class="{ 'st__status-badge--premium': business.is_premium }">
            {{ business.is_premium ? '⭐ Premium' : ' Reguler' }}
          </span>
        </div>
      </div>
      <div class="st__status-info">
        <p v-if="business.status === 'pending'">
           Toko Anda sedang dalam tahap verifikasi. Tim admin akan mereview dalam 1-3 hari kerja.
        </p>
        <p v-if="business.status === 'rejected' && business.rejection_reason">
           Alasan penolakan: {{ business.rejection_reason }}
        </p>
      </div>
    </div>
  </section>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import api from '@/api/axios'
import businessService from '@/services/business.js'
import { normalizeImageUrl, resolveBusinessLogo, onImageError } from '@/utils/image'
import PremiumBackground from '@/components/PremiumBackground.vue'

export default {
  name: 'SellerPengaturanToko',
  components: { PremiumBackground },
  setup() {
    const business = ref({
      id: null,
      name: '',
      description: '',
      category: 'lainnya',
      phone: '',
      address: '',
      logo: null,
      banner: null,
      theme_color: '#111827',
      status: 'pending',
      is_premium: false,
      rejection_reason: null,
    })

    const settings = ref({
      vacation_mode: false,
      email_notifications: true,
      whatsapp_notifications: false,
    })

    const loading = ref(true)
    const saving = ref(false)
    const error = ref('')
    const successMessage = ref('')

    const logoSrc = (path) => resolveBusinessLogo(path, business.value.is_premium)

    const premiumSecondary = computed(() => {
      const hex = business.value.theme_color || '#f59e0b'
      return hex
    })

    const premiumAccent = computed(() => '#7c3aed')

    const fetchBusiness = async () => {
      loading.value = true
      error.value = ''
      try {
        const result = await businessService.getSettings()
        const userBusiness = result.data

        if (!result.success || !userBusiness) {
          error.value = result.message || 'Belum ada toko'
          return
        }

        business.value = {
          id: userBusiness.id,
          name: userBusiness.name || '',
          description: userBusiness.description || '',
          category: userBusiness.category || 'lainnya',
          phone: userBusiness.phone || '',
          address: userBusiness.address || '',
          logo: userBusiness.logo || null,
          banner: userBusiness.banner || null,
          theme_color: userBusiness.theme_color || '#111827',
          status: userBusiness.status || 'pending',
          is_premium: userBusiness.is_premium || false,
          rejection_reason: userBusiness.rejection_reason || null,
        }
      } catch (err) {
        console.error('Fetch business error:', err)
        if (err.response?.status === 404) {
          error.value = 'Belum ada toko terdaftar. Silakan ajukan usaha di menu pendaftaran seller.'
        } else {
          error.value = 'Gagal memuat data toko'
        }
      } finally {
        loading.value = false
      }
    }

    const onLogoSelected = (e) => {
      const file = e.target.files[0]
      if (file) {
        const reader = new FileReader()
        reader.onload = (event) => {
          business.value.logoPreview = event.target.result
        }
        reader.readAsDataURL(file)
        business.value.logoFile = file
      }
    }

    const onBannerSelected = (e) => {
      const file = e.target.files[0]
      if (file) {
        const reader = new FileReader()
        reader.onload = (event) => {
          business.value.bannerPreview = event.target.result
        }
        reader.readAsDataURL(file)
        business.value.bannerFile = file
      }
    }

    const saveProfile = async () => {
      if (!business.value.id) {
        error.value = 'Data toko belum dimuat (ID tidak valid). Muat ulang halaman.'
        return
      }
      saving.value = true
      error.value = ''
      successMessage.value = ''
      try {
        const payload = {
          name: business.value.name,
          description: business.value.description,
          category: business.value.category,
          phone: business.value.phone,
          address: business.value.address,
        }
        await api.put(`/businesses/${business.value.id}`, payload)
        successMessage.value = 'Profil toko berhasil disimpan!'
        setTimeout(() => successMessage.value = '', 3000)
      } catch (err) {
        console.error('Save profile error:', err)
        error.value = err.response?.data?.message || 'Gagal menyimpan profil'
      } finally {
        saving.value = false
      }
    }

    const saveMedia = async () => {
      // Validate ID exists
      if (!business.value.id) {
        error.value = 'ID toko tidak ditemukan'
        return
      }

      saving.value = true
      error.value = ''
      successMessage.value = ''
      
      try {
        const formData = new FormData()
        if (business.value.is_premium) {
          let color = (business.value.theme_color || '#f59e0b').trim()
          if (color && !color.startsWith('#')) color = `#${color}`
          formData.append('theme_color', color)
        }
        
        // Check if files were actually selected (not just preview)
        if (business.value.logoFile instanceof File) {
          formData.append('logo', business.value.logoFile)
        }
        if (business.value.bannerFile instanceof File) {
          formData.append('banner', business.value.bannerFile)
        }
        
        // Use PUT directly, not POST with _method
        const response = await api.put(`/businesses/${business.value.id}`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })
        
        // Update preview URLs from response
        if (response.data.data?.logo) {
          business.value.logo = response.data.data.logo
          business.value.logoPreview = null // Clear preview
          business.value.logoFile = null // Clear file
        }
        if (response.data.data?.banner) {
          business.value.banner = response.data.data.banner
          business.value.bannerPreview = null // Clear preview
          business.value.bannerFile = null // Clear file
        }
        
        successMessage.value = 'Media dan tema berhasil disimpan!'
        setTimeout(() => successMessage.value = '', 3000)
      } catch (err) {
        console.error('Save media error:', err)
        error.value = err.response?.data?.message || 'Gagal menyimpan media'
      } finally {
        saving.value = false
      }
    }

    const saveSettings = async () => {
      saving.value = true
      error.value = ''
      successMessage.value = ''
      try {
        // In real scenario, save to preferences table
        // For now, just show success
        successMessage.value = 'Pengaturan berhasil disimpan!'
        setTimeout(() => successMessage.value = '', 3000)
      } catch (err) {
        error.value = 'Gagal menyimpan pengaturan'
      } finally {
        saving.value = false
      }
    }

    onMounted(fetchBusiness)

    return {
      business,
      settings,
      logoSrc,
      premiumSecondary,
      premiumAccent,
      loading,
      saving,
      error,
      successMessage,
      onLogoSelected,
      onBannerSelected,
      saveProfile,
      saveMedia,
      saveSettings,
    }
  }
}
</script>

<style scoped>
/* Main Container */
.st { padding: 24px 24px 56px; }
.st__head { margin-bottom: 18px; }
.st__title { font-size: 1.5rem; font-weight: 900; color: #111827; }
.st__sub { color: #6b7280; margin-top: 4px; font-size: .9rem; }

/* Alert Messages */
.alert {
  padding: 12px 16px;
  margin-bottom: 16px;
  border-radius: 8px;
  font-size: 0.9rem;
}
.alert-danger {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fecaca;
}
.alert-success {
  background: #dcfce7;
  color: #15803d;
  border: 1px solid #86efac;
}

/* Grid Layout */
.st__grid {
  display: grid;
  grid-template-columns: 1.2fr .8fr;
  gap: 12px;
  margin-bottom: 16px;
}
.st__grid--full {
  grid-template-columns: 1fr;
}

/* Card Styles */
.st__card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  padding: 20px;
}
.st__card-title {
  margin: 0 0 16px;
  font-weight: 900;
  color: #111827;
  font-size: 1.1rem;
}

/* Form Styles */
.st__form { display: flex; flex-direction: column; gap: 16px; }
.st__field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.st__field label {
  font-size: .85rem;
  font-weight: 800;
  color: #6b7280;
}
.st__field input,
.st__field textarea,
.st__field select {
  border: 1.5px solid #e5e7eb;
  border-radius: 10px;
  padding: 10px 12px;
  outline: none;
  font-family: inherit;
  font-size: 0.95rem;
}
.st__field input:focus,
.st__field textarea:focus,
.st__field select:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
.st__field textarea {
  resize: vertical;
}

/* Upload Styles */
.st__upload {
  border: 2px dashed #d1d5db;
  border-radius: 10px;
  padding: 12px;
  text-align: center;
}
.st__file {
  padding: 8px;
  cursor: pointer;
}
.st__preview {
  position: relative;
  margin-bottom: 12px;
  display: inline-block;
  width: 120px;
  height: 120px;
}
.st__preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 8px;
}
.st__preview--wide {
  width: 100%;
  height: 150px;
}
.st__preview--wide img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.st__preview-remove {
  position: absolute;
  top: -8px;
  right: -8px;
  width: 28px;
  height: 28px;
  background: #ef4444;
  border: none;
  border-radius: 50%;
  color: #fff;
  font-size: 1.2rem;
  cursor: pointer;
}

/* Color Input */
.st__color-input-group {
  display: flex;
  gap: 10px;
}
.st__color-input {
  width: 60px;
  height: 40px;
  border: 1.5px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
}
.st__color-text {
  flex: 1;
  width: 100%;
}
.st__field-hint {
  font-size: 0.78rem;
  color: #9ca3af;
  margin: -2px 0 8px;
}
.st__premium-preview {
  position: relative;
  height: 120px;
  border-radius: 12px;
  overflow: hidden;
  margin-top: 12px;
  border: 1px solid #fde68a;
  background: #fffbeb;
}
.st__premium-preview :deep(.premium-bg) {
  position: absolute;
}
.st__premium-preview-label {
  position: absolute;
  bottom: 8px;
  left: 12px;
  font-size: 0.72rem;
  font-weight: 700;
  color: #92400e;
  z-index: 1;
}

/* Button Styles */
.st__btn {
  height: 42px;
  padding: 0 14px;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
  background: #fff;
  color: #6b7280;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.2s;
}
.st__btn--primary {
  border: none;
  background: #111827;
  color: #fff;
}
.st__btn--primary:hover {
  opacity: 0.92;
}
.st__btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Settings Toggles */
.st__settings {
  margin-bottom: 16px;
}
.st__toggle {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  padding: 12px 0;
  border-top: 1px solid #f3f4f6;
  align-items: center;
}
.st__toggle:first-of-type {
  border-top: none;
  padding-top: 6px;
}
.st__toggle-title {
  font-weight: 900;
  color: #111827;
}
.st__toggle-sub {
  font-size: .82rem;
  color: #9ca3af;
  margin-top: 2px;
}

/* Custom Checkbox */
.st__checkbox {
  position: relative;
  cursor: pointer;
  user-select: none;
}
.st__checkbox input {
  display: none;
}
.st__checkbox-mark {
  inline-size: 20px;
  block-size: 20px;
  border: 2px solid #d1d5db;
  border-radius: 4px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}
.st__checkbox input:checked + .st__checkbox-mark {
  background: #111827;
  border-color: #111827;
  color: #fff;
}
.st__checkbox input:checked + .st__checkbox-mark::after {
  content: '';
  font-weight: bold;
}

/* Status Card */
.st__status-card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  padding: 20px;
  margin-top: 20px;
}
.st__status-card h3 {
  margin: 0 0 16px;
  font-weight: 900;
  color: #111827;
}
.st__status-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 16px;
}
.st__status-item {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.st__status-label {
  font-size: 0.85rem;
  font-weight: 700;
  color: #6b7280;
}
.st__status-badge {
  display: inline-flex;
  padding: 6px 12px;
  border-radius: 8px;
  background: #f3f4f6;
  color: #6b7280;
  font-weight: 700;
  width: max-content;
  font-size: 0.9rem;
}
.st__status-badge--approved {
  background: #dcfce7;
  color: #15803d;
}
.st__status-badge--pending {
  background: #fffbeb;
  color: #d97706;
}
.st__status-badge--rejected {
  background: #fee2e2;
  color: #991b1b;
}
.st__status-badge--premium {
  background: #dbeafe;
  color: #1e40af;
}
.st__status-info {
  background: #f9fafb;
  border-left: 4px solid #3b82f6;
  padding: 12px;
  border-radius: 6px;
}
.st__status-info p {
  margin: 0;
  color: #374151;
  font-size: 0.95rem;
}

.skeleton {
  background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
  border-radius: 8px;
}
@keyframes loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

@media (max-width: 900px) {
  .st__grid { grid-template-columns: 1fr; }
  .st__status-grid { grid-template-columns: 1fr; }
}
</style>

<style scoped>
.st{ padding: 24px 24px 56px; }
.st__head{ margin-bottom: 18px; }
.st__title{ font-size: 1.5rem; font-weight: 900; color: #111827; }
.st__sub{ color:#6b7280; margin-top: 4px; font-size: .9rem; }
.st__grid{ display:grid; grid-template-columns: 1.2fr .8fr; gap: 12px; }
.st__card{ background:#fff; border:1px solid #e5e7eb; border-radius: 14px; padding: 14px; }
.st__card-title{ margin: 0 0 10px; font-weight: 900; color:#111827; }
.st__field{ display:flex; flex-direction:column; gap: 6px; margin-bottom: 10px; }
.st__field label{ font-size: .78rem; font-weight: 800; color:#6b7280; }
.st__field input, .st__field textarea{
  border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 10px 12px; outline:none;
  font-family: inherit;
}
.st__field input:focus, .st__field textarea:focus{
  border-color:#fca5a5; box-shadow: 0 0 0 3px rgba(229,62,62,.10);
}
.st__btn{ height: 40px; padding: 0 14px; border-radius: 10px; border: 1px solid #e5e7eb; background:#fff; font-weight: 800; cursor:pointer; }
.st__btn--primary{ border:none; background:#111827; color:#fff; }
.st__toggle{ display:flex; justify-content:space-between; gap: 10px; padding: 12px 0; border-top: 1px solid #f3f4f6; align-items:center; }
.st__toggle:first-of-type{ border-top:none; padding-top: 6px; }
.st__toggle-title{ font-weight: 900; color:#111827; }
.st__toggle-sub{ font-size: .82rem; color:#9ca3af; margin-top: 2px; }
@media (max-width: 900px){ .st__grid{ grid-template-columns: 1fr; } }
</style>

