import { defineStore } from 'pinia'
import type { Dish } from '~/types/dish'

type QuantityRecord = Record<number, number>

export const useCartStore = defineStore('cart', () => {
  const items = useCookie<Dish[]>('cart_items', { default: () => [] })
  const quantities = useCookie<QuantityRecord>('cart_quantities')

  const total = computed(() => {
    let t = 0
    for (const item of items.value)
      t += getQuantityOfItem(item) * item.price

    return t
  })

  const quantity = computed(() => {
    let t = 0
    for (const item of items.value)
      t += getQuantityOfItem(item)

    return t
  })

  function itemExist(item: Dish) {
    return items.value.find(i => i.id === item.id) != null
  }

  function addItem(item: Dish) {
    if (itemExist(item)) {
      incrementQuantity(item)
    } else {
      items.value.push(item)
      quantities.value[item.id] = 1
    }
  }

  function deleteItem(id: number) {
    const index = items.value.findIndex(i => i.id === id)

    if (index !== -1) {
      items.value.splice(index, 1)

      const { [id]: _, ...rest } = quantities.value
      quantities.value = rest
    }
  }

  function clear() {
    items.value = []
    quantities.value = {}
  }

  function incrementQuantity(item: Dish) {
    if (quantities.value == null)
      quantities.value = {}

    if (quantities.value[item.id] == null)
      quantities.value[item.id] = 0

    quantities.value[item.id]! += 1
  }

  function decrementQuantity(item: Dish) {
    if (quantities.value == null)
      quantities.value = {}

    if (quantities.value[item.id] == null)
      quantities.value[item.id] = 0

    quantities.value[item.id]! = Math.max(0, quantities.value[item.id]! - 1)

    if (quantities.value[item.id]! == 0)
      deleteItem(item.id)
  }

  function getQuantityOfItem(item: Dish): number {
    if (quantities.value == null)
      quantities.value = {}

    return quantities.value[item.id] ?? 1
  }

  // Create an order on the API using the current cart content.
  // Returns the created order data on success.
  async function checkout() {
    const { $api } = useNuxtApp()

    const dishes = items.value.map(i => ({ id: i.id, quantity: getQuantityOfItem(i) }))

    const response = await $api<{ data: any }>('/api/orders', {
      method: 'POST',
      body: { dishes }
    })

    return response?.data
  }

  return {
    items,
    quantities,
    total,
    quantity,
    addItem,
    deleteItem,
    clear,
    incrementQuantity,
    decrementQuantity,
    getQuantityOfItem
    ,
    // expose checkout so callers can create an order from the cart
    checkout
  }
})
