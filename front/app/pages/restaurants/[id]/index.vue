<script setup lang="ts">
import type { Restaurant } from '~/types/restaurant'
import type { Dish } from '~/types/dish'

const { $api } = useNuxtApp()

const route = useRoute()
const id = route.params.id as string

const { data: restaurant } = await useAsyncData<Restaurant>(
  `restaurant:${id}`,
  async () => {
    const response = await $api<{ data: Restaurant }>(`/api/restaurants/${id}`)
    const restaurant = response.data

    if (restaurant == null)
      throw createError({
        statusCode: 404,
        message: $t('restaurant.notFound'),
        fatal: true
      })

    return restaurant
  }
)

const perPageItems = 12
const pageItems = ref(1)
const itemsState = ref<Dish[]>([])
const loadingItems = ref(false)
const lastItemsPage = ref(1)

async function loadItems(reset = false) {
  if (loadingItems.value) return
  if (reset) {
    pageItems.value = 1
    itemsState.value = []
  }

  loadingItems.value = true

  if (restaurant.value?.id == null) {
    loadingItems.value = false
    return
  }

  const resp: any = await $api(`/api/restaurants/${restaurant.value.id}/dishes`, {
    query: { page: pageItems.value, per_page: perPageItems }
  })
  itemsState.value = resp?.data ?? []
  lastItemsPage.value = resp?.last_page ?? 1
  loadingItems.value = false
}

async function prevPageItems() {
  if (pageItems.value > 1) {
    pageItems.value -= 1
    await loadItems()
  }
}

async function nextPageItems() {
  if (pageItems.value < lastItemsPage.value) {
    pageItems.value += 1
    await loadItems()
  }
}

watch(restaurant, (r) => {
  if (r?.id) loadItems(true)
}, { immediate: true })

useHead({ title: `${restaurant.value?.name} | ${$t('restaurant.pageTitle')}` })

useSeoMeta({
  title: restaurant.value?.name,
  ogTitle: restaurant.value?.name,
  description: restaurant.value?.description,
  ogDescription: restaurant.value?.description,
  ogImage: restaurant.value ? `http://localhost:3000${restaurant.value.image}` : undefined,
  twitterCard: 'summary_large_image'
})
</script>

<template>
  <UMain v-if="restaurant != null">
    <section class="relative flex flex-col items-center justify-center text-center py-40 px-4">
      <img
        class="absolute inset-0 w-full h-full object-cover brightness-50"
        :src="restaurant.image"
        :alt="restaurant.name"
      >

      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" />

      <div class="relative z-10 text-white space-y-6 max-w-2xl">
        <h2 class="text-5xl font-bold -translate-y-7">
          {{ restaurant.name }}
        </h2>
      </div>
    </section>

    <div class="-translate-y-20">
      <div class="flex justify-center px-20">
        <UCard class="z-20 w-full max-w-200 shadow-lg">
          <template #default>
            <img
              :src="restaurant.image"
              :alt="restaurant.name"
              class="w-full h-48 object-cover rounded-lg mb-4"
            >

            <div class="flex justify-between items-center mb-2">
              <h2 class="text-xl font-bold text-gray-800">
                {{ restaurant.name }}
              </h2>
              <span class="text-sm text-gray-600">{{ restaurant.city }}</span>
            </div>

            <p class="text-sm text-gray-700 italic mb-2">
              {{ restaurant.type.name }}
            </p>

            <p class="text-gray-600 mb-3">
              {{ restaurant.description }}
            </p>

            <div class="flex flex-wrap gap-2 mb-3">
              <UBadge
                v-for="feature in restaurant.features"
                :key="feature"
                color="neutral"
                variant="outline"
              >
                {{ feature }}
              </UBadge>
            </div>

            <div class="flex justify-between items-center">
              <StarRating :rating="restaurant.score" />
              <PriceRange :range="restaurant.price_score" />
            </div>
          </template>
        </UCard>
      </div>

      <section class="flex flex-col gap-15 p-20">
        <h2 class="text-center text-5xl font-bold">
          {{ $t('restaurant.takeawayTitle') }}
        </h2>

        <div v-if="loadingItems" class="flex justify-center py-10 text-gray-500">
          Chargement...
        </div>

        <div v-else-if="itemsState.length === 0" class="text-center py-10 text-gray-500">
          Aucun plat disponible
        </div>

        <div v-else class="flex flex-wrap gap-10 justify-center">
          <NuxtLink
            v-for="item in itemsState"
            :key="item.id"
            :to="{ name: 'restaurants-id-items-item_id', params: { item_id: item.id, id: restaurant.id } }"
          >
            <ItemCard
              class="cursor-pointer"
              :name="item.name"
              :price="item.price"
              :description="item.description"
              :image="item.image"
            />
          </NuxtLink>
        </div>

        <div v-if="lastItemsPage > 1" class="flex justify-center items-center gap-4">
          <UButton variant="outline" :disabled="pageItems <= 1" @click="prevPageItems">
            {{ $t('common.prev') }}
          </UButton>
          <span>{{ pageItems }} / {{ lastItemsPage }}</span>
          <UButton variant="outline" :disabled="pageItems >= lastItemsPage" @click="nextPageItems">
            {{ $t('common.next') }}
          </UButton>
        </div>
      </section>
    </div>
  </UMain>
</template>
