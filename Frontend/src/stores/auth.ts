import {defineStore} from 'pinia'
import {ref, computed} from 'vue'
import {authService} from '../services/auth'
import type {User, LoginData, RegisterData} from '../types/auth'

export const useAuthStore = defineStore('auth', () => {
    const user = ref<User | null>(authService.getStoredUser())
    const isLoading = ref(false)
    const error = ref<string | null>(null)

    const isAuthenticated = computed(() => !!user.value)
    const isAdmin = computed(() => user.value?.is_admin === true)
    const userRole = computed(() => user.value?.role || null)

    const setUser = (userData: User | null) => {
        user.value = userData
    }

    const setLoading = (loading: boolean) => {
        isLoading.value = loading
    }

    const setError = (errorMessage: string | null) => {
        error.value = errorMessage
    }

    const clearError = () => {
        error.value = null
    }

    const login = async (data: LoginData) => {
        try {
            setLoading(true)
            clearError()

            const response = await authService.login(data)
            authService.saveTokens(response)
            setUser(response.user)

            return response
        } catch (err: any) {
            const message = err.response?.data?.message || 'Erro ao fazer login'
            setError(message)
            throw err
        } finally {
            setLoading(false)
        }
    }

    const register = async (data: RegisterData) => {
        try {
            setLoading(true)
            clearError()

            const response = await authService.register(data)
            authService.saveTokens(response)
            setUser(response.user)

            return response
        } catch (err: any) {
            const message = err.response?.data?.message || 'Erro ao criar conta'
            setError(message)
            throw err
        } finally {
            setLoading(false)
        }
    }

    const logout = async () => {
        try {
            await authService.logout()
        } catch (err) {
            console.error('Erro ao fazer logout:', err)
        } finally {
            authService.clearTokens()
            setUser(null)
        }
    }

    const checkAuth = () => {
        const storedUser = authService.getStoredUser()
        const hasToken = authService.isAuthenticated()

        if (storedUser && hasToken) {
            setUser(storedUser)
            return true
        } else {
            setUser(null)
            return false
        }
    }

    const refreshUser = async () => {
        try {
            const userData = await authService.getUser()
            setUser(userData)
            authService.saveTokens({
                user: userData,
                access_token: authService.getStoredToken() || '',
                token_type: 'bearer',
                expires_in: 3600,
                success: true,
                message: 'User refreshed'
            })
        } catch (err) {
            console.error('Erro ao atualizar dados do usuário:', err)
            logout()
        }
    }

    return {
        user: computed(() => user.value),
        isLoading: computed(() => isLoading.value),
        error: computed(() => error.value),
        isAuthenticated,
        isAdmin,
        userRole,
        login,
        register,
        logout,
        checkAuth,
        refreshUser,
        clearError,
        setUser,
        setLoading,
        setError
    }
})