import axios from 'axios'
import router from '../router'

const api = axios.create({
    baseURL: 'https://travelscorp-api.geanpedro.com.br:80/api',
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
            console.log(`📤 Request to ${config.url} with token: ${token.substring(0, 20)}...`)
        } else {
            console.log(`📤 Request to ${config.url} without token`)
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

        console.log(`❌ Request failed: ${originalRequest.url} - Status: ${error.response?.status}`)

        if (originalRequest.url?.includes('/auth/refresh')) {
            console.log('🚫 Refresh request failed, not retrying')
            return Promise.reject(error)
        }
        
        if (error.response?.status === 401 && !originalRequest._retry && token) {
            console.log('🔄 Token expired, attempting refresh...')
            originalRequest._retry = true

            try {
                console.log(`🔑 Refreshing with token: ${token.substring(0, 20)}...`)
                const response = await refreshApi.post('/auth/refresh', {}, {
                    headers: {
                        Authorization: `Bearer ${token}`
                    }
                })
                
                const { access_token, user } = response.data.data
                console.log(`✅ Token refreshed successfully: ${access_token.substring(0, 20)}...`)

                localStorage.setItem('access_token', access_token)
                if (user) {
                    localStorage.setItem('user', JSON.stringify(user))
                }

                originalRequest.headers.Authorization = `Bearer ${access_token}`
                console.log(`🔄 Retrying original request: ${originalRequest.url}`)

                return api(originalRequest)
            } catch (refreshError) {
                console.log('❌ Token refresh failed, redirecting to login')
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