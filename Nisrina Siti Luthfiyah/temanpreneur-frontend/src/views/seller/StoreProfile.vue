<template>
  <section class="st">
    <header class="st__head">
      <h1 class="st__title">Profil Toko</h1>
      <p class="st__sub">Kelola informasi toko, kelas, logo, dan tampilan.</p>
    </header>

    <div v-if="loading" class="st__loading">
      <p>Memuat data toko...</p>
    </div>

    <div v-else-if="!hasBusiness" class="st__empty-state">
      <div class="st__card st__card--empty">
        <h3 class="st__card-title">Belum ada toko terdaftar</h3>
        <p>Selamat datang, {{ authStore.user?.name || 'Penjual' }}. Isi data di bawah ini untuk mengajukan toko dan mulai menjual produk Anda.</p>

        <div class="st__field">
          <label>Nama Toko</label>
          <input v-model="form.name" placeholder="Contoh: Mama Malya Snack" />
        </div>
        <div class="st__field">
          <label>Deskripsi</label>
          <textarea v-model="form.description" rows="3" placeholder="Ceritakan tentang usaha Anda..."></textarea>
        </div>
        <div class="st__field">
          <label>Kategori</label>
          <select v-model="form.category">
            <option value="fashion">Fashion</option>
            <option value="kuliner">Kuliner</option>
            <option value="kerajinan">Kerajinan</option>
            <option value="digital">Digital</option>
            <option value="aksesoris">Aksesoris</option>
            <option value="lainnya">Lainnya</option>
          </select>
        </div>
        <div class="st__field">
          <label>Kelas</label>
          <textarea v-model="form.address" rows="2" placeholder="XI IPA 1"></textarea>
        </div>
        <div class="st__field">
          <label>No. Telepon</label>
          <input v-model="form.phone" placeholder="08xxxxxxxxx" />
        </div>

        <button @click="save" class="st__btn st__btn--primary" :disabled="loading">
          {{ loading ? 'Mengajukan...' : 'Ajukan Toko Sekarang' }}
        </button>

        <p class="st__info-note">
          Jika Anda sudah pernah mengajukan toko, silakan tunggu verifikasi admin. Setelah disetujui, Anda dapat mengatur profil produk dan blog secara lengkap.
        </p>
      </div>
    </div>

    <div v-else class="st__grid">
      <!-- Toko Info Card -->
      <div class="st__card">
        <h3 class="st__card-title">Informasi Toko</h3>
        <div class="st__field">
          <label>Nama Toko</label>
          <input v-model="form.name" @blur="save" />
        </div>
        <div class="st__field">
          <label>Deskripsi</label>
          <textarea v-model="form.description" rows="3" @blur="save"></textarea>
        </div>
        <div class="st__field">
          <label>Kategori</label>
          <select v-model="form.category" @change="save('category')">
            <option value="fashion">Fashion</option>
            <option value="kuliner">Kuliner</option>
            <option value="kerajinan">Kerajinan</option>
            <option value="digital">Digital</option>
            <option value="aksesoris">Aksesoris</option>
            <option value="lainnya">Lainnya</option>
          </select>
        </div>
        <div class="st__field">
          <label>Kelas</label>
          <textarea v-model="form.address" rows="2" @blur="save" placeholder="XI IPA 1"></textarea>
        </div>
        <div class="st__field">
          <label>No. Telepon</label>
          <input v-model="form.phone" @blur="save" />
        </div>
      </div>

      <!-- Logo & Banner -->
      <div class="st__card">
        <h3 class="st__card-title">Logo & Banner Toko</h3>
        
        <div class="st__upload-group">
          <div class="st__upload">
            <label>Logo Toko</label>
            <div v-if="form.logo" class="st__preview">
              <img :src="logoUrl" alt="Logo" />
              <button @click="form.logo = null; save()" class="st__remove">×</button>
            </div>
            <input type="file" @change="handleLogoUpload" accept="image/*" />
            <small>Ganti logo (JPG, PNG, max 2MB)</small>
          </div>

          <div class="st__upload">
            <label>Banner Toko</label>
            <div v-if="form.banner" class="st__preview st__preview--wide">
              <img :src="bannerUrl" alt="Banner" />
              <button @click="form.banner = null; save()" class="st__remove">×</button>
            </div>
            <input type="file" @change="handleBannerUpload" accept="image/*" />
            <small>Ganti banner (JPG, PNG, max 5MB)</small>
          </div>
        </div>
      </div>

      <!-- Theme & Status -->
      <div class="st__card">
        <h3 class="st__card-title">Tampilan & Status</h3>
        <div class="st__field">
          <label>Warna Tema Toko</label>
          <input type="color" v-model="form.theme_color" @change="save" />
          <small>Contoh: #FF6B35 (orange)</small>
        </div>
        <div class="st__status">
          <div class="st__status-item">
            <span class="st__status-label">Status Toko:</span>
            <span class="st__status-value badge" :class="statusClass">{{ businessStatus }}</span>
          </div>
          <div class="st__status-item" v-if="!isPremium">
            <span class="st__status-label">Akun:</span>
            <span class="badge bg-warning">Basic (upgrade ke premium untuk fitur tambahan)</span>
            <router-link to="/seller/upgrade" class="st__upgrade-btn">Upgrade Premium</router-link>
          </div>
          <div class="st__status-item" v-else>
            <span class="st__status-label">Akun:</span>
            <span class="badge bg-primary">Premium Seller</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Error/Success Messages -->
    <div v-if="message" class="alert" :class="message.type === 'success' ? 'alert-success' : 'alert-danger'">
      {{ message.text }}
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue'
import { useAuthStore } from '@/stores/auth.js'
import { normalizeImageUrl, resolveBusinessLogo } from '@/utils/image'
import businessService from '@/services/business.js'

const authStore = useAuthStore()

const business = ref(null)
const form = ref({
  name: '',
  description: '',
  category: 'lainnya',
  address: '',
  phone: '',
  theme_color: '#111827',
  logo: null,
  banner: null,
})
const loading = ref(false)
const message = ref(null)
const isPremium = ref(false)

const hasBusiness = computed(() => Boolean(business.value && business.value.id))

const logoUrl = computed(() => {
  const v = form.value.logo
  if (!v) return resolveBusinessLogo(null, isPremium.value)
  if (typeof File !== 'undefined' && v instanceof File) return URL.createObjectURL(v)
  return resolveBusinessLogo(v, isPremium.value)
})

const bannerUrl = computed(() => {
  const v = form.value.banner
  if (!v) return '/placeholder-banner.jpg'
  if (typeof File !== 'undefined' && v instanceof File) return URL.createObjectURL(v)
  if (typeof v === 'string') {
    if (v.startsWith('http')) return v
    return normalizeImageUrl(v, '/placeholder-banner.jpg')
  }
  return '/placeholder-banner.jpg'
})

const businessStatus = computed(() => {
  if (!business.value) return 'Belum tersedia'
  return business.value.status === 'approved' ? 'Aktif' : business.value.status === 'pending' ? 'Menunggu' : 'Ditolak'
})

const statusClass = computed(() => {
  if (!business.value) return ''
  return business.value.status === 'approved' ? 'bg-success' : business.value.status === 'pending' ? 'bg-warning' : 'bg-danger'
})

const loadBusiness = async () => {
  loading.value = true
  message.value = null
  try {
    const result = await businessService.getSettings()
    const data = result.data

    if (result.success && data) {
      business.value = data
      form.value = { ...data }
      isPremium.value = data.is_premium
    } else {
      business.value = null
      form.value = {
        name: '',
        description: '',
        category: 'lainnya',
        address: '',
        phone: '',
        theme_color: '#111827',
        logo: null,
        banner: null,
      }
      isPremium.value = false
    }
  } catch (err) {
    message.value = { type: 'error', text: 'Gagal memuat data toko' }
  } finally {
    loading.value = false
  }
}

const save = async (field = null) => {
  message.value = null
  loading.value = true

  // Build payload from the form; all fields are optional on update, but required for creation.
  const payload = field ? { [field]: form.value[field] } : {
    name: form.value.name,
    description: form.value.description,
    category: form.value.category,
    address: form.value.address,
    phone: form.value.phone,
    theme_color: form.value.theme_color,
  }

  try {
    let result
    if (!hasBusiness.value) {
      result = await businessService.createBusiness(payload)
    } else {
      result = await businessService.updateBusiness(business.value.id, payload)
    }

    if (result.success) {
      business.value = result.data
      form.value = { ...result.data }
      isPremium.value = result.data?.is_premium || false

      if (authStore.user?.business?.id === result.data?.id) {
        authStore.user = {
          ...authStore.user,
          business: {
            ...authStore.user.business,
            ...result.data,
          },
        }
        localStorage.setItem('user', JSON.stringify(authStore.user))
      }

      message.value = {
        type: 'success',
        text: hasBusiness.value ? 'Toko berhasil diperbarui' : 'Pengajuan toko berhasil dikirim. Tunggu persetujuan admin.',
      }
      await nextTick()
    } else {
      throw new Error(result.message || 'Gagal menyimpan perubahan')
    }
  } catch (err) {
    message.value = { type: 'error', text: err.message || 'Gagal menyimpan perubahan' }
    if (hasBusiness.value) {
      await loadBusiness()
    }
  } finally {
    loading.value = false
  }
}

const handleLogoUpload = (e) => {
  const file = e.target.files?.[0]
  if (!file) return
  form.value.logo = file
  save('logo')
}

const handleBannerUpload = (e) => {
  const file = e.target.files?.[0]
  if (!file) return
  form.value.banner = file
  save('banner')
}

onMounted(loadBusiness)
</script>

<style scoped>
.st {
  padding: 24px;
  max-width: 1200px;
  margin: 0 auto;
}

.st__head {
  margin-bottom: 24px;
}

.st__title {
  font-size: 1.75rem;
  font-weight: 900;
  color: #111827;
  margin-bottom: 4px;
}

.st__sub {
  color: #6b7280;
  font-size: 0.95rem;
}

.st__grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

.st__card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.st__card-title {
  font-size: 1.125rem;
  font-weight: 800;
  color: #111827;
  margin-bottom: 20px;
  border-bottom: 2px solid #f3f4f6;
  padding-bottom: 12px;
}

.st__field {
  margin-bottom: 20px;
}

.st__field label {
  display: block;
  font-weight: 700;
  color: #374151;
  margin-bottom: 8px;
  font-size: 0.9rem;
}

.st__field input,
.st__field textarea {
  width: 100%;
  border: 1.5px solid #e5e7eb;
  border-radius: 10px;
  padding: 12px 14px;
  font-family: inherit;
  transition: border-color 0.2s, box-shadow 0.2s;
  font-size: 0.95rem;
}

.st__field input:focus,
.st__field textarea:focus {
  outline: none;
  border-color: #f59e0b;
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
}

.st__upload-group {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.st__upload {
  margin-bottom: 20px;
}

.st__upload label {
  display: block;
  font-weight: 700;
  margin-bottom: 12px;
}

.st__preview {
  position: relative;
  border: 2px dashed #d1d5db;
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  background: #f9fafb;
  margin-bottom: 12px;
}

.st__preview img {
  max-width: 100%;
  max-height: 120px;
  border-radius: 8px;
  object-fit: cover;
}

.st__preview--wide img {
  max-height: 160px;
}

.st__remove {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #ef4444;
  color: white;
  border: none;
  cursor: pointer;
  font-size: 16px;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
}

.st__upload input[type="file"] {
  width: 100%;
  padding: 12px;
  border: 2px dashed #d1d5db;
  border-radius: 12px;
  background: #f9fafb;
  cursor: pointer;
}

.st__upload input[type="file"]:hover {
  background: #f3f4f6;
  border-color: #9ca3af;
}

.st__status {
  margin-top: 20px;
  padding: 20px;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

.st__status-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.st__status-item:last-child {
  margin-bottom: 0;
}

.st__status-label {
  font-weight: 600;
  color: #475569;
  font-size: 0.9rem;
}

.st__upgrade-btn {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
  padding: 8px 16px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  font-size: 0.85rem;
  transition: all 0.2s;
}

.st__upgrade-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
}

.alert {
  padding: 12px 16px;
  border-radius: 10px;
  margin-top: 20px;
  font-weight: 500;
}

.alert-success {
  background: #dcfce7;
  color: #166534;
  border: 1px solid #22c55e;
}

.alert-danger {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #ef4444;
}

@media (max-width: 768px) {
  .st__grid {
    grid-template-columns: 1fr;
  }
}
</style>

