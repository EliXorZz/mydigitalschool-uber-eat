<script setup lang="ts">
const toaster = { position: 'bottom-center' }
</script>

<template>
  <UApp :toaster="toaster">
    <div class="flex flex-col items-center justify-center gap-4 p-4 h-screen">
      <div class="absolute w-full h-full overflow-hidden">
        <ClientOnly>
          <FlickeringGrid :max-opacity="0.05" />
        </ClientOnly>
      </div>
      <slot/>
    </div>
  </UApp>
</template>

<style scoped>

</style>