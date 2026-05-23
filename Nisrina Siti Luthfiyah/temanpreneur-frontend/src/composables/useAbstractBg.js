/**
 * Animated abstract canvas background — reusable di Home, Katalog, Blog
 */
import { ref, onMounted, onUnmounted } from 'vue'

export function useAbstractBg(options = {}) {
  const {
    intensity = 1,       // 1 = normal, 1.5 = lebih tebal/premium
    blobCount = 6,
    lineCount = 22,
    dotCount = 40,
    hueBase = 0,         // merah TemanPreneur
  } = options

  const canvasRef = ref(null)
  let animFrame = null
  let cleanup = null

  const init = () => {
    const canvas = canvasRef.value
    if (!canvas) return

    const ctx = canvas.getContext('2d')
    let W = window.innerWidth
    let H = document.documentElement.scrollHeight || window.innerHeight

    const resize = () => {
      W = window.innerWidth
      H = Math.max(document.documentElement.scrollHeight, window.innerHeight)
      canvas.width = W
      canvas.height = H
    }
    resize()
    window.addEventListener('resize', resize)

    const alphaMul = intensity
    const blobs = Array.from({ length: blobCount }, (_, i) => ({
      x: Math.random() * W,
      y: Math.random() * H,
      r: (180 + Math.random() * 220) * intensity,
      vx: (Math.random() - 0.5) * 0.4 * intensity,
      vy: (Math.random() - 0.5) * 0.4 * intensity,
      hue: hueBase + (i % 2 === 0 ? 0 : 10),
      alpha: (0.045 + Math.random() * 0.055) * alphaMul,
    }))

    const lines = Array.from({ length: lineCount }, () => ({
      x1: Math.random() * W, y1: Math.random() * H,
      x2: Math.random() * W, y2: Math.random() * H,
      vx1: (Math.random() - 0.5) * 0.25, vy1: (Math.random() - 0.5) * 0.25,
      vx2: (Math.random() - 0.5) * 0.25, vy2: (Math.random() - 0.5) * 0.25,
      alpha: (0.05 + Math.random() * 0.07) * alphaMul,
    }))

    const dots = Array.from({ length: dotCount }, () => ({
      x: Math.random() * W, y: Math.random() * H,
      r: (1.5 + Math.random() * 2.5) * intensity,
      vx: (Math.random() - 0.5) * 0.22, vy: (Math.random() - 0.5) * 0.22,
      alpha: (0.07 + Math.random() * 0.1) * alphaMul,
    }))

    const draw = () => {
      ctx.clearRect(0, 0, W, H)

      blobs.forEach(b => {
        b.x += b.vx; b.y += b.vy
        if (b.x < -b.r) b.x = W + b.r
        if (b.x > W + b.r) b.x = -b.r
        if (b.y < -b.r) b.y = H + b.r
        if (b.y > H + b.r) b.y = -b.r
        const g = ctx.createRadialGradient(b.x, b.y, 0, b.x, b.y, b.r)
        g.addColorStop(0, `hsla(${b.hue},78%,48%,${b.alpha})`)
        g.addColorStop(0.5, `hsla(${b.hue},72%,42%,${b.alpha * 0.4})`)
        g.addColorStop(1, `hsla(${b.hue},72%,42%,0)`)
        ctx.beginPath()
        ctx.arc(b.x, b.y, b.r, 0, Math.PI * 2)
        ctx.fillStyle = g
        ctx.fill()
      })

      lines.forEach(l => {
        l.x1 += l.vx1; l.y1 += l.vy1
        l.x2 += l.vx2; l.y2 += l.vy2
        ;[['x1','vx1'],['y1','vy1'],['x2','vx2'],['y2','vy2']].forEach(([pos, vel]) => {
          if (l[pos] < 0 || l[pos] > (pos.includes('x') ? W : H)) l[vel] *= -1
        })
        ctx.beginPath()
        ctx.moveTo(l.x1, l.y1)
        ctx.lineTo(l.x2, l.y2)
        ctx.strokeStyle = `rgba(197,48,48,${l.alpha})`
        ctx.lineWidth = 1.2 * intensity
        ctx.stroke()
      })

      dots.forEach(d => {
        d.x += d.vx; d.y += d.vy
        if (d.x < 0) d.x = W; if (d.x > W) d.x = 0
        if (d.y < 0) d.y = H; if (d.y > H) d.y = 0
        ctx.beginPath()
        ctx.arc(d.x, d.y, d.r, 0, Math.PI * 2)
        ctx.fillStyle = `rgba(197,48,48,${d.alpha})`
        ctx.fill()
      })

      animFrame = requestAnimationFrame(draw)
    }

    draw()

    cleanup = () => {
      cancelAnimationFrame(animFrame)
      window.removeEventListener('resize', resize)
    }
  }

  onMounted(() => init())
  onUnmounted(() => cleanup?.())

  return { canvasRef }
}

export default useAbstractBg
