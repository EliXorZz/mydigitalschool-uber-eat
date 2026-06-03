<script setup lang="ts">
import * as z from 'zod'
import ItemCard from '~/components/ItemCard.vue'
import { UButton } from '#components'
import type { TabsItem } from '#ui/components/Tabs.vue'
import type { Restaurant, RestaurantsResponse } from '~/types/restaurant'
import type { Dish } from '~/types/dish'
import type { TableColumn } from '#ui/components/Table.vue'
import type { Order } from '~/types/order'

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
    slot: 'restaurant'
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

const { data: restaurantResponse } = await useAsyncData(
  `restaurant:me`,
  async () => {
    const restaurantsResp = await $fetch('/api/restaurants')
    const restaurant = restaurantsResp.data?.[0]

    if (restaurant == null)
      throw createError({
        statusCode: 404,
        message: $t('dashboard.errors.restaurantNotFound'),
        fatal: true
      })

    return restaurant
  }
)

const restaurant = computed(() => restaurantResponse.value ?? null)

const { data: items } = await useAsyncData<Dish[]>(
  `items:me`,
  async () => {
    const response = await $fetch('/api/items')
    // response may be an array or paginated; normalize
    const list = Array.isArray(response) ? response : response.data ?? []
    return list
      .find((r: any) => r.id_restaurant === restaurant.value?.id)
      ?.items ?? []
  }
)

const { data: ordersResponse, pending: ordersPending, refresh: refreshOrders } = await useAsyncData(
  `orders:me`,
  async () => {
    // Fetch orders for the current restaurant via restaurant-specific endpoint
    if (!restaurant.value?.id) return []

    const resp = await $api(`/api/restaurants/${restaurant.value.id}/orders`)
    // Laravel returns paginator; extract data
    return resp.data ?? []
  }
)

const orders = computed(() => ordersResponse.value ?? [])

// Track expanded rows by order id
const expanded = ref<Record<number, boolean>>({})
function toggleExpand(id: number) {
  expanded.value[id] = !expanded.value[id]
}

const columns: TableColumn<Order>[] = [
  { accessorKey: 'id', header: '#', cell: ({ row }) => `#${row.getValue('id')}` },
  { accessorKey: 'name', header: $t('dashboard.orders.customerName'), cell: ({ row }) => h('div', { class: 'font-bold' }, row.getValue('name')) },
  { accessorKey: 'date', header: $t('dashboard.orders.date'), cell: ({ row }) => h('div', {}, row.getValue('date')) },
  { accessorKey: 'total', header: $t('dashboard.orders.total'), cell: ({ row }) => h('div', {}, `${row.getValue('total')}€`) },
  { id: 'actions', header: $t('dashboard.orders.actions'), cell: () => h(UButton, { class: 'cursor-pointer', size: 'xs', variant: 'solid', color: 'error', label: $t('dashboard.orders.cancel') }) }
]

// Dish form
const schemaDish = z.object({
  name: z.string($t('dashboard.forms.dish.name')),
  price: z.number($t('dashboard.forms.dish.price')),
  description: z.string($t('dashboard.forms.dish.description')),
  image: z.string($t('dashboard.forms.dish.image'))
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
  city: z.string($t('dashboard.forms.restaurant.city'))
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
function clickOpenUpdateDishModal(item: Dish) {
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
        <UPageCard
          :title="$t('dashboard.tabs.restaurant')"
          class="mt-8"
        >
          <UForm
            :schema="schemaRestaurant"
            :state="stateRestaurant"
            class="space-y-4"
          >
            <UFormField
              :label="$t('dashboard.forms.restaurant.name')"
              name="name"
            >
              <UInput
                v-model="stateRestaurant.name"
                class="w-full"
              />
            </UFormField>
            <UFormField
              :label="$t('dashboard.forms.restaurant.location')"
              name="location"
            >
              <UInput
                v-model="stateRestaurant.location"
                class="w-full"
              />
            </UFormField>
            <UFormField
              :label="$t('dashboard.forms.restaurant.zip')"
              name="zip"
            >
              <UInput
                v-model="stateRestaurant.zip"
                class="w-full"
              />
            </UFormField>
            <UFormField
              :label="$t('dashboard.forms.restaurant.city')"
              name="city"
            >
              <UInput
                v-model="stateRestaurant.city"
                class="w-full"
              />
            </UFormField>
            <UButton
              type="submit"
              class="cursor-pointer flex justify-center w-full"
              :disabled="!isFormValidRestaurant"
              @click="updateRestaurant"
            >
              {{ $t('dashboard.forms.restaurant.updateButton') }}
            </UButton>
          </UForm>
        </UPageCard>
      </template>

      <template #dishes>
        <section class="flex flex-col gap-15 p-20">
          <h2 class="text-center text-5xl font-bold">
            {{ $t('dashboard.tabs.dishes') }}
          </h2>
          <div class="flex flex-wrap gap-10 justify-center">
            <div
              v-for="item in items"
              :key="item.id"
            >
              <UModal
                v-model:open="openUpdateDishModal"
                :title="$t('dashboard.forms.dish.updateTitle')"
                :description="$t('dashboard.forms.dish.updateDescription')"
              >
                <template #body>
                  <UForm
                    :schema="schemaDish"
                    :state="stateDish"
                    class="space-y-4"
                  >
                    <UFormField
                      :label="$t('dashboard.forms.dish.name')"
                      name="name"
                    >
                      <UInput
                        v-model="stateDish.name"
                        class="w-full"
                      />
                    </UFormField>
                    <UFormField
                      :label="$t('dashboard.forms.dish.price')"
                      name="price"
                    >
                      <UInput
                        v-model="stateDish.price"
                        class="w-full"
                      />
                    </UFormField>
                    <UFormField
                      :label="$t('dashboard.forms.dish.description')"
                      name="description"
                    >
                      <UInput
                        v-model="stateDish.description"
                        class="w-full"
                      />
                    </UFormField>
                    <UFormField
                      :label="$t('dashboard.forms.dish.image')"
                      name="image"
                    >
                      <UInput
                        v-model="stateDish.image"
                        class="w-full"
                      />
                    </UFormField>
                    <div class="flex flex-col gap-4 mt-10">
                      <UButton
                        type="submit"
                        class="cursor-pointer flex justify-center w-full"
                        :disabled="!isFormValidDish"
                        @click="updateDish"
                      >
                        {{ $t('dashboard.forms.dish.updateButton') }}
                      </UButton>
                      <UButton
                        type="submit"
                        class="cursor-pointer flex justify-center w-full"
                        color="error"
                        @click="deleteDish"
                      >
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
          <UPageCard
            :title="$t('dashboard.tabs.orders')"
            class="mt-8"
          >
            <div v-if="ordersPending" class="p-6">Chargement...</div>
            <div v-else-if="!orders || orders.length === 0" class="p-6 text-gray-500">Aucune commande</div>
            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3" />
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <template v-for="order in orders" :key="order.id">
                    <tr>
                      <td class="px-6 py-4 whitespace-nowrap">#{{ order.id }}</td>
                      <td class="px-6 py-4 whitespace-nowrap">{{ order.user?.name ?? order.name ?? '—' }}</td>
                      <td class="px-6 py-4 whitespace-nowrap">{{ new Date(order.created_at).toLocaleString() }}</td>
                      <td class="px-6 py-4 whitespace-nowrap">{{ order.total }}€</td>
                      <td class="px-6 py-4 whitespace-nowrap text-right">
                        <UButton size="xs" variant="outline" @click="toggleExpand(order.id)">{{ expanded[order.id] ? 'Fermer' : 'Voir' }}</UButton>
                      </td>
                    </tr>

                    <tr v-if="expanded[order.id]">
                      <td colspan="5" class="px-6 py-4 bg-gray-50">
                        <div class="space-y-2">
                          <div v-for="(dish, idx) in order.dishes" :key="idx" class="flex justify-between items-center py-2">
                            <div class="flex items-center gap-4">
                              <img v-if="dish.image" :src="dish.image" alt="" class="w-12 h-12 object-cover rounded" />
                              <div>
                                <div class="font-medium">{{ dish.name }}</div>
                                <div class="text-sm text-gray-500">{{ dish.description }}</div>
                              </div>
                            </div>
                            <div class="text-sm">x{{ dish.pivot?.quantity ?? dish.quantity ?? 1 }}</div>
                          </div>
                        </div>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </UPageCard>
      </template>
    </UTabs>
  </UMain>
</template>
