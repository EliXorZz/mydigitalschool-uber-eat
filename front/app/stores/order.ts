import { defineStore } from 'pinia'
import type { Order } from '~/types/order'
import type { Paginator } from '~/types/api'

export const useOrderStore = defineStore('order', () => {
  const token = useCookie('token')

  const list = async (params: Record<string, string | number> = {}): Promise<Paginator<Order>> => {
    const config = useRuntimeConfig()
    const base = config.public.apiBaseUrl

    return $fetch<Paginator<Order>>(`${base}/api/orders`, {
      method: 'GET',
      headers: { Authorization: `Bearer ${token.value}` },
      params
    })
  }

  const statuses = async (): Promise<{ value: string; label: string }[]> => {
    const config = useRuntimeConfig()
    const base = config.public.apiBaseUrl

    const res = await $fetch<{ data: { value: string; label: string }[] }>(`${base}/api/orders-statuses`, {
      method: 'GET',
      headers: { Authorization: `Bearer ${token.value}` }
    })

    return res?.data ?? []
  }

  const transitions = async (orderId: number): Promise<string[]> => {
    const config = useRuntimeConfig()
    const base = config.public.apiBaseUrl

    const res = await $fetch<{ data: string[] }>(`${base}/api/orders/${orderId}/transitions`, {
      method: 'GET',
      headers: { Authorization: `Bearer ${token.value}` }
    })

    return res?.data ?? []
  }

  const updateState = async (orderId: number, state: string): Promise<Order | null> => {
    const config = useRuntimeConfig()
    const base = config.public.apiBaseUrl

    const res = await $fetch<{ data: Order }>(`${base}/api/orders/${orderId}/state`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token.value}` },
      body: { state }
    })

    return res?.data ?? null
  }

  const cancel = async (orderId: number): Promise<boolean> => {
    const config = useRuntimeConfig()
    const base = config.public.apiBaseUrl

    await $fetch(`${base}/api/orders/${orderId}/cancel`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token.value}` }
    })

    return true
  }

  return { list, statuses, transitions, updateState, cancel }
})
