<script setup lang="ts">
import type {Order} from "~/types/order";
import type {TableColumn} from "#ui/components/Table.vue";

definePageMeta({
  layout: 'default',
  ssr: false,

  middleware: ['auth'],
})

const { data: orders, pending: ordersPending } = await useAsyncData<Order[]>(
    `orders:me`,
    async () => {
      return [
        {
          id: 32,
          name: 'M. Dylan',
          date: 'Lundi 21 février 12:32',
          total: 23.4,
          items: []
        },
        {
          id: 35,
          name: 'M. Alex',
          date: 'Lundi 12 février 12:32',
          total: 50.4,
          items: []
        },
        {
          id: 36,
          name: 'M. Raphael',
          date: 'Lundi 21 février 12:32',
          total: 12.4,
          items: []
        }
      ]
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
      <LazyUTable :data="orders" :columns="columns" :loading="ordersPending" class="flex-1" />
    </UPageCard>
  </UMain>
</template>