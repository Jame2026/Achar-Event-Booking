const API_URL = (import.meta.env.VITE_API_URL || import.meta.env.VITE_API_BASE_URL || '/api').replace(/\/+$/, '')

export const API_BASE_URL = API_URL.endsWith('/api') ? API_URL : `${API_URL}/api`
export const API_ORIGIN = API_BASE_URL.replace(/\/api\/?$/, '')

export default API_URL
