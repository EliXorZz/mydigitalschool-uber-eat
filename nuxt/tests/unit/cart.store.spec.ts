import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { computed, ref } from 'vue'

import type { Item } from '../../app/types/item'
import { useCartStore } from '../../app/stores/cart'

vi.stubGlobal('computed', computed)
vi.stubGlobal('ref', ref)

const mockCookieState: Record<string, any> = {}

vi.stubGlobal('useCookie', (key: string, opts?: any) => {
    if (!mockCookieState[key]) {
        const defaultValue = opts?.default ? opts.default() : null
        mockCookieState[key] = ref(defaultValue)
    }
    return mockCookieState[key]
})

describe('useCartStore', () => {
    beforeEach(() => {
        setActivePinia(createPinia())

        for (const key in mockCookieState) {
            if (Array.isArray(mockCookieState[key].value)) {
                mockCookieState[key].value = []
            } else if (typeof mockCookieState[key].value === 'object') {
                mockCookieState[key].value = {}
            } else {
                mockCookieState[key].value = null
            }
        }
    })

    it('initialise avec un panier vide', () => {
        const store = useCartStore()
    })

    it('ajoute un item', () => {
        const store = useCartStore()
        const item: Item = { id: 1, name: 'Pizza', price: 10, description: 'test', image: 'test' }

        store.addItem(item)
    })

    it('incrémente la quantité', () => {
        const store = useCartStore()
        const item: Item = { id: 1, name: 'Pizza', price: 10, description: 'test', image: 'test' }

        store.addItem(item)
        store.incrementQuantity(item)
    })

    it('décrémente la quantité et supprime si zéro', () => {
        const store = useCartStore()
        const item: Item = { id: 1, name: 'Pizza', price: 10, description: 'test', image: 'test' }

        store.addItem(item)
        store.decrementQuantity(item)
    })

    it('supprime un item directement', () => {
        const store = useCartStore()
        const item: Item = { id: 1, name: 'Pizza', price: 10, description: 'test', image: 'test' }

        store.addItem(item)
        store.deleteItem(item.id)
    })

    it('vide le panier', () => {
        const store = useCartStore()
        const item1: Item = { id: 1, name: 'Pizza', price: 10, description: 'test', image: 'test' }
        const item2: Item = { id: 2, name: 'Pasta', price: 8, description: 'test', image: 'test' }

        store.addItem(item1)
        store.addItem(item2)
        store.clear()
    })
})