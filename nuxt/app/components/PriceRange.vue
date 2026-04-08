<script setup lang="ts">
defineProps<{
  range: number
}>()
</script>

<template>
  <div class="flex gap-0.2 items-center bg-emerald-500 p-1 rounded-2xl">
    <Icon v-for="i in range" :key="i" name="i-lucide:euro" class="text-white"/>
  </div>
</template>