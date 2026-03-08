import { fileURLToPath, URL } from 'node:url'

import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vite.dev/config/
export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  const apiOrigin = (env.VITE_API_BASE_URL || 'http://127.0.0.1:8000').replace(/\/api\/?$/, '')

  return {
    plugins: [
      vue(),
    ],
    server: {
      proxy: {
        '/api': {
          target: apiOrigin,
          changeOrigin: true,
        },
      },
    },
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url)),
        'lucide-vue-next': fileURLToPath(new URL('./src/shims/lucide-vue-next.js', import.meta.url)),
      },
    },
  }
})
