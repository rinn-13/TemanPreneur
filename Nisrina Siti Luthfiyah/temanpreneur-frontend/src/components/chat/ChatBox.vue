<template>
  <div class="chat-container border rounded p-3">
    <div class="messages" style="max-height: 300px; overflow-y: auto;" ref="messageContainer">
      <div v-for="msg in messages" :key="msg.id" class="mb-2" :class="{'text-end': msg.sender_id === userId}">
        <div class="d-inline-block p-2 rounded" :class="msg.sender_id === userId ? 'bg-primary text-white' : 'bg-light'">
          {{ msg.message }}
        </div>
        <small class="d-block text-muted">{{ formatDate(msg.created_at) }}</small>
      </div>
    </div>
    <div class="input-group mt-2">
      <input type="text" class="form-control" v-model="newMessage" @keyup.enter="sendMessage" placeholder="Ketik pesan...">
      <button class="btn btn-primary" @click="sendMessage" :disabled="!newMessage.trim()">Kirim</button>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue'
import api from '@/api/axios'
import { formatDistanceToNow } from 'date-fns'
import { id } from 'date-fns/locale'

export default {
  props: ['orderId'],
  setup(props) {
    const messages = ref([])
    const newMessage = ref('')
    const userId = ref(localStorage.getItem('user_id')) // simpan user_id saat login
    const messageContainer = ref(null)
    let interval

    const fetchMessages = async () => {
      try {
        const { data } = await api.get(`/orders/${props.orderId}/chat`)
        messages.value = data
        scrollToBottom()
      } catch (error) {
        console.error(error)
      }
    }

    const sendMessage = async () => {
      if (!newMessage.value.trim()) return
      try {
        await api.post(`/orders/${props.orderId}/chat`, { message: newMessage.value })
        newMessage.value = ''
        await fetchMessages()
      } catch (error) {
        console.error(error)
      }
    }

    const scrollToBottom = () => {
      nextTick(() => {
        if (messageContainer.value) {
          messageContainer.value.scrollTop = messageContainer.value.scrollHeight
        }
      })
    }

    const formatDate = (date) => {
      return formatDistanceToNow(new Date(date), { addSuffix: true, locale: id })
    }

    onMounted(() => {
      fetchMessages()
      interval = setInterval(fetchMessages, 3000) // polling 3 detik
    })

    onUnmounted(() => {
      clearInterval(interval)
    })

    // Scroll ke bawah saat messages berubah
    watch(messages, scrollToBottom)

    return { messages, newMessage, userId, sendMessage, formatDate, messageContainer }
  }
}
</script>