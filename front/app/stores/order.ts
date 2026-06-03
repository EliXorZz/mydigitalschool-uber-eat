import { defineStore } from 'pinia'
import type { Order } from '~/types/order'

export const useOrderStore = defineStore('order', () => {
  const token = useCookie('token')

  const list = async (params: Record<string, any> = {}): Promise<Order[]> => {
    const config = useRuntimeConfig()
    const base = config.public.apiBaseUrl

    const res: any = await $fetch(`${base}/api/orders`, {
      method: 'GET',
      headers: { Authorization: `Bearer ${token.value}` },
      params
    })

    return res?.data?.data ?? res?.data ?? []
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

  return { list, statuses, transitions, updateState }
})
