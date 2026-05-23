<template>
  <div class="issue-notif-center">
    <!-- Notif center button/icon -->
    <button class="issue-notif-btn" @click="togglePanel" :class="{ 'issue-notif-btn--active': panelOpen }">
      <span class="issue-notif-icon"></span>
      <span v-if="unreadCount > 0" class="issue-notif-badge">{{ unreadCount }}</span>
    </button>

    <!-- Notification panel -->
    <div v-if="panelOpen" class="issue-notif-panel">
      <div class="issue-notif-header">
        <h3>Notifikasi Penanganan Laporan</h3>
        <button class="issue-notif-close" @click="panelOpen = false" aria-label="Tutup">×</button>
      </div>

      <div class="issue-notif-list">
        <!-- Loading -->
        <div v-if="loading" class="issue-notif-loading">
          <span>⏳ Memuat...</span>
        </div>

        <!-- Notifications -->
        <div v-else-if="notifications.length">
          <div
            v-for="notif in notifications"
            :key="notif.id"
            class="issue-notif-item"
            :class="{ 'issue-notif-item--unread': !notif.is_read }"
            @click="goToDetail(notif)"
          >
            <div class="issue-notif-dot" v-if="!notif.is_read"></div>
            <div class="issue-notif-content">
              <p class="issue-notif-title">{{ notif.title }}</p>
              <p class="issue-notif-message">{{ notif.message }}</p>
              <span class="issue-notif-time">{{ formatTime(notif.created_at) }}</span>
            </div>
            <div class="issue-notif-icon-right">→</div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-else class="issue-notif-empty">
          <span style="font-size: 2rem;">∅</span>
          <p>Tidak ada notifikasi</p>
        </div>
      </div>

      <div class="issue-notif-footer">
        <button class="issue-notif-link" @click="goToAll">Lihat semua →</button>
      </div>
    </div>

    <!-- Backdrop -->
    <div v-if="panelOpen" class="issue-notif-backdrop" @click="panelOpen = false"></div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import api from '@/api/axios'

export default {
  name: 'IssueReportNotificationCenter',
  setup() {
    const panelOpen = ref(false)
    const notifications = ref([])
    const loading = ref(false)
    let pollInterval = null

    const unreadCount = computed(() => {
      return notifications.value.filter(n => !n.is_read).length
    })

    const togglePanel = () => {
      panelOpen.value = !panelOpen.value
    }

    const formatTime = (dateString) => {
      const date = new Date(dateString)
      const now = new Date()
      const diffMs = now - date
      const diffMins = Math.floor(diffMs / 60000)
      const diffHours = Math.floor(diffMs / 3600000)
      const diffDays = Math.floor(diffMs / 86400000)

      if (diffMins < 1) return 'Baru saja'
      if (diffMins < 60) return `${diffMins}m yang lalu`
      if (diffHours < 24) return `${diffHours}h yang lalu`
      if (diffDays < 7) return `${diffDays}d yang lalu`

      return date.toLocaleDateString('id-ID')
    }

    const fetchNotifications = async () => {
      try {
        loading.value = true
        const res = await api.get('/notifications?limit=10&type=issue_report_response')
        notifications.value = res.data.data || []
      } catch (error) {
        console.error('Failed to fetch notifications:', error)
      } finally {
        loading.value = false
      }
    }

    const goToDetail = async (notif) => {
      try {
        // Mark as read
        if (!notif.is_read) {
          await api.post(`/notifications/${notif.id}/read`)
          notif.is_read = true
        }
        // Navigate to detail
        window.location.href = `/notification-detail/${notif.related_id}?notificationId=${notif.id}`
      } catch (error) {
        console.error('Failed to navigate:', error)
      }
    }

    const goToAll = () => {
      window.location.href = '/notifications'
    }

    onMounted(() => {
      fetchNotifications()
      // Poll every 30 seconds
      pollInterval = setInterval(fetchNotifications, 30000)
    })

    onUnmounted(() => {
      if (pollInterval) {
        clearInterval(pollInterval)
      }
    })

    return {
      panelOpen,
      notifications,
      unreadCount,
      loading,
      togglePanel,
      formatTime,
      goToDetail,
      goToAll,
    }
  }
}
</script>

<style scoped>
.issue-notif-center {
  position: relative;
}

.issue-notif-btn {
  position: relative;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.5rem;
  padding: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  transition: all 0.2s;
}

.issue-notif-btn:hover {
  background: #f3f4f6;
}

.issue-notif-btn--active {
  background: #f0fdf4;
}

.issue-notif-badge {
  position: absolute;
  top: 0;
  right: 0;
  background: #ef4444;
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  font-weight: 700;
}

.issue-notif-panel {
  position: absolute;
  top: 100%;
  right: 0;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  width: 350px;
  max-height: 500px;
  display: flex;
  flex-direction: column;
  z-index: 1000;
  margin-top: 8px;
}

.issue-notif-header {
  padding: 16px;
  border-bottom: 1.5px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.issue-notif-header h3 {
  font-size: 1rem;
  color: #1f2937;
  margin: 0;
  font-weight: 700;
}

.issue-notif-close {
  background: none;
  border: none;
  cursor: pointer;
  color: #6b7280;
  font-size: 1.2rem;
  padding: 0;
}

.issue-notif-list {
  flex: 1;
  overflow-y: auto;
  max-height: 350px;
}

.issue-notif-item {
  padding: 12px 16px;
  border-bottom: 1px solid #f3f4f6;
  display: flex;
  gap: 12px;
  align-items: flex-start;
  cursor: pointer;
  transition: all 0.2s;
}

.issue-notif-item:hover {
  background: #f9fafb;
}

.issue-notif-item--unread {
  background: #f0fdf4;
}

.issue-notif-dot {
  width: 8px;
  height: 8px;
  background: #10b981;
  border-radius: 50%;
  flex-shrink: 0;
  margin-top: 6px;
}

.issue-notif-content {
  flex: 1;
  min-width: 0;
}

.issue-notif-title {
  font-size: 0.9rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.issue-notif-message {
  font-size: 0.8rem;
  color: #6b7280;
  margin: 0 0 6px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.issue-notif-time {
  font-size: 0.75rem;
  color: #9ca3af;
}

.issue-notif-icon-right {
  color: #d1d5db;
  font-size: 1rem;
  flex-shrink: 0;
}

.issue-notif-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  color: #6b7280;
}

.issue-notif-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  color: #9ca3af;
}

.issue-notif-footer {
  padding: 12px 16px;
  border-top: 1.5px solid #e5e7eb;
  text-align: center;
}

.issue-notif-link {
  background: none;
  border: none;
  color: #10b981;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.85rem;
  transition: color 0.2s;
}

.issue-notif-link:hover {
  color: #059669;
}

.issue-notif-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 999;
}
</style>
