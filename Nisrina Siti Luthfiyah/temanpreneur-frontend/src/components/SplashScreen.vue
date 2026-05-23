<template>
  <transition name="splash-fade">
    <div v-if="show" class="splash-screen">
      <div class="splash-container">

        <!-- Logo -->
        <div class="splash-logo-wrapper">
          <img
            :src="logoUrl"
            alt="TemanPreneur"
            class="splash-logo-img"
          />
        </div>

        <!-- Text -->
        <div class="splash-text">
          <h1>
            <span class="splash-text-dark">Teman</span>
            <span class="splash-text-red">Preneur</span>
          </h1>

          <p>Platform Marketplace Siswa</p>
        </div>

        <!-- Loading -->
        <div class="splash-dots">
          <span></span>
          <span></span>
          <span></span>
        </div>

      </div>
    </div>
  </transition>
</template>

<script>
import { ref, onMounted } from 'vue'
import { normalizeImageUrl } from '@/utils/image'

export default {
  name: 'SplashScreen',

  setup() {
    const show = ref(false)

    // URL logo backend Laravel
    const logoUrl = normalizeImageUrl('/storage/logo1.png')
    onMounted(() => {
      const hasSplashShown = sessionStorage.getItem('splashShown')
      const justLoggedIn = sessionStorage.getItem('justLoggedIn')

      const shouldShow = !hasSplashShown || justLoggedIn

      if (shouldShow) {
        show.value = true

        sessionStorage.setItem('splashShown', 'true')
        sessionStorage.removeItem('justLoggedIn')

        setTimeout(() => {
          show.value = false
        }, 5000)
      }
    })

    return {
      show,
      logoUrl
    }
  }
}
</script>

<style scoped>
.splash-screen {
  position: fixed;
  inset: 0;
  background: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.splash-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 28px;
  text-align: center;
  padding: 20px;
}

.splash-logo-wrapper {
  width: 120px;
  height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.splash-logo-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  animation: splash-pop 0.7s ease;
}

.splash-text h1 {
  margin: 0;
  font-size: 32px;
  font-weight: 700;
  letter-spacing: -0.5px;
  animation: splash-text-in 0.6s ease;
}

.splash-text-dark {
  color: #111827;
}

.splash-text-red {
  color: #dc2626;
}

.splash-text p {
  margin-top: 8px;
  font-size: 14px;
  color: #6b7280;
  font-weight: 500;
}

.splash-dots {
  display: flex;
  gap: 8px;
}

.splash-dots span {
  width: 8px;
  height: 8px;
  border-radius: 999px;
  background: #dc2626;
  animation: splash-bounce 1.2s infinite ease-in-out;
}

.splash-dots span:nth-child(2) {
  animation-delay: 0.2s;
}

.splash-dots span:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes splash-pop {
  0% {
    transform: scale(0.5);
    opacity: 0;
  }

  100% {
    transform: scale(1);
    opacity: 1;
  }
}

@keyframes splash-text-in {
  0% {
    opacity: 0;
    transform: translateY(10px);
  }

  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes splash-bounce {
  0%,
  80%,
  100% {
    transform: scale(0.6);
    opacity: 0.5;
  }

  40% {
    transform: scale(1);
    opacity: 1;
  }
}

.splash-fade-enter-active,
.splash-fade-leave-active {
  transition: opacity 0.5s ease;
}

.splash-fade-enter-from,
.splash-fade-leave-to {
  opacity: 0;
}
</style>