<script setup lang="ts">
import * as z from 'zod'
import type { FormSubmitEvent, AuthFormField } from '@nuxt/ui'

definePageMeta({
  layout: 'background'
})

const fields: AuthFormField[] = [
  {
    name: 'name',
    type: 'text',
    label: $t('auth.usernameLabel'),
    placeholder: $t('auth.usernamePlaceholder'),
    required: true
  },
  {
    name: 'email',
    type: 'email',
    label: $t('auth.emailLabel'),
    placeholder: $t('auth.emailPlaceholder'),
    required: true
  },
  {
    name: 'password',
    type: 'password',
    label: $t('auth.passwordLabel'),
    placeholder: $t('auth.passwordPlaceholder'),
    required: true
  },
  {
    name: 'confirmPassword',
    type: 'password',
    label: $t('auth.confirmPasswordLabel'),
    placeholder: $t('auth.confirmPasswordPlaceholder'),
    required: true
  }
]

const schema = z.object({
  name: z.string({ required_error: $t('auth.usernameRequired') }).min(1, $t('auth.usernameRequired')),
  email: z.string({ required_error: $t('validation.emailRequired') }).email($t('auth.emailInvalid')),
  password: z.string({ required_error: $t('auth.passwordRequired') }).min(8, $t('auth.passwordMin')),
  confirmPassword: z.string({ required_error: $t('auth.passwordRequired') }).min(1, $t('auth.passwordRequired')),
}).refine(data => data.password === data.confirmPassword, {
  message: $t('auth.passwordMismatch'),
  path: ['confirmPassword'],
})

type Schema = z.output<typeof schema>

const router = useRouter()
const toast = useToast()
const authStore = useAuthentificationStore()

async function onSubmit(payload: FormSubmitEvent<Schema>) {
  const { name, email, password } = payload.data
  const success = await authStore.register({ name, email, password })
  if (!success) {
    toast.add({
      title: $t('auth.toastTitle'),
      description: $t('auth.toastUserExists'),
      icon: 'i-lucide-user-x',
      color: 'error'
    })
    return
  }
  toast.add({
    title: $t('auth.toastTitle'),
    description: $t('auth.toastRegisterSuccess'),
    icon: 'i-lucide-user-check',
    color: 'success'
  })
  router.push({ name: 'index', replace: true })
}
</script>

<template>
  <AuthForm>
    <template #body>
      <UAuthForm
        :schema="schema"
        :title="$t('auth.registerFormTitle')"
        :description="$t('auth.registerFormDescription')"
        icon="i-lucide-user-plus"
        :fields="fields"
        :submit="{ label: $t('auth.registerSubmit') }"
        @submit="onSubmit"
      />
    </template>

    <template #footer>
      {{ $t('auth.alreadyHaveAccount') }}
      <NuxtLink
        :to="{ name: 'auth-login' }"
        class="text-primary"
      >{{ $t('auth.loginLink') }}</NuxtLink>
    </template>
  </AuthForm>
</template>
