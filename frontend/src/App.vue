<script setup lang="ts">
import { ref, watch } from 'vue'
import Register from './components/RegisterForm.vue'
import Login from './components/LoginForm.vue'
import HomePage from './components/HomePage.vue'

type AuthUser = {
  id: number
  name: string
  email: string
  role: string
}

const AUTH_USER_STORAGE_KEY = 'achar_auth_user'

const currentView = ref<'register' | 'login'>('login')
const loggedInUser = ref<AuthUser | null>(null)

const getStoredUser = (): AuthUser | null => {
  const stored = localStorage.getItem(AUTH_USER_STORAGE_KEY)
  if (!stored) return null

  try {
    return JSON.parse(stored) as AuthUser
  } catch {
    localStorage.removeItem(AUTH_USER_STORAGE_KEY)
    return null
  }
}

loggedInUser.value = getStoredUser()

const toggleView = () => {
  currentView.value = currentView.value === 'register' ? 'login' : 'register'
}

const onLoginSuccess = (user: AuthUser) => {
  loggedInUser.value = user
}

const logout = () => {
  loggedInUser.value = null
  currentView.value = 'login'
  localStorage.removeItem(AUTH_USER_STORAGE_KEY)
}

watch(loggedInUser, (user) => {
  if (!user) return
  localStorage.setItem(AUTH_USER_STORAGE_KEY, JSON.stringify(user))
})
</script>

<template>
  <div class="auth-root">
    <HomePage v-if="loggedInUser" :user="loggedInUser" @logout="logout" />
    <Register v-else-if="currentView === 'register'" @switch="toggleView" />
    <Login v-else @switch="toggleView" @success="onLoginSuccess" />
  </div>
</template>
