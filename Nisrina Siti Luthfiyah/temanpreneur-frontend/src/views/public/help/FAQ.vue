<template>
  <div class="help-page">
    <button class="back-btn" @click="$router.back()">← Kembali</button>
    
    <div class="help-header">
      <h1>Pusat Bantuan & FAQ</h1>
      <p>Jawaban cepat untuk pertanyaan umum</p>
    </div>

    <div class="help-content">
      <div class="faq-item">
        <h3>Apa itu TemanPreneur?</h3>
        <p>TemanPreneur adalah platform marketplace internal sekolah yang memungkinkan siswa untuk berjualan dan membeli produk sesama teman sekolah.</p>
      </div>
      <div class="faq-item">
        <h3>Bagaimana cara mendaftar?</h3>
        <p>Klik tombol "Daftar Gratis" di navbar, isi data pribadi, dan pilih peran Anda sebagai pembeli atau penjual.</p>
      </div>
      <div class="faq-item">
        <h3>Apakah gratis?</h3>
        <p>Ya, gratis untuk semua pengguna. Anda hanya membayar saat membeli produk dari penjual lain.</p>
      </div>
      <div class="faq-item">
        <h3>Bagaimana jika ada masalah transaksi?</h3>
        <p>Hubungi admin melalui email admin@temanpreneur.sch.id atau gunakan fitur chat untuk bantuan.</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HelpFAQPage',
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
.faq-item {
  padding: 20px;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #fff;
}
.faq-item h3 {
  font-size: 1.1rem;
  color: #111827;
  margin-bottom: 10px;
}
.faq-item p {
  color: #6b7280;
  line-height: 1.6;
}
</style>