import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthentificationStore } from '../../app/stores/authentification'

import { computed, ref } from 'vue'

vi.stubGlobal('computed', computed)
vi.stubGlobal('ref', ref)

const mockCookieState: Record<string, unknown> = {}

vi.stubGlobal('useCookie', (key: string) => {
  if (!mockCookieState[key]) {
    mockCookieState[key] = ref(null)
  }
  return mockCookieState[key]
})

vi.stubGlobal('useRuntimeConfig', () => ({
  public: { apiBaseUrl: 'http://localhost' }
}))

const mockFetch = vi.fn()
vi.stubGlobal('$fetch', mockFetch)

const mockNavigateTo = vi.fn()
vi.stubGlobal('navigateTo', mockNavigateTo)

describe('useAuthentificationStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())

    for (const key in mockCookieState) {
      (mockCookieState[key] as ReturnType<typeof ref>).value = null
    }

    mockNavigateTo.mockClear()
    mockFetch.mockReset()
  })

  it('initialise avec un état vide', () => {
    const store = useAuthentificationStore()

    expect(store.account).toBeNull()
    expect(store.isAuth).toBe(false)
    expect(store.role).toBeUndefined()
  })

  it('login success: admin', async () => {
    mockFetch
      .mockResolvedValueOnce({ data: { token: 'fake-token', type: 'bearer', expires_in: 3600 } })
      .mockResolvedValueOnce({ data: { id: 1, name: 'Admin', email: 'admin@test.com', role: 'admin' } })

    const store = useAuthentificationStore()
    const result = await store.login('admin', 'admin-mydigitalschool')

    expect(result).toBe(true)
    expect(store.isAuth).toBe(true)
    expect(store.role).toBe('admin')
  })
})
