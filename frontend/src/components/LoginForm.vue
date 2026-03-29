<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useLanguageCopy } from '../features/language'
import { API_BASE_URL, AUTH_PROXY_BASE } from '../features/apiUrl'

const emit = defineEmits<{
  switch: []
  success: [user: { id: number; name: string; email?: string | null; phone?: string | null; role: string }]
}>()

const showPassword = ref(false)
const authLogoSrc = ref(localStorage.getItem('achar_brand_logo') || '/achar-logo.png')
const apiBaseUrl = API_BASE_URL
const authBaseUrl = AUTH_PROXY_BASE
const form = reactive({
  login: '',
  password: '',
  remember: false,
})

const submitting = ref(false)
const errorMessage = ref('')
const copyByLanguage = {
  en: {
    backHome: 'Back to Home',
    title: 'Sign in to your account',
    subtitle: 'Use your email and password to continue.',
    emailOrPhone: 'Email or Phone',
    emailOrPhonePlaceholder: 'you@example.com or +85512345678',
    password: 'Password',
    passwordPlaceholder: 'Enter your password',
    remember: 'Remember this device',
    forgotPassword: 'Forgot password?',
    signingIn: 'Signing in...',
    login: 'Login',
    orContinueWith: 'or continue with',
    noAccount: 'No account yet?',
    registerNow: 'Register now',
  },
  km: {
    backHome: 'ត្រឡប់ទៅទំព័រដើម',
    title: 'ចូលគណនីរបស់អ្នក',
    subtitle: 'ប្រើអ៊ីមែល និងពាក្យសម្ងាត់របស់អ្នកដើម្បីបន្ត។',
    emailOrPhone: 'អ៊ីមែល ឬទូរស័ព្ទ',
    emailOrPhonePlaceholder: 'you@example.com ឬ +85512345678',
    password: 'ពាក្យសម្ងាត់',
    passwordPlaceholder: 'បញ្ចូលពាក្យសម្ងាត់របស់អ្នក',
    remember: 'ចងចាំឧបករណ៍នេះ',
    forgotPassword: 'ភ្លេចពាក្យសម្ងាត់?',
    signingIn: 'កំពុងចូល...',
    login: 'ចូល',
    orContinueWith: 'ឬបន្តជាមួយ',
    noAccount: 'មិនទាន់មានគណនីមែនទេ?',
    registerNow: 'ចុះឈ្មោះឥឡូវ',
  },
  zh: {
    backHome: '返回首页',
    title: '登录您的账户',
    subtitle: '使用您的邮箱和密码继续。',
    emailOrPhone: '邮箱或手机号',
    emailOrPhonePlaceholder: 'you@example.com 或 +85512345678',
    password: '密码',
    passwordPlaceholder: '输入您的密码',
    remember: '记住此设备',
    forgotPassword: '忘记密码？',
    signingIn: '登录中...',
    login: '登录',
    orContinueWith: '或使用以下方式继续',
    noAccount: '还没有账户？',
    registerNow: '立即注册',
  },
}
const { uiText } = useLanguageCopy(copyByLanguage)

function onAuthLogoError() {
  authLogoSrc.value = '/favicon.ico'
}

const startSocialAuth = (provider: 'google') => {
  const frontendUrl = encodeURIComponent(window.location.origin)
  window.location.href = `${authBaseUrl}/${provider}/redirect?frontend_url=${frontendUrl}`
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
  <section class="auth-shell auth-shell-login">
    <div class="auth-visual auth-visual-login-panel" aria-hidden="true"></div>

    <main class="auth-panel auth-panel-login">
      <section class="auth-card">
        <router-link class="auth-back-home" to="/">&larr; {{ uiText.backHome }}</router-link>

        <div class="brand-row auth-logo-only">
          <img class="auth-brand-logo auth-brand-logo-lg" :src="authLogoSrc" alt="Achar logo" @error="onAuthLogoError" />
        </div>

        <div class="form-head">
          <h2>{{ uiText.title }}</h2>
          <p>{{ uiText.subtitle }}</p>
        </div>

        <form class="auth-form" @submit.prevent="submitLogin">
          <label class="field">
            <span>{{ uiText.emailOrPhone }}</span>
            <input v-model="form.login" type="text" :placeholder="uiText.emailOrPhonePlaceholder" required />
          </label>

          <label class="field">
            <span>{{ uiText.password }}</span>
            <div class="password-wrap">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                :placeholder="uiText.passwordPlaceholder"
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
              <span>{{ uiText.remember }}</span>
            </label>
            <router-link class="link-btn" to="/forgot-password">{{ uiText.forgotPassword }}</router-link>
          </div>

          <p v-if="errorMessage" class="form-alert form-alert-error">{{ errorMessage }}</p>

          <button class="submit-btn" type="submit" :disabled="submitting">
            {{ submitting ? uiText.signingIn : uiText.login }}
          </button>
        </form>

        <div class="auth-divider">
          <span>{{ uiText.orContinueWith }}</span>
        </div>

        <div class="social-grid">
          <button type="button" class="social-btn social-btn-google" @click="startSocialAuth('google')">
            <span class="social-mark">G</span>
            <span>Google</span>
          </button>
        </div>

        <p class="switch-row">
          {{ uiText.noAccount }}
          <button type="button" class="link-btn" @click="emit('switch')">{{ uiText.registerNow }}</button>
        </p>
      </section>
    </main>
  </section>
</template>

