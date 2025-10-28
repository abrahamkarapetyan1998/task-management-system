<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { Modal } from 'flowbite'

/*
  Expose метод open(id) через defineExpose — родитель сможет вызывать:
  deleteModalRef.value.open(id)
*/
const taskIdToDelete = ref(null)
let modalInstance = null
const elId = 'delete-modal'

axios.defaults.baseURL = 'http://127.0.0.1:8000'
axios.defaults.withCredentials = true

// Инициализация modalInstance (лениво — если элемент появится позже, создадим при первом open)
function ensureModal() {
  if (modalInstance) return
  const el = document.getElementById(elId)
  if (!el) {
    console.warn('DeleteModal: element not found in DOM yet')
    return
  }
  modalInstance = new Modal(el)
}

// экспортируем open/close/confirm чтобы их можно было вызвать извне и из template
function open(id) {
  taskIdToDelete.value = id
  ensureModal()
  if (modalInstance) modalInstance.show()
}

function close() {
  if (modalInstance) modalInstance.hide()
  taskIdToDelete.value = null
}

async function confirmDelete() {
  if (!taskIdToDelete.value) return
  try {
    const token = localStorage.getItem('authToken')
    await axios.delete(`/api/tasks/${taskIdToDelete.value}`, {
      headers: { Authorization: `Bearer ${token}` },
    })
    emit('taskDeleted', taskIdToDelete.value)
    close()
  } catch (err) {
    console.error('Delete error', err)
  }
}

const emit = defineEmits(['taskDeleted'])

defineExpose({ open, close })

onMounted(() => {
  ensureModal()
})
</script>

<template>
  <div  id="delete-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
      <div class="relative bg-white rounded-lg shadow-sm ">
        <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:text-white" data-modal-hide="delete-modal">
          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
        <div class="p-4 md:p-5 text-center">
          <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
          </svg>
          <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete this product?</h3>
          <button @click="confirmDelete" data-modal-hide="delete-modal" type="button" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
            Yes, I'm sure
          </button>
          <button @click="close" data-modal-hide="delete-modal"
                  type="button"
                  class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
            No, cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>