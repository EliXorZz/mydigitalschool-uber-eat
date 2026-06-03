export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()
  const token = useCookie<string | null>('token')

  const api = $fetch.create({
    baseURL: config.public.apiBaseUrl,
    onRequest({ options }) {
      if (token.value) {
        options.headers = {
          ...(options.headers || {}),
          Authorization: `Bearer ${token.value}`
        }
      }
    }
  })

  return {
    provide: {
      api
    }
  }
})
