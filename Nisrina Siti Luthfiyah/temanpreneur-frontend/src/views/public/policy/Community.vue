<template>
  <div class="policy-page">
    <button class="back-btn" @click="$router.back()">← Kembali</button>
    
    <div class="policy-header">
      <h1>Panduan Komunitas</h1>
      <p>Aturan etika untuk menjaga lingkungan yang sehat dan positif</p>
    </div>

    <div class="policy-content">
      <div class="policy-section">
        <h2>1. Etika Berkomunikasi</h2>
        <ul>
          <li>Gunakan bahasa yang sopan dan menghormati</li>
          <li>Jangan menggunakan kata kasar atau merendahkan</li>
          <li>Hormati perbedaan pendapat</li>
          <li>Respons cepat terhadap komunikasi pembeli/penjual</li>
        </ul>
      </div>

      <div class="policy-section">
        <h2>2. Integritas Penjualan</h2>
        <ul>
          <li>Jelaskan produk dengan jujur dan akurat</li>
          <li>Foto harus menampilkan kondisi sebenarnya</li>
          <li>Tidak boleh menunggangi hype atau tipuan marketing</li>
          <li>Jika ada kerusakan/kecatatan, sebutkan di deskripsi</li>
        </ul>
      </div>

      <div class="policy-section">
        <h2>3. Rating & Review yang Jujur</h2>
        <ul>
          <li>Berikan rating sesuai pengalaman Anda yang sebenarnya</li>
          <li>Review harus membangun, bukan menyerang pribadi</li>
          <li>Sertakan bukti jika ada keluhan (foto, chat, dll)</li>
          <li>Tidak boleh memaksa pembeli/penjual untuk rating tertentu</li>
        </ul>
      </div>

      <div class="policy-section">
        <h2>4. Konten yang Dilarang</h2>
        <ul>
          <li>Spam, link mencurigakan, atau promosi ke luar platform</li>
          <li>Konten NSFW atau tidak pantas untuk lingkungan sekolah</li>
          <li>Bullying, harassment, atau ancaman</li>
          <li>Phishing atau trik untuk mendapat data pribadi</li>
          <li>Tanda tangan atau watermark produk orang lain</li>
        </ul>
      </div>

      <div class="policy-section">
        <h2>5. Pelaporan Pelanggaran</h2>
        <ul>
          <li>Laporkan user atau produk yang melanggar ke admin</li>
          <li>Sertakan bukti atau deskripsi jelas tentang pelanggaran</li>
          <li>Admin akan menyelidiki dan mengambil tindakan</li>
          <li>Jangan melakukan "balas dendam" sendiri</li>
        </ul>
      </div>

      <div class="policy-section">
        <h2>6. Hukuman Pelanggaran Komunitas</h2>
        <ul>
          <li><strong>Ringan:</strong> Peringatan atau penghapusan konten</li>
          <li><strong>Sedang:</strong> Suspend akun sementara (1-7 hari)</li>
          <li><strong>Berat:</strong> Suspend permanen dan laporan ke sekolah</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PolicyCommunityPage',
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