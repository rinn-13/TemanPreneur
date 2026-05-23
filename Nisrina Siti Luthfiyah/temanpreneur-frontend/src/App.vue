<template>
  <div id="app">
    <!-- Splash Screen -->
    <SplashScreen />

    <!-- ══════════════════════════════════════════════
         NAVBAR — Glass morphism + blur on scroll
    ══════════════════════════════════════════════ -->
    <Teleport v-if="!isShelllessRoute" to="body">
    <nav
      class="navbar tp-navbar-fixed"
      :class="{ 'navbar--scrolled': isScrolled }"
    >
      <div class="navbar__container">

        <!-- Brand -->
        <router-link class="navbar__brand" to="/">
          <span class="navbar__brand-icon">
            <img class="navbar__brand-logo" :src="brandLogoUrl" alt="Logo">
          </span>
          <span class="navbar__brand-text">Teman<span class="navbar__brand-accent">Preneur</span></span>
        </router-link>

        <!-- Nav links — selalu tampil -->
        <div class="navbar__links">
          <router-link class="navbar__link" to="/">Beranda</router-link>
          <router-link class="navbar__link" to="/katalog">Katalog</router-link>
          <router-link class="navbar__link" to="/blog">Blog</router-link>
        </div>

        <!-- Search bar tersedia untuk semua pengguna -->
        <div class="navbar__search">
          <svg class="navbar__search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none">
            <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
            <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            class="navbar__search-input"
            placeholder="Cari produk atau berita..."
            @keyup.enter="doSearch"
          />
        </div>

        <!-- ── BELUM LOGIN: tombol Masuk & Daftar ── -->
        <div class="navbar__actions" v-if="!isLoggedIn">
          <router-link class="navbar__btn navbar__btn--ghost" to="/login">Masuk</router-link>
          <router-link class="navbar__btn navbar__btn--primary" to="/register">Daftar Gratis</router-link>
          <button class="navbar__hamburger" @click="toggleMobile" :class="{ 'navbar__hamburger--open': mobileOpen }">
            <span></span><span></span><span></span>
          </button>
        </div>

        <!-- ── SUDAH LOGIN: ikon + identitas ── -->
        <div class="navbar__actions" v-else>

          <!-- Notifikasi -->
          <div class="navbar__icon-wrap">
            <button class="navbar__icon-btn" title="Notifikasi" @click="toggleNotif">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M13.73 21a2 2 0 01-3.46 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <span class="navbar__icon-badge" v-if="notifCount > 0">{{ notifCount > 9 ? '9+' : notifCount }}</span>
            </button>
          </div>

          <!-- Chat -->
          <div class="navbar__icon-wrap" v-if="isLoggedIn">
            <router-link class="navbar__icon-btn" to="/chat" title="Chat">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path d="M21 15a2 2 0 01-2 2H8l-4 4V5a2 2 0 012-2h13a2 2 0 012 2v10z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M7 10h10M7 14h7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
            </router-link>
          </div>

          <!-- Keranjang -->
          <div class="navbar__icon-wrap" v-if="displayRole !== 'admin'">
            <router-link class="navbar__icon-btn" to="/keranjang" title="Keranjang">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                <line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="2"/>
                <path d="M16 10a4 4 0 01-8 0" stroke="currentColor" stroke-width="2"/>
              </svg>
              <span class="navbar__icon-badge" v-if="cartCount > 0">{{ cartCount }}</span>
            </router-link>
          </div>

          <!-- User -->
          <div class="navbar__user" :class="{ 'navbar__user--open': dropdownOpen }">
            <button class="navbar__user-btn" @click="toggleDropdown">
              <div class="navbar__user-avatar">
                <img :src="userPhoto" alt="avatar" class="navbar__user-avatar-img" @error="$onImageError($event)" />
              </div>
              <div class="navbar__user-info">
                <span class="navbar__user-name">{{ user.name }}</span>
                <span class="navbar__user-role"><i :class="roleIcon" aria-hidden="true"></i> {{ roleLabel }}</span>
              </div>
              <svg class="navbar__chevron" width="12" height="12" viewBox="0 0 24 24" fill="none">
                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>

            <!-- Dropdown menu -->
            <div class="navbar__dropdown" v-if="dropdownOpen">
              <div class="navbar__dd-head">
                <div class="navbar__dd-avatar"><img :src="userPhoto" alt="avatar" class="navbar__dd-avatar-img" @error="$onImageError($event)" /></div>
                <div class="navbar__dd-meta">
                  <p class="navbar__dd-name">{{ user.name }}</p>
                  <span class="navbar__dd-badge" :class="`navbar__dd-badge--${displayRole}`">
                    <i :class="roleIcon" aria-hidden="true"></i> {{ roleLabel }}
                  </span>
                  <p class="navbar__dd-email">{{ user.email }}</p>
                </div>
              </div>
              <div class="navbar__dd-divider"></div>

              <button v-if="hasMultipleRoles" class="navbar__dd-item navbar__dd-item--highlight" @click="switchRole">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M8 7h12M8 12h12M8 17h12M3 7h.01M3 12h.01M3 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Ganti ke Mode {{ displayRole === 'buyer' ? 'Penjual' : 'Pembeli' }}
              </button>
              <button v-if="(user.role === 'seller' || user.role === 'seller_premium') && !user.roles?.includes('buyer')" class="navbar__dd-item navbar__dd-item--highlight" @click="registerAsBuyer">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" stroke="currentColor" stroke-width="2"/></svg>
                Daftar sebagai Pembeli
              </button>
              <div class="navbar__dd-divider" v-if="hasMultipleRoles || ((user.role === 'seller' || user.role === 'seller_premium') && !user.roles?.includes('buyer'))"></div>

              <template v-if="displayRole === 'admin'">
                <router-link class="navbar__dd-item" to="/admin" @click="closeDropdown">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/><rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/><rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/><rect x="14" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/></svg>
                  Dashboard Admin
                </router-link>
                <router-link class="navbar__dd-item" to="/admin/users" @click="closeDropdown">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/></svg>
                  Kelola Pengguna
                </router-link>
              </template>

              <template v-if="displayRole === 'seller' || displayRole === 'seller_premium'">
                <router-link class="navbar__dd-item" to="/seller" @click="closeDropdown">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="2"/></svg>
                  Dashboard Toko
                </router-link>
                <router-link class="navbar__dd-item" to="/seller/produk" @click="closeDropdown">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                  Tambah Produk
                </router-link>
                <router-link class="navbar__dd-item" to="/seller/orders" @click="closeDropdown">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M16 4h2a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2" stroke="currentColor" stroke-width="2"/><rect x="8" y="2" width="8" height="4" rx="1" stroke="currentColor" stroke-width="2"/></svg>
                  Pesanan Masuk
                </router-link>
              </template>

              <template v-if="displayRole === 'buyer'">
                <router-link class="navbar__dd-item" to="/buyer/dashboard" @click="closeDropdown">
                  <i class="bi bi-speedometer2" aria-hidden="true"></i>
                  Dashboard Buyer
                </router-link>
                <router-link class="navbar__dd-item" to="/buyer/orders" @click="closeDropdown">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M16 4h2a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2" stroke="currentColor" stroke-width="2"/></svg>
                  Pesanan Saya
                </router-link>
                <router-link class="navbar__dd-item" to="/wishlist" @click="closeDropdown">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z" stroke="currentColor" stroke-width="2"/></svg>
                  Wishlist
                </router-link>
                <router-link class="navbar__dd-item navbar__dd-item--highlight" to="/register-seller" @click="closeDropdown">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="2"/></svg>
                  Buka Toko Saya
                </router-link>
              </template>

              <div class="navbar__dd-divider"></div>

              <router-link class="navbar__dd-item" to="/buyer/profil" @click="closeDropdown">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/></svg>
                Profil & Pengaturan
              </router-link>
              <button class="navbar__dd-item navbar__dd-item--danger" @click="logout">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" stroke="currentColor" stroke-width="2"/><polyline points="16,17 21,12 16,7" stroke="currentColor" stroke-width="2"/><line x1="21" y1="12" x2="9" y2="12" stroke="currentColor" stroke-width="2"/></svg>
                Keluar
              </button>
            </div>
          </div>

          <button class="navbar__hamburger" @click="toggleMobile" :class="{ 'navbar__hamburger--open': mobileOpen }">
            <span></span><span></span><span></span>
          </button>
        </div>

      </div>

      <!-- ══ MOBILE MENU ══ -->
      <transition name="slide-down">
        <div class="navbar__mobile" v-if="mobileOpen">
<div class="navbar__m-search">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
              <input v-model="searchQuery" type="text" placeholder="Cari produk atau berita..." @keyup.enter="doSearch; mobileOpen=false"/>
          </div>

          <router-link class="navbar__m-link" to="/"        @click="mobileOpen=false">Beranda</router-link>
          <router-link class="navbar__m-link" to="/katalog"  @click="mobileOpen=false">Katalog</router-link>
          <router-link class="navbar__m-link" to="/blog"     @click="mobileOpen=false">Blog</router-link>
          <router-link class="navbar__m-link" to="/chat"    @click="mobileOpen=false">Chat</router-link>

          <template v-if="!isLoggedIn">
            <div class="navbar__m-divider"></div>
            <router-link class="navbar__m-link" to="/login"    @click="mobileOpen=false">Masuk</router-link>
            <router-link class="navbar__m-link navbar__m-link--primary" to="/register" @click="mobileOpen=false">Daftar Gratis</router-link>
          </template>

          <template v-else>
            <div class="navbar__m-divider"></div>
            <div class="navbar__m-identity">
              <div class="navbar__m-avatar"><img :src="userPhoto" alt="avatar" class="navbar__m-avatar-img" @error="$onImageError($event)"/></div>
              <div>
                <p class="navbar__m-name">{{ user.name }}</p>
                <span class="navbar__m-role"><i :class="roleIcon" aria-hidden="true"></i> {{ roleLabel }}</span>
              </div>
            </div>
            <div class="navbar__m-divider"></div>

            <template v-if="displayRole === 'admin'">
              <router-link class="navbar__m-link" to="/admin" @click="mobileOpen=false">Dashboard Admin</router-link>
              <router-link class="navbar__m-link" to="/admin/pengguna" @click="mobileOpen=false">Kelola Pengguna</router-link>
            </template>
            <template v-if="displayRole === 'seller' || displayRole === 'seller_premium'">
              <router-link class="navbar__m-link" to="/seller" @click="mobileOpen=false">Dashboard Toko</router-link>
              <router-link class="navbar__m-link" to="/seller/orders" @click="mobileOpen=false">Pesanan Masuk</router-link>
              <router-link class="navbar__m-link" to="/buyer/orders" @click="mobileOpen=false">Pembelian Saya</router-link>
            </template>
            <template v-if="displayRole === 'buyer'">
              <router-link class="navbar__m-link" to="/buyer/dashboard" @click="mobileOpen=false">Dashboard Buyer</router-link>
              <router-link class="navbar__m-link" to="/buyer/orders" @click="mobileOpen=false">Pesanan Saya</router-link>
              <router-link class="navbar__m-link" to="/keranjang" @click="mobileOpen=false">Keranjang</router-link>
              <router-link class="navbar__m-link" to="/wishlist" @click="mobileOpen=false">Wishlist</router-link>
              <router-link class="navbar__m-link navbar__m-link--primary" to="/register-seller" @click="mobileOpen=false">Buka Toko</router-link>
            </template>
            <router-link class="navbar__m-link" to="/buyer/profil" @click="mobileOpen=false">Profil & Pengaturan</router-link>
            <button class="navbar__m-link navbar__m-link--danger" @click="logout; mobileOpen=false">Keluar</button>
          </template>
        </div>
      </transition>
    </nav>

    <div
      v-if="dropdownOpen || mobileOpen"
      class="navbar__overlay"
      @click="closeAll"
    ></div>
    </Teleport>

    <!-- Notification Modal with Teleport -->
    <teleport to="body">
      <transition name="notif-fade">
        <div v-if="notifOpen" class="notif-modal-overlay" @click="closeAll"></div>
      </transition>
      <transition name="notif-slide">
        <div v-if="notifOpen" class="notif-modal">
          <div class="notif-modal-header">
            <h3>Notifikasi</h3>
            <button class="notif-modal-close" @click="notifOpen = false" aria-label="Tutup">×</button>
          </div>
          <button @click="markAllRead" class="notif-modal-clear">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="2"/><path d="M13.73 21a2 2 0 01-3.46 0" stroke="currentColor" stroke-width="2"/></svg>
            Tandai semua dibaca
          </button>
          <div class="notif-modal-list">
            <div v-if="notifications.length === 0" class="notif-modal-empty">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="#d1d5db" stroke-width="2"/></svg>
              <p>Tidak ada notifikasi</p>
            </div>
            <div
              v-for="n in notifications"
              :key="n.id"
              class="notif-modal-item"
              :class="{ 'notif-modal-item--unread': !n.read }"
              @click="goToNotification(n)"
            >
              <span class="notif-modal-dot" v-if="!n.read"></span>
              <div class="notif-modal-content">
                <p class="notif-modal-msg"><strong>{{ n.title || 'Notifikasi' }}</strong></p>
                <p class="notif-modal-desc">{{ n.message }}</p>
                <p class="notif-modal-time">{{ n.created_at ? new Date(n.created_at).toLocaleString('id-ID') : n.time }}</p>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </teleport>

    <ToastContainer />

    <!-- ══════════════════════════════════════════════
         MAIN CONTENT
    ══════════════════════════════════════════════ -->
    <main class="main-content" :class="{ 'main-content--shellless': isShelllessRoute, 'main-content--panel': isPanelRoute }">
      <router-view v-slot="{ Component }">
        <transition name="page-fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>

    <!-- ══════════════════════════════════════════════
         FOOTER
    ══════════════════════════════════════════════ -->
    <footer v-if="!isShelllessRoute && !isPanelRoute" class="footer">
      <div class="footer__glow"></div>
      <div class="footer__glow footer__glow--2"></div>
      <div class="footer__container">

        <!-- Brand -->
        <div class="footer__brand">
          <span class="footer__brand-text">Teman<span>Preneur</span></span>
          <p>Platform marketplace internal sekolah yang menghubungkan siswa penjual dengan pembeli. Dirancang untuk menumbuhkan jiwa wirausaha generasi muda Indonesia sejak dini.</p>
          <div class="footer__social">
            <a href="#" class="footer__social-btn" title="Instagram">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><rect x="2" y="2" width="20" height="20" rx="5" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="5" stroke="currentColor" stroke-width="2"/><circle cx="17.5" cy="6.5" r="1.5" fill="currentColor"/></svg>
            </a>
            <a href="#" class="footer__social-btn" title="Twitter / X">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </a>
            <a href="#" class="footer__social-btn" title="TikTok">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.32 6.32 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.95a8.16 8.16 0 004.77 1.52V7.03a4.85 4.85 0 01-1-.34z"/></svg>
            </a>
          </div>
        </div>

        <!-- Links -->
        <div class="footer__links">
          <div class="footer__col">
            <h4>Platform</h4>
            <router-link to="/">Beranda</router-link>
            <router-link to="/katalog">Katalog Produk</router-link>
            <router-link to="/blog">Blog & Artikel</router-link>
          </div>

          <div class="footer__col" v-if="!isLoggedIn">
            <h4>Akun</h4>
            <router-link to="/login">Masuk</router-link>
            <router-link to="/register">Daftar Gratis</router-link>
            <router-link to="/register">Buka Toko</router-link>
          </div>

          <div class="footer__col" v-if="isLoggedIn">
            <h4>Akun Saya</h4>
            <router-link to="/buyer/profil">Profil Saya</router-link>
            <router-link v-if="displayRole === 'admin'" to="/admin">Dashboard Admin</router-link>
            <router-link v-if="displayRole === 'seller' || displayRole === 'seller_premium'" to="/seller">Dashboard Toko</router-link>
            <router-link v-if="displayRole === 'buyer'" to="/buyer/dashboard">Dashboard Pembeli</router-link>
            <router-link v-if="displayRole === 'buyer'" to="/wishlist">Wishlist</router-link>
            <router-link v-if="displayRole === 'buyer'" to="/register-seller">Buka Toko</router-link>
          </div>

          <div class="footer__col">
            <h4>Bantuan</h4>
            <router-link to="/bantuan/faq">Pusat Bantuan / FAQ</router-link>
            <router-link to="/bantuan/cara-berjualan">Cara Berjualan</router-link>
            <router-link to="/bantuan/cara-berbelanja">Cara Berbelanja</router-link>
            <router-link to="/bantuan/hubungi-admin">Hubungi Admin</router-link>
          </div>
          <div class="footer__col">
            <h4>Kebijakan</h4>
            <router-link to="/kebijakan/syarat-ketentuan">Syarat & Ketentuan</router-link>
            <router-link to="/kebijakan/privasi">Kebijakan Privasi</router-link>
            <router-link to="/kebijakan/pengembalian">Kebijakan Pengembalian</router-link>
            <router-link to="/kebijakan/komunitas">Panduan Komunitas</router-link>
          </div>
        </div>
      </div>

      <!-- Trust bar -->
      <div class="footer__trust">
        <div class="footer__trust-inner">
          <div class="footer__trust-item">
            <div class="footer__trust-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2"/></svg>
            </div>
            Transaksi Aman & Terpercaya
          </div>
          <div class="footer__trust-sep"></div>
          <div class="footer__trust-item">
            <div class="footer__trust-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
            Layanan 7 Hari Seminggu
          </div>
          <div class="footer__trust-sep"></div>
          <div class="footer__trust-item">
            <div class="footer__trust-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="2"/></svg>
            </div>
            Komunitas Pelajar Aktif
          </div>
          <div class="footer__trust-sep"></div>
          <div class="footer__trust-item">
            <div class="footer__trust-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke="currentColor" stroke-width="2"/></svg>
            </div>
            Produk Berkualitas Terverifikasi
          </div>
        </div>
      </div>

      <div class="footer__bottom">
        <p>© {{ currentYear }} TemanPreneur. Dibuat dengan dedikasi untuk pelajar wirausaha Indonesia.</p>
        <p class="footer__bottom-sub">TemanPreneur adalah platform edukasi kewirausahaan berbasis sekolah. Seluruh transaksi dilakukan antar siswa di lingkungan sekolah.</p>
      </div>
    </footer>

  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted, provide, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'
import ToastContainer from '@/components/ToastContainer.vue'
import SplashScreen from '@/components/SplashScreen.vue'
import { normalizeImageUrl, resolveAvatar } from '@/utils/image'

export default {
  name: 'App',
  components: { ToastContainer, SplashScreen },
  setup() {
    const router = useRouter()
    const route = useRoute()
    const authStore = useAuthStore()

    const isShelllessRoute = computed(() =>
      ['/login', '/register', '/choose-role'].includes(route.path)
    )
    const isPanelRoute = computed(() =>
      route.path.startsWith('/admin') || route.path.startsWith('/seller')
    )

    const isLoggedIn  = computed(() => !!authStore.token)
    const user        = computed(() => authStore.user || {})
    const displayRole = computed(() => authStore.role || user.value?.role)
    const isScrolled  = ref(false)
    const dropdownOpen = ref(false)
    const notifOpen   = ref(false)
    const mobileOpen  = ref(false)
    const searchQuery = ref('')
    const notifCount  = ref(0)
    const cartCount   = ref(0)
    const notifications = ref([])

    const brandLogoUrl = computed(() => normalizeImageUrl('/storage/logo1.png'))
    const userInitial = computed(() => user.value.name ? user.value.name.charAt(0).toUpperCase() : '?')
    const roleLabel = computed(() => ({ admin:'Administrator', seller:'Penjual', seller_premium:'Penjual Premium', buyer:'Pembeli' }[displayRole.value] || 'Pengguna'))
    const roleIcon = computed(() => ({ admin:'bi bi-shield-lock', seller:'bi bi-shop', seller_premium:'bi bi-stars', buyer:'bi bi-bag' }[displayRole.value] || 'bi bi-person'))
    const avatarColor = computed(() => ({ admin:'linear-gradient(135deg,#6366f1,#4f46e5)', seller:'linear-gradient(135deg,#f56565,#c53030)', seller_premium:'linear-gradient(135deg,#f59e0b,#d97706)', buyer:'linear-gradient(135deg,#10b981,#059669)' }[displayRole.value] || 'linear-gradient(135deg,#9ca3af,#6b7280)'))
    const userPhoto = computed(() => resolveAvatar(user.value, displayRole.value))
    const hasMultipleRoles = computed(() => authStore.hasMultipleRoles)

    const loadNotifications = async () => {
      try {
        const response = await api.get('/notifications')
        const data = response.data
        const notifData = data?.data || data || []
        // Map is_read to read for consistency
        notifications.value = notifData.map(n => ({
          ...n,
          read: n.is_read || n.read || false
        }))
      } catch (error) {
        console.error('Failed to load notifications:', error)
        notifications.value = []
      }
    }

    const loadNotifCount = async () => {
      try {
        const response = await api.get('/notifications/unread-count')
        notifCount.value = response.data?.count ?? 0
      } catch (error) {
        console.error('Failed to load notification count:', error)
        notifCount.value = 0
      }
    }

    const checkAuth = async () => {
      const token = authStore.token || localStorage.getItem('token')
      if (!token) return
      try {
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`
        await authStore.fetchUser()
        const u = authStore.user
        cartCount.value  = u?.cart_count ?? 0
        await loadNotifications()
        await loadNotifCount()
      } catch {
        authStore.logout()
      }
    }

    const logout = async () => { await authStore.logout(); dropdownOpen.value = false; router.push('/') }
    const switchRole = () => { closeDropdown(); router.push('/choose-role') }
    const registerAsBuyer = async () => {
      try {
        await api.post('/user/register-as-buyer')
        const userResponse = await api.get('/user')
        const u = userResponse.data.user || userResponse.data
        authStore.user = u
        authStore.user.roles = authStore.user.roles || [authStore.user.role]
        localStorage.setItem('user', JSON.stringify(authStore.user))
        authStore.setActiveRole('buyer')
        closeDropdown()
        router.push('/buyer/orders')
      } catch (e) { console.error('Gagal daftar buyer', e) }
    }

    provide('auth', { isLoggedIn, user, checkAuth })

    const toggleDropdown = () => { dropdownOpen.value = !dropdownOpen.value; notifOpen.value = false }
    const closeDropdown  = () => { dropdownOpen.value = false }
    const toggleNotif    = async () => { 
      notifOpen.value = !notifOpen.value
      dropdownOpen.value = false
      
      // Auto-mark unread notifications as read when panel opens
      if (notifOpen.value) {
        const unreadNotifs = notifications.value.filter(n => !n.read)
        for (const notif of unreadNotifs) {
          try {
            await api.post(`/notifications/${notif.id}/read`)
            notif.read = true
            notifCount.value = Math.max(0, notifCount.value - 1)
          } catch (error) {
            console.error('Failed to mark notification as read:', error)
          }
        }
      }
    }
    const toggleMobile   = () => { mobileOpen.value = !mobileOpen.value }
    const closeAll       = () => { dropdownOpen.value = false; notifOpen.value = false; mobileOpen.value = false }
    const markAllRead = async () => {
      try {
        await api.post('/notifications/read-all')
        notifications.value.forEach(n => n.read = true)
        notifCount.value = 0
      } catch (error) {
        console.error('Failed to mark all notifications as read:', error)
      }
    }

    const goToNotification = async (notification) => {
      try {
        if (!notification.read) {
          await api.post(`/notifications/${notification.id}/read`)
          notification.read = true
          notifCount.value = Math.max(0, notifCount.value - 1)
        }
      } catch (error) {
        console.error('Failed to mark notification as read:', error)
      }

      const routeMap = {
        issue_report_response: () => ({ name: 'notification.detail', params: { responseId: notification.related_id } }),
        order_status_changed: () => ({ path: `/buyer/orders/${notification.related_id}/tracking` }),
        order_cancelled: () => ({ path: '/seller/pesanan' }),
        order_created: () => ({ path: '/buyer/orders' }),
        payment_confirmed: () => ({ path: '/buyer/orders' }),
        order_completed: () => ({ path: '/buyer/orders' }),
        business_verification: () => ({ path: '/admin/verifikasi' }),
        business_approved: () => ({ path: '/seller/dashboard' }),
        business_rejected: () => ({ name: 'seller.status' }),
        premium_activated: () => ({ path: '/seller/dashboard' }),
        business_verified: () => ({ path: '/seller/dashboard' }),
      }

      const target = routeMap[notification.type]?.() || { path: '/' }

      if (['business_approved', 'premium_activated', 'business_verified'].includes(notification.type)) {
        const targetRole = authStore.user?.roles?.includes('seller_premium') ? 'seller_premium' : 'seller'
        await authStore.setActiveRole(targetRole)
      }

      closeAll()
      router.push(target)
    }

    const doSearch = () => {
      const query = searchQuery.value.trim()
      if (!query) return

      const blogPrefix = query.match(/^(blog|berita):\s*/i)
      if (route.name === 'blog' || blogPrefix) {
        const q = blogPrefix ? query.replace(blogPrefix[0], '').trim() : query
        router.push({ path: '/blog', query: { q } })
      } else {
        router.push({ path: '/katalog', query: { q: query } })
      }
      searchQuery.value = ''
      mobileOpen.value = false
    }
    const handleScroll = () => { isScrolled.value = window.scrollY > 20 }

    onMounted(() => { 
      checkAuth()
      window.addEventListener('scroll', handleScroll)
    })
    onUnmounted(() => { window.removeEventListener('scroll', handleScroll) })

    watch([dropdownOpen, notifOpen], ([dropOpen, notifOpen]) => {
      document.body.style.overflow = dropOpen || notifOpen ? 'hidden' : ''
    })

    return {
      isLoggedIn, user, displayRole, userInitial, roleLabel, roleIcon, avatarColor, hasMultipleRoles,
      isScrolled, dropdownOpen, notifOpen, mobileOpen,
      searchQuery,       notifCount, cartCount, notifications,
      brandLogoUrl,
      toggleDropdown, closeDropdown, toggleNotif, toggleMobile, closeAll,
      markAllRead, doSearch, logout, switchRole, registerAsBuyer,
      currentYear: new Date().getFullYear(),
      normalizeImageUrl, userPhoto, isShelllessRoute, isPanelRoute,
    }
  }
}
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Fraunces:opsz,wght@9..144,700;9..144,900&display=swap');

:root {
  --red-50:  #FFF5F5; --red-100:#FED7D7; --red-200:#FEB2B2;
  --red-400: #FC8181; --red-500:#F56565; --red-600:#E53E3E;
  --red-700: #C53030; --red-800:#9B2C2C;
  --white:   #FFFFFF; --gray-50:#F9FAFB; --gray-100:#F3F4F6;
  --gray-200:#E5E7EB; --gray-300:#D1D5DB; --gray-400:#9CA3AF;
  --gray-500:#6B7280; --gray-600:#4B5563; --gray-700:#374151;
  --gray-800:#1F2937; --gray-900:#111827;
  --navbar-h: 64px;
  --tp-navbar-gap: 12px;
  --font-sans: 'Plus Jakarta Sans', sans-serif;
  --font-display: 'Fraunces', serif;
  --radius-sm: 8px;
  --radius: 12px;
  --radius-lg: 16px;
  --radius-xl: 20px;
  --radius-2xl: 24px;
  --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
  --shadow: 0 4px 16px rgba(0,0,0,0.06), 0 2px 6px rgba(0,0,0,0.04);
  --shadow-md: 0 8px 32px rgba(0,0,0,0.08), 0 4px 12px rgba(0,0,0,0.04);
  --shadow-lg: 0 20px 56px rgba(0,0,0,0.10), 0 8px 20px rgba(0,0,0,0.06);
  --shadow-red: 0 4px 20px rgba(229,62,62,0.28);
  --shadow-red-lg: 0 8px 32px rgba(229,62,62,0.36);
  --transition: 0.22s cubic-bezier(0.4,0,0.2,1);
  --transition-slow: 0.38s cubic-bezier(0.4,0,0.2,1);
  --spacing-1: 4px; --spacing-2: 8px; --spacing-3: 12px;
  --spacing-4: 16px; --spacing-5: 20px; --spacing-6: 24px;
  --spacing-8: 32px; --spacing-10: 40px; --spacing-12: 48px;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; font-size: 16px; }
body {
  font-family: var(--font-sans);
  background: linear-gradient(180deg, #fbfbfd 0%, #f8fafc 45%, #f4f7f9 100%);
  color: var(--gray-900);
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  line-height: 1.6;
  min-height: 100vh;
  position: relative;
}
/* Pastikan konten utama tidak tertutup oleh navbar fixed */
#app {
  padding-top: var(--navbar-h);
}
body::before {
  content: '';
  position: fixed;
  inset: 0;
  background: radial-gradient(circle at 20% 15%, rgba(229,62,62,0.10), transparent 20%),
              radial-gradient(circle at 85% 80%, rgba(251,146,60,0.06), transparent 18%);
  pointer-events: none;
  z-index: -1;
}
a { text-decoration: none; color: inherit; }

/* ── Page Transition ── */
.page-fade-enter-active,
.page-fade-leave-active { transition: opacity 0.24s ease, transform 0.24s ease; }
.page-fade-enter-from { opacity: 0; transform: translateY(10px); }
.page-fade-leave-to { opacity: 0; transform: translateY(-5px); }

/* ── Slide Down (Mobile Menu) ── */
.slide-down-enter-active,
.slide-down-leave-active { transition: opacity 0.28s ease, transform 0.28s cubic-bezier(0.16,1,0.3,1); }
.slide-down-enter-from { opacity: 0; transform: translateY(-14px); }
.slide-down-leave-to { opacity: 0; transform: translateY(-8px); }

/* ══════════════════════════════
   NAVBAR
══════════════════════════════ */
.tp-navbar-fixed,
.navbar {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  right: 0 !important;
  width: 100%;
  height: var(--navbar-h);
  display: block;
  z-index: 1200;
  will-change: transform;
  transition: background var(--transition), box-shadow var(--transition), border-color var(--transition);
  background: rgba(255, 255, 255, 0.97);
  border-bottom: 1px solid rgba(254, 202, 202, 0.35);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  box-shadow: 0 1px 0 rgba(229, 62, 62, 0.05), 0 4px 20px rgba(0, 0, 0, 0.05);
}
.navbar--scrolled {
  background: rgba(255, 255, 255, 0.98);
  box-shadow: 0 1px 0 rgba(229, 62, 62, 0.08), 0 6px 28px rgba(0, 0, 0, 0.07);
  border-bottom-color: rgba(254, 202, 202, 0.5);
}

.navbar__container {
  width: 100%;
  max-width: var(--tp-container-max, 1280px);
  margin: 0 auto;
  padding: 0 var(--tp-container-pad, clamp(16px, 3vw, 28px));
  height: var(--navbar-h);
  display: flex;
  align-items: center;
  gap: 8px;
  justify-content: flex-start;
}

/* Brand */
.navbar__brand { display: flex; align-items: center; gap: 10px; flex-shrink: 0; text-decoration: none; color: inherit; }
.navbar__brand:hover, .navbar__brand:focus { text-decoration: none; }
.navbar__brand-icon {
  width: 36px; height: 36px; border-radius: var(--radius-lg);
  background: #fff;
  display: flex; align-items: center; justify-content: center; color: #fff;
  box-shadow: var(--shadow-sm); flex-shrink: 0;
  transition: transform var(--transition), box-shadow var(--transition);
  border: 1.5px solid var(--gray-100);
}
.navbar__brand-logo {
  width: 100%;
  height: 100%;
  object-fit: contain;
  padding: 3px;
}
.navbar__brand:hover .navbar__brand-icon { transform: scale(1.06) rotate(-3deg); box-shadow: var(--shadow); }
.navbar__brand-text { font-family: var(--font-display); font-size: 1.35rem; font-weight: 700; color: var(--gray-900); letter-spacing: -0.02em; }
.navbar__brand-accent { color: var(--red-600); }

/* Nav links */
.navbar__links { display: flex; align-items: center; gap: 12px; flex-shrink: 0; margin: 0 16px; }
.navbar__link {
  padding: 8px 18px; font-size: 0.875rem; font-weight: 500;
  color: var(--gray-600); border-radius: var(--radius);
  transition: all var(--transition); position: relative;
  white-space: nowrap;
}
.navbar__link::after {
  content: ''; position: absolute; bottom: 5px; left: 50%; right: 50%;
  height: 2px; background: var(--red-500); border-radius: 2px;
  transition: left var(--transition), right var(--transition);
}
.navbar__link:hover { color: var(--red-600); background: var(--red-50); }
.navbar__link.router-link-active { color: var(--red-600); background: var(--red-50); }
.navbar__link.router-link-active::after { left: 16px; right: 16px; }
.navbar__link.router-link-exact-active { font-weight: 700; }

/* Search */
.navbar__search { flex: 1; max-width: 380px; position: relative; margin: 0 16px; }
.navbar__search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--gray-400); pointer-events: none; }
.navbar__search-input {
  width: 100%; height: 42px; padding: 0 18px 0 42px;
  border: 1.5px solid var(--gray-200); border-radius: 100px;
  font-family: var(--font-sans); font-size: 0.875rem;
  background: var(--gray-50); color: var(--gray-900); outline: none;
  transition: border-color var(--transition), box-shadow var(--transition), background var(--transition);
}
.navbar__search-input::placeholder { color: var(--gray-400); }
.navbar__search-input:focus { border-color: var(--red-400); background: #fff; box-shadow: 0 0 0 4px rgba(229,62,62,0.08); }

/* Actions wrapper */
.navbar__actions { display: flex; align-items: center; gap: 12px; margin-left: auto; flex-shrink: 0; }

/* Tombol Masuk / Daftar */
.navbar__btn {
  padding: 10px 22px; border-radius: var(--radius-lg); border: none; cursor: pointer;
  font-family: var(--font-sans); font-size: 0.875rem; font-weight: 600;
  transition: all var(--transition); white-space: nowrap;
}
.navbar__btn--ghost {
  background: transparent; color: var(--gray-700);
  border: 1.5px solid var(--gray-200);
}
.navbar__btn--ghost:hover { border-color: var(--red-300); color: var(--red-600); background: var(--red-50); }
.navbar__btn--primary {
  background: linear-gradient(135deg, var(--red-500), var(--red-700));
  color: #fff; box-shadow: var(--shadow-red);
}
.navbar__btn--primary:hover { transform: translateY(-2px); box-shadow: var(--shadow-red-lg); }

/* Icon buttons */
.navbar__icon-wrap { position: relative; }
.navbar__icon-btn {
  width: 42px; height: 42px;
  border: 1.5px solid var(--gray-200); border-radius: var(--radius-lg);
  background: #fff; display: flex; align-items: center;
  justify-content: center; color: var(--gray-500); cursor: pointer;
  transition: all var(--transition); position: relative; text-decoration: none;
}
.navbar__icon-btn:hover { border-color: var(--red-300); color: var(--red-600); background: var(--red-50); transform: translateY(-2px); box-shadow: var(--shadow-sm); }
.navbar__icon-badge {
  position: absolute; top: -6px; right: -6px;
  min-width: 18px; height: 18px; padding: 0 4px;
  background: var(--red-600); color: #fff; font-size: 0.625rem; font-weight: 800;
  border-radius: 100px; display: flex; align-items: center; justify-content: center;
  border: 2px solid #fff; animation: badgePop 0.35s cubic-bezier(0.34,1.56,0.64,1);
}
@keyframes badgePop { from { transform: scale(0); } to { transform: scale(1); } }

/* Notification Modal */
.notif-modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 400;
  animation: notif-fade 0.2s ease-out;
}

.notif-modal {
  position: fixed; bottom: 0; right: 0; top: 0; z-index: 401;
  width: 100%; max-width: 420px;
  background: #fff; display: flex; flex-direction: column;
  box-shadow: -4px 0 32px rgba(0,0,0,0.12);
  animation: notif-slide 0.3s cubic-bezier(0.16,1,0.3,1);
}

.notif-modal-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 20px 24px; border-bottom: 1px solid var(--gray-100);
  background: #fff; position: sticky; top: 0; z-index: 2;
}

.notif-modal-header h3 {
  margin: 0; font-size: 1.125rem; font-weight: 700; color: var(--gray-900);
}

.notif-modal-close {
  width: 36px; height: 36px;
  border: 1.5px solid var(--gray-200); border-radius: var(--radius-lg);
  background: #fff; color: var(--gray-500); font-size: 1.25rem;
  cursor: pointer; transition: all var(--transition);
  display: flex; align-items: center; justify-content: center;
}

.notif-modal-close:hover {
  border-color: var(--red-300); color: var(--red-600); background: var(--red-50);
}

.notif-modal-clear {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 16px; margin: 12px 20px;
  background: var(--red-50); color: var(--red-600);
  border: 1px solid var(--red-200); border-radius: var(--radius-lg);
  font-size: 0.8125rem; font-weight: 600; cursor: pointer;
  transition: all var(--transition);
}

.notif-modal-clear:hover {
  background: var(--red-100); border-color: var(--red-300);
}

.notif-modal-list {
  flex: 1; overflow-y: auto; overflow-x: hidden;
  padding: 8px 0;
}

.notif-modal-empty {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  padding: 60px 40px; text-align: center; color: var(--gray-400);
  min-height: 300px;
}

.notif-modal-empty svg {
  margin-bottom: 16px; opacity: 0.4;
}

.notif-modal-empty p {
  margin: 0; font-size: 0.9375rem; font-weight: 500;
}

.notif-modal-item {
  display: flex; align-items: flex-start; gap: 14px;
  padding: 16px 20px; border-bottom: 1px solid var(--gray-100);
  transition: background var(--transition); cursor: pointer;
}

.notif-modal-item:hover {
  background: var(--gray-50);
}

.notif-modal-item--unread {
  background: var(--red-50);
  border-left: 4px solid var(--red-500);
  padding-left: 16px;
}

.notif-modal-dot {
  width: 8px; height: 8px; border-radius: 50%;
  background: var(--red-500); margin-top: 6px; flex-shrink: 0;
}

.notif-modal-content {
  flex: 1; min-width: 0;
}

.notif-modal-msg {
  margin: 0 0 4px 0; font-size: 0.8125rem; color: var(--gray-800);
  font-weight: 600; line-height: 1.4;
}

.notif-modal-desc {
  margin: 0 0 6px 0; font-size: 0.75rem; color: var(--gray-600);
  line-height: 1.4;
}

.notif-modal-time {
  margin: 0; font-size: 0.7rem; color: var(--gray-400);
}

@keyframes notif-fade {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes notif-slide {
  from { transform: translateX(100%); }
  to { transform: translateX(0); }
}

.notif-fade-enter-active,
.notif-fade-leave-active { transition: opacity 0.2s ease; }
.notif-fade-enter-from,
.notif-fade-leave-to { opacity: 0; }

.notif-slide-enter-active,
.notif-slide-leave-active { transition: transform 0.3s cubic-bezier(0.16,1,0.3,1); }
.notif-slide-enter-from { transform: translateX(100%); }
.notif-slide-leave-to { transform: translateX(100%); }

/* User button */
.navbar__user { position: relative; }
.navbar__user-btn {
  display: flex; align-items: center; gap: 10px;
  padding: 5px 12px 5px 5px;
  border: 1.5px solid var(--gray-200); border-radius: 100px;
  background: #fff; cursor: pointer;
  transition: border-color var(--transition), box-shadow var(--transition), background var(--transition);
}
.navbar__user-btn:hover,
.navbar__user--open .navbar__user-btn {
  border-color: var(--red-300); box-shadow: 0 0 0 4px rgba(229,62,62,0.07);
  background: var(--red-50);
}
.navbar__user-avatar {
  width: 34px; height: 34px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 0.875rem; font-weight: 800; flex-shrink: 0;
}
.navbar__user-avatar-img, .navbar__dd-avatar-img, .navbar__m-avatar-img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; display: block; }
.navbar__user-info { display: flex; flex-direction: column; line-height: 1.25; }
.navbar__user-name { font-size: 0.8125rem; font-weight: 700; color: var(--gray-800); max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.navbar__user-role { font-size: 0.6875rem; color: var(--gray-400); }
.navbar__chevron { color: var(--gray-400); transition: transform var(--transition); flex-shrink: 0; }
.navbar__user--open .navbar__chevron { transform: rotate(180deg); }

/* Dropdown */
.navbar__dropdown {
  position: absolute; top: calc(100% + 12px); right: 0;
  width: 278px; background: #fff;
  border: 1px solid var(--gray-100); border-radius: var(--radius-2xl);
  box-shadow: var(--shadow-lg); overflow: hidden;
  animation: dropIn .25s cubic-bezier(0.16,1,0.3,1); z-index: 300;
}
@keyframes dropIn { from{ opacity:0; transform:translateY(-12px) scale(.96); } to{ opacity:1; transform:translateY(0) scale(1); } }

.navbar__dd-head {
  display: flex; gap: 12px; padding: 20px;
  background: linear-gradient(135deg, var(--red-50) 0%, #fff 100%);
  border-bottom: 1px solid var(--gray-100);
}
.navbar__dd-avatar {
  width: 48px; height: 48px; border-radius: 50%; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 1.125rem; font-weight: 800;
  box-shadow: var(--shadow-sm);
}
.navbar__dd-name { font-size: 0.9rem; font-weight: 800; color: var(--gray-900); line-height: 1.3; }
.navbar__dd-badge {
  display: inline-block; margin: 5px 0 4px;
  padding: 3px 10px; border-radius: 100px; font-size: 0.6875rem; font-weight: 700;
}
.navbar__dd-badge--admin          { background:rgba(99,102,241,.12);  color:#4f46e5; }
.navbar__dd-badge--seller         { background:rgba(229,62,62,.12);   color:#c53030; }
.navbar__dd-badge--seller_premium { background:rgba(245,158,11,.12);  color:#d97706; }
.navbar__dd-badge--buyer          { background:rgba(16,185,129,.12);  color:#059669; }
.navbar__dd-email { font-size: 0.6875rem; color: var(--gray-400); }
.navbar__dd-divider { height: 1px; background: var(--gray-100); margin: 4px 0; }

.navbar__dd-item {
  display: flex; align-items: center; gap: 10px; width: 100%;
  padding: 10px 20px; font-family: var(--font-sans); font-size: 0.875rem; font-weight: 500;
  color: var(--gray-700); background: none; border: none; cursor: pointer;
  transition: background var(--transition), color var(--transition); text-align: left; text-decoration: none;
}
.navbar__dd-item:hover { background: var(--gray-50); color: var(--gray-900); }
.navbar__dd-item--highlight { color: var(--red-600); font-weight: 600; }
.navbar__dd-item--highlight:hover { background: var(--red-50); }
.navbar__dd-item--danger { color: var(--red-600); }
.navbar__dd-item--danger:hover { background: var(--red-50); color: var(--red-700); }

/* Hamburger */
.navbar__hamburger {
  display: none; flex-direction: column; gap: 5px;
  width: 40px; height: 40px; padding: 8px; border: none;
  background: transparent; cursor: pointer; border-radius: var(--radius-lg);
  transition: background var(--transition); align-items: center; justify-content: center;
}
.navbar__hamburger:hover { background: var(--gray-100); }
.navbar__hamburger span { display: block; height: 2px; width: 20px; background: var(--gray-700); border-radius: 2px; transition: all var(--transition); transform-origin: center; }
.navbar__hamburger--open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.navbar__hamburger--open span:nth-child(2) { opacity: 0; transform: scaleX(0); }
.navbar__hamburger--open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

/* Mobile menu */
.navbar__mobile {
  background: rgba(255,255,255,0.98); border-top: 1px solid var(--gray-100);
  display: flex; flex-direction: column; gap: 2px;
  padding: 16px 20px 24px;
  backdrop-filter: blur(20px);
}
.navbar__m-search {
  display: flex; align-items: center; gap: 8px;
  height: 44px; padding: 0 16px;
  border: 1.5px solid var(--gray-200); border-radius: 100px;
  margin-bottom: 12px; color: var(--gray-400);
}
.navbar__m-search input { flex: 1; border: none; outline: none; background: transparent; font-family: var(--font-sans); font-size: 0.875rem; color: var(--gray-900); }
.navbar__m-link {
  display: block; padding: 12px 16px; font-size: 0.9rem; font-weight: 500;
  color: var(--gray-700); border-radius: var(--radius-lg); background: none; border: none;
  cursor: pointer; text-align: left; font-family: var(--font-sans); width: 100%; text-decoration: none;
  transition: background var(--transition), color var(--transition);
}
.navbar__m-link:hover { background: var(--red-50); color: var(--red-600); }
.navbar__m-link--primary { background: linear-gradient(135deg, var(--red-500), var(--red-700)); color: #fff !important; text-align: center; font-weight: 700; margin-top: 8px; box-shadow: var(--shadow-red); }
.navbar__m-link--primary:hover { background: var(--red-700); transform: translateY(-1px); }
.navbar__m-link--danger  { color: var(--red-600) !important; }
.navbar__m-divider { height: 1px; background: var(--gray-100); margin: 8px 0; }
.navbar__m-identity { display: flex; align-items: center; gap: 12px; padding: 12px 16px; background: var(--gray-50); border-radius: var(--radius-lg); }
.navbar__m-avatar { width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 0.9rem; font-weight: 800; flex-shrink: 0; }
.navbar__m-name  { font-size: 0.875rem; font-weight: 700; color: var(--gray-900); }
.navbar__m-role  { font-size: 0.75rem; color: var(--gray-500); }

/* Overlay */
.navbar__overlay { position: fixed; inset: 0; z-index: 1190; background: rgba(15, 23, 42, 0.25); }

/* ══════════════════════════════
   MAIN
══════════════════════════════ */
.main-content {
  min-height: calc(100vh - var(--navbar-h) - 200px);
  padding: var(--tp-navbar-gap, 8px) 0 0;
  position: relative;
}
.main-content--shellless {
  min-height: 100vh;
  padding: 0;
}
.main-content--panel {
  min-height: 100vh;
  padding: 0;
}
.main-content::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at top 15% left 10%, rgba(229,62,62,0.08), transparent 20%);
  pointer-events: none;
}


/* ══════════════════════════════
   FOOTER
══════════════════════════════ */
.footer {
  background: linear-gradient(170deg, #111827 0%, #0c1420 60%, #0f172a 100%);
  color: var(--gray-300);
  margin-top: 64px;
  position: relative;
  overflow: hidden;
}
.footer__glow {
  position: absolute; top: -100px; left: 30%;
  width: 600px; height: 260px;
  background: radial-gradient(ellipse, rgba(229,62,62,0.10) 0%, transparent 68%);
  filter: blur(4px);
  pointer-events: none;
}
.footer__glow--2 {
  top: auto; bottom: -60px; left: -10%;
  width: 400px; height: 200px;
  background: radial-gradient(ellipse, rgba(229,62,62,0.06) 0%, transparent 72%);
}
.footer__container {
  max-width: var(--tp-container-max, 1280px);
  margin: 0 auto;
  padding: 72px var(--tp-container-pad, 28px) 48px;
  display: grid;
  grid-template-columns: 1.6fr 1.5fr;
  gap: 64px;
}
.footer__brand-text { font-family: var(--font-display); font-size: 1.9rem; font-weight: 800; color: #fff; display: block; margin-bottom: 16px; letter-spacing: -0.04em; }
.footer__brand-text span { color: var(--red-400); }
.footer__brand p { font-size: 0.95rem; line-height: 1.95; max-width: 380px; margin-bottom: 28px; color: rgba(255,255,255,0.55); }
.footer__social { display: flex; gap: 12px; }
.footer__social-btn {
  width: 44px; height: 44px; border-radius: var(--radius-lg);
  background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);
  display: flex; align-items: center; justify-content: center;
  color: rgba(255,255,255,0.55); transition: all var(--transition); text-decoration: none;
}
.footer__social-btn:hover { background: var(--red-700); border-color: var(--red-600); color: #fff; transform: translateY(-3px); box-shadow: 0 10px 24px rgba(229,62,62,0.32); }
.footer__links { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 28px; }
.footer__col h4 { font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.14em; color: rgba(255,255,255,0.65); margin-bottom: 20px; }
.footer__col a { display: block; font-size: 0.92rem; margin-bottom: 12px; color: rgba(255,255,255,0.45); text-decoration: none; transition: color var(--transition), padding-left var(--transition); }
.footer__col a:hover { color: var(--red-400); padding-left: 6px; }

/* Trust bar */
.footer__trust { border-top: 1px solid rgba(255,255,255,0.06); border-bottom: 1px solid rgba(255,255,255,0.05); padding: 24px 48px; background: rgba(255,255,255,0.02); }
.footer__trust-inner { max-width: var(--tp-container-max, 1280px); margin: 0 auto; padding: 0 var(--tp-container-pad, 28px); display: flex; align-items: center; gap: 0; flex-wrap: wrap; justify-content: center; }
.footer__trust-item { display: flex; align-items: center; gap: 12px; font-size: 0.86rem; font-weight: 600; color: rgba(255,255,255,0.5); padding: 8px 32px; }
.footer__trust-sep { width: 1px; height: 28px; background: rgba(255,255,255,0.1); flex-shrink: 0; }
.footer__trust-icon { width: 34px; height: 34px; border-radius: var(--radius-lg); background: rgba(229,62,62,0.14); display: flex; align-items: center; justify-content: center; color: var(--red-400); flex-shrink: 0; }
.footer__bottom { padding: 32px var(--tp-container-pad, 28px); text-align: center; max-width: var(--tp-container-max, 1280px); margin: 0 auto; }
.footer__bottom p { font-size: 0.83rem; color: rgba(255,255,255,0.28); }
.footer__bottom-sub { font-size: 0.77rem !important; margin-top: 8px; color: rgba(255,255,255,0.16) !important; }

@media (max-width: 1024px) {
  .navbar__search { max-width: 260px; }
  .footer__container { grid-template-columns: 1fr; gap: 48px; padding: 56px 32px 40px; }
  .footer__links { grid-template-columns: repeat(2, 1fr); }
  .footer__trust { padding: 20px 32px; }
  .footer__bottom { padding: 28px 32px; }
}
@media (max-width: 768px) {
  .navbar__links  { display: none; }
  .navbar__search { display: none; }
  .navbar__hamburger { display: flex; }
  .navbar__btn--ghost { display: none; }
  .navbar__user-info { display: none; }
  :root { --navbar-h: 62px; }
}
@media (max-width: 480px) {
  .navbar__actions { gap: 4px; }
  .footer__links { grid-template-columns: 1fr 1fr; gap: 24px; }
  .footer__trust-inner { gap: 8px; }
  .footer__trust-sep { display: none; }
  .footer__trust-item { padding: 6px 16px; }
  .footer__container { padding: 44px 24px 36px; }
  .footer__trust { padding: 16px 24px; }
  .footer__bottom { padding: 24px; }
}


</style>

