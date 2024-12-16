import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig(({ mode }) => ({
  define: {
    // Enable Vue DevTools when built in 'development' mode
    __VUE_PROD_DEVTOOLS__: mode === 'development',
  },
  plugins: [
    laravel({
      input: ['resources/js/main.ts'],
      refresh: true,
      detectTls: 'devcamp.test',
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
  ],
  build: {
    chunkSizeWarningLimit: 1600,
  },
}))
