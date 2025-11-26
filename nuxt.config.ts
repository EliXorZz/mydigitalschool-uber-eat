// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  modules: [
    '@nuxt/ui',
    '@nuxt/eslint',
    '@nuxt/image',
    '@pinia/nuxt',
    '@vite-pwa/nuxt'
  ],
  css: ['~/assets/main.css'],

  app: {
    head: { title: 'Eat Research' }
  },

  colorMode: {
    preference: 'light',
  },

  pwa: {
    registerType: 'autoUpdate',

    manifest: {
      name: 'Uber Eeat',
      short_name: 'Uber',
      start_url: '/',
      display: 'standalone',
      background_color: '#ffffff',
      theme_color: '#8c00ff'
    },

    workbox: {
      navigateFallback: '/',
      globPatterns: ['**/*.{js,css,html,png,svg}'],

      runtimeCaching: [
        {
          urlPattern: /^https:\/\/.*\.(png|jpg|jpeg|svg|gif)$/,
          handler: 'CacheFirst',
          options: { cacheName: 'images-cache' }
        },
        {
          urlPattern: ({ request }) => request.mode === 'navigate',
          handler: 'NetworkFirst',
          options: {
            cacheName: 'pages-cache',
            networkTimeoutSeconds: 3,
          }
        }
      ]
    }
  }
})