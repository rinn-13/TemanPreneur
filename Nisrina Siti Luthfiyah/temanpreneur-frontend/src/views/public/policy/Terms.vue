<template>
  <div class="policy-page">
    <button class="back-btn" @click="$router.back()">← Kembali</button>
    
    <div class="policy-header">
      <h1>Syarat & Ketentuan</h1>
      <p>Harap baca dengan teliti sebelum menggunakan platform TemanPreneur</p>
    </div>

    <div class="policy-content">
      <div class="policy-section">
        <h2>1. Pengguna yang Diizinkan</h2>
        <p>Platform ini hanya dapat digunakan oleh siswa/siswi yang terdaftar di sekolah. Orang tua atau wali tidak diizinkan memiliki akun aktif, namun dapat membantu pengawasan.</p>
      </div>

      <div class="policy-section">
        <h2>2. Tanggung Jawab Pengguna</h2>
        <ul>
          <li>Anda bertanggung jawab atas semua aktivitas di akun Anda</li>
          <li>Jaga kerahasiaan password dan jangan share dengan orang lain</li>
          <li>Laporkan aktivitas mencurigakan ke admin segera</li>
        </ul>
      </div>

      <div class="policy-section">
        <h2>3. Produk yang Dilarang</h2>
        <p>Berikut produk TIDAK boleh dijual di TemanPreneur:</p>
        <ul>
          <li>Produk ilegal atau berbahaya</li>
          <li>Barang milik sekolah atau curi</li>
          <li>Produk palsu atau hasil titipan tanpa persetujuan</li>
          <li>Konten yang melanggar aturan sekolah</li>
        </ul>
      </div>

      <div class="policy-section">
        <h2>4. Transaksi Aman</h2>
        <p>Semua transaksi dilakukan antar siswa di lingkungan sekolah. Admin tidak mengambil komisi tetapi berhak memantau dan bertindak jika ada pelanggaran.</p>
      </div>

      <div class="policy-section">
        <h2>5. Pelanggaran & Hukuman</h2>
        <ul>
          <li>Peringatan untuk pelanggaran ringan</li>
          <li>Suspend akun untuk pelanggaran serius</li>
          <li>Laporan ke sekolah untuk pelanggaran berat (fraud, penipuan, dll)</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PolicyTermsPage',
}
</script>

<style scoped>
.policy-page {
  max-width: 800px;
  margin: 40px auto;
  padding: 0 20px;
  font-family: 'Plus Jakarta Sans', sans-serif;
}
.back-btn {
  background: none;
  border: none;
  color: #e53e3e;
  font-weight: 700;
  cursor: pointer;
  margin-bottom: 16px;
}
.policy-header {
  text-align: center;
  margin-bottom: 40px;
}
.policy-header h1 {
  font-size: 2.5rem;
  color: #111827;
  margin-bottom: 10px;
}
.policy-header p {
  font-size: 1.1rem;
  color: #6b7280;
}
.policy-content {
  display: flex;
  flex-direction: column;
  gap: 30px;
}
.policy-section h2 {
  font-size: 1.5rem;
  color: #374151;
  margin-bottom: 15px;
}
.policy-section p {
  color: #6b7280;
  line-height: 1.6;
  margin-bottom: 15px;
}
.policy-section ul {
  margin-left: 20px;
}
.policy-section li {
  color: #6b7280;
  line-height: 1.8;
  margin-bottom: 10px;
}
</style>