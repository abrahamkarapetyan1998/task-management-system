import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import { createWebHistory, createRouter } from 'vue-router'
import HomeView from "./components/HomeView.vue";
import Login from "./components/Auth/Login.vue";
import Register from "./components/Auth/Register.vue";
import Dashboard from "./components/Dashboard.vue";
import Admin from "./components/Admin.vue";

const routes = [
    { path: '/', component: HomeView },
    { path: '/login', component: Login },
    { path: '/register', component: Register },
    { path: '/dashboard', component: Dashboard },
    { path: '/admin', component: Admin },

]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

createApp(App).use(router).mount('#app')