import {z} from 'zod'

export const loginSchema = z.object({
    email: z
        .string({required_error: 'E-mail é obrigatório'})
        .min(1, 'E-mail é obrigatório')
        .email('E-mail deve ser válido'),

    password: z
        .string({required_error: 'Senha é obrigatória'})
        .min(1, 'Senha é obrigatória')
        .min(6, 'Senha deve ter pelo menos 6 caracteres')
})

export const registerSchema = z.object({
    name: z
        .string({required_error: 'Nome é obrigatório'})
        .min(1, 'Nome é obrigatório')
        .min(2, 'Nome deve ter pelo menos 2 caracteres'),

    email: z
        .string({required_error: 'E-mail é obrigatório'})
        .min(1, 'E-mail é obrigatório')
        .email('E-mail deve ser válido'),

    password: z
        .string({required_error: 'Senha é obrigatória'})
        .min(1, 'Senha é obrigatória')
        .min(6, 'Senha deve ter pelo menos 6 caracteres'),

    password_confirmation: z
        .string({required_error: 'Confirmação de senha é obrigatória'})
        .min(1, 'Confirmação de senha é obrigatória')
}).refine((data) => data.password === data.password_confirmation, {
    message: 'Senhas não conferem',
    path: ['password_confirmation']
})

export type LoginForm = z.infer<typeof loginSchema>
export type RegisterForm = z.infer<typeof registerSchema>