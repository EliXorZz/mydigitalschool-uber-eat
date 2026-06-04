<script setup lang="ts">
const props = withDefaults(defineProps<{
  modelValue?: string
  label?: string
  disabled?: boolean
}>(), {
  modelValue: '',
  disabled: false
})

const emit = defineEmits<{
  'update:modelValue': [url: string]
}>()

const config = useRuntimeConfig()
const token = useCookie<string | null>('token')
const toast = useToast()

const inputRef = ref<HTMLInputElement | null>(null)
const isDragging = ref(false)
const uploading = ref(false)

async function uploadFile(file: File) {
  if (!file.type.startsWith('image/')) {
    toast.add({ title: 'Fichier invalide', description: 'Seules les images sont acceptées.', color: 'error' })
    return
  }
  if (file.size > 5 * 1024 * 1024) {
    toast.add({ title: 'Fichier trop lourd', description: 'Maximum 5 Mo.', color: 'error' })
    return
  }

  uploading.value = true
  try {
    const form = new FormData()
    form.append('file', file)

    const headers: Record<string, string> = { }
    if (token.value) headers['Authorization'] = `Bearer ${token.value}`

    const response = await fetch(`${config.public.apiBaseUrl}/api/upload`, {
      method: 'POST',
      body: form,
      headers,
    })

    const res = await response.json()
    if (!response.ok) throw res
    emit('update:modelValue', res?.data?.url ?? '')
  } catch (e) {
    const err = e as { errors?: Record<string, string[]>; message?: string }
    const msg = err?.errors
      ? Object.values(err.errors).flat().join(' ')
      : (err?.message ?? 'Erreur inconnue')
    toast.add({ title: 'Erreur upload', description: msg, color: 'error' })
  } finally {
    uploading.value = false
  }
}

function onDrop(e: DragEvent) {
  isDragging.value = false
  if (props.disabled || uploading.value) return
  const file = e.dataTransfer?.files?.[0]
  if (file) uploadFile(file)
}

function onFileChange(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (file) uploadFile(file)
  // Reset input so same file can be re-uploaded if needed
  if (inputRef.value) inputRef.value.value = ''
}

function clearImage() {
  emit('update:modelValue', '')
}
</script>

<template>
  <div class="w-full">
    <!-- Label -->
    <p v-if="label" class="text-sm font-medium text-gray-700 mb-1">{{ label }}</p>

    <!-- Drop zone -->
    <div
      class="relative rounded-xl border-2 border-dashed transition-colors cursor-pointer overflow-hidden"
      :class="[
        isDragging
          ? 'border-primary bg-primary/5'
          : 'border-gray-300 hover:border-primary/60',
        (disabled || uploading) ? 'opacity-60 cursor-not-allowed' : ''
      ]"
      style="min-height: 140px;"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="onDrop"
      @click="!disabled && !uploading && inputRef?.click()"
    >
      <!-- Preview -->
      <template v-if="modelValue && !uploading">
        <img
          :src="modelValue"
          alt="preview"
          class="w-full h-40 object-cover"
        >
        <div class="absolute inset-0 bg-black/0 hover:bg-black/40 transition-colors flex items-center justify-center gap-3 opacity-0 hover:opacity-100">
          <UButton
            size="sm"
            variant="solid"
            color="neutral"
            icon="i-lucide-image"
            @click.stop="!disabled && !uploading && inputRef?.click()"
          >
            Changer
          </UButton>
          <UButton
            size="sm"
            variant="solid"
            color="error"
            icon="i-lucide-trash"
            @click.stop="clearImage"
          >
            Supprimer
          </UButton>
        </div>
      </template>

      <!-- Upload in progress -->
      <template v-else-if="uploading">
        <div class="flex flex-col items-center justify-center h-40 gap-3 text-gray-500">
          <UIcon name="i-lucide-loader" class="animate-spin text-primary" size="32" />
          <span class="text-sm">Envoi en cours...</span>
        </div>
      </template>

      <!-- Empty state -->
      <template v-else>
        <div class="flex flex-col items-center justify-center h-40 gap-2 text-gray-400 select-none">
          <UIcon
            :name="isDragging ? 'i-lucide-image-plus' : 'i-lucide-upload-cloud'"
            size="36"
            :class="isDragging ? 'text-primary' : ''"
          />
          <p class="text-sm font-medium" :class="isDragging ? 'text-primary' : ''">
            {{ isDragging ? 'Déposez l\'image ici' : 'Glissez-déposez ou cliquez' }}
          </p>
          <p class="text-xs">PNG, JPG, WebP — max 5 Mo</p>
        </div>
      </template>
    </div>

    <!-- Hidden file input -->
    <input
      ref="inputRef"
      type="file"
      accept="image/*"
      class="hidden"
      @change="onFileChange"
    >
  </div>
</template>
