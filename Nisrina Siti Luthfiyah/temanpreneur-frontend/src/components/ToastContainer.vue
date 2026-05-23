<template>
  <div class="toast-container">
    <transition-group name="toast" tag="div">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="['toast', `toast--${toast.type}`]"
      >
        <div class="toast-content">
          <span class="toast-icon">
            <svg
              v-if="toast.type === 'success'"
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <polyline points="20 6 9 17 4 12" />
            </svg>
            <svg
              v-else-if="toast.type === 'error'"
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <circle cx="12" cy="12" r="10" />
              <line x1="15" y1="9" x2="9" y2="15" />
              <line x1="9" y1="9" x2="15" y2="15" />
            </svg>
            <svg
              v-else-if="toast.type === 'warning'"
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3.05h16.94a2 2 0 001.71-3.05L13.71 3.86a2 2 0 00-3.42 0z" />
              <line x1="12" y1="9" x2="12" y2="13" />
              <line x1="12" y1="17" x2="12.01" y2="17" />
            </svg>
            <svg
              v-else
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <circle cx="12" cy="12" r="10" />
              <line x1="12" y1="16" x2="12" y2="12" />
              <line x1="12" y1="8" x2="12.01" y2="8" />
            </svg>
          </span>
          <span class="toast-message">{{ toast.message }}</span>
        </div>
        <button class="toast-close" @click="removeToast(toast.id)">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
          </svg>
        </button>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { useToast } from '@/composables/useToast'

const { toasts, removeToast } = useToast()
</script>

<style scoped>
.toast-container {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 9999;
  max-width: 400px;
  pointer-events: none;
}

.toast {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  margin-bottom: 8px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  animation: slideIn 0.3s ease-out;
  pointer-events: auto;
  backdrop-filter: blur(10px);
}

.toast--success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.toast--error {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
}

.toast--warning {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: white;
}

.toast--info {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
}

.toast-content {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
}

.toast-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.toast-message {
  line-height: 1.4;
}

.toast-close {
  background: none;
  border: none;
  color: inherit;
  cursor: pointer;
  padding: 4px;
  margin-left: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0.8;
  transition: opacity 0.2s;
  flex-shrink: 0;
}

.toast-close:hover {
  opacity: 1;
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  transform: translateX(400px);
  opacity: 0;
}

.toast-leave-to {
  transform: translateX(400px);
  opacity: 0;
}

@media (max-width: 640px) {
  .toast-container {
    left: 12px;
    right: 12px;
    max-width: none;
  }

  .toast {
    margin-bottom: 6px;
  }
}

@keyframes slideIn {
  from {
    transform: translateX(400px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}
</style>
