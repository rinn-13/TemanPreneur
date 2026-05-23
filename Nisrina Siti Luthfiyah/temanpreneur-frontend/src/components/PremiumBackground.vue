<template>
  <div
    class="premium-bg"
    :class="[
      `premium-bg--${variant}`,
      { 'premium-bg--vivid': vivid && variant === 'overlay' },
    ]"
    aria-hidden="true"
  >
    <canvas ref="canvasRef" class="premium-bg__canvas"></canvas>
    <div class="premium-bg__mesh" :style="meshStyle"></div>
    <div v-if="variant === 'overlay'" class="premium-bg__glow premium-bg__glow--a" :style="glowA"></div>
    <div v-if="variant === 'overlay'" class="premium-bg__glow premium-bg__glow--b" :style="glowB"></div>
    <div v-if="variant === 'overlay'" class="premium-bg__blur-layer premium-bg__blur-layer--a" :style="blurLayerA"></div>
    <div v-if="variant === 'overlay'" class="premium-bg__blur-layer premium-bg__blur-layer--b" :style="blurLayerB"></div>
    <div v-if="variant === 'overlay'" class="premium-bg__stream premium-bg__stream--a"></div>
    <div v-if="variant === 'overlay'" class="premium-bg__stream premium-bg__stream--b"></div>
    <div v-if="variant === 'overlay'" class="premium-bg__shimmer"></div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  primary: { type: String, default: '#f59e0b' },
  secondary: { type: String, default: '#e53e3e' },
  accent: { type: String, default: '#7c3aed' },
  vivid: { type: Boolean, default: true },
  /** overlay = subtle layer on existing page bg; fullscreen = legacy full-bleed (dashboard only) */
  variant: { type: String, default: 'overlay' },
})

const canvasRef = ref(null)
let animFrame = null
let resizeHandler = null

function hexToRgb(hex) {
  const h = String(hex || '#f59e0b').replace('#', '')
  const full = h.length === 3 ? h.split('').map((c) => c + c).join('') : h
  const n = parseInt(full, 16)
  if (Number.isNaN(n)) return { r: 245, g: 158, b: 11 }
  return { r: (n >> 16) & 255, g: (n >> 8) & 255, b: n & 255 }
}

const meshStyle = computed(() => {
  const p = hexToRgb(props.primary)
  const s = hexToRgb(props.secondary)
  const a = hexToRgb(props.accent)
  const alpha = props.variant === 'overlay' ? 0.14 : 0.28
  return {
    background: `
      radial-gradient(ellipse 70% 55% at 18% 22%, rgba(${p.r},${p.g},${p.b},${alpha}) 0%, transparent 68%),
      radial-gradient(ellipse 55% 48% at 82% 78%, rgba(${s.r},${s.g},${s.b},${alpha * 0.85}) 0%, transparent 70%),
      radial-gradient(ellipse 40% 38% at 58% 12%, rgba(${a.r},${a.g},${a.b},${alpha * 0.7}) 0%, transparent 72%)
    `,
  }
})

const glowA = computed(() => {
  const p = hexToRgb(props.primary)
  return { background: `radial-gradient(circle, rgba(${p.r},${p.g},${p.b},0.22) 0%, transparent 70%)` }
})

const glowB = computed(() => {
  const s = hexToRgb(props.secondary)
  return { background: `radial-gradient(circle, rgba(${s.r},${s.g},${s.b},0.16) 0%, transparent 70%)` }
})

const blurLayerA = computed(() => {
  const p = hexToRgb(props.primary)
  return { background: `radial-gradient(ellipse 80% 60% at 20% 30%, rgba(${p.r},${p.g},${p.b},0.08))` }
})

const blurLayerB = computed(() => {
  const s = hexToRgb(props.secondary)
  return { background: `radial-gradient(ellipse 70% 50% at 80% 70%, rgba(${s.r},${s.g},${s.b},0.06))` }
})

function startCanvas() {
  const canvas = canvasRef.value
  if (!canvas) return
  const ctx = canvas.getContext('2d')
  if (!ctx) return
  const primary = hexToRgb(props.primary)
  const isOverlay = props.variant === 'overlay'

  const resize = () => {
    const parent = canvas.parentElement
    canvas.width = parent?.clientWidth || window.innerWidth
    canvas.height = parent?.clientHeight || window.innerHeight
  }
  resize()
  if (resizeHandler) window.removeEventListener('resize', resizeHandler)
  resizeHandler = resize
  window.addEventListener('resize', resizeHandler)

  const blobCount = isOverlay ? (props.vivid ? 5 : 4) : props.vivid ? 6 : 4
  const blobs = Array.from({ length: blobCount }, (_, i) => ({
    x: Math.random() * canvas.width,
    y: Math.random() * canvas.height,
    r: (isOverlay ? 120 : 180) + Math.random() * (isOverlay ? 160 : 220),
    vx: (Math.random() - 0.5) * (isOverlay ? 0.6 : 0.7),
    vy: (Math.random() - 0.5) * (isOverlay ? 0.6 : 0.75),
    alpha: isOverlay
      ? 0.08 + Math.random() * 0.12
      : 0.14 + Math.random() * 0.09,
  }))

  const draw = () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height)
    blobs.forEach((b) => {
      b.x += b.vx
      b.y += b.vy
      if (b.x < -b.r) b.x = canvas.width + b.r
      if (b.x > canvas.width + b.r) b.x = -b.r
      if (b.y < -b.r) b.y = canvas.height + b.r
      if (b.y > canvas.height + b.r) b.y = -b.r
      const g = ctx.createRadialGradient(b.x, b.y, 0, b.x, b.y, b.r)
      g.addColorStop(0, `rgba(${primary.r},${primary.g},${primary.b},${b.alpha})`)
      g.addColorStop(1, 'transparent')
      ctx.beginPath()
      ctx.arc(b.x, b.y, b.r, 0, Math.PI * 2)
      ctx.fillStyle = g
      ctx.fill()
    })
    animFrame = requestAnimationFrame(draw)
  }
  draw()
}

onMounted(startCanvas)
watch(() => [props.primary, props.variant, props.vivid], () => {
  cancelAnimationFrame(animFrame)
  startCanvas()
})

onUnmounted(() => {
  cancelAnimationFrame(animFrame)
  if (resizeHandler) window.removeEventListener('resize', resizeHandler)
})
</script>

<style scoped>
.premium-bg {
  pointer-events: none;
  overflow: hidden;
  z-index: -1;
}

/* Overlay: stays inside parent, does not repaint whole page */
.premium-bg--overlay {
  position: absolute;
  inset: -14%;
  background: transparent;
}

/* Fullscreen variant for seller dashboard only */
.premium-bg--fullscreen {
  position: fixed;
  inset: 0;
  z-index: -1;
  background: linear-gradient(165deg, rgba(15, 23, 42, 0.92) 0%, rgba(30, 27, 75, 0.88) 45%, rgba(15, 23, 42, 0.92) 100%);
}

.premium-bg__canvas {
  width: 100%;
  height: 100%;
  opacity: 0.94;
}
.premium-bg--overlay .premium-bg__canvas {
  opacity: 0.88;
}

.premium-bg__mesh {
  position: absolute;
  inset: 0;
  animation: mesh-float 12s ease-in-out infinite alternate;
  mix-blend-mode: soft-light;
}

.premium-bg__glow {
  position: absolute;
  width: 55%;
  height: 55%;
  border-radius: 50%;
  filter: blur(48px);
  animation: float-glow 18s ease-in-out infinite alternate;
}
.premium-bg__glow--a {
  top: -8%;
  left: -5%;
}
.premium-bg__glow--b {
  bottom: -12%;
  right: -8%;
  animation-delay: -6s;
}

.premium-bg__blur-layer {
  position: absolute;
  filter: blur(30px);
  animation: pulse-blur 8s ease-in-out infinite;
}
.premium-bg__blur-layer--a {
  top: -5%;
  left: -8%;
  width: 45%;
  height: 45%;
  border-radius: 50%;
}
.premium-bg__blur-layer--b {
  bottom: -8%;
  right: -5%;
  width: 50%;
  height: 50%;
  border-radius: 50%;
  animation-delay: -3s;
}

.premium-bg__shimmer {
  position: absolute;
  inset: -50%;
  background: linear-gradient(
    115deg,
    transparent 42%,
    rgba(255, 255, 255, 0.05) 50%,
    transparent 58%
  );
  animation: premium-shimmer 10s ease-in-out infinite;
  opacity: 0.8;
}

.premium-bg__stream {
  position: absolute;
  width: 170%;
  height: 150%;
  border-radius: 50%;
  filter: blur(46px);
  opacity: 0.18;
  mix-blend-mode: screen;
}
.premium-bg__stream--a {
  top: -36%;
  left: -28%;
  background: radial-gradient(circle at 24% 30%, rgba(245, 158, 11, 0.22), transparent 36%);
  animation: stream-float-a 18s ease-in-out infinite alternate;
}
.premium-bg__stream--b {
  bottom: -32%;
  right: -22%;
  background: radial-gradient(circle at 62% 44%, rgba(59, 130, 246, 0.18), transparent 38%);
  animation: stream-float-b 16s ease-in-out infinite alternate;
}

@keyframes stream-float-a {
  from { transform: translate(0, 0) scale(1); }
  to { transform: translate(5%, -4%) scale(1.03); }
}
@keyframes stream-float-b {
  from { transform: translate(0, 0) scale(1); }
  to { transform: translate(-6%, 5%) scale(1.02); }
}

@keyframes mesh-float {
  0% { transform: scale(1) translate(0, 0); }
  25% { transform: scale(1.02) translate(-1%, 0.8%); }
  50% { transform: scale(1.04) translate(-1.5%, 1.5%); }
  75% { transform: scale(1.02) translate(-1%, 0.8%); }
  100% { transform: scale(1) translate(0, 0); }
}
@keyframes float-glow {
  0% { transform: translate(0, 0) scale(1); }
  25% { transform: translate(1.5%, 2%) scale(1.03); }
  50% { transform: translate(3%, 4%) scale(1.08); }
  75% { transform: translate(1.5%, 2%) scale(1.03); }
  100% { transform: translate(0, 0) scale(1); }
}
@keyframes premium-shimmer {
  0%, 100% { transform: translateX(-6%) rotate(0deg); opacity: 0.5; }
  50% { transform: translateX(6%) rotate(1deg); opacity: 0.8; }
}
@keyframes pulse-blur {
  0%, 100% { filter: blur(24px); opacity: 0.6; }
  50% { filter: blur(28px); opacity: 0.75; }
}
</style>
