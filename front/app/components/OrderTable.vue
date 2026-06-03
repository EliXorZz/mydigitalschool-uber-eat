<script setup lang="ts">
import type { TableColumn } from '#ui/components/Table.vue'
import type { Order } from '~/types/order'

import { UBadge, UButton } from '#components'

const props = defineProps<{
  title: string
  orders: Order[] | any
  loading?: boolean
}>()

const title = computed(() => props.title)
const loading = computed(() => props.loading ?? false)

const orderStore = useOrderStore()

const title = computed(() => props.title)
const loading = computed(() => props.loading ?? false)

const ordersList = computed(() => props.orders?.value ?? props.orders ?? [])

async function handleCancel(order: Order) {
  try {
    const ok = await orderStore.cancel(order.id)
    if (ok) {
      useToast().add({ title: $t('dashboard.orders.cancelSuccessTitle'), description: $t('dashboard.orders.cancelSuccessDescription'), color: 'success' })
      // reload page or emit an event — simplest is reload
      window.location.reload()
    }
  } catch (e) {
    useToast().add({ title: $t('dashboard.orders.cancelErrorTitle'), description: $t('dashboard.orders.cancelErrorDescription'), color: 'error' })
  }
}

// Track expanded rows for table view
const expanded = ref<Record<number, boolean>>({})
function toggleExpand(id: number) {
  expanded.value[id] = !expanded.value[id]
}

function stateValue(order: Order) {
  // normalize state key (lowercase string) without using ?? in template
  const v = order.state_name ?? order.state
  return String(v ?? '').toLowerCase()
}

function stateColor(order: Order) {
  const k = stateValue(order)
  if (k === 'pending') return 'warning'
  if (k === 'preparing') return 'info'
  if (k === 'confirmed') return 'primary'
  if (k === 'delivered') return 'success'
  return 'neutral'
}
</script>

<template>
  <UPageCard
    :title="title"
    spotlight-color="primary"
  >
    <div class="overflow-x-auto">
      <div v-if="loading" class="p-6">Chargement...</div>
      <div v-else>
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('orders.date') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('orders.restaurant') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('orders.total') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3" />
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <template v-for="order in ordersList" :key="order.id">
            <tr>
              <td class="px-6 py-4 whitespace-nowrap">#{{ order.id }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ new Date(order.created_at).toLocaleString() }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ order.user?.name ?? order.restaurant?.name ?? '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ order.total }}€</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ order.total_items ?? (order.items?.length ?? 0) }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                  <UBadge :color="stateColor(order)" variant="solid">{{ $t(`status.${stateValue(order)}`) }}</UBadge>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right">
                <div class="flex items-center justify-end gap-2">
                  <UButton size="xs" variant="outline" @click="toggleExpand(order.id)">{{ expanded[order.id] ? 'Fermer' : 'Voir' }}</UButton>
                  <UButton size="xs" variant="outline" color="error" @click="handleCancel(order)" v-if="(order.state_name ?? order.state) === 'pending'">{{ $t('orders.cancel') }}</UButton>
                </div>
              </td>
            </tr>

            <tr v-if="expanded[order.id]">
              <td colspan="7" class="px-6 py-4 bg-gray-50">
                <div class="space-y-2">
                  <div v-for="item in order.items ?? []" :key="item.id" class="flex justify-between items-center py-2">
                    <div>
                      <div class="font-medium">{{ item.name }}</div>
                      <div class="text-sm text-muted">{{ item.quantity }} x {{ item.price }}€</div>
                    </div>
                    <div class="font-semibold">{{ (item.price * item.quantity).toFixed(2) }}€</div>
                  </div>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
      </div>
    </div>
  </UPageCard>
</template>
