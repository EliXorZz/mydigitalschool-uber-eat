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
  email: z.string({ required_error: $t('validation.emailRequired') }).email($t('auth.emailInvalid')),
  name: z.string({ required_error: $t('validation.usernameRequired') }).min(1, $t('validation.usernameRequired')),
  password: z.string().min(8, $t('auth.passwordMin')).optional().or(z.literal('')),
  confirmPassword: z.string().optional(),
}).refine(data => !data.password || data.password === data.confirmPassword, {
  message: $t('auth.passwordMismatch'),
  path: ['confirmPassword'],
})

type Schema = z.output<typeof schema>

const state = reactive<Partial<Schema>>({
  email: account.value?.email,
  name: account.value?.name,
})

const avatar = ref<string>(account.value?.avatar ?? '')
const toast = useToast()

function onSubmit() {
  const body: Parameters<typeof authStore.updateProfile>[0] = {
    name: state.name as string,
    email: state.email as string,
    avatar: avatar.value || null,
    ...(state.password ? { password: state.password, password_confirmation: state.confirmPassword } : {})
  }

  authStore.updateProfile(body)
    .then(() => {
      toast.add({
        title: $t('auth.toastTitle'),
        description: $t('profile.updateSuccess'),
        icon: 'i-lucide-user-check',
        color: 'success',
      })
    })
    .catch(() => {
      toast.add({
        title: $t('auth.toastTitle'),
        description: $t('profile.updateError'),
        icon: 'i-lucide-alert-circle',
        color: 'error',
      })
    })
}
</script>

<template>
  <UMain class="px-4 py-6 sm:px-8 sm:py-10 max-w-2xl mx-auto">
    <UPageCard :title="$t('profile.pageTitle')">
      <UForm
        :schema="schema"
        :state="state"
        class="space-y-5"
        @submit="onSubmit"
      >
        <!-- Avatar -->
        <div class="flex items-center gap-4">
          <img
            v-if="avatar"
            :src="avatar"
            alt="avatar"
            class="w-16 h-16 rounded-full object-cover ring-2 ring-primary-500/30"
          >
          <div
            v-else
            class="w-16 h-16 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-2xl font-bold text-gray-400"
          >
            {{ account?.name?.[0]?.toUpperCase() }}
          </div>
          <ImageUpload
            v-model="avatar"
            :label="$t('profile.avatarLabel')"
            class="flex-1"
          />
        </div>

        <UFormField :label="$t('profile.usernameLabel')" name="name">
          <UInput v-model="state.name" class="w-full" />
        </UFormField>

        <UFormField :label="$t('profile.emailLabel')" name="email">
          <UInput v-model="state.email" class="w-full" />
        </UFormField>

        <UFormField :label="$t('profile.roleLabel')" name="role">
          <UInput :value="account?.role" class="w-full" disabled />
        </UFormField>

        <div class="border-t pt-4 space-y-4 dark:border-gray-700">
          <p class="text-sm font-medium text-gray-500">{{ $t('profile.changePasswordLabel') }}</p>

          <UFormField :label="$t('auth.passwordLabel')" name="password">
            <UInput v-model="state.password" type="password" class="w-full" />
          </UFormField>

          <UFormField :label="$t('auth.confirmPasswordLabel')" name="confirmPassword">
            <UInput v-model="state.confirmPassword" type="password" class="w-full" />
          </UFormField>
        </div>

        <UButton type="submit" class="cursor-pointer w-full justify-center">
          {{ $t('profile.applyChangesButton') }}
        </UButton>
      </UForm>
    </UPageCard>
  </UMain>
</template>
