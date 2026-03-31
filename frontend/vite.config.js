import { fileURLToPath, URL } from 'node:url'

import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vite.dev/config/
export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  const apiUrl = env.VITE_API_URL || env.VITE_API_BASE_URL || '/api'
  const apiOrigin = /^https?:\/\//.test(apiUrl)
    ? apiUrl.replace(/\/api\/?$/, '')
    : 'http://127.0.0.1:8000'

  return {
    cacheDir: '.vite-cache',
    plugins: [
      vue(),
    ],
    server: {
      proxy: {
        '/api': {
          target: apiOrigin,
          changeOrigin: true,
        },
        '/backend-auth': {
          target: apiOrigin,
          changeOrigin: true,
          rewrite: (path) => path.replace(/^\/backend-auth/, '/auth'),
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
