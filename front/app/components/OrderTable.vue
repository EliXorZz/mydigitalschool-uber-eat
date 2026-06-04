<script setup lang="ts">
import type { Order } from '~/types/order'
import { UBadge, UButton } from '#components'

type BadgeColor = 'error' | 'primary' | 'secondary' | 'success' | 'info' | 'warning' | 'neutral'

const props = defineProps<{
  title: string
  orders: Order[] | any
  loading?: boolean
  showRestaurantColumn?: boolean
  allowStateChange?: boolean
}>()

const emit = defineEmits<{
  refresh: []
}>()

const orderStore = useOrderStore()
const auth = useAuthentificationStore()

const isLoading = computed(() => props.loading ?? false)
const showRestaurant = computed(() => props.showRestaurantColumn ?? true)

const ordersList = computed<Order[]>(() => {
  const raw = props.orders?.value ?? props.orders ?? []
  if (raw && typeof raw === 'object' && Array.isArray((raw as any).data)) {
    return (raw as any).data
  }
  if (Array.isArray(raw)) return raw
  return []
})

const expanded = ref<Record<number, boolean>>({})

function toggleExpand(id: number) {
  expanded.value[id] = !expanded.value[id]
}

function stateValue(order: Order): string {
  return String(order?.state ?? '').toLowerCase()
}

function stateColor(order: Order): BadgeColor {
  const k = stateValue(order)
  if (k === 'pending') return 'warning'
  if (k === 'preparing') return 'info'
  if (k === 'confirmed') return 'primary'
  if (k === 'delivered') return 'success'
  return 'neutral'
}

function canCancel(order: Order): boolean {
  if (!order) return false
  const userId = auth.account?.id ?? null
  return stateValue(order) === 'pending' && userId !== null
    && Number(order.user_id ?? order.user?.id) === Number(userId)
}

async function handleCancel(order: Order) {
  try {
    await orderStore.cancel(order.id)
    useToast().add({
      title: $t('dashboard.orders.cancelSuccessTitle'),
      description: $t('dashboard.orders.cancelSuccessDescription'),
      color: 'success'
    })
    emit('refresh')
  } catch {
    useToast().add({
      title: $t('dashboard.orders.cancelErrorTitle'),
      description: $t('dashboard.orders.cancelErrorDescription'),
      color: 'error'
    })
  }
}

const stateColorMap: Record<string, BadgeColor> = {
  pending: 'warning',
  preparing: 'info',
  confirmed: 'primary',
  delivered: 'success',
  ready: 'neutral',
}

async function handleStateChange(order: Order, newState: string) {
  if (stateValue(order) === newState) return
  try {
    await orderStore.updateState(order.id, newState)
    useToast().add({
      title: $t('orders.updateStateSuccess'),
      color: 'success'
    })
    emit('refresh')
  } catch {
    useToast().add({
      title: $t('orders.updateStateError'),
      color: 'error'
    })
  }
}

function getColumnHeader(): string {
  return showRestaurant.value ? $t('orders.restaurant') : $t('orders.customer')
}

function getColumnValue(order: Order): string {
  if (showRestaurant.value) {
    return order?.restaurant?.name ?? '-'
  }
  return order?.user?.name ?? '-'
}
</script>

<template>
  <UPageCard :title="title" spotlight-color="primary">
    <div class="overflow-x-auto">
      <div v-if="isLoading" class="p-6 text-center text-gray-500">
        Chargement...
      </div>

      <div v-else-if="ordersList.length === 0" class="p-6 text-center text-gray-500">
        Aucune commande
      </div>

      <table v-else class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('orders.date') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ getColumnHeader() }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('orders.total') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3" />
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <template v-for="(order, oi) in ordersList.filter(Boolean)" :key="order?.id ?? oi">
            <tr>
              <td class="px-6 py-4 whitespace-nowrap">#{{ order?.id ?? '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ order?.created_at ? new Date(order.created_at).toLocaleString() : '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap font-medium">{{ getColumnValue(order) }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ order?.total ?? '-' }}€</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ order?.total_items ?? (order?.items?.length ?? 0) }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <UBadge :color="stateColor(order)" variant="solid">
                  {{ $t(`status.${stateValue(order)}`) }}
                </UBadge>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right">
                <div class="flex items-center justify-end gap-2">
                  <UButton size="xs" variant="outline" @click="toggleExpand(order?.id)">
                    {{ expanded[order?.id] ? 'Fermer' : 'Voir' }}
                  </UButton>
                  <UButton
                    v-if="canCancel(order)"
                    size="xs"
                    variant="outline"
                    color="error"
                    @click="handleCancel(order)"
                  >
                    {{ $t('dashboard.orders.cancel') }}
                  </UButton>
                </div>
              </td>
            </tr>

            <tr v-if="expanded[order?.id]">
              <td colspan="7" class="px-6 py-4 bg-gray-50">
                <div class="space-y-2 mb-3">
                  <div
                    v-for="(item, ii) in (order?.items ?? []).filter(Boolean)"
                    :key="item?.id ?? ii"
                    class="flex justify-between items-center py-1"
                  >
                    <div>
                      <div class="font-medium">{{ item.name }}</div>
                      <div class="text-sm text-gray-500">{{ item.quantity }} x {{ item.price }}€</div>
                    </div>
                    <div class="font-semibold">{{ (item.price * item.quantity).toFixed(2) }}€</div>
                  </div>
                </div>

                <div v-if="allowStateChange && (order.allowed_transitions?.length ?? 0) > 0" class="flex flex-wrap items-center gap-2 border-t pt-3">
                  <span class="text-sm font-medium text-gray-600">{{ $t('orders.stateLabel') }} :</span>
                  <UButton
                    v-for="t in (order.allowed_transitions ?? [])"
                    :key="t"
                    size="xs"
                    variant="outline"
                    :color="stateColorMap[t] ?? 'neutral'"
                    @click="handleStateChange(order, t)"
                  >
                    {{ $t(`status.${t}`) }}
                  </UButton>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </UPageCard>
</template>
