<template>
  <section class="team-mgmt">
    <header class="team-mgmt__head">
      <h1 class="team-mgmt__title"> Kelola Tim</h1>
      <p class="team-mgmt__sub">Undang anggota tim untuk membantu mengelola toko Anda.</p>
    </header>

    <!-- Invite Form -->
    <div class="team-mgmt__invite">
      <h3>Undang Anggota Baru</h3>
      <form @submit.prevent="sendInvitation" class="team-mgmt__form">
        <div class="form-group">
          <label>Email User yang akan diundang:</label>
          <input
            v-model="inviteForm.email"
            type="email"
            required
            placeholder="user@example.com"
            class="form-control"
          />
        </div>
        <div class="form-group">
          <label>Hak Akses:</label>
          <div class="checkbox-group">
            <label class="checkbox-label">
              <input v-model="inviteForm.permissions" type="checkbox" value="manage_products" />
              Kelola Produk
            </label>
            <label class="checkbox-label">
              <input v-model="inviteForm.permissions" type="checkbox" value="manage_orders" />
              Kelola Pesanan
            </label>
            <label class="checkbox-label">
              <input v-model="inviteForm.permissions" type="checkbox" value="view_analytics" />
              Lihat Analitik
            </label>
          </div>
        </div>
        <button type="submit" :disabled="inviting" class="btn btn-primary">
          {{ inviting ? 'Mengirim...' : 'Kirim Undangan' }}
        </button>
      </form>
    </div>

    <!-- Team Members -->
    <div class="team-mgmt__members">
      <h3>Anggota Tim</h3>
      <div v-if="loading" class="loading">Memuat...</div>
      <div v-else-if="teamMembers.length === 0" class="empty-state">
        Belum ada anggota tim. Undang anggota pertama Anda!
      </div>
      <div v-else class="members-list">
        <div v-for="member in teamMembers" :key="member.id" class="member-card">
          <div class="member-info">
            <div class="member-avatar">
              <img v-if="member.user.photo" :src="member.user.photo" :alt="member.user.name" />
              <div v-else class="member-initial">{{ member.user.name.charAt(0) }}</div>
            </div>
            <div class="member-details">
              <h4>{{ member.user.name }}</h4>
              <p>{{ member.user.email }}</p>
              <span class="member-role">{{ member.role }}</span>
            </div>
          </div>
          <div class="member-actions">
            <button @click="removeMember(member)" class="btn btn-sm btn-danger">Hapus</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Invitations -->
    <div class="team-mgmt__invitations">
      <h3>Undangan Terkirim</h3>
      <div v-if="loading" class="loading">Memuat...</div>
      <div v-else-if="sentInvitations.length === 0" class="empty-state">
        Belum ada undangan yang dikirim.
      </div>
      <div v-else class="invitations-list">
        <div v-for="invitation in sentInvitations" :key="invitation.id" class="invitation-card">
          <div class="invitation-info">
            <h4>{{ invitation.invited.name }}</h4>
            <p>{{ invitation.invited.email }}</p>
            <span class="invitation-status" :class="`status--${invitation.status}`">
              {{ invitation.status === 'pending' ? 'Menunggu' :
                 invitation.status === 'accepted' ? 'Diterima' : 'Ditolak' }}
            </span>
          </div>
          <div class="invitation-actions">
            <small>Dikirim: {{ formatDate(invitation.created_at) }}</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast -->
    <div v-if="toast.show" class="toast" :class="{ 'toast--error': toast.err }">
      {{ toast.msg }}
    </div>
  </section>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'

export default {
  name: 'TeamManagement',
  setup() {
    const authStore = useAuthStore()
    const teamMembers = ref([])
    const sentInvitations = ref([])
    const loading = ref(true)
    const inviting = ref(false)
    const toast = ref({ show: false, msg: '', err: false })

    const inviteForm = ref({
      email: '',
      permissions: ['manage_products', 'manage_orders']
    })

    const showToast = (msg, err = false) => {
      toast.value = { show: true, msg, err }
      setTimeout(() => toast.value.show = false, 3000)
    }

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('id-ID')
    }

    const fetchTeamData = async () => {
      loading.value = true
      try {
        // Fetch team members
        if (authStore.user.business) {
          const membersRes = await api.get(`/businesses/${authStore.user.business.id}/team`)
          teamMembers.value = membersRes.data.data || []
        }

        // Fetch sent invitations
        const invitationsRes = await api.get('/team-invitations')
        sentInvitations.value = invitationsRes.data.data.sent || []
      } catch (error) {
        showToast('Gagal memuat data tim', true)
      } finally {
        loading.value = false
      }
    }

    const sendInvitation = async () => {
      if (!inviteForm.value.email) {
        showToast('Email harus diisi', true)
        return
      }

      inviting.value = true
      try {
        // First, find user by email
        const usersRes = await api.get('/admin/users', {
          params: { search: inviteForm.value.email }
        })
        const user = usersRes.data.data?.find(u => u.email === inviteForm.value.email)

        if (!user) {
          showToast('User dengan email tersebut tidak ditemukan', true)
          return
        }

        const payload = {
          business_id: authStore.user.business.id,
          invited_user_id: user.id,
          permissions: inviteForm.value.permissions
        }

        await api.post('/team-invitations', payload)

        showToast('Undangan berhasil dikirim!')
        inviteForm.value.email = ''
        inviteForm.value.permissions = ['manage_products', 'manage_orders']
        fetchTeamData()
      } catch (error) {
        showToast(error.response?.data?.message || 'Gagal mengirim undangan', true)
      } finally {
        inviting.value = false
      }
    }

    const removeMember = async (member) => {
      if (!confirm('Apakah Anda yakin ingin menghapus anggota tim ini?')) return

      try {
        await api.delete(`/businesses/${authStore.user.business.id}/team/${member.id}`)
        showToast('Anggota tim berhasil dihapus')
        fetchTeamData()
      } catch (error) {
        showToast('Gagal menghapus anggota tim', true)
      }
    }

    onMounted(() => {
      if (authStore.user.business) {
        fetchTeamData()
      } else {
        showToast('Anda belum memiliki bisnis', true)
      }
    })

    return {
      teamMembers,
      sentInvitations,
      loading,
      inviting,
      inviteForm,
      toast,
      sendInvitation,
      removeMember,
      formatDate
    }
  }
}
</script>

<style scoped>
.team-mgmt {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.team-mgmt__head {
  margin-bottom: 2rem;
}

.team-mgmt__title {
  font-size: 2rem;
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.team-mgmt__sub {
  color: #666;
}

.team-mgmt__invite,
.team-mgmt__members,
.team-mgmt__invitations {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.team-mgmt__form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.checkbox-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.members-list,
.invitations-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.member-card,
.invitation-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
}

.member-info,
.invitation-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.member-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f0f0f0;
}

.member-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.member-initial {
  font-weight: bold;
  color: #666;
}

.member-role {
  background: #e3f2fd;
  color: #1976d2;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.875rem;
}

.invitation-status {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.875rem;
}

.status--pending {
  background: #fff3cd;
  color: #856404;
}

.status--accepted {
  background: #d4edda;
  color: #155724;
}

.status--declined {
  background: #f8d7da;
  color: #721c24;
}

.empty-state {
  text-align: center;
  color: #666;
  padding: 2rem;
}

.loading {
  text-align: center;
  padding: 2rem;
}

.toast {
  position: fixed;
  top: 20px;
  right: 20px;
  background: #4caf50;
  color: white;
  padding: 1rem;
  border-radius: 8px;
  z-index: 1000;
}

.toast--error {
  background: #f44336;
}

.btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}

.btn-primary {
  background: #1976d2;
  color: white;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

.btn-danger {
  background: #dc3545;
  color: white;
}

.form-control {
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
}
</style>