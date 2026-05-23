<template>
  <div class="ap">
    <div class="ap__head">
      <div>
        <h1 class="ap__title">Penanganan <span>Laporan</span></h1>
        <p class="ap__sub">Tinjau laporan pelanggaran dan masalah dari pengguna</p>
      </div>
      <div class="lp__head-stats">
        <div class="lp__hstat lp__hstat--red"><strong>{{ openCount }}</strong><span>Belum Ditangani</span></div>
        <div class="lp__hstat lp__hstat--yellow"><strong>{{ inProgressCount }}</strong><span>Sedang Diproses</span></div>
        <div class="lp__hstat lp__hstat--green"><strong>{{ closedCount }}</strong><span>Selesai</span></div>
      </div>
    </div>

    <!-- Priority banner -->
    <div class="lp__urgent-banner" v-if="urgent.length">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="2.5"/><line x1="12" y1="9" x2="12" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="12" y1="17" x2="12.01" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
      <span>{{ urgent.length }} laporan <strong>prioritas tinggi</strong> belum ditangani</span>
      <button @click="activeTab='open';filterPriority='high'" class="lp__urgent-link">Lihat →</button>
    </div>

    <div class="ap__card">
      <!-- Toolbar -->
      <div class="ap__toolbar">
        <div class="ap__search">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          <input v-model="q" placeholder="Cari laporan..."/>
        </div>
        <div class="lp__tabs">
          <button v-for="t in tabs" :key="t.id" class="lp__tab" :class="{'lp__tab--active':activeTab===t.id}" @click="activeTab=t.id;page=1">
            {{ t.icon }} {{ t.label }} <span>{{ t.count }}</span>
          </button>
        </div>
        <select v-model="filterType" class="ap__select">
          <option value="">Semua Jenis</option>
          <option value="penipuan">Penipuan</option>
          <option value="produk_palsu">Produk Palsu</option>
          <option value="ujaran_kebencian">Ujaran Kebencian</option>
          <option value="spam">Spam</option>
          <option value="lainnya">Lainnya</option>
        </select>
        <select v-model="filterPriority" class="ap__select">
          <option value="">Semua Prioritas</option>
          <option value="high"> Tinggi</option>
          <option value="medium">🟡 Sedang</option>
          <option value="low">🟢 Rendah</option>
        </select>
      </div>

      <!-- Loading -->
      <div style="padding:0 22px 20px;display:flex;flex-direction:column;gap:10px;" v-if="loading">
        <div v-for="n in 5" :key="n" class="skeleton" style="height:100px;"></div>
      </div>

      <div v-else>
        <div class="lp__list">
          <div v-for="r in paginated" :key="r.id" class="lp__item" :class="`lp__item--${r.status}`">
            <div class="lp__priority-dot" :class="`lp__dot--${r.priority}`" :title="`Prioritas ${r.priority}`"></div>

            <div class="lp__item-body">
              <div class="lp__item-top">
                <span class="lp__type-badge">{{ typeLabel[r.type] }}</span>
                <span class="ap__badge" :class="statusBadge(r.status)">{{ statusLabel[r.status] }}</span>
                <span class="lp__id">#{{ String(r.id).padStart(4,'0') }}</span>
                <span class="lp__date">{{ fmtDate(r.created_at) }}</span>
              </div>
              <p class="lp__item-desc">{{ r.description }}</p>
              <div class="lp__item-meta">
                <div class="lp__reporter">
                  <div class="lp__avatar lp__avatar--reporter" :style="`background:${color(r.reporter_id)}`">{{ r.reporter_name?.[0] }}</div>
                  <span>Dilaporkan oleh <strong>{{ r.reporter_name }}</strong></span>
                </div>
                <span class="lp__arrow">→</span>
                <div class="lp__reporter">
                  <div class="lp__avatar lp__avatar--reported" :style="`background:${color(r.reported_id)}`">{{ r.reported_name?.[0] }}</div>
                  <span>Terlapor: <strong>{{ r.reported_name }}</strong></span>
                </div>
              </div>
            </div>

            <div class="lp__item-actions">
              <button v-if="r.status==='open'" class="lp__act-btn lp__act-btn--process" @click="updateStatus(r,'in_progress')">
                Proses
              </button>
              <button class="lp__act-btn lp__act-btn--detail" @click="openDetail(r)">Detail</button>
            </div>
          </div>
        </div>
        <div class="empty-state" v-if="!filtered.length"><span>️</span><p>Tidak ada laporan ditemukan</p></div>
      </div>

      <div class="ap__pagination" v-if="totalPages>1">
        <span class="ap__pagination-info">{{ (page-1)*perPage+1 }}–{{ Math.min(page*perPage,filtered.length) }} dari {{ filtered.length }}</span>
        <div class="ap__pagination-btns">
          <button class="ap__page-btn" :disabled="page===1" @click="page--">‹</button>
          <button v-for="pp in Math.min(totalPages,5)" :key="pp" class="ap__page-btn" :class="{'ap__page-btn--active':page===pp}" @click="page=pp">{{ pp }}</button>
          <button class="ap__page-btn" :disabled="page===totalPages" @click="page++">›</button>
        </div>
      </div>
    </div>

    <!-- Detail modal with handling form -->
    <teleport to="body">
      <div class="modal-bg" v-if="detail.open" @click.self="detail.open=false">
        <div class="modal-box lp__detail-modal" style="max-width: 700px; max-height: 90vh; overflow-y: auto;">
          <button class="lp__close-btn" @click="detail.open=false" aria-label="Tutup">×</button>
          
          <div class="lp__detail-header">
            <span class="lp__type-badge">{{ typeLabel[detail.r?.type] }}</span>
            <span class="ap__badge" :class="statusBadge(detail.r?.status)">{{ statusLabel[detail.r?.status] }}</span>
            <span class="lp__priority-pill" :class="`lp__pill--${detail.r?.priority}`">{{ priorityLabel[detail.r?.priority] }}</span>
          </div>
          
          <h3 class="lp__detail-title">Laporan #{{ String(detail.r?.id||0).padStart(4,'0') }}</h3>
          <p class="lp__detail-desc">{{ detail.r?.description }}</p>
          
          <div class="lp__detail-parties">
            <div class="lp__party">
              <p class="lp__party-label">Pelapor</p>
              <div class="lp__party-user">
                <div class="lp__avatar" :style="`background:${color(detail.r?.reporter_id)}`">{{ detail.r?.reporter_name?.[0] }}</div>
                <div>
                  <p>{{ detail.r?.reporter_name }}</p>
                  <span>{{ detail.r?.reporter_class }}</span>
                  <p style="margin-top: 6px; font-size: 0.85rem; color: #0ea5e9;">
                     {{ detail.r?.reporter_phone || 'Nomor tidak tersedia' }}
                  </p>
                </div>
              </div>
            </div>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="#9ca3af" stroke-width="2" stroke-linecap="round"/></svg>
            <div class="lp__party">
              <p class="lp__party-label">Terlapor</p>
              <div class="lp__party-user">
                <div class="lp__avatar" :style="`background:${color(detail.r?.reported_id)}`">{{ detail.r?.reported_name?.[0] }}</div>
                <div>
                  <p>{{ detail.r?.reported_name }}</p>
                  <span>{{ detail.r?.reported_class }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Admin can contact via chat/WhatsApp using the phone number shown below -->
          <div class="lp__handling-section">
            <h4 style="margin-top: 20px; margin-bottom: 12px; color: #374151; font-size: 0.95rem; font-weight: 700;">️ Kontak & Tindak Lanjut</h4>
            <p style="margin-bottom: 12px; color: #4b5563; line-height:1.7;">Gunakan nomor berikut untuk menghubungi pelapor atau terlapor melalui chat. Tidak diperlukan tindakan administratif khusus di halaman ini.</p>
            <div class="lp__contact-grid" style="display:grid;gap:12px;">
              <div style="padding:12px; border:1px solid #e5e7eb; border-radius:12px; background:#f8fafc;">
                <p class="lp__party-label">Pelapor</p>
                <p style="font-size:.95rem; font-weight:700; margin:6px 0 2px;">{{ detail.r?.reporter_name }}</p>
                <p style="font-size:.85rem; color:#0ea5e9;"> {{ detail.r?.reporter_phone || 'Nomor tidak tersedia' }}</p>
              </div>
              <div style="padding:12px; border:1px solid #e5e7eb; border-radius:12px; background:#f8fafc;">
                <p class="lp__party-label">Terlapor</p>
                <p style="font-size:.95rem; font-weight:700; margin:6px 0 2px;">{{ detail.r?.reported_name }}</p>
                <p style="font-size:.85rem; color:#0ea5e9;"> {{ detail.r?.reported_phone || 'Nomor tidak tersedia' }}</p>
              </div>
            </div>
          </div>

          <!-- Previous responses (if any) -->
          <div v-if="detail.r?.responses && detail.r.responses.length" style="margin-top: 20px; padding-top: 20px; border-top: 1.5px solid #e5e7eb;">
            <h4 style="margin-bottom: 12px; color: #374151; font-size: 0.95rem; font-weight: 700;"> Riwayat Penanganan</h4>
            <div v-for="resp in detail.r.responses" :key="resp.id" style="margin-bottom: 12px; padding: 12px; background: #f9fafb; border-radius: 8px; border-left: 3px solid #fca5a5;">
              <div style="font-size: 0.85rem; color: #6b7280; margin-bottom: 6px;">
                <strong>{{ resp.admin?.name }}</strong> • {{ new Date(resp.created_at).toLocaleString('id-ID') }}
              </div>
              <p style="font-size: 0.9rem; color: #374151; margin-bottom: 6px;">{{ resp.response_message }}</p>
              <div style="font-size: 0.8rem; color: #9ca3af;">
                <span style="display: inline-block; background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 4px; margin-right: 8px;">{{ resp.action_type }}</span>
                <span style="display: inline-block; background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 4px;">{{ resp.status }}</span>
              </div>
            </div>
          </div>

          <div class="lp__detail-btns" style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end; flex-wrap: wrap;">
            <button 
              v-if="detail.r?.reporter_phone && detail.r?.status !== 'closed'" 
              class="ap__btn" 
              @click="contactViaWhatsApp('reporter')"
              style="background: linear-gradient(135deg, #25d366, #20ba5a); color: #fff; flex: 1; min-width: 200px;"
            >
               Hubungi Pengadu via WhatsApp
            </button>
            <button 
              v-if="detail.r?.reported_phone && detail.r?.status !== 'closed'" 
              class="ap__btn" 
              @click="contactViaWhatsApp('reported')"
              style="background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff; flex: 1; min-width: 200px;"
            >
               Hubungi Terlapor via WhatsApp
            </button>
            <button class="ap__btn ap__btn--ghost" @click="detail.open=false">Tutup</button>
            <button 
              v-if="detail.r?.status === 'open' || detail.r?.status === 'in_progress'" 
              class="ap__btn ap__btn--primary" 
              @click="completeHandling()"
              style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff;"
            >
               Tandai Selesai
            </button>
          </div>
        </div>
      </div>
    </teleport>

    <div class="ap__toast" :class="{'ap__toast--show':toast.show}">{{ toast.msg }}</div>
  </div>
</template>

<script>
import { ref, computed, onMounted, reactive } from 'vue'
import api from '@/api/axios'

export default {
  name: 'AdminLaporan',
  setup() {
    const loading = ref(true)
    const q = ref('')
    const activeTab = ref('all')
    const filterType = ref('')
    const filterPriority = ref('')
    const page = ref(1)
    const perPage = 8
    const reports = ref([])
    const detail = reactive({ 
      open: false, 
      r: null, 
      note: '',
      handling: {
        responseMessage: '',
        actionType: '',
        resolutionType: '',
        refundAmount: 0,
        additionalNotes: '',
        submitting: false
      }
    })
    const toast = reactive({ show:false, msg:'' })

    const typeLabel = { penipuan:' Penipuan', produk_palsu:' Produk Palsu', ujaran_kebencian:' Ujaran Kebencian', spam:' Spam', lainnya:' Lainnya' }
    const statusLabel = { open:'Belum Ditangani', in_progress:'Sedang Diproses', closed:'Selesai' }
    const priorityLabel = { high:' Tinggi', medium:'🟡 Sedang', low:'🟢 Rendah' }
    const statusBadge = s => ({'open':'ap__badge--red','in_progress':'ap__badge--yellow','closed':'ap__badge--green'})[s]||'ap__badge--gray'
    const colors_ = ['linear-gradient(135deg,#f43f5e,#e11d48)','linear-gradient(135deg,#6366f1,#4f46e5)','linear-gradient(135deg,#10b981,#059669)','linear-gradient(135deg,#f59e0b,#d97706)','linear-gradient(135deg,#0ea5e9,#0284c7)','linear-gradient(135deg,#ec4899,#db2777)']
    const color = id => colors_[id%colors_.length]
    const fmtDate = iso => new Intl.DateTimeFormat('id-ID',{day:'numeric',month:'short',year:'numeric'}).format(new Date(iso))

    const openCount      = computed(()=>reports.value.filter(r=>r.status==='open').length)
    const inProgressCount= computed(()=>reports.value.filter(r=>r.status==='in_progress').length)
    const closedCount    = computed(()=>reports.value.filter(r=>r.status==='closed').length)
    const urgent         = computed(()=>reports.value.filter(r=>r.priority==='high'&&r.status==='open'))

    const tabs = computed(()=>[
      {id:'all',icon:'',label:'Semua',count:reports.value.length},
      {id:'open',icon:'',label:'Belum',count:openCount.value},
      {id:'in_progress',icon:'🟡',label:'Diproses',count:inProgressCount.value},
      {id:'closed',icon:'',label:'Selesai',count:closedCount.value},
    ])

    const filtered = computed(()=>{
      let l=[...reports.value]
      if (activeTab.value!=='all') l=l.filter(r=>r.status===activeTab.value)
      if (filterType.value)     l=l.filter(r=>r.type===filterType.value)
      if (filterPriority.value) l=l.filter(r=>r.priority===filterPriority.value)
      if (q.value.trim()) { const s=q.value.toLowerCase(); l=l.filter(r=>r.description.toLowerCase().includes(s)||r.reporter_name.toLowerCase().includes(s)||r.reported_name.toLowerCase().includes(s)) }
      return l
    })
    const totalPages = computed(()=>Math.max(1,Math.ceil(filtered.value.length/perPage)))
    const paginated  = computed(()=>filtered.value.slice((page.value-1)*perPage,page.value*perPage))

    const showToast = msg => { toast.msg=msg; toast.show=true; setTimeout(()=>toast.show=false,3000) }
    const openDetail = r => { detail.r=r; detail.note=''; detail.handling = { responseMessage: '', actionType: '', resolutionType: '', refundAmount: 0, additionalNotes: '', submitting: false }; detail.open=true }

    const submitHandling = async () => {
      if (!detail.handling.responseMessage.trim()) {
        showToast(' Respons admin tidak boleh kosong')
        return
      }
      if (!detail.handling.actionType) {
        showToast(' Pilih tipe tindakan')
        return
      }
      if ((detail.handling.actionType === 'refund' || detail.handling.resolutionType === 'refund') && !detail.handling.refundAmount) {
        showToast(' Masukkan jumlah refund')
        return
      }

      detail.handling.submitting = true
      try {
        await api.post(`/admin/reports/${detail.r.id}/respond`, {
          response_message: detail.handling.responseMessage,
          action_type: detail.handling.actionType,
          resolution_type: detail.handling.resolutionType,
          refund_amount: detail.handling.refundAmount || null,
        })
        
        // Update local state
        const idx = reports.value.findIndex(x => x.id === detail.r.id)
        if (idx !== -1) {
          reports.value[idx].status = 'in_progress'
          reports.value[idx].admin_response = detail.handling.responseMessage
          reports.value[idx].admin_response_at = new Date().toISOString()
        }
        
        showToast(' Penanganan laporan berhasil dikirim')
        detail.open = false
      } catch (error) {
        console.error('Failed to submit handling:', error)
        showToast(' Gagal mengirim penanganan: ' + (error.response?.data?.message || error.message))
      } finally {
        detail.handling.submitting = false
      }
    }

    const completeHandling = async () => {
      try {
        // Get the latest response for this report
        const response = detail.r?.responses?.[0]
        if (!response) {
          showToast(' Tidak ada respons untuk diselesaikan')
          return
        }

        await api.patch(`/admin/reports/responses/${response.id}/complete`)
        
        // Update local state
        const idx = reports.value.findIndex(x => x.id === detail.r.id)
        if (idx !== -1) {
          reports.value[idx].status = 'closed'
        }
        
        showToast(' Penanganan laporan selesai')
        detail.open = false
      } catch (error) {
        console.error('Failed to complete handling:', error)
        showToast(' Gagal menyelesaikan penanganan: ' + (error.response?.data?.message || error.message))
      }
    }

    const updateStatus = async (r, status) => {
      try {
        await api.patch(`/admin/reports/${r.id}`, { status })
        // Update local state
        const idx = reports.value.findIndex(x => x.id === r.id)
        if (idx !== -1) {
          reports.value[idx].status = status
        }
        showToast(status === 'in_progress' ? ` Laporan #${r.id} sedang diproses` : ` Laporan #${r.id} selesai`)
      } catch (error) {
        console.error('Failed to update report status:', error)
        showToast(' Gagal memperbarui status laporan', true)
      }
    }

    const approveReport = async (r) => {
      try {
        // For reports, we might need to take action on the reported content
        // For now, just mark as closed with approval
        await api.patch(`/admin/reports/${r.id}`, {
          status: 'closed',
          action_taken: 'approved',
          admin_note: 'Laporan telah ditinjau dan disetujui'
        })
        const idx = reports.value.findIndex(x => x.id === r.id)
        if (idx !== -1) {
          reports.value[idx].status = 'closed'
        }
        showToast(` Laporan #${r.id} disetujui dan ditutup`)
      } catch (error) {
        console.error('Failed to approve report:', error)
        showToast(' Gagal menyetujui laporan', true)
      }
    }

    const rejectReport = async (r) => {
      try {
        await api.patch(`/admin/reports/${r.id}`, {
          status: 'closed',
          action_taken: 'rejected',
          admin_note: 'Laporan ditolak setelah peninjauan'
        })
        const idx = reports.value.findIndex(x => x.id === r.id)
        if (idx !== -1) {
          reports.value[idx].status = 'closed'
        }
        showToast(` Laporan #${r.id} ditolak`)
      } catch (error) {
        console.error('Failed to reject report:', error)
        showToast(' Gagal menolak laporan', true)
      }
    }

    const takeAction = async (r, action) => {
      try {
        // Depending on report type, take different actions
        if (action === 'ban_user') {
          await api.patch(`/admin/users/${r.reported_id}/status`, { status: 'banned' })
        } else if (action === 'remove_product') {
          await api.delete(`/admin/products/${r.reported_product_id}`)
        } else if (action === 'warn_user') {
          // Send warning notification
          await api.post('/admin/notifications', {
            user_id: r.reported_id,
            title: 'Peringatan dari Admin',
            message: 'Akun Anda mendapat peringatan karena melanggar aturan platform.',
            type: 'warning'
          })
        }

        await api.patch(`/admin/reports/${r.id}`, {
          status: 'closed',
          action_taken: action,
          admin_note: `Tindakan ${action} telah diambil`
        })

        const idx = reports.value.findIndex(x => x.id === r.id)
        if (idx !== -1) {
          reports.value[idx].status = 'closed'
        }
        showToast(` Tindakan ${action} berhasil diambil`)
      } catch (error) {
        console.error('Failed to take action:', error)
        showToast(' Gagal mengambil tindakan', true)
      }
    }

    const contactViaWhatsApp = (type) => {
      const phone = type === 'reporter' ? detail.r?.reporter_phone : detail.r?.reported_phone
      const name = type === 'reporter' ? detail.r?.reporter_name : detail.r?.reported_name
      
      if (!phone) {
        showToast(' Nomor WhatsApp tidak tersedia')
        return
      }

      // Format phone number for WhatsApp
      const phoneNumber = phone.replace(/\D/g, '')
      const formattedPhone = phoneNumber.startsWith('62') ? phoneNumber : '62' + phoneNumber.replace(/^0+/, '')

      const message = type === 'reporter' 
        ? `Halo ${name}, ini admin TemanPreneur. Kami telah menerima laporan Anda (Laporan #${String(detail.r?.id).padStart(4,'0')}). Mari kita diskusikan lebih lanjut di sini.`
        : `Halo ${name}, ini admin TemanPreneur. Kami telah menerima laporan mengenai akun Anda (Laporan #${String(detail.r?.id).padStart(4,'0')}). Mohon hubungi kami untuk diskusi lebih lanjut.`

      const whatsappUrl = `https://wa.me/${formattedPhone}?text=${encodeURIComponent(message)}`
      window.open(whatsappUrl, '_blank')
    }

    onMounted(async () => {
      try {
        let reportsData = []
        try {
          const res = await api.get('/admin/reports')
          reportsData = res.data.data || res.data || []
        } catch (apiError) {
          console.error('Failed to load admin reports:', apiError)
          reportsData = []
        }
        
        reports.value = reportsData
      } catch (error) {
        console.error('Failed to load reports:', error)
        reports.value = []
      } finally {
        loading.value = false
      }
    })

    return { loading,q,activeTab,filterType,filterPriority,page,perPage,reports,filtered,paginated,totalPages,detail,toast,tabs,openCount,inProgressCount,closedCount,urgent,typeLabel,statusLabel,priorityLabel,statusBadge,color,fmtDate,openDetail,updateStatus,approveReport,rejectReport,takeAction,submitHandling,completeHandling,contactViaWhatsApp }
  }
}
</script>

<style scoped>
.lp__head-stats { display:flex; gap:10px; }
.lp__hstat { text-align:center; padding:10px 18px; border-radius:12px; border:1.5px solid; }
.lp__hstat strong { display:block; font-size:1.4rem; font-weight:900; font-family:'Fraunces',serif; }
.lp__hstat span { font-size:.68rem; font-weight:600; }
.lp__hstat--red    { border-color:#fecaca; background:#fff5f5; } .lp__hstat--red strong,.lp__hstat--red span { color:#c53030; }
.lp__hstat--yellow { border-color:#fde68a; background:#fffbeb; } .lp__hstat--yellow strong,.lp__hstat--yellow span { color:#d97706; }
.lp__hstat--green  { border-color:#bbf7d0; background:#f0fdf4; } .lp__hstat--green strong,.lp__hstat--green span { color:#059669; }
.lp__urgent-banner { display:flex; align-items:center; gap:10px; padding:12px 18px; background:#fff5f5; border:1.5px solid #fecaca; border-radius:12px; margin-bottom:16px; font-size:.84rem; color:#c53030; }
.lp__urgent-banner svg { flex-shrink:0; color:#e53e3e; }
.lp__urgent-link { margin-left:auto; font-weight:700; font-size:.8rem; text-decoration:underline; background:none; border:none; color:#c53030; cursor:pointer; }
.lp__tabs { display:flex; gap:5px; flex-wrap:wrap; }
.lp__tab { display:flex; align-items:center; gap:5px; padding:6px 12px; border:1.5px solid #e5e7eb; border-radius:8px; background:#fff; font-family:'Plus Jakarta Sans',sans-serif; font-size:.78rem; font-weight:600; color:#6b7280; cursor:pointer; transition:all .2s; }
.lp__tab span { background:#e5e7eb; padding:1px 5px; border-radius:100px; font-size:.65rem; }
.lp__tab:hover { border-color:#fca5a5; color:#e53e3e; background:#fff5f5; }
.lp__tab--active { border-color:#e53e3e; color:#e53e3e; background:#fff5f5; }
.lp__list { display:flex; flex-direction:column; }
.lp__item { display:flex; align-items:flex-start; gap:14px; padding:18px 22px; border-bottom:1px solid #f9fafb; transition:background .15s; }
.lp__item:hover { background:#fafafa; }
.lp__item--closed { opacity:.7; }
.lp__priority-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; margin-top:6px; }
.lp__dot--high   { background:#ef4444; box-shadow:0 0 0 3px rgba(239,68,68,.2); }
.lp__dot--medium { background:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.2); }
.lp__dot--low    { background:#10b981; box-shadow:0 0 0 3px rgba(16,185,129,.2); }
.lp__item-body { flex:1; min-width:0; }
.lp__item-top { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:8px; }
.lp__type-badge { padding:3px 10px; border-radius:100px; background:#f3f4f6; font-size:.72rem; font-weight:700; color:#374151; }
.lp__id   { font-size:.72rem; color:#9ca3af; font-weight:600; }
.lp__date { font-size:.72rem; color:#9ca3af; margin-left:auto; }
.lp__item-desc { font-size:.84rem; color:#374151; line-height:1.6; margin-bottom:10px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.lp__item-meta { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.lp__reporter { display:flex; align-items:center; gap:7px; font-size:.78rem; color:#6b7280; }
.lp__reporter strong { color:#111827; }
.lp__avatar { width:24px; height:24px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; color:#fff; font-size:.6rem; font-weight:800; }
.lp__arrow { color:#d1d5db; font-size:1rem; }
.lp__item-actions { display:flex; flex-direction:column; gap:6px; flex-shrink:0; }
.lp__act-btn { padding:7px 14px; border-radius:8px; border:none; font-family:'Plus Jakarta Sans',sans-serif; font-size:.78rem; font-weight:700; cursor:pointer; transition:all .2s; white-space:nowrap; }
.lp__act-btn--process { background:#fffbeb; color:#d97706; border:1.5px solid #fde68a; }
.lp__act-btn--process:hover { background:#d97706; color:#fff; }
.lp__act-btn--close { background:#f0fdf4; color:#059669; border:1.5px solid #bbf7d0; }
.lp__act-btn--close:hover { background:#059669; color:#fff; }
.lp__act-btn--approve { background:#f0fdf4; color:#059669; border:1.5px solid #bbf7d0; }
.lp__act-btn--approve:hover { background:#059669; color:#fff; }
.lp__act-btn--reject { background:#fff5f5; color:#e53e3e; border:1.5px solid #fecaca; }
.lp__act-btn--reject:hover { background:#e53e3e; color:#fff; }
.lp__act-btn--detail { background:#f3f4f6; color:#6b7280; }
.lp__act-btn--detail:hover { background:#e5e7eb; }
.lp__priority-pill { padding:3px 10px; border-radius:100px; font-size:.7rem; font-weight:700; }
.lp__pill--high   { background:#fff5f5; color:#c53030; }
.lp__pill--medium { background:#fffbeb; color:#d97706; }
.lp__pill--low    { background:#f0fdf4; color:#059669; }
.lp__detail-modal { max-width:500px; text-align:left; }
.lp__close-btn { position:absolute; top:16px; right:16px; width:28px; height:28px; border:none; background:#f3f4f6; border-radius:7px; cursor:pointer; }
.lp__detail-header { display:flex; align-items:center; gap:8px; margin-bottom:12px; }
.lp__detail-title { font-family:'Fraunces',serif; font-size:1.4rem; font-weight:900; color:#111827; margin-bottom:10px; }
.lp__detail-desc { font-size:.875rem; color:#6b7280; line-height:1.7; margin-bottom:18px; }
.lp__detail-parties { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:16px; background:#f9fafb; border-radius:12px; margin-bottom:18px; }
.lp__party-label { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; margin-bottom:8px; }
.lp__party-user { display:flex; align-items:center; gap:8px; }
.lp__party-user p { font-size:.84rem; font-weight:700; color:#111827; }
.lp__party-user span { font-size:.72rem; color:#9ca3af; }
.lp__detail-note { margin-bottom:18px; }
.lp__detail-note label { display:block; font-size:.82rem; font-weight:700; color:#374151; margin-bottom:6px; }
.lp__detail-note textarea { width:100%; padding:10px 12px; border:1.5px solid #e5e7eb; border-radius:10px; font-family:'Plus Jakarta Sans',sans-serif; font-size:.85rem; outline:none; resize:vertical; transition:border-color .2s; }
.lp__detail-note textarea:focus { border-color:#fca5a5; }
.lp__detail-btns { display:flex; gap:8px; justify-content:flex-end; }
.modal-bg { position:fixed; inset:0; background:rgba(0,0,0,.45); backdrop-filter:blur(4px); z-index:1000; display:flex; align-items:center; justify-content:center; padding:24px; }
.modal-box { background:#fff; border-radius:20px; padding:28px; width:100%; box-shadow:0 20px 60px rgba(0,0,0,.2); animation:mIn .22s ease; position:relative; }
@keyframes mIn { from{opacity:0;transform:scale(.94) translateY(10px)} to{opacity:1;transform:scale(1) translateY(0)} }
.ap__toast { position:fixed; bottom:28px; right:28px; z-index:2000; padding:13px 22px; border-radius:12px; font-size:.875rem; font-weight:600; box-shadow:0 8px 28px rgba(0,0,0,.15); transform:translateY(20px); opacity:0; transition:all .3s; pointer-events:none; background:#111827; color:#fff; }
.ap__toast--show { transform:translateY(0); opacity:1; }
</style>