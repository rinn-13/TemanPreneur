<template>
  <div class="auth-wrap">

    <!-- ── KIRI: panel gelap ── -->
    <div class="auth-left">
      <div class="auth-left__top">
        <!-- Logo dengan icon yang lebih bagus -->
        <div class="auth-brand">
          <div class="auth-brand__icon">
            <img :src="brandLogoUrl" alt="TemanPreneur" class="auth-brand__logo" />
          </div>
          <div>
            <span class="auth-brand__name">TemanPreneur</span>
            <p class="auth-brand__sub">Young Entrepreneurs Platform</p>
          </div>
        </div>
        <div class="auth-left__hero">
          <h1 class="auth-hero-title">Mulai Perjalanan Bisnismu dari Sekolah.</h1>
          <p class="auth-hero-sub">Platform marketplace khusus siswa untuk melatih jiwa kewirausahaan sejak dini.</p>
        </div>
      </div>
      <div class="auth-left__bottom">
        <div class="auth-left__divider"></div>
        <button type="button" @click="$router.back()" class="auth-back-btn" aria-label="Kembali">
          <span class="auth-back-icon"><i class="bi bi-arrow-left"></i></span>
          <span>Kembali</span>
        </button>
      </div>
    </div>

    <!-- ── KANAN: form ── -->
    <div class="auth-right">
      <div class="auth-form-wrap">

        <div class="auth-form-head">
          <img :src="brandLogoUrl" alt="TemanPreneur" class="auth-form-logo" />
          <h2 class="auth-form-title">Selamat Datang!</h2>
          <p class="auth-form-sub">Masuk untuk mengelola tokomu atau belanja produk teman.</p>
        </div>

        <!-- Error -->
        <div class="auth-error" v-if="errorMessage">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          {{ errorMessage }}
          <router-link v-if="showRegisterLink" to="/register" class="auth-error__link">
            Daftar terlebih dahulu →
          </router-link>
        </div>

        <form @submit.prevent="handleLogin" class="auth-form">

          <div class="auth-field">
            <label class="auth-label">Email Belajar</label>
            <input
              type="email"
              v-model="form.email"
              autocomplete="username"
              class="auth-input"
              placeholder="nama@smk.belajar.id"
              required
            />
          </div>

          <div class="auth-field">
            <div class="auth-label-row">
              <label class="auth-label">Password</label>
              <router-link to="/forgot-password" class="auth-forgot">Lupa Password?</router-link>
            </div>
            <input
              type="password"
              v-model="form.password"
              autocomplete="current-password"
              class="auth-input"
              placeholder="••••••••"
              required
            />
          </div>

          <button type="submit" class="auth-submit-btn" :disabled="loading">
            <span>{{ loading ? 'Memproses...' : 'Masuk Sekarang' }}</span>
            <div class="auth-submit-icon" v-if="!loading"></div>
            <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" class="auth-spin"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-dasharray="28 28"/></svg>
          </button>

        </form>

        <div class="auth-divider"></div>

        <p class="auth-switch">
          Belum Punya Akun?
          <router-link to="/register" class="auth-switch__link">Daftar Gratis</router-link>
        </p>

      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { normalizeImageUrl } from '@/utils/image'

export default {
  setup() {
    const authStore = useAuthStore()
    const router = useRouter()
    const route = useRoute()

    const form = ref({
      email: '',
      password: ''
    })

    const loading = ref(false)
    const errorMessage = ref('')
    const showRegisterLink = ref(false)
    const brandLogoUrl = computed(() => normalizeImageUrl('logo1.png'))

    const getPostLoginRoute = () => {
      const redirect = route.query.redirect
      if (redirect && typeof redirect === 'string') {
        return redirect
      }

      if (authStore.hasMultipleRoles) {
        return { name: 'role-selection' }
      }

      if (authStore.isAdmin) {
        return { path: '/admin' }
      }

      if (authStore.isSeller) {
        return { path: '/seller' }
      }

      if (authStore.isBuyer) {
        return { path: '/buyer/dashboard' }
      }

      return '/'
    }

    const handleLogin = async () => {
      loading.value = true
      errorMessage.value = ''
      showRegisterLink.value = false

      const result = await authStore.login(form.value)

      if (result.success) {
        sessionStorage.setItem('justLoggedIn', 'true')

        const target = getPostLoginRoute()
        setTimeout(() => {
          router.push(target)
        }, 300)
      } else {
        errorMessage.value = result.message || 'Login gagal'
      }

      loading.value = false
    }

    return {
      form,
      loading,
      errorMessage,
      showRegisterLink,
      brandLogoUrl,
      handleLogin
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Fraunces:wght@700;900&display=swap');

/* ── Wrapper ─────────────────────────────────── */
.auth-wrap {
  min-height: 100vh;
  display: flex;
  background: radial-gradient(circle at top left, rgba(229,62,62,0.08), transparent 26%), #fff5f5;
  font-family: 'Plus Jakarta Sans', sans-serif;
  padding: 36px;
  gap: 24px;
  align-items: center;
  justify-content: center;
}

/* ── Kiri MERAH ──────────────────────────────── */
.auth-left {
  width: min(420px, 100%);
  flex-shrink: 0;
  background: linear-gradient(160deg, #c53030 0%, #9b2c2c 100%);
  border-radius: 28px;
  padding: 40px 34px;
  min-height: 580px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  box-shadow: 0 24px 60px rgba(197,48,48,.15);
}
.auth-left__top { display: flex; flex-direction: column; gap: 38px; }

/* Brand */
.auth-brand {
  display: flex;
  align-items: center;
  gap: 14px;
}

.auth-brand__icon {
  width: 50px;
  height: 50px;
  border-radius: 14px;
  background: rgba(255,255,255,.15);
  border: 2px solid rgba(255,255,255,.3);
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  overflow: hidden;
}
.auth-brand__icon--logo { padding: 6px; background: #fff; }
.auth-brand__logo { width: 100%; height: 100%; object-fit: contain; display: block; }

.auth-brand__name {
  font-size: 1.2rem;
  font-weight: 900;
  color: #fff;
  letter-spacing: -0.01em;
  display: block;
}

.auth-brand__sub {
  font-size: 0.75rem;
  color: rgba(255,255,255,0.65);
  margin: 2px 0 0;
  font-weight: 500;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

/* Hero text */
.auth-hero-title {
  font-size: clamp(1.6rem, 2.6vw, 2.2rem);
  font-weight: 900;
  color: #fff;
  line-height: 1.15;
  margin: 0 0 18px;
  letter-spacing: -.02em;
}
.auth-hero-sub {
  font-size: .95rem;
  color: rgba(255,255,255,.72);
  line-height: 1.75;
  margin: 0;
}

/* Bottom kiri */
.auth-left__bottom { display: flex; flex-direction: column; gap: 18px; }
.auth-left__divider { height: 1px; background: rgba(255,255,255,.22); }
.auth-back-btn {
  display: inline-flex; align-items: center; gap: 12px;
  background: rgba(255,255,255,.08);
  border: 1px solid rgba(255,255,255,.16);
  color: #fff;
  border-radius: 16px;
  padding: 12px 16px;
  font-size: .9rem;
  font-weight: 700;
  cursor: pointer;
  text-decoration: none;
}
.auth-back-btn:hover { background: rgba(255,255,255,.14); }
.auth-back-icon {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  background: rgba(255,255,255,.18);
  border: 1px solid rgba(255,255,255,.25);
  flex-shrink: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
}

/* ── Kanan PUTIH ─────────────────────────────── */
.auth-right {
  flex: 1;
  background: #ffffff;
  border-radius: 28px;
  min-height: 580px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 48px 42px;
  box-shadow: 0 24px 68px rgba(197,48,48,.12);
}
.auth-form-wrap { width: 100%; max-width: 470px; }

/* Head */
.auth-form-head {
  margin-bottom: 28px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.auth-form-logo {
  width: 72px;
  height: 72px;
  object-fit: contain;
  margin-bottom: 16px;
  border-radius: 16px;
  background: #fff;
  padding: 8px;
  box-shadow: 0 4px 16px rgba(229,62,62,0.12);
  border: 1px solid #fecaca;
}
.auth-form-title {
  font-size: clamp(1.8rem, 3vw, 2.4rem);
  font-weight: 900;
  color: #111827;
  letter-spacing: -.03em;
  margin: 0 0 10px;
}
.auth-form-sub { font-size: .95rem; color: #6b7280; line-height: 1.8; margin: 0; }

/* Error */
.auth-error {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 16px;
  border-radius: 16px;
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fecaca;
  margin-bottom: 20px;
  font-size: .9rem;
}
.auth-error__link { margin-left: auto; color: #b91c1c; font-weight: 700; text-decoration: underline; }

/* Form */
.auth-form { display: flex; flex-direction: column; gap: 18px; margin-bottom: 24px; }
.auth-field { display: flex; flex-direction: column; gap: 8px; }
.auth-label { font-size: .85rem; font-weight: 700; color: #374151; letter-spacing: .01em; }
.auth-label-row { display: flex; justify-content: space-between; align-items: center; }
.auth-forgot { font-size: .82rem; font-weight: 500; color: #ef4444; text-decoration: none; }
.auth-forgot:hover { color: #b91c1c; }
.auth-input { height: 52px; padding: 0 18px; background: #f8fafc; border: 1.5px solid #f3f4f6; border-radius: 16px; font-family: 'Plus Jakarta Sans', sans-serif; font-size: .95rem; color: #111827; outline: none; transition: all .2s; }
.auth-input::placeholder { color: #9ca3af; }
.auth-input:focus { border-color: rgba(229,62,62,.45); background: #fff; box-shadow: 0 0 0 4px rgba(248,113,113,.12); }

/* Submit */
.auth-submit-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  height: 54px;
  width: 100%;
  background: linear-gradient(135deg, #f56565, #c53030);
  border: none;
  border-radius: 18px;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: .95rem;
  font-weight: 800;
  color: #fff;
  cursor: pointer;
  transition: all .25s;
  letter-spacing: .01em;
  margin-top: 6px;
  box-shadow: 0 18px 46px rgba(229,62,62,.22);
}
.auth-submit-btn:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 22px 52px rgba(229,62,62,.28); }
.auth-submit-btn:disabled { opacity: .65; cursor: not-allowed; transform: none; box-shadow: none; }
.auth-submit-icon { width: 22px; height: 22px; border-radius: 8px; background: rgba(255,255,255,.2); border: 1px solid rgba(255,255,255,.35); flex-shrink: 0; }

/* Divider */
.auth-divider { height: 1px; background: #f3f4f6; margin-bottom: 20px; }

/* Switch */
.auth-switch { font-size: .93rem; color: #6b7280; text-align: center; margin: 0; }
.auth-switch__link { font-weight: 800; color: #e53e3e; text-decoration: none; }
.auth-switch__link:hover { color: #c53030; }

/* Spinner */
.auth-spin { animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Responsive */
@media (max-width: 900px) {
  .auth-wrap { flex-direction: column; padding: 20px; }
  .auth-left { width: 100%; border-radius: 24px; min-height: auto; padding: 32px; }
  .auth-right { width: 100%; border-radius: 24px; min-height: auto; padding: 32px 24px; box-shadow: none; }
}
</style>