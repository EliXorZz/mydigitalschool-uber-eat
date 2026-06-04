<script setup lang="ts">
import type { Restaurant } from '~/types/restaurant'

const { $api } = useNuxtApp()
const searchQuery = ref('')

const perPage = 12
const page = ref(1)
const restaurantsState = ref<Restaurant[]>([])
const loading = ref(false)
const lastPage = ref(1)

async function prevPage() {
  if (page.value > 1) {
    page.value -= 1
    await loadRestaurants()
  }
}

async function nextPage() {
  if (page.value < lastPage.value) {
    page.value += 1
    await loadRestaurants()
  }
}

async function loadRestaurants(reset = false) {
  if (loading.value) return
  if (reset) {
    page.value = 1
    restaurantsState.value = []
  }

  loading.value = true
  const resp: any = await $api('/api/restaurants', { query: { search: searchQuery.value, page: page.value, per_page: perPage } })
  const items = resp?.data ?? []
  restaurantsState.value = items
  lastPage.value = resp?.last_page ?? 1
  loading.value = false
}

// initial load
await loadRestaurants(true)

watch(searchQuery, async () => {
  await loadRestaurants(true)
})
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
      <div v-if="loading" class="flex justify-center py-10 text-gray-500">Chargement...</div>
      <div v-else-if="!loading && restaurantsState.length === 0" class="text-center py-10 text-gray-500">Aucun restaurant trouvé</div>
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

        <div class="w-full text-center py-6">
          <div v-if="loading">Chargement...</div>
          <div v-else class="flex justify-between items-center">
            <div />
            <div>
              <UButton variant="outline" :disabled="page <= 1" @click="prevPage">{{ $t('common.prev') }}</UButton>
              <span class="mx-4">{{ page }} / {{ lastPage }}</span>
              <UButton variant="outline" :disabled="page >= lastPage" @click="nextPage">{{ $t('common.next') }}</UButton>
            </div>
            <div />
          </div>
        </div>
      </div>
    </section>
  </UMain>
</template>
