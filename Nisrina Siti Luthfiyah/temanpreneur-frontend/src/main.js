// src/main.js
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap'
import 'bootstrap-icons/font/bootstrap-icons.css'
import '@/assets/design-tokens.css'
import '@/assets/layout.css'
import imagePlugin from '@/plugins/imagePlugin'

// Import komponen global
import PageHeader from '@/components/PageHeader.vue'
import ToastContainer from '@/components/ToastContainer.vue'
import TpImage from '@/components/TpImage.vue'

const pinia = createPinia()
const app = createApp(App)

app.use(pinia)
app.use(router)
app.use(imagePlugin)

// Daftarkan komponen global
app.component('PageHeader', PageHeader)
app.component('ToastContainer', ToastContainer)
app.component('TpImage', TpImage)

app.mount('#app')

