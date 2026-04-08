import {defineStore} from 'pinia'
import type {Item} from "~/types/item";

type QuantityRecord = Record<number, number>

export const useCartStore = defineStore('cart', () => {
    const items = useCookie<Item[]>("cart_items", { default: () => [] })
    const quantities = useCookie<QuantityRecord>("cart_quantities")

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

    function itemExist(item: Item) {
        return items.value.find(i => i.id === item.id) != null
    }

    function addItem(item: Item) {
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

    function incrementQuantity(item: Item) {
        if (quantities.value == null)
            quantities.value = {}

        if (quantities.value[item.id] == null)
            quantities.value[item.id] = 0

        quantities.value[item.id]! += 1
    }

    function decrementQuantity(item: Item) {
        if (quantities.value == null)
            quantities.value = {}

        if (quantities.value[item.id] == null)
            quantities.value[item.id] = 0

        quantities.value[item.id]! = Math.max(0, quantities.value[item.id]! - 1)

        if (quantities.value[item.id]! == 0)
            deleteItem(item.id)
    }

    function getQuantityOfItem(item: Item): number {
        if (quantities.value == null)
            quantities.value = {}

        return quantities.value[item.id] ?? 1
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
    }
})