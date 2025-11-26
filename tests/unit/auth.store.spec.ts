import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthentificationStore } from '../../app/stores/authentification'

import { computed, ref } from 'vue'

vi.stubGlobal('computed', computed)
vi.stubGlobal('ref', ref)

const mockCookieState: Record<string, any> = {}

vi.stubGlobal('useCookie', (key: string) => {
    if (!mockCookieState[key]) {
        mockCookieState[key] = ref(null)
    }
    return mockCookieState[key]
})

const mockNavigateTo = vi.fn()
vi.stubGlobal('navigateTo', mockNavigateTo)

describe('useAuthentificationStore', () => {
    beforeEach(() => {
        setActivePinia(createPinia())

        // Reset des cookies simulés
        for (const key in mockCookieState) {
            mockCookieState[key].value = null
        }

        mockNavigateTo.mockClear()
    })

    it('initialise avec un état vide', () => {
        const store = useAuthentificationStore()

        expect(store.account).toBeNull()
        expect(store.isAuth).toBe(false)
        expect(store.role).toBeUndefined()
    })

    it('login success: admin', async () => {
        const store = useAuthentificationStore()
        const result = await store.login('admin', 'admin-mydigitalschool')

        expect(result).toBe(true)
        expect(store.isAuth).toBe(true)
        expect(store.role).toBe('admin')
    })
})