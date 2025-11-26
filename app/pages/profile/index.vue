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
  email: z.email('Adresse mail obligatoire'),
  username: z.string('Nom d\'utilisateur obligatoire'),
})

type Schema = z.output<typeof schema>

const state = reactive<Partial<Schema>>({
  email: account.value?.email,
  username: account.value?.username,
})

const orders: Order[] = [
  {
    name: "Commande 302",
    date: "21-02-2025 12:31",
    total: 21,
  }
]
</script>

<template>
  <UMain class="p-10">
    <UPageCard title="Mon profile">
      <div class="flex gap-6 justify-around">
        <UPageCard class="flex-1/3" title="Mes informations">
          <UForm :schema="schema" :state="state" class="space-y-4">
            <UFormField label="Adresse mail" name="email">
              <UInput class="w-full" v-model="state.email" />
            </UFormField>

            <UFormField label="Nom d'utilisateur" name="username">
              <UInput class="w-full" v-model="state.username" />
            </UFormField>

            <UFormField label="Role" name="role">
              <UInput class="w-full" :value="account?.role"  disabled />
            </UFormField>

            <UButton class="mt-4 cursor-pointer" type="submit">
              Appliquer les modification
            </UButton>
          </UForm>
        </UPageCard>
        <OrderTable class="flex-2/3" title="Mes commandes" :orders="orders"/>
      </div>
    </UPageCard>
  </UMain>
</template>