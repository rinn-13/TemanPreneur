<template>
  <div class="blog-detail-page">
    <button class="back-btn" @click="$router.push('/blog')">← Kembali ke Blog</button>

    <div v-if="loading" class="loading">Memuat...</div>
    <div v-else-if="!article">
      <p>Artikel tidak ditemukan.</p>
    </div>
    <div v-else class="article">
      <div class="article-header">
        <span class="article-emoji">{{ article.emoji }}</span>
        <h1 class="article-title">{{ article.title }}</h1>
        <div class="article-meta">
          <span>{{ article.author }} • {{ article.date }}</span>
        </div>
      </div>
      <div v-if="article.image" class="article-hero">
        <img :src="article.image" :alt="article.title" />
      </div>
      <div class="article-body">
        <div v-if="article.content" v-html="formattedContent"></div>
        <p v-else>
          <!-- placeholder text for dummy articles -->
          Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus aliquet orci nec
          nibh pharetra, quis luctus purus porta. Ut eu risus non libero dignissim vulputate.
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'

export default {
  name: 'BlogDetail',
  props: ['slug'],
  setup(props) {
    const route = useRoute()
    const loading = ref(true)
    const article = ref(null)

    onMounted(async () => {
      const slug = props.slug || route.params.slug
      loading.value = true
      try {
        const { blogService } = await import('@/services/blog.js')
        const res = await blogService.getBlog(slug)
        article.value = res.data
      } catch (err) {
        console.error('Blog not found:', err)
        article.value = null
      } finally {
        loading.value = false
      }
    })

    const formattedContent = computed(() => {
      if (!article.value?.content) return ''
      const raw = String(article.value.content)
      const escaped = raw
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
      const paragraphs = escaped
        .split(/\r?\n\s*\r?\n/)
        .map((paragraph) => paragraph.trim())
        .filter(Boolean)
        .map((paragraph) => `<p>${paragraph.replace(/\r?\n/g, '<br>')}</p>`)
        .join('')
      return paragraphs
    })

    return { loading, article, formattedContent }
  }
}
</script>

<style scoped>
.blog-detail-page {
  max-width: 800px;
  margin: 0 auto;
  padding: 24px 16px;
  font-family: 'Plus Jakarta Sans', sans-serif;
}
.back-btn {
  background: none;
  border: none;
  color: #e53e3e;
  font-weight: 700;
  cursor: pointer;
  margin-bottom: 16px;
}
.article-header {
  text-align: center;
  margin-bottom: 24px;
}
.article-emoji {
  font-size: 4rem;
}
.article-title {
  font-size: 2rem;
  margin: 12px 0 8px;
}
.article-meta {
  font-size: .9rem;
  color: #6b7280;
}
.article-body {
  line-height: 1.8;
  font-size: 1rem;
}
.article-body p {
  margin: 0 0 1.3rem;
}
.article-body p:last-child {
  margin-bottom: 0;
}
.article-body br {
  display: inline;
}
.article-hero {
  margin: 18px 0 28px;
  text-align: center;
}
.article-hero img {
  width: 100%;
  max-height: 420px;
  object-fit: cover;
  border-radius: 12px;
  display: block;
}
.loading {
  text-align: center;
  padding: 40px 0;
}
</style>