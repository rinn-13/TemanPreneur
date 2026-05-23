<template>
  <div class="ap">
    <div class="ap__head">
      <div>
        <h1 class="ap__title">Pengaturan <span>Platform</span></h1>
        <p class="ap__sub">Konfigurasi platform dan parameter operasional</p>
      </div>
      <div class="pt__status-indicator pt__status--online">
        <span class="pt__status-dot"></span>
        Platform Online
      </div>
    </div>

    <!-- Maintenance mode banner (disembunyikan dari UI; route/API tetap ada) -->
    <div class="pt__maint-banner" v-if="showMaintenanceUi && pt.maintenance">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="2.5"/><line x1="12" y1="9" x2="12" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
      <span>Platform sedang dalam mode maintenance. Pengguna tidak dapat mengakses.</span>
      <button class="pt__maint-off" @click="toggleMaintenance">Matikan Maintenance</button>
    </div>

    <div class="pt__sections">

      <!-- Kolom kiri -->
      <div class="pt__col">

        <!-- Pengaturan Umum -->
        <div class="ap__card pt__section">
          <div class="ap__card-header">
            <h3 class="ap__card-title">️ Pengaturan Umum</h3>
            <button class="ap__btn ap__btn--primary" @click="saveGeneral" :disabled="saving">
              {{ saving?'Menyimpan...':'Simpan' }}
            </button>
          </div>
          <div class="pt__fields">
            <div class="pt__field">
              <label>Nama Platform</label>
              <input v-model="general.name" type="text" placeholder="TemanPreneur"/>
            </div>
            <div class="pt__field">
              <label>Deskripsi Singkat</label>
              <textarea v-model="general.description" rows="2" placeholder="Marketplace internal sekolah..."></textarea>
            </div>
            <div class="pt__field">
              <label>Email Admin Utama</label>
              <input v-model="general.adminEmail" type="email" placeholder="admin@sekolah.id"/>
            </div>
            <div class="pt__field">
              <label>URL Platform</label>
              <input v-model="general.url" type="text" placeholder="https://temanpreneur.sekolah.id"/>
            </div>
          </div>
        </div>

        <!-- Pengaturan Transaksi -->
        <div class="ap__card pt__section">
          <div class="ap__card-header">
            <h3 class="ap__card-title"> Pengaturan Transaksi</h3>
            <button class="ap__btn ap__btn--primary" @click="saveTrans" :disabled="savingTrans">Simpan</button>
          </div>
          <div class="pt__fields">
            <div class="pt__field-row">
              <div class="pt__field">
                <label>Minimum Transaksi (Rp)</label>
                <input v-model.number="trans.minOrder" type="number" min="0"/>
              </div>
              <div class="pt__field">
                <label>Maksimum Transaksi (Rp)</label>
                <input v-model.number="trans.maxOrder" type="number" min="0"/>
              </div>
            </div>
            <div class="pt__field">
              <label>Komisi Platform (%)</label>
              <div class="pt__input-suffix">
                <input v-model.number="trans.commission" type="number" min="0" max="100" step="0.5"/>
                <span>%</span>
              </div>
            </div>
            <div class="pt__toggle-row">
              <div class="pt__toggle-info">
                <p>Konfirmasi Otomatis Pesanan</p>
                <span>Pesanan dikonfirmasi otomatis setelah 3 hari</span>
              </div>
              <div class="pt__toggle" :class="{'pt__toggle--on':trans.autoConfirm}" @click="trans.autoConfirm=!trans.autoConfirm">
                <div class="pt__toggle-thumb"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Maintenance mode — disembunyikan dari panel; backend tetap dapat dipanggil manual -->
        <div class="ap__card pt__section" v-if="showMaintenanceUi">
          <div class="ap__card-header">
            <h3 class="ap__card-title"> Mode Maintenance</h3>
          </div>
          <div class="pt__fields">
            <div class="pt__toggle-row">
              <div class="pt__toggle-info">
                <p>Aktifkan Maintenance Mode</p>
                <span>Menonaktifkan akses publik ke platform</span>
              </div>
              <div class="pt__toggle" :class="{'pt__toggle--on':pt.maintenance}" @click="toggleMaintenance">
                <div class="pt__toggle-thumb"></div>
              </div>
            </div>
            <div class="pt__field" v-if="pt.maintenance">
              <label>Pesan Maintenance</label>
              <textarea v-model="pt.message" rows="2" placeholder="Platform sedang dalam pemeliharaan..."></textarea>
            </div>
            <div class="pt__field">
              <label>Jadwal Maintenance Berikutnya</label>
              <input v-model="pt.scheduledAt" type="datetime-local"/>
            </div>
          </div>
        </div>
      </div>

      <!-- Kolom kanan -->
      <div class="pt__col">

        <!-- Notifikasi -->
        <div class="ap__card pt__section">
          <div class="ap__card-header">
            <h3 class="ap__card-title"> Pengaturan Notifikasi</h3>
          </div>
          <div class="pt__fields">
            <div v-for="n in notifications" :key="n.id" class="pt__toggle-row">
              <div class="pt__toggle-info">
                <p>{{ n.label }}</p>
                <span>{{ n.desc }}</span>
              </div>
              <div class="pt__toggle" :class="{'pt__toggle--on':n.enabled}" @click="n.enabled=!n.enabled">
                <div class="pt__toggle-thumb"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Batasan platform -->
        <div class="ap__card pt__section">
          <div class="ap__card-header">
            <h3 class="ap__card-title">️ Batasan Platform</h3>
          </div>
          <div class="pt__fields">
            <div class="pt__field-row">
              <div class="pt__field">
                <label>Max Produk per Seller</label>
                <input v-model.number="limits.maxProducts" type="number" min="1"/>
              </div>
              <div class="pt__field">
                <label>Max Foto per Produk</label>
                <input v-model.number="limits.maxPhotos" type="number" min="1"/>
              </div>
            </div>
            <div class="pt__field">
              <label>Max Ukuran Foto (MB)</label>
              <input v-model.number="limits.maxFileSize" type="number" min="1"/>
            </div>
            <div class="pt__toggle-row">
              <div class="pt__toggle-info">
                <p>Registrasi Seller Dibuka</p>
                <span>Izinkan siswa mendaftar sebagai seller</span>
              </div>
              <div class="pt__toggle" :class="{'pt__toggle--on':limits.openRegistration}" @click="limits.openRegistration=!limits.openRegistration">
                <div class="pt__toggle-thumb"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Database & backup -->
        <div class="ap__card pt__section">
          <div class="ap__card-header">
            <h3 class="ap__card-title">️ Database & Backup</h3>
          </div>
          <div class="pt__db-stats">
            <div class="pt__db-stat" v-for="s in dbStats" :key="s.label">
              <p class="pt__db-val">{{ s.val }}</p>
              <p class="pt__db-lbl">{{ s.label }}</p>
            </div>
          </div>
          <div class="pt__db-actions">
            <button class="ap__btn ap__btn--ghost" @click="doBackup" :disabled="backingUp">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="currentColor" stroke-width="2"/><polyline points="7,10 12,15 17,10" stroke="currentColor" stroke-width="2"/><line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" stroke-width="2"/></svg>
              {{ backingUp?'Membackup...':'Backup Sekarang' }}
            </button>
            <button class="ap__btn ap__btn--ghost" style="color:#c53030;border-color:#fecaca;" @click="confirmClearCache">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><polyline points="3,6 5,6 21,6" stroke="currentColor" stroke-width="2"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" stroke="currentColor" stroke-width="2"/></svg>
              Clear Cache
            </button>
          </div>
          <div class="pt__last-backup" v-if="lastBackup">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            Backup terakhir: {{ lastBackup }}
          </div>
        </div>

      </div>
    </div>

    <div class="ap__toast" :class="{'ap__toast--show':toast.show,'ap__toast--err':toast.err}">{{ toast.msg }}</div>
  </div>
</template>

<script>
import { ref, reactive } from 'vue'
import api from '@/api/axios'

export default {
  name: 'AdminPengaturan',
  setup() {
    /** Set true jika ingin menampilkan kembali kontrol maintenance di UI */
    const showMaintenanceUi = false
    const saving     = ref(false)
    const savingTrans = ref(false)
    const backingUp  = ref(false)
    const lastBackup = ref('07 Mar 2025, 02:00')
    const toast = reactive({ show:false, msg:'', err:false })

    const general = reactive({ name:'TemanPreneur', description:'Platform marketplace internal sekolah untuk siswa wirausaha.', adminEmail:'admin@sekolah.id', url:'https://temanpreneur.sekolah.id' })
    const trans   = reactive({ minOrder:5000, maxOrder:5000000, commission:2.5, autoConfirm:true })
    const pt      = reactive({ maintenance:false, message:'Platform sedang dalam pemeliharaan. Mohon tunggu.', scheduledAt:'' })
    const limits  = reactive({ maxProducts:50, maxPhotos:5, maxFileSize:5, openRegistration:true })

    const notifications = reactive([
      {id:'new_order',label:'Pesanan Baru',desc:'Notifikasi ke admin saat ada pesanan masuk',enabled:true},
      {id:'new_user',label:'Pengguna Baru',desc:'Notifikasi saat siswa baru mendaftar',enabled:true},
      {id:'report',label:'Laporan Masuk',desc:'Notifikasi saat ada laporan pelanggaran',enabled:true},
      {id:'low_stock',label:'Stok Menipis',desc:'Notifikasi saat produk stok < 5',enabled:false},
    ])

    const dbStats = [
      {val:'2.4 GB',label:'Ukuran DB'},{val:'1.247',label:'Total User'},
      {val:'284',label:'Produk'},{val:'3.821',label:'Transaksi'},
    ]

    const showToast = (msg,err=false) => { toast.msg=msg; toast.err=err; toast.show=true; setTimeout(()=>toast.show=false,3000) }

    const saveGeneral = async () => { saving.value=true; try{ await api.put('/admin/settings/general',general); showToast(' Pengaturan umum tersimpan') }catch{ showToast('Gagal menyimpan',true) } finally{ saving.value=false } }
    const saveTrans   = async () => { savingTrans.value=true; try{ await api.put('/admin/settings/transaction',trans); showToast(' Pengaturan transaksi tersimpan') }catch{ showToast('Gagal menyimpan',true) } finally{ savingTrans.value=false } }
    const toggleMaintenance = async () => { pt.maintenance=!pt.maintenance; try{ await api.post('/admin/maintenance',{enabled:pt.maintenance}) }catch{} showToast(pt.maintenance?' Mode maintenance diaktifkan':' Platform kembali online', pt.maintenance) }
    const doBackup = async () => { backingUp.value=true; await new Promise(r=>setTimeout(r,2000)); lastBackup.value=new Intl.DateTimeFormat('id-ID',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}).format(new Date()); backingUp.value=false; showToast(' Backup berhasil dibuat') }
    const confirmClearCache = () => { if(confirm('Yakin ingin clear cache? Ini akan memperlambat platform sementara.')) { showToast('️ Cache berhasil dibersihkan') } }

    return { saving,savingTrans,backingUp,lastBackup,toast,general,trans,pt,limits,notifications,dbStats,saveGeneral,saveTrans,toggleMaintenance,doBackup,confirmClearCache,showMaintenanceUi }
  }
}
</script>

<style scoped>
.pt__status-indicator { display:flex; align-items:center; gap:8px; padding:8px 16px; border-radius:100px; font-size:.82rem; font-weight:700; border:1.5px solid; }
.pt__status-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
.pt__status--online { border-color:#bbf7d0; background:#f0fdf4; color:#059669; }
.pt__status--online .pt__status-dot { background:#10b981; box-shadow:0 0 0 3px rgba(16,185,129,.25); animation:pulse 2s infinite; }
.pt__status--maintenance { border-color:#fde68a; background:#fffbeb; color:#d97706; }
.pt__status--maintenance .pt__status-dot { background:#f59e0b; }
@keyframes pulse { 0%,100%{box-shadow:0 0 0 3px rgba(16,185,129,.25)} 50%{box-shadow:0 0 0 6px rgba(16,185,129,.1)} }
.pt__maint-banner { display:flex; align-items:center; gap:12px; padding:14px 18px; background:#fffbeb; border:1.5px solid #fde68a; border-radius:12px; margin-bottom:20px; font-size:.85rem; color:#d97706; }
.pt__maint-banner svg { flex-shrink:0; color:#f59e0b; }
.pt__maint-off { margin-left:auto; padding:6px 14px; background:#d97706; color:#fff; border:none; border-radius:8px; font-family:'Plus Jakarta Sans',sans-serif; font-size:.78rem; font-weight:700; cursor:pointer; transition:background .2s; white-space:nowrap; }
.pt__maint-off:hover { background:#b45309; }
.pt__sections { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.pt__col { display:flex; flex-direction:column; gap:16px; }
.pt__section {}
.pt__fields { padding:0 22px 20px; display:flex; flex-direction:column; gap:14px; }
.pt__field { display:flex; flex-direction:column; gap:5px; }
.pt__field label { font-size:.8rem; font-weight:700; color:#374151; }
.pt__field input,.pt__field textarea,.pt__field select { padding:9px 12px; border:1.5px solid #e5e7eb; border-radius:9px; font-family:'Plus Jakarta Sans',sans-serif; font-size:.875rem; outline:none; background:#fff; transition:border-color .2s,box-shadow .2s; }
.pt__field input:focus,.pt__field textarea:focus { border-color:#fca5a5; box-shadow:0 0 0 3px rgba(229,62,62,.08); }
.pt__field textarea { resize:vertical; }
.pt__field-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.pt__input-suffix { display:flex; border:1.5px solid #e5e7eb; border-radius:9px; overflow:hidden; transition:border-color .2s; }
.pt__input-suffix:focus-within { border-color:#fca5a5; box-shadow:0 0 0 3px rgba(229,62,62,.08); }
.pt__input-suffix input { border:none; outline:none; flex:1; padding:9px 12px; font-family:'Plus Jakarta Sans',sans-serif; font-size:.875rem; }
.pt__input-suffix span { padding:0 12px; background:#f3f4f6; border-left:1.5px solid #e5e7eb; display:flex; align-items:center; font-size:.85rem; font-weight:700; color:#6b7280; }
.pt__toggle-row { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:4px 0; }
.pt__toggle-info p { font-size:.85rem; font-weight:600; color:#111827; }
.pt__toggle-info span { font-size:.72rem; color:#9ca3af; }
.pt__toggle { width:44px; height:24px; border-radius:100px; background:#e5e7eb; cursor:pointer; position:relative; flex-shrink:0; transition:background .25s; }
.pt__toggle--on { background:#e53e3e; }
.pt__toggle-thumb { position:absolute; top:3px; left:3px; width:18px; height:18px; border-radius:50%; background:#fff; box-shadow:0 1px 4px rgba(0,0,0,.2); transition:left .25s; }
.pt__toggle--on .pt__toggle-thumb { left:23px; }
.pt__db-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; padding:0 22px 16px; }
.pt__db-stat { text-align:center; padding:12px 8px; background:#f9fafb; border-radius:10px; border:1px solid #f3f4f6; }
.pt__db-val { font-size:1.1rem; font-weight:900; color:#111827; font-family:'Fraunces',serif; }
.pt__db-lbl { font-size:.65rem; font-weight:600; color:#9ca3af; margin-top:2px; }
.pt__db-actions { display:flex; gap:10px; padding:0 22px 12px; }
.pt__last-backup { display:flex; align-items:center; gap:6px; padding:0 22px 16px; font-size:.72rem; color:#9ca3af; }
.pt__last-backup svg { color:#10b981; }
.ap__toast { position:fixed; bottom:28px; right:28px; z-index:2000; padding:13px 22px; border-radius:12px; font-size:.875rem; font-weight:600; box-shadow:0 8px 28px rgba(0,0,0,.15); transform:translateY(20px); opacity:0; transition:all .3s; pointer-events:none; background:#111827; color:#fff; }
.ap__toast--show { transform:translateY(0); opacity:1; }
.ap__toast--err { background:#fff5f5; color:#c53030; border:1px solid #fecaca; }
@media (max-width:768px) { .pt__sections{grid-template-columns:1fr} .pt__db-stats{grid-template-columns:repeat(2,1fr)} }
</style>