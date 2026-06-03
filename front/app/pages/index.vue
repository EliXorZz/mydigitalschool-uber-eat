<script setup lang="ts">
import type { Restaurant } from '~/types/restaurant'

const { $api } = useNuxtApp()
const searchQuery = ref('')

const perPage = 12
const page = ref(1)
const restaurantsState = ref<Restaurant[]>([])
const hasMore = ref(true)
const loading = ref(false)

async function loadRestaurants(reset = false) {
  if (loading.value) return
  if (reset) {
    page.value = 1
    restaurantsState.value = []
    hasMore.value = true
  }

  if (!hasMore.value) return

  loading.value = true
  const resp: any = await $api('/api/restaurants', { query: { search: searchQuery.value, page: page.value, per_page: perPage } })
  const items = resp?.data ?? []
  restaurantsState.value.push(...items)
  const last = resp?.last_page ?? 1
  if (page.value >= last) hasMore.value = false
  page.value += 1
  loading.value = false
}

// initial load
await loadRestaurants(true)

watch(searchQuery, async () => {
  await loadRestaurants(true)
})

// infinite scroll trigger
const loadMoreTrigger = ref<HTMLElement | null>(null)
if (process.client) {
  const io = new IntersectionObserver((entries) => {
    for (const e of entries) {
      if (e.isIntersecting && hasMore.value && !loading.value) {
        loadRestaurants()
      }
    }
  }, { root: null, rootMargin: '200px', threshold: 0.1 })

  watch(loadMoreTrigger, (el) => {
    if (el && el instanceof HTMLElement) io.observe(el)
  })
}
</script>

<template>
  <UMain>
    <section class="relative flex flex-col items-center justify-center text-center py-40 px-4">
      <img
        src="https://images.unsplash.com/photo-1600891964599-f61ba0e24092?q=80&w=2000"
        :alt="$t('home.heroImageAlt')"
        class="absolute inset-0 w-full h-full object-cover brightness-50"
      >

      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" />

      <div class="relative z-10 text-white space-y-6 max-w-2xl">
        <h2 class="text-5xl font-bold">
          {{ $t('home.heroTitle') }}
        </h2>
        <p class="text-lg text-gray-200">
          {{ $t('home.heroDescription') }}
        </p>

        <div class="flex items-center gap-2 mt-8 w-full max-w-xl mx-auto">
          <UInput
            v-model="searchQuery"
            icon="i-lucide-search"
            size="xl"
            :placeholder="$t('home.searchPlaceholder')"
            class="flex-1"
          />
        </div>
      </div>
    </section>

    <section class="py-20">
      <div
        v-if="pending"
        class="flex justify-center py-10 text-gray-500"
      >
        Chargement...
      </div>
      <div
        v-else-if="!restaurants || restaurants.length === 0"
        class="text-center py-10 text-gray-500"
      >
        Aucun restaurant trouvé
      </div>
      <div v-else class="flex flex-wrap gap-10 justify-center">
        <NuxtLink
          v-for="restaurant in restaurantsState"
          :key="restaurant.id"
          :to="{ name: 'restaurants-id', params: { id: restaurant.id } }"
        >
          <RestaurantCard
            class="cursor-pointer"
            :name="restaurant.name"
            :city="restaurant.city"
            :type="restaurant.type.name"
            :rating="restaurant.score"
            :price-range="restaurant.price_score"
            :features="[]"
            :image="restaurant.image"
          />
        </NuxtLink>

        <div v-if="loading" class="w-full text-center py-6">Chargement...</div>
        <div v-else-if="hasMore" ref="loadMoreTrigger" class="w-full text-center py-6">
          <UButton variant="outline" @click="loadRestaurants">Charger plus</UButton>
        </div>
      </div>
    </section>
  </UMain>
</template>
