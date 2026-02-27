<script setup lang="ts">
import { reactive, ref } from 'vue'

const emit = defineEmits<{
  switch: []
  success: [user: { id: number; name: string; email: string; role: string }]
}>()

const showPassword = ref(false)
const authLogoSrc = ref(localStorage.getItem('achar_brand_logo') || '/achar-logo.png')
const form = reactive({
  email: '',
  password: '',
  remember: false,
})

const submitting = ref(false)
const errorMessage = ref('')

function onAuthLogoError() {
  authLogoSrc.value = '/favicon.ico'
}

const submitLogin = async () => {
  if (submitting.value) return

  submitting.value = true
  errorMessage.value = ''

  try {
    const response = await fetch(
      `${import.meta.env.VITE_API_BASE_URL ?? 'http://127.0.0.1:8000'}/api/login`,
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify({
          email: form.email,
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
  <section class="auth-shell">
    <aside class="auth-visual auth-visual-login">
      <div class="visual-overlay">
        <h1>Welcome Back</h1>
        <p>Run your events, bookings, and vendor workflow from one place.</p>
      </div>
    </aside>

    <main class="auth-panel">
      <div class="brand-row auth-logo-only">
        <img class="auth-brand-logo auth-brand-logo-lg" :src="authLogoSrc" alt="Achar logo" @error="onAuthLogoError" />
      </div>

      <div class="form-head">
        <h2>Sign in to your account</h2>
        <p>Use your email and password to continue.</p>
      </div>

      <form class="auth-form" @submit.prevent="submitLogin">
        <label class="field">
          <span>Email</span>
          <input v-model="form.email" type="email" placeholder="you@example.com" required />
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
              {{ showPassword ? 'Hide' : 'Show' }}
            </button>
          </div>
        </label>

        <label class="check-row">
          <input v-model="form.remember" type="checkbox" />
          <span>Remember this device</span>
        </label>

        <p v-if="errorMessage" class="form-alert form-alert-error">{{ errorMessage }}</p>

        <button class="submit-btn" type="submit" :disabled="submitting">
          {{ submitting ? 'Signing in...' : 'Login' }}
        </button>
      </form>

      <p class="switch-row">
        No account yet?
        <button type="button" class="link-btn" @click="emit('switch')">Register now</button>
      </p>
    </main>
  </section>
</template>
