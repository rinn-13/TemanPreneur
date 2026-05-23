<template>
  <div class="buyer-page">
    <div class="buyer-back">
      <button @click="$router.back()" class="back-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Kembali
      </button>
    </div>

    <div class="buyer-body">

      <!-- ── PANEL FORM LAPORAN ── -->
      <div class="lp-main-card">
        <div class="lp-main-card__inner">

          <!-- Kiri: form -->
          <div class="lp-form-col">
            <!-- Gambar / ilustrasi -->
            <div class="lp-illust">
              <div class="lp-illust__box">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                  <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="white" stroke-width="2"/>
                  <line x1="12" y1="9" x2="12" y2="13" stroke="white" stroke-width="2" stroke-linecap="round"/>
                  <line x1="12" y1="17" x2="12.01" y2="17" stroke="white" stroke-width="2" stroke-linecap="round"/>
                </svg>
              </div>
            </div>

            <h2 class="lp-form-title">Laporkan Masalah</h2>
            <p class="lp-form-sub">Ceritakan masalah yang kamu alami dan tim kami akan segera menanganinya.</p>
          </div>

          <!-- Divider -->
          <div class="lp-divider"></div>

          <!-- Kanan: kolom form -->
          <div class="lp-fields-col">
            <!-- Status laporan sebelumnya -->
            <div class="lp-prev-reports" v-if="reports.length">
              <p class="lp-prev-title">Laporan Sebelumnya</p>
              <div v-for="rep in reports" :key="rep.id" class="lp-prev-item">
                <span class="lp-prev-type">{{ rep.subject || 'Laporan Masalah' }}</span>
                <span class="lp-prev-status" :class="reportStatusClass(rep.status)">{{ rep.status_display || reportStatusLabel(rep.status) }}</span>
                <span class="lp-prev-date">{{ new Date(rep.created_at).toLocaleDateString('id-ID') }}</span>
                <button
                  v-if="rep.status === 'in_progress'"
                  class="lp-resolve-btn"
                  :disabled="resolvingReportId === rep.id"
                  @click="confirmResolve(rep)"
                >
                  {{ resolvingReportId === rep.id ? 'Memproses...' : 'Konfirmasi Selesai' }}
                </button>
              </div>
            </div>

            <!-- Input fields (di dalam panel biru muda) -->
            <div class="lp-input-panel">
              <div class="lp-field">
                <input v-model="form.subject" type="text" placeholder="Subjek laporan..." class="lp-input"/>
                <p v-if="validationErrors.subject" class="lp-error-msg">{{ validationErrors.subject[0] }}</p>
              </div>
              <div class="lp-field">
                <input v-model="form.order_id" type="text" placeholder="ID Pesanan (angka, contoh: 123)" class="lp-input"/>
                <p v-if="validationErrors.order_id" class="lp-error-msg">{{ validationErrors.order_id[0] }}</p>
              </div>
              <p v-if="validationErrors.order_id" class="lp-error-msg">{{ validationErrors.order_id[0] }}</p>
            </div>
            
          </div>
        </div>
      </div>

      <!-- ── PANEL BAWAH (biru muda) ── -->
      <div class="lp-detail-panel">
        <div class="lp-detail-panel__fields">

          <div class="lp-field-full">
            <label>Jenis Masalah</label>
            <select v-model="form.type" class="lp-select">
              <option value="">— Pilih jenis masalah —</option>
              <option value="penipuan">🚨 Penipuan / Scam</option>
              <option value="produk_rusak">📦 Produk rusak</option>
              <option value="produk_tidak_sesuai">📦 Produk tidak sesuai</option>
              <option value="pengiriman_terlambat">🚚 Pengiriman terlambat</option>
              <option value="pengiriman_salah">🚚 Pengiriman salah</option>
              <option value="seller">💬 Seller tidak responsif</option>
              <option value="pembayaran">💳 Masalah pembayaran</option>
              <option value="lainnya">❓ Lainnya</option>
            </select>
            <p v-if="validationErrors.type" class="lp-error-msg">{{ validationErrors.type[0] }}</p>
          </div>

          <div class="lp-field-full">
            <label>Deskripsi Lengkap</label>
            <textarea
              v-model="form.message"
              rows="5"
              placeholder="Jelaskan masalah yang kamu alami secara lengkap dan rinci. Sertakan tanggal kejadian, nama produk, dan detail lainnya yang relevan..."
              class="lp-textarea"
            ></textarea>
            <span class="lp-char-count" :class="{'lp-char-count--over': form.message.length > 500}">
              {{ form.message.length }}/500
            </span>
            <p v-if="validationErrors.message" class="lp-error-msg">{{ validationErrors.message[0] }}</p>
          </div>

          <div class="lp-field-full">
            <label>Bukti (opsional)</label>
            <div class="lp-upload" @click="triggerUpload" @dragover.prevent @drop.prevent="handleDrop">
              <input ref="fileRef" type="file" accept="image/*" multiple @change="handleFile" style="display:none"/>
              <div v-if="!files.length" class="lp-upload__placeholder">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="#9ca3af" stroke-width="2"/><polyline points="17,8 12,3 7,8" stroke="#9ca3af" stroke-width="2"/><line x1="12" y1="3" x2="12" y2="15" stroke="#9ca3af" stroke-width="2"/></svg>
                <p>Klik atau seret gambar ke sini</p>
                <span>PNG, JPG hingga 5MB</span>
              </div>
              <div v-else class="lp-upload__previews">
                <div v-for="(f,i) in files" :key="i" class="lp-upload__preview">
                  <img :src="f.preview" alt=""/>
                  <button @click.stop="removeFile(i)">✕</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Submit -->
          <div class="lp-submit-row">
            <p class="lp-privacy-note">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="#9ca3af" stroke-width="2"/></svg>
              Laporanmu diproses secara rahasia dalam 1–3 hari kerja.
            </p>
            <button
              class="lp-submit-btn"
              @click="submitReport"
              :disabled="submitting || !form.subject || !form.type || !form.message"
            >
              {{ submitting ? 'Mengirim...' : '📨 Kirim Laporan' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast -->
    <div class="lp-toast" :class="{'lp-toast--show':toast.show,'lp-toast--err':toast.err}">{{ toast.msg }}</div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import api from '@/api/axios'

export default {
  name: 'BuyerLaporan',
  setup() {
    const submitting = ref(false)
    const resolvingReportId = ref(null)
    const fileRef    = ref(null)
    const files      = ref([])

    const form = reactive({ subject:'', order_id:'', type:'', message:'' })
    const validationErrors = ref({})
    const toast = reactive({ show:false, msg:'', err:false })

    const reports = ref([])

    const reportStatusLabel = status => ({
      open: 'Menunggu',
      in_progress: 'Sedang Diproses',
      closed: 'Selesai',
    }[status] || status)

    const reportStatusClass = status => ({
      open: 'lpr-open',
      in_progress: 'lpr-in_progress',
      closed: 'lpr-closed',
    }[status] || 'lpr-open')

    const showToast = (msg, err=false) => { toast.msg=msg; toast.err=err; toast.show=true; setTimeout(()=>toast.show=false,3500) }

    const triggerUpload = () => fileRef.value?.click()

    const handleFile = e => {
      Array.from(e.target.files).forEach(f => {
        files.value.push({ file:f, preview:URL.createObjectURL(f) })
      })
    }

    const handleDrop = e => {
      Array.from(e.dataTransfer.files).forEach(f => {
        if (f.type.startsWith('image/')) files.value.push({ file:f, preview:URL.createObjectURL(f) })
      })
    }

    const removeFile = i => {
      URL.revokeObjectURL(files.value[i].preview)
      files.value.splice(i, 1)
    }

    const submitReport = async () => {
      validationErrors.value = {}
      if (form.order_id && !/^\d+$/.test(form.order_id)) {
        showToast('ID pesanan harus berupa angka.', true)
        return
      }
      if (!form.message) {
        showToast('Lengkapi pesan laporan', true)
        return
      }
      submitting.value = true
      try {
        if (!form.message || form.message.length < 20) {
          showToast('Pesan laporan minimal 20 karakter.', true)
          return
        }

          const fd = new FormData()
          if (form.order_id) {
            fd.append('order_id', form.order_id)
          }
          fd.append('subject',     form.subject)
          fd.append('type',        form.type)
          fd.append('message',     form.message)
          files.value.forEach(f => fd.append('files[]', f.file))

          await api.post('/reports', fd, { headers:{'Content-Type':'multipart/form-data'} })

        showToast('✅ Laporan berhasil dikirim! Kami akan segera menanganinya.')
        form.subject=''; form.order_id=''; form.type=''; form.message=''
        files.value = []
        await fetchReports()
      } catch (e) {
        const response = e.response
        if (response?.status === 422 && response.data?.errors) {
          validationErrors.value = response.data.errors
          showToast('Periksa kembali data laporan Anda.', true)
        } else {
          showToast(response?.data?.message || 'Gagal mengirim laporan', true)
        }
      } finally {
        submitting.value = false
      }
    }

    const confirmResolve = async (report) => {
      if (!window.confirm('Apakah masalah ini sudah selesai dan dapat ditutup?')) {
        return
      }

      resolvingReportId.value = report.id
      try {
        await api.patch(`/reports/${report.id}/resolve`)
        showToast('✅ Laporan berhasil ditandai selesai.')
        await fetchReports()
      } catch (err) {
        console.error('Resolve report error:', err)
        showToast(err.response?.data?.message || 'Gagal menandai laporan selesai', true)
      } finally {
        resolvingReportId.value = null
      }
    }

    const fetchReports = async () => {
      try {
        const response = await api.get('/reports')
        reports.value = response.data.data || response.data || []
      } catch (err) {
        console.error('Fetch reports error:', err)
        reports.value = []
      }
    }

    onMounted(fetchReports)

    return { form, submitting, resolvingReportId, fileRef, files, toast, reports, validationErrors, showToast, triggerUpload, handleFile, handleDrop, removeFile, submitReport, confirmResolve, reportStatusLabel, reportStatusClass }
  }
}
</script>

<style scoped>
.buyer-page { min-height:100vh; background:#f4f5f7; font-family:'Plus Jakarta Sans',sans-serif; }
.buyer-back { max-width:1100px; margin:0 auto; padding:20px 28px 0; }
.back-btn { display:flex; align-items:center; gap:7px; background:none; border:none; font-family:'Plus Jakarta Sans',sans-serif; font-size:.95rem; font-weight:700; color:#111827; cursor:pointer; text-decoration:underline; text-underline-offset:3px; }
.back-btn:hover { color:#e53e3e; }
.buyer-body { max-width:1100px; margin:0 auto; padding:24px 28px 64px; display:flex; flex-direction:column; gap:20px; }

/* Main card */
.lp-main-card {
  background:linear-gradient(135deg,#d0d5dd,#b0b8c4);
  border-radius:20px; border:1px solid #9ca3af;
  padding:32px;
  overflow:hidden;
}
.lp-main-card__inner { display:flex; gap:0; align-items:flex-start; }
.lp-form-col { width:260px; flex-shrink:0; padding-right:32px; display:flex; flex-direction:column; gap:12px; }
.lp-illust { margin-bottom:4px; }
.lp-illust__box {
  width:80px; height:80px; border-radius:16px;
  background:linear-gradient(135deg,#ec4899,#db2777);
  display:flex; align-items:center; justify-content:center;
  box-shadow:0 4px 16px rgba(236,72,153,.3);
}
.lp-form-title { font-family:'Fraunces',serif; font-size:1.4rem; font-weight:900; color:#111827; }
.lp-form-sub { font-size:.82rem; color:#6b7280; line-height:1.6; }
.lp-divider { width:1px; background:#9ca3af; margin:0 32px; align-self:stretch; }
.lp-fields-col { flex:1; display:flex; flex-direction:column; gap:14px; }

/* Prev reports */
.lp-prev-title { font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:#6b7280; margin-bottom:8px; }
.lp-prev-item { display:flex; align-items:center; gap:8px; padding:7px 10px; background:rgba(255,255,255,.4); border-radius:9px; margin-bottom:6px; }
.lp-prev-type { font-size:.8rem; font-weight:600; color:#111827; flex:1; }
.lp-prev-status { padding:2px 9px; border-radius:100px; font-size:.65rem; font-weight:700; }
  .lp-resolve-btn { margin-left:auto; padding:6px 10px; border:none; border-radius:999px; background:#10b981; color:#fff; font-size:.72rem; font-weight:700; cursor:pointer; transition:transform .15s, opacity .15s; }
  .lp-resolve-btn:disabled { opacity:.6; cursor:not-allowed; }
  .lp-resolve-btn:hover:not(:disabled) { transform:translateY(-1px); }
.lpr-in_progress { background:#fef3c7; color:#d97706; }
.lpr-open        { background:#fff5f5; color:#c53030; }
.lp-prev-date { font-size:.7rem; color:#9ca3af; }

/* Input panel (biru muda) */
.lp-input-panel {
  background:linear-gradient(135deg,#dce8f0,#c8dce8);
  border-radius:14px; border:1px solid #a0b4c4;
  padding:18px; display:flex; flex-direction:column; gap:12px;
}
.lp-field { display:flex; flex-direction:column; gap:0; }
.lp-input {
  height:42px; padding:0 14px; border:none; border-bottom:1.5px solid #a0b4c4;
  background:transparent; font-family:'Plus Jakarta Sans',sans-serif; font-size:.875rem;
  color:#111827; outline:none; border-radius:0;
}
.lp-input::placeholder { color:#9ca3af; }
.lp-input:focus { border-bottom-color:#e53e3e; }

/* Detail panel (biru muda) */
.lp-detail-panel {
  background:linear-gradient(135deg,#dce8f0,#c8dce8);
  border-radius:20px; border:1px solid #a0b4c4;
  padding:28px 32px;
}
.lp-detail-panel__fields { display:flex; flex-direction:column; gap:18px; }
.lp-field-full { display:flex; flex-direction:column; gap:6px; }
.lp-field-full label { font-size:.8rem; font-weight:700; color:#374151; }
.lp-select {
  height:42px; padding:0 14px; border:1.5px solid #a0b4c4; border-radius:10px;
  background:#fff; font-family:'Plus Jakarta Sans',sans-serif; font-size:.875rem;
  color:#111827; outline:none; cursor:pointer; transition:border-color .18s;
}
.lp-select:focus { border-color:#e53e3e; }
.lp-textarea {
  padding:12px 14px; border:1.5px solid #a0b4c4; border-radius:10px;
  background:#fff; font-family:'Plus Jakarta Sans',sans-serif; font-size:.875rem;
  color:#111827; outline:none; resize:vertical; transition:border-color .18s; line-height:1.6;
}
.lp-textarea:focus { border-color:#e53e3e; }
.lp-char-count { font-size:.7rem; color:#9ca3af; align-self:flex-end; margin-top:3px; }
.lp-char-count--over { color:#e53e3e; }

/* Upload */
.lp-upload {
  border:2px dashed #a0b4c4; border-radius:12px; background:rgba(255,255,255,.5);
  min-height:100px; cursor:pointer; transition:border-color .18s; overflow:hidden;
}
.lp-upload:hover { border-color:#6366f1; }
.lp-upload__placeholder { padding:24px; text-align:center; }
.lp-upload__placeholder svg { display:block; margin:0 auto 8px; }
.lp-upload__placeholder p { font-size:.84rem; font-weight:600; color:#374151; }
.lp-upload__placeholder span { font-size:.72rem; color:#9ca3af; }
.lp-upload__previews { display:flex; flex-wrap:wrap; gap:8px; padding:12px; }
.lp-upload__preview { position:relative; width:64px; height:64px; border-radius:8px; overflow:hidden; }
.lp-upload__preview img { width:100%; height:100%; object-fit:cover; }
.lp-upload__preview button { position:absolute; top:2px; right:2px; width:18px; height:18px; border-radius:50%; background:#111827; border:none; color:#fff; font-size:.6rem; cursor:pointer; display:flex; align-items:center; justify-content:center; }

/* Submit row */
.lp-submit-row { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; }
.lp-privacy-note { display:flex; align-items:center; gap:6px; font-size:.76rem; color:#9ca3af; }
.lp-submit-btn {
  padding:12px 28px; border-radius:10px; border:none;
  background:linear-gradient(135deg,#374151,#111827);
  color:#fff; font-family:'Plus Jakarta Sans',sans-serif; font-size:.9rem; font-weight:700;
  cursor:pointer; transition:all .18s; white-space:nowrap;
}
.lp-submit-btn:hover:not(:disabled) { transform:translateY(-1px); box-shadow:0 4px 16px rgba(0,0,0,.25); }
.lp-submit-btn:disabled { opacity:.5; cursor:not-allowed; }

/* Toast */
.lp-toast { position:fixed; bottom:28px; right:28px; z-index:2000; padding:13px 22px; border-radius:12px; font-size:.875rem; font-weight:600; box-shadow:0 8px 28px rgba(0,0,0,.15); transform:translateY(20px); opacity:0; transition:all .3s; pointer-events:none; background:#111827; color:#fff; }
.lp-toast--show { transform:translateY(0); opacity:1; }
.lp-toast--err { background:#fff5f5; color:#c53030; border:1px solid #fecaca; }

@media (max-width:768px) {
  .lp-main-card__inner { flex-direction:column; gap:24px; }
  .lp-form-col { width:100%; padding-right:0; }
  .lp-divider { display:none; }
  .lp-detail-panel { padding:20px; }
  .buyer-body { padding:16px 14px 48px; }
}
</style>