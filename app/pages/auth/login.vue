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
    help: 'admin',
    required: true
  },
  {
    name: 'password',
    type: 'password',
    label: $t('auth.passwordLabel'),
    placeholder: $t('auth.passwordPlaceholder'),
    help: 'admin-mydigitalschool',
    required: true
  }
]

const schema = z.object({
  username: z.string($t('auth.usernameRequired')),
  password: z.string($t('auth.passwordRequired'))
})

type Schema = z.output<typeof schema>

const router = useRouter()
const toast = useToast()
const authStore = useAuthentificationStore()

async function onSubmit(payload: FormSubmitEvent<Schema>) {
  const { username, password } = payload.data
  const success = await authStore.login(username, password)

  if (!success) {
    toast.add({
      title: $t('auth.toastTitle'),
      description: $t('auth.toastInvalidCredentials'),
      icon: 'i-lucide-user-lock',
      color: 'error'
    })
    return
  }

  toast.add({
    title: $t('auth.toastTitle'),
    description: $t('auth.toastSuccess'),
    icon: 'i-lucide-user-lock',
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
          :title="$t('auth.formTitle')"
          :description="$t('auth.formDescription')"
          icon="i-lucide-user"
          :submit="{ label: $t('auth.submit') }"
          :fields="fields"
          @submit="onSubmit"
      />
    </template>

    <template #footer>
      {{ $t('auth.noAccount') }}
      <NuxtLink :to="{ name: 'auth-register' }" class="text-primary">{{ $t('auth.registerLink') }}</NuxtLink>
    </template>
  </AuthForm>
</template>