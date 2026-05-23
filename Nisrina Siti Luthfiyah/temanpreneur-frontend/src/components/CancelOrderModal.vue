<template>
  <teleport to="body">
    <div v-if="open" class="com-modal-bg" @click.self="$emit('close')">
      <div class="com-modal" role="dialog" aria-labelledby="cancel-title">
        <button class="com-modal__close" @click="$emit('close')" aria-label="Tutup">×</button>
        <div class="com-modal__icon">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" stroke="#ef4444" stroke-width="2"/>
            <line x1="12" y1="8" x2="12" y2="13" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/>
            <circle cx="12" cy="16.5" r="1" fill="#ef4444"/>
          </svg>
        </div>
        <h2 id="cancel-title" class="com-modal__title">Batalkan Pesanan?</h2>
        <p class="com-modal__sub">
          Pesanan <strong>#{{ orderId }}</strong> akan dibatalkan. Stok produk akan dikembalikan ke penjual.
          Tindakan ini tidak dapat dibatalkan.
        </p>

        <div class="com-modal__field">
          <label>Alasan Pembatalan <span class="req">*</span></label>
          <textarea
            v-model="reason"
            rows="4"
            placeholder="Jelaskan alasan pembatalan (minimal 10 karakter)..."
            class="com-modal__textarea"
            :class="{ 'com-modal__textarea--error': error }"
          />
          <p v-if="error" class="com-modal__error">{{ error }}</p>
          <p class="com-modal__hint">{{ reason.length }}/500 karakter</p>
        </div>

        <div class="com-modal__actions">
          <button class="com-btn com-btn--ghost" @click="$emit('close')" :disabled="loading">
            Kembali
          </button>
          <button class="com-btn com-btn--danger" @click="submit" :disabled="loading || reason.length < 10">
            {{ loading ? 'Membatalkan...' : 'Ya, Batalkan Pesanan' }}
          </button>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  open: { type: Boolean, default: false },
  orderId: { type: [Number, String], default: '' },
  loading: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'confirm'])

const reason = ref('')
const error = ref('')

const submit = () => {
  error.value = ''
  if (reason.value.trim().length < 10) {
    error.value = 'Alasan minimal 10 karakter'
    return
  }
  emit('confirm', reason.value.trim())
}
</script>

<style scoped>
.com-modal-bg {
  position: fixed; inset: 0; z-index: 9999;
  background: rgba(0,0,0,.5); backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center; padding: 16px;
}
.com-modal {
  background: #fff; border-radius: 16px; padding: 28px;
  max-width: 440px; width: 100%;
  box-shadow: 0 24px 48px rgba(0,0,0,.18);
  position: relative;
}
.com-modal__close {
  position: absolute; top: 14px; right: 16px;
  background: none; border: none; font-size: 1.4rem;
  color: #9ca3af; cursor: pointer; line-height: 1;
}
.com-modal__icon { text-align: center; margin-bottom: 12px; }
.com-modal__title {
  font-size: 1.15rem; font-weight: 800; color: #111827;
  text-align: center; margin: 0 0 8px;
}
.com-modal__sub {
  font-size: 0.875rem; color: #6b7280; text-align: center;
  line-height: 1.6; margin: 0 0 20px;
}
.com-modal__field label {
  display: block; font-size: 0.8rem; font-weight: 700;
  color: #374151; margin-bottom: 6px;
}
.req { color: #ef4444; }
.com-modal__textarea {
  width: 100%; padding: 10px 12px; border: 1.5px solid #e5e7eb;
  border-radius: 10px; font-family: inherit; font-size: 0.875rem;
  resize: vertical; transition: border-color .18s;
}
.com-modal__textarea:focus { outline: none; border-color: #e53e3e; }
.com-modal__textarea--error { border-color: #ef4444; }
.com-modal__error { color: #ef4444; font-size: 0.78rem; margin: 4px 0 0; }
.com-modal__hint { color: #9ca3af; font-size: 0.75rem; margin: 4px 0 0; text-align: right; }
.com-modal__actions { display: flex; gap: 10px; margin-top: 20px; }
.com-btn {
  flex: 1; padding: 11px; border-radius: 10px; font-weight: 700;
  font-size: 0.875rem; cursor: pointer; border: none; transition: all .18s;
}
.com-btn--ghost { background: #f3f4f6; color: #374151; }
.com-btn--ghost:hover:not(:disabled) { background: #e5e7eb; }
.com-btn--danger { background: #ef4444; color: #fff; }
.com-btn--danger:hover:not(:disabled) { background: #dc2626; }
.com-btn:disabled { opacity: .55; cursor: not-allowed; }
</style>
