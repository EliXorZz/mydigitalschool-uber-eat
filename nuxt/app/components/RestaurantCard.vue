<script setup lang="ts">
defineProps<{
  name: string,
  city: string
  type: string
  rating: number,
  priceRange: number,
  features: string[],
  image: string
}>()
</script>

<template>
  <div class="w-100 h-70 relative">
    <img
        :src="image"
        :alt="name"
        class="absolute inset-0 w-full h-full object-cover rounded-3xl"
    />

    <div class="absolute inset-0 hover:bg-black/20 bg-black/35 hover:backdrop-blur-none backdrop-blur-[1px] rounded-3xl"/>

    <div class="pointer-events-none absolute top-4 right-4 text-white">
      <PriceRange :range="priceRange"/>
    </div>

    <div class="pointer-events-none absolute bottom-4 right-4 text-white">
      <div class="flex flex-wrap-reverse justify-end gap-2">
        <UBadge v-for="feature in features" :key="feature" color="neutral" variant="outline">{{ feature }}</UBadge>
      </div>
    </div>

    <div class="pointer-events-none absolute top-25 left-8 text-white">
      <h3 class="text-2xl font-bold">{{ name }}</h3>
      <p class="text-sm">{{ type }} • {{ city }}</p>
      <StarRating class="mt-1.5" :rating="rating"/>
    </div>
  </div>
</template>