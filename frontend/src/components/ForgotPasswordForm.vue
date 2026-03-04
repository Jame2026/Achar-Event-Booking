<script setup lang="ts">
import { reactive, ref } from 'vue'

const authLogoSrc = ref(localStorage.getItem('achar_brand_logo') || '/achar-logo.png')
const apiOrigin = (import.meta.env.VITE_API_BASE_URL ?? 'http://127.0.0.1:8000').replace(/\/api\/?$/, '')
const apiBaseUrl = `${apiOrigin}/api`
const form = reactive({
  email: '',
})
const submitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

function onAuthLogoError() {
  authLogoSrc.value = '/favicon.ico'
}

async function submitForgotPassword() {
  if (submitting.value) return

  submitting.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const response = await fetch(`${apiBaseUrl}/forgot-password`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify({ email: form.email }),
    })

    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      errorMessage.value = data?.message ?? 'Unable to send reset link.'
      return
    }

    successMessage.value = data?.message ?? 'Password reset link sent.'
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
        <router-link class="auth-back-home" to="/legacy-app">Back to Sign in</router-link>

        <div class="brand-row auth-logo-only">
          <img class="auth-brand-logo auth-brand-logo-lg" :src="authLogoSrc" alt="Achar logo" @error="onAuthLogoError" />
        </div>

        <div class="form-head">
          <h2>Forgot password</h2>
          <p>Enter your email and we will send you a password reset link.</p>
        </div>

        <form class="auth-form" @submit.prevent="submitForgotPassword">
          <label class="field">
            <span>Email</span>
            <input v-model.trim="form.email" type="email" placeholder="you@example.com" required />
          </label>

          <p v-if="errorMessage" class="form-alert form-alert-error">{{ errorMessage }}</p>
          <p v-if="successMessage" class="form-alert form-alert-success">{{ successMessage }}</p>

          <button class="submit-btn" type="submit" :disabled="submitting">
            {{ submitting ? 'Sending...' : 'Send Reset Link' }}
          </button>
        </form>
      </section>
    </main>
  </section>
</template>

