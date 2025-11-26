<script setup lang="ts">
import type {Restaurant, RestaurantsResponse} from "~/types/restaurant";
import type {Item, ItemsResponse} from "~/types/item";
import PriceRange from "~/components/PriceRange.vue";
import ItemCard from "~/components/ItemCard.vue";

const route = useRoute()
const slug = route.params.slug as string

const { data: restaurant } = await useAsyncData<Restaurant>(
    `restaurant:${slug}`,
    async () => {
      const restaurants = await $fetch<RestaurantsResponse>('/api/restaurants')
      const restaurant = restaurants.find(r => r.slug === slug)

      if (restaurant == null)
        throw createError({
          statusCode: 404,
          message: 'Restaurant non trouvé',
          fatal: true
        })

      return restaurant
    }
)

const { data: items } = await useAsyncData<Item[]>(
    `items:${slug}`,
    async () => {
      const response = await $fetch<ItemsResponse>('/api/items')
      const items = response
          .find(r => r.id_restaurant === restaurant.value?.id)
          ?.items

      return items ?? []
    }
)

useHead({ title: `Restaurant | ${restaurant.value?.name}` })

useSeoMeta({
  title: restaurant.value?.name,
  ogTitle: restaurant.value?.name,
  description: restaurant.value?.description,
  ogDescription: restaurant.value?.description,
  ogImage: restaurant.value ? `http://localhost:3000${restaurant.value.image}` : undefined,
  twitterCard: 'summary_large_image',
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

      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"/>

      <div class="relative z-10 text-white space-y-6 max-w-2xl">
        <h2 class="text-5xl font-bold -translate-y-7">{{ restaurant.name }}</h2>
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
            />

            <div class="flex justify-between items-center mb-2">
              <h2 class="text-xl font-bold text-gray-800">{{ restaurant.name }}</h2>
              <span class="text-sm text-gray-600">{{ restaurant.city }}</span>
            </div>

            <p class="text-sm text-gray-700 italic mb-2">{{ restaurant.type }}</p>

            <p class="text-gray-600 mb-3">{{ restaurant.description }}</p>

            <div class="flex flex-wrap gap-2 mb-3">
              <UBadge v-for="feature in restaurant.features" color="neutral" variant="outline">
                {{ feature }}
              </UBadge>
            </div>

            <div class="flex justify-between items-center">
              <StarRating :rating="restaurant.rating"/>
              <PriceRange :range="restaurant.price_range"/>
            </div>
          </template>
        </UCard>
      </div>

      <section class="flex flex-col gap-15 p-20">
        <h2 class="text-center text-5xl font-bold">Plats à emporter</h2>

        <div class="flex flex-wrap gap-10 justify-center">
          <NuxtLink
              v-for="item in items"
              :key="item.id"
              :to="{ name: 'restaurants-slug-items-id', params: { id: item.id, slug: restaurant?.slug } }"
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
      </section>
    </div>
  </UMain>
</template>