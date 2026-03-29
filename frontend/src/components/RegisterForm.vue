<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useLanguageCopy } from '../features/language'
import { AUTH_PROXY_BASE } from '../features/apiUrl'

type ValidationErrors = Record<string, string[]>

const emit = defineEmits<{
  switch: []
  success: [user: { id: number; name: string; email?: string | null; phone?: string | null; role: string }]
}>()

const form = reactive({
  name: '',
  email: '',
  phone: '',
  role: 'user',
  password: '',
  password_confirmation: '',
})
const registerMethod = ref<'email' | 'phone'>('email')
const showPassword = ref(false)
const showConfirmPassword = ref(false)

const submitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const validationErrors = ref<ValidationErrors>({})
const copyByLanguage = {
  en: {
    backHome: 'Back to Home',
    title: 'Create your account',
    subtitle: 'Set up your profile and choose your account profession.',
    registerWith: 'Register With',
    email: 'Email',
    phone: 'Phone',
    fullName: 'Full Name',
    fullNamePlaceholder: 'Your full name',
    emailLabel: 'Email',
    emailPlaceholder: 'you@example.com',
    phoneNumber: 'Phone Number',
    profession: 'Profession',
    planner: 'Planner',
    vendor: 'Vendor',
    password: 'Password',
    passwordPlaceholder: 'At least 8 characters',
    confirmPassword: 'Confirm Password',
    confirmPasswordPlaceholder: 'Repeat password',
    creating: 'Creating...',
    createAccount: 'Create Account',
    orSignUpWith: 'or sign up with',
    alreadyRegistered: 'Already registered?',
    signIn: 'Sign in',
  },
  km: {
    backHome: 'ត្រឡប់ទៅទំព័រដើម',
    title: 'បង្កើតគណនីរបស់អ្នក',
    subtitle: 'រៀបចំព័ត៌មានរបស់អ្នក ហើយជ្រើសរើសប្រភេទគណនី។',
    registerWith: 'ចុះឈ្មោះដោយ',
    email: 'អ៊ីមែល',
    phone: 'ទូរស័ព្ទ',
    fullName: 'ឈ្មោះពេញ',
    fullNamePlaceholder: 'ឈ្មោះពេញរបស់អ្នក',
    emailLabel: 'អ៊ីមែល',
    emailPlaceholder: 'you@example.com',
    phoneNumber: 'លេខទូរស័ព្ទ',
    profession: 'វិជ្ជាជីវៈ',
    planner: 'អ្នករៀបចំ',
    vendor: 'អ្នកផ្គត់ផ្គង់',
    password: 'ពាក្យសម្ងាត់',
    passwordPlaceholder: 'យ៉ាងហោចណាស់ 8 តួអក្សរ',
    confirmPassword: 'បញ្ជាក់ពាក្យសម្ងាត់',
    confirmPasswordPlaceholder: 'បញ្ចូលពាក្យសម្ងាត់ម្តងទៀត',
    creating: 'កំពុងបង្កើត...',
    createAccount: 'បង្កើតគណនី',
    orSignUpWith: 'ឬចុះឈ្មោះជាមួយ',
    alreadyRegistered: 'បានចុះឈ្មោះរួចហើយ?',
    signIn: 'ចូល',
  },
  zh: {
    backHome: '返回首页',
    title: '创建您的账户',
    subtitle: '设置您的资料并选择账户身份。',
    registerWith: '注册方式',
    email: '邮箱',
    phone: '手机',
    fullName: '姓名',
    fullNamePlaceholder: '您的姓名',
    emailLabel: '邮箱',
    emailPlaceholder: 'you@example.com',
    phoneNumber: '手机号',
    profession: '身份',
    planner: '策划人',
    vendor: '商家',
    password: '密码',
    passwordPlaceholder: '至少 8 个字符',
    confirmPassword: '确认密码',
    confirmPasswordPlaceholder: '再次输入密码',
    creating: '创建中...',
    createAccount: '创建账户',
    orSignUpWith: '或使用以下方式注册',
    alreadyRegistered: '已经注册？',
    signIn: '登录',
  },
}
const { uiText } = useLanguageCopy(copyByLanguage)
const authLogoSrc = ref(localStorage.getItem('achar_brand_logo') || '/achar-logo.png')
const authBaseUrl = AUTH_PROXY_BASE

function onAuthLogoError() {
  authLogoSrc.value = '/favicon.ico'
}

function clearValidationErrors() {
  validationErrors.value = {}
}

function firstFieldError(field: string) {
  const messages = validationErrors.value[field]
  return Array.isArray(messages) && messages.length ? String(messages[0]) : ''
}

function normalizeValidationErrors(errors: unknown): ValidationErrors {
  if (!errors || typeof errors !== 'object') return {}

  return Object.fromEntries(
    Object.entries(errors).map(([key, value]) => [
      key,
      Array.isArray(value) ? value.map((entry) => String(entry)) : [String(value)],
    ]),
  )
}

function autoFormatPhone(event) {
  let value = event.target.value.replace(/\D/g, '') // strip all non-digits

  if (value.startsWith('0')) {
    value = value.substring(1) 
  }
}

const startSocialAuth = (provider: 'google') => {
  const frontendUrl = encodeURIComponent(window.location.origin)
  const selectedRole = encodeURIComponent(String(form.role || 'user'))
  window.location.href = `${authBaseUrl}/${provider}/redirect?frontend_url=${frontendUrl}&role=${selectedRole}`
}

onMounted(() => {
  const socialError = localStorage.getItem('achar_social_error')
  if (!socialError) return
  errorMessage.value = socialError
  localStorage.removeItem('achar_social_error')
})

const submitRegister = async () => {
  if (submitting.value) return

  submitting.value = true
  successMessage.value = ''
  errorMessage.value = ''
  clearValidationErrors()

  try {
    const payload = {
      name: form.name,
      role: form.role,
      password: form.password,
      password_confirmation: form.password_confirmation,
      email: registerMethod.value === 'email' ? form.email : '',
      phone: registerMethod.value === 'phone' ? form.phone : '',
    }

    const response = await fetch('/api/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify(payload),
    })

    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      if (response.status === 422) {
        validationErrors.value = normalizeValidationErrors(data?.errors)
        const firstError = Object.values(validationErrors.value).flat().find(Boolean)
        errorMessage.value = firstError ? String(firstError) : data?.message ?? 'Validation failed.'
        return
      }

      errorMessage.value = data?.message ?? 'Registration failed.'
      return
    }

    successMessage.value = data?.message ?? 'Registration successful.'

    if (data?.user && data.user.name && (data.user.email || data.user.phone)) {
      emit('success', {
        id: Number(data.user.id || Date.now()),
        name: String(data.user.name),
        email: data.user.email ? String(data.user.email) : '',
        phone: data.user.phone ? String(data.user.phone) : '',
        role: String(data.user.role || form.role || 'user'),
      })
      return
    }

    form.name = ''
    form.email = ''
    form.phone = ''
    form.role = 'user'
    form.password = ''
    form.password_confirmation = ''

    setTimeout(() => {
      emit('switch')
    }, 700)
  } catch (error) {
    errorMessage.value = error instanceof Error && error.message ? error.message : 'Unable to connect to server.'
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <section class="auth-shell auth-shell-register">
    <div class="auth-visual auth-visual-register-panel" aria-hidden="true"></div>
    <main class="auth-panel auth-panel-register">
      <section class="auth-card">
        <router-link class="auth-back-home" to="/">&larr; {{ uiText.backHome }}</router-link>

        <div class="brand-row auth-logo-only">
          <img class="auth-brand-logo auth-brand-logo-lg" :src="authLogoSrc" alt="Achar logo"
            @error="onAuthLogoError" />
        </div>

        <div class="form-head form-head-center">
          <h2>{{ uiText.title }}</h2>
          <p>{{ uiText.subtitle }}</p>
        </div>

        <form class="auth-form" @submit.prevent="submitRegister">
          <label class="field field-choice">
            <span>{{ uiText.registerWith }}</span>
            <div class="role-grid">
              <label class="role-card" :class="{ active: registerMethod === 'email' }">
                <input v-model="registerMethod" type="radio" value="email" name="register_method" />
                <span class="role-icon">&#x2709;&#xFE0F;</span>
                <span class="role-name">{{ uiText.email }}</span>
              </label>

              <label class="role-card" :class="{ active: registerMethod === 'phone' }">
                <input v-model="registerMethod" type="radio" value="phone" name="register_method" />
                <span class="role-icon">&#x1F4F1;</span>
                <span class="role-name">{{ uiText.phone }}</span>
              </label>
            </div>
          </label>

          <label class="field">
            <span>{{ uiText.fullName }}</span>
            <input v-model="form.name" type="text" :placeholder="uiText.fullNamePlaceholder" required />
            <small v-if="firstFieldError('name')" class="field-error">{{ firstFieldError('name') }}</small>
          </label>

          <label v-if="registerMethod === 'email'" class="field">
            <span>{{ uiText.emailLabel }}</span>
            <input v-model="form.email" type="email" :placeholder="uiText.emailPlaceholder" required />
            <small v-if="firstFieldError('email')" class="field-error">{{ firstFieldError('email') }}</small>
          </label>

          <label v-else class="field">
            <span>{{ uiText.phoneNumber }}</span>
            <input v-model="form.phone" type="tel" inputmode="tel" placeholder="+85512345678" pattern="^\+?[0-9]{8,15}$"
              title="Please include country code e.g. +85512345678" required @input="autoFormatPhone" />
            <small v-if="firstFieldError('phone')" class="field-error">{{ firstFieldError('phone') }}</small>
          </label>

          <label class="field field-choice">
            <span>{{ uiText.profession }}</span>
            <div class="role-grid">
              <label class="role-card" :class="{ active: form.role === 'user' }">
                <input v-model="form.role" type="radio" value="user" name="role" />
                <span class="role-icon">&#x1F4C5;</span>
                <span class="role-name">{{ uiText.planner }}</span>
              </label>

              <label class="role-card" :class="{ active: form.role === 'vendor' }">
                <input v-model="form.role" type="radio" value="vendor" name="role" />
                <span class="role-icon">&#x1F3EA;</span>
                <span class="role-name">{{ uiText.vendor }}</span>
              </label>
            </div>
            <small v-if="firstFieldError('role')" class="field-error">{{ firstFieldError('role') }}</small>
          </label>

          <label class="field">
            <span>{{ uiText.password }}</span>
            <div class="password-wrap">
              <input v-model="form.password" :type="showPassword ? 'text' : 'password'"
                :placeholder="uiText.passwordPlaceholder" minlength="8" required />
              <button type="button" class="ghost-btn" @click="showPassword = !showPassword"
                :aria-pressed="showPassword">
                <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                  fill="none" aria-hidden="true">
                  <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" />
                  <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                  aria-hidden="true">
                  <path d="M3 3l18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                  <path d="M10.6 10.6A3 3 0 0013.4 13.4" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" />
                  <path d="M9.9 5.1A10.9 10.9 0 0112 5c6.5 0 10 7 10 7a17.6 17.6 0 01-4.1 4.9" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M6.6 6.6A17.7 17.7 0 002 12s3.5 7 10 7c1.7 0 3.2-.4 4.6-1.1" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </button>
            </div>
            <small v-if="firstFieldError('password')" class="field-error">{{ firstFieldError('password') }}</small>
          </label>

          <label class="field">
            <span>{{ uiText.confirmPassword }}</span>
            <div class="password-wrap">
              <input v-model="form.password_confirmation" :type="showConfirmPassword ? 'text' : 'password'"
                :placeholder="uiText.confirmPasswordPlaceholder" minlength="8" required />
              <button type="button" class="ghost-btn" @click="showConfirmPassword = !showConfirmPassword"
                :aria-pressed="showConfirmPassword">
                <svg v-if="showConfirmPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                  viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" />
                  <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                  aria-hidden="true">
                  <path d="M3 3l18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                  <path d="M10.6 10.6A3 3 0 0013.4 13.4" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" />
                  <path d="M9.9 5.1A10.9 10.9 0 0112 5c6.5 0 10 7 10 7a17.6 17.6 0 01-4.1 4.9" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M6.6 6.6A17.7 17.7 0 002 12s3.5 7 10 7c1.7 0 3.2-.4 4.6-1.1" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </button>
            </div>
            <small v-if="firstFieldError('password_confirmation')" class="field-error">
              {{ firstFieldError('password_confirmation') }}
            </small>
          </label>

          <p v-if="errorMessage" class="form-alert form-alert-error">{{ errorMessage }}</p>
          <p v-if="successMessage" class="form-alert form-alert-success">{{ successMessage }}</p>

          <button class="submit-btn" type="submit" :disabled="submitting">
            {{ submitting ? uiText.creating : uiText.createAccount }}
          </button>
        </form>

        <div class="auth-divider">
          <span>{{ uiText.orSignUpWith }}</span>
        </div>

        <div class="social-grid">
          <button type="button" class="social-btn social-btn-google" @click="startSocialAuth('google')">
            <span class="social-mark">G</span>
            <span>Google</span>
          </button>
        </div>

        <p class="switch-row">
          {{ uiText.alreadyRegistered }}
          <button type="button" class="link-btn" @click="emit('switch')">{{ uiText.signIn }}</button>
        </p>
      </section>
    </main>
  </section>
</template>
