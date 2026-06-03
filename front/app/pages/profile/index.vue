<script setup lang="ts">
import * as z from 'zod'
import type { Order } from '~/types/order'

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
  // if password is set, confirmPassword must match
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

const orderStore = useOrderStore()

const { data: orders } = await useAsyncData<Order[]>(
  `orders:me`,
  async () => {
    try {
      return await orderStore.list()
    } catch (e) {
      // fallback to empty
      return []
    }
  }
)

function onSubmit() {
  // call store to update profile
  const body: any = { name: state.name as string, email: state.email as string }
  if (state.password) {
    body.password = state.password
    body.password_confirmation = state.confirmPassword
  }

  const res = updateProfile(body)

  res.then((ok) => {
    if (ok) {
      useToast()?.success?.($t('profile.updateSuccess'))
    } else {
      useToast()?.error?.($t('profile.updateError'))
    }
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

        <OrderTable
          v-if="orders"
          class="flex-2/3"
          :title="$t('profile.ordersCardTitle')"
          :orders="orders"
        />
      </div>
    </UPageCard>
  </UMain>
</template>
