<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'

const emit = defineEmits<{
  switch: []
  success: [user: { id: number; name: string; email: string; role: string }]
}>()

const showPassword = ref(false)
const authLogoSrc = ref(localStorage.getItem('achar_brand_logo') || '/achar-logo.png')
const apiOrigin = (import.meta.env.VITE_API_BASE_URL ?? 'http://127.0.0.1:8000').replace(/\/api\/?$/, '')
const apiBaseUrl = `${apiOrigin}/api`
const authBaseUrl = apiOrigin
const form = reactive({
  login: '',
  password: '',
  remember: false,
})

const submitting = ref(false)
const errorMessage = ref('')

function onAuthLogoError() {
  authLogoSrc.value = '/favicon.ico'
}

const startSocialAuth = (provider: 'google' | 'facebook') => {
  const frontendUrl = encodeURIComponent(window.location.origin)
  window.location.href = `${authBaseUrl}/auth/${provider}/redirect?frontend_url=${frontendUrl}`
}

onMounted(() => {
  const socialError = localStorage.getItem('achar_social_error')
  if (!socialError) return
  errorMessage.value = socialError
  localStorage.removeItem('achar_social_error')
})

const submitLogin = async () => {
  if (submitting.value) return

  submitting.value = true
  errorMessage.value = ''

  try {
    const response = await fetch(
      `${apiBaseUrl}/login`,
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify({
          login: form.login,
          password: form.password,
        }),
      },
    )

    const data = await response.json()

    if (!response.ok) {
      errorMessage.value = data?.message ?? 'Login failed.'
      return
    }

    emit('success', data.user)
  } catch {
    errorMessage.value = 'Unable to connect to server.'
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <section class="auth-shell auth-shell-form-only">

    <main class="auth-panel">
      <section class="auth-card">
        <router-link class="auth-back-home" to="/">← Back to Home</router-link>

        <div class="brand-row auth-logo-only">
          <img class="auth-brand-logo auth-brand-logo-lg" :src="authLogoSrc" alt="Achar logo" @error="onAuthLogoError" />
        </div>

        <div class="form-head">
          <h2>Sign in to your account</h2>
          <p>Use your email and password to continue.</p>
        </div>

        <form class="auth-form" @submit.prevent="submitLogin">
          <label class="field">
            <span>Email or Phone</span>
            <input v-model="form.login" type="text" placeholder="you@example.com or +85512345678" required />
          </label>

          <label class="field">
            <span>Password</span>
            <div class="password-wrap">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Enter your password"
                required
              />
              <button type="button" class="ghost-btn" @click="showPassword = !showPassword">
                <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M3 3l18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                  <path d="M10.6 10.6A3 3 0 0013.4 13.4" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                  <path d="M9.9 5.1A10.9 10.9 0 0112 5c6.5 0 10 7 10 7a17.6 17.6 0 01-4.1 4.9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M6.6 6.6A17.7 17.7 0 002 12s3.5 7 10 7c1.7 0 3.2-.4 4.6-1.1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </button>
            </div>
          </label>

          <div class="auth-help-row">
            <label class="check-row">
              <input v-model="form.remember" type="checkbox" />
              <span>Remember this device</span>
            </label>
            <router-link class="link-btn" to="/forgot-password">Forgot password?</router-link>
          </div>

          <p v-if="errorMessage" class="form-alert form-alert-error">{{ errorMessage }}</p>

          <button class="submit-btn" type="submit" :disabled="submitting">
            {{ submitting ? 'Signing in...' : 'Login' }}
          </button>
        </form>

        <div class="auth-divider">
          <span>or continue with</span>
        </div>

        <div class="social-grid">
          <button type="button" class="social-btn social-btn-google" @click="startSocialAuth('google')">
            <span class="social-mark">G</span>
            <span>Google</span>
          </button>
          <button type="button" class="social-btn social-btn-facebook" @click="startSocialAuth('facebook')">
            <span class="social-mark">f</span>
            <span>Facebook</span>
          </button>
        </div>

        <p class="switch-row">
          No account yet?
          <button type="button" class="link-btn" @click="emit('switch')">Register now</button>
        </p>
      </section>
    </main>
  </section>
</template>

