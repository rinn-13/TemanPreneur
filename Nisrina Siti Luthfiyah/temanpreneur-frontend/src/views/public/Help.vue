<template>
  <div class="help-page">
    <div class="help-header">
      <h1>Pusat Bantuan & FAQ</h1>
      <p>Temukan jawaban atas pertanyaan umum tentang TemanPreneur</p>
    </div>

    <div class="help-content">
      <div class="help-section">
        <h2> Pembelian & Belanja</h2>
        <div class="faq-item">
          <h3>Bagaimana cara membeli produk?</h3>
          <p>Jelajahi katalog, klik produk yang diinginkan, lalu tambahkan ke keranjang dan lakukan checkout.</p>
        </div>
        <div class="faq-item">
          <h3>Apakah aman berbelanja di sini?</h3>
          <p>Ya, semua transaksi dilakukan antar siswa di lingkungan sekolah dengan pengawasan admin.</p>
        </div>
      </div>

      <div class="help-section">
        <h2> Penjualan & Toko</h2>
        <div class="faq-item">
          <h3>Bagaimana cara membuka toko?</h3>
          <p>Daftar sebagai penjual, lengkapi profil toko, dan mulai tambahkan produk Anda.</p>
        </div>
        <div class="faq-item">
          <h3>Apakah ada biaya untuk berjualan?</h3>
          <p>Tidak ada biaya untuk penjualan dasar. Upgrade ke Premium untuk fitur tambahan.</p>
        </div>
      </div>

      <div class="help-section">
        <h2> Kontak Kami</h2>
        <p>Jika pertanyaan Anda tidak terjawab, hubungi admin melalui email atau chat.</p>
        <p><strong>Email:</strong> admin@temanpreneur.sch.id</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HelpPage',
}
</script>

<style scoped>
.help-page {
  max-width: 800px;
  margin: 40px auto;
  padding: 0 20px;
  font-family: 'Plus Jakarta Sans', sans-serif;
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
  gap: 30px;
}
.help-section h2 {
  font-size: 1.5rem;
  color: #374151;
  margin-bottom: 20px;
}
.faq-item {
  margin-bottom: 20px;
}
.faq-item h3 {
  font-size: 1.1rem;
  color: #111827;
  margin-bottom: 8px;
}
.faq-item p {
  color: #6b7280;
  line-height: 1.6;
}
</style>