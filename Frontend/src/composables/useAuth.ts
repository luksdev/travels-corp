import { useRouter } from 'vue-router'
import { toast } from 'vue-sonner'
import { useAuthStore } from '../stores/auth'
import type { LoginData, RegisterData } from '../types/auth'

export function useAuth() {
    const router = useRouter()
    const authStore = useAuthStore()

    const login = async (data: LoginData) => {
        try {
            const response = await authStore.login(data)
            await router.push('/dashboard')
            return response
        } catch (err: any) {
            toast.error(authStore.error || 'Erro ao fazer login')
            throw err
        }
    }

    const register = async (data: RegisterData) => {
        try {
            const response = await authStore.register(data)

            toast.success('Conta criada com sucesso! Redirecionando para o dashboard...', {
                duration: 3000
            })

            setTimeout(async () => {
                await router.push('/dashboard')
            }, 2000)

            return response
        } catch (err: any) {
            toast.error(authStore.error || 'Erro ao criar conta')
            throw err
        }
    }

    const logout = async () => {
        try {
            await authStore.logout()
            await router.push('/login')
        } catch (err) {
            console.error('Erro ao fazer logout:', err)
            await router.push('/login')
        }
    }

    return {
        user: authStore.user,
        isAuthenticated: authStore.isAuthenticated,
        isAdmin: authStore.isAdmin,
        userRole: authStore.userRole,
        isLoading: authStore.isLoading,
        error: authStore.error,
        login,
        register,
        logout,
        clearError: authStore.clearError,
        checkAuth: authStore.checkAuth,
        refreshUser: authStore.refreshUser
    }
}