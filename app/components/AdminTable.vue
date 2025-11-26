<script setup lang="ts">
import * as z from 'zod'

import type {Restaurant, RestaurantsResponse} from "~/types/restaurant";
import type {TableColumn} from "#ui/components/Table.vue";
import {LazyModalConfirmation, UBadge, UButton} from "#components";

const { data: restaurants, pending: restaurantPending } = await useAsyncData<RestaurantsResponse>(
    'restaurants',
    () => $fetch('/api/restaurants')
)

const columns: TableColumn<Restaurant>[] = [
  {
    accessorKey: 'id',
    header: '#',
    cell: ({ row }) => `#${row.getValue('id')}`
  },
  {
    accessorKey: 'name',
    header: "Restaurant",
    cell: ({ row }) => h('div', { class: 'font-bold' }, row.getValue('name'))
  },
  {
    accessorKey: 'city',
    header: 'Localisation',
    cell: ({ row }) => h('div', {}, row.getValue('city'))
  },
  {
    accessorKey: 'type',
    header: 'Type',
    cell: ({ row }) => h(UBadge, { color: 'neutral', variant: 'outline' }, () => row.getValue('type'))
  },
  {
    id: 'actions',
    header: 'Actions',
    cell: ({ row }) => h('div', { class: 'flex gap-3' }, [
      h(UButton, { class: 'cursor-pointer' ,icon: 'i-lucide-trash', size: 'xs', variant: 'solid', color: 'error', onClick: () => deleteRestaurant(row.original) }),
      h(UButton, { class: 'cursor-pointer' ,icon: 'i-lucide-edit', size: 'xs', variant: 'solid', color: 'warning' })
    ]),
  },
]
const search = ref()

const toast = useToast()
const overlay = useOverlay()
const confirmModal = overlay.create(LazyModalConfirmation)

const openCreateModal = ref(false)

const schema = z.object({
  name: z.string('Nom obligatoire'),
  location: z.string('Adresse postal obligatoire'),
  zip: z.string('Code postal obligatoire')
      .regex(/^\d{5}$/, "Le code postal doit contenir 5 chiffres"),
  city: z.string('Ville obligatoire'),
  email: z.email('Email obligatoire'),
  password: z.string('Mot de passe obligatoire')
})

const isFormValid = computed(() => schema.safeParse(state).success)

type Schema = z.output<typeof schema>

const state = reactive<Partial<Schema>>({
  name: undefined,
  location: undefined,
  zip: undefined,
  city: undefined,
  email: undefined,
  password: undefined
})

async function createRestaurant() {
  openCreateModal.value = false
}

async function deleteRestaurant(restaurant: Restaurant) {
  const response = confirmModal.open({
    title: 'Supression',
    description: `Voulez vous supprimer le restaurant ${restaurant.name}`,
    action: 'Supprimer'
  })

  const success = await response.result

  if (success)
    toast.add({
      title: 'Dashboard admin',
      description: `Le restaurant ${restaurant.name} a bien été supprimé`,
      color: 'success'
    })
}
</script>

<template>
  <UPageCard title="Liste des restaurants" spotlight-color="primary">
    <div class="flex justify-between items-center py-3">
      <UInput v-model="search" icon="i-lucide-search" size="md" variant="outline" placeholder="Rechercher un restaurant ..." />
      <UModal
          v-model:open="openCreateModal"
          title="Création de restaurant"
          description="Créer un restaurant avec le formulaire ci-dessous"
      >
        <UButton class="cursor-pointer" icon="i-lucide-plus">Créer un nouveau restaurant</UButton>

        <template #body>
          <UForm :schema="schema" :state="state" class="space-y-4">
            <UFormField label="Nom" name="name">
              <UInput v-model="state.name" class="w-full" />
            </UFormField>

            <UFormField label="Adresse" name="location">
              <UInput v-model="state.location" class="w-full" />
            </UFormField>

            <UFormField label="Code postal" name="zip">
              <UInput v-model="state.zip" class="w-full" />
            </UFormField>

            <UFormField label="Ville" name="city">
              <UInput v-model="state.city" class="w-full" />
            </UFormField>

            <UFormField label="Adresse mail" name="email">
              <UInput v-model="state.email" class="w-full" />
            </UFormField>

            <UFormField label="Mot de passe" name="password">
              <UInput v-model="state.password" class="w-full" />
            </UFormField>

            <UButton type="submit" class="cursor-pointer flex justify-center w-full" :disabled="!isFormValid" @click="createRestaurant">
              Créer le restaurant
            </UButton>
          </UForm>
        </template>
      </UModal>
    </div>

    <LazyUTable v-model:global-filter="search" :data="restaurants" :columns="columns" :loading="restaurantPending" class="flex-1" />
  </UPageCard>
</template>