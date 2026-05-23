<template>
  <header class="page-header">
    <div class="page-header__container">
      <!-- Back button (optional) -->
      <button
        v-if="showBack"
        class="page-header__back"
        @click="goBack"
        title="Kembali"
      >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
          <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Kembali
      </button>

      <!-- Title -->
      <div class="page-header__content">
        <h1 class="page-header__title">
          <template v-if="splitColor">
            <span class="page-header__title-dark">{{ firstWord }}</span>
            <span class="page-header__title-red">{{ restWords }}</span>
          </template>
          <template v-else>
            {{ title }}
          </template>
        </h1>
        <p v-if="subtitle" class="page-header__subtitle">{{ subtitle }}</p>
      </div>

      <!-- Actions slot -->
      <div class="page-header__actions">
        <slot name="actions"></slot>
      </div>
    </div>
  </header>
</template>

<script>
import { computed } from 'vue'
import { useRouter } from 'vue-router'

export default {
  name: 'PageHeader',
  props: {
    title: {
      type: String,
      required: true
    },
    subtitle: {
      type: String,
      default: ''
    },
    showBack: {
      type: Boolean,
      default: false
    }
  },
  setup(props) {
    const router = useRouter()

    const splitColor = computed(() => {
      return props.title && props.title.split(' ').length > 1
    })

    const firstWord = computed(() => {
      const words = props.title.split(' ')
      return words.shift() || props.title
    })

    const restWords = computed(() => {
      const words = props.title.split(' ')
      words.shift()
      return words.join(' ')
    })

    const goBack = () => {
      if (window.history.length > 1) {
        router.go(-1)
      } else {
        router.push('/')
      }
    }

    return { goBack, splitColor, firstWord, restWords }
  }
}
</script>

<style scoped>
.page-header {
  background: #fff;
  border-bottom: 1px solid var(--gray-100);
  padding: 24px 0;
  margin-bottom: 32px;
}

.page-header__container {
  max-width: var(--tp-container-max, 1280px);
  margin: 0 auto;
  padding: 0 var(--tp-container-pad, clamp(16px, 3vw, 28px));
  display: flex;
  align-items: center;
  gap: 24px;
}

.page-header__back {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border: 1.5px solid var(--gray-200);
  border-radius: var(--radius-lg);
  background: #fff;
  color: var(--gray-600);
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition);
  flex-shrink: 0;
}

.page-header__back:hover {
  border-color: var(--red-300);
  color: var(--red-600);
  background: var(--red-50);
}

.page-header__content {
  flex: 1;
}

.page-header__title {
  font-family: var(--font-display);
  font-size: clamp(1.8rem, 4vw, 2.4rem);
  font-weight: 900;
  color: var(--gray-900);
  margin: 0;
  letter-spacing: -0.02em;
  line-height: 1.1;
}
.page-header__title-dark {
  color: var(--gray-900);
}

.page-header__title-red {
  color: var(--red-600);
}
.page-header__subtitle {
  font-size: 1rem;
  color: var(--gray-600);
  margin: 8px 0 0;
  font-weight: 500;
  line-height: 1.5;
}

.page-header__actions {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 0;
}

@media (max-width: 768px) {
  .page-header {
    padding: 20px 0;
    margin-bottom: 24px;
  }

  .page-header__container {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }

  .page-header__back {
    align-self: flex-start;
  }

  .page-header__title {
    font-size: 1.6rem;
  }

  .page-header__actions {
    align-self: stretch;
    justify-content: flex-end;
  }
}

@media (max-width: 480px) {
  .page-header__title {
    font-size: 1.4rem;
  }
}
</style>