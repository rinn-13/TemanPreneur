<template>
  <div class="help-page">
    <button class="back-btn" @click="$router.back()">← Kembali</button>
    
    <div class="help-header">
      <h1>Cara Berjualan</h1>
      <p>Panduan lengkap untuk memulai berjualan di TemanPreneur</p>
    </div>

    <div class="help-content">
      <div class="step">
        <h3>1. Daftar sebagai Penjual</h3>
        <p>Buat akun, lalu klik "Buka Toko" atau "Daftar sebagai Penjual" di menu profil Anda.</p>
      </div>
      <div class="step">
        <h3>2. Lengkapi Profil Toko</h3>
        <p>Tambahkan nama toko, deskripsi, kontak, dan foto profil untuk menarik pembeli.</p>
      </div>
      <div class="step">
        <h3>3. Tambahkan Produk</h3>
        <p>Masuk ke Dashboard Toko → Kelola Produk → Tambah Produk. Isi detail, harga, dan foto produk.</p>
      </div>
      <div class="step">
        <h3>4. Kelola Pesanan</h3>
        <p>Setiap ada pembelian, Anda akan menerima notifikasi. Proses pesanan dengan cepat untuk rating yang bagus.</p>
      </div>
      <div class="step">
        <h3>5. Upgrade ke Premium (Opsional)</h3>
        <p>Dapatkan fitur ekstra seperti analytics, promosi, dan badge premium untuk meningkatkan penjualan Anda.</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HelpSellPage',
}
</script>

<style scoped>
.help-page {
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
.help-header {
  text-align: center;
  margin-bottom: 40px;
}
.help-header h1 {
  font-size: 2.5rem;
  color: #111827;
  margin-bottom: 10px;
}
.help-header p {
  font-size: 1.1rem;
  color: #6b7280;
}
.help-content {
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.step {
  padding: 20px;
  border: 2px solid #fecaca;
  border-left: 5px solid #e53e3e;
  border-radius: 8px;
  background: #fffbfb;
}
.step h3 {
  font-size: 1.1rem;
  color: #111827;
  margin-bottom: 10px;
}
.step p {
  color: #6b7280;
  line-height: 1.6;
}
</style>