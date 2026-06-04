import { defineStore } from 'pinia'
import type { Account } from '~/types/account'

export const useAuthentificationStore = defineStore('auth', () => {
  const token = useCookie('token')
  const account = useCookie<Account | null>('account')

  const role = computed(() => account.value?.role)
  const isAuth = computed(() => token.value != null)

  // Use the API for authentication. No front-end business logic or shortcuts here.
  async function login(email: string, password: string): Promise<boolean> {
    try {
      const config = useRuntimeConfig()
      const base = config.public.apiBaseUrl

      // Call the API login endpoint
      const res: any = await $fetch(`${base}/api/auth/login`, {
        method: 'POST',
        body: { email, password }
      })

      const tokenValue = res?.data?.token
      if (!tokenValue) return false

      token.value = tokenValue

      // Fetch current user
      const me: any = await $fetch(`${base}/api/auth/me`, {
        headers: { Authorization: `Bearer ${token.value}` }
      })

      account.value = me?.data ?? null

      return true
    } catch (e) {
      return false
    }
  }

  async function register({ name, email, password }: { name: string, email: string, password: string }): Promise<boolean> {
    try {
      const config = useRuntimeConfig()
      const base = config.public.apiBaseUrl

      const res: any = await $fetch(`${base}/api/auth/register`, {
        method: 'POST',
        body: { name, email, password }
      })

      const tokenValue = res?.data?.token
      if (!tokenValue) return false

      token.value = tokenValue

      const me: any = await $fetch(`${base}/api/auth/me`, {
        headers: { Authorization: `Bearer ${token.value}` }
      })

      account.value = me?.data ?? null

      return true
    } catch (e) {
      return false
    }
  }

  async function logout() {
    token.value = null
    account.value = null

    await navigateTo({ name: 'index' }, { replace: true })
  }

  // Update the current user's profile via API and refresh stored account
  async function updateProfile(payload: { name?: string; email?: string; password?: string; password_confirmation?: string } ): Promise<boolean> {
    try {
      const config = useRuntimeConfig()
      const base = config.public.apiBaseUrl

      const res: any = await $fetch(`${base}/api/auth/me`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${token.value}` },
        body: payload
      })

      account.value = res?.data ?? account.value

      return true
    } catch (e) {
      // propagate error so caller can display validation messages
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
