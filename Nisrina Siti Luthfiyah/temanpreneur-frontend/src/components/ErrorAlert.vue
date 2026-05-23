<!-- src/components/ErrorAlert.vue -->
<template>
  <Transition name="fade">
    <div v-if="visible" class="error-alert" :class="errorClass">
      <div class="error-alert-content">
        <div class="error-icon">
          <i :class="getIcon()"></i>
        </div>
        <div class="error-message">
          <h4 class="error-title">{{ title }}</h4>
          <p class="error-text">{{ message }}</p>
          
          <!-- Connection Error Specific Help -->
          <div v-if="isConnectionError" class="error-help">
            <p class="help-text">Solusi:</p>
            <ul class="help-list">
              <li>Pastikan Server Backend sudah berjalan</li>
              <li>Jalankan: <code>php artisan serve</code></li>
              <li>Cek port (default: 8000)</li>
              <li>Refresh halaman setelah server berjalan</li>
            </ul>
          </div>
        </div>
        
        <button class="error-close" @click="close" aria-label="Close error">
          <i class="fa fa-times"></i>
        </button>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Error, null],
    default: null
  },
  type: {
    type: String,
    default: 'error' // 'error', 'warning', 'info'
  },
  duration: {
    type: Number,
    default: 0 // 0 = manual close, >0 = auto-close after seconds
  }
})

const emit = defineEmits(['update:modelValue', 'close'])

const visible = ref(false)
let autoCloseTimer = null

const message = computed(() => {
  if (!props.modelValue) return ''
  if (props.modelValue instanceof Error) {
    return props.modelValue.message
  }
  return String(props.modelValue)
})

const isConnectionError = computed(() => {
  return message.value.includes('Server Backend') || 
         message.value.includes('connection') ||
         message.value.includes('Network Error')
})

const errorClass = computed(() => {
  return `error-alert--${props.type}`
})

const title = computed(() => {
  if (isConnectionError.value) {
    return 'Server Tidak Terhubung'
  }
  
  switch (props.type) {
    case 'warning':
      return 'Peringatan'
    case 'info':
      return 'Informasi'
    default:
      return 'Terjadi Kesalahan'
  }
})

const getIcon = () => {
  if (isConnectionError.value) {
    return 'fa fa-wifi'
  }
  
  switch (props.type) {
    case 'warning':
      return 'fa fa-exclamation-triangle'
    case 'info':
      return 'fa fa-info-circle'
    default:
      return 'fa fa-times-circle'
  }
}

const close = () => {
  visible.value = false
  emit('update:modelValue', null)
  emit('close')
}

watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    visible.value = true
    
    // Clear previous timer
    if (autoCloseTimer) {
      clearTimeout(autoCloseTimer)
    }
    
    // Set auto-close timer jika duration > 0
    if (props.duration > 0) {
      autoCloseTimer = setTimeout(() => {
        close()
      }, props.duration * 1000)
    }
  } else {
    visible.value = false
  }
})

onMounted(() => {
  if (props.modelValue) {
    visible.value = true
  }
})
</script>

<style scoped>
.error-alert {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 10000;
  min-width: 300px;
  max-width: 500px;
  margin: 0;
  animation: slideInRight 0.3s ease-out;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.error-alert-content {
  display: flex;
  gap: 12px;
  padding: 16px;
  border-radius: 8px;
  border-left: 4px solid;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  background: white;
}

/* Error type variants */
.error-alert--error {
  border-left-color: #dc3545;
}

.error-alert--error .error-icon {
  color: #dc3545;
}

.error-alert--warning {
  border-left-color: #ffc107;
}

.error-alert--warning .error-icon {
  color: #ffc107;
}

.error-alert--info {
  border-left-color: #0dcaf0;
}

.error-alert--info .error-icon {
  color: #0dcaf0;
}

.error-icon {
  font-size: 24px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.error-message {
  flex: 1;
  min-width: 0;
}

.error-title {
  margin: 0 0 4px 0;
  font-size: 14px;
  font-weight: 600;
  color: #212529;
}

.error-text {
  margin: 0 0 8px 0;
  font-size: 13px;
  color: #495057;
  word-break: break-word;
}

.error-help {
  margin-top: 8px;
  padding: 8px;
  background: #f8f9fa;
  border-radius: 4px;
}

.help-text {
  margin: 0 0 6px 0;
  font-size: 12px;
  font-weight: 600;
  color: #212529;
}

.help-list {
  margin: 0;
  padding-left: 16px;
  font-size: 12px;
  color: #495057;
}

.help-list li {
  margin: 4px 0;
}

.help-list code {
  background: white;
  padding: 2px 4px;
  border-radius: 3px;
  font-family: 'Monaco', 'Courier New', monospace;
  color: #dc3545;
}

.error-close {
  background: none;
  border: none;
  color: #999;
  font-size: 18px;
  cursor: pointer;
  padding: 0;
  flex-shrink: 0;
  transition: color 0.2s;
}

.error-close:hover {
  color: #333;
}

/* Fade transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Mobile responsiveness */
@media (max-width: 640px) {
  .error-alert {
    top: 10px;
    right: 10px;
    left: 10px;
    min-width: unset;
    max-width: unset;
  }
}
</style>
