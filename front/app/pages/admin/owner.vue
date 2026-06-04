<script setup lang="ts">
import * as z from 'zod'
import type { TabsItem } from '#ui/components/Tabs.vue'
import type { Restaurant, RestaurantType, RestaurantsResponse } from '~/types/restaurant'
import type { Dish } from '~/types/dish'
import type { Order } from '~/types/order'
import type { Paginator } from '~/types/api'

definePageMeta({
  layout: 'default',
  ssr: false,
  middleware: ['auth'],
  roles: ['restaurant']
})

const { $api } = useNuxtApp()
const authStore = useAuthentificationStore()
const { account } = storeToRefs(authStore)
const orderStore = useOrderStore()
const toast = useToast()

const tabs: TabsItem[] = [
  { label: $t('dashboard.tabs.restaurant'), icon: 'i-lucide-store', slot: 'restaurant' },
  { label: $t('dashboard.tabs.dishes'), icon: 'i-lucide-utensils', slot: 'dishes' },
  { label: $t('dashboard.tabs.orders'), icon: 'i-lucide-receipt', slot: 'orders' }
]

// ─── Restaurant types (for select) ───────────────────────────────────────────

const { data: typesData } = await useAsyncData('owner:restaurant-types', async () => {
  const resp = await $api<{ data: RestaurantType[] }>('/api/restaurant-types')
  return resp?.data ?? []
})
const typeOptions = computed(() =>
  (typesData.value ?? []).map((t: RestaurantType) => ({ label: t.name, value: t.id }))
)

const priceScoreOptions = [
  { label: $t('restaurants.priceScoreOptions.1'), value: 1 },
  { label: $t('restaurants.priceScoreOptions.2'), value: 2 },
  { label: $t('restaurants.priceScoreOptions.3'), value: 3 },
  { label: $t('restaurants.priceScoreOptions.4'), value: 4 },
]

// ─── Restaurant ──────────────────────────────────────────────────────────────

const { data: restaurant, refresh: refreshRestaurant } = await useAsyncData<Restaurant>(
  'restaurant:me',
  async () => {
    const resp = await $api<RestaurantsResponse>('/api/restaurants', { query: { per_page: 100 } })
    const list = resp?.data ?? []
    const found = list.find(r => r.owner_id === account.value?.id)
    if (!found) throw createError({ statusCode: 404, message: $t('dashboard.errors.restaurantNotFound'), fatal: true })
    return found
  }
)

const schemaRestaurant = z.object({
  name: z.string({ required_error: $t('dashboard.forms.restaurant.nameRequired') }).min(1, $t('dashboard.forms.restaurant.nameRequired')),
  description: z.string().optional(),
  city: z.string().optional(),
  image: z.string().optional(),
  price_score: z.number().optional(),
  type_id: z.number().optional(),
  tags: z.string().optional(),
})

type SchemaRestaurant = z.output<typeof schemaRestaurant>

const stateRestaurant = reactive<Partial<SchemaRestaurant>>({
  name: restaurant.value?.name ?? '',
  description: restaurant.value?.description ?? '',
  city: restaurant.value?.city ?? '',
  image: restaurant.value?.image ?? '',
  price_score: restaurant.value?.price_score,
  type_id: restaurant.value?.type_id,
  tags: (restaurant.value?.tags ?? []).join(', ')
})

async function updateRestaurant() {
  if (!restaurant.value?.id) return
  try {
    const tags = stateRestaurant.tags
      ? stateRestaurant.tags.split(',').map(t => t.trim()).filter(Boolean)
      : []
    const body = { ...stateRestaurant, tags }
    await $api(`/api/restaurants/${restaurant.value.id}`, { method: 'PUT', body })
    await refreshRestaurant()
    toast.add({ title: $t('dashboard.forms.restaurant.updateSuccess'), icon: 'i-lucide-check', color: 'success' })
  } catch (e) {
    toast.add({ title: $t('dashboard.forms.restaurant.updateError'), description: (e as { data?: { message?: string } })?.data?.message, color: 'error' })
  }
}

// ─── Dishes ──────────────────────────────────────────────────────────────────

const { data: dishesData, refresh: refreshDishes } = await useAsyncData('dishes:me', async () => {
  if (!restaurant.value?.id) return []
  const resp = await $api<{ data: Dish[] }>(`/api/restaurants/${restaurant.value.id}/dishes`, { query: { per_page: 100 } })
  return resp?.data ?? []
})

const dishes = computed<Dish[]>(() => dishesData.value ?? [])

const openDishModal = ref(false)
const dishModalMode = ref<'create' | 'update'>('create')
const selectedDish = ref<Dish | null>(null)

const schemaDish = z.object({
  name: z.string({ required_error: $t('dashboard.forms.dish.nameRequired') }).min(1, $t('dashboard.forms.dish.nameRequired')),
  price: z.coerce.number({ required_error: $t('dashboard.forms.dish.priceRequired'), invalid_type_error: $t('dashboard.forms.dish.priceRequired') }).min(0.01, $t('dashboard.forms.dish.priceRequired')),
  description: z.string().optional(),
  image: z.string().optional(),
})

type SchemaDish = z.output<typeof schemaDish>

const stateDish = reactive<Partial<SchemaDish>>({
  name: undefined,
  price: undefined,
  description: undefined,
  image: undefined
})

function openCreateDish() {
  dishModalMode.value = 'create'
  selectedDish.value = null
  stateDish.name = undefined
  stateDish.price = undefined
  stateDish.description = undefined
  stateDish.image = undefined
  openDishModal.value = true
}

function openEditDish(item: Dish) {
  dishModalMode.value = 'update'
  selectedDish.value = item
  stateDish.name = item.name
  stateDish.price = item.price
  stateDish.description = item.description
  stateDish.image = item.image
  openDishModal.value = true
}

async function submitDish() {
  if (!restaurant.value?.id) return
  try {
    if (dishModalMode.value === 'create') {
      await $api(`/api/restaurants/${restaurant.value.id}/dishes`, { method: 'POST', body: stateDish })
      toast.add({ title: $t('dashboard.forms.dish.createSuccess'), icon: 'i-lucide-check', color: 'success' })
    } else if (selectedDish.value) {
      await $api(`/api/dishes/${selectedDish.value.id}`, { method: 'PUT', body: stateDish })
      toast.add({ title: $t('dashboard.forms.dish.updateSuccess'), icon: 'i-lucide-check', color: 'success' })
    }
    openDishModal.value = false
    await refreshDishes()
  } catch (e) {
    const title = dishModalMode.value === 'create' ? $t('dashboard.forms.dish.createError') : $t('dashboard.forms.dish.updateError')
    toast.add({ title, description: (e as { data?: { message?: string } })?.data?.message, color: 'error' })
  }
}

async function deleteDish() {
  if (!selectedDish.value) return
  try {
    await $api(`/api/dishes/${selectedDish.value.id}`, { method: 'DELETE' })
    toast.add({ title: $t('dashboard.forms.dish.deleteSuccess'), icon: 'i-lucide-trash', color: 'success' })
    openDishModal.value = false
    await refreshDishes()
  } catch (e) {
    toast.add({ title: $t('dashboard.forms.dish.deleteError'), description: (e as { data?: { message?: string } })?.data?.message, color: 'error' })
  }
}

// ─── Orders ──────────────────────────────────────────────────────────────────

const ordersPage = ref(1)
const ordersPerPage = 10

const { data: ordersResponse, pending: ordersPending, refresh: refreshOrders } = await useAsyncData(
  () => `orders:restaurant:${restaurant.value?.id}:${ordersPage.value}`,
  async (): Promise<Paginator<Order>> => {
    if (!restaurant.value?.id) return { data: [], current_page: 1, last_page: 1, per_page: ordersPerPage, total: 0 }
    try {
      return await $api<Paginator<Order>>(`/api/restaurants/${restaurant.value.id}/orders`, {
        query: { page: ordersPage.value, per_page: ordersPerPage }
      })
    } catch {
      return { data: [], current_page: 1, last_page: 1, per_page: ordersPerPage, total: 0 }
    }
  }
)

const ordersPagination = computed(() => ({
  current: ordersResponse.value?.current_page ?? 1,
  last: ordersResponse.value?.last_page ?? 1,
}))

function prevOrdersPage() {
  if (ordersPage.value > 1) { ordersPage.value -= 1; refreshOrders() }
}
function nextOrdersPage() {
  if (ordersPage.value < ordersPagination.value.last) { ordersPage.value += 1; refreshOrders() }
}

// ─── Real-time notifications ──────────────────────────────────────────────────

const restaurantId = computed(() => restaurant.value?.id ?? null)

useOrderNotifications(
  restaurantId,
  (newOrder) => {
    toast.add({
      title: $t('dashboard.orders.toasts.created'),
      description: `Commande #${newOrder.id} — ${newOrder.total}€`,
      color: 'info',
      icon: 'i-lucide-bell',
      duration: 8000
    })
    refreshOrders()
  },
  (updatedOrder) => {
    toast.add({
      title: $t('dashboard.orders.toasts.updated'),
      description: `Commande #${updatedOrder.id} — ${updatedOrder.state}`,
      color: 'success',
      icon: 'i-lucide-refresh-cw',
      duration: 5000
    })
    refreshOrders()
  },
  (deletedOrder) => {
    toast.add({
      title: $t('dashboard.orders.toasts.cancelled'),
      description: `Commande #${deletedOrder.id} annulée par le client`,
      color: 'warning',
      icon: 'i-lucide-x-circle',
      duration: 5000
    })
    refreshOrders()
  }
)
</script>

<template>
  <UMain class="px-4 py-6 sm:px-8 sm:py-10">
    <UTabs :items="tabs">

      <!-- ── Restaurant tab ── -->
      <template #restaurant>
        <UPageCard :title="$t('dashboard.tabs.restaurant')" class="mt-8">
          <UForm :schema="schemaRestaurant" :state="stateRestaurant" class="space-y-4" @submit="updateRestaurant">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <UFormField :label="$t('dashboard.forms.restaurant.name')" name="name" class="md:col-span-2">
                <UInput v-model="stateRestaurant.name" class="w-full" />
              </UFormField>

              <UFormField :label="$t('dashboard.forms.restaurant.description')" name="description" class="md:col-span-2">
                <UTextarea v-model="stateRestaurant.description" class="w-full" :rows="3" />
              </UFormField>

              <UFormField :label="$t('dashboard.forms.restaurant.city')" name="city">
                <UInput v-model="stateRestaurant.city" class="w-full" />
              </UFormField>

              <UFormField :label="$t('dashboard.forms.restaurant.priceScore')" name="price_score">
                <USelect
                  v-model="stateRestaurant.price_score"
                  :items="priceScoreOptions"
                  value-key="value"
                  label-key="label"
                  class="w-full"
                />
              </UFormField>

              <UFormField :label="$t('dashboard.forms.restaurant.type')" name="type_id">
                <USelect
                  v-model="stateRestaurant.type_id"
                  :items="typeOptions"
                  value-key="value"
                  label-key="label"
                  :placeholder="$t('dashboard.forms.restaurant.type')"
                  class="w-full"
                />
              </UFormField>

              <UFormField :label="$t('dashboard.forms.restaurant.tags')" name="tags">
                <UInput v-model="stateRestaurant.tags" class="w-full" placeholder="Terrasse, Livraison, Veggie..." />
              </UFormField>

              <div class="md:col-span-2">
                <ImageUpload
                  v-model="stateRestaurant.image"
                  :label="$t('dashboard.forms.restaurant.image')"
                />
              </div>
            </div>

            <UButton type="submit" class="cursor-pointer" icon="i-lucide-save">
              {{ $t('dashboard.forms.restaurant.updateButton') }}
            </UButton>
          </UForm>
        </UPageCard>
      </template>

      <!-- ── Dishes tab ── -->
      <template #dishes>
        <div class="mt-8 flex flex-col gap-6">
          <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold">{{ $t('dashboard.tabs.dishes') }}</h2>
            <UButton icon="i-lucide-plus" class="cursor-pointer" @click="openCreateDish">
              {{ $t('dashboard.forms.dish.createButton') }}
            </UButton>
          </div>

          <div v-if="dishes.length === 0" class="text-center py-10 text-gray-500">
            Aucun plat — ajoutez-en un !
          </div>

          <div v-else class="flex flex-wrap gap-6 justify-center">
            <div
              v-for="item in dishes"
              :key="item.id"
              class="cursor-pointer"
              @click="openEditDish(item)"
            >
              <ItemCard
                :name="item.name"
                :price="item.price"
                :description="item.description"
                :image="item.image"
              />
            </div>
          </div>
        </div>

        <!-- Modal create/update dish -->
        <UModal
          v-model:open="openDishModal"
          :title="dishModalMode === 'create' ? $t('dashboard.forms.dish.createTitle') : $t('dashboard.forms.dish.updateTitle')"
          :description="dishModalMode === 'create' ? $t('dashboard.forms.dish.createDescription') : $t('dashboard.forms.dish.updateDescription')"
        >
          <template #body>
            <UForm :schema="schemaDish" :state="stateDish" class="space-y-4" @submit="submitDish">
              <div class="grid grid-cols-2 gap-4">
                <UFormField :label="$t('dashboard.forms.dish.name')" name="name" class="col-span-2">
                  <UInput v-model="stateDish.name" class="w-full" />
                </UFormField>

                <UFormField :label="$t('dashboard.forms.dish.price')" name="price">
                  <UInput v-model.number="stateDish.price" type="number" step="0.01" min="0" class="w-full" />
                </UFormField>

                <div class="col-span-2">
                  <ImageUpload
                    v-model="stateDish.image"
                    :label="$t('dashboard.forms.dish.image')"
                  />
                </div>

                <UFormField :label="$t('dashboard.forms.dish.description')" name="description" class="col-span-2">
                  <UTextarea v-model="stateDish.description" class="w-full" :rows="2" />
                </UFormField>
              </div>

              <div class="flex flex-col gap-2 pt-2">
                <UButton type="submit" class="cursor-pointer justify-center w-full" icon="i-lucide-save">
                  {{ dishModalMode === 'create' ? $t('dashboard.forms.dish.createButton') : $t('dashboard.forms.dish.updateButton') }}
                </UButton>
                <UButton
                  v-if="dishModalMode === 'update'"
                  type="button"
                  color="error"
                  variant="subtle"
                  class="cursor-pointer justify-center w-full"
                  icon="i-lucide-trash"
                  @click="deleteDish"
                >
                  {{ $t('dashboard.forms.dish.deleteButton') }}
                </UButton>
              </div>
            </UForm>
          </template>
        </UModal>
      </template>

      <!-- ── Orders tab ── -->
      <template #orders>
        <div class="mt-8 flex flex-col gap-4">
          <OrderTable
            :title="$t('dashboard.tabs.orders')"
            :orders="ordersResponse"
            :loading="ordersPending"
            :show-restaurant-column="false"
            :allow-state-change="true"
            @refresh="refreshOrders"
          />

          <div v-if="ordersPagination.last > 1" class="flex justify-between items-center">
            <UButton variant="outline" :disabled="ordersPagination.current <= 1" @click="prevOrdersPage">
              {{ $t('common.prev') }}
            </UButton>
            <span>{{ ordersPagination.current }} / {{ ordersPagination.last }}</span>
            <UButton variant="outline" :disabled="ordersPagination.current >= ordersPagination.last" @click="nextOrdersPage">
              {{ $t('common.next') }}
            </UButton>
          </div>
        </div>
      </template>

    </UTabs>
  </UMain>
</template>
