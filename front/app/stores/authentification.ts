import { defineStore } from 'pinia'
import type { Account } from '~/types/account'

export const useAuthentificationStore = defineStore('auth', () => {
  const token = useCookie('token')
  const account = useCookie<Account | null>('account')

  const role = computed(() => account.value?.role)
  const isAuth = computed(() => token.value != null)

  async function login(username: string, password: string): Promise<boolean> {
    if (username == 'admin' && password == 'admin-mydigitalschool') {
      token.value = `${username}:${password}` // Fake connection without API
      account.value = { username, password, email: 'dylan.battig@my-digital-school.org', role: 'admin' }

      return true
    }
    
    if (username == 'dylan' && password == 'admin-mydigitalschool') {
      token.value = `${username}:${password}` // Fake connection without API
      account.value = { username, password, email: 'dylan.battig@my-digital-school.org', role: 'owner' }

      return true
    }

    return false
  }

  async function register({ username, email, password }: { username: string, email: string, password: string }): Promise<boolean> {
    // In this mock, usernames 'admin' and 'dylan' are reserved
    if (["admin", "dylan"].includes(username)) {
      return false;
    }
    // automatically logs in after "register"
    token.value = `${username}:${password}`;
    account.value = {
      username,
      email,
      role: 'default',
      name: username,
      id: Date.now()
    };
    return true;
  }

  async function logout() {
    token.value = null
    account.value = null

    await navigateTo({ name: 'index' }, { replace: true })
  }

  return {
    account,
    role,
    isAuth,
    login,
    logout
  }
})
