import type { Order } from '~/types/order'

export function useOrderNotifications(restaurantId: MaybeRef<number | null | undefined>, onNew?: (order: Order) => void, onUpdate?: (order: Order) => void, onDelete?: (order: Order) => void) {
  const { $echo } = useNuxtApp()
  const authStore = useAuthentificationStore()
  const { isAuth, role } = storeToRefs(authStore)

  let channel: any = null

  function subscribe(id: number) {
    if (!$echo || !id || channel !== null) return

    channel = $echo.private(`restaurants.${id}.orders`)

    channel.listen('OrderCreated', (e: { order: Order }) => {
      onNew?.(e.order)
    })

    channel.listen('OrderUpdated', (e: { order: Order }) => {
      onUpdate?.(e.order)
    })

    channel.listen('OrderDeleted', (e: { order: Order }) => {
      onDelete?.(e.order)
    })
  }

  function unsubscribe(id: number) {
    if (!$echo || !id) return
    channel?.stopListening('OrderCreated')
    channel?.stopListening('OrderUpdated')
    channel?.stopListening('OrderDeleted')
    $echo.leave(`restaurants.${id}.orders`)
    channel = null
  }

  const idRef = computed(() => unref(restaurantId))

  watch(
    [idRef, isAuth],
    ([newId, authed], [oldId]) => {
      if (oldId) unsubscribe(oldId)
      if (authed && newId && role.value === 'restaurant') subscribe(newId)
    },
    { immediate: true }
  )

  onUnmounted(() => {
    const id = unref(restaurantId)
    if (id) unsubscribe(id)
  })

  return { channel }
}
