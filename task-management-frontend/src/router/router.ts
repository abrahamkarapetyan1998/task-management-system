import { createRouter, createWebHistory } from 'vue-router'
import HomeView from "../components/HomeView.vue";
import Login from "../components/Auth/Login.vue";
import Dashboard from "../components/Dashboard.vue";
import Admin from '../components/Admin.vue';

const routes = [
    {
        path: '/efefe',
        name: 'Home',
        component: () =>  HomeView,
    },
    {
        path: '/login',
        name: 'login',
        component: () =>  Login,
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: () =>  Dashboard,
    },
    {
        path: '/admin',
        name: 'admin',
        component: () =>  Admin,
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

export default router