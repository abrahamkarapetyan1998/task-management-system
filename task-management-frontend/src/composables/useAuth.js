import {ref} from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
const router = useRouter()


axios.defaults.baseURL = 'http://127.0.0.1:8000'
axios.defaults.withCredentials = true
const user = ref(null)

export function useAuth() {
    async function fetchUser() {
        try {
            const token = localStorage.getItem('authToken')
            if (!token) {
                user.value = null
                return null
            }

            const { data } = await axios.get('/api/user', {
                headers: { Authorization: `Bearer ${token}` }
            })
            user.value = data
            return data
        } catch (err) {
            user.value = null
            localStorage.removeItem('authToken')
            return null
        }
    }


    async function logout() {
        try {
            await axios.post('/api/logout', {}, { withCredentials: true })
        } catch (e) {
            console.error(e)
        }

        localStorage.removeItem('authToken')
        user.value = null
        await router.push('/login')
    }

    return { user, logout, fetchUser }
}