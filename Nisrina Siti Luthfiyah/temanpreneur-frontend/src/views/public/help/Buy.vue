<template>
  <div class="help-page">
    <button class="back-btn" @click="$router.back()">← Kembali</button>
    
    <div class="help-header">
      <h1>Cara Berbelanja</h1>
      <p>Panduan lengkap untuk berbelanja di TemanPreneur</p>
    </div>

    <div class="help-content">
      <div class="step">
        <h3>1. Daftar & Login</h3>
        <p>Buat akun dengan email sekolah Anda, lalu login untuk mulai berbelanja.</p>
      </div>
      <div class="step">
        <h3>2. Jelajahi Katalog</h3>
        <p>Klik "Katalog" di navbar untuk melihat semua produk. Gunakan filter kategori atau cari produk spesifik.</p>
      </div>
      <div class="step">
        <h3>3. Lihat Detail Produk</h3>
        <p>Klik produk untuk melihat foto, harga, deskripsi, rating, dan ulasan dari pembeli lain.</p>
      </div>
      <div class="step">
        <h3>4. Tambah ke Keranjang</h3>
        <p>Pilih jumlah yang ingin dibeli, lalu klik "Tambah ke Keranjang".</p>
      </div>
      <div class="step">
        <h3>5. Checkout</h3>
        <p>Buka keranjang, review pembelian Anda, pilih metode pembayaran, lalu checkout.</p>
      </div>
      <div class="step">
        <h3>6. Tunggu & Terima</h3>
        <p>Penjual akan memproses dan mengirim barang. Terima barang, cek kondisi, lalu konfirmasi pembelian.</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HelpBuyPage',
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
  border: 2px solid #d1fae5;
  border-left: 5px solid #10b981;
  border-radius: 8px;
  background: #f0fdf4;
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