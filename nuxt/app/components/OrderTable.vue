<script setup lang="ts">
import type {TableColumn} from "#ui/components/Table.vue";
import type {Order} from "~/types/order";

import {UBadge} from "#components";

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
    accessorKey: 'date',
    header: $t('orders.date'),
    cell: ({ row }) => h('div', {}, row.getValue('date'))
  },
  {
    accessorKey: 'total',
    header: $t('orders.total'),
    cell: ({ row }) => h(UBadge, { color: 'neutral', variant: 'outline' }, () => row.getValue('total') + '€')
  },
]
</script>

<template>
  <UPageCard :title="title" spotlight-color="primary">
    <LazyUTable :data="orders" :columns="columns" class="flex-1" />
  </UPageCard>
</template>