<template>
  <AuthLayout>
    <form @submit.prevent="handleSubmit" class="register-form">
      <h2 class="form-title">Criar Conta</h2>
      
      <div v-if="error" class="error-message">
        {{ error }}
      </div>
      
      <div class="form-group">
        <label for="name" class="form-label">Nome</label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          class="form-input"
          :class="{ 'error': nameError }"
          placeholder="Seu nome completo"
          required
        />
        <span v-if="nameError" class="field-error">{{ nameError }}</span>
      </div>
      
      <div class="form-group">
        <label for="email" class="form-label">Email</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          class="form-input"
          :class="{ 'error': emailError }"
          placeholder="seu@email.com"
          required
        />
        <span v-if="emailError" class="field-error">{{ emailError }}</span>
      </div>
      
      <div class="form-group">
        <label for="password" class="form-label">Senha</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          class="form-input"
          :class="{ 'error': passwordError }"
          placeholder="Mínimo 6 caracteres"
          required
        />
        <span v-if="passwordError" class="field-error">{{ passwordError }}</span>
      </div>
      
      <div class="form-group">
        <label for="password_confirmation" class="form-label">Confirmar Senha</label>
        <input
          id="password_confirmation"
          v-model="form.password_confirmation"
          type="password"
          class="form-input"
          :class="{ 'error': passwordConfirmationError }"
          placeholder="Confirme sua senha"
          required
        />
        <span v-if="passwordConfirmationError" class="field-error">{{ passwordConfirmationError }}</span>
      </div>
      
      <button
        type="submit"
        class="btn btn-primary"
        :disabled="isLoading"
      >
        <span v-if="isLoading">Criando conta...</span>
        <span v-else>Criar Conta</span>
      </button>
      
      <div class="form-footer">
        <p>
          Já tem uma conta?
          <router-link to="/login" class="link">Fazer login</router-link>
        </p>
      </div>
    </form>
  </AuthLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useAuth } from '../composables/useAuth'
import AuthLayout from '../components/AuthLayout.vue'

const { register, isLoading, error, clearError } = useAuth()

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const nameError = ref('')
const emailError = ref('')
const passwordError = ref('')
const passwordConfirmationError = ref('')

const validateForm = () => {
  nameError.value = ''
  emailError.value = ''
  passwordError.value = ''
  passwordConfirmationError.value = ''
  
  let isValid = true
  
  if (!form.value.name.trim()) {
    nameError.value = 'Nome é obrigatório'
    isValid = false
  } else if (form.value.name.trim().length < 2) {
    nameError.value = 'Nome deve ter pelo menos 2 caracteres'
    isValid = false
  }
  
  if (!form.value.email) {
    emailError.value = 'Email é obrigatório'
    isValid = false
  } else if (!form.value.email.includes('@')) {
    emailError.value = 'Email deve ser válido'
    isValid = false
  }
  
  if (!form.value.password) {
    passwordError.value = 'Senha é obrigatória'
    isValid = false
  } else if (form.value.password.length < 6) {
    passwordError.value = 'Senha deve ter pelo menos 6 caracteres'
    isValid = false
  }
  
  if (!form.value.password_confirmation) {
    passwordConfirmationError.value = 'Confirmação de senha é obrigatória'
    isValid = false
  } else if (form.value.password !== form.value.password_confirmation) {
    passwordConfirmationError.value = 'Senhas não coincidem'
    isValid = false
  }
  
  return isValid
}

const handleSubmit = async () => {
  clearError()
  
  if (!validateForm()) {
    return
  }
  
  try {
    await register(form.value)
  } catch (err) {
    console.error('Erro no registro:', err)
  }
}
</script>

<style scoped>
.register-form {
  width: 100%;
}

.form-title {
  text-align: center;
  margin: 0 0 1.5rem 0;
  color: #1a202c;
  font-size: 1.5rem;
  font-weight: 600;
}

.error-message {
  background: #fed7d7;
  color: #c53030;
  padding: 0.75rem;
  border-radius: 6px;
  margin-bottom: 1rem;
  font-size: 0.875rem;
  text-align: center;
}

.form-group {
  margin-bottom: 1rem;
}

.form-label {
  display: block;
  margin-bottom: 0.5rem;
  color: #2d3748;
  font-weight: 500;
  font-size: 0.875rem;
}

.form-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 1rem;
  box-sizing: border-box;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-input.error {
  border-color: #e53e3e;
}

.field-error {
  display: block;
  color: #e53e3e;
  font-size: 0.75rem;
  margin-top: 0.25rem;
}

.btn {
  width: 100%;
  padding: 0.75rem 1rem;
  border: none;
  border-radius: 6px;	
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  margin-top: 0.5rem;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #5a67d8;
}

.btn-primary:disabled {
  background: #a0aec0;
  cursor: not-allowed;
}

.form-footer {
  text-align: center;
  margin-top: 1.5rem;
}

.form-footer p {
  margin: 0;
  color: #718096;
  font-size: 0.875rem;
}

.link {
  color: #667eea;
  text-decoration: none;
  font-weight: 500;
}

.link:hover {
  text-decoration: underline;
}
</style>