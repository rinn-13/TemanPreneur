<template>
  <div class="admin-layout">

    <button
      type="button"
      class="admin-mobile-toggle"
      @click="mobileOpen = !mobileOpen"
      aria-label="Menu navigasi"
    >
      <i class="bi" :class="mobileOpen ? 'bi-x-lg' : 'bi-list'"></i>
    </button>

    <div
      v-if="mobileOpen"
      class="admin-sidebar-overlay"
      @click="mobileOpen = false"
    ></div>

    <!-- SIDEBAR -->
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
        <span class="sidebar__brand-name" v-if="!collapsed">Teman<em>Preneur</em></span>
        <button class="sidebar__toggle" @click="collapsed = !collapsed" :title="collapsed ? 'Perluas' : 'Kecilkan'">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
            <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </div>

      <div class="sidebar__section-label" v-if="!collapsed">NAVIGASI</div>
      <div class="sidebar__divider"></div>

      <nav class="sidebar__nav">
        <router-link
          v-for="item in menu"
          :key="item.path"
          :to="item.path"
          class="sidebar__item"
          :class="{ 'sidebar__item--active': isActive(item.path) }"
          :title="collapsed ? item.label : ''"
          @click="mobileOpen = false"
        >
          <span class="sidebar__icon" v-html="item.icon"></span>
          <span class="sidebar__text" v-if="!collapsed">{{ item.label }}</span>
          <span class="sidebar__badge" v-if="item.badge && !collapsed">{{ item.badge }}</span>
        </router-link>
      </nav>

      <!-- User info -->
      <div class="sidebar__footer">
        <div class="sidebar__user" :class="{ 'sidebar__user--mini': collapsed }">
          <div class="sidebar__avatar">
            <img v-if="userPhoto" :src="userPhoto" alt="avatar" class="sidebar__avatar-img" />
            <span v-else>{{ userInitial }}</span>
          </div>
          <template v-if="!collapsed">
            <div class="sidebar__user-text">
              <p class="sidebar__user-name">{{ userName }}</p>
              <p class="sidebar__user-role">Super Admin</p>
            </div>
            <button class="sidebar__logout" @click="logout" title="Keluar">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <polyline points="16,17 21,12 16,7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <line x1="21" y1="12" x2="9" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
            </button>
          </template>
        </div>
      </div>
    </aside>

    <!-- KONTEN -->
    <main class="admin-content">
      <router-view />
    </main>

  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { normalizeImageUrl, resolveAvatar } from '@/utils/image'

export default {
  name: 'AdminLayout',
  setup() {
    const route   = useRoute()
    const router  = useRouter()
    const collapsed = ref(false)
    const mobileOpen = ref(false)

    const brandLogoUrl = computed(() => normalizeImageUrl('/storage/logo1.png'))

    const currentUser = computed(() => {
      try {
        return JSON.parse(localStorage.getItem('user') || '{}')
      } catch {
        return {}
      }
    })

    const userName = computed(() => currentUser.value.name || 'Administrator')
    const userInitial = computed(() => userName.value.charAt(0).toUpperCase())
    const userPhoto = computed(() => resolveAvatar(currentUser.value, 'admin'))

    const isActive = (path) => {
      if (path === '/admin') return route.path === '/admin'
      return route.path.startsWith(path)
    }

    const logout = () => {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      router.push('/login')
    }

    const menu = [
      { path:'/admin',             label:'Dashboard',               icon:'<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="14" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="3" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="14" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="2"/></svg>' },
      { path:'/admin/verifikasi',  label:'Verifikasi Toko',        icon:'<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="2"/><polyline points="9,22 9,12 15,12 15,22" stroke="currentColor" stroke-width="2"/></svg>' },
      { path:'/admin/premium-approval', label:'Upgrade Premium',   icon:'<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 17l-5-5 1.41-1.41L11 14.17V3h2v11.17l2.59-2.58L17 12l-5 5z" fill="currentColor"/><path d="M5 20h14v-2H5v2z" fill="currentColor"/></svg>' },
      { path:'/admin/pengguna',    label:'Manajemen Pengguna',      icon:'<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="2"/></svg>' },
      { path:'/admin/konten',      label:'Moderasi Konten',         icon:'<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z" stroke="currentColor" stroke-width="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" stroke="currentColor" stroke-width="2"/></svg>' },
      { path:'/admin/pesanan',     label:'Riwayat Pesanan',         icon:'<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M9 11l3 3L22 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>' },
      { path:'/admin/laporan',     label:'Penanganan Laporan',      icon:'<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="2"/><line x1="12" y1="9" x2="12" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="12" y1="17" x2="12.01" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>' },
      { path:'/admin/performa',    label:'Laporan Performa',        icon:'<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><polyline points="22,12 18,12 15,21 9,3 6,12 2,12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' },
      { path:'/admin/pengaturan',  label:'Pengaturan',icon:'<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" stroke="currentColor" stroke-width="2"/></svg>' },
    ]

    return { collapsed, mobileOpen, menu, userName, userInitial, userPhoto, isActive, logout, brandLogoUrl }
  }
}
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,700;9..144,900&display=swap');

:root {
  --sb-bg: linear-gradient(180deg, #111827 0%, #0d1520 100%);
  --sb-w: 256px;
  --sb-w-col: 74px;
  --red: #e53e3e;
  --red-light: #fff5f5;
  --font: 'Plus Jakarta Sans', sans-serif;
  --display: 'Fraunces', serif;
  --adm-radius: 16px;
  --adm-shadow: 0 4px 24px rgba(0,0,0,0.06), 0 2px 8px rgba(0,0,0,0.03);
  --adm-shadow-md: 0 8px 40px rgba(0,0,0,0.08), 0 4px 12px rgba(0,0,0,0.04);
  --adm-shadow-lg: 0 20px 60px rgba(0,0,0,0.10), 0 8px 20px rgba(0,0,0,0.06);
}

.admin-layout {
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
.sidebar__badge {
  background: var(--red); color: #fff; font-size: 0.62rem; font-weight: 800;
  padding: 3px 9px; border-radius: 999px;
}

/* Footer / User area */
.sidebar__footer {
  padding: 12px 10px 16px;
  border-top: 1px solid rgba(255,255,255,0.07);
  flex-shrink: 0;
}
.sidebar__user {
  display: flex; align-items: center; gap: 10px;
  padding: 12px 14px;
  background: rgba(255,255,255,0.07); border-radius: 14px;
  border: 1px solid rgba(255,255,255,0.08);
  transition: background .2s;
}
.sidebar__user:hover { background: rgba(255,255,255,0.10); }
.sidebar__user--mini { justify-content: center; }
.sidebar__avatar {
  width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
  background: linear-gradient(135deg,#e53e3e,#c53030);
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 0.9rem; font-weight: 800;
  box-shadow: 0 2px 10px rgba(229,62,62,0.3);
}
.sidebar__user-name { font-size: 0.82rem; font-weight: 700; color: rgba(255,255,255,0.9); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sidebar__user-role { font-size: 0.7rem; color: rgba(255,255,255,0.38); }
.sidebar__user-text { flex: 1; min-width: 0; }
.sidebar__logout {
  width: 28px; height: 28px; border: none; cursor: pointer; flex-shrink: 0;
  background: rgba(255,255,255,0.08); border-radius: 10px;
  color: rgba(255,255,255,0.5);
  display: flex; align-items: center; justify-content: center;
  transition: all .2s;
}
.sidebar__logout:hover { background: rgba(229,62,62,0.24); color: #fff; transform: translateX(1px); }

/* ── Admin Content Area ── */
.admin-content {
  flex: 1; min-width: 0; overflow-x: hidden;
  padding: 32px 40px 48px;
}

/* ══════════════════════════════════════════
   SHARED ADMIN PAGE COMPONENTS (ap__*)
══════════════════════════════════════════ */
.ap {
  animation: apFadeIn 0.35s ease;
}
@keyframes apFadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to   { opacity: 1; transform: translateY(0); }
}

.ap__head {
  display: flex; align-items: flex-start; justify-content: space-between;
  flex-wrap: wrap; gap: 20px; margin-bottom: 32px;
}
.ap__title {
  font-family: var(--display); font-size: 2.1rem; font-weight: 900;
  color: #111827; line-height: 1.1; letter-spacing: -0.03em;
}
.ap__title span { color: var(--red); }
.ap__sub { font-size: 0.92rem; color: #6b7280; margin-top: 6px; font-weight: 400; }

/* Card */
.ap__card {
  background: #fff; border-radius: 22px;
  border: 1px solid rgba(229,231,235,0.7); overflow: hidden;
  box-shadow: var(--adm-shadow-md);
  transition: box-shadow .3s ease;
}
.ap__card:hover { box-shadow: var(--adm-shadow-lg); }

.ap__card-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 22px 28px; border-bottom: 1px solid #f3f4f6;
  flex-wrap: wrap; gap: 12px;
}
.ap__card-title { font-size: 0.95rem; font-weight: 800; color: #111827; }

/* Toolbar */
.ap__toolbar {
  display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
  padding: 20px 28px; border-bottom: 1px solid #f9fafb;
}

/* Search */
.ap__search {
  display: flex; align-items: center; gap: 10px;
  flex: 1; min-width: 180px; max-width: 340px;
  height: 44px; padding: 0 16px;
  border: 1.5px solid #e5e7eb; border-radius: 14px;
  background: #fafafa; color: #9ca3af;
  transition: border-color .22s, box-shadow .22s, background .22s;
}
.ap__search:focus-within {
  border-color: rgba(229,62,62,0.4); background: #fff;
  box-shadow: 0 0 0 4px rgba(229,62,62,0.07);
}
.ap__search input {
  flex: 1; border: none; outline: none; background: transparent;
  font-family: var(--font); font-size: 0.9rem; color: #111827;
}
.ap__search input::placeholder { color: #9ca3af; }

.ap__select {
  height: 44px; padding: 0 14px; border: 1.5px solid #e5e7eb;
  border-radius: 14px; font-family: var(--font); font-size: 0.88rem;
  color: #374151; background: #fafafa; outline: none; cursor: pointer;
  transition: border-color .2s, box-shadow .2s;
}
.ap__select:focus { border-color: rgba(229,62,62,0.4); box-shadow: 0 0 0 4px rgba(229,62,62,0.07); }

/* Buttons */
.ap__btn {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 11px 20px; border-radius: 14px; border: none;
  cursor: pointer; font-family: var(--font); font-size: 0.86rem;
  font-weight: 700; transition: all .25s; white-space: nowrap; text-decoration: none;
}
.ap__btn--primary {
  background: linear-gradient(135deg,#f56565,#c53030); color: #fff;
  box-shadow: 0 8px 24px rgba(229,62,62,0.24);
}
.ap__btn--primary:hover { transform: translateY(-2px); box-shadow: 0 14px 32px rgba(229,62,62,0.3); }
.ap__btn--ghost {
  background: #fff; color: #374151;
  border: 1.5px solid #e5e7eb;
}
.ap__btn--ghost:hover { border-color: rgba(229,62,62,0.35); color: #e53e3e; background: #fff5f5; }

/* Badges */
.ap__badge {
  display: inline-flex; align-items: center;
  padding: 4px 12px; border-radius: 999px;
  font-size: 0.71rem; font-weight: 700; letter-spacing: 0.01em;
}
.ap__badge--green  { background: #dcfce7; color: #15803d; }
.ap__badge--yellow { background: #fef3c7; color: #b45309; }
.ap__badge--red    { background: #fee2e2; color: #c53030; }
.ap__badge--blue   { background: #eff6ff; color: #1d4ed8; }
.ap__badge--gray   { background: #f3f4f6; color: #6b7280; }

/* Skeleton loader */
.skeleton {
  background: linear-gradient(90deg,#f3f4f6 25%,#eaecef 50%,#f3f4f6 75%);
  background-size: 200% 100%; border-radius: 14px;
  animation: shimmer 1.6s infinite ease-in-out;
}
@keyframes shimmer { 0%{ background-position:200% 0; } 100%{ background-position:-200% 0; } }

/* Empty state */
.empty-state { text-align: center; padding: 64px 24px; color: #9ca3af; }
.empty-state span { font-size: 3.5rem; display: block; margin-bottom: 16px; opacity: 0.6; }
.empty-state p { font-size: 0.9rem; font-weight: 500; }

/* Table */
.ap__table { width: 100%; }
.ap__th {
  display: grid; padding: 12px 28px;
  background: #fafafa; font-size: 0.68rem;
  font-weight: 800; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.07em;
  border-bottom: 1px solid #f3f4f6;
}
.ap__tr {
  display: grid; padding: 14px 28px; align-items: center;
  border-bottom: 1px solid #f9fafb; transition: background .18s;
}
.ap__tr:hover { background: #fafafa; }
.ap__tr:last-child { border-bottom: none; }

/* Pagination */
.ap__pagination {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 28px; border-top: 1px solid #f3f4f6;
  flex-wrap: wrap; gap: 12px;
}
.ap__pagination-info { font-size: 0.78rem; color: #9ca3af; font-weight: 500; }
.ap__pagination-btns { display: flex; gap: 6px; }
.ap__page-btn {
  width: 36px; height: 36px; border: 1.5px solid #e5e7eb;
  border-radius: 12px; background: #fff; cursor: pointer;
  font-family: var(--font); font-size: 0.82rem; font-weight: 600;
  color: #374151; display: flex; align-items: center; justify-content: center;
  transition: all .2s;
}
.ap__page-btn:hover:not(:disabled) { border-color: rgba(229,62,62,0.45); color: #e53e3e; background: #fff5f5; }
.ap__page-btn--active { background: #e53e3e; border-color: #e53e3e; color: #fff !important; box-shadow: 0 4px 12px rgba(229,62,62,0.28); }
.ap__page-btn:disabled { opacity: 0.35; cursor: not-allowed; }

/* Modal */
.modal-bg {
  position: fixed; inset: 0; background: rgba(0,0,0,0.45);
  backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);
  z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 24px;
}
.modal-box {
  background: #fff; border-radius: 24px; padding: 36px;
  width: 100%; box-shadow: 0 24px 80px rgba(0,0,0,0.22);
  animation: modalIn .28s cubic-bezier(0.16,1,0.3,1);
}
@keyframes modalIn { from{opacity:0;transform:scale(.93) translateY(14px)} to{opacity:1;transform:scale(1) translateY(0)} }
.modal-box__btns { display: flex; gap: 10px; justify-content: flex-end; margin-top: 24px; }

/* Toast */
.ap__toast {
  position: fixed; bottom: 32px; right: 32px; z-index: 2000;
  padding: 14px 24px; border-radius: 16px; font-size: 0.875rem; font-weight: 600;
  box-shadow: 0 12px 40px rgba(0,0,0,0.18); transform: translateY(24px);
  opacity: 0; transition: all .3s cubic-bezier(0.16,1,0.3,1); pointer-events: none;
  background: #111827; color: #fff;
}
.ap__toast--show { transform: translateY(0); opacity: 1; }
.ap__toast--err { background: #fff5f5; color: #c53030; border: 1px solid #fecaca; }

@media (max-width: 1024px) {
  .admin-content { padding: 24px 28px 40px; }
}
.admin-mobile-toggle {
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
  box-shadow: var(--adm-shadow);
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  cursor: pointer;
}
.admin-sidebar-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.45);
  z-index: 280;
  backdrop-filter: blur(2px);
}

@media (max-width: 768px) {
  .admin-mobile-toggle { display: inline-flex; }
  .admin-sidebar-overlay { display: block; }
  .admin-layout { flex-direction: row; }
  .sidebar {
    position: fixed;
    left: 0;
    top: 0;
    height: 100vh;
    width: min(280px, 86vw) !important;
    z-index: 300;
    transform: translateX(-105%);
    transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1);
    flex-direction: column;
    flex-wrap: nowrap;
    padding: 0;
    border-bottom: none;
  }
  .sidebar--mobile-open { transform: translateX(0); }
  .sidebar__brand { border-bottom: 1px solid rgba(255,255,255,0.07); padding: 20px 16px 16px; }
  .sidebar__brand-name { display: block; }
  .sidebar__nav { flex-direction: column; overflow-x: hidden; overflow-y: auto; padding: 12px 10px; }
  .sidebar__item { padding: 11px 14px; }
  .sidebar__text { display: inline; }
  .sidebar__section-label, .sidebar__divider, .sidebar__footer { display: block; }
  .admin-content { padding: 68px 16px 40px; width: 100%; }
  .ap__toolbar { padding: 16px 20px; flex-wrap: wrap; }
  .ap__th, .ap__tr { padding: 12px 20px; }
  .ap__pagination { padding: 14px 20px; flex-wrap: wrap; }
  .ap__card-header { padding: 18px 20px; }
  .ap__title { font-size: 1.7rem; }
  .mk__grid { grid-template-columns: 1fr; }
}
</style>