<script setup lang="ts">
import type { TableColumn } from '#ui/components/Table.vue'
import type { Order } from '~/types/order'

import { UBadge } from '#components'

defineProps<{
  title: string
  orders: Order[]
}>()

const columns: TableColumn<Order>[] = [
  {
    accessorKey: 'name',
    header: $t('orders.restaurant'),
    cell: ({ row }) => h('div', { class: 'font-bold' }, row.getValue('name'))
  },
  {
    accessorKey: 'state',
    header: 'Status',
    cell: ({ row }) => h(UBadge, { color: 'primary', variant: 'solid' }, () => row.getValue('state_name') || row.getValue('state'))
  },
  {
    accessorKey: 'date',
    header: $t('orders.date'),
    cell: ({ row }) => h('div', {}, row.getValue('date'))
  },
  {
    accessorKey: 'total',
    header: $t('orders.total'),
    cell: ({ row }) => h(UBadge, { color: 'neutral', variant: 'outline' }, () => row.getValue('total') + '€')
  }
]

// expose a simple slot to show details when needed
</script>

<template #details>
  <template v-for="order in orders" :key="order.id">
    <div class="p-4 border rounded-md mb-2">
      <div class="flex justify-between">
        <div>
          <div class="font-semibold">Order #{{ order.id }}</div>
          <div class="text-sm text-muted">Items: {{ order.total_items ?? (order.items?.length ?? 0) }}</div>
        </div>
        <div class="text-right">
          <div class="font-semibold">{{ order.total }}€</div>
          <div class="text-sm">Status: {{ order.state_name ?? order.state }}</div>
        </div>
      </div>

      <ul class="mt-2 space-y-1">
        <li v-for="item in order.items ?? []" :key="item.id" class="flex justify-between">
          <span>{{ item.name }} x{{ item.quantity }}</span>
          <span>{{ item.price }}€</span>
        </li>
      </ul>
    </div>
  </template>
</template>

// The Table component supports slots for rows; keep this simple — consumers can expand
</script>

<template>
  <UPageCard
    :title="title"
    spotlight-color="primary"
  >
    <LazyUTable
      :data="orders"
      :columns="columns"
      class="flex-1"
    />
  </UPageCard>
</template>
