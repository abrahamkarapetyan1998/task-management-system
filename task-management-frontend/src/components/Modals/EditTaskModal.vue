<script setup>
import { ref, onMounted, nextTick } from 'vue'
import axios from 'axios'
import { Modal, initModals, Datepicker } from 'flowbite'

const emit = defineEmits(['taskUpdated'])
const modalRef = ref(null)
const startInput = ref(null)
const endInput = ref(null)
let modalInstance = null

const task = ref({
  id: null,
  title: '',
  description: '',
  start_date: '',
  end_date: '',
  status: 'pending',
  user_id: null
})

const users = ref([])
const query = ref('')
const selectedUser = ref(null)
const error = ref('')
const errorMessage = ref('')

axios.defaults.baseURL = 'http://127.0.0.1:8000'
axios.defaults.withCredentials = true

onMounted(() => {
  modalInstance = new Modal(modalRef.value)
  initModals()
})

function open(taskData) {
  task.value = { ...taskData }
  query.value = selectedUser.value?.name || ''

  if (taskData.user) {
    selectedUser.value = { ...taskData.user }
    query.value = selectedUser.value.name
  } else {
    selectedUser.value = null
    query.value = ''
  }
  nextTick(() => {
    modalInstance.show()

    if (startInput.value) {
      const startPicker = new Datepicker(startInput.value, { autohide: true, format: 'yyyy-mm-dd' })
      startPicker.setDate(new Date(task.value.start_date))
      startInput.value.addEventListener('changeDate', (e) => {
        const date = e.detail?.datepicker?.getDate()
        if (date) task.value.start_date = date.toISOString().split('T')[0]
      })
    }

    if (endInput.value) {
      const endPicker = new Datepicker(endInput.value, { autohide: true, format: 'yyyy-mm-dd' })
      endPicker.setDate(new Date(task.value.end_date))
      endInput.value.addEventListener('changeDate', (e) => {
        const date = e.detail?.datepicker?.getDate()
        if (date) task.value.end_date = date.toISOString().split('T')[0]
      })
    }
  })
}

async function searchUsers() {
  if (query.value.length < 2) {
    users.value = []
    return
  }
  try {
    const { data } = await axios.get(`/api/users/search?query=${query.value}`)
    users.value = data
  } catch (err) {
    console.error(err)
    users.value = []
  }
}

function selectUser(user) {
  selectedUser.value = user
  query.value = user.name
  task.value.user_id = user.id
  users.value = []
}

async function save() {

  if (!selectedUser.value) {
    error.value = 'Выберите пользователя'
    return
  }

  try {
    const token = localStorage.getItem('authToken')
    const { data } = await axios.put(`/api/tasks/${task.value.id}`, task.value, {
      headers: { Authorization: `Bearer ${token}` }
    })
    emit('taskUpdated', data)
    modalInstance.hide()
  } catch (e) {
      error.value = e.response?.data?.errors
  }
}

defineExpose({ open, save })
</script>

<template>
  <div ref="modalRef" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
      <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
        <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Edit Task
          </h3>
          <button data-modal-toggle="crud-modal" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="crud-modal">
            <svg class="w-3 h-3" aria-hidden="true" fill="none" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
            <span class="sr-only">Close modal</span>
          </button>
        </div>

        <form class="p-4 md:p-5" @submit.prevent="save">
          <div class="grid gap-4 mb-4 grid-cols-2">
            <p v-if="error.title" class="text-green-600 text-sm mb-4 font-medium text-center">
              {{ error.title }}
            </p>
            <div class="col-span-2">
              <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
              <input v-model="task.title" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"/>
            </div>

            <div class="col-span-2">
              <p v-if="error.user" class="text-green-600 text-sm mb-4 font-medium text-center">
                {{ error.user }}
              </p>
              <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">User</label>
              <input
                  type="text"
                  v-model="query"
                  @input="searchUsers"
                  class="border rounded p-2 w-full"
                  placeholder="Search user"
              />
              <ul v-if="users.length" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 max-h-40 overflow-y-auto dark:bg-gray-700">
                <li
                    v-for="user in users"
                    :key="user.id"
                    class="px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-600 cursor-pointer"
                    @click="selectUser(user)"
                >
                  {{ user.name }}
                </li>
              </ul>
            </div>

            <div class="col-span-2">

              <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Start Date</label>
              <input ref="startInput" type="text" placeholder="Select start date" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"/>
              <p v-if="error.start_date" class="text-green-600 text-sm mb-4 font-medium text-center">
                {{ error.start_date }}
              </p>
            </div>

            <div class="col-span-2">
              <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">End Date</label>
              <input ref="endInput" type="text" placeholder="Select end date" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"/>
              <p v-if="error.end_date" class="text-green-600 text-sm mb-4 font-medium text-center">
                {{ error.end_date }}
              </p>
            </div>

            <div class="col-span-2 sm:col-span-1">

              <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Status</label>
              <select v-model="task.status" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="done">Done</option>
                <option value="blocked">Blocked</option>
                <option value="canceled">Canceled</option>
              </select>
              <p v-if="error.status" class="text-green-600 text-sm mb-4 font-medium text-center">
                {{ error.status }}
              </p>
            </div>

            <div class="col-span-2">
              <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description</label>
              <textarea v-model="task.description" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 dark:bg-gray-600 dark:border-gray-500 dark:text-white"></textarea>
            </div>
            <p v-if="error.description" class="text-green-600 text-sm mb-4 font-medium text-center">
              {{ error.description }}
            </p>
          </div>

          <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700">
            Save Changes
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
