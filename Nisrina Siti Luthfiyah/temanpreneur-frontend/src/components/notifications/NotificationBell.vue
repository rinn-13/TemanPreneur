<template>
  <div class="dropdown">
    <button class="btn btn-light position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
      <i class="bi bi-bell"></i>
      <span v-if="unreadCount > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
        {{ unreadCount }}
      </span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end p-2" style="width: 300px;">
      <li v-for="notif in notifications" :key="notif.id" class="border-bottom pb-2 mb-2">
        <strong>{{ notif.title }}</strong>
        <p class="mb-0 small">{{ notif.message }}</p>
        <small class="text-muted">{{ formatDate(notif.created_at) }}</small>
      </li>
      <li v-if="notifications.length === 0" class="text-center text-muted">
        Tidak ada notifikasi
      </li>
    </ul>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue'
import api from '@/api/axios'
import { formatDistanceToNow } from 'date-fns'
import { id } from 'date-fns/locale'

export default {
  name: 'NotificationBell',
  setup() {
    const notifications = ref([])
    const unreadCount = ref(0)
    let interval

    const fetchData = async () => {
      try {
        const notifRes = await api.get('/notifications?limit=5')
        notifications.value = notifRes.data.data.slice(0, 5)
        const countRes = await api.get('/notifications/unread-count')
        unreadCount.value = countRes.data.count
      } catch (error) {
        console.error('Gagal mengambil notifikasi', error)
      }
    }

    onMounted(() => {
      fetchData()
      interval = setInterval(fetchData, 5000) // polling setiap 5 detik
    })

    onUnmounted(() => {
      clearInterval(interval)
    })

    const formatDate = (date) => {
      return formatDistanceToNow(new Date(date), { addSuffix: true, locale: id })
    }

    return { notifications, unreadCount, formatDate }
  }
}
</script>