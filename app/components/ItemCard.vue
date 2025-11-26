<script setup lang="ts">
defineProps<{
  name: string
  price: number
  description: string
  image: string
}>()
</script>

<template>
  <UCard class="flex flex-col w-100 h-full overflow-hidden">
    <div class="flex flex-col flex-grow gap-5 h-full">
      <img
          :src="image"
          :alt="name"
          class="w-full h-50 object-cover rounded-t-xl"
      />

      <div class="px-3 flex justify-between items-center flex-grow">
        <div>
          <h3 class="font-semibold text-lg">{{ name }}</h3>
          <p class="text-gray-600 text-sm mt-1">{{ description }}</p>
        </div>
        <div class="mt-2 text-primary text-xl font-bold">{{ price }}€</div>
      </div>
    </div>
  </UCard>
</template>