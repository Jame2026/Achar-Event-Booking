<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useLanguageCopy } from '../features/language'

const route = useRoute()
const router = useRouter()
const authLogoSrc = ref(localStorage.getItem('achar_brand_logo') || '/achar-logo.png')
const apiOrigin = (import.meta.env.VITE_API_BASE_URL ?? 'http://127.0.0.1:8000').replace(/\/api\/?$/, '')
const apiBaseUrl = `${apiOrigin}/api`

type Step = 'set_password' | 'verify_code' | 'done'
const step = ref<Step>('set_password')

const form = reactive({
  login: '',
  password: '',
  password_confirmation: '',
})

const otpDigits = ref<string[]>(Array.from({ length: 6 }, () => ''))
const otpRefs = ref<Array<HTMLInputElement | null>>(Array.from({ length: 6 }, () => null))

const showPassword = ref(false)
const showConfirmPassword = ref(false)
const submitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const codeSentMessage = ref('')

const resendSeconds = ref(0)
let resendInterval: number | null = null

const copyByLanguage = {
  en: {
    backToSignIn: 'Back to Sign in',
    title: 'Reset password',
    subtitle: 'Create a new password, then verify with the 6-digit code we send to your email or SMS.',
    emailOrPhone: 'Email or Phone',
    emailOrPhonePlaceholder: 'you@example.com or +85512345678',
    newPassword: 'New Password',
    passwordPlaceholder: 'At least 8 characters',
    confirmNewPassword: 'Confirm New Password',
    confirmPasswordPlaceholder: 'Repeat new password',
    continue: 'Continue',
    verifyTitle: 'Verify code',
    verifySubtitle: 'Enter the 6-digit code we sent to you.',
    codeLabel: '6-digit code',
    codePlaceholder: '123456',
    verifying: 'Verifying...',
    verifyAndChange: 'Verify & Change Password',
    resend: 'Resend code',
    resendIn: 'Resend in',
    passwordChanged: 'Password changed successfully.',
    goSignIn: 'Go to Sign in',
    changeDetails: 'Change email/phone',
  },
  km: {
    backToSignIn: 'ត្រឡប់ទៅចូលគណនី',
    title: 'កំណត់ពាក្យសម្ងាត់ឡើងវិញ',
    subtitle: 'បង្កើតពាក្យសម្ងាត់ថ្មី រួចផ្ទៀងផ្ទាត់ជាមួយលេខកូដ 6 ខ្ទង់។',
    emailOrPhone: 'អ៊ីមែល ឬទូរស័ព្ទ',
    emailOrPhonePlaceholder: 'you@example.com ឬ +85512345678',
    newPassword: 'ពាក្យសម្ងាត់ថ្មី',
    passwordPlaceholder: 'យ៉ាងហោចណាស់ 8 តួអក្សរ',
    confirmNewPassword: 'បញ្ជាក់ពាក្យសម្ងាត់ថ្មី',
    confirmPasswordPlaceholder: 'បញ្ចូលពាក្យសម្ងាត់ថ្មីម្តងទៀត',
    continue: 'បន្ត',
    verifyTitle: 'ផ្ទៀងផ្ទាត់លេខកូដ',
    verifySubtitle: 'បញ្ចូលលេខកូដ 6 ខ្ទង់ដែលយើងបានផ្ញើ។',
    codeLabel: 'លេខកូដ 6 ខ្ទង់',
    codePlaceholder: '123456',
    verifying: 'កំពុងផ្ទៀងផ្ទាត់...',
    verifyAndChange: 'ផ្ទៀងផ្ទាត់ និងប្ដូរពាក្យសម្ងាត់',
    resend: 'ផ្ញើម្តងទៀត',
    resendIn: 'ផ្ញើម្តងទៀតក្នុង',
    passwordChanged: 'បានប្ដូរពាក្យសម្ងាត់ជោគជ័យ។',
    goSignIn: 'ទៅចូលគណនី',
    changeDetails: 'ប្ដូរអ៊ីមែល/ទូរស័ព្ទ',
  },
  zh: {
    backToSignIn: '返回登录',
    title: '重置密码',
    subtitle: '先设置新密码，再输入我们发送到邮箱或短信的6位验证码。',
    emailOrPhone: '邮箱或手机号',
    emailOrPhonePlaceholder: 'you@example.com 或 +85512345678',
    newPassword: '新密码',
    passwordPlaceholder: '至少 8 个字符',
    confirmNewPassword: '确认新密码',
    confirmPasswordPlaceholder: '再次输入新密码',
    continue: '继续',
    verifyTitle: '验证验证码',
    verifySubtitle: '输入我们发送的 6 位验证码。',
    codeLabel: '6 位验证码',
    codePlaceholder: '123456',
    verifying: '验证中...',
    verifyAndChange: '验证并修改密码',
    resend: '重新发送',
    resendIn: '重新发送倒计时',
    passwordChanged: '密码修改成功。',
    goSignIn: '去登录',
    changeDetails: '修改邮箱/手机号',
  },
}
const { uiText } = useLanguageCopy(copyByLanguage)

const canResend = computed(() => resendSeconds.value <= 0 && !submitting.value)
const otpCode = computed(() => otpDigits.value.join(''))

function onAuthLogoError() {
  authLogoSrc.value = '/favicon.ico'
}

function startResendCountdown(seconds: number) {
  resendSeconds.value = Math.max(0, seconds)
  if (resendInterval) window.clearInterval(resendInterval)
  resendInterval = window.setInterval(() => {
    resendSeconds.value = Math.max(0, resendSeconds.value - 1)
    if (resendSeconds.value <= 0 && resendInterval) {
      window.clearInterval(resendInterval)
      resendInterval = null
    }
  }, 1000)
}

function setOtpRef(el: Element | null, index: number) {
  otpRefs.value[index] = el instanceof HTMLInputElement ? el : null
}

function focusOtp(index: number) {
  otpRefs.value[index]?.focus()
}

function fillOtp(value: string) {
  const digits = value.replace(/\D+/g, '').slice(0, 6).split('')
  otpDigits.value = Array.from({ length: 6 }, (_, i) => digits[i] ?? '')
}

function handleOtpInput(index: number, event: Event) {
  const target = event.target as HTMLInputElement
  const digits = target.value.replace(/\D+/g, '')

  if (digits.length <= 1) {
    otpDigits.value[index] = digits
    if (digits && index < 5) focusOtp(index + 1)
    return
  }

  fillOtp(digits)
  focusOtp(Math.min(5, digits.length - 1))
}

function handleOtpKeydown(index: number, event: KeyboardEvent) {
  if (event.key === 'Backspace') {
    if (otpDigits.value[index]) {
      otpDigits.value[index] = ''
      return
    }
    if (index > 0) focusOtp(index - 1)
    return
  }

  if (event.key === 'ArrowLeft' && index > 0) {
    focusOtp(index - 1)
    return
  }

  if (event.key === 'ArrowRight' && index < 5) {
    focusOtp(index + 1)
  }
}

function handleOtpPaste(event: ClipboardEvent) {
  const text = event.clipboardData?.getData('text') ?? ''
  if (!text) return
  event.preventDefault()
  fillOtp(text)
  nextTick(() => focusOtp(Math.min(5, otpCode.value.length)))
}

async function requestCode(): Promise<boolean> {
  if (submitting.value) return false

  submitting.value = true
  errorMessage.value = ''
  codeSentMessage.value = ''

  try {
    const response = await fetch(`${apiBaseUrl}/password-reset/request`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify({ login: form.login }),
    })

    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      errorMessage.value = data?.message ?? 'Unable to send verification code.'
      return false
    }

    codeSentMessage.value = data?.message ?? 'Verification code sent.'
    startResendCountdown(60)
    return true
  } catch {
    errorMessage.value = 'Unable to connect to server.'
    return false
  } finally {
    submitting.value = false
  }
}

async function continueToVerify() {
  errorMessage.value = ''
  successMessage.value = ''

  if (!form.login.trim()) {
    errorMessage.value = 'Email or phone is required.'
    return
  }
  if (form.password.length < 8) {
    errorMessage.value = 'Password must be at least 8 characters.'
    return
  }
  if (form.password !== form.password_confirmation) {
    errorMessage.value = 'Passwords do not match.'
    return
  }

  const ok = await requestCode()
  if (!ok) return

  step.value = 'verify_code'
  otpDigits.value = Array.from({ length: 6 }, () => '')
  await nextTick()
  focusOtp(0)
}

async function verifyAndChangePassword() {
  if (submitting.value) return

  submitting.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const response = await fetch(`${apiBaseUrl}/password-reset/verify`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify({
        login: form.login,
        code: otpCode.value,
        password: form.password,
        password_confirmation: form.password_confirmation,
      }),
    })

    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      errorMessage.value = data?.message ?? 'Verification failed.'
      return
    }

    successMessage.value = data?.message ?? uiText.value.passwordChanged
    step.value = 'done'
  } catch {
    errorMessage.value = 'Unable to connect to server.'
  } finally {
    submitting.value = false
  }
}

function changeDetails() {
  step.value = 'set_password'
  errorMessage.value = ''
  codeSentMessage.value = ''
}

function goToSignIn() {
  router.push({ path: '/legacy-app' }).catch(() => {})
}

onMounted(() => {
  const login = route.query.login ?? route.query.email
  form.login = typeof login === 'string' ? login : ''
})

onBeforeUnmount(() => {
  if (resendInterval) window.clearInterval(resendInterval)
})
</script>

<template>
  <section class="auth-shell auth-shell-form-only">
    <main class="auth-panel">
      <section class="auth-card">
        <router-link class="auth-back-home" to="/legacy-app">{{ uiText.backToSignIn }}</router-link>

        <div class="brand-row auth-logo-only">
          <img class="auth-brand-logo auth-brand-logo-lg" :src="authLogoSrc" alt="Achar logo" @error="onAuthLogoError" />
        </div>

        <div class="auth-stepper" aria-hidden="true">
          <span class="auth-step" :class="{ active: step === 'set_password', done: step !== 'set_password' }">
            <span class="auth-step-dot">1</span>
            <span class="auth-step-label">{{ uiText.newPassword }}</span>
          </span>
          <span class="auth-step-line"></span>
          <span class="auth-step" :class="{ active: step === 'verify_code', done: step === 'done' }">
            <span class="auth-step-dot">2</span>
            <span class="auth-step-label">{{ uiText.verifyTitle }}</span>
          </span>
          <span class="auth-step-line"></span>
          <span class="auth-step" :class="{ active: step === 'done', done: step === 'done' }">
            <span class="auth-step-dot">3</span>
            <span class="auth-step-label">{{ uiText.passwordChanged }}</span>
          </span>
        </div>

        <div class="form-head form-head-center" v-if="step === 'set_password'">
          <h2>{{ uiText.title }}</h2>
          <p>{{ uiText.subtitle }}</p>
        </div>

        <div class="form-head form-head-center" v-else-if="step === 'verify_code'">
          <h2>{{ uiText.verifyTitle }}</h2>
          <p>{{ uiText.verifySubtitle }}</p>
        </div>

        <div class="form-head form-head-center" v-else>
          <h2>{{ uiText.passwordChanged }}</h2>
          <p>{{ successMessage || uiText.passwordChanged }}</p>
        </div>

        <form v-if="step === 'set_password'" class="auth-form" @submit.prevent="continueToVerify">
          <label class="field">
            <span>{{ uiText.emailOrPhone }}</span>
            <input v-model.trim="form.login" type="text" :placeholder="uiText.emailOrPhonePlaceholder" required />
          </label>

          <label class="field">
            <span>{{ uiText.newPassword }}</span>
            <div class="password-wrap">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                :placeholder="uiText.passwordPlaceholder"
                minlength="8"
                required
              />
              <button type="button" class="ghost-btn" @click="showPassword = !showPassword" :aria-pressed="showPassword">
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
            <span>{{ uiText.confirmNewPassword }}</span>
            <div class="password-wrap">
              <input
                v-model="form.password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                :placeholder="uiText.confirmPasswordPlaceholder"
                minlength="8"
                required
              />
              <button type="button" class="ghost-btn" @click="showConfirmPassword = !showConfirmPassword" :aria-pressed="showConfirmPassword">
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

          <button class="submit-btn" type="submit" :disabled="submitting">
            {{ submitting ? uiText.verifying : uiText.continue }}
          </button>
        </form>

        <form v-else-if="step === 'verify_code'" class="auth-form" @submit.prevent="verifyAndChangePassword">
          <div class="auth-inline-actions">
            <button type="button" class="link-btn" @click="changeDetails">{{ uiText.changeDetails }}</button>
          </div>

          <label class="field">
            <span>{{ uiText.codeLabel }}</span>
            <div class="otp-grid" @paste="handleOtpPaste">
              <input
                v-for="(_, index) in otpDigits"
                :key="index"
                :ref="(el) => setOtpRef(el, index)"
                class="otp-input"
                inputmode="numeric"
                pattern="\\d*"
                autocomplete="one-time-code"
                :aria-label="`${uiText.codeLabel} ${index + 1}`"
                :value="otpDigits[index]"
                maxlength="1"
                @input="handleOtpInput(index, $event)"
                @keydown="handleOtpKeydown(index, $event)"
              />
            </div>
          </label>

          <div class="pin-actions">
            <button type="button" class="secondary-button pin-resend" :disabled="!canResend" @click="requestCode">
              <span v-if="resendSeconds > 0">{{ uiText.resendIn }} {{ resendSeconds }}s</span>
              <span v-else>{{ uiText.resend }}</span>
            </button>
            <button class="submit-btn" type="submit" :disabled="submitting || otpCode.length !== 6">
              {{ submitting ? uiText.verifying : uiText.verifyAndChange }}
            </button>
          </div>

          <p v-if="codeSentMessage" class="form-alert form-alert-success">{{ codeSentMessage }}</p>
          <p v-if="errorMessage" class="form-alert form-alert-error">{{ errorMessage }}</p>
        </form>

        <div v-else class="auth-form">
          <button type="button" class="submit-btn" @click="goToSignIn">{{ uiText.goSignIn }}</button>
        </div>
      </section>
    </main>
  </section>
</template>
