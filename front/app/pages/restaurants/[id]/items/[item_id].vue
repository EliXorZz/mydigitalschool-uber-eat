<script setup lang="ts">
import type { Restaurant } from '~/types/restaurant'
import type { Dish } from '~/types/dish'

const route = useRoute()
const restaurant_id = route.params.id as string
const id = Number(route.params.item_id)

const { $api } = useNuxtApp()

const { data: restaurant } = await useAsyncData<Restaurant>(
  `restaurant:${id}`,
  async () => {
    const response = await $api<{ data: Restaurant }>(`/api/restaurants/${restaurant_id}`)
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

const { data: item } = await useAsyncData<Dish>(
  `item:${id}`,
  async () => {
    const response = await $api<{ data: Dish }>(`/api/dishes/${id}`)
    const item = response.data

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
  twitterCard: 'summary_large_image'
})

const authStore = useAuthentificationStore()
const { isAuth } = storeToRefs(authStore)

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
  <UMain
    v-if="item"
    class="flex justify-center"
  >
    <UCard class="mt-25 w-fit h-fit shadow-xl">
      <div class="flex flex-col gap-4 justify-center items-center">
        <h1 class="text-2xl font-bold">
          {{ item.name }}
        </h1>
        <img
          class="size-100 object-cover rounded-2xl"
          :src="item.image"
          :alt="item.name"
        >
        <p>{{ item.description }}</p>
        <div class="text-primary text-xl font-bold">
          {{ item.price }}€
        </div>
        <UButton
          v-if="isAuth"
          class="cursor-pointer"
          icon="i-lucide-shopping-basket"
          @click="add(item)"
        >
          {{ $t('item.order') }}
        </UButton>
        <UButton
          v-else
          class="cursor-pointer"
          :disabled="true"
        >
          {{ $t('item.order-no-auth') }}
        </UButton>
      </div>
    </UCard>
  </UMain>
</template>
