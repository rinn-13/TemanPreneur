<template>
  <div class="help-page">
    <button class="back-btn" @click="$router.back()">← Kembali</button>
    
    <div class="help-header">
      <h1>Hubungi Admin</h1>
      <p>Dapatkan bantuan langsung dari tim kami</p>
    </div>

    <div class="help-content">
      <div class="contact-section">
        <h3> Email</h3>
        <p>Kirim pertanyaan atau laporan ke:</p>
        <p class="contact-value">admin@temanpreneur.sch.id</p>
      </div>

      <div class="contact-section">
        <h3>Chat</h3>

        <p>
          Ingin bantuan cepat? Gunakan fitur chat di aplikasi untuk berbicara langsung dengan tim support kami.
        </p>

        <a
          href="https://wa.me/6281234567890?text=Halo%20admin"
          target="_blank"
          rel="noopener"
          class="btn-chat"
        >
          Buka Chat
        </a>
      </div>

      <div class="contact-section">
        <h3> Jam Operasional</h3>
        <p>
          Senin - Jumat: 08:00 - 16:00<br>
          Sabtu: 08:00 - 12:00<br>
          Minggu & Libur: Tutup
        </p>
      </div>

      <div class="contact-section">
        <h3> Laporan Masalah</h3>
        <p>Jika menemukan bug atau masalah teknis, silakan laporkan dengan detail agar tim kami dapat membantu dengan cepat.</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HelpContactPage',
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
.contact-section {
  padding: 20px;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #fff;
}
.contact-section h3 {
  font-size: 1.2rem;
  color: #111827;
  margin-bottom: 10px;
}
.contact-section p {
  color: #6b7280;
  line-height: 1.6;
  margin-bottom: 8px;
}
.contact-value {
  font-weight: 700;
  color: #e53e3e;
  font-size: 1.1rem;
}
.btn-chat {
  display: inline-block;
  padding: 10px 20px;
  background: #e53e3e;
  color: #fff;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 700;
  transition: background 0.2s;
  margin-top: 10px;
}
.btn-chat:hover {
  background: #c53030;
}
</style>