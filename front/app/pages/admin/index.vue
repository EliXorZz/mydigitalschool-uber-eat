<script setup lang="ts">
definePageMeta({
  layout: 'default',

  middleware: ['auth'],
  roles: ['admin']
})
</script>

<template>
  <UMain class="px-4 py-6 sm:px-8 sm:py-10">
    <AdminTable />
  </UMain>
</template>
