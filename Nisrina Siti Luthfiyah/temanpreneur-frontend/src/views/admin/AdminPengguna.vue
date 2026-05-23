<template>
  <div class="ap">
    <div class="ap__head">
      <div>
        <h1 class="ap__title">Manajemen <span>Pengguna</span></h1>
        <p class="ap__sub">Kelola akun siswa, seller, dan admin platform</p>
      </div>
      <div class="ap__head-actions">
        <button class="ap__btn ap__btn--primary" @click="openAdd">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
            <line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
            <line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
          </svg>
          Tambah Pengguna
        </button>
        <div class="ap__export-buttons">
          <button class="ap__btn ap__btn--outline" @click="exportData('pdf')" :disabled="exporting">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2"/>
              <polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2"/>
            </svg>
            Export PDF
          </button>
          <button class="ap__btn ap__btn--outline" @click="exportData('excel')" :disabled="exporting">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2"/>
              <polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2"/>
              <path d="M16 13H8" stroke="currentColor" stroke-width="2"/>
              <path d="M16 17H8" stroke="currentColor" stroke-width="2"/>
              <polyline points="10,9 9,9 8,9" stroke="currentColor" stroke-width="2"/>
            </svg>
            Export Excel
          </button>
          <button class="ap__btn ap__btn--outline" @click="exportData('word')" :disabled="exporting">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2"/>
              <polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2"/>
              <path d="M16 13H8" stroke="currentColor" stroke-width="2"/>
              <path d="M16 17H8" stroke="currentColor" stroke-width="2"/>
              <path d="M10 9h4" stroke="currentColor" stroke-width="2"/>
            </svg>
            Export Word
          </button>
        </div>
      </div>
    </div>

    <div class="pg__stats">
      <div class="pg__stat-card" v-for="s in statCards" :key="s.label" :style="`--c:${s.color}`">
        <div class="pg__stat-icon" v-html="s.icon"></div>
        <div><p class="pg__stat-val">{{ s.val }}</p><p class="pg__stat-lbl">{{ s.label }}</p></div>
      </div>
    </div>

    <div class="ap__card">
      <div class="ap__toolbar">
        <div class="ap__search">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
            <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
            <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          <input v-model="searchQuery" placeholder="Cari nama, email, atau kelas..."/>
        </div>
        <select v-model="filterRole" class="ap__select">
          <option value="">Semua Role</option>
          <option value="admin">Admin</option>
          <option value="seller_premium">Seller Premium</option>
          <option value="seller">Seller</option>
          <option value="buyer">Buyer</option>
        </select>
        <select v-model="filterStatus" class="ap__select">
          <option value="">Semua Status</option>
          <option value="active">Aktif</option>
          <option value="banned">Diblokir</option>
          <option value="pending">Diproses</option>
          <option value="deleted">Dihapus</option>
        </select>
        <span class="pg__count">{{ filtered.length }} pengguna</span>
      </div>

      <div style="padding:0 22px 20px;display:flex;flex-direction:column;gap:8px;" v-if="loading">
        <div v-for="n in 6" :key="`skeleton-${n}`" class="skeleton" style="height:54px;"></div>
      </div>

      <div v-else>
        <div class="ap__th pg__grid">
          <span>Pengguna</span><span>Role</span><span>Kelas</span><span>Bergabung</span><span>Status</span><span>Aksi</span>
        </div>
        <div class="ap__tr pg__grid" v-for="u in paginated" :key="u.id">
          <div class="pg__user-cell">
            <div class="pg__avatar" :style="{ background: roleColor(u.role || 'buyer') }">
                <img v-if="u.photo" :src="formatImage(u.photo)" alt="avatar" @error="handleImageError" />
              <span v-else>
                {{ (u.name || 'U')?.charAt(0).toUpperCase() }}
              </span>
            </div>
            <div>
              <p class="pg__user-name">{{ u.name }}</p>
              <p class="pg__user-email">{{ u.email }}</p>
            </div>
          </div>
          <span class="ap__badge" :class="roleBadge(u.role)">{{ roleLabel(u.role) }}</span>
          <span class="pg__class">{{ u.class || '—' }}</span>
          <span class="pg__date">{{ fmtDate(u.created_at) }}</span>
          <span class="ap__badge" :class="statusBadgeClass(u.status)">
            {{ statusLabel(u.status) }}
          </span>
          <div class="pg__actions">
            <button class="pg__act-btn pg__act-btn--edit" @click="openEdit(u)" :disabled="actionLoading[u.id]" title="Edit">️</button>
            <button class="pg__act-btn" :class="getActionButtonClass(u.status)" @click="toggleBan(u)" :disabled="actionLoading[u.id]" :title="getActionButtonTitle(u.status)">
              {{ getActionButtonIcon(u.status) }}
            </button>
            <button class="pg__act-btn pg__act-btn--del" @click="confirmDelete(u)" :disabled="actionLoading[u.id]" title="Hapus">️</button>
          </div>
        </div>
        <div class="empty-state" v-if="!filtered.length"><span></span><p>Tidak ada pengguna ditemukan</p></div>
      </div>

      <div class="ap__pagination" v-if="totalPages>1">
        <span class="ap__pagination-info">{{ (page-1)*perPage+1 }}–{{ Math.min(page*perPage,filtered.length) }} dari {{ filtered.length }}</span>
        <div class="ap__pagination-btns">
          <button class="ap__page-btn" :disabled="page===1" @click="page--">‹</button>
          <button v-for="p in Array.from({length:totalPages},(_,i)=>i+1)" :key="p" class="ap__page-btn" :class="{'ap__page-btn--active':page===p}" @click="page=p">{{ p }}</button>
          <button class="ap__page-btn" :disabled="page===totalPages" @click="page++">›</button>
        </div>
      </div>
    </div>

    <teleport to="body">
      <div class="modal-bg" v-if="formModal.open" @click.self="formModal.open=false">
        <div class="modal-box pg__form-modal">
          <h3>{{ formModal.isEdit?'Edit Pengguna':'Tambah Pengguna' }}</h3>
          <div class="pg__form-grid">
            <div class="pg__field">
              <label>Nama Lengkap</label>
              <input v-model="formModal.data.name" type="text" placeholder="Nama Lengkap"/>
            </div>
            <div class="pg__field">
              <label>Email</label>
              <input v-model="formModal.data.email" type="email" placeholder="email@sekolah.id"/>
            </div>
            <div class="pg__field">
              <label>Kelas</label>
              <input v-model="formModal.data.class" type="text" placeholder="XI IPA 1"/>
            </div>
            <div class="pg__field">
              <label>Role</label>
              <select v-model="formModal.data.role">
                <option value="buyer">Buyer</option>
                <option value="seller">Seller</option>
                <option value="seller_premium">Seller Premium</option>
                <option value="admin">Admin</option>
              </select>
            </div>
          </div>
          <div class="modal-box__btns">
            <button class="ap__btn ap__btn--ghost" @click="formModal.open=false">Batal</button>
            <button class="ap__btn ap__btn--primary" @click="saveUser" :disabled="saving">
              {{ saving?'Menyimpan...':'Simpan' }}
            </button>
          </div>
        </div>
      </div>
    </teleport>

    <div class="ap__toast" :class="{'ap__toast--show':toast.show,'ap__toast--err':toast.err}">{{ toast.msg }}</div>
  </div>
</template>

<script lang="js">
import { ref, computed, onMounted, onUnmounted, reactive, watch } from 'vue'
import api from '@/api/axios'
import { normalizeImageUrl } from '@/utils/image'

// Fungsi debounce sederhana (tanpa lodash)
function debounce(fn, delay) {
  let timer = null
  return function(...args) {
    if (timer) clearTimeout(timer)
    timer = setTimeout(() => fn(...args), delay)
  }
}

export default {
  name: 'AdminPengguna',
  setup() {
    const loading = ref(true)
    const saving  = ref(false)
    const exporting = ref(false)
    const actionLoading = reactive({})
    const searchInput = ref('')
    const q = ref('')
    const updateSearch = debounce((val) => { q.value = val }, 300)
    watch(searchInput, (newVal) => updateSearch(newVal))

    const filterRole   = ref('')
    const filterStatus = ref('')
    const page    = ref(1)
    const perPage = 10
    const users   = ref([])
    const toast   = reactive({ show:false, msg:'', err:false })
    const formModal = reactive({ open:false, isEdit:false, data:{name:'',email:'',class:'',role:'buyer'} })

    let abortController = null
    // Flag untuk mencegah fetch ulang jika sudah pernah
    let fetched = false

    const colors = { admin:'linear-gradient(135deg,#6366f1,#4f46e5)', seller:'linear-gradient(135deg,#e53e3e,#c53030)', seller_premium:'linear-gradient(135deg,#f59e0b,#d97706)', buyer:'linear-gradient(135deg,#10b981,#059669)' }

    // Helper functions
    const formatImage = (url) => normalizeImageUrl(url, '')

    const handleImageError = (event) => {
      event.target.style.display = 'none'
      event.target.nextElementSibling.style.display = 'block'
    }

    const roleColor = r => colors[r]||'linear-gradient(135deg,#9ca3af,#6b7280)'
    const roleBadge = r => ({'admin':'ap__badge--blue','seller':'ap__badge--red','seller_premium':'ap__badge--yellow','buyer':'ap__badge--green'})[r]||'ap__badge--gray'
    const roleLabel = r => ({'admin':'Admin','seller':'Seller','seller_premium':'⭐ Premium','buyer':'Buyer'})[r]||r
    const statusBadgeClass = s => ({'active':'ap__badge--green','banned':'ap__badge--red','pending':'ap__badge--yellow','deleted':'ap__badge--gray'})[s]||'ap__badge--gray'
    const statusLabel = s => ({'active':'Aktif','banned':'Diblokir','pending':'Diproses','deleted':'Dihapus'})[s]||s
    const getActionButtonClass = s => ({'active':'pg__act-btn--ban','banned':'pg__act-btn--unban','pending':'pg__act-btn--approve','deleted':'pg__act-btn--restore'})[s]||'pg__act-btn--unban'
    const getActionButtonTitle = s => ({'active':'Blokir','banned':'Aktifkan','pending':'Setujui','deleted':'Pulihkan'})[s]||'Aktifkan'
    const getActionButtonIcon = s => ({'active':'','banned':'','pending':'','deleted':''})[s]||''
    const fmtDate = iso => new Intl.DateTimeFormat('id-ID',{day:'numeric',month:'short',year:'numeric'}).format(new Date(iso))

    // Dummy data sebagai fallback
    const statCards = computed(()=>[
      {label:'Total Pengguna', val:users.value.length, color:'#6366f1', icon:'<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="2"/></svg>'},
      {label:'Seller', val:users.value.filter(u=>u.role==='seller'||u.role==='seller_premium').length, color:'#e53e3e', icon:'<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="2"/></svg>'},
      {label:'Buyer', val:users.value.filter(u=>u.role==='buyer').length, color:'#10b981', icon:'<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" stroke="currentColor" stroke-width="2"/></svg>'},
      {label:'Diblokir', val:users.value.filter(u=>u.status==='banned').length, color:'#f59e0b', icon:'<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07" stroke="currentColor" stroke-width="2"/></svg>'},
    ])

    const filtered = computed(()=>{
      let l=[...users.value]
      if (filterRole.value) l=l.filter(u=>u.role===filterRole.value)
      if (filterStatus.value) l=l.filter(u=>u.status===filterStatus.value)
      if (q.value.trim()) { const s=q.value.toLowerCase(); l=l.filter(u=>u.name.toLowerCase().includes(s)||u.email.toLowerCase().includes(s)||(u.class||'').toLowerCase().includes(s)) }
      return l
    })
    const totalPages = computed(()=>Math.max(1,Math.ceil(filtered.value.length/perPage)))
    const paginated  = computed(()=>filtered.value.slice((page.value-1)*perPage,page.value*perPage))

    watch([filtered], () => {
      if (page.value > totalPages.value) page.value = totalPages.value
      if (page.value < 1) page.value = 1
    }, { immediate: true })

    const showToast = (msg,err=false)=>{ toast.msg=msg; toast.err=err; toast.show=true; setTimeout(()=>toast.show=false,3000) }

    const openAdd  = () => { 
      formModal.isEdit=false
      formModal.data = { name:'', email:'', class:'', role:'buyer' }
      formModal.open=true
    }
    const openEdit = u  => { 
      formModal.isEdit=true
      formModal.data = { id: u.id, name: u.name, email: u.email, class: u.class, role: u.role }
      formModal.open=true
    }

    const saveUser = async () => {
      if (!formModal.data.name?.trim() || !formModal.data.email?.trim()) {
        showToast('Nama dan email wajib diisi', true)
        return
      }

      saving.value=true
      try {
        if (formModal.isEdit) {
          const { id, name, email, class: kelas, role } = formModal.data
          await api.put(`/admin/users/${id}`, { name, email, class: kelas, role })
          const idx=users.value.findIndex(u=>u.id===id)
          if(idx!==-1) {
            users.value[idx] = { ...users.value[idx], name, email, class: kelas, role }
          }
        } else {
          const payload = {
            name: formModal.data.name,
            email: formModal.data.email,
            class: formModal.data.class,
            role: formModal.data.role,
          }
          const r=await api.post('/admin/users', payload)
          users.value.unshift(r.data)
        }
        showToast(formModal.isEdit ? ' Pengguna diperbarui' : ' Pengguna ditambahkan')
        formModal.open=false
      } catch(e){
        const msg = e.response?.data?.message || 'Gagal menyimpan'
        showToast(msg, true)
      } finally {
        saving.value=false
      }
    }

    const toggleBan = async (u) => {
      if (actionLoading[u.id]) return
      actionLoading[u.id] = true
      try {
        let newStatus
        let actionMessage
        
        if (u.status === 'active') {
          newStatus = 'banned'
          actionMessage = ` ${u.name} diblokir`
        } else if (u.status === 'banned') {
          newStatus = 'active'
          actionMessage = ` ${u.name} diaktifkan kembali`
        } else if (u.status === 'pending') {
          newStatus = 'active'
          actionMessage = ` ${u.name} disetujui`
        } else if (u.status === 'deleted') {
          newStatus = 'active'
          actionMessage = ` ${u.name} dipulihkan`
        } else {
          newStatus = 'active'
          actionMessage = ` ${u.name} diaktifkan`
        }
        
        await api.patch(`/admin/users/${u.id}/status`, { status: newStatus })
        u.status = newStatus
        showToast(actionMessage)
      } catch(e){
        showToast('Gagal mengubah status', true)
      } finally {
        delete actionLoading[u.id]
      }
    }

    const confirmDelete = async (u) => {
      if (!confirm(`Hapus pengguna "${u.name}"? Aksi ini tidak bisa dibatalkan.`)) return
      if (actionLoading[u.id]) return
      actionLoading[u.id] = true
      try {
        await api.delete(`/admin/users/${u.id}`)
        users.value = users.value.filter(x=>x.id!==u.id)
        showToast('️ Pengguna dihapus')
      } catch(e){
        showToast('Gagal menghapus', true)
      } finally {
        delete actionLoading[u.id]
      }
    }

    const exportData = async (format) => {
      if (exporting.value) return

      exporting.value = true
      try {
        const params = new URLSearchParams()
        if (filterRole.value) params.append('role', filterRole.value)
        if (filterStatus.value) params.append('status', filterStatus.value)
        if (q.value.trim()) params.append('search', q.value.trim())
        params.append('format', format)

        const { data } = await api.get(`/admin/users/export?${params.toString()}`, {
          responseType: 'blob'
        })

        const url = window.URL.createObjectURL(new Blob([data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', `users-report.${format}`)
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.URL.revokeObjectURL(url)
      } catch (error) {
        console.error('Export failed:', error)
        showToast('Gagal mengexport data', true)
      } finally {
        exporting.value = false
      }
    }

const fetchUsers = async () => {
  loading.value = true

  try {
    const r = await api.get('/admin/users')
    users.value = r.data.data || r.data || []
  } catch (e) {
    console.error('Failed to fetch users:', e)
    users.value = []
    showToast('Gagal memuat pengguna', true)
  } finally {
    loading.value = false
  }
}

    onMounted(() => {
      fetchUsers()
    })

    onUnmounted(() => {
      if (abortController) abortController.abort()
    })

    return {
      loading,
      saving,
      exporting,
      actionLoading,
      searchQuery: searchInput,
      q,
      filterRole,
      filterStatus,
      page,
      perPage,
      users,
      filtered,
      paginated,
      totalPages,
      statCards,
      toast,
      formModal,
      roleColor,
      roleBadge,
      roleLabel,
      statusBadgeClass,
      statusLabel,
      getActionButtonClass,
      getActionButtonTitle,
      getActionButtonIcon,
      fmtDate,
      roleColor,
      roleBadge,
      roleLabel,
      fmtDate,
      formatImage,
      handleImageError,
      openAdd,
      openEdit,
      saveUser,
      toggleBan,
      confirmDelete,
      exportData,
    }
  }
}
</script>

<style scoped>
/* ... (style sama seperti sebelumnya) ... */
.pg__stats { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px; }
.pg__stat-card { background:#fff; border-radius:14px; padding:16px 18px; display:flex; align-items:center; gap:13px; border:1.5px solid #f3f4f6; }
.pg__stat-icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,.06); color:var(--c); flex-shrink:0; }
.pg__stat-val { font-size:1.5rem; font-weight:900; color:#111827; font-family:'Fraunces',serif; line-height:1; }
.pg__stat-lbl { font-size:.72rem; font-weight:600; color:#9ca3af; margin-top:2px; }
.pg__count { font-size:.78rem; color:#9ca3af; font-weight:600; white-space:nowrap; margin-left:auto; }
.pg__grid { grid-template-columns:2.5fr 1fr 1fr 1.2fr 1fr 100px; gap:12px; }
.pg__user-cell { display:flex; align-items:center; gap:10px; min-width:0; }
.pg__avatar { width:34px; height:34px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; color:#fff; font-size:.82rem; font-weight:800; }
.pg__user-name { font-size:.84rem; font-weight:700; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.pg__user-email { font-size:.72rem; color:#9ca3af; }
.pg__class,.pg__date { font-size:.8rem; color:#6b7280; }
.pg__actions { display:flex; gap:5px; }
.pg__act-btn { width:30px; height:30px; border:none; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:.85rem; transition:all .2s; }
.pg__act-btn:disabled { opacity:0.5; cursor:not-allowed; }
.pg__act-btn--edit   { background:#eff6ff; }  .pg__act-btn--edit:hover:not(:disabled)   { background:#1d4ed8; color:#fff; }
.pg__act-btn--ban    { background:#fff5f5; }  .pg__act-btn--ban:hover:not(:disabled)    { background:#e53e3e; color:#fff; }
.pg__act-btn--unban  { background:#f0fdf4; }  .pg__act-btn--unban:hover:not(:disabled)  { background:#059669; color:#fff; }
.pg__act-btn--approve{ background:#fef3c7; }  .pg__act-btn--approve:hover:not(:disabled){ background:#f59e0b; color:#fff; }
.pg__act-btn--restore{ background:#e0e7ff; }  .pg__act-btn--restore:hover:not(:disabled){ background:#6366f1; color:#fff; }
.pg__act-btn--del    { background:#f9fafb; }  .pg__act-btn--del:hover:not(:disabled)    { background:#f3f4f6; }
.ap__head-actions {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.ap__export-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}
.pg__form-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:20px; }
.pg__field { display:flex; flex-direction:column; gap:5px; }
.pg__field label { font-size:.8rem; font-weight:700; color:#374151; }
.pg__field input,.pg__field select { height:40px; padding:0 12px; border:1.5px solid #e5e7eb; border-radius:9px; font-family:'Plus Jakarta Sans',sans-serif; font-size:.875rem; outline:none; transition:border-color .2s; }
.pg__field input:focus,.pg__field select:focus { border-color:#fca5a5; box-shadow:0 0 0 3px rgba(229,62,62,.08); }
.modal-bg { position:fixed; inset:0; background:rgba(0,0,0,.45); backdrop-filter:blur(4px); z-index:1000; display:flex; align-items:center; justify-content:center; padding:24px; }
.modal-box { background:#fff; border-radius:20px; padding:32px; width:100%; box-shadow:0 20px 60px rgba(0,0,0,.2); animation:mIn .22s ease; }
@keyframes mIn { from{opacity:0;transform:scale(.94) translateY(10px)} to{opacity:1;transform:scale(1) translateY(0)} }
.modal-box__btns { display:flex; gap:10px; justify-content:flex-end; }
.ap__toast { position:fixed; bottom:28px; right:28px; z-index:2000; padding:13px 22px; border-radius:12px; font-size:.875rem; font-weight:600; box-shadow:0 8px 28px rgba(0,0,0,.15); transform:translateY(20px); opacity:0; transition:all .3s; pointer-events:none; background:#111827; color:#fff; }
.ap__toast--show { transform:translateY(0); opacity:1; }
.ap__toast--err { background:#fff5f5; color:#c53030; border:1px solid #fecaca; }
@media (max-width:768px) { .pg__stats{grid-template-columns:1fr 1fr} .pg__form-grid{grid-template-columns:1fr} }
.pg__avatar {
  width:34px;
  height:34px;
  border-radius:50%;
  overflow:hidden; /* penting */
  display:flex;
  align-items:center;
  justify-content:center;
  color:#fff;
  font-size:.82rem;
  font-weight:800;
}

.pg__avatar img {
  width:100%;
  height:100%;
  object-fit:cover;
  display:block;
}
</style>