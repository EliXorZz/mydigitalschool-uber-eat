<script setup lang="ts">
import * as z from 'zod'
import type { FormSubmitEvent, AuthFormField } from '@nuxt/ui'

definePageMeta({
  layout: 'background',
  ssr: false
})

const fields: AuthFormField[] = [
  {
    name: 'username',
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
  username: z.string($t('auth.usernameRequired')),
  email: z.string().email($t('auth.emailInvalid')),
  password: z.string($t('auth.passwordRequired')).min(8, $t('auth.passwordMin')),
  confirmPassword: z.string($t('auth.passwordRequired'))
}).refine((data) => data.password === data.confirmPassword, {
  message: $t('auth.passwordMismatch'),
  path: ["confirmPassword"],
})

type Schema = z.output<typeof schema>

function onSubmit(payload: FormSubmitEvent<Schema>) {
  console.log('Submitted', payload)
}
</script>

<template>
  <AuthForm>
    <template #body>
      <UAuthForm
          :schema="schema"
          :title="$t('auth.formTitle')"
          :description="$t('auth.formDescription')"
          icon="i-lucide-user"
          :fields="fields"
          :submit="{ label: $t('auth.submit') }"
          @submit="onSubmit"
      />
    </template>

    <template #footer>
      {{ $t('auth.alreadyHaveAccount') }}
      <NuxtLink :to="{ name: 'auth-login' }" class="text-primary">{{ $t('auth.loginLink') }}</NuxtLink>
    </template>
  </AuthForm>
</template>