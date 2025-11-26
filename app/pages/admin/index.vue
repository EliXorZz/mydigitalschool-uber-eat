<script setup lang="ts">
definePageMeta({
  layout: 'default',
  ssr: false,

  middleware: ['auth'],
  roles: ['admin']
})
</script>

<template>
  <UMain class="p-10">
    <AdminTable class=""/>
  </UMain>
</template>