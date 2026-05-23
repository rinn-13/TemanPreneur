<template>
  <div class="buyer-page">
    <div class="buyer-back">
      <button @click="$router.back()" class="back-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Kembali
      </button>
    </div>

    <div class="buyer-body">
      <div class="ul-header">
        <h1 class="ul-title">Ulasan Saya</h1>
        <p class="ul-sub">{{ reviews.length }} ulasan telah diberikan</p>
      </div>

      <!-- Loading -->
      <div class="ul-list" v-if="loading">
        <div v-for="n in 3" :key="n" class="ul-skeleton"></div>
      </div>

      <!-- Empty -->
      <div class="ul-empty" v-else-if="!reviews.length">
        <span>⭐</span>
        <p>Belum ada ulasan. Beli produk dan bagikan pengalamanmu!</p>
        <router-link to="/katalog" class="ul-btn ul-btn--outline">Belanja Sekarang</router-link>
      </div>

      <!-- List ulasan -->
      <div class="ul-list" v-else>
        <div v-for="r in reviews" :key="r.id" class="ul-card">

          <!-- Gambar produk di kiri -->
          <div class="ul-card__img" :style="`background:${r.imgBg}`">
            <span>{{ r.emoji }}</span>
          </div>

          <!-- Konten ulasan di kanan -->
          <div class="ul-card__content">
            <div class="ul-card__top">
              <div>
                <p class="ul-card__product">{{ r.product_name }}</p>
                <p class="ul-card__toko">dari <span>{{ r.toko }}</span> · {{ r.date }}</p>
              </div>
              <div class="ul-card__actions">
                <button class="ul-act-btn ul-act-btn--edit" @click="openEdit(r)">️ Edit</button>
                <button class="ul-act-btn ul-act-btn--del" @click="deleteReview(r.id)">️</button>
              </div>
            </div>

            <!-- Bintang -->
            <div class="ul-stars">
              <span
                v-for="s in 5"
                :key="s"
                class="ul-star"
                :class="s <= r.rating ? 'ul-star--on' : 'ul-star--off'"
              ></span>
              <span class="ul-rating-label">{{ r.rating }}/5</span>
            </div>

            <!-- Teks ulasan -->
            <p class="ul-card__text">{{ r.text }}</p>

            <!-- Likes -->
            <div class="ul-card__footer">
              <button class="ul-like-btn"> {{ r.likes }} orang terbantu</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal edit -->
    <teleport to="body">
      <div class="ul-modal-bg" v-if="editModal.open" @click.self="editModal.open=false">
        <div class="ul-modal">
          <h3>Edit Ulasan</h3>
          <p class="ul-modal__product">{{ editModal.review?.product_name }}</p>
          <!-- Bintang interaktif -->
          <div class="ul-stars ul-stars--edit">
            <span
              v-for="s in 5"
              :key="s"
              class="ul-star ul-star--clickable"
              :class="s <= editModal.rating ? 'ul-star--on' : 'ul-star--off'"
              @click="editModal.rating = s"
            ></span>
          </div>
          <textarea v-model="editModal.text" rows="4" placeholder="Tulis ulasanmu..."></textarea>
          <div class="ul-modal__btns">
            <button class="ul-btn ul-btn--ghost" @click="editModal.open=false">Batal</button>
            <button class="ul-btn ul-btn--primary" @click="saveEdit" :disabled="saving">
              {{ saving ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </div>
      </div>
    </teleport>

    <div class="ul-toast" :class="{'ul-toast--show':toast.show}">{{ toast.msg }}</div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import api from '@/api/axios'

export default {
  name: 'BuyerUlasan',
  setup() {
    const loading = ref(true)
    const saving  = ref(false)
    const reviews = ref([])

    const editModal = reactive({
      open:false,
      review:null,
      rating:5,
      text:''
    })

    const toast = reactive({
      show:false,
      msg:''
    })

    const showToast = msg => {
      toast.msg = msg
      toast.show = true
      setTimeout(()=> toast.show=false, 3000)
    }

    // =========================
    //  FETCH REVIEW YANG BENAR
    // =========================
    const fetchMyReviews = async () => {
      try {
        const res = await api.get('/reviews/my')

        reviews.value = res.data.data.map(r => ({
          id: r.id,
          rating: r.rating,
          text: r.comment,
          product_name: r.product_name,
          toko: r.business_name || 'Toko',
          date: r.created_at,
          likes: 0,
          emoji: '️',
          imgBg: '#f1f5f9'
        }))

      } catch (err) {
        console.error('Fetch reviews error:', err)
        showToast('Gagal mengambil ulasan')
      } finally {
        loading.value = false
      }
    }

    // =========================
    // EDIT
    // =========================
    const openEdit = r => {
      editModal.review = r
      editModal.rating = r.rating
      editModal.text   = r.text
      editModal.open   = true
    }

    const saveEdit = async () => {
      saving.value = true
      try {
        await api.put(`/reviews/${editModal.review.id}`, {
          rating: editModal.rating,
          comment: editModal.text // ️ backend pakai "comment"
        })

        const idx = reviews.value.findIndex(r => r.id === editModal.review.id)

        if (idx !== -1) {
          reviews.value[idx].rating = editModal.rating
          reviews.value[idx].text   = editModal.text
        }

        showToast(' Ulasan berhasil diperbarui')
        editModal.open = false

      } catch (err) {
        console.error(err)
        showToast('Gagal update ulasan')
      } finally {
        saving.value = false
      }
    }

    // =========================
    // DELETE
    // =========================
    const deleteReview = async (id) => {
      if (!confirm('Hapus ulasan ini?')) return

      try {
        await api.delete(`/reviews/${id}`)
        reviews.value = reviews.value.filter(r => r.id !== id)
        showToast('️ Ulasan dihapus')
      } catch (err) {
        console.error(err)
        showToast('Gagal hapus')
      }
    }

    // =========================
    // MOUNT
    // =========================
    onMounted(() => {
      fetchMyReviews()
    })

    return {
      loading,
      saving,
      reviews,
      editModal,
      toast,
      openEdit,
      saveEdit,
      deleteReview
    }
  }
}
</script>

<style scoped>
.buyer-page { min-height:100vh; background:#f4f5f7; font-family:'Plus Jakarta Sans',sans-serif; }
.buyer-back { max-width:1100px; margin:0 auto; padding:20px 28px 0; }
.back-btn { display:flex; align-items:center; gap:7px; background:none; border:none; font-family:'Plus Jakarta Sans',sans-serif; font-size:.95rem; font-weight:700; color:#111827; cursor:pointer; text-decoration:underline; text-underline-offset:3px; }
.back-btn:hover { color:#e53e3e; }
.buyer-body { max-width:1100px; margin:0 auto; padding:24px 28px 64px; }

/* Header */
.ul-header { margin-bottom:24px; }
.ul-title { font-family:'Fraunces',serif; font-size:1.7rem; font-weight:900; color:#111827; }
.ul-sub { font-size:.85rem; color:#9ca3af; margin-top:3px; }

/* List */
.ul-list { display:flex; flex-direction:column; gap:18px; }
.ul-skeleton { height:160px; border-radius:18px; background:linear-gradient(90deg,#d0d5dd 25%,#b0b8c4 50%,#d0d5dd 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
@keyframes shimmer { 0%{background-position:200% 0}100%{background-position:-200% 0} }

/* Card */
.ul-card {
  display:flex;
  gap:0;
  background:#d0d5dd;
  border-radius:18px;
  border:1px solid #9ca3af;
  overflow:hidden;
  transition:box-shadow .2s;
}
.ul-card:hover { box-shadow:0 6px 24px rgba(0,0,0,.1); }

/* Image */
.ul-card__img {
  width:180px; min-height:160px; flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
  font-size:4rem;
  border-radius:16px;
  margin:16px 0 16px 16px;
}

/* Content */
.ul-card__content { flex:1; padding:18px 22px; }
.ul-card__top { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:10px; }
.ul-card__product { font-size:.95rem; font-weight:800; color:#111827; }
.ul-card__toko { font-size:.75rem; color:#9ca3af; margin-top:2px; }
.ul-card__toko span { color:#6366f1; font-weight:600; }
.ul-card__actions { display:flex; gap:6px; flex-shrink:0; }
.ul-act-btn { padding:5px 12px; border-radius:8px; font-family:'Plus Jakarta Sans',sans-serif; font-size:.75rem; font-weight:700; cursor:pointer; border:none; transition:all .18s; }
.ul-act-btn--edit { background:#fff; color:#374151; border:1px solid #b0b8c4; }
.ul-act-btn--edit:hover { border-color:#6366f1; color:#6366f1; }
.ul-act-btn--del { background:#fff5f5; color:#c53030; border:1px solid #fecaca; }
.ul-act-btn--del:hover { background:#e53e3e; color:#fff; }

/* Stars */
.ul-stars { display:flex; align-items:center; gap:2px; margin-bottom:8px; }
.ul-star { font-size:1.2rem; cursor:default; transition:transform .15s; }
.ul-star--on  { color:#f59e0b; }
.ul-star--off { color:#d1d5db; }
.ul-star--clickable { cursor:pointer; }
.ul-star--clickable:hover { transform:scale(1.2); }
.ul-stars--edit .ul-star { font-size:1.6rem; }
.ul-rating-label { font-size:.78rem; font-weight:700; color:#374151; margin-left:4px; }

/* Text */
.ul-card__text { font-size:.84rem; color:#374151; line-height:1.7; margin-bottom:12px; }

/* Footer */
.ul-card__footer { border-top:1px solid rgba(0,0,0,.08); padding-top:10px; }
.ul-like-btn { background:none; border:none; font-size:.78rem; color:#9ca3af; cursor:default; }

/* Empty */
.ul-empty { text-align:center; padding:72px 24px; }
.ul-empty span { font-size:3.5rem; display:block; margin-bottom:12px; }
.ul-empty p { font-size:.9rem; color:#9ca3af; margin-bottom:18px; max-width:320px; margin-left:auto; margin-right:auto; }
.ul-btn { display:inline-flex; align-items:center; gap:6px; padding:10px 20px; border-radius:9px; font-family:'Plus Jakarta Sans',sans-serif; font-size:.85rem; font-weight:700; cursor:pointer; border:none; transition:all .18s; text-decoration:none; }
.ul-btn--outline { background:#fff; color:#374151; border:1.5px solid #d1d5db; }
.ul-btn--outline:hover { border-color:#e53e3e; color:#e53e3e; }
.ul-btn--primary { background:linear-gradient(135deg,#374151,#111827); color:#fff; }
.ul-btn--ghost { background:#f3f4f6; color:#374151; }
.ul-btn--ghost:hover { background:#e5e7eb; }

/* Modal */
.ul-modal-bg { position:fixed; inset:0; background:rgba(0,0,0,.45); backdrop-filter:blur(4px); z-index:1000; display:flex; align-items:center; justify-content:center; padding:24px; }
.ul-modal { background:#fff; border-radius:20px; padding:32px; max-width:440px; width:100%; box-shadow:0 20px 60px rgba(0,0,0,.2); animation:mIn .22s ease; }
@keyframes mIn { from{opacity:0;transform:scale(.94) translateY(10px)}to{opacity:1;transform:scale(1) translateY(0)} }
.ul-modal h3 { font-family:'Fraunces',serif; font-size:1.3rem; font-weight:900; color:#111827; margin-bottom:4px; }
.ul-modal__product { font-size:.82rem; color:#9ca3af; margin-bottom:14px; }
.ul-modal textarea { width:100%; padding:10px 12px; border:1.5px solid #e5e7eb; border-radius:10px; font-family:'Plus Jakarta Sans',sans-serif; font-size:.875rem; outline:none; resize:vertical; margin:12px 0 18px; }
.ul-modal textarea:focus { border-color:#e53e3e; }
.ul-modal__btns { display:flex; gap:10px; justify-content:flex-end; }

/* Toast */
.ul-toast { position:fixed; bottom:28px; right:28px; z-index:2000; padding:13px 22px; border-radius:12px; font-size:.875rem; font-weight:600; box-shadow:0 8px 28px rgba(0,0,0,.15); transform:translateY(20px); opacity:0; transition:all .3s; pointer-events:none; background:#111827; color:#fff; }
.ul-toast--show { transform:translateY(0); opacity:1; }

@media (max-width:640px) {
  .ul-card { flex-direction:column; }
  .ul-card__img { width:calc(100% - 32px); height:120px; min-height:unset; margin:16px 16px 0; border-radius:12px; }
  .buyer-body { padding:16px 14px 48px; }
}
</style>