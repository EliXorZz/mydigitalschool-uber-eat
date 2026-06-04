import { defineStore } from 'pinia'
import type { Order } from '~/types/order'

export const useOrderStore = defineStore('order', () => {
  const token = useCookie('token')

  const list = async (params: Record<string, any> = {}): Promise<any> => {
    const config = useRuntimeConfig()
    const base = config.public.apiBaseUrl

    const res: any = await $fetch(`${base}/api/orders`, {
      method: 'GET',
      headers: { Authorization: `Bearer ${token.value}` },
      params
    })
    // Return the raw response (can be paginator or array)
    return res
  }

  const statuses = async (): Promise<{ value: string; label: string }[]> => {
    const config = useRuntimeConfig()
    const base = config.public.apiBaseUrl

    const res: any = await $fetch(`${base}/api/orders-statuses`, {
      method: 'GET',
      headers: { Authorization: `Bearer ${token.value}` }
    })

    return res?.data ?? []
  }

  const transitions = async (orderId: number) => {
    const config = useRuntimeConfig()
    const base = config.public.apiBaseUrl

    const res: any = await $fetch(`${base}/api/orders/${orderId}/transitions`, {
      method: 'GET',
      headers: { Authorization: `Bearer ${token.value}` }
    })

    return res?.data ?? []
  }

  const updateState = async (orderId: number, state: string): Promise<Order | null> => {
    const config = useRuntimeConfig()
    const base = config.public.apiBaseUrl

    const res: any = await $fetch(`${base}/api/orders/${orderId}/state`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token.value}` },
      body: { state }
    })

    return res?.data ?? null
  }

  const cancel = async (orderId: number): Promise<boolean> => {
    const config = useRuntimeConfig()
    const base = config.public.apiBaseUrl

    const res: any = await $fetch(`${base}/api/orders/${orderId}/cancel`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token.value}` }
    })

    // If API returns 204 empty, $fetch resolves to null — treat as success
    return res !== false
  }

  return { list, statuses, transitions, updateState, cancel }
})
