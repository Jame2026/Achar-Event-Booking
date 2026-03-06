<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'

const emit = defineEmits<{
  switch: []
  success: [user: { id: number; name: string; email: string; role: string }]
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
const authLogoSrc = ref(localStorage.getItem('achar_brand_logo') || '/achar-logo.png')
const apiOrigin = (import.meta.env.VITE_API_BASE_URL ?? 'http://127.0.0.1:8000').replace(/\/api\/?$/, '')
const apiBaseUrl = `${apiOrigin}/api`
const authBaseUrl = apiOrigin

function onAuthLogoError() {
  authLogoSrc.value = '/favicon.ico'
}

const startSocialAuth = (provider: 'google') => {
  const frontendUrl = encodeURIComponent(window.location.origin)
  window.location.href = `${authBaseUrl}/auth/${provider}/redirect?frontend_url=${frontendUrl}`
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

  try {
    const payload = {
      name: form.name,
      role: form.role,
      password: form.password,
      password_confirmation: form.password_confirmation,
      email: registerMethod.value === 'email' ? form.email : '',
      phone: registerMethod.value === 'phone' ? form.phone : '',
    }

    const response = await fetch(
      `${apiBaseUrl}/register`,
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify(payload),
      },
    )

    const data = await response.json()

    if (!response.ok) {
      if (data?.errors && typeof data.errors === 'object') {
        const firstError = Object.values(data.errors)[0]
        errorMessage.value = Array.isArray(firstError) ? String(firstError[0]) : 'Validation failed.'
      } else {
        errorMessage.value = data?.message ?? 'Registration failed.'
      }
      return
    }

    successMessage.value = data?.message ?? 'Registration successful.'

    if (data?.user && data.user.name && data.user.email) {
      emit('success', {
        id: Number(data.user.id || Date.now()),
        name: String(data.user.name),
        email: String(data.user.email),
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
          <h2>Create your account</h2>
          <p>Set up your profile and choose your account profession.</p>
        </div>

        <form class="auth-form" @submit.prevent="submitRegister">
          <label class="field">
            <span>Register With</span>
            <div class="role-grid">
              <label class="role-card" :class="{ active: registerMethod === 'email' }">
                <input v-model="registerMethod" type="radio" value="email" name="register_method" />
                <span class="role-icon">&#x2709;&#xFE0F;</span>
                <span class="role-name">Email</span>
              </label>

              <label class="role-card" :class="{ active: registerMethod === 'phone' }">
                <input v-model="registerMethod" type="radio" value="phone" name="register_method" />
                <span class="role-icon">&#x1F4F1;</span>
                <span class="role-name">Phone</span>
              </label>
            </div>
          </label>

          <label class="field">
            <span>Full Name</span>
            <input v-model="form.name" type="text" placeholder="Your full name" required />
          </label>

          <label v-if="registerMethod === 'email'" class="field">
            <span>Email</span>
            <input v-model="form.email" type="email" placeholder="you@example.com" required />
          </label>

          <label v-else class="field">
            <span>Phone Number</span>
            <input
              v-model="form.phone"
              type="tel"
              inputmode="tel"
              placeholder="+85512345678"
              pattern="^\+?[0-9]{8,15}$"
              required
            />
          </label>

          <label class="field">
            <span>Profession</span>
            <div class="role-grid">
              <label class="role-card" :class="{ active: form.role === 'user' }">
                <input v-model="form.role" type="radio" value="user" name="role" />
                <span class="role-icon">&#x1F4C5;</span>
                <span class="role-name">Planner</span>
              </label>

              <label class="role-card" :class="{ active: form.role === 'vendor' }">
                <input v-model="form.role" type="radio" value="vendor" name="role" />
                <span class="role-icon">&#x1F3EA;</span>
                <span class="role-name">Vendor</span>
              </label>
            </div>
          </label>

          <label class="field">
            <span>Password</span>
            <div class="password-wrap">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="At least 8 characters"
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
            <span>Confirm Password</span>
            <div class="password-wrap">
              <input
                v-model="form.password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                placeholder="Repeat password"
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
            {{ submitting ? 'Creating...' : 'Create Account' }}
          </button>
        </form>

        <div class="auth-divider">
          <span>or sign up with</span>
        </div>

        <div class="social-grid">
          <button type="button" class="social-btn social-btn-google" @click="startSocialAuth('google')">
            <span class="social-mark">G</span>
            <span>Google</span>
          </button>
        </div>

        <p class="switch-row">
          Already registered?
          <button type="button" class="link-btn" @click="emit('switch')">Sign in</button>
        </p>
      </section>
    </main>
  </section>
</template>

