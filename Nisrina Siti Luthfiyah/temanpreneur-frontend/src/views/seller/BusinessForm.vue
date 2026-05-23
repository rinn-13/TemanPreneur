<template>
  <div class="container mt-4" style="max-width: 600px;">
    <h2>Ajukan Usaha</h2>
    <form @submit.prevent="submit">
      <div class="mb-3">
        <label for="name" class="form-label">Nama Usaha</label>
        <input type="text" class="form-control" id="name" v-model="form.name" required>
      </div>
      <div class="mb-3">
        <label for="description" class="form-label">Deskripsi</label>
        <textarea class="form-control" id="description" rows="3" v-model="form.description"></textarea>
      </div>
      <div v-if="error" class="alert alert-danger">{{ error }}</div>
      <button type="submit" class="btn btn-primary" :disabled="loading">
        <span v-if="loading" class="spinner-border spinner-border-sm"></span>
        Ajukan
      </button>
    </form>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/axios'

export default {
  name: 'BusinessForm',
  setup() {
    const router = useRouter()
    const form = ref({ name: '', description: '' })
    const error = ref('')
    const loading = ref(false)

    const submit = async () => {
      error.value = ''
      loading.value = true
      try {
        await api.post('/businesses', form.value)
        router.push('/seller/dashboard')
      } catch (err) {
        error.value = err.response?.data?.message || 'Gagal mengajukan usaha'
      } finally {
        loading.value = false
      }
    }

    return { form, error, loading, submit }
  }
}
</script>