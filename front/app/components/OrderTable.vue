<script setup lang="ts">
import type { TableColumn } from '#ui/components/Table.vue'
import type { Order } from '~/types/order'

import { UBadge, UButton } from '#components'

defineProps<{
  title: string
  orders: Order[]
}>()

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
    cell: ({ row }) => h(UBadge, { color: 'neutral', variant: 'outline' }, () => `${row.getValue('total')}€`)
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
  },
  {
    id: 'actions',
    header: '',
    cell: ({ row }) => h('div', { class: 'flex gap-2' }, [
      h(UButton, { size: 'xs', variant: 'outline', onClick: () => {} }, () => $t('common.back'))
    ])
  }
]

// The Table component supports slots for rows; keep this simple — consumers can expand
</script>

<template>
  <UPageCard
    :title="title"
    spotlight-color="primary"
  >
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <template v-for="order in orders" :key="order.id">
        <UPageCard class="p-4">
          <div class="flex items-start justify-between gap-4">
            <div>
              <div class="text-sm text-muted">#{{ order.id }} • {{ new Date(order.created_at).toLocaleString() }}</div>
              <h3 class="text-lg font-semibold mt-1">{{ order.user?.name ?? $t('orders.restaurant') }}</h3>
              <div class="text-sm text-muted">Items: {{ order.total_items ?? (order.items?.length ?? 0) }}</div>
            </div>

            <div class="text-right">
              <UBadge color="primary" variant="solid">{{ order.state_name ?? order.state }}</UBadge>
              <div class="text-xl font-bold mt-2">{{ order.total }}€</div>
            </div>
          </div>

          <div class="mt-4">
            <ul class="space-y-2">
              <li v-for="item in order.items ?? []" :key="item.id" class="flex justify-between items-center">
                <div>
                  <div class="font-medium">{{ item.name }}</div>
                  <div class="text-sm text-muted">{{ item.quantity }} x {{ item.price }}€</div>
                </div>
                <div class="font-semibold">{{ (item.price * item.quantity).toFixed(2) }}€</div>
              </li>
            </ul>
          </div>

        </UPageCard>
      </template>
    </div>
  </UPageCard>
</template>
