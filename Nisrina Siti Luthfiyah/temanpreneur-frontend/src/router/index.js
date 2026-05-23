import { createRouter, createWebHistory } from 'vue-router'

// ── Public ──────────────────────────────────────────────────
import Home          from '../views/public/Home.vue'
import Catalog       from '../views/public/Catalog.vue'
import Blog          from '../views/public/Blog.vue'
import BlogDetail    from '../views/public/BlogDetail.vue'
import Chat          from '../views/public/Chat.vue'
import HelpFAQ       from '../views/public/help/FAQ.vue'
import HelpSell      from '../views/public/help/Sell.vue'
import HelpBuy       from '../views/public/help/Buy.vue'
import HelpContact   from '../views/public/help/Contact.vue'
import PolicyTerms   from '../views/public/policy/Terms.vue'
import PolicyPrivacy from '../views/public/policy/Privacy.vue'
import PolicyReturn  from '../views/public/policy/Return.vue'
import PolicyCommunity from '../views/public/policy/Community.vue'
import ProductDetail from '../views/public/ProductDetail.vue'
import SellerProfile from '../views/public/SellerProfile.vue'

// ── Auth ────────────────────────────────────────────────────
import Login         from '../views/auth/Login.vue'
import Register      from '../views/auth/Register.vue'
import RoleSelection from '../views/auth/RoleSelection.vue'

// ── Buyer ───────────────────────────────────────────────────
import BuyerDashboard from '../views/buyer/Dashboard.vue'
import BuyerOrders    from '../views/buyer/Orders.vue'
import BuyerProfil    from '../views/buyer/BuyerProfil.vue'
import BuyerCart      from '../views/buyer/BuyerCart.vue'
import BuyerReview    from '../views/buyer/BuyerReview.vue'
import OrderDetail    from '../views/buyer/OrderDetail.vue'
import OrderTracking  from '../views/buyer/OrderTracking.vue'
import NotificationDetail from '../views/buyer/NotificationDetail.vue'

// ── Seller ──────────────────────────────────────────────────
import SellerApply   from '../views/seller/Apply.vue'
import SellerLayout  from '../views/seller/SellerLayout.vue'
import SellerDashboard from '../views/seller/Dashboard.vue'
import SellerProdukSaya from '../views/seller/ProdukSaya.vue'
import SellerPesananMasuk from '../views/seller/PesananMasuk.vue'
import SellerAnalitik from '../views/seller/Analitik.vue'
import SellerSaldoDompet from '../views/seller/SaldoDompet.vue'
import SellerBlogUsaha from '../views/seller/BlogUsaha.vue'
import SellerPengaturanToko from '../views/seller/PengaturanToko.vue'
import SellerUpgradePremium from '../views/seller/UpgradePremium.vue'
import SellerStatus from '../views/seller/SellerStatus.vue'
import TeamManagement from '../views/seller/TeamManagement.vue'
import SellerAnalyticsPremium from '../views/seller/SellerAnalyticsPremium.vue'
import SellerMarketing from '../views/seller/SellerMarketing.vue'
import SellerFeatured from '../views/seller/SellerFeatured.vue'

// ── Admin Layout + Halaman ──────────────────────────────────
// Letakkan semua file admin di: src/views/admin/
import AdminLayout       from '../views/admin/AdminLayout.vue'
import AdminDashboard    from '../views/admin/Dashboard.vue'          // file lama kamu
import AdminVerifikasi   from '../views/admin/AdminVerifikasi.vue'    // = ApplySeller (yang lama)
import AdminPengguna     from '../views/admin/AdminPengguna.vue'
import AdminKonten       from '../views/admin/AdminKonten.vue'
import AdminPesanan      from '../views/admin/AdminPesanan.vue'
import AdminLaporan      from '../views/admin/AdminLaporan.vue'
import AdminPerforma     from '../views/admin/AdminPerforma.vue'
import AdminPremiumApproval from '../views/admin/AdminPremiumApproval.vue'
import AdminPengaturan   from '../views/admin/AdminPengaturan.vue'

const routes = [

  // ══════════════════════════════
  // PUBLIC
  // ══════════════════════════════
  {
    path: '/register-seller',
    redirect: { name: 'seller.apply' },
  },
  {
    path: '/ajukan-usaha',
    redirect: { name: 'seller.apply' },
  },
  {
    path: '/toko/:id',
    redirect: (to) => ({ name: 'seller.public', params: { id: to.params.id } }),
  },
  {
    path: '/',
    name: 'home',
    component: Home,
  },
  {
    path: '/katalog',
    name: 'catalog',
    component: Catalog,
    alias: '/catalog',        // allow english URL as fallback
  },
  {
    path: '/blog',
    name: 'blog',
    component: Blog,
  },
  {
    path: '/blog/tulis',
    name: 'blog.write',
    component: SellerBlogUsaha,
    meta: { requiresAuth: true, role: ['seller', 'seller_premium'] },
  },
  {
    path: '/blog/:slug',
    name: 'blog.detail',
    component: BlogDetail,
    props: true,
  },
  {
    path: '/chat',
    name: 'chat',
    component: Chat,
    meta: { requiresAuth: true },
  },
  {
    path: '/bantuan/faq',
    name: 'help.faq',
    component: HelpFAQ,
  },
  {
    path: '/bantuan/cara-berjualan',
    name: 'help.sell',
    component: HelpSell,
  },
  {
    path: '/bantuan/cara-berbelanja',
    name: 'help.buy',
    component: HelpBuy,
  },
  {
    path: '/bantuan/hubungi-admin',
    name: 'help.contact',
    component: HelpContact,
  },
  {
    path: '/kebijakan/syarat-ketentuan',
    name: 'policy.terms',
    component: PolicyTerms,
  },
  {
    path: '/kebijakan/privasi',
    name: 'policy.privacy',
    component: PolicyPrivacy,
  },
  {
    path: '/kebijakan/pengembalian',
    name: 'policy.return',
    component: PolicyReturn,
  },
  {
    path: '/kebijakan/komunitas',
    name: 'policy.community',
    component: PolicyCommunity,
  },
  {
    path: '/product/:id',
    name: 'product-detail',
    component: ProductDetail,
    alias: '/produk/:id',
    props: true,
  },

  // ══════════════════════════════
  // AUTH
  // ══════════════════════════════
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { guestOnly: true },   // redirect ke home jika sudah login
  },
  {
    path: '/register',
    name: 'register',
    component: Register,
    meta: { guestOnly: true },
  },
  {
    path: '/choose-role',
    name: 'role-selection',
    component: RoleSelection,
    meta: { requiresAuth: true },
  },

  // ══════════════════════════════
  // SELLER
  // ══════════════════════════════
  {
    path: '/seller',
    component: SellerLayout,
    meta: { requiresAuth: true, role: ['seller', 'seller_premium'] },
    children: [
      {
        path: '',
        name: 'seller.dashboard',
        component: SellerDashboard,
      },
      {
        path: 'produk',
        name: 'seller.produk',
        component: SellerProdukSaya,
      },
      {
        path: 'pesanan',
        name: 'seller.pesanan',
        component: SellerPesananMasuk,
      },
      {
        path: 'analitik',
        name: 'seller.analitik',
        component: SellerAnalitik,
      },
      {
        path: 'analitik-premium',
        name: 'seller.analitik-premium',
        component: SellerAnalyticsPremium,
        meta: { requiresPremium: true },
      },
      {
        path: 'marketing',
        name: 'seller.marketing',
        component: SellerMarketing,
        meta: { requiresPremium: true },
      },
      {
        path: 'featured',
        name: 'seller.featured',
        component: SellerFeatured,
        meta: { requiresPremium: true },
      },
      {
        path: 'saldo',
        name: 'seller.saldo',
        component: SellerSaldoDompet,
        meta: { disabled: true },
      },
      {
        path: 'blog',
        name: 'seller.blog',
        component: SellerBlogUsaha,
      },
      {
        path: 'profil',
        name: 'seller.profil',
        component: () => import('@/views/seller/StoreProfile.vue'),
      },
      {
        path: 'upgrade',
        name: 'seller.upgrade',
        component: SellerUpgradePremium,
      },
      {
        path: 'pengaturan',
        name: 'seller.pengaturan',
        component: SellerPengaturanToko,
      },
      {
        path: 'team',
        name: 'seller.team',
        component: TeamManagement,
        meta: { requiresPremium: true },
      },
    ],
  },
  {
    path: '/seller/apply',
    name: 'seller.apply',
    component: SellerApply,
    meta: { requiresAuth: true },
  },
  {
    path: '/seller/status',
    name: 'seller.status',
    component: SellerStatus,
    meta: { requiresAuth: true, role: ['seller', 'seller_premium'] },
  },
  {
    path: '/seller/:id',
    name: 'seller.public',
    component: SellerProfile,
    props: true,
  },

  // ══════════════════════════════
  // BUYER
  // ══════════════════════════════
  {
    path: '/buyer/dashboard',
    name: 'buyer.dashboard',
    component: BuyerDashboard,
    meta: { requiresAuth: true },
  },
  {
    path: '/buyer/favorit',
    alias: '/wishlist',
    name: 'buyer.favorite',
    component: () => import('@/views/buyer/BuyerFav.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/buyer/ulasan',
    name: 'buyer.reviews',
    component: () => import('@/views/buyer/BuyerUlasan.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/buyer/laporan',
    name: 'buyer.reports',
    component: () => import('@/views/buyer/BuyerLaporan.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/buyer/ordertracking/:id?',
    name: 'buyer.ordertracking',
    component: () => import('@/views/buyer/OrderDetail.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/buyer/profil',
    name: 'buyer.profile',
    component: BuyerProfil,
    meta: { requiresAuth: true },
  },
  {
    path: '/profil',
    redirect: { name: 'buyer.profile' },
  },
  {
    path: '/keranjang',
    name: 'buyer.cart',
    component: BuyerCart,
    meta: { requiresAuth: true },
  },
  {
    path: '/checkout',
    name: 'buyer.checkout',
    component: () => import('@/views/buyer/Checkout.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/buyer/orders',
    name: 'buyer.orders',
    component: BuyerOrders,
    meta: { requiresAuth: true },
  },
  {
    path: '/buyer/orders/:id/tracking',
    name: 'buyer.order.tracking',
    component: OrderTracking,
    meta: { requiresAuth: true },
  },
  {
    path: '/buyer/orders/:id',
    name: 'buyer.order.detail',
    component: OrderDetail,
    meta: { requiresAuth: true },
  },
  {
    path: '/buyer/review',
    name: 'buyer.review',
    component: BuyerReview,
    meta: { requiresAuth: true },
  },
  {
    path: '/notification-detail/:responseId',
    name: 'notification.detail',
    component: NotificationDetail,
    meta: { requiresAuth: true },
  },

  // ══════════════════════════════
  // ADMIN  (nested, pakai AdminLayout sebagai shell sidebar)
  // ══════════════════════════════
  {
    path: '/admin',
    component: AdminLayout,           // ← sidebar persisten ada di sini
    meta: { requiresAuth: true, role: 'admin' },
    children: [
      {
        path: '',                     // /admin  → Dashboard
        name: 'admin.dashboard',
        component: AdminDashboard,
      },
      {
        path: 'verifikasi',           // /admin/verifikasi  (gantikan /admin/apply-seller)
        name: 'admin.verifikasi',
        component: AdminVerifikasi,
      },
      {
        path: 'pengguna',             // /admin/pengguna
        name: 'admin.pengguna',
        component: AdminPengguna,
      },
      {
        path: 'konten',               // /admin/konten
        name: 'admin.konten',
        component: AdminKonten,
      },
      {
        path: 'pesanan',              // /admin/pesanan
        name: 'admin.pesanan',
        component: AdminPesanan,
      },
      {
        path: 'laporan',              // /admin/laporan
        name: 'admin.laporan',
        component: AdminLaporan,
      },
      {
        path: 'performa',             // /admin/performa
        name: 'admin.performa',
        component: AdminPerforma,
      },
      {
        path: 'premium-approval',     // /admin/premium-approval
        name: 'admin.premium',
        component: AdminPremiumApproval,
      },
      {
        path: 'pengaturan',           // /admin/pengaturan
        name: 'admin.pengaturan',
        component: AdminPengaturan,
      },

      // Redirect alias lama supaya link /admin/apply-seller masih bekerja
      {
        path: 'apply-seller',
        redirect: { name: 'admin.verifikasi' },
      },
    ],
  },

  // ══════════════════════════════
  // FALLBACK
  // ══════════════════════════════
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    redirect: '/',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0, behavior: 'smooth' }),
})

// ── Navigation Guard ────────────────────────────────────────
router.beforeEach((to, from, next) => {
  const token   = localStorage.getItem('token')
  const isLoggedIn = !!token

  // Role aktif: untuk dual role (buyer+seller) pakai activeRole
  let userRole = null
  try {
    const user = JSON.parse(localStorage.getItem('user') || '{}')
    if (user.role === 'admin') {
      userRole = 'admin'
    } else {
      userRole = localStorage.getItem('activeRole') || user.role || null
    }
  } catch {
    userRole = null
  }

  // Halaman butuh login tapi belum login → ke /login
  if (to.meta.requiresAuth && !isLoggedIn) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }

  // Jika route dinonaktifkan sementara via meta.disabled → redirect ke home
  if (to.meta && to.meta.disabled) {
    return next({ name: 'home' })
  }

  // Halaman butuh role tertentu tapi role tidak cocok → ke home
if (to.meta.role) {
  const required = Array.isArray(to.meta.role) ? to.meta.role : [to.meta.role]

  // kalau userRole kosong → anggap unauthorized
  if (!userRole) return next({ name: 'login' })

  if (!required.includes(userRole)) {
    return next({ name: 'home' })
  }
}

  // Tidak ada pengalihan profil karena sekarang menggunakan buyer.profile dan /profil mengarah ke sana

  // Halaman khusus tamu (login/register) tapi sudah login → ke home
  if (to.meta.guestOnly && isLoggedIn) {
    return next({ name: 'home' })
  }

  next()
})

export default router