<script setup lang="ts">
import * as z from 'zod'

definePageMeta({
  layout: 'default',
  ssr: false,
  middleware: ['auth']
})

const authStore = useAuthentificationStore()
const { account } = storeToRefs(authStore)

const schema = z.object({
  email: z.string().email($t('validation.emailRequired')),
  name: z.string($t('validation.usernameRequired')),
  password: z.string().min(8).optional(),
  confirmPassword: z.string().optional()
}).refine(data => {
  if (!data.password) return true
  return data.password === data.confirmPassword
}, {
  message: $t('auth.passwordMismatch'),
  path: ['confirmPassword']
})

type Schema = z.output<typeof schema>

const state = reactive<Partial<Schema>>({
  email: account.value?.email,
  name: account.value?.name
})

const { updateProfile } = authStore

function onSubmit() {
  const body: any = { name: state.name as string, email: state.email as string }
  if (state.password) {
    body.password = state.password
    body.password_confirmation = state.confirmPassword
  }

  const toast = useToast()
  updateProfile(body)
    .then(() => {
      toast.add({
        title: $t('auth.toastTitle'),
        description: $t('profile.updateSuccess'),
        icon: 'i-lucide-user-check',
        color: 'success'
      })
    })
    .catch((err: any) => {
      const message = err?.data?.message ?? $t('profile.updateError')
      toast.add({
        title: $t('auth.toastTitle'),
        description: message,
        icon: 'i-lucide-alert-circle',
        color: 'error'
      })
    })
}
</script>

<template>
  <UMain class="p-10">
    <UPageCard :title="$t('profile.pageTitle')">
      <div class="flex gap-6 justify-around">
        <UPageCard
          class="flex-1/3"
          :title="$t('profile.informationCardTitle')"
        >
          <UForm
            :schema="schema"
            :state="state"
            class="space-y-4"
            @submit="onSubmit"
          >
            <UFormField
              :label="$t('profile.emailLabel')"
              name="email"
            >
              <UInput
                v-model="state.email"
                class="w-full"
              />
            </UFormField>

            <UFormField
              :label="$t('profile.usernameLabel')"
              name="name"
            >
              <UInput
                v-model="state.name"
                class="w-full"
              />
            </UFormField>

            <UFormField
              :label="$t('auth.passwordLabel')"
              name="password"
            >
              <UInput
                v-model="state.password"
                type="password"
                class="w-full"
              />
            </UFormField>

            <UFormField
              :label="$t('auth.confirmPasswordLabel')"
              name="confirmPassword"
            >
              <UInput
                v-model="state.confirmPassword"
                type="password"
                class="w-full"
              />
            </UFormField>

            <UFormField
              :label="$t('profile.roleLabel')"
              name="role"
            >
              <UInput
                class="w-full"
                :value="account?.role"
                disabled
              />
            </UFormField>

            <UButton
              class="mt-4 cursor-pointer"
              type="submit"
            >
              {{ $t('profile.applyChangesButton') }}
            </UButton>
          </UForm>
        </UPageCard>
      </div>
    </UPageCard>
  </UMain>
</template>
