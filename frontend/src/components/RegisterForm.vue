<script setup lang="ts">
import { reactive, ref } from 'vue'

const emit = defineEmits<{
  switch: []
}>()

const form = reactive({
  name: '',
  email: '',
  role: 'user',
  password: '',
  password_confirmation: '',
})

const submitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const authLogoSrc = ref(localStorage.getItem('achar_brand_logo') || '/achar-logo.png')

function onAuthLogoError() {
  authLogoSrc.value = '/favicon.ico'
}

const submitRegister = async () => {
  if (submitting.value) return

  submitting.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const response = await fetch(
      `${import.meta.env.VITE_API_BASE_URL ?? 'http://127.0.0.1:8000'}/api/register`,
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify(form),
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
    form.name = ''
    form.email = ''
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
  <section class="auth-shell">
    <aside class="auth-visual auth-visual-register">
      <div class="visual-overlay">
        <h1>Build Better Events</h1>
        <p>
          Join as a customer or vendor and manage your booking experience with a professional
          dashboard.
        </p>
      </div>
    </aside>

    <main class="auth-panel">
      <div class="brand-row auth-logo-only">
        <img class="auth-brand-logo auth-brand-logo-lg" :src="authLogoSrc" alt="Achar logo" @error="onAuthLogoError" />
      </div>

      <div class="form-head">
        <h2>Create your account</h2>
        <p>Set up your profile and choose your account profession.</p>
      </div>

      <form class="auth-form" @submit.prevent="submitRegister">
        <label class="field">
          <span>Full Name</span>
          <input v-model="form.name" type="text" placeholder="Your full name" required />
        </label>

        <label class="field">
          <span>Email</span>
          <input v-model="form.email" type="email" placeholder="you@example.com" required />
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
          <input
            v-model="form.password"
            type="password"
            placeholder="At least 8 characters"
            minlength="8"
            required
          />
        </label>

        <label class="field">
          <span>Confirm Password</span>
          <input
            v-model="form.password_confirmation"
            type="password"
            placeholder="Repeat password"
            minlength="8"
            required
          />
        </label>

        <p v-if="errorMessage" class="form-alert form-alert-error">{{ errorMessage }}</p>
        <p v-if="successMessage" class="form-alert form-alert-success">{{ successMessage }}</p>

        <button class="submit-btn" type="submit" :disabled="submitting">
          {{ submitting ? 'Creating...' : 'Create Account' }}
        </button>
      </form>

      <p class="switch-row">
        Already registered?
        <button type="button" class="link-btn" @click="emit('switch')">Sign in</button>
      </p>
    </main>
  </section>
</template>
