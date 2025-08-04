import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../services/auth'
import type { User, LoginData, RegisterData } from '../types/auth'

const user = ref<User | null>(authService.getStoredUser())
const isLoading = ref(false)
const error = ref<string | null>(null)

export function useAuth() {
  const router = useRouter()

  const isAuthenticated = computed(() => !!user.value)

  const login = async (data: LoginData) => {
    try {
      isLoading.value = true
      error.value = null
      
      const response = await authService.login(data)
      authService.saveTokens(response)
      user.value = response.user
      
      await router.push('/dashboard')
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao fazer login'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const register = async (data: RegisterData) => {
    try {
      isLoading.value = true
      error.value = null
      
      const response = await authService.register(data)
      authService.saveTokens(response)
      user.value = response.user
      
      await router.push('/dashboard')
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao criar conta'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const logout = async () => {
    try {
      await authService.logout()
    } catch (err) {
      console.error('Erro ao fazer logout:', err)
    } finally {
      authService.clearTokens()
      user.value = null
      await router.push('/login')
    }
  }

  const clearError = () => {
    error.value = null
  }

  const checkAuth = () => {
    const storedUser = authService.getStoredUser()
    const hasToken = authService.isAuthenticated()
    
    if (storedUser && hasToken) {
      user.value = storedUser
      return true
    } else {
      user.value = null
      return false
    }
  }

  return {
    user: computed(() => user.value),
    isAuthenticated,
    isLoading: computed(() => isLoading.value),
    error: computed(() => error.value),
    login,
    register,
    logout,
    clearError,
    checkAuth
  }
}