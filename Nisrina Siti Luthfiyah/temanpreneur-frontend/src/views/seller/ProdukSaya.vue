<template>
  <section class="sp">
    <PageHeader title="Produk Saya" subtitle="Kelola daftar produk yang kamu jual" showBack>
      <template #actions>
      </template>
    </PageHeader>

    <!-- Alert: Premium limit or Not Verified -->
    <div v-if="businessNotVerified" class="alert alert-warning">
      ️ Toko Anda belum diverifikasi. Hubungi admin untuk diverifikasi terlebih dahulu.
    </div>
    <div v-if="productLimitReached && !isPremium" class="alert alert-info">
      ℹ️ Anda sudah mencapai batas {{ maxProducts === Infinity ? 'tanpa batas' : maxProducts }} produk. Upgrade ke Premium untuk menambah produk tanpa batas.
    </div>

    <div class="sp__card">
      <div class="sp__toolbar">
        <select v-model="filterCategory" class="sp__select">
          <option value="">Semua Kategori</option>
          <option v-for="category in categories" :key="category.id" :value="category.id">
            {{ category.name }}
          </option>
        </select>

        <select v-model="sortBy" class="sp__select">
          <option value="newest">Terbaru</option>
          <option value="oldest">Terlama</option>
          <option value="price_low">Harga Terendah</option>
          <option value="price_high">Harga Tertinggi</option>
          <option value="stock_low">Stok Terendah</option>
          <option value="stock_high">Stok Tertinggi</option>
        </select>

        <input 
          v-model="searchQuery" 
          class="sp__search" 
          placeholder="Cari produk..."
        />
        <button 
          class="sp__btn" 
          @click="showModal = true"
          :disabled="businessNotVerified || productLimitReached"
        >
          + Tambah Produk
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="sp__loading">
        <p>Memuat produk...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredProducts.length === 0" class="sp__empty">
        <p>Belum ada produk. Mulai dengan membuat produk baru!</p>
      </div>

      <!-- Products Table -->
      <div v-else class="sp__table">
        <div class="sp__th">
          <span>Produk</span><span>Stok</span><span>Harga</span><span>Terjual</span><span>Aksi</span>
        </div>
        <div class="sp__row" v-for="p in filteredProducts" :key="p.id">
          <div class="sp__prod">
            <img
                v-if="p.image"
                :src="normalizeImageUrl(p.image, '/placeholder-product.png')"
                class="sp__thumb"
              />
            <div v-else class="sp__thumb sp__thumb--empty"></div>
            <div>
              <div class="sp__name">{{ p.name }}</div>
              <div class="sp__meta">ID: {{ p.id }}<span v-if="p.category"> · {{ p.category && p.category.name ? p.category.name : p.category }}</span></div>
            </div>
          </div>
          <span class="sp__stock" :class="p.stock < 5 ? 'sp__stock--low' : ''">{{ p.stock }}</span>
          <span class="sp__price">Rp {{ p.price.toLocaleString('id-ID') }}</span>
          <span>{{ getSoldCount(p) }}</span>
          <div class="sp__actions">
            <button class="sp__action-btn sp__action-btn--edit" @click="editProduct(p)" title="Edit" type="button" aria-label="Edit">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button class="sp__action-btn sp__action-btn--delete" @click="deleteProduct(p.id)" title="Hapus" type="button" aria-label="Hapus">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Add/Edit Product -->
    <div v-if="showModal" class="modal" @click.self="closeModal">
      <div class="modal__content">
        <div class="modal__header">
          <h2>{{ editingProduct?.id ? 'Edit Produk' : 'Tambah Produk Baru' }}</h2>
          <button class="modal__close" @click="closeModal">×</button>
        </div>

        <form @submit.prevent="saveProduct" class="modal__form">
          <div class="form__group">
            <label>Nama Produk *</label>
            <input 
              v-model="form.name" 
              type="text" 
              placeholder="Contoh: Kue Brownies..." 
              required
            />
          </div>

          <div class="form__group">
            <label>Deskripsi</label>
            <textarea 
              v-model="form.description" 
              placeholder="Deskripsikan produk Anda..."
              rows="4"
            ></textarea>
          </div>

          <div class="form__group">
            <label>Kategori Produk *</label>
            <select v-model.number="form.category_id" required>
              <option value="" disabled>Pilih kategori</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>

          <div class="form__row">
            <div class="form__group">
              <label>Harga *</label>
              <input 
                v-model.number="form.price" 
                type="number" 
                placeholder="0"
                min="0"
                required
              />
            </div>
            <div class="form__group">
              <label>Stok *</label>
              <input 
                v-model.number="form.stock" 
                type="number" 
                placeholder="0"
                min="0"
                required
              />
            </div>
          </div>

          <div class="form__group">
            <label>{{ editingProduct ? 'Tambah / lihat gambar' : 'Gambar Produk *' }}</label>
            <input 
              @change="onImagesSelected"
              type="file" 
              accept="image/*"
              multiple
              class="form__file"
            />
            <small>Maksimal 5 gambar total per produk. Foto lama tetap tersimpan saat Anda menambah foto baru.</small>
            <div v-if="previewImages.length" class="form__previews">
              <div v-for="(preview, index) in previewImages" :key="index" class="form__preview-item">
                <img :src="preview" class="form__preview" />
                <button type="button" @click="removeImage(index)" class="form__preview-remove">×</button>
              </div>
            </div>
          </div>

          <div class="form__actions">
            <button type="button" class="form__btn form__btn--cancel" @click="closeModal">Batal</button>
            <button type="submit" class="form__btn form__btn--submit">
              {{ editingProduct?.id ? 'Simpan Perubahan' : 'Tambah Produk' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </section>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import api from '@/api/axios'
import { normalizeImageUrl } from '@/utils/image'

export default {
  name: 'SellerProdukSaya',
  setup() {
    const products = ref([])
    const searchQuery = ref('')
    const filterCategory = ref('')
    const sortBy = ref('newest')
    const loading = ref(false)
    const showModal = ref(false)
    const editingProduct = ref(null)
    const businessNotVerified = ref(false)
    const isPremium = ref(false)
    const categories = ref([])
    const maxProducts = computed(() => isPremium.value ? Infinity : 2)
    const productLimitReached = computed(() => !isPremium.value && products.value.length >= maxProducts.value)

    const form = ref({
      name: '',
      description: '',
      price: 0,
      stock: 0,
      category_id: null,
      images: [], // Multiple images
    })
    const previewImages = ref([])

    const filteredProducts = computed(() => {
      let list = [...products.value]

      if (filterCategory.value) {
        list = list.filter((p) => {
          const category = p.category || p.category_id || null
          const categoryId = typeof category === 'object' ? category.id : category
          const categorySlug = typeof category === 'object' ? category.slug : category
          return String(categoryId) === String(filterCategory.value) || String(categorySlug) === String(filterCategory.value)
        })
      }

      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        list = list.filter((p) => {
          return (
            String(p.name || '').toLowerCase().includes(query) ||
            String(p.description || '').toLowerCase().includes(query) ||
            String(p.category?.name || p.category || '').toLowerCase().includes(query)
          )
        })
      }

      if (sortBy.value === 'price_low') {
        list.sort((a, b) => (Number(a.price) || 0) - (Number(b.price) || 0))
      } else if (sortBy.value === 'price_high') {
        list.sort((a, b) => (Number(b.price) || 0) - (Number(a.price) || 0))
      } else if (sortBy.value === 'stock_low') {
        list.sort((a, b) => (Number(a.stock) || 0) - (Number(b.stock) || 0))
      } else if (sortBy.value === 'stock_high') {
        list.sort((a, b) => (Number(b.stock) || 0) - (Number(a.stock) || 0))
      } else if (sortBy.value === 'oldest') {
        list.sort((a, b) => {
          const dateA = a.created_at ? new Date(a.created_at).getTime() : 0
          const dateB = b.created_at ? new Date(b.created_at).getTime() : 0
          return dateA - dateB
        })
      } else {
        list.sort((a, b) => {
          const dateA = a.created_at ? new Date(a.created_at).getTime() : 0
          const dateB = b.created_at ? new Date(b.created_at).getTime() : 0
          return dateB - dateA
        })
      }

      return list
    })

    const loadCategories = async () => {
      try {
        const response = await api.get('/categories')
        // API returns { data: [...] } — normalize to array
        categories.value = response.data?.data ?? response.data ?? []
      } catch (err) {
        console.error('Load categories error:', err)
        categories.value = []
      }
    }

    const fetchProducts = async () => {
      loading.value = true
      try {
        // === Get seller stats untuk premium dan verification status ===
        const statsResponse = await api.get('/seller/stats')
        const statsData = statsResponse?.data || {}

        // === Safe property assignment ===
        isPremium.value = Boolean(statsData?.isPremium)
        businessNotVerified.value = (
          statsData?.businessStatus !== 'approved' && 
          statsData?.businessStatus !== null
        )

        // === Fetch products dengan validasi ===
        const productsResponse = await api.get('/seller/products')
        const responseData = productsResponse?.data

        // === Smart data extraction ===
        let productsList = []
        if (Array.isArray(responseData?.data)) {
          productsList = responseData.data
        } else if (Array.isArray(responseData)) {
          productsList = responseData
        }

        products.value = productsList
      } catch (err) {
        console.error('Fetch products error:', err)

        // === Status code-specific error handling ===
        const statusCode = err.response?.status
        const serverMessage = err.response?.data?.message

        let errorMsg = ''
        if (statusCode === 401) {
          errorMsg = '️ Sesi Anda telah berakhir. Silakan login kembali.'
        } else if (statusCode === 403) {
          errorMsg = '️ Anda tidak memiliki akses untuk melihat produk ini.'
        } else if (statusCode === 404) {
          errorMsg = '️ Anda belum memiliki usaha. Silakan buat usaha terlebih dahulu.'
          businessNotVerified.value = true
        } else if (statusCode === 500) {
          errorMsg = '️ Terjadi kesalahan server. Silakan hubungi administrator.'
        } else if (serverMessage) {
          errorMsg = `️ ${serverMessage}`
        } else if (err.message) {
          errorMsg = `️ ${err.message}`
        } else {
          errorMsg = '️ Gagal memuat produk. Silakan coba lagi.'
        }

        alert(errorMsg)

        // === Fallback values untuk mencegah UI crash ===
        products.value = []
        isPremium.value = false
        businessNotVerified.value = true
      } finally {
        loading.value = false
      }
    }

    const existingGalleryCount = computed(() => {
      if (!editingProduct.value?.id) return 0
      const p = editingProduct.value
      if (p.images && Array.isArray(p.images) && p.images.length) return p.images.length
      if (p.image) return 1
      return 0
    })

    const onImagesSelected = (e) => {
      const picked = Array.from(e.target.files)
      const maxNew = editingProduct.value
        ? Math.max(0, 5 - existingGalleryCount.value - form.value.images.length)
        : Math.max(0, 5 - form.value.images.length)
      if (picked.length > maxNew) {
        alert(`Anda hanya dapat menambah ${maxNew} gambar lagi (maks. 5 total)`)
      }
      const files = picked.slice(0, maxNew)
      e.target.value = ''

      form.value.images.push(...files)

      files.forEach(file => {
        const reader = new FileReader()
        reader.onload = (event) => {
          previewImages.value.push(event.target.result)
        }
        reader.readAsDataURL(file)
      })
    }

    const removeImage = (index) => {
      const existingSlots = editingProduct.value ? existingGalleryCount.value : 0
      if (index < existingSlots) {
        alert('Foto yang sudah tersimpan tidak dapat dihapus dari sini. Hubungi admin jika perlu.')
        return
      }
      const fileIndex = index - existingSlots
      form.value.images.splice(fileIndex, 1)
      previewImages.value.splice(index, 1)
    }

    const editProduct = (product) => {
      editingProduct.value = product
      // Determine category_id from direct response values or category object/slug
      let categoryId = product.category_id ?? null
      if (!categoryId && product.category_slug) {
        const found = categories.value.find(c => c.id === product.category_slug || c.slug === product.category_slug || c.name === product.category_slug)
        categoryId = found ? found.id : null
      }
      if (!categoryId && product.category) {
        if (typeof product.category === 'object' && product.category.id) {
          categoryId = product.category.id
        } else if (Array.isArray(categories.value) && categories.value.length) {
          const found = categories.value.find(c => c.id === product.category?.id || c.slug === product.category || c.name === product.category)
          categoryId = found ? found.id : categoryId
        }
      }

      form.value = {
        name: product.name,
        description: product.description || '',
        price: product.price,
        stock: product.stock,
        category_id: categoryId,
        images: [],
      }
      // Handle existing images
      previewImages.value = []
      if (product.images && Array.isArray(product.images)) {
        product.images.forEach(imagePath => {
          previewImages.value.push(normalizeImageUrl(imagePath, '/placeholder-product.png'))
        })
      } else if (product.image) {
        previewImages.value.push(normalizeImageUrl(product.image, '/placeholder-product.png'))
      }
      showModal.value = true
    }

    const closeModal = () => {
      showModal.value = false
      editingProduct.value = null
      form.value = {
        name: '',
        description: '',
        price: 0,
        stock: 0,
        category_id: null,
        images: [],
      }
      previewImages.value = []
    }

    const getErrorMessage = (err) => {
      if (err.response?.data?.message) {
        return err.response.data.message
      }
      if (err.response?.data?.errors) {
        return Object.values(err.response.data.errors).flat().join(' ')
      }
      if (err.message) {
        return err.message
      }
      return 'Terjadi kesalahan saat menyimpan produk.'
    }

    const createProduct = async (formData) => {
      const response = await api.post('/products', formData)
      return response.data.data || response.data
    }

    const updateProduct = async (productId, formData) => {
      formData.append('_method', 'PUT')
      const response = await api.post(`/products/${productId}`, formData)
      return response.data.data || response.data
    }

    const saveProduct = async () => {
      try {
        const formData = new FormData()
        formData.append('name', form.value.name)
        formData.append('description', form.value.description)
        formData.append('price', form.value.price)
        formData.append('stock', form.value.stock)
        if (form.value.category_id) {
          formData.append('category_id', form.value.category_id)
          const sel = categories.value.find(c => c.id === form.value.category_id)
          if (sel) {
            formData.append('category', sel.slug || (sel.name || '').toLowerCase())
          }
        }

        if (form.value.images?.length > 0) {
          form.value.images.forEach((image, index) => {
            formData.append(`images[${index}]`, image)
          })
        }

        let savedProduct = null
        if (editingProduct.value?.id) {
          savedProduct = await updateProduct(editingProduct.value.id, formData)
          if (form.value.images.length) {
            for (const file of form.value.images) {
              const fdImg = new FormData()
              fdImg.append('image', file)
              await api.post(`/products/${editingProduct.value.id}/image`, fdImg, {
                headers: { 'Content-Type': 'multipart/form-data' },
              })
            }
          }
          await fetchProducts()
        } else {
          savedProduct = await createProduct(formData)
          products.value.unshift(savedProduct)
        }

        closeModal()
        alert('Produk berhasil disimpan!')
      } catch (err) {
        console.error('Save product error:', err)
        alert(getErrorMessage(err))
      }
    }

    const deleteProduct = async (productId) => {
      if (!confirm('Apakah Anda yakin ingin menghapus produk ini?')) return

      try {
        await api.delete(`/products/${productId}`)
        const index = products.value.findIndex(p => p.id === productId)
        if (index !== -1) {
          products.value.splice(index, 1)
        }
        alert('Produk berhasil dihapus!')
      } catch (err) {
        console.error('Delete product error:', err)
        alert(err.response?.data?.message || 'Gagal menghapus produk')
      }
    }

    const getSoldCount = (product) => {
      const count = product?.terjual ?? product?.total_sold ?? product?.totalSold ?? product?.sold ?? product?.sold_count ?? 0
      return Number(count || 0).toLocaleString('id-ID')
    }

    onMounted(async () => {
      await loadCategories()
      await fetchProducts()
    })

    return {
      products,
      filteredProducts,
      searchQuery,
      filterCategory,
      sortBy,
      loading,
      showModal,
      editingProduct,
      form,
      previewImages,
      existingGalleryCount,
      businessNotVerified,
      isPremium,
      categories,
      productLimitReached,
      onImagesSelected,
      removeImage,
      editProduct,
      closeModal,
      saveProduct,
      deleteProduct,
      getSoldCount,
      normalizeImageUrl,
    }
  }
}
</script>

<style scoped>
/* Main Container */
.sp { padding: 24px 24px 56px; }

/* Alert Styles */
.alert {
  padding: 12px 16px;
  margin-bottom: 16px;
  border-radius: 8px;
  font-size: 0.9rem;
}
.alert-warning {
  background: #fffbeb;
  color: #92400e;
  border: 1px solid #fcd34d;
}
.alert-info {
  background: #eff6ff;
  color: #1e40af;
  border: 1px solid #93c5fd;
}

/* Card & Toolbar */
.sp__card { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden; }
.sp__toolbar { display: flex; gap: 10px; padding: 14px; border-bottom: 1px solid #f3f4f6; flex-wrap: wrap; align-items: center; }
.sp__select, .sp__search { min-width: 150px; height: 40px; border-radius: 10px; border: 1.5px solid #e5e7eb; padding: 0 12px; outline: none; background: #fff; }
.sp__select { appearance: none; -webkit-appearance: none; -moz-appearance: none; cursor: pointer; }
.sp__search { flex: 1; }
.sp__select:focus, .sp__search:focus { border-color: #fca5a5; box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.1); }
.sp__btn { height: 40px; padding: 0 14px; border-radius: 10px; border: none; background: #111827; color: #fff; font-weight: 800; cursor: pointer; }
.sp__btn:hover { opacity: 0.92; }
.sp__btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* Loading & Empty States */
.sp__loading, .sp__empty {
  padding: 40px 20px;
  text-align: center;
  color: #9ca3af;
}

/* Table Styles */
.sp__table { width: 100%; }
.sp__th { display: grid; grid-template-columns: 1.8fr .6fr .7fr .6fr .6fr; gap: 12px; padding: 10px 14px; background: #f9fafb; color: #9ca3af; font-size: .72rem; font-weight: 800; text-transform: uppercase; }
.sp__row { display: grid; grid-template-columns: 1.8fr .6fr .7fr .6fr .6fr; gap: 12px; padding: 12px 14px; border-top: 1px solid #f3f4f6; align-items: center; }
.sp__prod { display: flex; gap: 10px; align-items: center; min-width: 0; }
.sp__thumb { width: 38px; height: 38px; border-radius: 10px; background: #e5e7eb; object-fit: cover; flex-shrink: 0; }
.sp__thumb--empty { background: #d1d5db; }
.sp__name { font-weight: 900; color: #111827; }
.sp__meta { color: #9ca3af; font-size: .78rem; }
.sp__stock { font-weight: 600; }
.sp__stock--low { color: #dc2626; font-weight: 800; }
.sp__price { font-weight: 700; color: #111827; }
.sp__actions { display: flex; gap: 6px; }
.sp__action-btn { width: 30px; height: 30px; border-radius: 6px; border: 1px solid #e5e7eb; background: #fff; cursor: pointer; font-size: 1rem; }
.sp__action-btn--edit { color: #3b82f6; }
.sp__action-btn--edit:hover { background: #dbeafe; }
.sp__action-btn--delete { color: #ef4444; }
.sp__action-btn--delete:hover { background: #fee2e2; }

/* Modal */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.modal__content {
  background: #fff;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}
.modal__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
}
.modal__header h2 {
  margin: 0;
  font-size: 1.25rem;
  color: #111827;
}
.modal__close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #9ca3af;
}

/* Form */
.modal__form { padding: 20px; }
.form__group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-bottom: 16px;
}
.form__group label {
  font-size: .85rem;
  font-weight: 700;
  color: #374151;
}
.form__group input,
.form__group textarea {
  padding: 10px 12px;
  border: 1.5px solid #e5e7eb;
  border-radius: 8px;
  font-family: inherit;
  outline: none;
}
.form__group input:focus,
.form__group textarea:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
.form__group small { color: #9ca3af; font-size: 0.75rem; }
.form__hint-muted {
  margin: 0 0 12px;
  font-size: 0.85rem;
  color: #6b7280;
  display: flex;
  align-items: center;
  gap: 6px;
}
.form__row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form__file { padding: 8px; }
.form__preview {
  width: 100%;
  max-width: 200px;
  height: auto;
  border-radius: 8px;
  margin-top: 8px;
}
.form__previews { display:flex; gap:10px; flex-wrap:wrap; margin-top:10px; }
.form__preview-item { position:relative; }
.form__preview { width:80px; height:80px; object-fit:cover; border-radius:8px; border:2px solid #e5e7eb; }
.form__preview-remove { position:absolute; top:-5px; right:-5px; background:#ef4444; color:white; border:none; border-radius:50%; width:20px; height:20px; display:flex; align-items:center; justify-content:center; font-size:12px; cursor:pointer; }
.form__actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #e5e7eb;
}
.form__btn {
  padding: 10px 16px;
  border-radius: 8px;
  border: none;
  font-weight: 700;
  cursor: pointer;
}
.form__btn--cancel {
  background: #f3f4f6;
  color: #374151;
}
.form__btn--cancel:hover {
  background: #e5e7eb;
}
.form__btn--submit {
  background: #111827;
  color: #fff;
}
.form__btn--submit:hover {
  opacity: 0.92;
}

@media (max-width: 768px) {
  .sp__toolbar { flex-direction: column; }
  .sp__th, .sp__row { grid-template-columns: 1fr !important; }
  .modal__content { width: 95%; }
}
</style>

