<template>
  <div class="buyer-page">
    <div class="buyer-back">
      <button @click="$router.back()" class="back-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Kembali
      </button>
    </div>

    <div class="buyer-body">

      <!-- Banner profil -->
      <div class="pp-banner">
        <div class="pp-banner__avatar-wrap">
          <img 
            v-if="profileImage" 
            :src="normalizeImageUrl(profileImage, '/placeholder-avatar.png')" 
            :alt="form.name"
            class="pp-banner__avatar"
          />
          <div v-else class="pp-banner__avatar" :style="`background:${avatarGrad}`">{{ userInitial }}</div>
          <button 
            @click="triggerPhotoUpload"
            class="pp-banner__change-photo"
            type="button"
            :disabled="uploadingPhoto"
          >
            {{ uploadingPhoto ? '⏳' : '' }}
          </button>
          <input 
            ref="photoInput"
            type="file"
            accept="image/*"
            @change="handlePhotoUpload"
            style="display: none"
          />
        </div>
        <div class="pp-banner__info">
          <h2 class="pp-banner__name">{{ form.name || 'Nama Pembeli' }}</h2>
          <p class="pp-banner__email">{{ form.email }}</p>
          <span class="pp-banner__badge">Akun</span>
        </div>
      </div>

      <!-- 2 kolom -->
      <div class="pp-columns">

        <!-- Kiri: form data diri -->
        <div class="pp-left">

          <div class="pp-card">
            <h3 class="pp-card__title">Data Diri</h3>
            <div class="pp-field">
              <label>Nama Lengkap</label>
              <input v-model="form.name" type="text" placeholder="Masukkan nama lengkap"/>
            </div>
            <div class="pp-field">
              <label>Email</label>
              <input v-model="form.email" type="email" placeholder="email@sekolah.id"/>
            </div>
            <div class="pp-field">
              <label>No. WhatsApp</label>
              <div class="pp-input-prefix">
                <span>+62</span>
                <input v-model="form.phone" type="tel" placeholder="8xxxxxxxxxx"/>
              </div>
            </div>
            <div class="pp-field">
              <label>Kelas</label>
              <input v-model="form.kelas" type="text" placeholder="XI IPA 1"/>
            </div>
            <button class="pp-save-btn" @click="saveProfile" :disabled="saving">
              {{ saving ? 'Menyimpan...' : ' Simpan Perubahan' }}
            </button>
          </div>

          <form class="pp-card" @submit.prevent="changePassword">
            <h3 class="pp-card__title">Ubah Kata Sandi</h3>
            <div class="pp-field">
              <label>Kata Sandi Lama</label>
              <input v-model="pw.old" type="password" placeholder="••••••••"/>
            </div>
            <div class="pp-field">
              <label>Kata Sandi Baru</label>
              <input v-model="pw.new_" type="password" placeholder="••••••••"/>
            </div>
            <div class="pp-field">
              <label>Konfirmasi Kata Sandi Baru</label>
              <input v-model="pw.confirm" type="password" placeholder="••••••••"/>
            </div>
            <button type="submit" class="pp-save-btn pp-save-btn--outline" :disabled="changingPw">
              {{ changingPw ? 'Mengubah...' : ' Ubah Kata Sandi' }}
            </button>
          </form>

        </div>

        <!-- Kanan: statistik & quick links -->
        <div class="pp-right">

          <div class="pp-stat-card">
            <h3 class="pp-card__title">Statistik Akun</h3>
            <div class="pp-stat-grid">
              <div class="pp-stat-item" v-for="s in statItems" :key="s.label" :style="`--c:${s.color}`">
                <div class="pp-stat-icon" v-html="s.icon"></div>
                <strong>{{ s.val }}</strong>
                <span>{{ s.label }}</span>
              </div>
            </div>
          </div>

          <div class="pp-quick-card">
            <h3 class="pp-card__title">Akses Cepat</h3>
            <div class="pp-quick-list">
              <router-link v-for="q in quickLinks" :key="q.path" :to="q.path" class="pp-quick-item">
                <span class="pp-quick-item__icon" :style="`background:${q.color}`" v-html="q.icon"></span>
                <span>{{ q.label }}</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="#9ca3af" stroke-width="2" stroke-linecap="round"/></svg>
              </router-link>
            </div>
          </div>

          <!-- Riwayat Pesanan Terbaru -->
          <div class="pp-card">
            <div class="pp-card__head">
              <h3 class="pp-card__title">Riwayat Pesanan Terbaru</h3>
              <router-link to="/buyer/orders" class="pp-card__link">Lihat semua →</router-link>
            </div>
            <div v-if="loadingOrders" class="pp-loading">
              <div v-for="n in 3" :key="n" class="skeleton" style="height:60px;border-radius:8px;margin-bottom:8px;"></div>
            </div>
            <div v-else-if="!recentOrders.length" class="pp-empty">
              <p>Belum ada pesanan</p>
              <router-link to="/katalog" class="btn btn-sm btn-outline">Mulai Belanja</router-link>
            </div>
            <div v-else class="pp-orders-list">
              <div v-for="order in recentOrders" :key="order.id" class="pp-order-item">
                <div class="pp-order__image">
                  <img 
                    :src="getOrderImage(order)" 
                    :alt="order.items?.[0]?.product?.name || 'Produk'"
                    class="pp-order__img"
                  />
                </div>
                <div class="pp-order__info">
                  <h4 class="pp-order__name">{{ order.items?.[0]?.product?.name || 'Produk' }}</h4>
                  <p class="pp-order__shop">Toko: {{ order.items?.[0]?.product?.business?.name || '-' }}</p>
                  <p class="pp-order__price">Rp {{ Number(order.total_amount || 0).toLocaleString('id-ID') }}</p>
                </div>
                <div class="pp-order__status">
                  <span class="status-badge" :class="`status--${order.status}`">{{ translateStatus(order.status) }}</span>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Toast -->
    <div class="pp-toast" :class="{'pp-toast--show':toast.show,'pp-toast--err':toast.err}">{{ toast.msg }}</div>
  </div>
</template>

<script>
import { ref, computed, reactive, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'
import { normalizeImageUrl } from '@/utils/image'

export default {
  name: 'BuyerProfil',
  setup() {
    const userRaw = JSON.parse(localStorage.getItem('user') || '{}')

    const saving = ref(false)
    const changingPw = ref(false)
    const uploadingPhoto = ref(false)
    const photoInput = ref(null)
    const loadingOrders = ref(true)
    const recentOrders = ref([])

    const authStore = useAuthStore()

    const form = reactive({
      name: userRaw.name || '',
      email: userRaw.email || '',
      phone: userRaw.phone || '',
      kelas: userRaw.class || '',
      photo:
        userRaw.photo ||
        userRaw.avatar ||
        userRaw.photo_url ||
        userRaw.image ||
        userRaw.profile_picture ||
        userRaw.profile_photo ||
        userRaw.profile_photo_path ||
        userRaw.profileImage ||
        ''
    })

    const pw = reactive({
      old: '',
      new_: '',
      confirm: ''
    })

    const toast = reactive({
      show: false,
      msg: '',
      err: false
    })

    const avatarGrad = computed(() =>
      'linear-gradient(135deg,#10b981,#059669)'
    )

    const profileImage = computed(() =>
      form.photo ||
      form.avatar ||
      form.photo_url ||
      form.image ||
      form.profile_picture ||
      form.profile_photo ||
      form.profile_photo_path ||
      form.profileImage ||
      ''
    )

    const userInitial = computed(() =>
      (form.name || 'P').charAt(0).toUpperCase()
    )

    const showToast = (msg, err = false) => {
      toast.msg = msg
      toast.err = err
      toast.show = true
      setTimeout(() => (toast.show = false), 3000)
    }

    const translateStatus = (status) => {
      const map = {
        diproses: 'Diproses',
        dikemas: 'Dikemas',
        diantarkan: 'Diantarkan',
        selesai: 'Selesai',
        dibatalkan: 'Dibatalkan'
      }
      return map[status] || status
    }

    const getOrderImage = (order) => normalizeImageUrl(order.items?.[0]?.product?.image, '/placeholder-product.png')

    // =============================
    //  UPLOAD FOTO (FIXED)
    // =============================
    const triggerPhotoUpload = () => {
      photoInput.value?.click()
    }

    const handlePhotoUpload = async (e) => {
      const file = e.target.files?.[0]
      if (!file) return

      uploadingPhoto.value = true

      try {
        const fd = new FormData()
        fd.append('photo', file)

        const r = await api.post('/user/upload-photo', fd, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })

        const photoUrl = r.data.photo || r.data.user?.photo
        form.photo = photoUrl

        const updatedUser = r.data.user || {
          ...userRaw,
          photo: photoUrl
        }

        authStore.user = {
          ...authStore.user,
          ...updatedUser,
          photo: photoUrl,
        }
        localStorage.setItem('user', JSON.stringify(authStore.user))

        showToast(' Foto profil berhasil diubah')
      } catch (e) {
        showToast(
          e.response?.data?.message || 'Gagal upload foto',
          true
        )
      } finally {
        uploadingPhoto.value = false
        if (photoInput.value) photoInput.value.value = ''
      }
    }

    // =============================
    //  SAVE PROFILE
    // =============================
    const saveProfile = async () => {
      saving.value = true

      try {
        const payload = {
          name: form.name,
          email: form.email,
          phone: form.phone,
          class: form.kelas,
        }

        const r = await api.put('/user/profile', payload)
        const updatedUser = r.data.user || r.data

        authStore.user = {
          ...authStore.user,
          ...updatedUser,
        }
        localStorage.setItem('user', JSON.stringify(authStore.user))

        Object.assign(form, {
          name: authStore.user.name || '',
          email: authStore.user.email || '',
          phone: authStore.user.phone || '',
          kelas: authStore.user.class || '',
          photo: authStore.user.photo || form.photo,
        })

        showToast(' Profil berhasil diperbarui')
      } catch (e) {
        showToast(
          e.response?.data?.message || 'Gagal menyimpan',
          true
        )
      } finally {
        saving.value = false
      }
    }

    // =============================
    //  CHANGE PASSWORD
    // =============================
    const changePassword = async () => {
      if (pw.new_ !== pw.confirm) {
        showToast('Konfirmasi kata sandi tidak cocok', true)
        return
      }

      changingPw.value = true

      try {
        await api.post('/user/change-password', {
          old_password: pw.old,
          new_password: pw.new_
        })

        showToast(' Kata sandi berhasil diubah')

        pw.old = ''
        pw.new_ = ''
        pw.confirm = ''
      } catch (e) {
        showToast(
          e.response?.data?.message ||
            'Gagal mengubah kata sandi',
          true
        )
      } finally {
        changingPw.value = false
      }
    }

    // =============================
    //  FETCH RECENT ORDERS
    // =============================
    const fetchRecentOrders = async () => {
      loadingOrders.value = true
      try {
        const r = await api.get('/orders')
        
        // Handle different response formats
        let ordersData = []
        
        if (r.data?.success && r.data?.data) {
          // Backend response format: { success: true, data: [...], meta: {...} }
          ordersData = Array.isArray(r.data.data) ? r.data.data : []
        } else if (r.data?.data && Array.isArray(r.data.data)) {
          // Alternative format
          ordersData = r.data.data
        } else if (Array.isArray(r.data)) {
          // Direct array
          ordersData = r.data
        } else {
          console.warn('Unexpected orders response format in profile:', r.data)
          ordersData = []
        }

        console.log('Profile fetched orders:', ordersData.length, 'orders')
        recentOrders.value = ordersData.slice(0, 3) // Show only 3 recent orders
      } catch (error) {
        console.error('Failed to fetch recent orders:', error)
        recentOrders.value = []
      } finally {
        loadingOrders.value = false
      }
    }

    // =============================
    //  LOAD USER
    // =============================
    onMounted(async () => {
      try {
        const r = await api.get('/user')
        const userData = r.data.user || r.data

        Object.assign(form, {
          name: userData.name || '',
          email: userData.email || '',
          phone: userData.phone || '',
          kelas: userData.class || '',
          photo:
            userData.photo ||
            userData.avatar ||
            userData.photo_url ||
            userData.image ||
            userData.profile_picture ||
            userData.profile_photo ||
            userData.profile_photo_path ||
            userData.profileImage ||
            ''
        })

        // Fetch recent orders
        await fetchRecentOrders()
      } catch {}
    })

    return {
      form,
      pw,
      saving,
      changingPw,
      uploadingPhoto,
      photoInput,
      toast,
      avatarGrad,
      userInitial,
      loadingOrders,
      recentOrders,
      saveProfile,
      changePassword,
      triggerPhotoUpload,
      handlePhotoUpload,
      translateStatus,
      getOrderImage,
      normalizeImageUrl,
    }
  }
}
</script>

<style scoped>
.buyer-page { min-height:100vh; background:#f4f5f7; font-family:'Plus Jakarta Sans',sans-serif; }
.buyer-back { max-width:1100px; margin:0 auto; padding:20px 28px 0; }
.back-btn { display:flex; align-items:center; gap:7px; background:none; border:none; font-family:'Plus Jakarta Sans',sans-serif; font-size:.95rem; font-weight:700; color:#111827; cursor:pointer; text-decoration:underline; text-underline-offset:3px; }
.back-btn:hover { color:#e53e3e; }
.buyer-body { max-width:1100px; margin:0 auto; padding:24px 28px 64px; display:flex; flex-direction:column; gap:24px; }

/* Banner */
.pp-banner {
  background:linear-gradient(135deg,#d0d5dd,#b0b8c4);
  border-radius:20px; border:1px solid #9ca3af;
  padding:28px 32px;
  display:flex; align-items:center; gap:24px; flex-wrap:wrap;
}
.pp-banner__avatar-wrap { position:relative; flex-shrink:0; }
.pp-banner__avatar {
  width:110px; height:110px; border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  font-size:2.8rem; font-weight:900; color:#fff;
  box-shadow:0 4px 20px rgba(0,0,0,.2);
}
.pp-banner__change-photo {
  position:absolute; bottom:2px; right:2px;
  width:28px; height:28px; border-radius:50%; border:2px solid #fff;
  background:#374151; font-size:.75rem; cursor:pointer;
  display:flex; align-items:center; justify-content:center;
}
.pp-banner__name { font-family:'Fraunces',serif; font-size:1.4rem; font-weight:900; color:#111827; margin-bottom:4px; }
.pp-banner__email { font-size:.85rem; color:#6b7280; margin-bottom:8px; }
.pp-banner__badge { display:inline-flex; align-items:center; gap:5px; padding:4px 12px; background:rgba(16,185,129,.15); border:1px solid rgba(16,185,129,.3); border-radius:100px; font-size:.75rem; font-weight:700; color:#059669; }

/* Columns */
.pp-columns { display:grid; grid-template-columns:1.3fr 1fr; gap:20px; align-items:start; }
.pp-left,.pp-right { display:flex; flex-direction:column; gap:16px; }

/* Card */
.pp-card { background:#d0d5dd; border-radius:18px; border:1px solid #9ca3af; padding:22px 24px; }
.pp-stat-card,.pp-quick-card { background:#d0d5dd; border-radius:18px; border:1px solid #9ca3af; padding:22px 24px; }
.pp-card__title { font-size:.9rem; font-weight:800; color:#111827; margin-bottom:16px; }

/* Fields */
.pp-field { display:flex; flex-direction:column; gap:5px; margin-bottom:14px; }
.pp-field label { font-size:.78rem; font-weight:700; color:#374151; }
.pp-field input,.pp-field textarea,.pp-field select {
  padding:9px 12px; border:1.5px solid #b0b8c4; border-radius:9px;
  font-family:'Plus Jakarta Sans',sans-serif; font-size:.875rem;
  background:#f4f5f7; outline:none; color:#111827;
  transition:border-color .18s;
}
.pp-field input:focus,.pp-field textarea:focus { border-color:#e53e3e; background:#fff; }
.pp-field textarea { resize:vertical; }
.pp-input-prefix { display:flex; border:1.5px solid #b0b8c4; border-radius:9px; overflow:hidden; background:#f4f5f7; transition:border-color .18s; }
.pp-input-prefix:focus-within { border-color:#e53e3e; }
.pp-input-prefix span { padding:9px 12px; background:#e5e7eb; border-right:1.5px solid #b0b8c4; font-size:.85rem; font-weight:700; color:#6b7280; flex-shrink:0; }
.pp-input-prefix input { flex:1; border:none; outline:none; background:#f4f5f7; padding:9px 12px; font-family:'Plus Jakarta Sans',sans-serif; font-size:.875rem; }

/* Buttons */
.pp-save-btn { width:100%; padding:11px; border-radius:10px; background:linear-gradient(135deg,#374151,#111827); color:#fff; border:none; font-family:'Plus Jakarta Sans',sans-serif; font-size:.875rem; font-weight:700; cursor:pointer; transition:all .18s; margin-top:4px; }
.pp-save-btn:hover:not(:disabled) { transform:translateY(-1px); box-shadow:0 4px 14px rgba(0,0,0,.2); }
.pp-save-btn:disabled { opacity:.6; cursor:not-allowed; }
.pp-save-btn--outline { background:transparent; color:#374151; border:1.5px solid #9ca3af; }
.pp-save-btn--outline:hover:not(:disabled) { border-color:#e53e3e; color:#e53e3e; background:#fff5f5; }

/* Stats */
.pp-stat-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.pp-stat-item { background:#f4f5f7; border-radius:12px; padding:14px 12px; text-align:center; border:1px solid #b0b8c4; }
.pp-stat-item .pp-stat-icon { display:flex; justify-content:center; color:var(--c); margin-bottom:6px; }
.pp-stat-item strong { display:block; font-family:'Fraunces',serif; font-size:1.4rem; font-weight:900; color:#111827; }
.pp-stat-item span { font-size:.68rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:.05em; }

/* Quick links */
.pp-quick-list { display:flex; flex-direction:column; gap:4px; }
.pp-quick-item { display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:10px; text-decoration:none; transition:background .15s; }
.pp-quick-item:hover { background:rgba(255,255,255,.5); }
.pp-quick-item__icon { width:30px; height:30px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.pp-quick-item span:nth-child(2) { flex:1; font-size:.84rem; font-weight:600; color:#111827; }

/* Riwayat Pesanan */
.pp-card__head { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
.pp-card__link { font-size:.8rem; color:#e53e3e; text-decoration:none; font-weight:600; }
.pp-card__link:hover { text-decoration:underline; }
.pp-loading { display:flex; flex-direction:column; gap:8px; padding:20px 0; }
.pp-empty { text-align:center; padding:30px 20px; color:#6b7280; }
.pp-orders-list { display:flex; flex-direction:column; gap:12px; }
.pp-order-item { display:flex; align-items:center; gap:12px; padding:12px; background:#f4f5f7; border-radius:10px; border:1px solid #e5e7eb; }
.pp-order__image { flex-shrink:0; }
.pp-order__img { width:50px; height:50px; border-radius:8px; object-fit:cover; border:1px solid #e5e7eb; }
.pp-order__info { flex:1; }
.pp-order__name { font-size:.9rem; font-weight:700; color:#111827; margin-bottom:2px; }
.pp-order__shop { font-size:.75rem; color:#6b7280; margin-bottom:2px; }
.pp-order__price { font-size:.85rem; font-weight:600; color:#059669; }
.pp-order__status { flex-shrink:0; }
.status-badge { padding:4px 8px; border-radius:6px; font-size:.7rem; font-weight:700; text-transform:uppercase; }
.status--diproses { background:#fef3c7; color:#d97706; }
.status--dikemas { background:#dbeafe; color:#2563eb; }
.status--diantarkan { background:#fef3c7; color:#d97706; }
.status--selesai { background:#d1fae5; color:#059669; }
.status--dibatalkan { background:#fee2e2; color:#dc2626; }

/* Toast */
.pp-toast { position:fixed; bottom:28px; right:28px; z-index:2000; padding:13px 22px; border-radius:12px; font-size:.875rem; font-weight:600; box-shadow:0 8px 28px rgba(0,0,0,.15); transform:translateY(20px); opacity:0; transition:all .3s; pointer-events:none; background:#111827; color:#fff; }
.pp-toast--show { transform:translateY(0); opacity:1; }
.pp-toast--err { background:#fff5f5; color:#c53030; border:1px solid #fecaca; }

@media (max-width:768px) {
  .pp-columns { grid-template-columns:1fr; }
  .buyer-body { padding:16px 14px 48px; }
}
</style>