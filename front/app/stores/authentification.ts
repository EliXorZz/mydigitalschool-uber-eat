import { defineStore } from 'pinia'
import type { Account, AuthToken } from '~/types/account'

export const useAuthentificationStore = defineStore('auth', () => {
  const token = useCookie('token')
  const account = useCookie<Account | null>('account')

  const role = computed(() => account.value?.role)
  const isAuth = computed(() => token.value != null)

  async function login(email: string, password: string): Promise<boolean> {
    try {
      const config = useRuntimeConfig()
      const base = config.public.apiBaseUrl

      const res = await $fetch<{ data: AuthToken }>(`${base}/api/auth/login`, {
        method: 'POST',
        body: { email, password }
      })

      const tokenValue = res?.data?.token
      if (!tokenValue) return false

      token.value = tokenValue

      const me = await $fetch<{ data: Account }>(`${base}/api/auth/me`, {
        headers: { Authorization: `Bearer ${token.value}` }
      })

      account.value = me?.data ?? null

      return true
    } catch {
      return false
    }
  }

  async function register({ name, email, password }: { name: string, email: string, password: string }): Promise<boolean> {
    try {
      const config = useRuntimeConfig()
      const base = config.public.apiBaseUrl

      const res = await $fetch<{ data: AuthToken }>(`${base}/api/auth/register`, {
        method: 'POST',
        body: { name, email, password }
      })

      const tokenValue = res?.data?.token
      if (!tokenValue) return false

      token.value = tokenValue

      const me = await $fetch<{ data: Account }>(`${base}/api/auth/me`, {
        headers: { Authorization: `Bearer ${token.value}` }
      })

      account.value = me?.data ?? null

      return true
    } catch {
      return false
    }
  }

  async function logout() {
    token.value = null
    account.value = null

    await navigateTo({ name: 'index' }, { replace: true })
  }

  async function updateProfile(payload: { name?: string; email?: string; avatar?: string | null; password?: string; password_confirmation?: string }): Promise<boolean> {
    try {
      const config = useRuntimeConfig()
      const base = config.public.apiBaseUrl

      const res = await $fetch<{ data: Account }>(`${base}/api/auth/me`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${token.value}` },
        body: payload
      })

      account.value = res?.data ?? account.value

      return true
    } catch (e) {
      throw e
    }
  }

  return {
    account,
    role,
    isAuth,
    login,
    register,
    logout,
    updateProfile
  }
})
