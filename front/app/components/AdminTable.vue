<script setup lang="ts">
import * as z from 'zod'
import type { Restaurant, RestaurantType } from '~/types/restaurant'
import type { TableColumn } from '#ui/components/Table.vue'
import { LazyModalConfirmation, UBadge, UButton } from '#components'

const { $api } = useNuxtApp()
const toast = useToast()

// ─── Data fetching ────────────────────────────────────────────────────────────

const { data: restaurantsResponse, pending: restaurantPending, refresh } = await useAsyncData(
  'admin:restaurants',
  () => $api('/api/restaurants')
)

const restaurants = computed<Restaurant[]>(() => (restaurantsResponse.value as any)?.data ?? [])

const { data: typesData } = await useAsyncData('restaurant-types', async () => {
  const resp: any = await $api('/api/restaurant-types')
  return resp?.data ?? []
})
const restaurantTypes = computed<RestaurantType[]>(() => typesData.value ?? [])
const typeOptions = computed(() => restaurantTypes.value.map(t => ({ label: t.name, value: t.id })))

const { data: ownersData } = await useAsyncData('admin:owners', async () => {
  try {
    const resp: any = await $api('/api/users/owners')
    return resp?.data ?? []
  } catch {
    return []
  }
})
const ownerOptions = computed(() =>
  (ownersData.value ?? []).map((u: any) => ({ label: `${u.name} — ${u.email}`, value: u.id }))
)

// ─── Table columns ────────────────────────────────────────────────────────────

const search = ref('')

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
    cell: ({ row }) => h('div', {}, row.getValue('city') ?? '-')
  },
  {
    id: 'type',
    header: $t('restaurants.typeHeader'),
    cell: ({ row }) => h(UBadge, { color: 'neutral', variant: 'outline' }, () => row.original.type?.name ?? '-')
  },
  {
    id: 'owner',
    header: $t('restaurants.ownerIdLabel'),
    cell: ({ row }) => h('div', { class: 'text-sm text-gray-600' }, row.original.owner?.name ?? '-')
  },
  {
    id: 'actions',
    header: $t('restaurants.actionsHeader'),
    cell: ({ row }) => h('div', { class: 'flex gap-2' }, [
      h(UButton, {
        class: 'cursor-pointer',
        icon: 'i-lucide-trash',
        size: 'xs',
        variant: 'solid',
        color: 'error',
        onClick: () => deleteRestaurant(row.original)
      })
    ])
  }
]

// ─── Create form ──────────────────────────────────────────────────────────────

const openCreateModal = ref(false)
const overlay = useOverlay()
const confirmModal = overlay.create(LazyModalConfirmation)

const priceScoreOptions = [
  { label: $t('restaurants.priceScoreOptions.1'), value: 1 },
  { label: $t('restaurants.priceScoreOptions.2'), value: 2 },
  { label: $t('restaurants.priceScoreOptions.3'), value: 3 },
  { label: $t('restaurants.priceScoreOptions.4'), value: 4 },
]

const schema = z.object({
  name: z.string({ required_error: $t('restaurants.validation.name') }).min(1, $t('restaurants.validation.name')),
  description: z.string().optional(),
  city: z.string().optional(),
  image: z.string().optional(),
  price_score: z.number().optional(),
  type_id: z.number().optional(),
  owner_id: z.number({ required_error: $t('restaurants.validation.ownerId'), invalid_type_error: $t('restaurants.validation.ownerId') }).min(1, $t('restaurants.validation.ownerId')),
})

type Schema = z.output<typeof schema>

const state = reactive<Partial<Schema>>({
  name: undefined,
  description: undefined,
  city: undefined,
  image: undefined,
  price_score: undefined,
  type_id: undefined,
  owner_id: undefined
})

const isFormValid = computed(() => schema.safeParse(state).success)

function resetForm() {
  state.name = undefined
  state.description = undefined
  state.city = undefined
  state.image = undefined
  state.price_score = undefined
  state.type_id = undefined
  state.owner_id = undefined
}

async function createRestaurant() {
  try {
    await $api('/api/restaurants', {
      method: 'POST',
      body: state
    })
    toast.add({ title: $t('restaurants.createSuccess'), color: 'success' })
    openCreateModal.value = false
    resetForm()
    await refresh()
  } catch (e: any) {
    toast.add({
      title: $t('restaurants.createError'),
      description: e?.data?.message,
      color: 'error'
    })
  }
}

async function deleteRestaurant(restaurant: Restaurant) {
  const response = confirmModal.open({
    title: $t('restaurants.deleteConfirmationTitle'),
    description: $t('restaurants.deleteConfirmationDescription', { name: restaurant.name }),
    action: $t('restaurants.deleteAction')
  })

  const success = await response.result
  if (!success) return

  try {
    await $api(`/api/restaurants/${restaurant.id}`, { method: 'DELETE' })
    toast.add({
      title: $t('restaurants.deleteSuccessTitle'),
      description: $t('restaurants.deleteSuccessDescription', { name: restaurant.name }),
      color: 'success'
    })
    await refresh()
  } catch (e: any) {
    toast.add({
      title: $t('restaurants.deleteError'),
      description: e?.data?.message,
      color: 'error'
    })
  }
}
</script>

<template>
  <UPageCard :title="$t('restaurants.listTitle')" spotlight-color="primary" class="overflow-hidden">
    <div class="flex flex-wrap gap-3 justify-between items-center py-3">
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
        <UButton class="cursor-pointer" icon="i-lucide-plus">
          {{ $t('restaurants.createButton') }}
        </UButton>

        <template #body>
          <UForm :schema="schema" :state="state" class="space-y-4" @submit="createRestaurant">

            <div class="grid grid-cols-2 gap-4">
              <UFormField :label="$t('restaurants.name')" name="name" class="col-span-2">
                <UInput v-model="state.name" class="w-full" :placeholder="$t('restaurants.name')" />
              </UFormField>

              <UFormField :label="$t('restaurants.description')" name="description" class="col-span-2">
                <UTextarea v-model="state.description" class="w-full" :rows="2" :placeholder="$t('restaurants.description')" />
              </UFormField>

              <UFormField :label="$t('restaurants.city')" name="city">
                <UInput v-model="state.city" class="w-full" :placeholder="$t('restaurants.city')" />
              </UFormField>

              <UFormField :label="$t('restaurants.priceScore')" name="price_score">
                <USelect
                  v-model="state.price_score"
                  :items="priceScoreOptions"
                  value-key="value"
                  label-key="label"
                  class="w-full"
                />
              </UFormField>

              <UFormField :label="$t('restaurants.type')" name="type_id">
                <USelect
                  v-model="state.type_id"
                  :items="typeOptions"
                  value-key="value"
                  label-key="label"
                  :placeholder="$t('restaurants.type')"
                  class="w-full"
                />
              </UFormField>

              <UFormField :label="$t('restaurants.ownerIdLabel')" name="owner_id">
                <USelect
                  v-model="state.owner_id"
                  :items="ownerOptions"
                  value-key="value"
                  label-key="label"
                  :placeholder="$t('restaurants.owner')"
                  class="w-full"
                />
              </UFormField>

              <div class="col-span-2">
                <ImageUpload
                  v-model="state.image"
                  :label="$t('restaurants.image')"
                />
              </div>
            </div>

            <UButton
              type="submit"
              class="cursor-pointer justify-center w-full"
              :disabled="!isFormValid"
            >
              {{ $t('restaurants.createRestaurantButton') }}
            </UButton>
          </UForm>
        </template>
      </UModal>
    </div>

    <div class="overflow-x-auto -mx-4 sm:mx-0">
      <LazyUTable
        v-model:global-filter="search"
        :data="restaurants"
        :columns="columns"
        :loading="restaurantPending"
        class="min-w-full"
      />
    </div>
  </UPageCard>
</template>
