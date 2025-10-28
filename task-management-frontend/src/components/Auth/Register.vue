<script setup>
import { onMounted, ref } from "vue";
import { useRouter } from 'vue-router'
import axios from "axios";
import { useAuth } from "../../composables/useAuth.js"; // не нужно переименовывать
import NavBar from "../NavBar.vue";

const router = useRouter()
const email = ref('')
const name = ref('')
const password = ref('')
const password_confirmation = ref('')
const errors = ref({ email: '', name: '', password: '', password_confirmation: '' })

const { fetchUser } = useAuth() // ✅ вызываем useAuth, чтобы получить методы

axios.defaults.baseURL = 'http://127.0.0.1:8000'
axios.defaults.withCredentials = true

onMounted(async () => {
  const token = localStorage.getItem('authToken')
  if (token) {
    await router.push('/')
  }
})

async function register() {
  errors.value = { email: '', name: '', password: '', password_confirmation: '' }

  try {
    await axios.get('/sanctum/csrf-cookie')

    const response = await axios.post('/api/register', {
      email: email.value,
      name: name.value,
      password: password.value,
      password_confirmation: password_confirmation.value
    })

    await fetchUser()
    await router.push({ path: '/login', query: { registered: '1' } })
  } catch (e) {
    if (e.response?.data?.errors) {
      const serverErrors = e.response.data.errors
      errors.value.email = serverErrors.email?.[0] || ''
      errors.value.name = serverErrors.name?.[0] || ''
      errors.value.password = serverErrors.password?.[0] || ''
      errors.value.password_confirmation = serverErrors.password_confirmation?.[0] || ''
    } else {
      console.error(e)
    }
  }
}
</script>


<template>
  <NavBar />
  <section class="bg-gray-50 dark:bg-gray-900">
    <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
      <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-gray-900 dark:text-white">
        <img class="w-8 h-8 mr-2" src="https://flowbite.s3.amazonaws.com/blocks/marketing-ui/logo.svg" alt="logo">
        Task Manager
      </a>
      <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
        <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
          <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
            Create An Account
          </h1>
          <form @submit.prevent="register" class="space-y-4 md:space-y-6">
            <div>
              <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your email</label>
              <input
                  v-model="email"
                  type="email"
                  name="email"
                  id="email"
                  class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5
                         dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                  placeholder="name@company.com"
                  required
              >
              <p v-if="errors.email" class="mt-2 text-xs text-red-600 dark:text-red-400">
                <span class="font-medium">Oh, snap!</span> {{ errors.email }}
              </p>
            </div>

            <div>
              <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
              <input v-model="name" type="text" name="name" id="name" placeholder="Enter Your Name"
                     class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5
                            dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
              <p v-if="errors.name" class="mt-2 text-xs text-red-600 dark:text-red-400">
                <span class="font-medium">Oh, snap!</span> {{ errors.name }}
              </p>
            </div>

            <div>
              <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
              <input v-model="password" type="password" name="password" id="password" placeholder="••••••••"
                     class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5
                            dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
              <p v-if="errors.password" class="mt-2 text-xs text-red-600 dark:text-red-400">
                <span class="font-medium">Oh, snap!</span> {{ errors.password }}
              </p>
            </div>

            <div>
              <label for="confirm_password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Confirm Password</label>
              <input v-model="password_confirmation" type="password" name="password_confirmation" id="confirm_password" placeholder="••••••••"
                     class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5
                            dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
              <p v-if="errors.password_confirmation" class="mt-2 text-xs text-red-600 dark:text-red-400">
                <span class="font-medium">Oh, snap!</span> {{ errors.password_confirmation }}
              </p>
            </div>

            <button
                type="submit"
                class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center"
            >
              Sign Up
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>
</template>
