<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useLanguageCopy } from '../features/language'

const route = useRoute()
const router = useRouter()
const authLogoSrc = ref(localStorage.getItem('achar_brand_logo') || '/achar-logo.png')
const apiOrigin = (import.meta.env.VITE_API_BASE_URL ?? 'http://127.0.0.1:8000').replace(/\/api\/?$/, '')
const apiBaseUrl = `${apiOrigin}/api`
const form = reactive({
  token: '',
  email: '',
  password: '',
  password_confirmation: '',
})
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const submitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const copyByLanguage = {
  en: {
    backToSignIn: 'Back to Sign in',
    title: 'Reset password',
    subtitle: 'Set a new password for your account.',
    email: 'Email',
    emailPlaceholder: 'you@example.com',
    resetToken: 'Reset Token',
    resetTokenPlaceholder: 'Reset token',
    newPassword: 'New Password',
    passwordPlaceholder: 'At least 8 characters',
    confirmNewPassword: 'Confirm New Password',
    confirmPasswordPlaceholder: 'Repeat new password',
    resetting: 'Resetting...',
    resetPassword: 'Reset Password',
  },
  km: {
    backToSignIn: 'ត្រឡប់ទៅចូលគណនី',
    title: 'កំណត់ពាក្យសម្ងាត់ឡើងវិញ',
    subtitle: 'កំណត់ពាក្យសម្ងាត់ថ្មីសម្រាប់គណនីរបស់អ្នក។',
    email: 'អ៊ីមែល',
    emailPlaceholder: 'you@example.com',
    resetToken: 'កូដកំណត់ឡើងវិញ',
    resetTokenPlaceholder: 'កូដកំណត់ឡើងវិញ',
    newPassword: 'ពាក្យសម្ងាត់ថ្មី',
    passwordPlaceholder: 'យ៉ាងហោចណាស់ 8 តួអក្សរ',
    confirmNewPassword: 'បញ្ជាក់ពាក្យសម្ងាត់ថ្មី',
    confirmPasswordPlaceholder: 'បញ្ចូលពាក្យសម្ងាត់ថ្មីម្តងទៀត',
    resetting: 'កំពុងកំណត់ឡើងវិញ...',
    resetPassword: 'កំណត់ពាក្យសម្ងាត់ឡើងវិញ',
  },
  zh: {
    backToSignIn: '返回登录',
    title: '重置密码',
    subtitle: '为您的账户设置新密码。',
    email: '邮箱',
    emailPlaceholder: 'you@example.com',
    resetToken: '重置令牌',
    resetTokenPlaceholder: '重置令牌',
    newPassword: '新密码',
    passwordPlaceholder: '至少 8 个字符',
    confirmNewPassword: '确认新密码',
    confirmPasswordPlaceholder: '再次输入新密码',
    resetting: '重置中...',
    resetPassword: '重置密码',
  },
}
const { uiText } = useLanguageCopy(copyByLanguage)

function onAuthLogoError() {
  authLogoSrc.value = '/favicon.ico'
}

onMounted(() => {
  const token = route.query.token
  const email = route.query.email
  form.token = typeof token === 'string' ? token : ''
  form.email = typeof email === 'string' ? email : ''
})

async function submitResetPassword() {
  if (submitting.value) return

  submitting.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const response = await fetch(`${apiBaseUrl}/reset-password`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify({
        token: form.token,
        email: form.email,
        password: form.password,
        password_confirmation: form.password_confirmation,
      }),
    })

    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      errorMessage.value = data?.message ?? 'Password reset failed.'
      return
    }

    successMessage.value = data?.message ?? 'Your password has been reset.'
    form.password = ''
    form.password_confirmation = ''
    setTimeout(() => {
      router.push('/legacy-app')
    }, 1000)
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
        <router-link class="auth-back-home" to="/legacy-app">{{ uiText.backToSignIn }}</router-link>

        <div class="brand-row auth-logo-only">
          <img class="auth-brand-logo auth-brand-logo-lg" :src="authLogoSrc" alt="Achar logo" @error="onAuthLogoError" />
        </div>

        <div class="form-head">
<<<<<<< HEAD
          <h2><span aria-hidden="true">🔁</span> Reset password</h2>
          <p>Set a new password for your account.</p>
=======
          <h2>{{ uiText.title }}</h2>
          <p>{{ uiText.subtitle }}</p>
>>>>>>> d12a1173e4941b7a545b98d160aa716f1c54076f
        </div>

        <form class="auth-form" @submit.prevent="submitResetPassword">
          <label class="field">
<<<<<<< HEAD
            <span>📧 Email</span>
            <input v-model.trim="form.email" type="email" placeholder="you@example.com" required />
          </label>

          <label class="field">
            <span>🧾 Reset Token</span>
            <input v-model.trim="form.token" type="text" placeholder="Reset token" required />
          </label>

          <label class="field">
            <span>🔐 New Password</span>
=======
            <span>{{ uiText.email }}</span>
            <input v-model.trim="form.email" type="email" :placeholder="uiText.emailPlaceholder" required />
          </label>

          <label class="field">
            <span>{{ uiText.resetToken }}</span>
            <input v-model.trim="form.token" type="text" :placeholder="uiText.resetTokenPlaceholder" required />
          </label>

          <label class="field">
            <span>{{ uiText.newPassword }}</span>
>>>>>>> d12a1173e4941b7a545b98d160aa716f1c54076f
            <div class="password-wrap">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                :placeholder="uiText.passwordPlaceholder"
                minlength="8"
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

          <label class="field">
<<<<<<< HEAD
            <span>✅ Confirm New Password</span>
=======
            <span>{{ uiText.confirmNewPassword }}</span>
>>>>>>> d12a1173e4941b7a545b98d160aa716f1c54076f
            <div class="password-wrap">
              <input
                v-model="form.password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                :placeholder="uiText.confirmPasswordPlaceholder"
                minlength="8"
                required
              />
              <button type="button" class="ghost-btn" @click="showConfirmPassword = !showConfirmPassword">
                <svg v-if="showConfirmPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
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

          <p v-if="errorMessage" class="form-alert form-alert-error">{{ errorMessage }}</p>
          <p v-if="successMessage" class="form-alert form-alert-success">{{ successMessage }}</p>

          <button class="submit-btn" type="submit" :disabled="submitting">
<<<<<<< HEAD
            {{ submitting ? 'Resetting...' : '🔄 Reset Password' }}
=======
            {{ submitting ? uiText.resetting : uiText.resetPassword }}
>>>>>>> d12a1173e4941b7a545b98d160aa716f1c54076f
          </button>
        </form>
      </section>
    </main>
  </section>
</template>

