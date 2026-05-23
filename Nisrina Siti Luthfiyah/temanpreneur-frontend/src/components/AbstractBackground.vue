<template>
  <div class="abstract-bg" aria-hidden="true">
    <canvas ref="canvasRef" class="abstract-bg__canvas"></canvas>
  </div>
</template>

<script setup>
import { useAbstractBg } from '@/composables/useAbstractBg'

const props = defineProps({
  intensity: { type: Number, default: 1.35 },
  blobCount: { type: Number, default: 6 },
})

const { canvasRef } = useAbstractBg({
  intensity: props.intensity,
  blobCount: props.blobCount,
})
</script>

<style scoped>
.abstract-bg {
  position: fixed;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  overflow: hidden;
}
.abstract-bg__canvas {
  width: 100%;
  height: 100%;
  opacity: 1;
}
</style>
