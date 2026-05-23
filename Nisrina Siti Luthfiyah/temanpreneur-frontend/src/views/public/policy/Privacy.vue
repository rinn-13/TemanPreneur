<template>
  <div class="policy-page">
    <button class="back-btn" @click="$router.back()">← Kembali</button>
    
    <div class="policy-header">
      <h1>Kebijakan Privasi</h1>
      <p>Kami menganggap privasi Anda sangat penting</p>
    </div>

    <div class="policy-content">
      <div class="policy-section">
        <h2>1. Data yang Kami Kumpulkan</h2>
        <ul>
          <li>Nama lengkap dan email sekolah</li>
          <li>Nomor telepon (opsional)</li>
          <li>Riwayat pembelian dan penjualan</li>
          <li>Rating dan ulasan</li>
          <li>IP address dan data teknis browser</li>
        </ul>
      </div>

      <div class="policy-section">
        <h2>2. Penggunaan Data</h2>
        <p>Data Anda digunakan untuk:</p>
        <ul>
          <li>Memproses transaksi Anda</li>
          <li>Meningkatkan layanan platform</li>
          <li>Mengirim notifikasi penting</li>
          <li>Mencegah fraud dan penipuan</li>
          <li>Mentaati aturan sekolah</li>
        </ul>
      </div>

      <div class="policy-section">
        <h2>3. Keamanan Data</h2>
        <p>Kami menggunakan enkripsi dan protokol keamanan untuk melindungi data Anda dari akses tidak sah.</p>
      </div>

      <div class="policy-section">
        <h2>4. Pembagian Data</h2>
        <p>Data Anda TIDAK akan dibagikan atau dijual kepada pihak ketiga, kecuali:</p>
        <ul>
          <li>Diperlukan untuk memproses transaksi</li>
          <li>Diperintahkan oleh sekolah atau hukum</li>
          <li>Anda memberikan persetujuan eksplisit</li>
        </ul>
      </div>

      <div class="policy-section">
        <h2>5. Hak Anda</h2>
        <ul>
          <li>Akses data pribadi Anda</li>
          <li>Meminta koreksi data yang salah</li>
          <li>Meminta penghapusan akun dan data</li>
          <li>Export data Anda</li>
        </ul>
        <p style="margin-top: 15px;">Hubungi admin@temanpreneur.sch.id untuk mengajukan permintaan.</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PolicyPrivacyPage',
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