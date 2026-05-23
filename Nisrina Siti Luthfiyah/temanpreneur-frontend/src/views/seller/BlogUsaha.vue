<template>
  <div class="seller-blog-page">
    <div class="sb-header">
      <h1>Blog Toko</h1>
      <p>Kelola artikel dan cerita sukses tokomu</p>
      <button class="sb-new-btn" @click="showCreate = true">
        ️ Tulis Artikel Baru
      </button>
    </div>

    <!-- Blog list -->
    <div class="sb-list">
      <div v-if="loading" class="sb-skeleton">
        <div v-for="n in 4" :key="n" class="sb-skeleton-item"></div>
      </div>
      <div v-else-if="!sellerBlogs.length" class="sb-empty">
        <span></span>
        <p>Belum ada artikel blog</p>
        <button @click="showCreate = true" class="sb-btn sb-btn--primary">Buat Artikel Pertama</button>
      </div>
      <div v-else class="sb-grid">
        <div v-for="blog in sellerBlogs" :key="blog.id" class="sb-card">
          <div class="sb-card-header">
            <div class="sb-author">
              <div class="sb-avatar">{{ business?.name[0] }}</div>
              <span>{{ business?.name }}</span>
            </div>
            <div class="sb-actions">
              <button @click="editBlog(blog)" class="sb-action-btn">️</button>
              <button @click="deleteBlog(blog.id)" class="sb-action-btn sb-danger">️</button>
            </div>
          </div>
          <h3 class="sb-title">{{ blog.title }}</h3>
          <p class="sb-excerpt">{{ blog.excerpt }}</p>
          <div class="sb-meta">
            <span class="sb-date">{{ new Date(blog.created_at).toLocaleDateString('id-ID') }}</span>
            <router-link :to="`/blog/${blog.slug}`" class="sb-view">Lihat Publik</router-link>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="!loading && totalPages > 1" class="sb-pagination">
        <button :disabled="page === 1" @click="page--" class="sb-pag-btn">←</button>
        <span>{{ page }} / {{ totalPages }}</span>
        <button :disabled="page === totalPages" @click="page++" class="sb-pag-btn">→</button>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreate" class="sb-modal" @click.self="showCreate = false">
      <div class="sb-modal-content">
        <h2>{{ editingBlog ? 'Edit Artikel' : 'Tulis Artikel Baru' }}</h2>
        <form @submit.prevent="saveBlog">
          <div class="sb-form-group">
            <label>Judul</label>
            <input v-model="form.title" required />
          </div>
          <div class="sb-form-group">
            <label>Excerpt (Ringkasan)</label>
            <textarea v-model="form.excerpt" rows="3"></textarea>
          </div>
          <div class="sb-form-group">
            <label>Isi Artikel</label>
            <textarea v-model="form.content" rows="10" required></textarea>
          </div>
          <div class="sb-form-group">
            <label>Gambar (Opsional)</label>
            <input type="file" @change="handleImage" accept="image/*" />
          </div>
          <div class="sb-form-actions">
            <button type="button" @click="showCreate = false" class="sb-btn sb-btn--outline">Batal</button>
            <button type="submit" class="sb-btn sb-btn--primary">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { blogService } from '@/services/blog.js'
import businessService from '@/services/business.js'

const router = useRouter()
const loading = ref(true)
const sellerBlogs = ref([])
const page = ref(1)
const totalPages = ref(1)
const business = ref(null)
const showCreate = ref(false)
const editingBlog = ref(null)
const form = ref({
  title: '',
  excerpt: '',
  content: '',
  image: null
})

onMounted(async () => {
  await loadData()
})

const loadData = async () => {
  loading.value = true
  const [businessResult, blogResult] = await Promise.all([
    businessService.getMyBusiness(),
    blogService.getSellerBlogs({ page: page.value }),
  ])

  business.value = businessResult.data || null
  sellerBlogs.value = blogResult.data?.data || []
  totalPages.value = blogResult.data?.last_page || 1
  loading.value = false
}

const handleImage = (e) => {
  form.value.image = e.target.files[0]
}

const saveBlog = async () => {
  try {
    const data = new FormData()
    data.append('title', form.value.title)
    data.append('excerpt', form.value.excerpt)
    data.append('content', form.value.content)
    if (form.value.image) data.append('image', form.value.image)

    let result
    if (editingBlog.value) {
      result = await blogService.updateBlog(editingBlog.value.id, data)
    } else {
      result = await blogService.createBlog(data)
    }

    if (result.success) {
      showCreate.value = false
      editingBlog.value = null
      form.value = { title: '', excerpt: '', content: '', image: null }
      await loadData()
    }
  } catch (error) {
    const message = error.response?.data?.message || error.message || 'Gagal menyimpan blog'
    alert(message)
  }
}

const editBlog = (blog) => {
  editingBlog.value = blog
  form.value = {
    title: blog.title,
    excerpt: blog.excerpt || '',
    content: blog.content,
    image: null
  }
  showCreate.value = true
}

const deleteBlog = async (id) => {
  if (confirm('Hapus artikel ini?')) {
    const result = await blogService.deleteBlog(id)
    if (result.success) {
      await loadData()
    }
  }
}

watch(page, loadData)
</script>

<style scoped>
.seller-blog-page {
  padding: 24px;
  max-width: 1200px;
  margin: 0 auto;
}

.sb-header {
  text-align: center;
  margin-bottom: 32px;
}

.sb-header h1 {
  font-size: 2rem;
  font-weight: 800;
  color: #111827;
  margin-bottom: 8px;
}

.sb-header p {
  color: #6b7280;
  margin-bottom: 24px;
}

.sb-new-btn {
  background: #e53e3e;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 12px;
  font-weight: 700;
  cursor: pointer;
}

.sb-list {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.sb-skeleton-item {
  height: 120px;
  background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
  border-radius: 12px;
  animation: shimmer 1.5s infinite;
}

.sb-empty {
  text-align: center;
  padding: 48px 24px;
  color: #6b7280;
}

.sb-empty span {
  font-size: 4rem;
  display: block;
  margin-bottom: 16px;
}

.sb-grid {
  display: grid;
  gap: 20px;
}

.sb-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
  transition: box-shadow 0.2s;
}

.sb-card:hover {
  box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.sb-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.sb-author {
  display: flex;
  align-items: center;
  gap: 12px;
}

.sb-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #e53e3e, #dc2626);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
}

.sb-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 8px;
}

.sb-excerpt {
  color: #6b7280;
  line-height: 1.6;
  margin-bottom: 16px;
}

.sb-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.875rem;
  color: #9ca3af;
}

.sb-view {
  color: #e53e3e;
  font-weight: 600;
  text-decoration: none;
}

.sb-actions {
  display: flex;
  gap: 8px;
}

.sb-action-btn {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  transition: background 0.2s;
}

.sb-action-btn:hover {
  background: #f3f4f6;
}

.sb-danger:hover {
  background: #fef2f2;
  color: #dc2626;
}

.sb-pagination {
  display: flex;
  justify-content: center;
  gap: 12px;
  margin-top: 32px;
  padding-top: 24px;
  border-top: 1px solid #e5e7eb;
}

.sb-pag-btn {
  background: white;
  border: 1.5px solid #d1d5db;
  border-radius: 8px;
  padding: 8px 12px;
  cursor: pointer;
}

.sb-pag-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.sb-modal {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

.sb-modal-content {
  background: white;
  border-radius: 16px;
  padding: 32px;
  max-width: 600px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.sb-form-group {
  margin-bottom: 24px;
}

.sb-form-group label {
  display: block;
  font-weight: 600;
  color: #374151;
  margin-bottom: 8px;
}

.sb-form-group input,
.sb-form-group textarea {
  width: 100%;
  padding: 12px;
  border: 1.5px solid #d1d5db;
  border-radius: 8px;
  font-family: inherit;
}

.sb-form-group textarea {
  resize: vertical;
  min-height: 120px;
}

.sb-form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.sb-btn {
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.sb-btn--primary {
  background: #e53e3e;
  color: white;
}

.sb-btn--primary:hover {
  background: #dc2626;
}

.sb-btn--outline {
  background: white;
  color: #374151;
  border: 1.5px solid #d1d5db;
}

.sb-btn--outline:hover {
  border-color: #e53e3e;
  color: #e53e3e;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

@media (max-width: 768px) {
  .sb-grid {
    grid-template-columns: 1fr;
  }
  
  .sb-card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .sb-actions {
    align-self: flex-end;
  }
}
</style>