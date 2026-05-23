<template>
  <section class="up">
    <header class="up__head">
      <h1 class="up__title">Upgrade Premium</h1>
      <p class="up__sub">Tingkatkan bisnis Anda dengan fitur premium eksklusif.</p>
    </header>

    <!-- Current Status Alert -->
    <div v-if="isPremium" class="alert alert-success">
       Anda sudah menjadi seller premium! Nikmati semua fitur eksklusif.
    </div>

    <!-- Features Comparison -->
    <div class="up__comparison">
      <table class="up__table">
        <thead>
          <tr>
            <th class="up__table-feature">Fitur</th>
            <th>Gratis</th>
            <th class="up__table-premium">Premium</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Produk Maksimal</td>
            <td class="up__table-center">
              <span class="up__check">2 Produk</span>
            </td>
            <td class="up__table-center up__table-highlight">
              <span class="up__check">️ Tanpa Batas</span>
            </td>
          </tr>
          <tr>
            <td>Kelola Pesanan</td>
            <td class="up__table-center"></td>
            <td class="up__table-center up__table-highlight"></td>
          </tr>
          <tr>
            <td>Analitik Lanjutan</td>
            <td class="up__table-center"></td>
            <td class="up__table-center up__table-highlight"></td>
          </tr>
          <tr>
            <td>Manajemen Tim</td>
            <td class="up__table-center"></td>
            <td class="up__table-center up__table-highlight"> (hingga 5 anggota)</td>
          </tr>
          <tr>
            <td>Custom Tema Toko</td>
            <td class="up__table-center"></td>
            <td class="up__table-center up__table-highlight"></td>
          </tr>
          <tr>
            <td>Prioritas Tampilan</td>
            <td class="up__table-center"></td>
            <td class="up__table-center up__table-highlight"></td>
          </tr>
          <tr>
            <td>Badge Premium</td>
            <td class="up__table-center"></td>
            <td class="up__table-center up__table-highlight">⭐ Terlihat</td>
          </tr>
          <tr>
            <td>Dukungan Prioritas</td>
            <td class="up__table-center"></td>
            <td class="up__table-center up__table-highlight"></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pricing Cards -->
    <div class="up__grid">
      <!-- Free Plan -->
      <div class="up__card">
        <div class="up__card-badge">Paket Gratis</div>
        <h3 class="up__plan-name">Regular Seller</h3>
        <p class="up__price">Rp 0 <span>/ bulan</span></p>
        <p class="up__price-note">Gratis selamanya</p>
        
        <ul class="up__list">
          <li> Kelola hingga 2 produk (reguler)</li>
          <li> Kelola pesanan pembeli</li>
          <li> Blog usaha sederhana</li>
          <li> Notifikasi pesanan</li>
          <li> Produk tanpa batas</li>
          <li> Manajemen tim</li>
          <li> Custom tema toko</li>
        </ul>

        <button class="up__btn up__btn--secondary" :disabled="!isPremium">
          {{ isPremium ? 'Paket Aktif' : 'Paket Saat Ini' }}
        </button>
      </div>

      <!-- Premium Plan -->
      <div class="up__card up__card--featured">
        <div class="up__card-badge up__card-badge--premium">⭐ Paket Premium</div>
        <h3 class="up__plan-name">Premium Seller</h3>
        <p class="up__price">Rp 99.000 <span>/ bulan</span></p>
        <p class="up__price-note">Hemat hingga 35% untuk paket tahunan</p>
        
        <ul class="up__list">
          <li> Produk tanpa batas</li>
          <li> Manajemen tim (hingga 5 anggota)</li>
          <li> Custom tema toko & warna</li>
          <li> Prioritas tampil di katalog</li>
          <li> Analitik penjualan lanjutan</li>
          <li> Badge premium di toko</li>
          <li> Dukungan prioritas 24/7</li>
        </ul>

        <div class="up__code-field">
          <label for="premium-code">Kode akses dari admin (opsional)</label>
          <input
            id="premium-code"
            v-model="accessCode"
            type="text"
            autocomplete="off"
            placeholder="Contoh: PREMIUM-2026-XXXX"
          />
        </div>

        <button 
          @click="upgradeToPremium"
          :disabled="isPremium"
          class="up__btn up__btn--primary"
        >
          {{ isPremium ? 'Anda Sudah Premium' : 'Aktifkan Premium' }}
        </button>

        <p class="up__payment-note">
          Premium diaktifkan dengan kode dari admin sekolah. Integrasi pembayaran online dapat ditambahkan kemudian.
        </p>
      </div>
    </div>

    <!-- Benefits Section -->
    <div class="up__benefits">
      <h2 class="up__benefits-title">Keuntungan Menjadi Premium</h2>
      <div class="up__benefits-grid">
        <div class="up__benefit-card">
          <div class="up__benefit-icon"></div>
          <h4>Produk Tanpa Batas</h4>
          <p>Jual sebanyak mungkin produk tanpa batasan. Lebih banyak produk = lebih banyak penjualan!</p>
        </div>

        <div class="up__benefit-card">
          <div class="up__benefit-icon"></div>
          <h4>Tim Profesional</h4>
          <p>Tambahkan hingga 5 anggota tim untuk membantu mengelola bisnis Anda dengan lebih efisien.</p>
        </div>

        <div class="up__benefit-card">
          <div class="up__benefit-icon"></div>
          <h4>Analitik Mendalam</h4>
          <p>Pantau performa produk, tren penjualan, dan perilaku pembeli dengan detail lengkap.</p>
        </div>

        <div class="up__benefit-card">
          <div class="up__benefit-icon"></div>
          <h4>Tema Custom</h4>
          <p>Desain toko unik dengan warna dan tema pilihan Anda untuk meningkatkan identitas brand.</p>
        </div>

        <div class="up__benefit-card">
          <div class="up__benefit-icon">⭐</div>
          <h4>Badge Premium</h4>
          <p>Tampilkan badge premium di profil toko Anda dan dapatkan kepercayaan pembeli lebih.</p>
        </div>

        <div class="up__benefit-card">
          <div class="up__benefit-icon"></div>
          <h4>Prioritas Tampilan</h4>
          <p>Produk Anda ditampilkan lebih tinggi di katalog untuk mendapat visibilitas lebih besar.</p>
        </div>
      </div>
    </div>

    <!-- FAQ Section -->
    <div class="up__faq">
      <h2 class="up__faq-title">Pertanyaan Umum</h2>
      <div class="up__faq-items">
        <div 
          v-for="(faq, idx) in faqs" 
          :key="idx"
          class="up__faq-item"
          @click="toggleFaq(idx)"
        >
          <div class="up__faq-question">
            <span>{{ faq.q }}</span>
            <span class="up__faq-toggle">{{ expandedFaq === idx ? '▼' : '▶' }}</span>
          </div>
          <div v-if="expandedFaq === idx" class="up__faq-answer">
            {{ faq.a }}
          </div>
        </div>
      </div>
    </div>

    <!-- Loading / Processing -->
    <div v-if="processing" class="up__processing">
      <div class="up__spinner"></div>
      <p>Memproses upgrade premium Anda...</p>
    </div>
  </section>
</template>

<script>
import { ref, onMounted } from 'vue'
import api from '@/api/axios'

export default {
  name: 'SellerUpgradePremium',
  setup() {
    const isPremium = ref(false)
    const accessCode = ref('')
    const expandedFaq = ref(null)
    const processing = ref(false)

    const faqs = [
      {
        q: 'Bagaimana cara membayar premium?',
        a: 'Kami menawarkan berbagai metode pembayaran termasuk transfer bank, kartu kredit, dan e-wallet melalui Midtrans. Proses pembayaran aman dan terpercaya.'
      },
      {
        q: 'Apakah bisa membatalkan premium kapan saja?',
        a: 'Ya, Anda dapat membatalkan langganan kapan saja tanpa penalti. Namun fitur premium akan langsung tidak tersedia pada tanggal pembatalan.'
      },
      {
        q: 'Apa yang terjadi jika saya downgrade setelah membeli banyak produk?',
        a: 'Jika Anda downgrade ke paket reguler, produk yang melebihi batas 2 akan perlu dikurangi sesuai kebijakan platform.'
      },
      {
        q: 'Apakah ada garansi uang kembali?',
        a: 'Ya! Kami menawarkan garansi uang kembali 100% dalam 7 hari pertama jika Anda tidak puas dengan layanan premium kami.'
      }
    ]

    const checkPremium = async () => {
      try {
        const { data } = await api.get('/seller/stats')
        isPremium.value = data.isPremium || false
      } catch (err) {
        console.error('Check premium error:', err)
      }
    }

    const toggleFaq = (idx) => {
      expandedFaq.value = expandedFaq.value === idx ? null : idx
    }

    const upgradeToPremium = async () => {
      if (isPremium.value) {
        alert('Anda sudah menjadi seller premium!')
        return
      }

      processing.value = true
      try {
        const { data } = await api.post('/subscription/upgrade', {
          plan: 'premium',
          duration: 'monthly',
          access_code: accessCode.value?.trim() || undefined,
        })

        if (data.payment_url) {
          window.location.href = data.payment_url
          return
        }

        if (data.success && data.data?.is_premium) {
          isPremium.value = true
          alert(data.message || 'Premium berhasil diaktifkan!')
          accessCode.value = ''
          return
        }

        alert(data.message || 'Gunakan kode akses dari admin atau hubungi admin sekolah.')
      } catch (err) {
        console.error('Upgrade error:', err)
        const msg = err.response?.data?.message || err.message || 'Gagal melakukan upgrade.'
        alert(msg)
      } finally {
        processing.value = false
      }
    }

    onMounted(checkPremium)

    return {
      isPremium,
      accessCode,
      expandedFaq,
      processing,
      faqs,
      toggleFaq,
      upgradeToPremium,
    }
  }
}
</script>

<style scoped>
/* Main Container */
.up { padding: 24px 24px 56px; }
.up__head { margin-bottom: 24px; }
.up__title { font-size: 1.875rem; font-weight: 900; color: #1f2937; }
.up__sub { color: #6b7280; margin-top: 4px; font-size: .95rem; }

/* Alert Styles */
.alert {
  padding: 12px 16px;
  margin-bottom: 16px;
  border-radius: 8px;
  font-size: 0.9rem;
}
.alert-success {
  background: #dcfce7;
  color: #15803d;
  border: 1px solid #86efac;
}

.up__code-field {
  margin: 16px 0;
}
.up__code-field label {
  display: block;
  font-size: 0.8rem;
  font-weight: 700;
  color: #374151;
  margin-bottom: 6px;
}
.up__code-field input {
  width: 100%;
  max-width: 360px;
  padding: 10px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.9rem;
}

/* Comparison Table */
.up__comparison {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  padding: 20px;
  margin-bottom: 24px;
  overflow-x: auto;
}
.up__table {
  width: 100%;
  border-collapse: collapse;
}
.up__table thead {
  background: #f9fafb;
  border-bottom: 2px solid #e5e7eb;
}
.up__table th {
  padding: 16px;
  text-align: left;
  font-weight: 800;
  color: #374151;
  font-size: 0.9rem;
}
.up__table-feature { width: 40%; }
.up__table-premium { background: #eff6ff; color: #1e40af; }
.up__table td {
  padding: 14px 16px;
  border-bottom: 1px solid #f3f4f6;
}
.up__table tbody tr:hover {
  background: #f9fafb;
}
.up__table-center {
  text-align: center;
}
.up__table-highlight {
  background: #f0f9ff;
  font-weight: 600;
}
.up__check {
  display: inline-block;
  padding: 4px 8px;
  background: #dcfce7;
  color: #15803d;
  border-radius: 4px;
  font-size: 0.85rem;
  font-weight: 700;
}

/* Plan Cards */
.up__grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
  margin-bottom: 40px;
}
.up__card {
  background: #fff;
  border: 2px solid #e5e7eb;
  border-radius: 16px;
  padding: 28px;
  position: relative;
  transition: all 0.3s ease;
}
.up__card:hover {
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}
.up__card--featured {
  border-color: #3b82f6;
  background: linear-gradient(135deg, #f0f9ff 0%, #fff 100%);
  transform: scale(1.02);
}
.up__card-badge {
  position: absolute;
  top: 16px;
  right: 16px;
  background: #f3f4f6;
  color: #6b7280;
  padding: 4px 12px;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 800;
}
.up__card-badge--premium {
  background: #fbbf24;
  color: #92400e;
}

.up__plan-name {
  margin: 0 0 12px;
  font-size: 1.3rem;
  font-weight: 900;
  color: #111827;
}
.up__price {
  font-size: 2rem;
  font-weight: 900;
  color: #111827;
  margin: 0 0 4px;
}
.up__price span {
  font-size: 1rem;
  color: #9ca3af;
  font-weight: 600;
}
.up__price-note {
  color: #9ca3af;
  font-size: 0.85rem;
  margin: 0 0 20px;
}

.up__list {
  list-style: none;
  padding: 0;
  margin: 0 0 24px;
}
.up__list li {
  padding: 10px 0;
  color: #374151;
  font-size: 0.95rem;
  border-bottom: 1px solid #f3f4f6;
}
.up__list li:last-child {
  border-bottom: none;
}

.up__btn {
  width: 100%;
  padding: 14px;
  border-radius: 10px;
  border: none;
  font-weight: 800;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s;
}
.up__btn--primary {
  background: #111827;
  color: #fff;
}
.up__btn--primary:hover:not(:disabled) {
  opacity: 0.92;
  transform: translateY(-2px);
}
.up__btn--secondary {
  background: #f3f4f6;
  color: #6b7280;
  border: 1px solid #d1d5db;
}
.up__btn--secondary:hover:not(:disabled) {
  background: #e5e7eb;
}
.up__btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.up__payment-note {
  margin-top: 12px;
  font-size: 0.75rem;
  color: #9ca3af;
  text-align: center;
}

/* Benefits Section */
.up__benefits {
  margin-bottom: 40px;
}
.up__benefits-title {
  text-align: center;
  font-size: 1.75rem;
  font-weight: 900;
  color: #111827;
  margin-bottom: 30px;
}
.up__benefits-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}
.up__benefit-card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 24px;
  text-align: center;
  transition: all 0.3s;
}
.up__benefit-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
}
.up__benefit-icon {
  font-size: 3rem;
  margin-bottom: 12px;
}
.up__benefit-card h4 {
  margin: 0 0 8px;
  font-size: 1.1rem;
  color: #111827;
}
.up__benefit-card p {
  margin: 0;
  color: #6b7280;
  font-size: 0.9rem;
  line-height: 1.5;
}

/* FAQ Section */
.up__faq {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  padding: 24px;
  margin-bottom: 40px;
}
.up__faq-title {
  margin: 0 0 20px;
  font-size: 1.3rem;
  font-weight: 900;
  color: #111827;
}
.up__faq-items { display: flex; flex-direction: column; gap: 8px; }
.up__faq-item {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}
.up__faq-item:hover {
  border-color: #3b82f6;
  background: #f0f9ff;
}
.up__faq-question {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px;
  font-weight: 700;
  color: #111827;
}
.up__faq-toggle {
  color: #9ca3af;
  transition: transform 0.2s;
}
.up__faq-answer {
  padding: 12px 14px;
  border-top: 1px solid #e5e7eb;
  color: #6b7280;
  line-height: 1.6;
}

/* Processing State */
.up__processing {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}
.up__spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #f3f4f6;
  border-top-color: #111827;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}
.up__processing p {
  color: #fff;
  font-weight: 600;
}

@media (max-width: 1024px) {
  .up__benefits-grid { grid-template-columns: repeat(2, 1fr); }
  .up__grid { grid-template-columns: 1fr; }
  .up__card--featured { transform: scale(1); }
}

@media (max-width: 768px) {
  .up { padding: 16px 16px 48px; }
  .up__title { font-size: 1.3rem; }
  .up__benefits-grid { grid-template-columns: 1fr; }
  .up__comparison { overflow-x: auto; }
}
</style>

<style scoped>
.up{ padding: 24px 24px 56px; }
.up__head{ margin-bottom: 18px; }
.up__title{ font-size: 1.5rem; font-weight: 900; color: #111827; }
.up__sub{ color:#6b7280; margin-top: 4px; font-size: .9rem; }
.up__grid{ display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 12px; }
.up__card{ background:#fff; border:1px solid #e5e7eb; border-radius: 14px; padding: 16px; }
.up__card h3{ margin: 0 0 10px; font-weight: 900; color:#111827; }
.up__price{ font-size: 1.6rem; font-weight: 900; color:#111827; margin: 0 0 12px; }
.up__price span{ font-size: .9rem; color:#9ca3af; font-weight: 800; }
.up__list{ margin: 0 0 14px; padding-left: 18px; color:#374151; }
.up__list li{ margin: 6px 0; }
.up__btn{ height: 42px; width: 100%; border-radius: 10px; border:none; background:#111827; color:#fff; font-weight: 900; cursor:pointer; }
.up__btn--ghost{ background:#fff; color:#6b7280; border: 1px solid #e5e7eb; cursor:not-allowed; }
.up__card--muted{ background:#f9fafb; }
@media (max-width: 900px){ .up__grid{ grid-template-columns: 1fr; } }
</style>

