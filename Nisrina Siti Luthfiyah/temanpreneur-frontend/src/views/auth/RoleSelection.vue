 <template>
  <div class="auth-wrap">

    <!-- ── KIRI: panel gelap ── -->
    <div class="auth-left">
      <div class="auth-left__top">
        <div class="auth-brand">
          <img :src="logoSrc" alt="TemanPreneur" class="auth-brand__img" />
          <span class="auth-brand__name">TemanPreneur</span>
        </div>
        <div class="auth-left__hero">
          <h1 class="auth-hero-title">Mulai Perjalanan Bisnismu dari Sekolah.</h1>
          <p class="auth-hero-sub">Platform marketplace khusus siswa untuk melatih jiwa kewirausahaan sejak dini.</p>
        </div>
      </div>
      <div class="auth-left__bottom">
        <div class="auth-left__divider"></div>
        <router-link to="/" class="auth-back-btn">
          <div class="auth-back-icon"></div>
          <span>Kembali</span>
        </router-link>
      </div>
    </div>

    <!-- ── KANAN: pilih role ── -->
    <div class="auth-right">
      <div class="auth-form-wrap">

        <div class="auth-form-head">
          <h2 class="auth-form-title">
            {{ isDualRole ? 'Pilih Mode' : 'Daftar Berhasil!' }}
          </h2>
          <p class="auth-form-sub">
            {{ isDualRole
              ? 'Anda dapat berperan sebagai pembeli maupun penjual. Pilih mode yang ingin digunakan sekarang:'
              : 'Pilih role untuk melanjutkan ke platform TemanPreneur.'
            }}
          </p>
        </div>

        <!-- Pilihan role — 2 kartu sesuai mockup -->
        <div class="role-cards">

          <!-- Pembeli -->
          <div
            class="role-card"
            :class="{ 'role-card--active': selectedRole === 'buyer' }"
            @click="selectedRole = 'buyer'"
          >
            <div class="role-card__img">
              <svg width="36" height="36" viewBox="0 0 24 24" fill="none">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="2"/>
                <path d="M16 10a4 4 0 01-8 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
            </div>
            <span class="role-card__label">Pembeli</span>
            <small class="role-card__hint">Belanja produk dari teman-teman sekolah</small>
            <!-- radio hidden tapi tetap ada untuk logika v-model -->
            <input type="radio" value="buyer" v-model="selectedRole" class="role-card__radio"/>
          </div>

          <!-- Penjual -->
          <div
            class="role-card"
            :class="{ 'role-card--active': selectedRole === 'seller' }"
            @click="selectedRole = 'seller'"
          >
            <div class="role-card__img">
              <svg width="36" height="36" viewBox="0 0 24 24" fill="none">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <polyline points="9,22 9,12 15,12 15,22" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
            </div>
            <span class="role-card__label">Penjual</span>
            <small class="role-card__hint">
              Buka toko dan jual produk kreatifmu
              {{ !hasSellerRole ? ' (ajukan dulu ke admin)' : '' }}
            </small>
            <input type="radio" value="seller" v-model="selectedRole" class="role-card__radio"/>
          </div>

        </div>

        <!-- Tombol lanjutkan -->
        <button
          class="auth-submit-btn"
          @click="continueRole"
          :disabled="!selectedRole"
        >
          <span>Lanjutkan</span>
          <div class="auth-submit-icon" v-if="selectedRole"></div>
        </button>

        <div class="auth-divider"></div>

      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { normalizeImageUrl } from '@/utils/image'

export default {
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()

    const roles = authStore.roles

    const hasSellerRole = roles.includes('seller') || roles.includes('seller_premium')

    const isDualRole =
      roles.filter(r => ['buyer', 'seller', 'seller_premium'].includes(r)).length > 1

    const selectedRole = ref(
      isDualRole ? 'buyer' : hasSellerRole ? 'seller' : 'buyer'
    )

    const continueRole = () => {
      authStore.setActiveRole(selectedRole.value)

      if (selectedRole.value === 'buyer') {
        router.push('/buyer/dashboard')

      } else {
        if (hasSellerRole) {
          //  FIX: sudah seller → dashboard
          router.push('/seller/dashboard')
        } else {
          //  belum seller → apply
          router.push('/seller/apply')
        }
      }
    }

    const logoSrc = computed(() => normalizeImageUrl('/storage/logo1.png'))

    return {
      selectedRole,
      continueRole,
      hasSellerRole,
      isDualRole,
      logoSrc,
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap');

.auth-wrap {
  min-height: 100vh;
  display: flex;
  background: #fff5f5;
  font-family: 'Plus Jakarta Sans', sans-serif;
  padding: 40px;
  align-items: center;
  justify-content: center;
}

/* ── Kiri MERAH ── */
.auth-left {
  width: 380px; flex-shrink: 0;
  background: linear-gradient(160deg, #c53030 0%, #9b2c2c 100%);
  border-radius: 20px 0 0 20px;
  padding: 36px 36px 32px;
  min-height: 560px; display: flex; flex-direction: column; justify-content: space-between;
}
.auth-left__top { display: flex; flex-direction: column; gap: 40px; }
.auth-brand { display: flex; align-items: center; gap: 10px; }
.auth-brand__img {
  width: 38px;
  height: 38px;
  object-fit: contain;
  border-radius: 10px;
  background: #fff;
  padding: 6px;
  box-shadow: 0 10px 30px rgba(0,0,0,.15);
}
.auth-brand__name { font-size: 1.15rem; font-weight: 800; color: #fff; letter-spacing: -.01em; }
.auth-hero-title {
  font-size: clamp(1.4rem, 2.5vw, 1.8rem); font-weight: 800;
  color: #fff; line-height: 1.25; margin: 0 0 16px; letter-spacing: -.02em;
}
.auth-hero-sub { font-size: .875rem; color: rgba(255,255,255,.55); line-height: 1.65; margin: 0; }
.auth-left__bottom { display: flex; flex-direction: column; gap: 16px; }
.auth-left__divider { height: 1px; background: rgba(255,255,255,.2); }
.auth-back-btn {
  display: flex; align-items: center; gap: 10px;
  background: none; border: none; cursor: pointer;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: .875rem; font-weight: 600; color: rgba(255,255,255,.7);
  text-decoration: underline; text-underline-offset: 3px; padding: 0;
}
.auth-back-btn:hover { color: #fff; }
.auth-back-icon {
  width: 26px; height: 26px; border-radius: 6px;
  background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25); flex-shrink: 0;
}

/* ── Kanan PUTIH ── */
.auth-right {
  flex: 1; background: #ffffff; border-radius: 0 20px 20px 0;
  min-height: 560px; display: flex; align-items: center; justify-content: center;
  padding: 48px 52px; box-shadow: 6px 0 32px rgba(197,48,48,.12);
}
.auth-form-wrap { width: 100%; max-width: 420px; }
.auth-form-head { margin-bottom: 28px; }
.auth-form-title {
  font-size: clamp(1.6rem, 3vw, 2.1rem); font-weight: 900;
  color: #111827; letter-spacing: -.03em; margin: 0 0 8px;
}
.auth-form-sub { font-size: .875rem; color: #6b7280; line-height: 1.55; margin: 0; }

/* ── Role cards ── */
.role-cards {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
  margin-bottom: 24px;
}
.role-card {
  background: #fff5f5;
  border: 2px solid #fecaca;
  border-radius: 14px;
  padding: 22px 16px 18px;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  transition: all .2s;
  position: relative;
  text-align: center;
}
.role-card:hover { background: #fef2f2; border-color: #f56565; }
.role-card--active {
  border-color: #e53e3e;
  background: #fff5f5;
  box-shadow: 0 0 0 2px #e53e3e, 0 4px 14px rgba(229,62,62,.15);
}
.role-card__img {
  width: 64px; height: 64px; border-radius: 12px;
  background: linear-gradient(135deg, #f56565, #c53030);
  display: flex; align-items: center; justify-content: center;
  color: #fff;
  transition: all .2s;
  box-shadow: 0 3px 10px rgba(229,62,62,.3);
}
.role-card--active .role-card__img {
  background: linear-gradient(135deg, #c53030, #9b2c2c);
  box-shadow: 0 4px 14px rgba(197,48,48,.4);
}
.role-card__label {
  font-size: .92rem; font-weight: 800; color: #111827;
}
.role-card__hint {
  font-size: .72rem; color: #9ca3af; line-height: 1.4;
  display: block;
}
.role-card__radio {
  position: absolute; opacity: 0; width: 0; height: 0;
}

/* Submit */
.auth-submit-btn {
  display: flex; align-items: center; justify-content: center; gap: 10px;
  height: 52px; width: 100%;
  background: linear-gradient(135deg, #f56565, #c53030);
  border: none; border-radius: 12px;
  font-family: 'Plus Jakarta Sans', sans-serif; font-size: .95rem; font-weight: 800;
  color: #fff; cursor: pointer; transition: all .2s; letter-spacing: .01em;
  margin-bottom: 20px; box-shadow: 0 4px 14px rgba(229,62,62,.35);
}
.auth-submit-btn:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(229,62,62,.45); }
.auth-submit-btn:disabled { opacity: .45; cursor: not-allowed; transform: none; box-shadow: none; }
.auth-submit-icon {
  width: 22px; height: 22px; border-radius: 5px;
  background: rgba(255,255,255,.2); border: 1px solid rgba(255,255,255,.3); flex-shrink: 0;
}

.auth-divider { height: 1px; background: #f3f4f6; }

@media (max-width: 820px) {
  .auth-wrap { flex-direction: column; padding: 20px; }
  .auth-left { width: 100%; border-radius: 20px 20px 0 0; min-height: auto; padding: 28px; }
  .auth-left__hero { display: none; }
  .auth-right { border-radius: 0 0 20px 20px; min-height: auto; padding: 32px 20px; box-shadow: none; }
}
@media (max-width: 420px) {
  .role-cards { grid-template-columns: 1fr; }
}
</style>