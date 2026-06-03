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
const hasMoreItems = ref(true)
const loadingItems = ref(false)

async function loadItems(reset = false) {
  if (loadingItems.value) return
  if (reset) {
    pageItems.value = 1
    itemsState.value = []
    hasMoreItems.value = true
  }

  if (!hasMoreItems.value) return

  loadingItems.value = true
  const resp: any = await $api(`/api/restaurants/${restaurant.value.id}/dishes`, { query: { page: pageItems.value, per_page: perPageItems } })
  const items = resp?.data ?? []
  itemsState.value.push(...items)
  const last = resp?.last_page ?? 1
  if (pageItems.value >= last) hasMoreItems.value = false
  pageItems.value += 1
  loadingItems.value = false
}

await loadItems(true)

const items = computed(() => itemsState.value)

const loadMoreItemsTrigger = ref<HTMLElement | null>(null)
if (process.client) {
  const io = new IntersectionObserver((entries) => {
    for (const e of entries) {
      if (e.isIntersecting && hasMoreItems.value && !loadingItems.value) {
        loadItems()
      }
    }
  }, { root: null, rootMargin: '200px', threshold: 0.1 })

  watch(loadMoreItemsTrigger, (el) => {
    if (el && el instanceof HTMLElement) io.observe(el)
  })
}

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

        <div class="flex flex-wrap gap-10 justify-center">
          <NuxtLink
            v-for="item in items"
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

          <div v-if="loadingItems" class="w-full text-center py-6">Chargement...</div>
          <div v-else-if="hasMoreItems" ref="loadMoreItemsTrigger" class="w-full text-center py-6">
            <UButton variant="outline" @click="loadItems">Charger plus</UButton>
          </div>
        </div>
      </section>
    </div>
  </UMain>
</template>
