<script setup lang="ts">
import * as z from 'zod'
import type {Order} from "~/types/order";

definePageMeta({
  layout: 'default',
  ssr: false,
  middleware: ['auth'],
})

const authStore = useAuthentificationStore()
const { account } = storeToRefs(authStore)

const schema = z.object({
  email: z.email($t('validation.emailRequired')),
  username: z.string($t('validation.usernameRequired')),
})

type Schema = z.output<typeof schema>

const state = reactive<Partial<Schema>>({
  email: account.value?.email,
  username: account.value?.username,
})

const { data: orders } = await useAsyncData<Order[]>(
    `orders:me`,
    async () => {
      return [
        {
          id: 32,
          name: 'M. Dylan',
          date: 'Lundi 21 février 12:32',
          total: 23.4,
          items: []
        }
      ]
    }
)
</script>

<template>
  <UMain class="p-10">
    <UPageCard :title="$t('profile.pageTitle')">
      <div class="flex gap-6 justify-around">
        <UPageCard class="flex-1/3" :title="$t('profile.informationCardTitle')">
          <UForm :schema="schema" :state="state" class="space-y-4">
            <UFormField :label="$t('profile.emailLabel')" name="email">
              <UInput class="w-full" v-model="state.email" />
            </UFormField>

            <UFormField :label="$t('profile.usernameLabel')" name="username">
              <UInput class="w-full" v-model="state.username" />
            </UFormField>

            <UFormField :label="$t('profile.roleLabel')" name="role">
              <UInput class="w-full" :value="account?.role" disabled />
            </UFormField>

            <UButton class="mt-4 cursor-pointer" type="submit">
              {{ $t('profile.applyChangesButton') }}
            </UButton>
          </UForm>
        </UPageCard>

        <OrderTable v-if="orders" class="flex-2/3" :title="$t('profile.ordersCardTitle')" :orders="orders"/>
      </div>
    </UPageCard>
  </UMain>
</template>