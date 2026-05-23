import { ref } from 'vue'

export function useFileUpload() {
  const isUploading = ref(false)
  const uploadProgress = ref(0)
  const uploadError = ref(null)
  const uploadedFile = ref(null)

  const validateFile = (file, options = {}) => {
    const {
      maxSize = 2 * 1024 * 1024, // 2MB default
      allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'],
    } = options

    if (!file) {
      return { valid: false, error: 'File tidak dipilih' }
    }

    // Check file size
    if (file.size > maxSize) {
      const maxSizeMB = maxSize / (1024 * 1024)
      return { valid: false, error: `File terlalu besar. Maksimal ${maxSizeMB}MB` }
    }

    // Check file type
    if (!allowedTypes.includes(file.type)) {
      return { valid: false, error: 'Tipe file tidak diizinkan. Gunakan JPG, PNG, atau WebP' }
    }

    return { valid: true, error: null }
  }

  const uploadFile = async (file, endpoint, onProgress = null) => {
    isUploading.value = true
    uploadProgress.value = 0
    uploadError.value = null

    try {
      // Validate file
      const validation = validateFile(file)
      if (!validation.valid) {
        uploadError.value = validation.error
        throw new Error(validation.error)
      }

      // Create FormData
      const formData = new FormData()
      formData.append('file', file)

      // Upload
      const { api } = await import('@/api/axios.js')
      const response = await api.post(endpoint, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
        onUploadProgress: (progressEvent) => {
          uploadProgress.value = Math.round((progressEvent.loaded / progressEvent.total) * 100)
          if (onProgress) onProgress(uploadProgress.value)
        },
      })

      uploadedFile.value = response.data
      return response.data
    } catch (error) {
      uploadError.value = error.message || 'Gagal mengupload file'
      throw error
    } finally {
      isUploading.value = false
    }
  }

  const resetUpload = () => {
    isUploading.value = false
    uploadProgress.value = 0
    uploadError.value = null
    uploadedFile.value = null
  }

  return {
    isUploading,
    uploadProgress,
    uploadError,
    uploadedFile,
    validateFile,
    uploadFile,
    resetUpload,
  }
}

// Utility function to preview file
export function getFilePreview(file) {
  return new Promise((resolve) => {
    const reader = new FileReader()
    reader.onload = (e) => resolve(e.target.result)
    reader.readAsDataURL(file)
  })
}
