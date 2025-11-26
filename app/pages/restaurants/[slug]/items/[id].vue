<script setup lang="ts">
import type {Item, ItemsResponse} from "~/types/item";
import type {Restaurant, RestaurantsResponse} from "~/types/restaurant";

const route = useRoute()
const slug = route.params.slug as string
const id = Number(route.params.id)

const { data: restaurant } = await useAsyncData<Restaurant>(
    `restaurant:${slug}`,
    async () => {
      const restaurants = await $fetch<RestaurantsResponse>('/api/restaurants')
      const restaurant = restaurants.find(r => r.slug === slug)

      if (restaurant == null)
        throw createError({
          statusCode: 404,
          message: $t('restaurant.notFound'),
          fatal: true
        })

      return restaurant
    }
)

const { data: item } = await useAsyncData<Item>(
    `item:${id}`,
    async () => {
      const response = await $fetch<ItemsResponse>('/api/items')
      const items = response
          .find(r => r.id_restaurant === restaurant.value?.id)
          ?.items

      const item = items?.find(i => i.id === id)

      if (item == null)
        throw createError({
          statusCode: 404,
          message: $t('item.notFound'),
          fatal: true
        })

      return item
    }
)

useHead({ title: `${restaurant.value?.name} | ${item.value?.name}` })

useSeoMeta({
  title: item.value?.name,
  ogTitle: item.value?.name,
  description: item.value?.description,
  ogDescription: item.value?.description,
  ogImage: item.value ? `http://localhost:3000${item.value.image}` : undefined,
  twitterCard: 'summary_large_image',
})

const toast = useToast()
const cartStore = useCartStore()

function add(item: Item) {
  cartStore.addItem(item)

  toast.add({
    title: $t('cart.title'),
    description: $t('cart.addedItem', { name: item.name }),
    icon: 'i-lucide-shopping-basket',
    color: 'info'
  })
}
</script>

<template>
  <UMain v-if="item" class="flex justify-center">
    <UCard class="mt-25 w-fit h-fit shadow-xl">
      <div class="flex flex-col gap-4 justify-center items-center">
        <h1 class="text-2xl font-bold">{{ item.name }}</h1>
        <img class="size-100 object-cover rounded-2xl" :src="item.image" :alt="item.name">
        <p>{{ item.description }}</p>
        <div class="text-primary text-xl font-bold">{{ item.price }}€</div>
        <UButton class="cursor-pointer" icon="i-lucide-shopping-basket" @click="add(item)">
          {{ $t('item.order') }}
        </UButton>
      </div>
    </UCard>
  </UMain>
</template>