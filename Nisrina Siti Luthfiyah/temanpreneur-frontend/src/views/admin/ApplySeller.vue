<template>
  <div class="admin-apply">

    <!-- Page Header -->
    <div class="admin-apply__header">
      <div class="admin-apply__header-left">
        <h1 class="admin-apply__title">Pengajuan <span>Usaha</span></h1>
        <p class="admin-apply__subtitle">Kelola dan verifikasi pengajuan usaha dari siswa</p>
      </div>
      <div class="admin-apply__header-right">
        <div class="admin-apply__stat admin-apply__stat--pending">
          <strong>{{ stats.pending }}</strong>
          <span>Menunggu</span>
        </div>
        <div class="admin-apply__stat admin-apply__stat--approved">
          <strong>{{ stats.approved }}</strong>
          <span>Disetujui</span>
        </div>
        <div class="admin-apply__stat admin-apply__stat--rejected">
          <strong>{{ stats.rejected }}</strong>
          <span>Ditolak</span>
        </div>
      </div>
    </div>

    <!-- Filter & Search -->
    <div class="admin-apply__toolbar">
      <div class="admin-apply__search">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
          <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
          <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari nama usaha atau siswa..."
        />
      </div>

      <div class="admin-apply__filters">
        <button
          v-for="tab in statusTabs"
          :key="tab.id"
          class="admin-apply__filter-btn"
          :class="{ 'admin-apply__filter-btn--active': activeStatus === tab.id }"
          @click="activeStatus = tab.id"
        >
          {{ tab.icon }} {{ tab.label }}
          <span class="admin-apply__filter-count">{{ tab.count }}</span>
        </button>
      </div>

      <select v-model="activeKategori" class="admin-apply__select">
        <option value="">Semua Kategori</option>
        <option v-for="k in kategoriList" :key="k.id" :value="k.id">{{ k.icon }} {{ k.nama }}</option>
      </select>
    </div>

    <!-- Loading -->
    <div class="admin-apply__loading" v-if="loading">
      <div v-for="n in 4" :key="n" class="admin-apply__skeleton"></div>
    </div>

    <!-- Empty -->
    <div class="admin-apply__empty" v-else-if="!filteredApplications.length">
      <span></span>
      <p>Tidak ada pengajuan {{ activeStatus !== 'all' ? 'dengan status ini' : '' }}</p>
    </div>

    <!-- Table / List -->
    <div class="admin-apply__list" v-else>
      <div
        v-for="app in filteredApplications"
        :key="app.id"
        class="admin-apply__card"
        :class="`admin-apply__card--${app.status}`"
      >
        <!-- Status stripe -->
        <div class="admin-apply__stripe"></div>

        <!-- Card content -->
        <div class="admin-apply__card-main">

          <!-- Left: Info -->
          <div class="admin-apply__card-info">
            <div class="admin-apply__card-top">
              <div class="admin-apply__business-icon">{{ getCategoryIcon(app.category) }}</div>
              <div>
                <h3 class="admin-apply__business-name">{{ app.name }}</h3>
                <div class="admin-apply__meta-row">
                  <span class="admin-apply__kat-tag">{{ getCategoryLabel(app.category) }}</span>
                  <span class="admin-apply__meta-dot">·</span>
                  <span class="admin-apply__date">{{ formatDate(app.created_at) }}</span>
                </div>
              </div>
              <span class="admin-apply__status-badge" :class="`admin-apply__status-badge--${app.status}`">
                {{ statusIcon[app.status] }} {{ statusLabel[app.status] }}
              </span>
            </div>

            <p class="admin-apply__desc">{{ app.description }}</p>

            <!-- Pengaju info -->
            <div class="admin-apply__pengaju">
              <div class="admin-apply__pengaju-avatar" :style="`background:${getAvatarColor(app.user_id)}`">
                {{ app.user_name.charAt(0) }}
              </div>
              <div class="admin-apply__pengaju-info">
                <p class="admin-apply__pengaju-name">{{ app.user_name }}</p>
                <p class="admin-apply__pengaju-meta">{{ app.user_class }} · {{ app.user_email }}</p>
              </div>
              <a :href="`https://wa.me/62${app.phone}`" target="_blank" class="admin-apply__wa-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                  <path d="M12 0C5.373 0 0 5.373 0 12c0 2.134.558 4.13 1.535 5.865L.057 24l6.305-1.654A11.954 11.954 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.818 9.818 0 01-5.034-1.388l-.361-.214-3.741.981.999-3.648-.235-.374A9.818 9.818 0 0112 2.182c5.42 0 9.818 4.398 9.818 9.818 0 5.42-4.398 9.818-9.818 9.818z"/>
                </svg>
                WhatsApp
              </a>
            </div>

            <!-- Catatan penolakan -->
            <div class="admin-apply__rejection-note" v-if="app.status === 'rejected' && app.rejection_reason">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
              <span>Alasan penolakan: {{ app.rejection_reason }}</span>
            </div>
          </div>

          <!-- Right: Actions -->
          <div class="admin-apply__card-actions" v-if="app.status === 'pending'">
            <button
              class="admin-apply__action-btn admin-apply__action-btn--approve"
              @click="openConfirm(app, 'approve')"
              :disabled="processingId === app.id"
            >
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><polyline points="20,6 9,17 4,12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Setujui
            </button>
            <button
              class="admin-apply__action-btn admin-apply__action-btn--reject"
              @click="openConfirm(app, 'reject')"
              :disabled="processingId === app.id"
            >
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
              Tolak
            </button>
          </div>

          <div class="admin-apply__card-status-info" v-else>
            <p class="admin-apply__processed-at" v-if="app.processed_at">
              {{ app.status === 'approved' ? ' Disetujui' : ' Ditolak' }}
              {{ formatDate(app.processed_at) }}
            </p>
            <button
              v-if="app.status === 'rejected'"
              class="admin-apply__action-btn admin-apply__action-btn--reopen"
              @click="openConfirm(app, 'approve')"
            >
              Batalkan Penolakan
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div class="admin-apply__pagination" v-if="totalPages > 1">
      <button :disabled="currentPage === 1" @click="currentPage--" class="admin-apply__page-btn">← Prev</button>
      <span>Halaman {{ currentPage }} dari {{ totalPages }}</span>
      <button :disabled="currentPage === totalPages" @click="currentPage++" class="admin-apply__page-btn">Next →</button>
    </div>

    <!-- ── MODAL KONFIRMASI ── -->
    <teleport to="body">
      <div class="modal-overlay" v-if="confirmModal.open" @click.self="closeConfirm">
        <div class="modal" :class="`modal--${confirmModal.action}`">
          <div class="modal__icon">
            {{ confirmModal.action === 'approve' ? '' : '' }}
          </div>
          <h3 class="modal__title">
            {{ confirmModal.action === 'approve' ? 'Setujui Pengajuan?' : 'Tolak Pengajuan?' }}
          </h3>
          <p class="modal__desc">
            Usaha <strong>{{ confirmModal.app?.name }}</strong> dari
            <strong>{{ confirmModal.app?.user_name }}</strong>
            akan {{ confirmModal.action === 'approve' ? 'disetujui dan toko akan diaktifkan.' : 'ditolak.' }}
          </p>

          <!-- Alasan penolakan -->
          <div class="modal__reason" v-if="confirmModal.action === 'reject'">
            <label class="modal__reason-label">Alasan Penolakan <span>(opsional)</span></label>
            <textarea
              v-model="confirmModal.reason"
              class="modal__reason-input"
              placeholder="Contoh: Deskripsi usaha kurang lengkap, atau produk tidak sesuai kategori..."
              rows="3"
            ></textarea>
          </div>

          <div class="modal__actions">
            <button class="modal__btn modal__btn--ghost" @click="closeConfirm" :disabled="processingId !== null">
              Batal
            </button>
            <button
              class="modal__btn"
              :class="confirmModal.action === 'approve' ? 'modal__btn--approve' : 'modal__btn--reject'"
              @click="processAction"
              :disabled="processingId !== null"
            >
              <span class="modal__spinner" v-if="processingId !== null"></span>
              {{ processingId !== null ? 'Memproses...' : (confirmModal.action === 'approve' ? 'Ya, Setujui' : 'Ya, Tolak') }}
            </button>
          </div>
        </div>
      </div>
    </teleport>

    <!-- Toast -->
    <div class="admin-apply__toast" :class="{ 'admin-apply__toast--show': toast.show, [`admin-apply__toast--${toast.type}`]: true }">
      {{ toast.message }}
    </div>

  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@/api/axios'

export default {
  name: 'AdminBusinessApplications',
  setup() {
    const loading      = ref(true)
    const processingId = ref(null)
    const searchQuery  = ref('')
    const activeStatus = ref('all')
    const activeKategori = ref('')
    const currentPage  = ref(1)
    const perPage      = 8
    const applications = ref([])

    const confirmModal = reactive({
      open:   false,
      action: '',
      app:    null,
      reason: '',
    })

    const toast = reactive({ show: false, message: '', type: 'success' })

    // ── Helpers ────────────────────────────────────────────────
    const kategoriList = [
      { id:'fashion',   icon:'', nama:'Fashion'   },
      { id:'kuliner',   icon:'', nama:'Kuliner'   },
      { id:'kerajinan', icon:'', nama:'Kerajinan' },
      { id:'digital',   icon:'', nama:'Digital'   },
      { id:'aksesoris', icon:'', nama:'Aksesoris' },
      { id:'lainnya',   icon:'', nama:'Lainnya'   },
    ]

    const statusLabel = { pending:'Menunggu', approved:'Disetujui', rejected:'Ditolak' }
    const statusIcon  = { pending:'⏳',        approved:'',        rejected:'' }

    const avatarColors = [
      'linear-gradient(135deg,#f43f5e,#e11d48)',
      'linear-gradient(135deg,#6366f1,#4f46e5)',
      'linear-gradient(135deg,#10b981,#059669)',
      'linear-gradient(135deg,#f59e0b,#d97706)',
      'linear-gradient(135deg,#ec4899,#db2777)',
      'linear-gradient(135deg,#0ea5e9,#0284c7)',
    ]
    const getAvatarColor  = (id) => avatarColors[id % avatarColors.length]
    const getCategoryIcon  = (cat) => kategoriList.find(k => k.id === cat)?.icon  || ''
    const getCategoryLabel = (cat) => kategoriList.find(k => k.id === cat)?.nama  || 'Lainnya'

    const formatDate = (iso) => {
      if (!iso) return '-'
      return new Intl.DateTimeFormat('id-ID', { day:'numeric', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' }).format(new Date(iso))
    }

    // ── Stats ──────────────────────────────────────────────────
    const stats = computed(() => ({
      pending:  applications.value.filter(a => a.status === 'pending').length,
      approved: applications.value.filter(a => a.status === 'approved').length,
      rejected: applications.value.filter(a => a.status === 'rejected').length,
    }))

    const statusTabs = computed(() => [
      { id:'all',      icon:'', label:'Semua',    count: applications.value.length         },
      { id:'pending',  icon:'⏳', label:'Menunggu', count: stats.value.pending   },
      { id:'approved', icon:'', label:'Disetujui',count: stats.value.approved  },
      { id:'rejected', icon:'', label:'Ditolak',  count: stats.value.rejected  },
    ])

    // ── Filter + pagination ────────────────────────────────────
    const filteredApplications = computed(() => {
      let list = [...applications.value]
      if (activeStatus.value !== 'all')  list = list.filter(a => a.status === activeStatus.value)
      if (activeKategori.value)          list = list.filter(a => a.category === activeKategori.value)
      if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase()
        list = list.filter(a =>
          a.name.toLowerCase().includes(q) ||
          a.user_name.toLowerCase().includes(q) ||
          a.user_email.toLowerCase().includes(q)
        )
      }
      const start = (currentPage.value - 1) * perPage
      return list.slice(start, start + perPage)
    })

    const totalPages = computed(() => {
      let list = applications.value
      if (activeStatus.value !== 'all')  list = list.filter(a => a.status === activeStatus.value)
      if (activeKategori.value)          list = list.filter(a => a.category === activeKategori.value)
      return Math.max(1, Math.ceil(list.length / perPage))
    })

    // ── Fetch ──────────────────────────────────────────────────
    const fetchApplications = async () => {
      loading.value = true
      try {
        // Admin list: GET /businesses (auth + role admin), not /admin/businesses
        const res = await api.get('/businesses')
        const raw = res.data.data || res.data || []
        applications.value = raw.map((b) => ({
          ...b,
          user_id: b.user_id ?? b.user?.id,
          user_name: b.user?.name ?? b.user_name ?? '—',
          user_email: b.user?.email ?? b.user_email ?? '',
          user_class: b.user?.class ?? b.user_class ?? '',
        }))
        console.debug('[ApplySeller] businesses loaded', applications.value.length)
      } catch (e) {
        console.error('[ApplySeller] fetchApplications', e.response?.status, e.response?.data)
        applications.value = []
      } finally {
        loading.value = false
      }
    }

    // ── Modal & Actions ────────────────────────────────────────
    const openConfirm  = (app, action) => { confirmModal.open = true; confirmModal.app = app; confirmModal.action = action; confirmModal.reason = '' }
    const closeConfirm = () => { confirmModal.open = false; confirmModal.app = null; confirmModal.reason = '' }

    const showToast = (message, type = 'success') => {
      toast.message = message; toast.type = type; toast.show = true
      setTimeout(() => { toast.show = false }, 3500)
    }

    const processAction = async () => {
      if (!confirmModal.app) return
      processingId.value = confirmModal.app.id
      try {
        const endpoint = confirmModal.action === 'approve'
          ? `/admin/businesses/${confirmModal.app.id}/approve`
          : `/admin/businesses/${confirmModal.app.id}/reject`

        await api.post(endpoint, {
          reason: confirmModal.reason || undefined,
        })

        // Update local state
        const idx = applications.value.findIndex(a => a.id === confirmModal.app.id)
        if (idx !== -1) {
          applications.value[idx].status       = confirmModal.action === 'approve' ? 'approved' : 'rejected'
          applications.value[idx].processed_at  = new Date().toISOString()
          applications.value[idx].rejection_reason = confirmModal.reason || ''
        }

        showToast(
          confirmModal.action === 'approve'
            ? ` Usaha "${confirmModal.app.name}" berhasil disetujui!`
            : ` Usaha "${confirmModal.app.name}" telah ditolak.`,
          confirmModal.action === 'approve' ? 'success' : 'error'
        )
        closeConfirm()
      } catch (err) {
        showToast(err.response?.data?.message || 'Gagal memproses pengajuan.', 'error')
      } finally {
        processingId.value = null
      }
    }

    onMounted(fetchApplications)

    return {
      loading, processingId, searchQuery, activeStatus, activeKategori,
      applications, filteredApplications, currentPage, totalPages,
      confirmModal, toast, stats, statusTabs,
      kategoriList, statusLabel, statusIcon,
      getAvatarColor, getCategoryIcon, getCategoryLabel, formatDate,
      openConfirm, closeConfirm, processAction,
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:wght@700;900&display=swap');

.admin-apply {
  padding: 32px 28px;
  max-width: 1100px;
  font-family: 'Plus Jakarta Sans', sans-serif;
  position: relative;
}

/* ── Header ── */
.admin-apply__header {
  display: flex; align-items: flex-start; justify-content: space-between;
  flex-wrap: wrap; gap: 20px; margin-bottom: 28px;
}
.admin-apply__title {
  font-family: 'Fraunces', serif; font-size: 1.9rem; font-weight: 900; color: #111827; line-height: 1.15;
}
.admin-apply__title span { color: #e53e3e; }
.admin-apply__subtitle { font-size: .875rem; color: #9ca3af; margin-top: 4px; }
.admin-apply__header-right { display: flex; gap: 12px; }
.admin-apply__stat {
  text-align: center; padding: 12px 20px; border-radius: 12px;
  border: 1.5px solid; min-width: 72px;
}
.admin-apply__stat strong { display: block; font-size: 1.5rem; font-weight: 900; }
.admin-apply__stat span { font-size: .72rem; font-weight: 600; }
.admin-apply__stat--pending  { border-color: #fde68a; background: #fffbeb; }
.admin-apply__stat--pending strong, .admin-apply__stat--pending span { color: #d97706; }
.admin-apply__stat--approved { border-color: #bbf7d0; background: #f0fdf4; }
.admin-apply__stat--approved strong, .admin-apply__stat--approved span { color: #059669; }
.admin-apply__stat--rejected { border-color: #fecaca; background: #fff5f5; }
.admin-apply__stat--rejected strong, .admin-apply__stat--rejected span { color: #c53030; }

/* ── Toolbar ── */
.admin-apply__toolbar {
  display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
  margin-bottom: 24px;
}
.admin-apply__search {
  display: flex; align-items: center; gap: 10px;
  flex: 1; min-width: 200px; max-width: 320px;
  height: 40px; padding: 0 14px;
  border: 1.5px solid #e5e7eb; border-radius: 10px; background: #fff;
  color: #9ca3af; transition: border-color .2s, box-shadow .2s;
}
.admin-apply__search:focus-within { border-color: #fca5a5; box-shadow: 0 0 0 3px rgba(229,62,62,.08); }
.admin-apply__search input {
  flex: 1; border: none; outline: none; background: transparent;
  font-family: 'Plus Jakarta Sans', sans-serif; font-size: .875rem; color: #111827;
}
.admin-apply__filters { display: flex; gap: 6px; flex-wrap: wrap; }
.admin-apply__filter-btn {
  display: flex; align-items: center; gap: 6px; padding: 7px 14px;
  border: 1.5px solid #e5e7eb; border-radius: 8px; background: #fff;
  font-family: 'Plus Jakarta Sans', sans-serif; font-size: .82rem; font-weight: 600;
  color: #6b7280; cursor: pointer; transition: all .2s;
}
.admin-apply__filter-btn:hover { border-color: #fca5a5; color: #e53e3e; background: #fff5f5; }
.admin-apply__filter-btn--active { border-color: #e53e3e; color: #e53e3e; background: #fff5f5; }
.admin-apply__filter-count {
  background: #e5e7eb; color: #6b7280; padding: 1px 7px; border-radius: 100px; font-size: .7rem;
}
.admin-apply__filter-btn--active .admin-apply__filter-count { background: #fecaca; color: #c53030; }
.admin-apply__select {
  height: 40px; padding: 0 12px; border: 1.5px solid #e5e7eb; border-radius: 10px;
  font-family: 'Plus Jakarta Sans', sans-serif; font-size: .85rem; color: #374151;
  background: #fff; outline: none; cursor: pointer;
}

/* ── Loading / Empty ── */
.admin-apply__loading { display: flex; flex-direction: column; gap: 12px; }
.admin-apply__skeleton {
  height: 140px; border-radius: 14px;
  background: linear-gradient(90deg,#f3f4f6 25%,#e5e7eb 50%,#f3f4f6 75%);
  background-size: 200% 100%; animation: shimmer 1.4s infinite;
}
@keyframes shimmer { 0%{ background-position:200% 0; } 100%{ background-position:-200% 0; } }
.admin-apply__empty { text-align: center; padding: 64px; color: #9ca3af; font-size: .95rem; }
.admin-apply__empty span { font-size: 3rem; display: block; margin-bottom: 12px; }

/* ── Cards ── */
.admin-apply__list { display: flex; flex-direction: column; gap: 14px; }
.admin-apply__card {
  background: #fff; border: 1.5px solid #f3f4f6; border-radius: 16px;
  overflow: hidden; display: flex; transition: box-shadow .2s;
}
.admin-apply__card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.07); }
.admin-apply__card--pending  { border-color: #fde68a; }
.admin-apply__card--approved { border-color: #bbf7d0; }
.admin-apply__card--rejected { border-color: #fecaca; }

.admin-apply__stripe { width: 5px; flex-shrink: 0; }
.admin-apply__card--pending  .admin-apply__stripe { background: linear-gradient(180deg,#f59e0b,#d97706); }
.admin-apply__card--approved .admin-apply__stripe { background: linear-gradient(180deg,#10b981,#059669); }
.admin-apply__card--rejected .admin-apply__stripe { background: linear-gradient(180deg,#ef4444,#c53030); }

.admin-apply__card-main {
  flex: 1; display: flex; align-items: flex-start; gap: 20px; padding: 20px 22px;
}
.admin-apply__card-info { flex: 1; min-width: 0; }
.admin-apply__card-top {
  display: flex; align-items: flex-start; gap: 12px; margin-bottom: 10px;
  flex-wrap: wrap;
}
.admin-apply__business-icon {
  width: 44px; height: 44px; border-radius: 10px; background: #f9fafb;
  border: 1.5px solid #e5e7eb; display: flex; align-items: center;
  justify-content: center; font-size: 1.4rem; flex-shrink: 0;
}
.admin-apply__business-name { font-size: 1.02rem; font-weight: 800; color: #111827; line-height: 1.2; }
.admin-apply__meta-row { display: flex; align-items: center; gap: 6px; margin-top: 4px; }
.admin-apply__kat-tag {
  font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing:.04em;
  color: #e53e3e; background: #fff5f5; padding: 2px 8px; border-radius: 4px;
}
.admin-apply__meta-dot { color: #d1d5db; }
.admin-apply__date { font-size: .75rem; color: #9ca3af; }
.admin-apply__status-badge {
  margin-left: auto; padding: 4px 12px; border-radius: 100px; font-size: .73rem; font-weight: 700;
}
.admin-apply__status-badge--pending  { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
.admin-apply__status-badge--approved { background: #f0fdf4; color: #059669; border: 1px solid #bbf7d0; }
.admin-apply__status-badge--rejected { background: #fff5f5; color: #c53030; border: 1px solid #fecaca; }

.admin-apply__desc {
  font-size: .85rem; color: #6b7280; line-height: 1.65; margin-bottom: 14px;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}

.admin-apply__pengaju {
  display: flex; align-items: center; gap: 10px; padding: 10px 14px;
  background: #f9fafb; border-radius: 10px; border: 1px solid #f3f4f6;
}
.admin-apply__pengaju-avatar {
  width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: .85rem; font-weight: 800;
}
.admin-apply__pengaju-name { font-size: .83rem; font-weight: 700; color: #111827; }
.admin-apply__pengaju-meta { font-size: .72rem; color: #9ca3af; }
.admin-apply__wa-btn {
  margin-left: auto; display: flex; align-items: center; gap: 6px;
  padding: 5px 12px; background: #dcfce7; color: #15803d;
  border-radius: 100px; font-size: .72rem; font-weight: 700; text-decoration: none;
  transition: all .2s; border: 1px solid #bbf7d0;
}
.admin-apply__wa-btn:hover { background: #16a34a; color: #fff; }

.admin-apply__rejection-note {
  display: flex; align-items: flex-start; gap: 8px; margin-top: 10px;
  padding: 8px 12px; background: #fff5f5; border-radius: 8px; border: 1px solid #fecaca;
  font-size: .78rem; color: #c53030;
}

/* Card actions */
.admin-apply__card-actions { display: flex; flex-direction: column; gap: 8px; flex-shrink: 0; min-width: 110px; }
.admin-apply__card-status-info { display: flex; flex-direction: column; gap: 8px; flex-shrink: 0; min-width: 130px; align-items: flex-end; }
.admin-apply__processed-at { font-size: .75rem; color: #9ca3af; text-align: right; }

.admin-apply__action-btn {
  display: flex; align-items: center; justify-content: center; gap: 6px;
  padding: 9px 16px; border-radius: 9px; border: none; cursor: pointer;
  font-family: 'Plus Jakarta Sans', sans-serif; font-size: .82rem; font-weight: 700;
  transition: all .2s; white-space: nowrap;
}
.admin-apply__action-btn:disabled { opacity: .6; cursor: not-allowed; }
.admin-apply__action-btn--approve { background: #dcfce7; color: #15803d; border: 1.5px solid #bbf7d0; }
.admin-apply__action-btn--approve:hover:not(:disabled) { background: #16a34a; color: #fff; }
.admin-apply__action-btn--reject  { background: #fff5f5; color: #c53030; border: 1.5px solid #fecaca; }
.admin-apply__action-btn--reject:hover:not(:disabled)  { background: #e53e3e; color: #fff; }
.admin-apply__action-btn--reopen  { background: #f9fafb; color: #6b7280; border: 1.5px solid #e5e7eb; font-size: .75rem; }
.admin-apply__action-btn--reopen:hover { background: #e5e7eb; }

/* ── Pagination ── */
.admin-apply__pagination {
  display: flex; align-items: center; justify-content: center; gap: 16px;
  margin-top: 28px; font-size: .85rem; color: #6b7280;
}
.admin-apply__page-btn {
  padding: 8px 18px; border: 1.5px solid #e5e7eb; border-radius: 8px;
  background: #fff; font-family: 'Plus Jakarta Sans', sans-serif; font-size: .82rem;
  font-weight: 600; color: #374151; cursor: pointer; transition: all .2s;
}
.admin-apply__page-btn:hover:not(:disabled) { border-color: #fca5a5; color: #e53e3e; background: #fff5f5; }
.admin-apply__page-btn:disabled { opacity: .4; cursor: not-allowed; }

/* ── Modal ── */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,.45); backdrop-filter: blur(4px);
  z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 24px;
}
.modal {
  background: #fff; border-radius: 20px; padding: 36px 32px;
  max-width: 440px; width: 100%; text-align: center;
  box-shadow: 0 20px 60px rgba(0,0,0,.2); animation: modalIn .25s ease;
}
@keyframes modalIn { from{ opacity:0; transform:scale(.94) translateY(12px); } to{ opacity:1; transform:scale(1) translateY(0); } }
.modal__icon { font-size: 3rem; margin-bottom: 14px; }
.modal__title { font-family: 'Fraunces', serif; font-size: 1.4rem; font-weight: 900; color: #111827; margin-bottom: 10px; }
.modal__desc { font-size: .88rem; color: #6b7280; line-height: 1.65; margin-bottom: 20px; }
.modal__reason { text-align: left; margin-bottom: 20px; }
.modal__reason-label { display: block; font-size: .82rem; font-weight: 700; color: #374151; margin-bottom: 6px; }
.modal__reason-label span { color: #9ca3af; font-weight: 400; }
.modal__reason-input {
  width: 100%; padding: 10px 13px; border: 1.5px solid #e5e7eb; border-radius: 10px;
  font-family: 'Plus Jakarta Sans', sans-serif; font-size: .85rem; outline: none; resize: vertical;
  transition: border-color .2s, box-shadow .2s;
}
.modal__reason-input:focus { border-color: #fca5a5; box-shadow: 0 0 0 3px rgba(229,62,62,.08); }
.modal__actions { display: flex; gap: 10px; justify-content: center; }
.modal__btn {
  flex: 1; padding: 11px 20px; border-radius: 10px; border: none; cursor: pointer;
  font-family: 'Plus Jakarta Sans', sans-serif; font-size: .875rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center; gap: 8px; transition: all .2s;
}
.modal__btn:disabled { opacity: .6; cursor: not-allowed; }
.modal__btn--ghost   { background: #f3f4f6; color: #6b7280; }
.modal__btn--ghost:hover:not(:disabled) { background: #e5e7eb; }
.modal__btn--approve { background: linear-gradient(135deg,#22c55e,#16a34a); color: #fff; box-shadow: 0 3px 12px rgba(16,185,129,.3); }
.modal__btn--approve:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(16,185,129,.4); }
.modal__btn--reject  { background: linear-gradient(135deg,#f56565,#c53030); color: #fff; box-shadow: 0 3px 12px rgba(229,62,62,.3); }
.modal__btn--reject:hover:not(:disabled)  { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(229,62,62,.4); }
.modal__spinner { width: 15px; height: 15px; border: 2px solid rgba(255,255,255,.4); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; }
@keyframes spin { to{ transform:rotate(360deg); } }

/* ── Toast ── */
.admin-apply__toast {
  position: fixed; bottom: 28px; right: 28px; z-index: 2000;
  padding: 14px 22px; border-radius: 12px; font-size: .875rem; font-weight: 600;
  box-shadow: 0 8px 28px rgba(0,0,0,.15); max-width: 360px;
  transform: translateY(20px); opacity: 0; transition: all .3s ease;
  pointer-events: none;
}
.admin-apply__toast--show { transform: translateY(0); opacity: 1; }
.admin-apply__toast--success { background: #111827; color: #fff; }
.admin-apply__toast--error   { background: #fff5f5; color: #c53030; border: 1px solid #fecaca; }

/* ── Responsive ── */
@media (max-width: 768px) {
  .admin-apply { padding: 20px 16px; }
  .admin-apply__header { flex-direction: column; }
  .admin-apply__card-main { flex-direction: column; }
  .admin-apply__card-actions { flex-direction: row; }
  .admin-apply__card-status-info { align-items: flex-start; }
  .admin-apply__toolbar { flex-direction: column; align-items: stretch; }
  .admin-apply__search { max-width: 100%; }
}
@media (max-width: 480px) {
  .admin-apply__card-top { gap: 8px; }
  .admin-apply__status-badge { margin-left: 0; }
  .modal { padding: 24px 18px; }
  .modal__actions { flex-direction: column; }
}
</style>