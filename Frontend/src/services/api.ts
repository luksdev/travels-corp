import axios from 'axios'
import router from '../router'

const api = axios.create({
    baseURL: 'http://localhost:8000/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
})

api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('access_token')
        if (token) {
            config.headers.Authorization = `Bearer ${token}`
        }
        return config
    },
    (error) => {
        return Promise.reject(error)
    }
)

const refreshApi = axios.create({
    baseURL: 'http://127.0.0.1:8000/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
})

api.interceptors.response.use(
    (response) => response,
    async (error) => {
        const originalRequest = error.config
        const token = localStorage.getItem('access_token')

        if (originalRequest.url?.includes('/auth/refresh')) {
            return Promise.reject(error)
        }
        
        if (error.response?.status === 401 && !originalRequest._retry && token) {
            originalRequest._retry = true

            try {
                const response = await refreshApi.post('/auth/refresh', {}, {
                    headers: {
                        Authorization: `Bearer ${token}`
                    }
                })
                
                const { access_token, user } = response.data.data

                localStorage.setItem('access_token', access_token)
                if (user) {
                    localStorage.setItem('user', JSON.stringify(user))
                }

                originalRequest.headers.Authorization = `Bearer ${access_token}`

                return api(originalRequest)
            } catch (refreshError) {
                localStorage.removeItem('access_token')
                localStorage.removeItem('user')
                await router.push('/login')
                return Promise.reject(refreshError)
            }
        }

        return Promise.reject(error)
    }
)

export default api