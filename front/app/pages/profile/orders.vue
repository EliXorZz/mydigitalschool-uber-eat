<script setup lang="ts">
import OrderTable from '~/components/OrderTable.vue'

definePageMeta({
  layout: 'default',
  middleware: ['auth']
})

const orderStore = useOrderStore()
const page = ref(1)
const perPage = 10

const { data: ordersResponse, pending: ordersPending, refresh } = await useAsyncData(
  () => `orders:me:${page.value}`,
  async () => {
    try {
      return await orderStore.list({ page: page.value, per_page: perPage })
    } catch {
      return { data: [], current_page: 1, last_page: 1, per_page: 10, total: 0 }
    }
  }
)

const pagination = computed(() => ({
  current: ordersResponse.value?.current_page ?? 1,
  last: ordersResponse.value?.last_page ?? 1,
}))

function prevPage() {
  if (page.value > 1) {
    page.value -= 1
    refresh()
  }
}

function nextPage() {
  if (page.value < pagination.value.last) {
    page.value += 1
    refresh()
  }
}
</script>

<template>
  <UMain class="px-4 py-6 sm:px-8 sm:py-10">
    <OrderTable
      :title="$t('profile.ordersCardTitle')"
      :orders="ordersResponse"
      :loading="ordersPending"
      @refresh="refresh"
    />

    <div class="flex justify-between items-center mt-6">
      <UButton variant="outline" :disabled="pagination.current <= 1" @click="prevPage">
        {{ $t('common.prev') }}
      </UButton>
      <div>{{ pagination.current }} / {{ pagination.last }}</div>
      <UButton variant="outline" :disabled="pagination.current >= pagination.last" @click="nextPage">
        {{ $t('common.next') }}
      </UButton>
    </div>
  </UMain>
</template>
