<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useLanguageCopy } from '../features/language'

const router = useRouter()
const authLogoSrc = ref(localStorage.getItem('achar_brand_logo') || '/achar-logo.png')
const form = reactive({
  email: '',
})
const submitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const copyByLanguage = {
  en: {
    backToSignIn: 'Back to Sign in',
    title: 'Forgot password',
    subtitle: 'Enter your email or phone number to get a 6-digit verification code.',
    email: 'Email or Phone',
    emailPlaceholder: 'you@example.com or +85512345678',
    sending: 'Sending...',
    sendResetLink: 'Continue',
  },
  km: {
    backToSignIn: 'ត្រឡប់ទៅចូលគណនី',
    title: 'ភ្លេចពាក្យសម្ងាត់',
    subtitle: 'បញ្ចូលអ៊ីមែលរបស់អ្នក ហើយយើងនឹងផ្ញើតំណកំណត់ពាក្យសម្ងាត់ឡើងវិញ។',
    email: 'អ៊ីមែល',
    emailPlaceholder: 'you@example.com',
    sending: 'កំពុងផ្ញើ...',
    sendResetLink: 'ផ្ញើតំណកំណត់ឡើងវិញ',
  },
  zh: {
    backToSignIn: '返回登录',
    title: '忘记密码',
    subtitle: '输入您的邮箱，我们会发送重置密码链接。',
    email: '邮箱',
    emailPlaceholder: 'you@example.com',
    sending: '发送中...',
    sendResetLink: '发送重置链接',
  },
}
const { uiText } = useLanguageCopy(copyByLanguage)

function onAuthLogoError() {
  authLogoSrc.value = '/favicon.ico'
}

async function submitForgotPassword() {
  if (submitting.value) return

  submitting.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const response = await fetch('/api/password-reset/request', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify({ login: form.email }),
    })

    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      errorMessage.value = data?.message ?? 'Unable to send reset link.'
      return
    }

    successMessage.value = data?.message ?? 'Verification code sent.'
    router.push({ path: '/reset-password', query: { login: form.email } }).catch(() => {})
  } catch (error) {
    errorMessage.value = error instanceof Error && error.message ? error.message : 'Unable to connect to server.'
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <section class="auth-shell auth-shell-form-only">
    <main class="auth-panel">
      <section class="auth-card">
        <router-link class="auth-back-home" to="/legacy-app">{{ uiText.backToSignIn }}</router-link>

        <div class="brand-row auth-logo-only">
          <img class="auth-brand-logo auth-brand-logo-lg" :src="authLogoSrc" alt="Achar logo" @error="onAuthLogoError" />
        </div>

        <div class="form-head">
          <h2>{{ uiText.title }}</h2>
          <p>{{ uiText.subtitle }}</p>
        </div>

        <form class="auth-form" @submit.prevent="submitForgotPassword">
          <label class="field">
            <span>{{ uiText.email }}</span>
            <input v-model.trim="form.email" type="text" :placeholder="uiText.emailPlaceholder" required />
          </label>

          <p v-if="errorMessage" class="form-alert form-alert-error">{{ errorMessage }}</p>
          <p v-if="successMessage" class="form-alert form-alert-success">{{ successMessage }}</p>

          <button class="submit-btn" type="submit" :disabled="submitting">
            {{ submitting ? uiText.sending : uiText.sendResetLink }}
          </button>
        </form>
      </section>
    </main>
  </section>
</template>

