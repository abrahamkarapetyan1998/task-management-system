
<script setup>
import { computed, onMounted } from 'vue'
import { useAuth } from '../composables/useAuth.js'

const auth = useAuth()

onMounted(async () => {
  const token = localStorage.getItem('authToken')
  if (token) {
    await auth.fetchUser()
  }
})

const hasManagerRole = computed(() =>
    auth.user.value?.roles?.some(r => r.name === 'manager')
)
</script>

<template>
  <nav class="fixed top-0 left-0 w-full z-50 bg-white border-gray-200 dark:bg-gray-900 shadow-md">
    <div class="flex flex-wrap items-center justify-between px-6 py-3">
      <RouterLink to="/" class="flex items-center space-x-3 rtl:space-x-reverse">
        <img src="https://flowbite.com/docs/images/logo.svg" class="h-8" alt="Flowbite Logo" />
        <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">Task Manager</span>
      </RouterLink>
      <div class="space-x-4">
        <RouterLink v-if="auth.user.value" to="/dashboard">Dashboard</RouterLink>
        <RouterLink v-if="hasManagerRole" to="/admin">Admin</RouterLink>

        <RouterLink v-if="!auth.user.value" to="/login">Login</RouterLink>
        <RouterLink v-if="!auth.user.value" to="/register">Register</RouterLink>

        <RouterLink v-if="auth.user.value" @click.prevent="auth.logout" to="#">Logout</RouterLink>

      </div>
    </div>
  </nav>
</template>