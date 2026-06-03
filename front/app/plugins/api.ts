export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()
  const token = useCookie<string | null>('token')
  // Single shared refresh promise to serialize refreshes
  let refreshing: Promise<void> | null = null

  // Decode JWT to check expiry
  function isTokenExpiringSoon(tok?: string | null, withinSeconds = 30) {
    if (!tok) return false
    try {
      const parts = tok.split('.')
      if (parts.length < 2) return false
      const payload = JSON.parse(decodeURIComponent(escape(atob(parts[1].replace(/-/g, '+').replace(/_/g, '/')))))
      const exp = Number(payload.exp || 0)
      const now = Math.floor(Date.now() / 1000)
      return exp <= now + withinSeconds
    } catch (e) {
      return false
    }
  }

  async function doRefresh() {
    if (refreshing) return refreshing

    refreshing = (async () => {
      try {
        const refreshUrl = `${config.public.apiBaseUrl}/api/auth/refresh`
        const res: any = await $fetch(refreshUrl, {
          method: 'POST',
          headers: { Authorization: token.value ? `Bearer ${token.value}` : '' }
        })

        const newToken = res?.data?.token ?? res?.token ?? (res?.data ?? {}).token
        if (newToken) {
          token.value = newToken
          // After refreshing token, also refresh current user account cookie
          try {
            const me: any = await $fetch(`${config.public.apiBaseUrl}/api/auth/me`, {
              headers: { Authorization: `Bearer ${token.value}` }
            })
            useCookie('account').value = me?.data ?? null
          } catch (e) {
            // ignore
          }
        } else {
          token.value = null
          try { await navigateTo({ name: 'auth-login' }) } catch (e) {}
          throw new Error('Unable to refresh token')
        }
      } finally {
        refreshing = null
      }
    })()

    return refreshing
  }

  const api = $fetch.create({
    baseURL: config.public.apiBaseUrl,
    async onRequest({ options, request }) {
      // If token exists and is expiring soon, refresh first
      if (token.value && isTokenExpiringSoon(token.value)) {
        // avoid refreshing if this request is the refresh endpoint itself
        if (!request?.includes('/api/auth/refresh')) {
          await doRefresh()
        }
      }

      if (token.value) {
        options.headers = {
          ...(options.headers || {}),
          Authorization: `Bearer ${token.value}`
        }
      }
    },

    async onResponseError({ request, options, response }) {
      // Only handle 401 unauthorized
      if (response?.status !== 401) throw createError({ statusCode: response?.status ?? 500 })

      // If the failed request is the refresh endpoint, don't try again
      if (request && request.includes('/api/auth/refresh')) {
        // token is invalid -> clear and redirect to login
        token.value = null
        try { await navigateTo({ name: 'auth-login' }) } catch (e) {}
        throw createError({ statusCode: 401, statusMessage: 'Authentication required' })
      }

      // Try refresh then retry
      await doRefresh()

      // retry the original request with updated token
      options.headers = {
        ...(options.headers || {}),
        Authorization: token.value ? `Bearer ${token.value}` : undefined
      }

      return $fetch(request, options)
    }
  })

  return {
    provide: {
      api
    }
  }
})
