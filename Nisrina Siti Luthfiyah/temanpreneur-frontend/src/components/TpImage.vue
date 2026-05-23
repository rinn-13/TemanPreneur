<template>
  <img
    :src="resolvedSrc"
    :alt="alt"
    :class="imgClass"
    :loading="loading"
    @error="handleError"
  />
</template>

<script setup>
import { ref, watch } from 'vue'
import { normalizeImageUrl, onImageError, PLACEHOLDER_IMAGE } from '@/utils/image'

const props = defineProps({
  src: { type: [String, Object], default: null },
  alt: { type: String, default: '' },
  fallback: { type: String, default: PLACEHOLDER_IMAGE },
  imgClass: { type: String, default: '' },
  loading: { type: String, default: 'lazy' },
})

const resolvedSrc = ref(normalizeImageUrl(props.src, props.fallback))

watch(
  () => [props.src, props.fallback],
  () => {
    resolvedSrc.value = normalizeImageUrl(props.src, props.fallback)
  }
)

const handleError = (e) => {
  onImageError(e, props.fallback)
  resolvedSrc.value = props.fallback || PLACEHOLDER_IMAGE
}
</script>
