export interface User {
  id: string
  name: string
  email: string
  role: string
  is_admin: boolean
  email_verified_at: string | null
  created_at: string
  updated_at: string
}

export interface LoginData {
  email: string
  password: string
}

export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export interface AuthResponse {
  success: boolean
  message: string
  access_token: string
  token_type: string
  expires_in: number
  user: User
}

export interface ApiResponse<T> {
  data: T
}

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
}