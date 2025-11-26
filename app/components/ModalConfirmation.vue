<script setup lang="ts">
defineProps<{
  title: string
  description: string
  action: string
}>()

const emit = defineEmits<{ close: [boolean] }>()
</script>

<template>
  <UModal
      :close="{ onClick: () => emit('close', false) }"
      :title="title"
      :description="description"
  >
    <template #footer>
      <div class="flex gap-2">
        <UButton class="cursor-pointer" label="Annuler" @click="emit('close', false)" />
        <UButton class="cursor-pointer" color="error" :label="action" @click="emit('close', true)" />
      </div>
    </template>
  </UModal>
</template>