<template>
  <div class="user-avatar" :class="[`user-avatar--${size}`, { 'user-avatar--premium': isPremium }]">
    <img
      :src="avatarSrc"
      :alt="altText"
      class="user-avatar__img"
      @error="onError"
      loading="lazy"
    />
    <span v-if="isPremium" class="user-avatar__badge" aria-label="Premium">★</span>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { resolveAvatar, getDefaultAvatar } from '@/composables/useAvatar'

const props = defineProps({
  user: { type: Object, default: null },
  role: { type: String, default: null },
  size: { type: String, default: 'md' }, // sm | md | lg | xl
  isPremium: { type: Boolean, default: false },
  alt: { type: String, default: '' },
})

const failed = ref(false)
const avatarSrc = ref(resolveAvatar(props.user, props.role))

const altText = computed(() => props.alt || props.user?.name || 'Avatar')

watch(() => [props.user, props.role], () => {
  failed.value = false
  avatarSrc.value = resolveAvatar(props.user, props.role)
})

const onError = () => {
  if (!failed.value) {
    failed.value = true
    avatarSrc.value = getDefaultAvatar(props.role || props.user?.role || 'buyer')
  }
}
</script>

<style scoped>
.user-avatar {
  position: relative;
  display: inline-flex;
  flex-shrink: 0;
  border-radius: 50%;
  overflow: hidden;
  background: #f3f4f6;
}
.user-avatar--sm { width: 28px; height: 28px; }
.user-avatar--md { width: 40px; height: 40px; }
.user-avatar--lg { width: 56px; height: 56px; }
.user-avatar--xl { width: 72px; height: 72px; }
.user-avatar--premium {
  box-shadow: 0 0 0 2px #f59e0b, 0 0 16px rgba(245, 158, 11, 0.45);
}
.user-avatar__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.user-avatar__badge {
  position: absolute;
  bottom: -2px;
  right: -2px;
  width: 16px;
  height: 16px;
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: #fff;
  font-size: 9px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  border: 2px solid #fff;
  animation: pulse-badge 2s ease-in-out infinite;
}
@keyframes pulse-badge {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}
</style>
