<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { useAuth } from '../../composables/useAuth.js'
import NavBar from "../NavBar.vue";

const route = useRoute()
const router = useRouter()
const email = ref('')
const password = ref('')
const errors = ref({ email: '', password: '' })
const successMessage = ref('')

onMounted(() => {
  if (route.query.registered === '1') {
    successMessage.value = 'Registration SuccessFull, please enter your credentials to log in :)'
  }
})

axios.defaults.baseURL = 'http://127.0.0.1:8000'
axios.defaults.withCredentials = true
const auth = useAuth()

async function login() {
  errors.value = { email: '', password: '' }

  try {
    await axios.get('/sanctum/csrf-cookie')

    const response = await axios.post('/api/login', {
      email: email.value,
      password: password.value,
    })

    localStorage.setItem('authToken', response.data.token)
    await auth.fetchUser()
    await router.push('/')
  } catch (e) {
    if (e.response?.data?.errors) {
      const serverErrors = e.response.data.errors
      errors.value.email = serverErrors.email?.[0] || ''
      errors.value.password = serverErrors.password?.[0] || ''
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
            Sign in to your account
          </h1>

          <p v-if="successMessage" class="text-green-600 text-sm mb-4 font-medium text-center">
            {{ successMessage }}
          </p>

          <form @submit.prevent="login" class="space-y-4 md:space-y-6">
            <div>
              <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your email</label>
              <input v-model="email" type="email" name="email" id="email" placeholder="name@company.com"
                     class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600
                            focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600
                            dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
              <p v-if="errors.email" class="mt-2 text-xs text-red-600 dark:text-red-400">
                <span class="font-medium">Oh, snap!</span> {{ errors.email }}
              </p>
            </div>

            <div>
              <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
              <input v-model="password" type="password" name="password" id="password" placeholder="••••••••"
                     class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600
                            focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600
                            dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
              <p v-if="errors.password" class="mt-2 text-xs text-red-600 dark:text-red-400">
                <span class="font-medium">Oh, snap!</span> {{ errors.password }}
              </p>
            </div>

            <button type="submit"
                    class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none
                           focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
              Sign in
            </button>
          </form>

        </div>
      </div>
    </div>
  </section>
</template>
