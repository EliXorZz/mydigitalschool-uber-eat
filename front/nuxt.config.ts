// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: [
    '@nuxt/eslint',
    '@nuxt/ui',
    '@nuxtjs/i18n',
    '@nuxt/image',
    '@pinia/nuxt',
    '@vite-pwa/nuxt'
  ],

  devtools: {
    enabled: true
  },

  app: {
    head: { title: 'Eat Research' }
  },

  css: ['~/assets/css/main.css'],

  colorMode: {
    preference: 'light'
  },

  runtimeConfig: {
    public: {
      apiBaseUrl: 'http://localhost:8000',
      reverbAppKey: 'hcznehuul9so2rjmnnqk',
      reverbHost: 'localhost',
      reverbPort: 8081,
      reverbScheme: 'http'
    }
  },

  routeRules: {
    '/': { prerender: true }
  },

  compatibilityDate: '2025-01-15',

  eslint: {
    config: {
      stylistic: {
        commaDangle: 'never',
        braceStyle: '1tbs'
      }
    }
  },

  i18n: {
    strategy: 'no_prefix',
    locales: [
      { code: 'en', language: 'en-US', file: 'en.json' },
      { code: 'fr', language: 'fr-FR', file: 'fr.json' }
    ],
    defaultLocale: 'fr'
  },

  pwa: {
    registerType: 'autoUpdate',

    manifest: {
      name: 'Uber Eeat',
      short_name: 'Uber',
      start_url: '/',
      display: 'standalone',
      background_color: '#ffffff',
      theme_color: '#0ea5e9'
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
            networkTimeoutSeconds: 3
          }
        }
      ]
    }
  }
})
