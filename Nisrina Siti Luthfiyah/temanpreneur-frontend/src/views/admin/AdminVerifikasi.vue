<template>
  <div class="ap">
    <!-- Header -->
    <div class="ap__head">
      <div>
        <h1 class="ap__title">Verifikasi <span>Toko</span></h1>
        <p class="ap__sub">Tinjau dan proses pengajuan toko dari siswa</p>
      </div>
      <div class="ap__head-stats">
        <div class="ap__stat ap__stat--yellow"><strong>{{ pending.length }}</strong><span>Menunggu</span></div>
        <div class="ap__stat ap__stat--green"><strong>{{ approved }}</strong><span>Disetujui</span></div>
        <div class="ap__stat ap__stat--red"><strong>{{ rejected }}</strong><span>Ditolak</span></div>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="ap__card">
      <div class="ap__toolbar">
        <div class="ap__search">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          <input v-model="q" placeholder="Cari nama toko atau siswa..."/>
        </div>
        <div class="vf__tabs">
          <button v-for="t in tabs" :key="t.id" class="vf__tab" :class="{'vf__tab--active': activeTab===t.id}" @click="activeTab=t.id">
            {{ t.icon }} {{ t.label }} <span>{{ t.count }}</span>
          </button>
        </div>
        <select v-model="filterKat" class="ap__select">
          <option value="">Semua Kategori</option>
          <option v-for="k in katList" :key="k.id" :value="k.id">{{ k.icon }} {{ k.nama }}</option>
        </select>
      </div>

      <!-- Loading -->
      <div class="vf__loading" v-if="loading">
        <div v-for="n in 4" :key="n" class="skeleton" style="height:120px;margin:0 22px 12px;"></div>
      </div>

      <!-- Empty -->
      <div class="empty-state" v-else-if="!filtered.length">
        <span></span><p>Tidak ada pengajuan ditemukan</p>
      </div>

      <!-- List -->
      <div v-else class="vf__list">
        <div v-for="item in filtered" :key="item.id" class="vf__card" :class="`vf__card--${item.status}`">
          <div class="vf__stripe"></div>
          <div class="vf__card-body">
            <div class="vf__top">
              <div class="vf__biz-icon">{{ getIcon(item.category) }}</div>
              <div class="vf__biz-info">
                <h3>{{ item.name }}</h3>
                <div class="vf__meta">
                  <span class="vf__date">{{ fmtDate(item.created_at) }}</span>
                </div>
              </div>
              <span class="ap__badge" :class="badgeClass(item.status)">{{ statusLabel[item.status] }}</span>
            </div>

            <p class="vf__desc">{{ item.description }}</p>

            <div class="vf__user-row">
              <div class="vf__avatar" :style="`background:${color(item.user_id)}`">
                <img v-if="item.owner?.photo" :src="item.owner.photo" alt="avatar" />
                <span v-else>{{ item.owner?.name?.[0] || item.user_name?.[0] || '?' }}</span>
              </div>
              <div>
                <p class="vf__user-name">{{ item.owner?.name || item.user_name || 'Pengguna' }}</p>
                <p class="vf__user-meta">{{ item.owner?.class || item.user_class || '—' }} · {{ item.owner?.email || item.user_email || '—' }}</p>
              </div>
              <a v-if="item.phone" :href="`https://wa.me/62${String(item.phone).replace(/^0/,'')}`" target="_blank" class="vf__wa">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.134.558 4.13 1.535 5.865L.057 24l6.305-1.654A11.954 11.954 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.818 9.818 0 01-5.034-1.388l-.361-.214-3.741.981.999-3.648-.235-.374A9.818 9.818 0 0112 2.182c5.42 0 9.818 4.398 9.818 9.818 0 5.42-4.398 9.818-9.818 9.818z"/></svg>
                WA
              </a>
              <div class="vf__actions">
                <button class="vf__btn vf__btn--view" @click="openProductModal(item)" title="Lihat toko">️</button>
                <template v-if="item.status==='pending'">
                  <button class="vf__btn vf__btn--approve" @click="openModal(item,'approve')" :disabled="processing===item.id"> Setujui</button>
                  <button class="vf__btn vf__btn--reject"  @click="openModal(item,'reject')"  :disabled="processing===item.id"> Tolak</button>
                </template>
                <template v-else-if="item.status==='blocked'">
                  <button class="vf__btn vf__btn--approve" @click="quickUnblock(item)" :disabled="processing===item.id"> Aktifkan</button>
                </template>
                <template v-else-if="['active','approved'].includes(item.status)">
                  <button class="vf__btn vf__btn--reject" @click="quickBlock(item)" :disabled="processing===item.id"> Blokir</button>
                </template>
                <template v-else-if="item.status==='rejected'">
                  <button class="vf__btn vf__btn--delete" @click="deleteRejected(item)" :disabled="processing===item.id">️ Hapus</button>
                </template>
              </div>
              <div class="vf__done-label" v-if="item.status!=='pending'">
                {{ ['active','approved'].includes(item.status) ? ' Aktif' : item.status==='blocked' ? ' Diblokir' : ' Ditolak' }} {{ fmtDate(item.processed_at) }}
              </div>
            </div>

            <div class="vf__reason" v-if="item.status==='rejected' && item.rejection_reason">
              ️ Alasan: {{ item.rejection_reason }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Verifikasi-->
    <teleport to="body">
      <div class="modal-bg" v-if="modal.open" @click.self="modal.open=false">
        <div class="modal-box">
          <div class="modal-box__icon">{{ modal.action==='approve'?'':'' }}</div>
          <h3>{{ modal.action==='approve'?'Setujui Pengajuan?':'Tolak Pengajuan?' }}</h3>
          <p>Toko <strong>{{ modal.item?.name }}</strong> akan {{ modal.action==='approve'?'disetujui dan toko diaktifkan.':'ditolak.' }}</p>
          <div class="modal-box__reason" v-if="modal.action==='reject'">
            <label>Alasan Penolakan <span>(opsional)</span></label>
            <textarea v-model="modal.reason" rows="3" placeholder="Tuliskan alasan penolakan..."></textarea>
          </div>
          <div class="modal-box__btns">
            <button class="ap__btn ap__btn--ghost" @click="modal.open=false">Batal</button>
            <button class="ap__btn" :class="modal.action==='approve'?'vf__btn--approve ap__btn--approve':'vf__btn--reject ap__btn--reject-solid'" @click="doAction">
              {{ modal.action==='approve'?'Ya, Setujui':'Ya, Tolak' }}
            </button>
          </div>
        </div>
      </div>
    </teleport>

    <!-- Modal Produk -->
    <teleport to="body">
      <div class="modal-bg" v-if="productModal.open" @click.self="productModal.open=false">
        <div class="modal-box modal-box--products">
          <div class="modal-box__header">
            <h3>Produk dari {{ productModal.business?.name }}</h3>
            <button class="modal-box__close" @click="productModal.open=false" aria-label="Tutup">×</button>
          </div>
          <div v-if="productModal.loading" class="modal-box__loading">Memuat produk...</div>
          <div v-else-if="!productModal.products.length" class="modal-box__empty">
            <span></span>
            <p>Belum ada produk</p>
          </div>
          <div v-else class="product-table">
            <div class="prod-header">
              <div class="prod-col prod-col--name">Nama Produk</div>
              <div class="prod-col prod-col--price">Harga</div>
              <div class="prod-col prod-col--stock">Stok</div>
              <div class="prod-col prod-col--sold">Terjual</div>
            </div>
            <div v-for="p in productModal.products" :key="p.id" class="prod-row">
              <div class="prod-col prod-col--name">{{ p.name }}</div>
              <div class="prod-col prod-col--price">Rp {{ p.price.toLocaleString('id-ID') }}</div>
              <div class="prod-col prod-col--stock">{{ p.stock }}</div>
              <div class="prod-col prod-col--sold">{{ p.total_sold }}</div>
            </div>
          </div>
          <div class="modal-box__btns" v-if="productModal.products.length">
            <button class="ap__btn ap__btn--ghost" @click="productModal.open=false">Tutup</button>
          </div>
        </div>
      </div>
    </teleport>

    <!-- Toast -->
    <div class="ap__toast" :class="{'ap__toast--show':toast.show,'ap__toast--err':toast.err}">{{ toast.msg }}</div>
  </div>
</template>

<script>
import { ref, computed, onMounted, reactive, watch } from 'vue'
import api from '@/api/axios'

export default {
  name: 'AdminVerifikasi',
  setup() {
    const loading = ref(true)
    const processing = ref(null)
    const q = ref('')
    const activeTab = ref('all')
    const filterKat = ref('')
    const items = ref([])
    const approved = ref(0)
    const rejected = ref(0)

    const modal = reactive({ open:false, action:'', item:null, reason:'' })
    const productModal = reactive({ open:false, business:null, products:[], loading:false })
    const toast = reactive({ show:false, msg:'', err:false })

    const katList = [
      {id:'fashion',icon:'',nama:'Fashion'},{id:'kuliner',icon:'',nama:'Kuliner'},
      {id:'kerajinan',icon:'',nama:'Kerajinan'},{id:'digital',icon:'',nama:'Digital'},
      {id:'aksesoris',icon:'',nama:'Aksesoris'},{id:'lainnya',icon:'',nama:'Lainnya'},
    ]
    const katMap = {fashion:'',kuliner:'',kerajinan:'',digital:'',aksesoris:'',lainnya:''}
    const katLabel = {fashion:'Fashion',kuliner:'Kuliner',kerajinan:'Kerajinan',digital:'Digital',aksesoris:'Aksesoris',lainnya:'Lainnya'}
    const statusLabel = {pending:'Menunggu',active:'Aktif',approved:'Disetujui',blocked:'Diblokir',rejected:'Ditolak'}
    const colors_ = ['linear-gradient(135deg,#f43f5e,#e11d48)','linear-gradient(135deg,#6366f1,#4f46e5)','linear-gradient(135deg,#10b981,#059669)','linear-gradient(135deg,#f59e0b,#d97706)','linear-gradient(135deg,#ec4899,#db2777)','linear-gradient(135deg,#0ea5e9,#0284c7)']

    const getIcon  = c => katMap[c]||''
    const getLabel = c => katLabel[c]||'Lainnya'
    const color    = id => colors_[id%colors_.length]
    const fmtDate  = iso => iso ? new Intl.DateTimeFormat('id-ID',{day:'numeric',month:'short',year:'numeric'}).format(new Date(iso)) : '-'
    const badgeClass = s => ({
      'pending':'ap__badge--yellow',
      'active':'ap__badge--green', 
      'approved':'ap__badge--green',
      'blocked':'ap__badge--red',
      'rejected':'ap__badge--red'
    })[s]||'ap__badge--gray'

    const pending = computed(() => items.value.filter(i=>i.status==='pending'))

    const tabs = computed(() => [
      {id:'all',icon:'',label:'Semua',count:items.value.length},
      {id:'pending',icon:'⏳',label:'Menunggu',count:pending.value.length},
      {id:'approved',icon:'',label:'Disetujui',count:approved.value},
      {id:'rejected',icon:'',label:'Ditolak',count:rejected.value},
    ])

    const filtered = computed(() => {
      let list = [...items.value]
      if (activeTab.value !== 'all') list = list.filter(i=>i.status===activeTab.value)
      if (filterKat.value) list = list.filter(i=>i.category===filterKat.value)
      if (q.value.trim()) { const s=q.value.toLowerCase(); list=list.filter(i=>i.name.toLowerCase().includes(s) || (i.owner?.name || i.user_name || '').toLowerCase().includes(s)) }
      return list
    })

    const fetch_ = async () => {
      loading.value = true
      try {
        // Use admin/verifications with status filter
        const statusParam = activeTab.value === 'all' ? '' : `&status=${activeTab.value}`
        const r = await api.get(`/admin/verifications?per_page=100${statusParam}`)
        if (r.data.data && Array.isArray(r.data.data)) {
          items.value = r.data.data
          // Update counts from API response
          if (r.data.counts) {
            approved.value = r.data.counts.approved || 0
            rejected.value = r.data.counts.rejected || 0
          } else {
            approved.value = items.value.filter(i => i.status === 'approved').length
            rejected.value = items.value.filter(i => i.status === 'rejected').length
          }
        } else if (Array.isArray(r.data)) {
          items.value = r.data
          approved.value = items.value.filter(i => i.status === 'approved').length
          rejected.value = items.value.filter(i => i.status === 'rejected').length
        } else {
          console.warn('Unexpected API response format:', r.data)
          items.value = []
        }
      } catch (err) {
        console.error('API fetch error:', err)
        items.value = []
        showToast('Gagal memuat pengajuan usaha', true)
      }
      loading.value = false
    }

    const openModal = (item, action) => { modal.item=item; modal.action=action; modal.reason=''; modal.open=true }

    const openProductModal = async (business) => {
      productModal.business = business
      productModal.products = []
      productModal.loading = true
      productModal.open = true
      await loadProducts(business.id)
    }

    const loadProducts = async (businessId) => {
      try {
        const r = await api.get(`/admin/businesses/${businessId}/products`)
        productModal.products = r.data.data || r.data
      } catch (e) {
        productModal.products = []
        showToast('Gagal memuat produk', true)
      } finally {
        productModal.loading = false
      }
    }

    const showToast = (msg,err=false) => { toast.msg=msg; toast.err=err; toast.show=true; setTimeout(()=>toast.show=false,3200) }

    const quickBlock = async (item) => {
      const reason = prompt('Alasan blokir (opsional):') || ''
      if (confirm(`Blokir toko "${item.name}"?`)) {
        processing.value = item.id
        try {
          await api.post(`/admin/businesses/${item.id}/block`, { reason })
          item.status = 'blocked'
          item.processed_at = new Date().toISOString()
          item.rejection_reason = reason
          showToast(` "${item.name}" diblokir`)
        } catch(e) {
          showToast('Gagal blokir', true)
        } finally {
          processing.value = null
        }
      }
    }

    const deleteRejected = async (item) => {
      if (confirm(`Hapus pengajuan toko "${item.name}" secara permanen? Tindakan ini tidak dapat dibatalkan.`)) {
        processing.value = item.id
        try {
          await api.delete(`/admin/verifications/${item.id}`)
          // Remove from items array
          const idx = items.value.findIndex(i => i.id === item.id)
          if (idx !== -1) {
            items.value.splice(idx, 1)
          }
          rejected.value--
          showToast(`️ Pengajuan "${item.name}" dihapus`)
        } catch(e) {
          showToast('Gagal menghapus pengajuan', true)
        } finally {
          processing.value = null
        }
      }
    }
         const quickUnblock = async (item) => {
  if (confirm(`Aktifkan kembali toko "${item.name}"?`)) {
    processing.value = item.id
    try {
      await api.post(`/admin/businesses/${item.id}/unblock`)
      item.status = 'active'
      item.processed_at = new Date().toISOString()

      showToast(` "${item.name}" diaktifkan`)
    } catch(e) {
      showToast('Gagal unblock', true)
    } finally {
      processing.value = null
    }
  }
}

    const doAction = async () => {
      processing.value = modal.item.id
      try {
        const ep = modal.action==='approve' ? `/admin/businesses/${modal.item.id}/approve` : `/admin/businesses/${modal.item.id}/reject`
        await api.post(ep, { reason: modal.reason||undefined })
        const idx = items.value.findIndex(i=>i.id===modal.item.id)
        if (idx!==-1) {
          items.value[idx].status = modal.action==='approve'?'approved':'rejected'
          items.value[idx].processed_at = new Date().toISOString()
          items.value[idx].rejection_reason = modal.reason||''
        }
        approved.value = items.value.filter(i=>i.status==='approved').length
        rejected.value = items.value.filter(i=>i.status==='rejected').length
        showToast(modal.action==='approve'?` "${modal.item.name}" disetujui!`:` "${modal.item.name}" ditolak!`, modal.action!=='approve')
        modal.open = false
      } catch(e) { showToast(e.response?.data?.message||'Gagal memproses.',true) }
      finally { processing.value = null }
    }

    onMounted(fetch_)
    watch(activeTab, fetch_)
    return { loading,processing,q,activeTab,filterKat,items,pending,approved,rejected,tabs,filtered,modal,productModal,toast,katList,statusLabel,getIcon,getLabel,color,fmtDate,badgeClass,openModal,openProductModal,doAction,quickBlock,quickUnblock,deleteRejected }
  }
}
</script>

<style scoped>
.ap__head-stats { display:flex; gap:10px; }
.ap__stat { text-align:center; padding:10px 18px; border-radius:12px; border:1.5px solid; }
.ap__stat strong { display:block; font-size:1.4rem; font-weight:900; font-family:'Fraunces',serif; }
.ap__stat span { font-size:.68rem; font-weight:600; }
.ap__stat--yellow { border-color:#fde68a; background:#fffbeb; } .ap__stat--yellow strong,.ap__stat--yellow span { color:#d97706; }
.ap__stat--green  { border-color:#bbf7d0; background:#f0fdf4; } .ap__stat--green strong,.ap__stat--green span { color:#059669; }
.ap__stat--red    { border-color:#fecaca; background:#fff5f5; } .ap__stat--red strong,.ap__stat--red span { color:#c53030; }

.vf__tabs { display:flex; gap:5px; flex-wrap:wrap; }
.vf__tab { display:flex; align-items:center; gap:5px; padding:6px 13px; border:1.5px solid #e5e7eb; border-radius:8px; background:#fff; font-family:'Plus Jakarta Sans',sans-serif; font-size:.78rem; font-weight:600; color:#6b7280; cursor:pointer; transition:all .2s; }
.vf__tab span { background:#e5e7eb; padding:1px 6px; border-radius:100px; font-size:.65rem; color:#6b7280; }
.vf__tab:hover { border-color:#fca5a5; color:#e53e3e; background:#fff5f5; }
.vf__tab--active { border-color:#e53e3e; color:#e53e3e; background:#fff5f5; }
.vf__tab--active span { background:#fecaca; color:#c53030; }

.vf__loading { padding:0 22px 20px; display:flex; flex-direction:column; gap:12px; }
.vf__list { display:flex; flex-direction:column; }
.vf__card { display:flex; border-bottom:1px solid #f9fafb; transition:background .15s; }
.vf__card:last-child { border-bottom:none; }
.vf__card:hover { background:#fafafa; }
.vf__stripe { width:4px; flex-shrink:0; }
.vf__card--pending .vf__stripe { background:linear-gradient(180deg,#f59e0b,#d97706); }
.vf__card--approved .vf__stripe { background:linear-gradient(180deg,#10b981,#059669); }
.vf__card--rejected .vf__stripe { background:linear-gradient(180deg,#ef4444,#c53030); }
.vf__card-body { flex:1; padding:18px 22px; }
.vf__top { display:flex; align-items:flex-start; gap:11px; margin-bottom:10px; flex-wrap:wrap; }
.vf__biz-icon { width:42px; height:42px; border-radius:10px; background:#f3f4f6; border:1.5px solid #e5e7eb; display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0; }
.vf__biz-info { flex:1; }
.vf__biz-info h3 { font-size:.98rem; font-weight:800; color:#111827; }
.vf__meta { display:flex; align-items:center; gap:6px; margin-top:3px; }
.vf__kat { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#e53e3e; background:#fff5f5; padding:2px 7px; border-radius:4px; }
.vf__date { font-size:.72rem; color:#9ca3af; }
.vf__desc { font-size:.84rem; color:#6b7280; line-height:1.65; margin-bottom:12px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.vf__user-row { display:flex; align-items:center; gap:9px; flex-wrap:wrap; }
.vf__avatar {
  width:32px;
  height:32px;
  border-radius:50%;
  flex-shrink:0;
  display:flex;
  align-items:center;
  justify-content:center;
  color:#fff;
  font-size:.8rem;
  font-weight:800;

  overflow: hidden; /* penting */
}
.vf__avatar img {
  width:100%;
  height:100%;
  object-fit: cover; /* biar proporsional dan tidak gepeng */
  display:block;
}
.vf__user-name { font-size:.82rem; font-weight:700; color:#111827; }
.vf__user-meta { font-size:.7rem; color:#9ca3af; }
.vf__wa { display:flex; align-items:center; gap:5px; padding:4px 10px; background:#dcfce7; color:#15803d; border-radius:100px; font-size:.7rem; font-weight:700; text-decoration:none; border:1px solid #bbf7d0; transition:all .2s; }
.vf__wa:hover { background:#16a34a; color:#fff; }
.vf__actions { display:flex; gap:6px; margin-left:auto; }
.vf__btn { display:inline-flex; align-items:center; gap:5px; padding:7px 14px; border-radius:8px; border:none; font-family:'Plus Jakarta Sans',sans-serif; font-size:.78rem; font-weight:700; cursor:pointer; transition:all .2s; }
.vf__btn:disabled { opacity:.6; cursor:not-allowed; }
.vf__btn--view { background:#e0e7ff; color:#4f46e5; border:1.5px solid #c7d2fe; }
.vf__btn--view:hover:not(:disabled) { background:#4f46e5; color:#fff; }
.vf__btn--approve { background:#dcfce7; color:#15803d; border:1.5px solid #bbf7d0; }
.vf__btn--approve:hover:not(:disabled) { background:#16a34a; color:#fff; }
.vf__btn--reject  { background:#fff5f5; color:#c53030; border:1.5px solid #fecaca; }
.vf__btn--reject:hover:not(:disabled)  { background:#e53e3e; color:#fff; }
.vf__btn--delete  { background:#fef2f2; color:#dc2626; border:1.5px solid #fecaca; }
.vf__btn--delete:hover:not(:disabled)  { background:#dc2626; color:#fff; }
.vf__done-label { margin-left:auto; font-size:.75rem; color:#9ca3af; }
.vf__reason { margin-top:10px; padding:8px 12px; background:#fff5f5; border:1px solid #fecaca; border-radius:8px; font-size:.78rem; color:#c53030; }

/* modal */
.modal-bg { position:fixed; inset:0; background:rgba(0,0,0,.45); backdrop-filter:blur(4px); z-index:1000; display:flex; align-items:center; justify-content:center; padding:24px; }
.modal-box { background:#fff; border-radius:20px; padding:36px 32px; max-width:420px; width:100%; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,.2); animation:mIn .22s ease; }
.modal-box--products { max-width:600px; text-align:left; }
.modal-box__header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
.modal-box__header h3 { margin:0; font-size:1.15rem; font-weight:800; }
.modal-box__close { background:none; border:none; font-size:1.5rem; cursor:pointer; color:#9ca3af; transition:color .2s; }
.modal-box__close:hover { color:#374151; }
.modal-box__loading, .modal-box__empty { padding:40px 20px; text-align:center; color:#9ca3af; }
.modal-box__empty span { font-size:2.5rem; display:block; margin-bottom:10px; }
@keyframes mIn { from{opacity:0;transform:scale(.94) translateY(10px)} to{opacity:1;transform:scale(1) translateY(0)} }
.modal-box__icon { font-size:3rem; margin-bottom:12px; }
.modal-box h3 { font-family:'Fraunces',serif; font-size:1.35rem; font-weight:900; color:#111827; margin-bottom:10px; }
.modal-box p { font-size:.875rem; color:#6b7280; line-height:1.65; margin-bottom:18px; }
.modal-box__reason label { display:block; font-size:.82rem; font-weight:700; color:#374151; margin-bottom:6px; text-align:left; }
.modal-box__reason label span { font-weight:400; color:#9ca3af; }
.modal-box__reason textarea { width:100%; padding:10px 12px; border:1.5px solid #e5e7eb; border-radius:10px; font-family:'Plus Jakarta Sans',sans-serif; font-size:.85rem; outline:none; resize:vertical; margin-bottom:18px; }
.modal-box__btns { display:flex; gap:10px; justify-content:center; }
.product-table { background:#f9fafb; border-radius:12px; overflow:hidden; margin-bottom:20px; }
.prod-header { display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:12px; padding:12px 16px; background:#f3f4f6; font-weight:700; font-size:.8rem; color:#6b7280; border-bottom:1px solid #e5e7eb; }
.prod-row { display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:12px; padding:12px 16px; border-bottom:1px solid #e5e7eb; font-size:.85rem; align-items:center; }
.prod-row:last-child { border-bottom:none; }
.prod-col--name { color:#111827; font-weight:600; }
.prod-col--price { color:#e53e3e; font-weight:700; }
.prod-col--stock { text-align:center; color:#6b7280; }
.prod-col--sold { text-align:center; color:#6b7280; }
.ap__btn--approve { background:linear-gradient(135deg,#22c55e,#16a34a); color:#fff; }
.ap__btn--reject-solid { background:linear-gradient(135deg,#f56565,#c53030); color:#fff; }

/* toast */
.ap__toast { position:fixed; bottom:28px; right:28px; z-index:2000; padding:13px 22px; border-radius:12px; font-size:.875rem; font-weight:600; box-shadow:0 8px 28px rgba(0,0,0,.15); transform:translateY(20px); opacity:0; transition:all .3s; pointer-events:none; background:#111827; color:#fff; }
.ap__toast--show { transform:translateY(0); opacity:1; }
.ap__toast--err { background:#fff5f5; color:#c53030; border:1px solid #fecaca; }
</style>