<template>
  <AuthLayout>
    <div class="p-8 border border-gray-200 rounded-lg w-full max-w-[510px]">
      <div class="flex justify-center mb-4 items-center space-x-1">
        <PlaneTakeoff class="text-primary"/>
        <span class="text-3xl font-bold italic text-primary">
          TravelsCorp
        </span>
      </div>

      <form @submit="onSubmit" class="w-full space-y-3">
        <h2 class="text-center text-xl font-semibold text-gray-900 mb-6">Criar Conta</h2>

        <FormInput
            name="name"
            id="name"
            type="text"
            label="Nome Completo"
            placeholder="Digite seu nome completo"
        />

        <FormInput
            name="email"
            id="email"
            type="email"
            label="E-mail"
            placeholder="Digite seu e-mail"
        />

        <FormInput
            name="password"
            id="password"
            type="password"
            label="Senha"
            placeholder="Mínimo 6 caracteres"
        />

        <FormInput
            name="password_confirmation"
            id="password_confirmation"
            type="password"
            label="Confirmar Senha"
            placeholder="Confirme sua senha"
        />

        <div class="space-y-3">
          <Button
              type="submit"
              :disabled="!isFormValid || isLoading"
              class="w-full"
          >
            <span v-if="isLoading">Criando conta...</span>
            <span v-else>Criar Conta</span>
          </Button>

          <Button
              type="button"
              variant="outline"
              class="w-full rounded-lg cursor-pointer"
              @click="$router.push('/login')"
          >
            Já tem uma conta? Fazer login
          </Button>
        </div>
      </form>
    </div>
  </AuthLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { useAuth } from '../composables/useAuth'
import { FormInput } from '@/components/ui/form'
import { Button } from '@/components/ui/button'
import { PlaneTakeoff } from 'lucide-vue-next'
import { registerSchema, type RegisterForm } from '@/schemas/auth'
import AuthLayout from '@/components/AuthLayout.vue'

const auth = useAuth()
const { register, clearError } = auth
const isLoading = computed(() => auth.isLoading)

// VeeValidate form with Zod schema
const { handleSubmit, values, meta } = useForm<RegisterForm>({
  validationSchema: toTypedSchema(registerSchema),
  initialValues: {
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
  }
})

const isFormValid = computed(() => {
  return meta.value.valid && values.name && values.email && values.password && values.password_confirmation
})

const onSubmit = handleSubmit(async (formData) => {
  clearError()
  
  try {
    await register(formData)
  } catch (err) {
    console.error('Erro no registro:', err)
  }
})
</script>

