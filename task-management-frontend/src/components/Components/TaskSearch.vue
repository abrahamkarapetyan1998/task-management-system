<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'
import { Datepicker } from 'flowbite'

const props = defineProps({
  showUserFilter: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(['tasksFiltered'])

const searchQuery = ref('')
const startInput = ref(null)
const endInput = ref(null)
const startDate = ref('')
const endDate = ref('')
const userId = ref('')
const users = ref([])
const showUsers = ref(false)
const status = ref('')

const statuses = [
  { value: '', label: 'All statuses' },
  { value: 'pending', label: 'Pending' },
  { value: 'in_progress', label: 'In progress' },
  { value: 'done', label: 'Done' },
  { value: 'blocked', label: 'Blocked' },
  { value: 'canceled', label: 'Canceled' },
]

axios.defaults.baseURL = 'http://127.0.0.1:8000'
axios.defaults.withCredentials = true

const fetchFilteredTasks = async () => {
  try {
    const token = localStorage.getItem('authToken')
    const params = {
      search: searchQuery.value || undefined,
      user_id: props.showUserFilter ? userId.value || undefined : undefined,
      start_date: startDate.value || undefined,
      end_date: endDate.value || undefined,
      status: status.value || undefined,
    }

    const { data } = await axios.get('/api/tasks', {
      params,
      headers: { Authorization: `Bearer ${token}` },
    })

    emit('tasksFiltered', data)
  } catch (err) {
    console.error('Ошибка при поиске задач:', err)
  }
}

const searchUsers = async () => {
  if (!props.showUserFilter || searchQuery.value.length < 2) {
    users.value = []
    return
  }

  try {
    const { data } = await axios.get(`/api/users/search?query=${searchQuery.value}`)
    users.value = data
    showUsers.value = true
  } catch (err) {
    console.error(err)
  }
}

function selectUser(user) {
  userId.value = user.id
  searchQuery.value = user.name
  showUsers.value = false
  fetchFilteredTasks()
}

watch([searchQuery, startDate, endDate, status], fetchFilteredTasks)

onMounted(() => {
  if (startInput.value) {
    const startPicker = new Datepicker(startInput.value, { autohide: true, format: 'yyyy-mm-dd' })
    startInput.value.addEventListener('changeDate', (e) => {
      const date = e.detail?.datepicker?.getDate()
      if (date) {
        startDate.value = date.toISOString().split('T')[0]
        fetchFilteredTasks()
      }
    })
  }

  if (endInput.value) {
    const endPicker = new Datepicker(endInput.value, { autohide: true, format: 'yyyy-mm-dd' })
    endInput.value.addEventListener('changeDate', (e) => {
      const date = e.detail?.datepicker?.getDate()
      if (date) {
        endDate.value = date.toISOString().split('T')[0]
        fetchFilteredTasks()
      }
    })
  }
})
</script>

<template>
  <div class="flex flex-col md:flex-row flex-wrap gap-4 mb-4 items-start md:items-end">
    <div class="w-full md:w-1/3 relative">
      <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
        Search by title or description
      </label>
      <input
          v-model="searchQuery"
          @input="searchUsers"
          type="text"
          class="border border-gray-300 rounded-lg p-2 w-full dark:bg-gray-700 dark:text-white"
          placeholder="Type to search..."
      />
      <ul
          v-if="props.showUserFilter && showUsers && users.length"
          class="absolute bg-white dark:bg-gray-700 border border-gray-300 rounded-lg mt-1 w-full z-10 max-h-48 overflow-y-auto"
      >
        <li
            v-for="user in users"
            :key="user.id"
            @click="selectUser(user)"
            class="px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-600 cursor-pointer"
        >
          {{ user.name }}
        </li>
      </ul>
    </div>

    <div>
      <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Start date</label>
      <input
          ref="startInput"
          type="text"
          placeholder="Select start date"
          class="border border-gray-300 rounded-lg p-2 dark:bg-gray-700 dark:text-white"
      />
    </div>

    <div>
      <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">End date</label>
      <input
          ref="endInput"
          type="text"
          placeholder="Select end date"
          class="border border-gray-300 rounded-lg p-2 dark:bg-gray-700 dark:text-white"
      />
    </div>

    <div>
      <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
      <select
          v-model="status"
          class="border border-gray-300 rounded-lg p-2 dark:bg-gray-700 dark:text-white"
      >
        <option
            v-for="option in statuses"
            :key="option.value"
            :value="option.value"
        >
          {{ option.label }}
        </option>
      </select>
    </div>
  </div>
</template>
