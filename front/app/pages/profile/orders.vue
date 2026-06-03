<script setup lang="ts">
import type { Order } from '~/types/order'
import type { TableColumn } from '#ui/components/Table.vue'

definePageMeta({
  layout: 'default',
  ssr: false,

  middleware: ['auth']
})

const orderStore = useOrderStore()
const { data: orders, pending: ordersPending } = await useAsyncData(
  `orders:me`,
  async () => {
    try {
      const res: any = await orderStore.list()
      return res ?? []
    } catch (e) {
      return []
    }
  }
)

const tableData = computed(() => {
  // orders may be a paginator object ({ data: [], meta, links }) or an array
  if (!orders.value) return []
  return orders.value.data ?? orders.value
})

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
    <UPageCard title="Mes commandes">
      <LazyUTable
        :data="tableData"
        :columns="columns"
        :loading="ordersPending"
        class="flex-1"
      />
    </UPageCard>
  </UMain>
</template>
