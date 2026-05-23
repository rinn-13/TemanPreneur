<template>
  <div class="product-form">
    <div class="form-section">
      <h3 class="section-title">Informasi Produk</h3>
      
      <FormInput
        id="product-name"
        type="text"
        label="Nama Produk *"
        :modelValue="form.values.name"
        :error="form.errors.name"
        placeholder="Contoh: Brownies Coklat Premium"
        @update:modelValue="form.handleChange('name', $event)"
        @blur="form.handleBlur('name')"
      />

      <div class="form-group">
        <label class="form-label">Deskripsi Produk *</label>
        <textarea
          :value="form.values.description"
          @input="form.handleChange('description', $event.target.value)"
          @blur="form.handleBlur('description')"
          placeholder="Jelaskan detail produk, bahan, cara penggunaan, dll..."
          class="form-textarea"
          :class="{ 'is-error': form.errors.description }"
          rows="5"
        ></textarea>
        <span v-if="form.errors.description" class="form-error">{{ form.errors.description }}</span>
        <small class="form-hint">{{ form.values.description?.length || 0 }}/1000 karakter</small>
      </div>

      <div class="form-row">
        <FormInput
          id="product-price"
          type="number"
          label="Harga (Rp) *"
          :modelValue="form.values.price"
          :error="form.errors.price"
          placeholder="0"
          @update:modelValue="form.handleChange('price', Number($event))"
          @blur="form.handleBlur('price')"
        />

        <FormInput
          id="product-stock"
          type="number"
          label="Stok *"
          :modelValue="form.values.stock"
          :error="form.errors.stock"
          placeholder="0"
          @update:modelValue="form.handleChange('stock', Number($event))"
          @blur="form.handleBlur('stock')"
        />
      </div>

      <div class="form-group">
        <label class="form-label">Kategori Produk</label>
        <select
          :value="form.values.category_id"
          @change="form.handleChange('category_id', Number($event.target.value))"
          class="form-input"
        >
          <option value="">Pilih Kategori</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </select>
      </div>
    </div>

    <div class="form-section">
      <h3 class="section-title">Gambar Produk</h3>
      <FileUpload
        label="Upload Foto Produk"
        description="Drag and drop atau klik untuk memilih gambar"
        :endpoint="imageEndpoint"
        @upload="handleImageUpload"
        @error="handleUploadError"
        @clear="handleImageClear"
      />
    </div>

    <div class="form-actions">
      <button
        type="button"
        class="btn btn-secondary"
        @click="$emit('cancel')"
      >
        Batal
      </button>
      <button
        type="button"
        class="btn btn-primary"
        :disabled="isSubmitting || !isFormValid"
        @click="handleSubmit"
      >
        <template v-if="!isSubmitting">
          {{ isEditMode ? 'Update Produk' : 'Buat Produk' }}
        </template>
        <template v-else>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="spin">
            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-dasharray="28 28"/>
          </svg>
          Memproses...
        </template>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useFormValidation } from '@/composables/useFormValidation'
import { useToast } from '@/composables/useToast'
import api from '@/api/axios'
import FormInput from '@/components/FormInput.vue'
import FileUpload from '@/components/FileUpload.vue'

const props = defineProps({
  product: Object,
  productId: [String, Number],
  isEditMode: { type: Boolean, default: false },
})

const emit = defineEmits(['submit', 'cancel', 'success'])

const { success, error: showError } = useToast()

const categories = ref([])
const isSubmitting = ref(false)
const imageEndpoint = ref(null)

const form = useFormValidation({
  name: props.product?.name || '',
  description: props.product?.description || '',
  price: props.product?.price || 0,
  stock: props.product?.stock || 0,
  category_id: props.product?.category_id || null,
})

const isFormValid = computed(() => {
  return (
    form.values.name &&
    form.values.description &&
    form.values.price > 0 &&
    form.values.stock >= 0
  )
})

const fetchCategories = async () => {
  try {
    const { data } = await api.get('/categories')
    categories.value = data || []
  } catch (error) {
    console.error('Error fetching categories:', error)
  }
}

const handleImageUpload = (imageData) => {
  // Image already uploaded via FileUpload component
  console.log('Image uploaded:', imageData)
}

const handleUploadError = (error) => {
  showError('Gagal mengupload gambar: ' + error)
}

const handleImageClear = () => {
  // Handle image clear
}

const handleSubmit = async () => {
  // Validate form
  const rules = {
    name: { required: true, minLength: 3, maxLength: 255 },
    description: { required: true, minLength: 20, maxLength: 1000 },
    price: { required: true, min: 1000 },
    stock: { required: true, min: 0 },
  }

  if (!form.validateForm(rules)) {
    showError('Mohon lengkapi semua data dengan benar')
    return
  }

  isSubmitting.value = true
  try {
    const endpoint = props.isEditMode 
      ? `/products/${props.productId}` 
      : '/products'
    
    const method = props.isEditMode ? 'put' : 'post'
    
    const payload = {
      name: form.values.name,
      description: form.values.description,
      price: Number(form.values.price),
      stock: Number(form.values.stock),
      category_id: form.values.category_id || null,
    }

    const response = await api[method](endpoint, payload)
    
    success(props.isEditMode ? 'Produk berhasil diupdate' : 'Produk berhasil dibuat')
    emit('success', response.data)
  } catch (error) {
    showError(error.message)
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  fetchCategories()
  if (props.productId) {
    imageEndpoint.value = `/products/${props.productId}/image`
  }
})
</script>

<style scoped>
.product-form {
  max-width: 800px;
  margin: 0 auto;
}

.form-section {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 20px;
  border: 1px solid #e5e7eb;
}

.section-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #111827;
  margin: 0 0 20px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-group {
  margin-bottom: 16px;
}

.form-label {
  display: block;
  font-size: 0.9rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 8px;
}

.form-textarea {
  width: 100%;
  padding: 10px 14px;
  border: 1.5px solid #e5e7eb;
  border-radius: 8px;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.9rem;
  resize: vertical;
  transition: border-color 0.18s;
}

.form-textarea:focus {
  outline: none;
  border-color: #e53e3e;
}

.form-textarea.is-error {
  border-color: #e53e3e;
  background: #fff5f5;
}

.form-input,
.form-textarea {
  width: 100%;
}

.form-error {
  display: block;
  font-size: 0.75rem;
  color: #e53e3e;
  margin-top: 4px;
}

.form-hint {
  display: block;
  font-size: 0.8rem;
  color: #9ca3af;
  margin-top: 4px;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 24px;
}

.btn {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
  font-family: 'Plus Jakarta Sans', sans-serif;
}

.btn-primary {
  background: linear-gradient(135deg, #e53e3e, #c53030);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(229, 62, 62, 0.3);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-secondary {
  background: #f3f4f6;
  color: #374151;
}

.btn-secondary:hover {
  background: #e5e7eb;
}

.spin {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@media (max-width: 640px) {
  .form-row {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column-reverse;
  }

  .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>
