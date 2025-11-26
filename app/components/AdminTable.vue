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
    header: $t('restaurants.idHeader'),
    cell: ({ row }) => `#${row.getValue('id')}`
  },
  {
    accessorKey: 'name',
    header: $t('restaurants.nameHeader'),
    cell: ({ row }) => h('div', { class: 'font-bold' }, row.getValue('name'))
  },
  {
    accessorKey: 'city',
    header: $t('restaurants.cityHeader'),
    cell: ({ row }) => h('div', {}, row.getValue('city'))
  },
  {
    accessorKey: 'type',
    header: $t('restaurants.typeHeader'),
    cell: ({ row }) => h(UBadge, { color: 'neutral', variant: 'outline' }, () => row.getValue('type'))
  },
  {
    id: 'actions',
    header: $t('restaurants.actionsHeader'),
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
  name: z.string($t('restaurants.validation.name')),
  location: z.string($t('restaurants.validation.location')),
  zip: z.string($t('restaurants.validation.zip'))
      .regex(/^\d{5}$/, $t('restaurants.validation.zipFormat')),
  city: z.string($t('restaurants.validation.city')),
  email: z.string().email($t('restaurants.validation.email')),
  password: z.string($t('restaurants.validation.password'))
})

type Schema = z.output<typeof schema>

const state = reactive<Partial<Schema>>({
  name: undefined,
  location: undefined,
  zip: undefined,
  city: undefined,
  email: undefined,
  password: undefined
})

const isFormValid = computed(() => schema.safeParse(state).success)

async function createRestaurant() {
  openCreateModal.value = false
}

async function deleteRestaurant(restaurant: Restaurant) {
  const response = confirmModal.open({
    title: $t('restaurants.deleteConfirmationTitle'),
    description: $t('restaurants.deleteConfirmationDescription', { name: restaurant.name }),
    action: $t('restaurants.deleteAction')
  })

  const success = await response.result

  if (success)
    toast.add({
      title: $t('restaurants.deleteSuccessTitle'),
      description: $t('restaurants.deleteSuccessDescription', { name: restaurant.name }),
      color: 'success'
    })
}
</script>

<template>
  <UPageCard :title="$t('restaurants.listTitle')" spotlight-color="primary">
    <div class="flex justify-between items-center py-3">
      <UInput
          v-model="search"
          icon="i-lucide-search"
          size="md"
          variant="outline"
          :placeholder="$t('restaurants.searchPlaceholder')"
      />
      <UModal
          v-model:open="openCreateModal"
          :title="$t('restaurants.createModalTitle')"
          :description="$t('restaurants.createModalDescription')"
      >
        <UButton class="cursor-pointer" icon="i-lucide-plus">{{ $t('restaurants.createButton') }}</UButton>

        <template #body>
          <UForm :schema="schema" :state="state" class="space-y-4">
            <UFormField :label="$t('restaurants.name')" name="name">
              <UInput v-model="state.name" class="w-full" />
            </UFormField>

            <UFormField :label="$t('restaurants.location')" name="location">
              <UInput v-model="state.location" class="w-full" />
            </UFormField>

            <UFormField :label="$t('restaurants.zip')" name="zip">
              <UInput v-model="state.zip" class="w-full" />
            </UFormField>

            <UFormField :label="$t('restaurants.city')" name="city">
              <UInput v-model="state.city" class="w-full" />
            </UFormField>

            <UFormField :label="$t('restaurants.email')" name="email">
              <UInput v-model="state.email" class="w-full" />
            </UFormField>

            <UFormField :label="$t('restaurants.password')" name="password">
              <UInput v-model="state.password" class="w-full" />
            </UFormField>

            <UButton type="submit" class="cursor-pointer flex justify-center w-full" :disabled="!isFormValid" @click="createRestaurant">
              {{ $t('restaurants.createRestaurantButton') }}
            </UButton>
          </UForm>
        </template>
      </UModal>
    </div>

    <LazyUTable v-model:global-filter="search" :data="restaurants" :columns="columns" :loading="restaurantPending" class="flex-1" />
  </UPageCard>
</template>