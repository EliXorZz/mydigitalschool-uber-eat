<script setup lang="ts">

</script>

<template>
  <UPageCard class="w-full max-w-md" spotlight-color="primary">
    <template #header>
      <NuxtLink :to="{ name: 'index' }" class="flex items-center gap-2">
        <UIcon name="i-lucide:arrow-left"/>
        <span class="text-xs">Retour</span>
      </NuxtLink>
    </template>

    <slot name="body"/>

    <USeparator/>

    <p class="text-slate-600 text-sm text-center">
      <slot name="footer"/>
    </p>
  </UPageCard>
</template>