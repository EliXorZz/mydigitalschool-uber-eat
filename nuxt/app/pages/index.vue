<script setup lang="ts">
import type {RestaurantsResponse} from "~/types/restaurant";

const { data: restaurants } = await useAsyncData<RestaurantsResponse>(
    'restaurants',
    () => $fetch('/api/restaurants')
)
</script>

<template>
  <UMain>
    <section class="relative flex flex-col items-center justify-center text-center py-40 px-4">
      <img
          src="https://images.unsplash.com/photo-1600891964599-f61ba0e24092?q=80&w=2000"
          :alt="$t('home.heroImageAlt')"
          class="absolute inset-0 w-full h-full object-cover brightness-50"
      />

      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"/>

      <div class="relative z-10 text-white space-y-6 max-w-2xl">
        <h2 class="text-5xl font-bold">{{ $t('home.heroTitle') }}</h2>
        <p class="text-lg text-gray-200">
          {{ $t('home.heroDescription') }}
        </p>

        <div class="flex items-center gap-2 mt-8 w-full max-w-xl mx-auto">
          <UInput
              icon="i-lucide-search"
              size="xl"
              :placeholder="$t('home.searchPlaceholder')"
              class="flex-1"
          />
        </div>
      </div>
    </section>

    <section class="py-20">
      <div class="flex flex-wrap gap-10 justify-center">
        <NuxtLink
            v-for="restaurant in restaurants"
            :key="restaurant.id"
            :to="{ name: 'restaurants-slug', params: { slug: restaurant.slug } }"
        >
          <RestaurantCard
              class="cursor-pointer"
              :name="restaurant.name"
              :city="restaurant.city"
              :type="restaurant.type"
              :rating="restaurant.rating"
              :price-range="restaurant.price_range"
              :features="restaurant.features"
              :image="restaurant.image"
          />
        </NuxtLink>
      </div>
    </section>
  </UMain>
</template>