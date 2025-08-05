import api from './api'
import type { LoginData, RegisterData, AuthResponse, User, ApiResponse } from '../types/auth'

export const authService = {
  async login(data: LoginData): Promise<AuthResponse> {
    const response = await api.post<ApiResponse<AuthResponse>>('/auth/login', data)
    return response.data.data
  },

  async register(data: RegisterData): Promise<AuthResponse> {
    const response = await api.post<ApiResponse<AuthResponse>>('/auth/register', data)
    return response.data.data
  },

  async logout(): Promise<void> {
    await api.post('/auth/logout')
  },

  async refresh(): Promise<{ access_token: string }> {
    const response = await api.post<{ access_token: string }>('/auth/refresh')
    return response.data
  },

  async getUser(): Promise<User> {
    const response = await api.get<ApiResponse<User>>('/auth/user')
    return response.data.data
  },

  saveTokens(authResponse: AuthResponse): void {
    localStorage.setItem('access_token', authResponse.access_token)
    localStorage.setItem('user', JSON.stringify(authResponse.user))
  },

  clearTokens(): void {
    localStorage.removeItem('access_token')
    localStorage.removeItem('user')
  },

  getStoredUser(): User | null {
    const user = localStorage.getItem('user')
    return user ? JSON.parse(user) : null
  },

  getStoredToken(): string | null {
    return localStorage.getItem('access_token')
  },

  isAuthenticated(): boolean {
    return !!this.getStoredToken()
  }
}