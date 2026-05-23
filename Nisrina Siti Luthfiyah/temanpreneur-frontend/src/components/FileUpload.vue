<template>
  <div class="file-upload">
    <input
      ref="fileInput"
      type="file"
      :accept="accept"
      :disabled="isUploading"
      @change="handleFileChange"
      style="display: none"
    />

    <div
      v-if="!preview && !uploadedFile"
      class="upload-zone"
      @click="triggerFileInput"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="handleFileDrop"
      :class="{ 'is-dragging': isDragging }"
    >
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <polyline points="17 8 12 3 7 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <line x1="12" y1="3" x2="12" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <p class="upload-title">{{ label }}</p>
      <p class="upload-desc">{{ description }}</p>
      <button type="button" class="upload-btn" @click="triggerFileInput">
        Pilih File
      </button>
    </div>

    <!-- Preview -->
    <div v-if="preview" class="preview-zone">
      <img :src="preview" :alt="label" class="preview-image" />
      <div class="preview-actions">
        <button v-if="!isUploading" type="button" class="action-btn cancel" @click="clearPreview">
          Ganti File
        </button>
      </div>
    </div>

    <!-- Upload progress -->
    <div v-if="isUploading" class="upload-progress">
      <div class="progress-bar">
        <div class="progress-fill" :style="{ width: uploadProgress + '%' }"></div>
      </div>
      <p class="progress-text">Uploading... {{ uploadProgress }}%</p>
    </div>

    <!-- Error message -->
    <div v-if="uploadError" class="upload-error">
      ️ {{ uploadError }}
    </div>

    <!-- Success message -->
    <div v-if="uploadedFile && !isUploading" class="upload-success">
       File berhasil diupload
    </div>

    <!-- File info -->
    <div v-if="selectedFile && !preview" class="file-info">
      <p class="file-name">{{ selectedFile.name }}</p>
      <p class="file-size">{{ formatFileSize(selectedFile.size) }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useFileUpload, getFilePreview } from '@/composables/useFileUpload'

const props = defineProps({
  label: { type: String, default: 'Upload File' },
  description: { type: String, default: 'Drag and drop file here or click to select' },
  accept: { type: String, default: 'image/*' },
  maxSize: { type: Number, default: 2 * 1024 * 1024 },
  allowedTypes: { type: Array, default: () => ['image/jpeg', 'image/png', 'image/webp'] },
  endpoint: String,
})

const emit = defineEmits(['upload', 'error', 'clear'])

const fileInput = ref(null)
const isDragging = ref(false)
const selectedFile = ref(null)
const preview = ref(null)

const { isUploading, uploadProgress, uploadError, uploadedFile, uploadFile, resetUpload } = useFileUpload()

const triggerFileInput = () => {
  fileInput.value?.click()
}

const handleFileChange = async (event) => {
  const file = event.target.files?.[0]
  if (file) {
    await handleFileSelect(file)
  }
}

const handleFileDrop = async (event) => {
  isDragging.value = false
  const file = event.dataTransfer.files?.[0]
  if (file) {
    await handleFileSelect(file)
  }
}

const handleFileSelect = async (file) => {
  selectedFile.value = file
  
  // Generate preview
  try {
    preview.value = await getFilePreview(file)
  } catch (error) {
    console.error('Failed to generate preview:', error)
  }

  // Auto-upload if endpoint provided
  if (props.endpoint) {
    await performUpload(file)
  } else {
    emit('upload', file)
  }
}

const performUpload = async (file) => {
  try {
    const result = await uploadFile(file, props.endpoint)
    emit('upload', result)
  } catch (error) {
    emit('error', error.message)
  }
}

const clearPreview = () => {
  preview.value = null
  selectedFile.value = null
  resetUpload()
  if (fileInput.value) {
    fileInput.value.value = ''
  }
  emit('clear')
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}
</script>

<style scoped>
.file-upload {
  width: 100%;
}

.upload-zone {
  border: 2px dashed #e5e7eb;
  border-radius: 12px;
  padding: 40px 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
  background: #f9fafb;
}

.upload-zone:hover,
.upload-zone.is-dragging {
  border-color: #e53e3e;
  background: #fff5f5;
}

.upload-zone svg {
  color: #e53e3e;
  margin-bottom: 12px;
}

.upload-title {
  font-size: 1rem;
  font-weight: 700;
  color: #111827;
  margin: 0 0 4px;
}

.upload-desc {
  font-size: 0.85rem;
  color: #9ca3af;
  margin: 0 0 16px;
}

.upload-btn {
  padding: 8px 20px;
  background: linear-gradient(135deg, #e53e3e, #c53030);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s;
}

.upload-btn:hover {
  transform: translateY(-2px);
}

.preview-zone {
  position: relative;
  margin-top: 20px;
}

.preview-image {
  width: 100%;
  max-width: 400px;
  height: auto;
  border-radius: 12px;
  border: 2px solid #e5e7eb;
  display: block;
}

.preview-actions {
  display: flex;
  gap: 8px;
  margin-top: 12px;
}

.action-btn {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s;
}

.action-btn.cancel {
  background: #f3f4f6;
  color: #374151;
}

.action-btn.cancel:hover {
  background: #e5e7eb;
}

.upload-progress {
  margin-top: 16px;
}

.progress-bar {
  width: 100%;
  height: 6px;
  background: #e5e7eb;
  border-radius: 3px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #e53e3e, #c53030);
  transition: width 0.3s;
}

.progress-text {
  font-size: 0.85rem;
  color: #6b7280;
  text-align: center;
  margin-top: 8px;
}

.upload-error {
  background: #fee;
  color: #c53030;
  padding: 12px;
  border-radius: 6px;
  margin-top: 12px;
  font-size: 0.85rem;
}

.upload-success {
  background: #ecfdf5;
  color: #10b981;
  padding: 12px;
  border-radius: 6px;
  margin-top: 12px;
  font-size: 0.85rem;
}

.file-info {
  margin-top: 12px;
  padding: 12px;
  background: #f9fafb;
  border-radius: 6px;
}

.file-name {
  font-size: 0.9rem;
  font-weight: 600;
  color: #111827;
  margin: 0 0 4px;
}

.file-size {
  font-size: 0.8rem;
  color: #9ca3af;
  margin: 0;
}
</style>
