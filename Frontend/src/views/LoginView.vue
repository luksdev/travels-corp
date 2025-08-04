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
        <div v-if="error"
             class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm text-center">
          {{ error }}
        </div>

        <FormInput
            name="email"
            id="email"
            type="email"
            label="E-mail ou CPF"
            placeholder="Digite seu e-mail ou CPF"
        />

        <FormInput
            name="password"
            id="password"
            type="password"
            label="Senha"
            placeholder="Digite sua senha"
        />

        <div class="text-left">
          <button type="button" class="text-xs text-primary hover:text-primary-800 hover:underline">
            Esqueceu sua senha
          </button>
        </div>

        <div class="space-y-3">
          <Button
              type="submit"
              :disabled="!isFormValid || isLoading"
              class="w-full"
          >
            <span v-if="isLoading">Entrando...</span>
            <span v-else>Entrar</span>
          </Button>

          <Button
              type="button"
              variant="outline"
              class="w-full rounded-lg cursor-pointer"
              @click="$router.push('/register')"
          >
            É sua primeira vez aqui?
          </Button>
        </div>
      </form>
    </div>
  </AuthLayout>
</template>

<script setup lang="ts">
import {computed} from 'vue'
import {useForm} from 'vee-validate'
import {toTypedSchema} from '@vee-validate/zod'
import {useAuth} from '../composables/useAuth'
import {FormInput} from '@/components/ui/form'
import {Button} from '@/components/ui/button'
import {PlaneTakeoff} from 'lucide-vue-next'
import {loginSchema, type LoginForm} from '@/schemas/auth'
import AuthLayout from "@/components/AuthLayout.vue";

const {login, isLoading, error, clearError} = useAuth()

const {handleSubmit, values, meta} = useForm<LoginForm>({
  validationSchema: toTypedSchema(loginSchema),
  initialValues: {
    email: '',
    password: ''
  }
})

const isFormValid = computed(() => {
  return meta.value.valid && values.email && values.password
})

const onSubmit = handleSubmit(async (formData) => {
  clearError()

  try {
    await login(formData)
  } catch (err) {
    console.error('Erro no login:', err)
  }
})
</script>

