<script setup>
import axios from "axios";
import 'flowbite';
import {Datepicker, initModals} from 'flowbite'
import { Modal } from 'flowbite';
import {onMounted, ref, watch} from "vue";


const title = ref('')
const description = ref('')
const start_date = ref('')
const end_date = ref('')
const status = ref('pending')
const startInput = ref(null)
const endInput = ref(null)
const users = ref([])
const query = ref('')
const selectedUser = ref(null)
const error = ref('')
const emit = defineEmits(['taskCreated'])

axios.defaults.baseURL = 'http://127.0.0.1:8000'
axios.defaults.withCredentials = true

async function createTask() {
  if (!selectedUser.value) {
    error.value = 'Выберите пользователя'
    return
  }

  try {
    const token = localStorage.getItem('authToken')
    const { data } = await axios.post(
        '/api/tasks',
        {
          title: title.value,
          description: description.value,
          start_date: start_date.value,
          end_date: end_date.value,
          status: status.value,
          user_id: selectedUser.value.id,
        },
        { headers: { Authorization: `Bearer ${token}` } }
    )

    emit('taskCreated', data)

    const $targetEl = document.getElementById('crud-modal');
    const modal = new Modal($targetEl);

    modal.hide()

    initModals()

    title.value = ''
    description.value = ''
    start_date.value = ''
    end_date.value = ''
    status.value = 'pending'
    query.value = ''
    selectedUser.value = null
    error.value = ''
  } catch (e) {
    error.value = e.response?.data?.errors
  }
}
async function searchUsers() {
  if (query.value.length < 2) {
    users.value = []
    return
  }
  try {
    const { data } = await axios.get(`/api/users/search?query=${query.value}`)
    users.value = data.data
  } catch (e) {
    error.value = e.response?.data?.errors
    users.value = []
  }
}


function selectUser(user) {
  selectedUser.value = user
  query.value = user.name
  users.value = []
}

onMounted(() => {
  if (startInput.value) {
    const startPicker = new Datepicker(startInput.value, { autohide: true, format: 'yyyy-mm-dd' })
    startInput.value.addEventListener('changeDate', (e) => {
      const date = e.detail?.datepicker?.getDate()
      if (date) start_date.value = date.toISOString().split('T')[0]
    })
  }

  if (endInput.value) {
    const endPicker = new Datepicker(endInput.value, { autohide: true, format: 'yyyy-mm-dd' })
    endInput.value.addEventListener('changeDate', (e) => {
      const date = e.detail?.datepicker?.getDate()
      if (date) end_date.value = date.toISOString().split('T')[0]
    })
  }
})

</script>

<template>
  <div id="crud-modal" tabindex="-1"
       aria-hidden="true"
       class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
      <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
        <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Create New Task
          </h3>
          <button type="button"
                  class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                  data-modal-toggle="crud-modal">
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
            <span class="sr-only">Close modal</span>
          </button>
        </div>
        <form class="p-4 md:p-5" @submit.prevent="createTask">
          <div class="grid gap-4 mb-4 grid-cols-2">
            <div class="col-span-2">
              <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
              <input v-model="title" type="text" name="title" id="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Type product name" required="">
              <p v-if="error.title" class="text-green-600 text-sm mb-4 font-medium text-center">
                {{ error.title }}
              </p>
            </div>
            <div class="col-span-2">
              <label for="user" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">User</label>
              <input
                  name="user"
                  v-model="query"
                  @input="searchUsers"
                  type="text"
                  placeholder="Search user"
                  class="border rounded p-2 w-full"
              />
              <ul
                  v-if="users.length"
                  class="absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 max-h-40 overflow-y-auto dark:bg-gray-700"
              >
                <li
                    v-for="user in users"
                    :key="user.id"
                    class="px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-600 cursor-pointer"
                    @click="selectUser(user)"
                >
                  {{ user.name }}
                </li>
              </ul>
              <input type="hidden" :value="selectedUser?.id" name="user_id" />
            </div>

            <div class="col-span-2" id="test">
              <label for="start-datepicker" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                Start Date
              </label>
              <input
                  ref="startInput"
                  id="start-datepicker"
                  type="text"
                  placeholder="Select start date"
                  class=" bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              />
              <p v-if="error.start_date" class="text-green-600 text-sm mb-4 font-medium text-center">
                {{ error.start_date }}
              </p>
            </div>
            <div class="col-span-2">
              <label for="end-datepicker" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                End Date
              </label>
              <input
                  ref="endInput"
                  id="end-datepicker"
                  type="text"
                  placeholder="Select end date"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                  @input="handleEndDateChange"
              />
            </div>
            <div class="col-span-2 sm:col-span-1">
              <label for="category" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Status</label>
              <select v-model="status" id="category" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
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
              <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Product Description</label>
              <textarea id="description" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Write product description here"></textarea>
              <p v-if="error.description" class="text-green-600 text-sm mb-4 font-medium text-center">
                {{ error.description }}
              </p>
            </div>
          </div>
          <button type="submit" class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
            <svg class="me-1 -ms-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
            Add new task
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>