<script setup>
  import { ref, onMounted } from 'vue'
  import axios from 'axios'
  import { useAuth } from '../composables/useAuth.js'
  import TaskSearch from "./Components/TaskSearch.vue";
  import NavBar from "./NavBar.vue";

  const tasks = ref([])
  const error = ref('')
  const auth = useAuth()

  axios.defaults.baseURL = 'http://127.0.0.1:8000'
  axios.defaults.withCredentials = true
  const successMessage = ref('')

  async function updateStatus(task) {
    try {
      const token = localStorage.getItem('authToken')
      await axios.patch(
          `/api/tasks/${task.id}`,
          { status: task.status },
          { headers: { Authorization: `Bearer ${token}` } }
      )
      successMessage.value = 'Status updated successfully!'
      setTimeout(() => (successMessage.value = ''), 3000)
    } catch (err) {
      console.error(err)
      successMessage.value = 'Failed to update status'
      setTimeout(() => (successMessage.value = ''), 3000)
    }
  }
  async function fetchTasks() {
  try {
    const token = localStorage.getItem('authToken')
    const  user = await auth.fetchUser()

    const { data } = await axios.get(`/api/tasks/${user.id}`, {
      headers: { Authorization: `Bearer ${token}` },
    })
    tasks.value = data.data
    console.log(tasks.value)
  } catch (err) {
  error.value = 'Ошибка при загрузке данных'
  console.error(err)
}
}
onMounted(() => {
  fetchTasks()
})

</script>

<template>
  <NavBar />
  <TaskSearch @tasksFiltered="tasks = $event" :showUserFilter="false" />

  <h1>Tasks</h1>
  <div class="relative overflow-x-auto overflow-y-auto max-h-[500px] shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
      <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
      <tr>
        <th class="px-6 py-3">Task Name</th>
        <th class="px-6 py-3">Description</th>
        <th class="px-6 py-3">Start Date</th>
        <th class="px-6 py-3">End Date</th>
        <th class="px-6 py-3">Status</th>
        <th class="px-6 py-3">User ID</th>
      </tr>
      </thead>
      <tbody>
      <tr
          v-for="task in tasks"
          :key="task.id"
          class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700 border-gray-200"
      >
        <th class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ task.title }}</th>
        <td class="px-6 py-4">{{ task.description }}</td>
        <td class="px-6 py-4">{{ task.start_date }}</td>
        <td class="px-6 py-4">{{ task.end_date }}</td>
        <td class="px-6 py-4">
          <select
              v-model="task.status"
              @change="updateStatus(task)"
              class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg px-2 py-1 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          >
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="done">Done</option>
            <option value="blocked">Blocked</option>
            <option value="canceled">Canceled</option>
          </select>
          <div v-if="successMessage" class="mb-2 text-green-600 dark:text-green-400">
          {{ successMessage }}
        </div>
        </td>
        <td class="px-6 py-4">{{ task.user_id }}</td>

      </tr>
      </tbody>
    </table>
  </div>


</template>

<style scoped>

</style>