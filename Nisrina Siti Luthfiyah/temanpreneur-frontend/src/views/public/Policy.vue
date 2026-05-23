<template>
  <div class="policy-page">
    <div class="policy-header">
      <h1>Kebijakan & Syarat Penggunaan</h1>
      <p>Aturan dan ketentuan penggunaan platform TemanPreneur</p>
    </div>

    <div class="policy-content">
      <div class="policy-section">
        <h2>1. Syarat & Ketentuan</h2>
        <p>Dengan menggunakan TemanPreneur, Anda setuju untuk mematuhi aturan berikut:</p>
        <ul>
          <li>Hanya siswa sekolah yang boleh menggunakan platform ini</li>
          <li>Produk yang dijual harus sesuai dengan nilai-nilai sekolah</li>
          <li>Dilarang menjual barang ilegal atau berbahaya</li>
          <li>Transaksi dilakukan dengan tanggung jawab pribadi</li>
        </ul>
      </div>

      <div class="policy-section">
        <h2>2. Kebijakan Privasi</h2>
        <p>Kami menghormati privasi Anda:</p>
        <ul>
          <li>Data pribadi hanya digunakan untuk keperluan platform</li>
          <li>Tidak ada data yang dijual ke pihak ketiga</li>
          <li>Anda dapat meminta penghapusan data kapan saja</li>
          <li>Komunikasi dilakukan secara aman</li>
        </ul>
      </div>

      <div class="policy-section">
        <h2>3. Kebijakan Pengembalian</h2>
        <p>Untuk pengembalian produk:</p>
        <ul>
          <li>Hubungi penjual dalam 24 jam setelah menerima barang</li>
          <li>Barang harus dalam kondisi asli</li>
          <li>Pengembalian diputuskan oleh penjual</li>
          <li>Admin dapat turut campur jika ada perselisihan</li>
        </ul>
      </div>

      <div class="policy-section">
        <h2>4. Panduan Komunitas</h2>
        <p>Untuk menjaga lingkungan yang positif:</p>
        <ul>
          <li>Bersikap sopan dan menghormati sesama</li>
          <li>Dilarang melakukan penipuan atau kecurangan</li>
          <li>Laporkan pelanggaran ke admin</li>
          <li>Promosikan produk dengan jujur</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PolicyPage',
}
</script>

<style scoped>
.policy-page {
  max-width: 800px;
  margin: 40px auto;
  padding: 0 20px;
  font-family: 'Plus Jakarta Sans', sans-serif;
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
  line-height: 1.6;
  margin-bottom: 8px;
}
</style>