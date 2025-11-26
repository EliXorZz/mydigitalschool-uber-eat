<script setup lang="ts">
import * as z from "zod";
import ItemCard from "~/components/ItemCard.vue";
import {UButton} from "#components";
import type {TabsItem} from "#ui/components/Tabs.vue";
import type {Restaurant, RestaurantsResponse} from "~/types/restaurant";
import type {Item, ItemsResponse} from "~/types/item";
import type {TableColumn} from "#ui/components/Table.vue";
import type {Order} from "~/types/order";

definePageMeta({
  layout: 'default',
  ssr: false,
  middleware: ['auth'],
  roles: ['owner']
})

const tabs: TabsItem[] = [
  {
    label: $t('dashboard.tabs.restaurant'),
    icon: 'i-lucide-store',
    slot: 'restaurant',
  },
  {
    label: $t('dashboard.tabs.dishes'),
    icon: 'i-lucide-utensils',
    slot: 'dishes'
  },
  {
    label: $t('dashboard.tabs.orders'),
    icon: 'i-lucide-receipt',
    slot: 'orders'
  }
]

const { data: restaurant } = await useAsyncData<Restaurant>(
    `restaurant:me`,
    async () => {
      const restaurants = await $fetch<RestaurantsResponse>('/api/restaurants')
      const restaurant = restaurants[0]

      if (restaurant == null)
        throw createError({
          statusCode: 404,
          message: $t('dashboard.errors.restaurantNotFound'),
          fatal: true
        })

      return restaurant
    }
)

const { data: items } = await useAsyncData<Item[]>(
    `items:me`,
    async () => {
      const response = await $fetch<ItemsResponse>('/api/items')
      return response
          .find(r => r.id_restaurant === restaurant.value?.id)
          ?.items ?? []
    }
)

const { data: orders, pending: ordersPending } = await useAsyncData<Order[]>(
    `orders:me`,
    async () => [
      { id: 32, name: 'M. Dylan', date: 'Lundi 21 février 12:32', total: 23.4, items: [] },
      { id: 35, name: 'M. Alex', date: 'Lundi 12 février 12:32', total: 50.4, items: [] },
      { id: 36, name: 'M. Raphael', date: 'Lundi 21 février 12:32', total: 12.4, items: [] }
    ]
)

const columns: TableColumn<Order>[] = [
  { accessorKey: 'id', header: '#', cell: ({ row }) => `#${row.getValue('id')}` },
  { accessorKey: 'name', header: $t('dashboard.orders.customerName'), cell: ({ row }) => h('div', { class: 'font-bold' }, row.getValue('name')) },
  { accessorKey: 'date', header: $t('dashboard.orders.date'), cell: ({ row }) => h('div', {}, row.getValue('date')) },
  { accessorKey: 'total', header: $t('dashboard.orders.total'), cell: ({ row }) => h('div', {}, `${row.getValue('total')}€`) },
  { id: 'actions', header: $t('dashboard.orders.actions'), cell: () => h(UButton, { class: 'cursor-pointer', size: 'xs', variant: 'solid', color: 'error', label: $t('dashboard.orders.cancel') }) },
]

// Dish form
const schemaDish = z.object({
  name: z.string($t('dashboard.forms.dish.name')),
  price: z.number($t('dashboard.forms.dish.price')),
  description: z.string($t('dashboard.forms.dish.description')),
  image: z.string($t('dashboard.forms.dish.image')),
})

type SchemaDish = z.output<typeof schemaDish>
const stateDish = reactive<Partial<SchemaDish>>({
  name: undefined,
  price: undefined,
  description: undefined,
  image: undefined
})
const isFormValidDish = computed(() => schemaDish.safeParse(stateDish).success)

// Restaurant form
const schemaRestaurant = z.object({
  name: z.string($t('dashboard.forms.restaurant.name')),
  location: z.string($t('dashboard.forms.restaurant.location')),
  zip: z.string($t('dashboard.forms.restaurant.zip')).regex(/^\d{5}$/, $t('dashboard.forms.restaurant.zipFormat')),
  city: z.string($t('dashboard.forms.restaurant.city')),
})

type SchemaRestaurant = z.output<typeof schemaRestaurant>
const stateRestaurant = reactive<Partial<SchemaRestaurant>>({
  name: 'Le chilli',
  location: 'Rue de la paix',
  zip: '74000',
  city: 'Annecy'
})
const isFormValidRestaurant = computed(() => schemaRestaurant.safeParse(stateRestaurant).success)

const openUpdateDishModal = ref(false)
function clickOpenUpdateDishModal(item: Item) {
  stateDish.name = item.name
  stateDish.price = item.price
  stateDish.description = item.description
  stateDish.image = item.image
  openUpdateDishModal.value = true
}

function updateRestaurant() {}
function updateDish() { openUpdateDishModal.value = false }
function deleteDish() { openUpdateDishModal.value = false }
</script>

<template>
  <UMain class="p-10">
    <UTabs :items="tabs">
      <template #restaurant>
        <UPageCard :title="$t('dashboard.tabs.restaurant')" class="mt-8">
          <UForm :schema="schemaRestaurant" :state="stateRestaurant" class="space-y-4">
            <UFormField label="$t('dashboard.forms.restaurant.name')" name="name">
              <UInput v-model="stateRestaurant.name" class="w-full" />
            </UFormField>
            <UFormField label="$t('dashboard.forms.restaurant.location')" name="location">
              <UInput v-model="stateRestaurant.location" class="w-full" />
            </UFormField>
            <UFormField label="$t('dashboard.forms.restaurant.zip')" name="zip">
              <UInput v-model="stateRestaurant.zip" class="w-full" />
            </UFormField>
            <UFormField label="$t('dashboard.forms.restaurant.city')" name="city">
              <UInput v-model="stateRestaurant.city" class="w-full" />
            </UFormField>
            <UButton type="submit" class="cursor-pointer flex justify-center w-full" :disabled="!isFormValidRestaurant" @click="updateRestaurant">
              {{ $t('dashboard.forms.restaurant.updateButton') }}
            </UButton>
          </UForm>
        </UPageCard>
      </template>

      <template #dishes>
        <section class="flex flex-col gap-15 p-20">
          <h2 class="text-center text-5xl font-bold">{{ $t('dashboard.tabs.dishes') }}</h2>
          <div class="flex flex-wrap gap-10 justify-center">
            <div v-for="item in items" :key="item.id">
              <UModal
                  v-model:open="openUpdateDishModal"
                  :title="$t('dashboard.forms.dish.updateTitle')"
                  :description="$t('dashboard.forms.dish.updateDescription')"
              >
                <template #body>
                  <UForm :schema="schemaDish" :state="stateDish" class="space-y-4">
                    <UFormField label="$t('dashboard.forms.dish.name')" name="name">
                      <UInput v-model="stateDish.name" class="w-full" />
                    </UFormField>
                    <UFormField label="$t('dashboard.forms.dish.price')" name="price">
                      <UInput v-model="stateDish.price" class="w-full" />
                    </UFormField>
                    <UFormField label="$t('dashboard.forms.dish.description')" name="description">
                      <UInput v-model="stateDish.description" class="w-full" />
                    </UFormField>
                    <UFormField label="$t('dashboard.forms.dish.image')" name="image">
                      <UInput v-model="stateDish.image" class="w-full" />
                    </UFormField>
                    <div class="flex flex-col gap-4 mt-10">
                      <UButton type="submit" class="cursor-pointer flex justify-center w-full" :disabled="!isFormValidDish" @click="updateDish">
                        {{ $t('dashboard.forms.dish.updateButton') }}
                      </UButton>
                      <UButton type="submit" class="cursor-pointer flex justify-center w-full" color="error" @click="deleteDish">
                        {{ $t('dashboard.forms.dish.deleteButton') }}
                      </UButton>
                    </div>
                  </UForm>
                </template>
              </UModal>
              <ItemCard
                  class="cursor-pointer"
                  :name="item.name"
                  :price="item.price"
                  :description="item.description"
                  :image="item.image"
                  @click="clickOpenUpdateDishModal(item)"
              />
            </div>
          </div>
        </section>
      </template>

      <template #orders>
        <UPageCard :title="$t('dashboard.tabs.orders')" class="mt-8">
          <LazyUTable :data="orders" :columns="columns" :loading="ordersPending" class="flex-1" />
        </UPageCard>
      </template>
    </UTabs>
  </UMain>
</template>