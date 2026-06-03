<script setup lang="ts">
import type { Order } from '~/types/order'
import type { TableColumn } from '#ui/components/Table.vue'
import OrderTable from '~/components/OrderTable.vue'

definePageMeta({
  layout: 'default',
  ssr: false,

  middleware: ['auth']
})

const orderStore = useOrderStore()
const page = ref(1)
const perPage = 10

const { data: ordersResponse, pending: ordersPending, refresh } = await useAsyncData(
  () => `orders:me:${page.value}`,
  async () => {
    try {
      const res: any = await orderStore.list({ page: page.value, per_page: perPage })
      return res ?? { data: [], current_page: 1, last_page: 1 }
    } catch (e) {
      return { data: [], current_page: 1, last_page: 1 }
    }
  }
)

const tableData = computed(() => ordersResponse.value?.data ?? [])
const pagination = computed(() => ({
  current: ordersResponse.value?.current_page ?? 1,
  last: ordersResponse.value?.last_page ?? 1,
  per_page: ordersResponse.value?.per_page ?? perPage,
  total: ordersResponse.value?.total ?? 0,
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

const columns: TableColumn<Order>[] = [
  {
    accessorKey: 'id',
    header: '#',
    cell: ({ row }) => `#${row.getValue('id')}`
  },
  {
    accessorKey: 'created_at',
    header: $t('orders.date'),
    cell: ({ row }) => new Date(row.getValue('created_at')).toLocaleString()
  },
  {
    id: 'customer',
    header: $t('orders.restaurant'),
    cell: ({ row }) => h('div', { class: 'font-medium' }, row.original.user?.name ?? row.original.restaurant?.name ?? '-')
  },
  {
    accessorKey: 'total',
    header: $t('orders.total'),
    cell: ({ row }) => h('div', {}, `${row.getValue('total')}€`)
  },
  {
    id: 'items',
    header: 'Items',
    cell: ({ row }) => `${row.original.total_items ?? (row.original.items?.length ?? 0)}`
  },
  {
    id: 'state',
    header: 'Status',
    cell: ({ row }) => {
      const s = row.original.state_name ?? row.getValue('state')
      const key = (s || '').toString().toLowerCase()
      const label = $t(`status.${key}`)
      const colorMap: Record<string, string> = {
        pending: 'warning',
        preparing: 'info',
        confirmed: 'primary',
        delivered: 'success',
        ready: 'neutral'
      }
      const color = colorMap[key] ?? 'neutral'
      return h(UBadge, { color, variant: 'solid' }, () => label)
    }
  }
]
</script>

<template>
  <UMain class="p-10">
    <OrderTable title="Mes commandes" :orders="ordersResponse" :loading="ordersPending" />

    <div class="flex justify-between items-center mt-6">
      <UButton :disabled="pagination.current <= 1" @click="prevPage">{{ $t('common.prev') }}</UButton>
      <div>{{ pagination.current }} / {{ pagination.last }}</div>
      <UButton :disabled="pagination.current >= pagination.last" @click="nextPage">{{ $t('common.next') }}</UButton>
    </div>
  </UMain>
</template>
