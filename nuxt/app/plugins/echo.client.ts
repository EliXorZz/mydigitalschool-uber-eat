import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

declare global {
  interface Window {
    Pusher: typeof Pusher
  }
}

export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()

  window.Pusher = Pusher

  const echo = new Echo({
    broadcaster: 'pusher',
    key: config.public.reverbAppKey as string,
    cluster: 'mt1',
    wsHost: config.public.reverbHost as string,
    wsPort: config.public.reverbPort as number,
    wssPort: config.public.reverbPort as number,
    forceTLS: (config.public.reverbScheme as string) === 'https',
    enabledTransports: ['ws', 'wss'],
    authorizer: (channel: { name: string }) => {
      return {
        authorize: (socketId: string, callback: (error: boolean, data: unknown) => void) => {
          $fetch('/broadcasting/auth', {
            method: 'POST',
            body: { socket_id: socketId, channel_name: channel.name }
          })
            .then((response) => callback(false, response))
            .catch((error) => callback(true, error))
        }
      }
    }
  })

  return {
    provide: {
      echo
    }
  }
})
