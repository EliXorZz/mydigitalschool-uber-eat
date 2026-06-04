export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()
  const token = useCookie<string | null>('token')
  let refreshing: Promise<void> | null = null

  function isTokenExpiringSoon(tok?: string | null, withinSeconds = 30) {
    if (!tok) return false
    try {
      if (typeof tok !== 'string') return false
      const parts = tok.split('.')
      if (!parts || parts.length < 2) return false

      const part = parts[1]
      if (!part) return false
      const b64 = part.replace(/-/g, '+').replace(/_/g, '/')
      const pad = b64.length % 4
      const padded = pad === 0 ? b64 : b64 + '='.repeat(4 - pad)

      let decoded = ''
      if (typeof atob === 'function') {
        decoded = decodeURIComponent(escape(atob(padded)))
      } else {
        return false
      }

      const payload = JSON.parse(decoded)
      const exp = Number(payload.exp || 0)
      const now = Math.floor(Date.now() / 1000)
      return exp <= now + withinSeconds
    } catch {
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
          try {
            const me: any = await $fetch(`${config.public.apiBaseUrl}/api/auth/me`, {
              headers: { Authorization: `Bearer ${token.value}` }
            })
            useCookie('account').value = me?.data ?? null
          } catch {
            // ignore
          }
        } else {
          token.value = null
          try { await navigateTo({ name: 'auth-login' }) } catch {}
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
    headers: { Accept: 'application/json' },
    async onRequest({ options, request }) {
      if (token.value && isTokenExpiringSoon(token.value)) {
        if (!String(request).includes('/api/auth/refresh')) {
          await doRefresh()
        }
      }

      if (token.value) {
        // Append without replacing — keeps FormData Content-Type boundary intact
        if (options.headers instanceof Headers) {
          options.headers.set('Authorization', `Bearer ${token.value}`)
        } else if (options.headers && typeof options.headers === 'object') {
          (options.headers as Record<string, string>)['Authorization'] = `Bearer ${token.value}`
        } else {
          options.headers = { Authorization: `Bearer ${token.value}` } as any
        }
      }
    },

    async onResponseError({ request, options, response }) {
      // For non-401 errors, let ofetch throw its own FetchError (which carries e.data = response body)
      if (response?.status !== 401) return

      if (String(request).includes('/api/auth/refresh')) {
        token.value = null
        try { await navigateTo({ name: 'auth-login' }) } catch {}
        return
      }

      await doRefresh()

      if (token.value) {
        if (options.headers instanceof Headers) {
          options.headers.set('Authorization', `Bearer ${token.value}`)
        } else if (options.headers && typeof options.headers === 'object') {
          (options.headers as Record<string, string>)['Authorization'] = `Bearer ${token.value}`
        } else {
          options.headers = { Authorization: `Bearer ${token.value}` } as any
        }
      }

      return $fetch(request as string, options as any)
    }
  })

  return {
    provide: { api }
  }
})
