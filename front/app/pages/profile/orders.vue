<script setup lang="ts">
import type { Order } from '~/types/order'
import type { TableColumn } from '#ui/components/Table.vue'

definePageMeta({
  layout: 'default',
  ssr: false,

  middleware: ['auth']
})

const orderStore = useOrderStore()
const { data: orders, pending: ordersPending } = await useAsyncData<Order[]>(
  `orders:me`,
  async () => {
    try {
      return await orderStore.list()
    } catch (e) {
      return []
    }
  }
)

const columns: TableColumn<Order>[] = [
  {
    accessorKey: 'id',
    header: '#',
    cell: ({ row }) => `#${row.getValue('id')}`
  },
  {
    accessorKey: 'date',
    header: 'Date',
    cell: ({ row }) => h('div', {}, row.getValue('date'))
  },
  {
    accessorKey: 'total',
    header: 'Total',
    cell: ({ row }) => h('div', {}, `${row.getValue('total')}€`)
  }
]
</script>

<template>
  <UMain class="p-10">
    <UPageCard title="Mes commandes">
      <LazyUTable
        :data="orders"
        :columns="columns"
        :loading="ordersPending"
        class="flex-1"
      />
    </UPageCard>
  </UMain>
</template>
