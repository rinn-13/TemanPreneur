<template>
  <div class="seller-layout">
    <button
      type="button"
      class="seller-mobile-toggle"
      @click="mobileOpen = !mobileOpen"
      aria-label="Menu navigasi"
    >
      <i class="bi" :class="mobileOpen ? 'bi-x-lg' : 'bi-list'"></i>
    </button>
    <div v-if="mobileOpen" class="seller-sidebar-overlay" @click="mobileOpen = false"></div>
    <aside
      class="sidebar"
      :class="{
        'sidebar--collapsed': collapsed && !mobileOpen,
        'sidebar--mobile-open': mobileOpen
      }"
    >
      <!-- Logo / Brand area -->
      <div class="sidebar__brand">
        <div class="sidebar__brand-icon sidebar__brand-icon--logo">
          <img :src="brandLogoUrl" alt="TemanPreneur" class="sidebar__brand-logo-img" />
        </div>
        <span class="sidebar__brand-name" v-if="!collapsed">Toko<em>Saya</em></span>
        <button class="sidebar__toggle" @click="collapsed = !collapsed" :title="collapsed ? 'Perluas' : 'Kecilkan'">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
            <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </div>

      <div class="sidebar__section-label" v-if="!collapsed">MENU</div>
      <div class="sidebar__divider"></div>

      <nav class="sidebar__nav">
        <router-link
          v-for="item in menu"
          :key="item.to"
          :to="item.to"
          class="sidebar__item"
          :class="{ 'sidebar__item--active': isActive(item.to) }"
          :title="collapsed ? item.label : ''"
          @click="mobileOpen = false"
        >
          <span class="sidebar__icon" v-html="item.icon"></span>
          <span class="sidebar__text" v-if="!collapsed">{{ item.label }}</span>
        </router-link>
      </nav>

      <!-- Footer -->
      <div class="sidebar__footer">
        <div class="sidebar__user-footer" :class="{ 'sidebar__user-footer--mini': collapsed }">
          <div class="sidebar__avatar">
            <img v-if="userPhoto" :src="userPhoto" alt="avatar" class="sidebar__avatar-img" />
            <span v-else>{{ userInitial }}</span>
          </div>
          <div v-if="!collapsed" class="sidebar__user-footer-text">
            <p class="sidebar__user-name">{{ userName }}</p>
            <p class="sidebar__user-role">Akun Penjual</p>
          </div>
        </div>

        <router-link to="/seller/upgrade" class="sidebar__upgrade" :class="{ 'sidebar__upgrade--mini': collapsed }">
          <svg v-if="collapsed" width="16" height="16" viewBox="0 0 24 24" fill="none">
            <path d="M12 17l-5-5 1.41-1.41L11 14.17V3h2v11.17l2.59-2.58L17 12l-5 5z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          <span v-else>Upgrade Premium</span>
        </router-link>
      </div>
    </aside>

    <main class="seller-content">
      <router-view />
    </main>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { normalizeImageUrl, resolveAvatar } from '@/utils/image'

export default {
  name: 'SellerLayout',
  setup() {
    const route = useRoute()
    const collapsed = ref(false)
    const mobileOpen = ref(false)

    const brandLogoUrl = computed(() => normalizeImageUrl('/storage/logo1.png'))

    const router = useRouter()
    const authStore = useAuthStore()
    const currentUser = computed(() => authStore.user || {})
    const userName = computed(() => currentUser.value.name || 'Penjual')
    const userPhoto = computed(() => resolveAvatar(currentUser.value, 'seller'))
    const userInitial = computed(() => userName.value.charAt(0).toUpperCase())

    const menu = computed(() => {
      const base = [
      { label: 'Dashboard',                to: '/seller', icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="14" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="3" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="14" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="2"/></svg>' },
      { label: 'Produk Saya',              to: '/seller/produk', icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M3 3h4v4H3z" stroke="currentColor" stroke-width="2"/><path d="M10 3h4v4h-4z" stroke="currentColor" stroke-width="2"/><path d="M17 3h4v4h-4z" stroke="currentColor" stroke-width="2"/><path d="M3 10h4v4H3z" stroke="currentColor" stroke-width="2"/><path d="M10 10h4v4h-4z" stroke="currentColor" stroke-width="2"/><path d="M17 10h4v4h-4z" stroke="currentColor" stroke-width="2"/></svg>' },
      { label: 'Pesanan Masuk',            to: '/seller/pesanan', icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M9 11l3 3L22 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>' },
      { label: 'Analitik',                 to: '/seller/analitik', icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><polyline points="22,12 18,12 15,21 9,3 6,12 2,12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' },
      { label: 'Saldo Dompet',             to: '/seller/saldo', icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z" stroke="currentColor" stroke-width="2"/><circle cx="16" cy="15" r="2" stroke="currentColor" stroke-width="2"/></svg>' },
      { label: 'Blog Toko',               to: '/seller/blog', icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M2 3h6v6H2zM9 3h6v6H9zM16 3h6v6h-6zM2 10h6v6H2zM9 10h6v6H9zM16 10h6v6h-6zM2 17h6v6H2zM9 17h6v6H9zM16 17h6v6h-6z" stroke="currentColor" stroke-width="2"/></svg>' },
      { label: 'Profil & Pengaturan', to: '/seller/pengaturan', icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" stroke="currentColor" stroke-width="2"/></svg>' },
      ]

      // If seller.saldo route is enabled in router, include it; otherwise omit
      try {
        const resolved = router.resolve({ name: 'seller.saldo' })
        if (!resolved || !resolved.matched || !resolved.matched.length || !(resolved.meta && resolved.meta.disabled)) {
          base.splice(4, 0, { label: 'Saldo Dompet', to: '/seller/saldo', icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z" stroke="currentColor" stroke-width="2"/><circle cx="16" cy="15" r="2" stroke="currentColor" stroke-width="2"/></svg>' })
        }
      } catch (e) {}

      return base
    })

    const isActive = (to) => {
      if (to === '/seller') return route.path === '/seller'
      return route.path.startsWith(to)
    }

    return { menu, isActive, collapsed, mobileOpen, brandLogoUrl, userName, userInitial, userPhoto }
  }
}
</script>

<style scoped>
:root {
  --sb-bg: linear-gradient(180deg, #111827 0%, #0d1520 100%);
  --sb-w: 256px;
  --sb-w-col: 74px;
  --red: #e53e3e;
  --red-light: #fff5f5;
  --font: 'Plus Jakarta Sans', sans-serif;
  --display: 'Fraunces', serif;
  --adm-radius: 16px;
}

.seller-layout {
  display: flex;
  min-height: 100vh;
  font-family: var(--font);
  background: #f0f2f8;
}

/* ── Sidebar ── */
.sidebar {
  width: var(--sb-w);
  background: var(--sb-bg);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  display: flex; flex-direction: column;
  padding: 0;
  flex-shrink: 0;
  transition: width .3s cubic-bezier(0.4,0,0.2,1);
  position: sticky; top: 0;
  height: 100vh;
  overflow: hidden;
  border-right: 1px solid rgba(255,255,255,0.06);
  box-shadow: 4px 0 32px rgba(0,0,0,0.18);
}
.sidebar--collapsed { width: var(--sb-w-col); }

/* Brand row */
.sidebar__brand {
  display: flex; align-items: center; gap: 10px;
  padding: 20px 16px 16px;
  border-bottom: 1px solid rgba(255,255,255,0.07);
  flex-shrink: 0;
}
.sidebar__brand-icon {
  width: 38px; height: 38px; border-radius: 12px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 16px rgba(0,0,0,0.2);
  transition: transform .2s ease;
}
.sidebar__brand-icon--logo {
  background: rgba(255,255,255,0.1);
  padding: 5px;
}
.sidebar__brand:hover .sidebar__brand-icon { transform: scale(1.04); }
.sidebar__brand-logo-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
  border-radius: 8px;
}
.sidebar__brand-name {
  flex: 1; font-family: var(--display); font-size: 1.15rem; font-weight: 700;
  color: #fff; letter-spacing: -0.02em; white-space: nowrap;
}
.sidebar__brand-name em { color: #fc8181; font-style: normal; }

.sidebar__toggle {
  width: 30px; height: 30px; margin-left: auto; flex-shrink: 0;
  background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 10px; color: rgba(255,255,255,0.6); cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all .2s ease;
}
.sidebar--collapsed .sidebar__toggle svg { transform: rotate(180deg); }
.sidebar__toggle:hover { background: rgba(255,255,255,0.16); color: #fff; }

.sidebar__section-label {
  font-size: 0.62rem; font-weight: 800; letter-spacing: 0.20em;
  color: rgba(255,255,255,0.25); padding: 20px 20px 8px;
  text-transform: uppercase;
}
.sidebar__divider { height: 1px; background: rgba(255,255,255,0.07); margin: 0 16px 12px; }

.sidebar__nav {
  flex: 1; display: flex; flex-direction: column;
  gap: 3px; padding: 0 10px; overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: rgba(255,255,255,0.1) transparent;
}
.sidebar__nav::-webkit-scrollbar { width: 4px; }
.sidebar__nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 999px; }

.sidebar__item {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 14px; border-radius: 14px;
  color: rgba(255,255,255,0.58); text-decoration: none;
  font-size: 0.855rem; font-weight: 600; white-space: nowrap;
  transition: all .22s cubic-bezier(0.4,0,0.2,1);
  position: relative; overflow: hidden;
}
.sidebar__item::before {
  content: ''; position: absolute; inset: 0; border-radius: 14px;
  background: rgba(255,255,255,0); transition: background .22s;
}
.sidebar__item:hover { color: rgba(255,255,255,0.9); }
.sidebar__item:hover::before { background: rgba(255,255,255,0.07); }
.sidebar__item--active {
  background: linear-gradient(135deg, rgba(229,62,62,0.22), rgba(197,48,48,0.16));
  color: #fff !important;
  box-shadow: 0 8px 24px rgba(229,62,62,0.12), inset 0 1px 0 rgba(255,255,255,0.08);
  border: 1px solid rgba(229,62,62,0.2);
}
.sidebar__item--active::before { background: none; }
.sidebar__icon { flex-shrink: 0; display: flex; color: inherit; width: 16px; height: 16px; }
.sidebar__text { flex: 1; }

/* Footer / User area */
.sidebar__footer {
  padding: 12px 10px 16px;
  border-top: 1px solid rgba(255,255,255,0.07);
  flex-shrink: 0;
}
.sidebar__user-footer {
  display: flex; align-items: center; gap: 12px; margin-bottom: 14px;
}
.sidebar__user-footer--mini { justify-content: center; }
.sidebar__user-footer .sidebar__avatar {
  width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
  color: #fff; background: rgba(255,255,255,0.12); flex-shrink: 0;
}
.sidebar__user-footer .sidebar__avatar-img {
  width: 100%; height: 100%; object-fit: cover; border-radius: 50%; display: block;
}
.sidebar__user-footer-text {
  display: flex; flex-direction: column; gap: 2px;
}
.sidebar__user-name {
  font-size: .85rem; font-weight: 700; color: #fff;
  margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.sidebar__user-role {
  font-size: .72rem; color: rgba(255,255,255,0.62);
  margin: 0;
}
.sidebar__upgrade {
  display: flex; align-items: center; justify-content: center;
  text-align: center; padding: 12px 14px;
  border-radius: 14px;
  border: 1.5px solid rgba(245, 158, 11, .55);
  color: rgba(255,255,255,.85);
  background: rgba(245, 158, 11, .10);
  text-decoration: none;
  font-weight: 700;
  font-size: 0.8rem;
  transition: all .2s;
  white-space: nowrap;
  gap: 8px;
}
.sidebar__upgrade:hover {
  background: rgba(245, 158, 11, .16);
  border-color: rgba(245, 158, 11, .75);
  color: #fff;
}
.sidebar__upgrade--mini {
  padding: 8px;
  border: none;
  background: transparent;
  color: rgba(255,255,255,0.5);
}
.sidebar__upgrade--mini:hover {
  background: rgba(245, 158, 11, .15);
  color: rgba(245, 158, 11, 1);
}

/* ── Content ── */
.seller-content {
  flex: 1;
  min-width: 0;
  overflow-x: hidden;
  padding: 24px 28px 48px;
  font-family: var(--font, 'Plus Jakarta Sans', sans-serif);
  background: #f0f2f8;
}

.seller-mobile-toggle {
  display: none;
  position: fixed;
  top: 12px;
  left: 12px;
  z-index: 320;
  width: 42px;
  height: 42px;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  background: #fff;
  color: #111827;
  box-shadow: 0 4px 16px rgba(0,0,0,.08);
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  cursor: pointer;
}
.seller-sidebar-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.45);
  z-index: 280;
}

@media (max-width: 768px) {
  .seller-mobile-toggle { display: inline-flex; }
  .seller-sidebar-overlay { display: block; }
  .sidebar {
    position: fixed;
    left: 0;
    top: 0;
    height: 100vh;
    width: min(280px, 86vw) !important;
    z-index: 300;
    transform: translateX(-105%);
    transition: transform 0.28s ease;
  }
  .sidebar--mobile-open { transform: translateX(0); }
  .seller-content { padding: 68px 16px 40px; width: 100%; }
}
</style>

