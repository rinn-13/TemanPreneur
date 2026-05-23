<template>
  <div class="policy-page">
    <button class="back-btn" @click="$router.back()">← Kembali</button>
    
    <div class="policy-header">
      <h1>Kebijakan Pengembalian</h1>
      <p>Aturan dan proses pengembalian produk</p>
    </div>

    <div class="policy-content">
      <div class="policy-section">
        <h2>1. Jangka Waktu Pengembalian</h2>
        <ul>
          <li>Produk dapat dikembalikan dalam 7 hari setelah diterima</li>
          <li>Pengembalian harus dikommunikasikan ke penjual dalam 24 jam pertama</li>
          <li>Setelah 7 hari, tidak ada pengembalian kecuali produk rusak/cacat pabrik</li>
        </ul>
      </div>

      <div class="policy-section">
        <h2>2. Syarat Pengembalian</h2>
        <ul>
          <li>Produk dalam kondisi asli (tidak rusak karena penggunaan pembeli)</li>
          <li>Kemasan masih layak dan barang tidak hilang bagiannya</li>
          <li>Jika ada barang bawaan (tas, kotak), harus kembalikan lengkap</li>
          <li>Untuk fashion: belum pernah dipakai atau hanya dicoba ukuran</li>
        </ul>
      </div>

      <div class="policy-section">
        <h2>3. Alasan Pengembalian yang Diterima</h2>
        <ul>
          <li>Produk rusak atau cacat bawaan</li>
          <li>Produk tidak sesuai dengan deskripsi</li>
          <li>Barang tidak sampai atau hilang</li>
          <li>Salah pengiriman (barang yang diterima bukan pesanan)</li>
        </ul>
      </div>

      <div class="policy-section">
        <h2>4. Proses Pengembalian</h2>
        <ol style="margin-left: 20px;">
          <li>Hubungi penjual dan jelaskan alasan pengembalian</li>
          <li>Penjual dapat menerima atau menolak permintaan Anda</li>
          <li>Jika penjual setuju, kirimkan barang kembali dalam kondisi baik</li>
          <li>Penjual akan mengembalikan dana Anda setelah menerima barang</li>
          <li>Jika ada perselisihan, admin akan membantu mediasi</li>
        </ol>
      </div>

      <div class="policy-section">
        <h2>5. Biaya Pengiriman</h2>
        <ul>
          <li>Untuk pengembalian karena kesalahan penjual: penjual yang bayar</li>
          <li>Untuk pengembalian karena pilihan pembeli: pembeli yang bayar</li>
          <li>Untuk barang rusak/hilang: tergantung kasus dan mediasi admin</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PolicyReturnPage',
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
.policy-section ol,
.policy-section ul {
  margin-left: 20px;
}
.policy-section li {
  color: #6b7280;
  line-height: 1.8;
  margin-bottom: 10px;
}
</style>