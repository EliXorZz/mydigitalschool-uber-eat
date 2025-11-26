<script setup lang="ts">
defineProps<{
  rating: number
}>()
</script>

<template>
  <div class="flex gap-0.5 items-center">
    <Icon v-for="i in Math.floor(rating)" :key="i" name="i-bxs:star" class="text-yellow-400"/>
  </div>
</template>