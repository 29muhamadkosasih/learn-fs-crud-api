import { createApp } from 'vue'
import './style.css'
import 'sweetalert2/dist/sweetalert2.min.css'
import App from './App.vue'
import router from './router'

createApp(App).use(router).mount('#app')
