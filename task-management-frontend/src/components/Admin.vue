<script setup>
import CreateTaskModal from "./Modals/CreateTaskModal.vue";
import EditTaskModal from "./Modals/EditTaskModal.vue";
import DeleteTaskModal from "./Modals/DeleteTaskModal.vue";
import { ref, onMounted , watch } from 'vue'
import axios from 'axios'
import 'flowbite';
import {initModals, Modal} from 'flowbite';
import TaskSearch from "./Components/TaskSearch.vue";
import NavBar from "./NavBar.vue";

const successMessage = ref('')
const tasks = ref([])
const deleteModalRef = ref(null)
const editModal = ref(null)

axios.defaults.baseURL = 'http://127.0.0.1:8000'
axios.defaults.withCredentials = true

function handleTaskCreated(newTask) {
  tasks.value.unshift(newTask)
}

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
    successMessage.value = 'Failed to update status'
    setTimeout(() => (successMessage.value = ''), 3000)
  }
}
async function fetchTasks() {
  try {
    const token = localStorage.getItem('authToken')

    const {data} = await axios.get(`/api/tasks/`, {
      headers: {Authorization: `Bearer ${token}`},
    })
    tasks.value = data.data
  } catch (err) {
    error.value = 'Ошибка при загрузке данных'
    console.error(err)
  }
}


onMounted(() => {
  initModals()
  fetchTasks()
})
function openDeleteModal(task) {
  if (!deleteModalRef.value) {
    console.warn('deleteModalRef not ready yet')
    return
  }

  if (typeof deleteModalRef.value.open !== 'function') {
    console.warn('deleteModalRef.open is not a function — check DeleteModal.vue defineExpose')
    return
  }

  deleteModalRef.value.open(task.id)
}

function handleTaskDeleted(id) {
  tasks.value = tasks.value.filter(t => t.id !== id)
}
function  openEditModal(task) {
  editModal.value.open(task)
}

function handleTaskUpdated(updatedTask) {
  const index = tasks.value.findIndex(t => t.id === updatedTask.id)
  if (index !== -1) tasks.value[index] = updatedTask
}
</script>
<template>
  <NavBar />
  <div v-if="successMessage" class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
    <span class="font-medium">Success alert!</span>  {{ successMessage }}
  </div>

  <h1>Tasks</h1>
  <TaskSearch @tasksFiltered="tasks = $event"  :showUserFilter="true"/>
  <button
      data-modal-target="crud-modal"
      data-modal-toggle="crud-modal"
      class="mb-10 block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
      type="button"
  >
    Create New Task
  </button>

   <div class="overflow-x-auto max-h-[500px] shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
        <tr>
          <th class="px-6 py-3">Task Name</th>
          <th class="px-6 py-3">Description</th>
          <th class="px-6 py-3">Start Date</th>
          <th class="px-6 py-3">End Date</th>
          <th class="px-6 py-3">Status</th>
          <th class="px-6 py-3">User ID</th>
          <th class="px-6 py-3">Actions</th>

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
          </td>
          <td class="px-6 py-4">{{ task.user.id}}</td>
          <td class="px-6 py-4 space-x-2">
            <button @click="openEditModal(task)">Edit</button>
            <button @click="openDeleteModal(task)" class="text-red-600">Delete</button>
          </td>
        </tr>
        </tbody>
      </table>
    </div>
  <Teleport to="body">
    <CreateTaskModal @taskCreated="handleTaskCreated" />
    <DeleteTaskModal ref="deleteModalRef" @taskDeleted="handleTaskDeleted" />
    <EditTaskModal ref="editModal" @taskUpdated="handleTaskUpdated" />
  </Teleport>

</template>

