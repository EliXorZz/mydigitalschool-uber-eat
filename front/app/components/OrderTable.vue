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
      color: 'success',
    })
    emit('refresh')
  } catch {
    useToast().add({
      title: $t('dashboard.orders.cancelErrorTitle'),
      description: $t('dashboard.orders.cancelErrorDescription'),
      color: 'error',
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
      color: 'success',
    })
    emit('refresh')
  } catch {
    useToast().add({
      title: $t('orders.updateStateError'),
      color: 'error',
    })
  }
}
</script>

<template>
  <UPageCard :title="title" spotlight-color="primary" class="overflow-hidden">
    <div v-if="isLoading" class="py-10 text-center text-gray-500">
      Chargement...
    </div>

    <div v-else-if="ordersList.length === 0" class="py-10 text-center text-gray-500">
      Aucune commande
    </div>

    <div v-else class="overflow-x-auto -mx-4 sm:mx-0">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
        <thead>
          <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
            <th class="px-4 py-3">#</th>
            <th class="px-4 py-3">{{ $t('orders.date') }}</th>
            <th class="px-4 py-3">
              {{ showRestaurant ? $t('orders.restaurant') : $t('orders.customer') }}
            </th>
            <th class="px-4 py-3 text-right">{{ $t('orders.total') }}</th>
            <th class="px-4 py-3 text-center hidden sm:table-cell">Items</th>
            <th class="px-4 py-3">Statut</th>
            <th class="px-4 py-3" />
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
          <template v-for="(order, oi) in ordersList.filter(Boolean)" :key="order?.id ?? oi">
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
              <td class="px-4 py-3 text-gray-400 whitespace-nowrap">#{{ order?.id }}</td>

              <td class="px-4 py-3 whitespace-nowrap text-gray-600 dark:text-gray-300">
                {{ order?.created_at ? new Date(order.created_at).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) : '-' }}
              </td>

              <!-- Restaurant link (user view) or customer name (restaurant view) -->
              <td class="px-4 py-3 whitespace-nowrap font-medium">
                <NuxtLink
                  v-if="showRestaurant && order?.restaurant_id"
                  :to="`/restaurants/${order.restaurant_id}`"
                  class="hover:underline text-primary-600 dark:text-primary-400"
                >
                  {{ order?.restaurant?.name ?? `Restaurant #${order.restaurant_id}` }}
                </NuxtLink>
                <span v-else>{{ order?.user?.name ?? '-' }}</span>
              </td>

              <td class="px-4 py-3 whitespace-nowrap text-right font-semibold">
                {{ Number(order?.total ?? 0).toFixed(2) }}€
              </td>

              <td class="px-4 py-3 whitespace-nowrap text-center hidden sm:table-cell text-gray-500">
                {{ order?.total_items ?? order?.items?.length ?? 0 }}
              </td>

              <td class="px-4 py-3 whitespace-nowrap">
                <UBadge :color="stateColor(order)" variant="solid" size="sm">
                  {{ $t(`status.${stateValue(order)}`) }}
                </UBadge>
              </td>

              <td class="px-4 py-3 whitespace-nowrap text-right">
                <div class="flex items-center justify-end gap-2">
                  <UButton size="xs" variant="ghost" @click="toggleExpand(order?.id)">
                    {{ expanded[order?.id] ? 'Fermer' : 'Détails' }}
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

            <!-- Expanded detail row -->
            <tr v-if="expanded[order?.id]">
              <td colspan="7" class="px-4 py-4 bg-gray-50 dark:bg-gray-800/30">
                <div class="space-y-1 mb-3">
                  <div
                    v-for="(item, ii) in (order?.items ?? []).filter(Boolean)"
                    :key="item?.id ?? ii"
                    class="flex justify-between items-center py-1.5 border-b border-gray-100 dark:border-gray-700 last:border-0"
                  >
                    <div class="flex items-center gap-2">
                      <NuxtLink
                        v-if="order?.restaurant_id && item?.id"
                        :to="`/restaurants/${order.restaurant_id}/items/${item.id}`"
                        class="font-medium hover:underline text-primary-600 dark:text-primary-400"
                      >
                        {{ item.name }}
                      </NuxtLink>
                      <span v-else class="font-medium">{{ item.name }}</span>
                      <span class="text-xs text-gray-400">× {{ item.quantity }}</span>
                    </div>
                    <span class="font-semibold text-sm">
                      {{ (item.price * item.quantity).toFixed(2) }}€
                    </span>
                  </div>
                </div>

                <div v-if="allowStateChange && (order.allowed_transitions?.length ?? 0) > 0" class="flex flex-wrap items-center gap-2 pt-3 border-t border-gray-200 dark:border-gray-700">
                  <span class="text-xs font-medium text-gray-500">{{ $t('orders.stateLabel') }} :</span>
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
