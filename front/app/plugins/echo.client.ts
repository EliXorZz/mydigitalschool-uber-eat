import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

declare global {
  interface Window {
    Pusher: typeof Pusher
  }
}

interface ReverbConnector {
  pusher?: {
    config?: {
      auth?: {
        headers?: Record<string, string>
      }
    }
  }
}

export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()
  const token = useCookie<string | null>('token')

  window.Pusher = Pusher

  Pusher.logToConsole = true

  const echo = new Echo({
    broadcaster: 'reverb',
    key: config.public.reverbAppKey as string,
    wsHost: config.public.reverbHost as string,
    wsPort: config.public.reverbPort as number,
    wssPort: config.public.reverbPort as number,
    forceTLS: (config.public.reverbScheme as string) === 'https',
    enabledTransports: (config.public.reverbScheme as string) === 'https' ? ['wss'] : ['ws'],
    authEndpoint: `${config.public.apiBaseUrl}/api/broadcasting/auth`,
    auth: {
      headers: {
        Authorization: token.value ? `Bearer ${token.value}` : ''
      }
    }
  })

  watch(token, (newToken) => {
    const connector = (echo as unknown as { connector: ReverbConnector }).connector
    if (connector?.pusher?.config?.auth?.headers) {
      connector.pusher.config.auth.headers['Authorization'] = newToken
        ? `Bearer ${newToken}`
        : ''
    }
  })

  return {
    provide: { echo }
  }
})
